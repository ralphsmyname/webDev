
<?php

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';
$productNames = [
    'bottle' => 'Water Bottle',
    'bag' => 'Tote Bag',
    'hoodie' => 'Hoodie',
    'balaclava' => 'Balaclava'
];

$notice = '';
$noticeType = 'success';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $notice = 'Session expired, please try again.';
        $noticeType = 'error';
    } else {
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = $pdo->prepare('DELETE FROM merch_orders WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $notice = 'Order deleted.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_status') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $notice = 'Session expired, please try again.';
        $noticeType = 'error';
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['pending', 'processing', 'completed', 'cancelled'], true)) {
            $stmt = $pdo->prepare('UPDATE merch_orders SET status = :s WHERE id = :id');
            $stmt->execute([':s' => $status, ':id' => $id]);
            $notice = 'Status updated.';

            if ($status === 'completed') {
                $stmt = $pdo->prepare(
                    'SELECT mo.product, mo.quantity, c.full_name, c.email
                     FROM merch_orders mo
                     JOIN customers c ON c.id = mo.customer_id
                     WHERE mo.id = :id'
                );
                $stmt->execute([':id' => $id]);
                $orderInfo = $stmt->fetch();

                if ($orderInfo) {
                    $productLabel = $productNames[$orderInfo['product']] ?? $orderInfo['product'];
                    send_order_completed_email(
                        $orderInfo['email'],
                        $orderInfo['full_name'],
                        $productLabel,
                        (int) $orderInfo['quantity'],
                        $id
                    );
                }
            }
        }
    }
}



$stockRows = $pdo->query(
    'SELECT product, stock_quantity FROM merch_stock'
)->fetchAll();

$rows = $pdo->query(
    'SELECT o.*, c.full_name, c.email
     FROM merch_orders o
     JOIN customers c ON c.id = o.customer_id
     ORDER BY o.created_at DESC'
)->fetchAll();

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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Merch Orders | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="admin-body">

    <?php include '_header.php'; ?>

    <div class="admin-wrap">

        <h1>Merch orders</h1>

        <?php if ($notice): ?>
            <p class="admin-alert admin-alert-<?= $noticeType === 'error' ? 'error' : 'success' ?>">
                <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <div class="admin-card">

            <h2 style="margin-top:0;">Current stock</h2>

            <div class="admin-grid" style="margin-bottom:0;">

                <?php foreach ($stockRows as $s): ?>

                    <div class="admin-stat">

                        <span class="num">
                            <?= (int) $s['stock_quantity'] ?>
                        </span>

                        <?= htmlspecialchars(
                            $productNames[$s['product']] ?? $s['product'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                        <form
                            method="post"
                            action="../actions/restock.php"
                            style="margin-top:8px;display:flex;gap:6px;"
                        >
                            <?= csrf_field() ?>

                            <input
                                type="hidden"
                                name="product"
                                value="<?= htmlspecialchars($s['product'], ENT_QUOTES, 'UTF-8') ?>"
                            >

                            <input
                                type="number"
                                name="amount"
                                min="1"
                                placeholder="Add"
                                style="width:70px;padding:4px;"
                            >

                            <button
                                class="admin-btn"
                                type="submit"
                                style="font-size:12px;padding:4px 10px;"
                            >
                                +
                            </button>
                        </form>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

        <div class="admin-card">

            <span class="admin-pill-count">
                <?= count($rows) ?> total
            </span>

            <?php if (empty($rows)): ?>

                <p class="admin-empty">No orders yet.</p>

            <?php else: ?>

                <table class="admin-table">
    <thead>
        <tr>
            <th>Customer</th>
            <th>Email</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Location</th>
            <th>Contact</th>
            <th>Status</th>
            <th>Ordered</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $o): ?>
            <tr>
                <td><?= htmlspecialchars($o['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($o['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($productNames[$o['product']] ?? $o['product'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) $o['quantity'] ?></td>
                <td><?= htmlspecialchars($o['location'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($o['contact_number'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <span class="admin-badge admin-badge-<?= $o['status'] === 'completed' ? 'confirmed' : ($o['status'] === 'cancelled' ? 'unread' : 'pending') ?>">
                        <?= htmlspecialchars(ucfirst($o['status']), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($o['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <div class="admin-actions">
                        <form method="post" action="orders.php" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="set_status">
                            <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                            <select name="status" onchange="this.form.submit()" class="admin-btn" style="padding:4px 8px;">
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <form method="post" action="orders.php" style="display:inline;" onsubmit="return confirm('Delete this order?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                            <button class="admin-btn admin-btn-danger" type="submit">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

            <?php endif; ?>

        </div>

    </div>

</body>

</html>

