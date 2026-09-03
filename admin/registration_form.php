<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
$isEdit = $id > 0;

$record = ['full_name' => '', 'email' => '', 'phone' => '', 'status' => 'pending'];
if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM registrations WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        die('Registration not found.');
    }
    $record = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['_general'] = 'Session expired, please try again.';
    } else {
        $fullName = sanitize_string($_POST['full_name'] ?? '');
        $emailRaw = $_POST['email'] ?? '';
        $email    = sanitize_email($emailRaw);
        $phone    = sanitize_phone($_POST['phone'] ?? '');
        $status   = in_array($_POST['status'] ?? '', ['pending', 'confirmed'], true) ? $_POST['status'] : 'pending';

        $record = ['full_name' => $fullName, 'email' => (string) $emailRaw, 'phone' => $_POST['phone'] ?? '', 'status' => $status];

        if ($fullName === '') {
            $errors['full_name'] = 'Full name is required.';
        } elseif (mb_strlen($fullName) > 100) {
            $errors['full_name'] = 'Name is too long.';
        }

        if (trim($emailRaw) === '') {
            $errors['email'] = 'Email is required.';
        } elseif ($email === null) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($phone === null) {
            $errors['phone'] = 'Enter a valid phone number.';
        }

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    $stmt = $pdo->prepare(
                        'UPDATE registrations SET full_name = :name, email = :email, phone = :phone, status = :status WHERE id = :id'
                    );
                    $stmt->execute([
                        ':name'   => $fullName,
                        ':email'  => $email,
                        ':phone'  => $phone !== '' ? $phone : null,
                        ':status' => $status,
                        ':id'     => $id,
                    ]);
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO registrations (full_name, email, phone, status) VALUES (:name, :email, :phone, :status)'
                    );
                    $stmt->execute([
                        ':name'   => $fullName,
                        ':email'  => $email,
                        ':phone'  => $phone !== '' ? $phone : null,
                        ':status' => $status,
                    ]);
                }

                header('Location: registrations.php');
                exit;
            } catch (PDOException $e) {
                if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), '1062')) {
                    $errors['email'] = 'This email is already registered.';
                } else {
                    error_log('registration_form.php save failed: ' . $e->getMessage());
                    $errors['_general'] = 'Something went wrong saving this record.';
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
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Registration | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">

    <?php include '_header.php'; ?>

    <div class="admin-wrap">
        <h1><?= $isEdit ? 'Edit registration' : 'Add registration' ?></h1>

        <div class="admin-card" style="max-width:520px;">
            <?php if (!empty($errors['_general'])): ?>
                <p class="admin-alert admin-alert-error"><?= htmlspecialchars($errors['_general'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form class="admin-form" method="post" action="registration_form.php<?= $isEdit ? '?id=' . $id : '' ?>">
                <?= csrf_field() ?>
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" maxlength="100"
                       value="<?= htmlspecialchars($record['full_name'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($errors['full_name'])): ?><span class="field-error"><?= htmlspecialchars($errors['full_name'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="150"
                       value="<?= htmlspecialchars($record['email'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($errors['email'])): ?><span class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>

                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" maxlength="30"
                       value="<?= htmlspecialchars($record['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($errors['phone'])): ?><span class="field-error"><?= htmlspecialchars($errors['phone'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>

                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="pending" <?= $record['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= $record['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                </select>

                <div style="margin-top:20px;display:flex;gap:10px;">
                    <button class="admin-btn admin-btn-primary" type="submit"><?= $isEdit ? 'Save changes' : 'Create' ?></button>
                    <a class="admin-btn" href="registrations.php">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
