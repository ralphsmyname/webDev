<?php
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['user_role'] === 'admin' ? 'admin/index.php' : 'merch.php'));
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? ($_POST['redirect'] ?? '');

if (!empty($_GET['timeout'])) {
    $error = 'Your session timed out. Please log in again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Session expired, please try again.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT id, role, full_name, password_hash FROM users WHERE email = :email OR username = :username LIMIT 1');
        $stmt->execute([':email' => $identifier, ':username' => $identifier]);      
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];

            if ($user['role'] === 'admin') {
                $_SESSION['admin_last_active'] = time();
                header('Location: ' . ($redirect ?: 'admin/index.php'));
            } else {
                header('Location: ' . ($redirect ?: 'merch.php'));
            }
            exit;
        }

        $error = 'Invalid email/username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log In | Hinlo Airsoft Zone</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'partials/header.php'; ?>

<main>
<section class="inner-hero"><div class="container"><h1>LOG IN</h1></div></section>
<section class="account-section">
<div class="container">
<div class="account-card">
    <h2>Log In</h2>
    <p class="subtitle">Customers and admins both log in here</p>

    <?php if ($error): ?>
        <p class="form-note-error" style="text-align:center;margin-bottom:16px;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>">

        <input type="text" name="identifier" placeholder="Email or username" required autofocus>
        <input type="password" name="password" placeholder="Password" required>

        <button class="btn" type="submit">Log In</button>
    </form>

    <p class="account-footer">Don't have an account? <a href="account/register.php">Sign up</a></p>
</div>
</div>
</section>
</main>

<?php include 'partials/footer.php'; ?>
<script src="js/main.js"></script>
</body>
</html>