
<?php

require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['user_id'])) {
    json_response([
        'success' => false,
        'message' => 'Please log in before placing an order.'
    ], 401);
}

if (
    isset($_SESSION['user_role']) &&
    $_SESSION['user_role'] !== 'customer'
) {
    json_response([
        'success' => false,
        'message' => 'Only customer accounts can place orders.'
    ], 403);
}

/*
|--------------------------------------------------------------------------
| REQUEST METHOD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'success' => false,
        'message' => 'Invalid request method.'
    ], 405);
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    json_response([
        'success' => false,
        'message' => 'Your session expired. Please refresh the page and try again.'
    ], 403);
}

/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/

$product = sanitize_string($_POST['product'] ?? '');

$quantityRaw = $_POST['quantity'] ?? '';

$location = sanitize_string(
    $_POST['location'] ?? ''
);

$contactRaw = $_POST['contact_number'] ?? '';

$contact = sanitize_phone($contactRaw);

$errors = [];

/*
|--------------------------------------------------------------------------
| PRODUCT
|--------------------------------------------------------------------------
|
| We DO NOT hard-code product names here.
| The database decides which products are valid.
|--------------------------------------------------------------------------
*/

if ($product === '') {

    $errors['product'] = 'Please select a product.';

}

/*
|--------------------------------------------------------------------------
| QUANTITY
|--------------------------------------------------------------------------
*/

$quantity = filter_var(
    $quantityRaw,
    FILTER_VALIDATE_INT
);

if ($quantity === false || $quantity < 1) {

    $errors['quantity'] = 'Please enter a valid quantity.';

} elseif ($quantity > 100) {

    $errors['quantity'] = 'Maximum quantity is 100.';

}

/*
|--------------------------------------------------------------------------
| LOCATION
|--------------------------------------------------------------------------
*/

if ($location === '') {

    $errors['location'] = 'Please enter your delivery location.';

} elseif (mb_strlen($location) > 255) {

    $errors['location'] = 'Delivery location is too long.';

}

/*
|--------------------------------------------------------------------------
| CONTACT NUMBER
|--------------------------------------------------------------------------
*/

if (trim($contactRaw) === '') {

    $errors['contact_number'] =
        'Please enter your contact number.';

} elseif ($contact === null) {

    $errors['contact_number'] =
        'Please enter a valid contact number.';

}

/*
|--------------------------------------------------------------------------
| STOP IF VALIDATION FAILED
|--------------------------------------------------------------------------
*/

if (!empty($errors)) {

    json_response([
        'success' => false,
        'errors' => $errors,
        'message' => 'Please fix the highlighted fields.'
    ], 422);

}

/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/

$userId = (int) $_SESSION['user_id'];

try {

    /*
    |--------------------------------------------------------------------------
    | START TRANSACTION
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | VERIFY USER
    |--------------------------------------------------------------------------
    */

    $userStmt = $pdo->prepare(
        'SELECT
            id,
            full_name,
            email
         FROM users
         WHERE id = :id
         LIMIT 1'
    );

    $userStmt->execute([
        ':id' => $userId
    ]);

    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {

        $pdo->rollBack();

        json_response([
            'success' => false,
            'message' => 'Your account could not be found. Please log in again.'
        ], 401);

    }

    /*
    |--------------------------------------------------------------------------
    | GET PRODUCT / STOCK / PRICE
    |--------------------------------------------------------------------------
    */

    $stockStmt = $pdo->prepare(
        'SELECT
            product,
            stock_quantity,
            price
         FROM merch_stock
         WHERE product = :product
         LIMIT 1
         FOR UPDATE'
    );

    $stockStmt->execute([
        ':product' => $product
    ]);

    $stockRow = $stockStmt->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | PRODUCT DOES NOT EXIST
    |--------------------------------------------------------------------------
    */

    if (!$stockRow) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        json_response([
            'success' => false,
            'errors' => [
                'product' => 'The selected product is not available.'
            ],
            'message' => 'The selected product is not available.'
        ], 422);

    }

    $stock = (int) $stockRow['stock_quantity'];

    $unitPrice = (float) $stockRow['price'];

    /*
    |--------------------------------------------------------------------------
    | STOCK CHECK
    |--------------------------------------------------------------------------
    */

    if ($stock < $quantity) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        json_response([
            'success' => false,
            'errors' => [
                'quantity' =>
                    'Not enough stock available. Only ' .
                    $stock .
                    ' item(s) remain.'
            ],
            'message' =>
                'Not enough stock available. Only ' .
                $stock .
                ' item(s) remain.'
        ], 409);

    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL
    |--------------------------------------------------------------------------
    */

    $totalPrice = $unitPrice * $quantity;

    /*
    |--------------------------------------------------------------------------
    | DECREASE STOCK
    |--------------------------------------------------------------------------
    */

    $newStock = $stock - $quantity;

    $updateStock = $pdo->prepare(
        'UPDATE merch_stock
         SET stock_quantity = :stock
         WHERE product = :product'
    );

    $updateStock->execute([
        ':stock' => $newStock,
        ':product' => $product
    ]);

    /*
    |--------------------------------------------------------------------------
    | INSERT ORDER
    |--------------------------------------------------------------------------
    */

    $orderStmt = $pdo->prepare(
        'INSERT INTO merch_orders
        (
            customer_id,
            product,
            quantity,
            unit_price,
            location,
            contact_number
        )
        VALUES
        (
            :customer_id,
            :product,
            :quantity,
            :unit_price,
            :location,
            :contact_number
        )'
    );

    $orderStmt->execute([
        ':customer_id' => $userId,
        ':product' => $product,
        ':quantity' => $quantity,
        ':unit_price' => $unitPrice,
        ':location' => $location,
        ':contact_number' => $contact,
    ]);

    $orderId = (int) $pdo->lastInsertId();

    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | EMAIL
    |--------------------------------------------------------------------------
    |
    | Email failure must NOT cancel the order.
    |--------------------------------------------------------------------------
    */

    if (!empty($user['email'])) {

        try {

            /*
             * Use the database product value directly.
             * This avoids another hard-coded product mismatch.
             */

           send_order_confirmation_email(
            $user['email'],
            $user['full_name'],
            $product,
            $quantity,
            $unitPrice,
            $location,
            $contact,
            $orderId
);

        } catch (Throwable $mailError) {

            error_log(
                'Order confirmation email failed for order #' .
                $orderId .
                ': ' .
                $mailError->getMessage()
            );

        }
    }

    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    json_response([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $orderId,
        'total' => number_format(
            $totalPrice,
            2,
            '.',
            ''
        )
    ]);

} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'order.php failed: ' .
        $e->getMessage()
    );

    json_response([
        'success' => false,
        'message' =>
            'Unable to place your order right now. Please try again.'
    ], 500);
}

