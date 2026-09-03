<?php
/**
 * One-time setup: creates the first admin account.
 * This page refuses to run once an admin_users row already exists —
 * delete it after use if you want to be extra tidy, but it's safe
 * to leave in place either way.
 */
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

$count = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();

if ($count > 0) {
    http_response_code(403);
    die('An admin account already exists. Go to <a href="login.php">login.php</a>. If you need to reset it, do so directly in the database.');
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Session expired — please reload and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';

        if ($username === '' || mb_strlen($username) < 3) {
            $error = 'Username must be at least 3 characters.';
        } elseif (mb_strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (:u, :p)');
            $stmt->execute([
                ':u' => $username,
                ':p' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $success = true;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Setup | Hinlo Airsoft Zone</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">
    <div class="admin-login-box admin-card">
        <h2>Create admin account</h2>

        <?php if ($success): ?>
            <p class="admin-alert admin-alert-success">Admin account created. You can now <a href="login.php">log in</a>.</p>
        <?php else: ?>
            <?php if ($error): ?>
                <p class="admin-alert admin-alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form class="admin-form" method="post" action="setup.php">
                <?= csrf_field() ?>

                <label for="username">Username</label>
                <input type="text" id="username" name="username" required minlength="3" maxlength="50">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">

                <label for="confirm">Confirm password</label>
                <input type="password" id="confirm" name="confirm" required minlength="8">

                <button class="btn admin-btn-primary" type="submit" style="margin-top:20px;width:100%;border-radius:4px;">
                    Create account
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
