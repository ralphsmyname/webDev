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

// kani sanitation
$reviewerName = sanitize_string($_POST['reviewer_name'] ?? '');
$rating       = sanitize_rating($_POST['rating'] ?? 5);
$reviewText   = sanitize_string($_POST['review'] ?? '');

// kani validation
$errors = [];

if ($reviewerName === '') {
    $errors['reviewer_name'] = 'Please enter your name.';
} elseif (mb_strlen($reviewerName) > 100) {
    $errors['reviewer_name'] = 'Name is too long (max 100 characters).';
}

if ($reviewText === '') {
    $errors['review'] = 'Please write a review before submitting.';
} elseif (mb_strlen($reviewText) < 5) {
    $errors['review'] = 'Review is too short.';
} elseif (mb_strlen($reviewText) > 2000) {
    $errors['review'] = 'Review is too long (max 2000 characters).';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors, 'message' => 'Please fix the highlighted fields.'], 422);
}

// kani kay diri nako paagion paingon sa admin before ipagawa sa site
try {
    $stmt = $pdo->prepare(
        'INSERT INTO reviews (reviewer_name, rating, review_text, is_published) VALUES (:name, :rating, :text, 0)'
    );
    $stmt->execute([
        ':name'   => $reviewerName,
        ':rating' => $rating,
        ':text'   => $reviewText,
    ]);

    json_response(['success' => true, 'message' => 'Thanks for the review! It will appear once approved.']);
} catch (PDOException $e) {
    error_log('review.php insert failed: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Something went wrong on our end. Please try again.'], 500);
}
