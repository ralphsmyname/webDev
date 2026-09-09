<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

if (empty($_SESSION['user_id'])) {
    json_response(['success' => false, 'message' => 'Please log in to place an order.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Your session expired. Please refresh and try again.'], 403);
}

$productNames = ['bottle' => 'Water Bottle', 'bag' => 'Tote Bag', 'hoodie' => 'Hoodie', 'balaclava' => 'Balaclava'];

$validProducts = array_keys($productNames);
$product       = $_POST['product'] ?? '';
$quantity      = (int) ($_POST['quantity'] ?? 0);
$location      = sanitize_string($_POST['location'] ?? '');
$contactNumber = sanitize_phone($_POST['contact_number'] ?? '');

$errors = [];

if (!in_array($product, $validProducts, true)) {
    $errors['product'] = 'Invalid product.';
}
if ($quantity < 1 || $quantity > 20) {
    $errors['quantity'] = 'Quantity must be between 1 and 20.';
}
if ($location === '') {
    $errors['location'] = 'Please enter a delivery or pickup location.';
} elseif (mb_strlen($location) > 255) {
    $errors['location'] = 'Location is too long.';
}
if (($_POST['contact_number'] ?? '') === '' || trim($_POST['contact_number']) === '') {
    $errors['contact_number'] = 'Please enter a contact number.';
} elseif ($contactNumber === null) {
    $errors['contact_number'] = 'Please enter a valid contact number.';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors, 'message' => 'Please fix the highlighted fields.'], 422);
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT stock_quantity FROM merch_stock WHERE product = :p FOR UPDATE');
    $stmt->execute([':p' => $product]);
    $stock = $stmt->fetchColumn();

    if ($stock === false) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'This product is not available.'], 404);
    }

    if ($quantity > (int) $stock) {
        $pdo->rollBack();
        json_response([
            'success' => false,
            'errors'  => ['quantity' => "Only {$stock} left in stock."],
            'message' => "Only {$stock} left in stock.",
        ], 409);
    }

    $stmt = $pdo->prepare('UPDATE merch_stock SET stock_quantity = stock_quantity - :q WHERE product = :p');
    $stmt->execute([':q' => $quantity, ':p' => $product]);

    $stmt = $pdo->prepare(
        'INSERT INTO merch_orders (customer_id, product, quantity, location, contact_number)
         VALUES (:customer_id, :product, :quantity, :location, :contact_number)'
    );
    $stmt->execute([
        ':customer_id'    => $_SESSION['user_id'],
        ':product'        => $product,
        ':quantity'       => $quantity,
        ':location'       => $location,
        ':contact_number' => $contactNumber,
    ]);

    $orderId = (int) $pdo->lastInsertId();

    $pdo->commit();

    $stmt = $pdo->prepare('SELECT full_name, email FROM users WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $customer = $stmt->fetch();

    if ($customer) {
        send_order_confirmation_email(
            $customer['email'],
            $customer['full_name'],
            $productNames[$product],
            $quantity,
            $location,
            $contactNumber,
            $orderId
        );
    }

    json_response(['success' => true, 'message' => "Order placed! Check your email for a confirmation."]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('order.php insert failed: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
}