<?php
require_once 'includes/csrf.php';
require_once 'includes/functions.php';
require_once 'includes/customer_auth.php';
require_once 'config/database.php';

$merchUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/merch.php';
require_customer_login('login.php', $merchUrl);

$validProducts = [
    'bottle'    => 'Water Bottle',
    'bag'       => 'Tote Bag',
    'hoodie'    => 'Hoodie',
    'balaclava' => 'Balaclava',
];

$product = $_GET['product'] ?? '';
if (!isset($validProducts[$product])) {
    die('Unknown product.');
}

$stmt = $pdo->prepare('SELECT price FROM merch_stock WHERE product = :p');
$stmt->execute([':p' => $product]);
$unitPrice = (float) ($stmt->fetchColumn() ?: 0);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order <?= htmlspecialchars($validProducts[$product], ENT_QUOTES, 'UTF-8') ?> | Hinlo Airsoft Zone</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'partials/header.php'; ?>

<main>
<section class="inner-hero"><div class="container"><h1>ORDER: <?= strtoupper(htmlspecialchars($validProducts[$product], ENT_QUOTES, 'UTF-8')) ?></h1></div></section>
<section class="page-section">
<div class="container" style="max-width:480px;">

<p style="text-align:center;font-size:18px;">Price: <strong><?= htmlspecialchars(format_price($unitPrice), ENT_QUOTES, 'UTF-8') ?></strong> each</p>

<form class="contact-box" data-demo-form data-endpoint="actions/order.php" method="post" action="actions/order.php" novalidate>
<?= csrf_field() ?>
<input type="hidden" name="product" value="<?= htmlspecialchars($product, ENT_QUOTES, 'UTF-8') ?>">

<label for="quantity">Quantity</label>
<input type="number" id="quantity" name="quantity" min="1" max="20" value="1" data-unit-price="<?= htmlspecialchars((string) $unitPrice, ENT_QUOTES, 'UTF-8') ?>" oninput="document.getElementById('order-total').textContent = (this.value > 0 ? (this.value * this.dataset.unitPrice).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00');">
<span class="field-error" data-error-for="quantity"></span>

<p style="text-align:center;margin-top:4px;">Total: ₱<span id="order-total"><?= number_format($unitPrice, 2) ?></span></p>

<label for="location">Delivery / pickup location</label>
<input type="text" id="location" name="location" maxlength="255" placeholder="e.g. Barangay, city">
<span class="field-error" data-error-for="location"></span>

<label for="contact_number">Contact number</label>
<input type="text" id="contact_number" name="contact_number" maxlength="30" placeholder="e.g. 09123456789">
<span class="field-error" data-error-for="contact_number"></span>

<button class="btn" type="submit">Place order</button>
<p class="form-note"></p>
</form>

<p style="margin-top:16px;">Logged in as <?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></p>
<div style="display:flex;gap:10px;justify-content:center;margin-top:12px;">
    <a class="btn btn-light" href="account/orders.php">My Orders</a>
    <a class="btn" href="logout.php">Log Out</a>
</div>
</div>
</section>
</main>

<?php include 'partials/footer.php'; ?>
<script src="js/main.js"></script>
</body>
</html>