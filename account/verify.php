<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

$basePath = '../';

if (empty($_SESSION['pending_customer_id'])) {
    header('Location: login.php');
    exit;
}

$pendingId = $_SESSION['pending_customer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['verify_error'] = 'Session expired, please try again.';
    } elseif (isset($_POST['resend'])) {
        $otp = generate_otp();
        $stmt = $pdo->prepare(
            'UPDATE customers SET otp_code = :otp, otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE id = :id'
        );
        $stmt->execute([':otp' => $otp, ':id' => $pendingId]);

        $stmt = $pdo->prepare('SELECT full_name, email FROM customers WHERE id = :id');
        $stmt->execute([':id' => $pendingId]);
        $c = $stmt->fetch();

        if ($c) {
            send_otp_email($c['email'], $c['full_name'], $otp);
            $_SESSION['verify_resent'] = true;
        }
    } else {
        $code = trim($_POST['otp'] ?? '');

        $stmt = $pdo->prepare(
            'SELECT id, full_name, otp_code, otp_expires_at FROM customers WHERE id = :id'
        );
        $stmt->execute([':id' => $pendingId]);
        $customer = $stmt->fetch();

        if (!$customer) {
            $_SESSION['verify_error'] = 'Something went wrong. Please sign up again.';
        } elseif ($customer['otp_code'] === null || $code !== $customer['otp_code']) {
            $_SESSION['verify_error'] = 'Incorrect code. Please try again.';
        } elseif (strtotime($customer['otp_expires_at']) < time()) {
            $_SESSION['verify_error'] = 'This code has expired. Click "Resend code" below.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE customers SET is_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE id = :id'
            );
            $stmt->execute([':id' => $customer['id']]);

            session_regenerate_id(true);
            unset($_SESSION['pending_customer_id']);
            $_SESSION['customer_id']   = $customer['id'];
            $_SESSION['customer_name'] = $customer['full_name'];

            header('Location: ../merch.php');
            exit;
        }
    }

    header('Location: verify.php');
    exit;
}

$error = $_SESSION['verify_error'] ?? '';
$resent = !empty($_SESSION['verify_resent']);
unset($_SESSION['verify_error'], $_SESSION['verify_resent']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verify Email | Hinlo Airsoft Zone</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../partials/header.php'; ?>

<main>
<section class="inner-hero"><div class="container"><h1>VERIFY YOUR EMAIL</h1></div></section>
<section class="account-section">
<div class="container">
<div class="account-card">
    <h2>Enter Code</h2>
    <p class="subtitle">We sent a 6-digit code to your email</p>

    <?php if ($resent): ?>
        <p style="text-align:center;color:var(--green);margin-bottom:16px;">A new code has been sent.</p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="form-note-error" style="text-align:center;margin-bottom:16px;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="verify.php">
        <?= csrf_field() ?>
        <input type="text" name="otp" placeholder="6-digit code" maxlength="6" inputmode="numeric" autofocus>
        <button class="btn" type="submit">Verify</button>
    </form>

    <form method="post" action="verify.php" style="margin-top:14px;">
        <?= csrf_field() ?>
        <input type="hidden" name="resend" value="1">
        <button type="submit" style="background:none;border:none;color:var(--green);font-weight:bold;cursor:pointer;">
            Resend code
        </button>
    </form>
</div>
</div>
</section>
</main>

<?php include '../partials/footer.php'; ?>
<script src="../js/main.js"></script>
</body>
</html>