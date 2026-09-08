<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

$basePath = '../';

if (!empty($_SESSION['customer_id'])) {
    header('Location: orders.php');
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? ($_POST['redirect'] ?? '../merch.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Session expired, please try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, is_verified FROM customers WHERE email = :e LIMIT 1');
$stmt->execute([':e' => $email]);
$customer = $stmt->fetch();

if ($customer && password_verify($password, $customer['password_hash'])) {
    /*if (!$customer['is_verified']) {
        $otp = generate_otp();
        $stmt = $pdo->prepare(
            'UPDATE customers SET otp_code = :otp, otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE id = :id'
        );
        $stmt->execute([':otp' => $otp, ':id' => $customer['id']]);
        send_otp_email($customer['email'], $customer['full_name'], $otp);

        session_regenerate_id(true);
        $_SESSION['pending_customer_id'] = $customer['id'];

        header('Location: verify.php');
        exit;
    } */

    session_regenerate_id(true);
    $_SESSION['customer_id']   = $customer['id'];
    $_SESSION['customer_name'] = $customer['full_name'];
    header('Location: ' . ($redirect ?: 'orders.php'));
    exit;
}

$error = 'Invalid email or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log In | Hinlo Airsoft Zone</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../partials/header.php'; ?>

<main>
<section class="inner-hero"><div class="container"><h1>LOG IN</h1></div></section>
<section class="account-section">
<div class="container">
<div class="account-card">
    <h2>Log In</h2>
    <p class="subtitle">Welcome back to Hinlo Airsoft Zone</p>

    <?php if ($error): ?>
        <p class="form-note-error" style="text-align:center;margin-bottom:16px;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>">

        <input type="email" name="email" placeholder="Email address" required>
        <input type="password" name="password" placeholder="Password" required>

        <button class="btn" type="submit">Log In</button>
    </form>

    <p class="account-footer">Don't have an account? <a href="register.php">Sign up</a></p>
</div>
</div>
</section>
</main>

<?php include '../partials/footer.php'; ?>
<script src="../js/main.js"></script>
</body>
</html>