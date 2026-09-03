<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Your session expired. Please refresh the page and try again.'], 403);
}

// ---- Sanitize ----
$fullName = sanitize_string($_POST['full_name'] ?? '');
$emailRaw = $_POST['email'] ?? '';
$email    = sanitize_email($emailRaw);
$phone    = sanitize_phone($_POST['phone'] ?? '');

// ---- Validate ----
$errors = [];

if ($fullName === '') {
    $errors['full_name'] = 'Please enter your full name.';
} elseif (mb_strlen($fullName) < 2) {
    $errors['full_name'] = 'Name looks too short.';
} elseif (mb_strlen($fullName) > 100) {
    $errors['full_name'] = 'Name is too long (max 100 characters).';
}

if (trim($emailRaw) === '') {
    $errors['email'] = 'Please enter your email address.';
} elseif ($email === null) {
    $errors['email'] = 'Please enter a valid email address.';
}

if ($phone === null) {
    $errors['phone'] = 'Please enter a valid contact number.';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors, 'message' => 'Please fix the highlighted fields.'], 422);
}

// ---- Insert (prepared statement — never string-concatenated SQL) ----
try {
    $stmt = $pdo->prepare(
        'INSERT INTO registrations (full_name, email, phone) VALUES (:full_name, :email, :phone)'
    );
    $stmt->execute([
        ':full_name' => $fullName,
        ':email'     => $email,
        ':phone'     => $phone !== '' ? $phone : null,
    ]);

    json_response(['success' => true, 'message' => 'Thanks for registering! We\'ll be in touch soon.']);
} catch (PDOException $e) {
    if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), '1062')) {
        json_response(['success' => false, 'errors' => ['email' => 'This email is already registered.'], 'message' => 'This email is already registered.'], 409);
    }

    error_log('register.php insert failed: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Something went wrong on our end. Please try again.'], 500);
}
