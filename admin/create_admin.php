<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$errors = [];
$success = false;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['_general'] = 'Session expired, please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';

        if (mb_strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters.';
        }
        if (mb_strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $errors['confirm'] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO users (role, full_name, username, password_hash) VALUES ('admin', :u, :u, :p)"
                );
                $stmt->execute([
                    ':u' => $username,
                    ':p' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $success = true;
            } catch (PDOException $e) {
                if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), '1062')) {
                    $errors['username'] = 'That username is already taken.';
                } else {
                    error_log('create_admin.php: ' . $e->getMessage());
                    $errors['_general'] = 'Something went wrong.';
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Admin | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">

    <?php include '_header.php'; ?>

    <div class="admin-wrap">
        <h1>Add admin account</h1>

        <div class="admin-card" style="max-width:420px;">
            <?php if ($success): ?>
                <p class="admin-alert admin-alert-success">Admin account "<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" created.</p>
            <?php endif; ?>

            <?php if (!empty($errors['_general'])): ?>
                <p class="admin-alert admin-alert-error"><?= htmlspecialchars($errors['_general'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form class="admin-form" method="post" action="create_admin.php">
                <?= csrf_field() ?>

                <label for="username">Username</label>
                <input type="text" id="username" name="username" maxlength="50">
                <?php if (!empty($errors['username'])): ?><span class="field-error"><?= htmlspecialchars($errors['username'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>

                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <?php if (!empty($errors['password'])): ?><span class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>

                <label for="confirm">Confirm password</label>
                <input type="password" id="confirm" name="confirm">
                <?php if (!empty($errors['confirm'])): ?><span class="field-error"><?= htmlspecialchars($errors['confirm'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>

                <button class="admin-btn admin-btn-primary" type="submit" style="margin-top:20px;width:100%;">Create account</button>
            </form>
        </div>
    </div>

</body>
</html>