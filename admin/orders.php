```php
<?php

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

$productNames = [
    'bottle'   => 'Water Bottle',
    'bag'      => 'Tote Bag',
    'hoodie'   => 'Hoodie',
    'balaclava'=> 'Balaclava'
];

$notice = '';
$noticeType = 'success';

/*
|--------------------------------------------------------------------------
| DELETE ORDER
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'delete'
) {

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {

        $notice = 'Session expired, please try again.';
        $noticeType = 'error';

    } else {

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {

            $notice = 'Invalid order.';
            $noticeType = 'error';

        } else {

            try {

                $stmt = $pdo->prepare(
                    'DELETE FROM merch_orders
                     WHERE id = :id'
                );

                $stmt->execute([
                    ':id' => $id
                ]);

                if ($stmt->rowCount() > 0) {
                    $notice = 'Order deleted.';
                } else {
                    $notice = 'Order not found.';
                    $noticeType = 'error';
                }

            } catch (Throwable $e) {

                error_log(
                    'admin/orders.php delete failed: ' .
                    $e->getMessage()
                );

                $notice = 'Unable to delete the order.';
                $noticeType = 'error';
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| UPDATE PRODUCT PRICE
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'set_price'
) {

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {

        $notice = 'Session expired, please try again.';
        $noticeType = 'error';

    } else {

        $product = sanitize_string($_POST['product'] ?? '');
        $price = filter_var(
            $_POST['price'] ?? null,
            FILTER_VALIDATE_FLOAT
        );

        if (!isset($productNames[$product])) {

            $notice = 'Invalid product.';
            $noticeType = 'error';

        } elseif ($price === false || $price < 0) {

            $notice = 'Price must be zero or more.';
            $noticeType = 'error';

        } else {

            try {

                $stmt = $pdo->prepare(
                    'UPDATE merch_stock
                     SET price = :price
                     WHERE product = :product'
                );

                $stmt->execute([
                    ':price' => $price,
                    ':product' => $product
                ]);

                if ($stmt->rowCount() > 0) {
                    $notice = 'Price updated successfully.';
                } else {
                    $notice = 'Product price was not changed.';
                }

            } catch (Throwable $e) {

                error_log(
                    'admin/orders.php price update failed: ' .
                    $e->getMessage()
                );

                $notice = 'Unable to update the price.';
                $noticeType = 'error';
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| UPDATE ORDER STATUS
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'set_status'
) {

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {

        $notice = 'Session expired, please try again.';
        $noticeType = 'error';

    } else {

        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        $allowedStatuses = [
            'pending',
            'processing',
            'completed',
            'cancelled'
        ];

        if ($id <= 0) {

            $notice = 'Invalid order.';
            $noticeType = 'error';

        } elseif (!in_array($status, $allowedStatuses, true)) {

            $notice = 'Invalid order status.';
            $noticeType = 'error';

        } else {

            try {

                /*
                |--------------------------------------------------------------------------
                | Get old status first
                |--------------------------------------------------------------------------
                */

                $oldStmt = $pdo->prepare(
                    'SELECT status
                     FROM merch_orders
                     WHERE id = :id
                     LIMIT 1'
                );

                $oldStmt->execute([
                    ':id' => $id
                ]);

                $oldStatus = $oldStmt->fetchColumn();

                if ($oldStatus === false) {

                    $notice = 'Order not found.';
                    $noticeType = 'error';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Update status
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare(
                        'UPDATE merch_orders
                         SET status = :status
                         WHERE id = :id'
                    );

                    $stmt->execute([
                        ':status' => $status,
                        ':id' => $id
                    ]);

                    $notice = 'Order status updated.';

                    /*
                    |--------------------------------------------------------------------------
                    | Send completion email
                    |--------------------------------------------------------------------------
                    |
                    | Only send the completion email when the order changes
                    | TO completed. This prevents repeated emails if the
                    | admin selects "completed" again.
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $status === 'completed' &&
                        $oldStatus !== 'completed'
                    ) {

                        $stmt = $pdo->prepare(
                            'SELECT
                                mo.product,
                                mo.quantity,
                                mo.unit_price,
                                u.full_name,
                                u.email
                             FROM merch_orders mo
                             JOIN users u
                               ON u.id = mo.customer_id
                             WHERE mo.id = :id
                             LIMIT 1'
                        );

                        $stmt->execute([
                            ':id' => $id
                        ]);

                        $orderInfo = $stmt->fetch(PDO::FETCH_ASSOC);

                        if (
                            $orderInfo &&
                            !empty($orderInfo['email'])
                        ) {

                            $productLabel =
                                $productNames[$orderInfo['product']]
                                ?? $orderInfo['product'];

                            try {

                                send_order_completed_email(
                                    $orderInfo['email'],
                                    $orderInfo['full_name'],
                                    $productLabel,
                                    (int) $orderInfo['quantity'],
                                    $id
                                );

                            } catch (Throwable $mailError) {

                                error_log(
                                    'Completion email failed for order #' .
                                    $id .
                                    ': ' .
                                    $mailError->getMessage()
                                );

                                $notice =
                                    'Order status updated, but the completion email could not be sent.';
                            }
                        }
                    }
                }

            } catch (Throwable $e) {

                error_log(
                    'admin/orders.php status update failed: ' .
                    $e->getMessage()
                );

                $notice = 'Unable to update the order status.';
                $noticeType = 'error';
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| GET CURRENT STOCK
|--------------------------------------------------------------------------
*/

try {

    $stockRows = $pdo->query(
        'SELECT
            product,
            stock_quantity,
            price
         FROM merch_stock
         ORDER BY product ASC'
    )->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {

    error_log(
        'admin/orders.php stock query failed: ' .
        $e->getMessage()
    );

    $stockRows = [];

    $notice = 'Unable to load current stock.';
    $noticeType = 'error';
}

/*
|--------------------------------------------------------------------------
| GET ORDERS
|--------------------------------------------------------------------------
|
| IMPORTANT:
| orders.customer_id -> users.id
|
| The old query incorrectly joined users as "u" but selected
| c.full_name and c.email.
|
|--------------------------------------------------------------------------
*/

try {

    $rows = $pdo->query(
        'SELECT
            o.id,
            o.customer_id,
            o.product,
            o.quantity,
            o.unit_price,
            o.location,
            o.contact_number,
            o.status,
            o.created_at,
            o.updated_at,
            u.full_name,
            u.email
         FROM merch_orders o
         JOIN users u
           ON u.id = o.customer_id
         ORDER BY o.created_at DESC'
    )->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {

    error_log(
        'admin/orders.php orders query failed: ' .
        $e->getMessage()
    );

    $rows = [];

    $notice = 'Unable to load orders.';
    $noticeType = 'error';
}

$statuses = [
    'pending',
    'processing',
    'completed',
    'cancelled'
];

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Merch Orders | Admin</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body class="admin-body">

<?php include '_header.php'; ?>

<div class="admin-wrap">

    <h1>Merch Orders</h1>

    <?php if ($notice): ?>

        <p class="admin-alert admin-alert-<?= 
            $noticeType === 'error'
                ? 'error'
                : 'success'
        ?>">

            <?= htmlspecialchars(
                $notice,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </p>

    <?php endif; ?>


    <!-- =========================================================
         CURRENT STOCK
    ========================================================== -->

    <div class="admin-card">

        <h2 style="margin-top:0;">
            Current Stock
        </h2>

        <?php if (empty($stockRows)): ?>

            <p class="admin-empty">
                No stock products found.
            </p>

        <?php else: ?>

            <div
                class="admin-grid"
                style="margin-bottom:0;"
            >

                <?php foreach ($stockRows as $s): ?>

                    <?php

                    $stockProduct = $s['product'];

                    $stockProductName =
                        $productNames[$stockProduct]
                        ?? $stockProduct;

                    $stockQuantity =
                        (int) $s['stock_quantity'];

                    $stockPrice =
                        (float) $s['price'];

                    ?>

                    <div class="admin-stat">

                        <span class="num">
                            <?= $stockQuantity ?>
                        </span>

                        <?= htmlspecialchars(
                            $stockProductName,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                        <div
                            style="
                                margin-top:6px;
                                font-weight:600;
                            "
                        >

                            <?= htmlspecialchars(
                                format_price($stockPrice),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </div>


                        <!-- RESTOCK -->

                        <form
                            method="post"
                            action="../actions/restock.php"
                            class="admin-inline-form"
                        >

                            <?= csrf_field() ?>

                            <input
                                type="hidden"
                                name="product"
                                value="<?= htmlspecialchars(
                                    $stockProduct,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <input
                                type="number"
                                name="amount"
                                min="1"
                                placeholder="Add"
                                required
                            >

                            <button
                                class="admin-btn"
                                type="submit"
                            >
                                +
                            </button>

                        </form>


                        <!-- SET PRICE -->

                        <form
                            method="post"
                            action="orders.php"
                            class="admin-inline-form admin-inline-form-price"
                        >

                            <?= csrf_field() ?>

                            <input
                                type="hidden"
                                name="action"
                                value="set_price"
                            >

                            <input
                                type="hidden"
                                name="product"
                                value="<?= htmlspecialchars(
                                    $stockProduct,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <input
                                type="number"
                                name="price"
                                min="0"
                                step="0.01"
                                placeholder="New price"
                                required
                            >

                            <button
                                class="admin-btn"
                                type="submit"
                            >
                                Set price
                            </button>

                        </form>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>


    <!-- =========================================================
         ORDERS
    ========================================================== -->

    <div class="admin-card">

        <span class="admin-pill-count">
            <?= count($rows) ?> total
        </span>


        <?php if (empty($rows)): ?>

            <p class="admin-empty">
                No orders yet.
            </p>

        <?php else: ?>

            <div style="overflow-x:auto;">

                <table class="admin-table">

                    <thead>

                        <tr>

                            <th>Customer</th>

                            <th>Email</th>

                            <th>Product</th>

                            <th>Qty</th>

                            <th>Unit Price</th>

                            <th>Total</th>

                            <th>Location</th>

                            <th>Contact</th>

                            <th>Status</th>

                            <th>Ordered</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach ($rows as $o): ?>

                        <?php

                        $orderProduct =
                            $productNames[$o['product']]
                            ?? $o['product'];

                        $quantity =
                            (int) $o['quantity'];

                        $unitPrice =
                            (float) ($o['unit_price'] ?? 0);

                        $total =
                            $unitPrice * $quantity;

                        $status =
                            $o['status'] ?? 'pending';

                        ?>

                        <tr>

                            <!-- CUSTOMER -->

                            <td>
                                <?= htmlspecialchars(
                                    $o['full_name'] ?? 'Unknown',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>


                            <!-- EMAIL -->

                            <td>
                                <?= htmlspecialchars(
                                    $o['email'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>


                            <!-- PRODUCT -->

                            <td>
                                <?= htmlspecialchars(
                                    $orderProduct,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>


                            <!-- QUANTITY -->

                            <td>
                                <?= $quantity ?>
                            </td>


                            <!-- UNIT PRICE -->

                            <td>
                                <?= htmlspecialchars(
                                    format_price($unitPrice),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>


                            <!-- TOTAL -->

                            <td>
                                <?= htmlspecialchars(
                                    format_price($total),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>


                            <!-- LOCATION -->

                            <td>
                                <?= htmlspecialchars(
                                    $o['location'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>


                            <!-- CONTACT -->

                            <td>
                                <?= htmlspecialchars(
                                    $o['contact_number'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="admin-badge admin-badge-<?=
                                        $status === 'completed'
                                            ? 'confirmed'
                                            : (
                                                $status === 'cancelled'
                                                    ? 'unread'
                                                    : 'pending'
                                            )
                                    ?>"
                                >

                                    <?= htmlspecialchars(
                                        ucfirst($status),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </span>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?= htmlspecialchars(
                                    $o['created_at'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </td>


                            <!-- ACTIONS -->

                            <td>

                                <div class="admin-actions">


                                    <!-- CHANGE STATUS -->

                                    <form
                                        method="post"
                                        action="orders.php"
                                        style="display:inline;"
                                    >

                                        <?= csrf_field() ?>

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="set_status"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $o['id'] ?>"
                                        >

                                        <select
                                            name="status"
                                            onchange="this.form.submit()"
                                            class="admin-btn"
                                            style="
                                                padding:4px 8px;
                                            "
                                        >

                                            <?php foreach (
                                                $statuses
                                                as $availableStatus
                                            ): ?>

                                                <option
                                                    value="<?= htmlspecialchars(
                                                        $availableStatus,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                    <?= $status === $availableStatus
                                                        ? 'selected'
                                                        : ''
                                                    ?>
                                                >

                                                    <?= htmlspecialchars(
                                                        ucfirst(
                                                            $availableStatus
                                                        ),
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>

                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </form>


                                    <!-- DELETE -->

                                    <form
                                        method="post"
                                        action="orders.php"
                                        style="display:inline;"
                                        onsubmit="return confirm('Delete this order?');"
                                    >

                                        <?= csrf_field() ?>

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $o['id'] ?>"
                                        >

                                        <button
                                            class="admin-btn admin-btn-danger"
                                            type="submit"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>
```
