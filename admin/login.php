<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Very small login rate-limit to slow down brute forcing.
if (empty($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_attempts_at'] = time();
}
if (time() - $_SESSION['login_attempts_at'] > 900) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_attempts_at'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['login_attempts'] >= 10) {
        $error = 'Too many attempts. Please wait a few minutes and try again.';
    } elseif (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Session expired — please reload and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_last_active'] = time();
            $_SESSION['login_attempts'] = 0;
            header('Location: index.php');
            exit;
        }

        $_SESSION['login_attempts']++;
        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | Hinlo Airsoft Zone</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">
    <div class="admin-login-box admin-card">
        <h2>Admin login</h2>

        <?php if (isset($_GET['timeout'])): ?>
            <p class="admin-alert admin-alert-error">Your session timed out. Please log in again.</p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="admin-alert admin-alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form class="admin-form" method="post" action="login.php">
            <?= csrf_field() ?>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button class="btn admin-btn-primary" type="submit" style="margin-top:20px;width:100%;border-radius:4px;">
                Log in
            </button>
        </form>
    </div>
</body>
</html>
