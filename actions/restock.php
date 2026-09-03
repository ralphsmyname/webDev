<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    header('Location: ../admin/orders.php');
    exit;
}

$product = $_POST['product'] ?? '';
$amount  = (int) ($_POST['amount'] ?? 0);

if (in_array($product, ['bottle', 'bag', 'hoodie', 'balaclava'], true) && $amount > 0) {
    $stmt = $pdo->prepare('UPDATE merch_stock SET stock_quantity = stock_quantity + :a WHERE product = :p');
    $stmt->execute([':a' => $amount, ':p' => $product]);
}

header('Location: ../admin/orders.php');
exit;