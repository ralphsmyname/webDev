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

//kani sanitation
$name     = sanitize_string($_POST['name'] ?? '');
$emailRaw = $_POST['email'] ?? '';
$email    = sanitize_email($emailRaw);
$message  = sanitize_string($_POST['message'] ?? '');

// kani validation
$errors = [];

if ($name === '') {
    $errors['name'] = 'Please enter your name.';
} elseif (mb_strlen($name) > 100) {
    $errors['name'] = 'Name is too long (max 100 characters).';
}

if (trim($emailRaw) === '') {
    $errors['email'] = 'Please enter your email address.';
} elseif ($email === null) {
    $errors['email'] = 'Please enter a valid email address.';
}

if ($message === '') {
    $errors['message'] = 'Please enter a message.';
} elseif (mb_strlen($message) < 5) {
    $errors['message'] = 'Message is too short.';
} elseif (mb_strlen($message) > 5000) {
    $errors['message'] = 'Message is too long (max 5000 characters).';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors, 'message' => 'Please fix the highlighted fields.'], 422);
}

// ---- Insert ----
try {
    $stmt = $pdo->prepare(
        'INSERT INTO contact_messages (name, email, message) VALUES (:name, :email, :message)'
    );
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':message' => $message,
    ]);

    json_response(['success' => true, 'message' => 'Thanks! Your message has been sent — we\'ll reply soon.']);
} catch (PDOException $e) {
    error_log('contact.php insert failed: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Something went wrong on our end. Please try again.'], 500);
}
