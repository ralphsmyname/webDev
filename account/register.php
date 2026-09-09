<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$basePath = '../';

if (!empty($_SESSION['user_id'])) {
    header('Location: orders.php');
    exit;
}

$errors = [];
$fullName = '';
$emailRaw = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['_general'] = 'Session expired, please try again.';
    } else {
        $fullName = sanitize_string($_POST['full_name'] ?? '');
        $emailRaw = $_POST['email'] ?? '';
        $email    = sanitize_email($emailRaw);
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';

        if ($fullName === '') {
            $errors['full_name'] = 'Please enter your name.';
        }
        if ($email === null) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if (mb_strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $errors['confirm'] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO users (role, full_name, email, password_hash) VALUES ('customer', :n, :e, :p)"
                );
                $stmt->execute([
                    ':n' => $fullName,
                    ':e' => $email,
                    ':p' => password_hash($password, PASSWORD_DEFAULT),
                ]);

                session_regenerate_id(true);
                $_SESSION['user_id']   = $pdo->lastInsertId();
                $_SESSION['user_role'] = 'customer';
                $_SESSION['user_name'] = $fullName;

                header('Location: orders.php');
                exit;
            } catch (PDOException $e) {
                if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), '1062')) {
                    $errors['email'] = 'An account with this email already exists.';
                } else {
                    error_log('register.php: ' . $e->getMessage());
                    $errors['_general'] = 'Something went wrong. Please try again.';
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
<title>Sign Up | Hinlo Airsoft Zone</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../partials/header.php'; ?>

<main>
<section class="inner-hero"><div class="container"><h1>CREATE AN ACCOUNT</h1></div></section>
<section class="account-section">
<div class="container">
<div class="account-card">
    <h2>Sign Up</h2>
    <p class="subtitle">Create your Hinlo Airsoft Zone account</p>

    <?php if (!empty($errors['_general'])): ?>
        <p class="form-note-error" style="text-align:center;margin-bottom:16px;"><?= htmlspecialchars($errors['_general'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="register.php" novalidate>
        <?= csrf_field() ?>

        <input type="text" name="full_name" placeholder="Full name" value="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>">
        <span class="field-error"><?= htmlspecialchars($errors['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>

        <input type="email" name="email" placeholder="Email address" value="<?= htmlspecialchars($emailRaw, ENT_QUOTES, 'UTF-8') ?>">
        <span class="field-error"><?= htmlspecialchars($errors['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>

        <input type="password" name="password" placeholder="Password">
        <span class="field-error"><?= htmlspecialchars($errors['password'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>

        <input type="password" name="confirm" placeholder="Confirm password">
        <span class="field-error"><?= htmlspecialchars($errors['confirm'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>

        <button class="btn" type="submit">Sign Up</button>
    </form>

    <p class="account-footer">Already have an account? <a href="../login.php">Log in</a></p>
</div>
</div>
</section>
</main>

<?php include '../partials/footer.php'; ?>
<script src="../js/main.js"></script>
</body>
</html>