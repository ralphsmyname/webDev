<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer_auth.php';
require_customer_login('login.php');
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->prepare('SELECT * FROM merch_orders WHERE customer_id = :id ORDER BY created_at DESC');
$stmt->execute([':id' => $_SESSION['customer_id']]);
$orders = $stmt->fetchAll();

$productNames = ['bottle' => 'Water Bottle', 'bag' => 'Tote Bag', 'hoodie' => 'Hoodie', 'balaclava' => 'Balaclava'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Orders | Hinlo Airsoft Zone</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../partials/header.php'; ?>

<main>
<section class="inner-hero"><div class="container"><h1>MY ORDERS</h1></div></section>
<section class="page-section">
<div class="container">
<p>Logged in as <?= htmlspecialchars($_SESSION['customer_name'], ENT_QUOTES, 'UTF-8') ?> — <a href="logout.php">log out</a></p>

<?php if (empty($orders)): ?>
<p>You haven't placed any orders yet. <a href="../merch.php">Browse merch</a></p>
<?php else: ?>
<table class="admin-table">
<thead><tr><th>Product</th><th>Qty</th><th>Status</th><th>Ordered</th></tr></thead>
<tbody>
<?php foreach ($orders as $o): ?>
<tr>
<td><?= htmlspecialchars($productNames[$o['product']] ?? $o['product'], ENT_QUOTES, 'UTF-8') ?></td>
<td><?= (int) $o['quantity'] ?></td>
<td><?= htmlspecialchars(ucfirst($o['status']), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars($o['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</section>
</main>

<?php include '../partials/footer.php'; ?>
<script src="../js/main.js"></script>
</body>
</html>