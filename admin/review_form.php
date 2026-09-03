<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
$isEdit = $id > 0;

$record = ['reviewer_name' => '', 'rating' => 5, 'review_text' => '', 'is_published' => 0];
if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM reviews WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        die('Review not found.');
    }
    $record = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['_general'] = 'Session expired, please try again.';
    } else {
        $reviewerName = sanitize_string($_POST['reviewer_name'] ?? '');
        $rating       = sanitize_rating($_POST['rating'] ?? 5);
        $reviewText   = sanitize_string($_POST['review_text'] ?? '');
        $isPublished  = !empty($_POST['is_published']) ? 1 : 0;

        $record = ['reviewer_name' => $reviewerName, 'rating' => $rating, 'review_text' => $reviewText, 'is_published' => $isPublished];

        if ($reviewerName === '') {
            $errors['reviewer_name'] = 'Reviewer name is required.';
        } elseif (mb_strlen($reviewerName) > 100) {
            $errors['reviewer_name'] = 'Name is too long.';
        }

        if ($reviewText === '') {
            $errors['review_text'] = 'Review text is required.';
        } elseif (mb_strlen($reviewText) > 2000) {
            $errors['review_text'] = 'Review is too long (max 2000 characters).';
        }

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    $stmt = $pdo->prepare(
                        'UPDATE reviews SET reviewer_name = :name, rating = :rating, review_text = :text, is_published = :pub WHERE id = :id'
                    );
                    $stmt->execute([
                        ':name'   => $reviewerName,
                        ':rating' => $rating,
                        ':text'   => $reviewText,
                        ':pub'    => $isPublished,
                        ':id'     => $id,
                    ]);
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO reviews (reviewer_name, rating, review_text, is_published) VALUES (:name, :rating, :text, :pub)'
                    );
                    $stmt->execute([
                        ':name'   => $reviewerName,
                        ':rating' => $rating,
                        ':text'   => $reviewText,
                        ':pub'    => $isPublished,
                    ]);
                }

                header('Location: reviews.php');
                exit;
            } catch (PDOException $e) {
                error_log('review_form.php save failed: ' . $e->getMessage());
                $errors['_general'] = 'Something went wrong saving this record.';
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
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Review | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">

    <?php include '_header.php'; ?>

    <div class="admin-wrap">
        <h1><?= $isEdit ? 'Edit review' : 'Add review' ?></h1>

        <div class="admin-card" style="max-width:560px;">
            <?php if (!empty($errors['_general'])): ?>
                <p class="admin-alert admin-alert-error"><?= htmlspecialchars($errors['_general'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form class="admin-form" method="post" action="review_form.php<?= $isEdit ? '?id=' . $id : '' ?>">
                <?= csrf_field() ?>
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

                <label for="reviewer_name">Reviewer name</label>
                <input type="text" id="reviewer_name" name="reviewer_name" maxlength="100"
                       value="<?= htmlspecialchars($record['reviewer_name'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($errors['reviewer_name'])): ?><span class="field-error"><?= htmlspecialchars($errors['reviewer_name'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>

                <label for="rating">Rating</label>
                <select id="rating" name="rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>" <?= (int) $record['rating'] === $i ? 'selected' : '' ?>>
                            <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <label for="review_text">Review text</label>
                <textarea id="review_text" name="review_text" maxlength="2000"><?= htmlspecialchars($record['review_text'], ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php if (!empty($errors['review_text'])): ?><span class="field-error"><?= htmlspecialchars($errors['review_text'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>

                <label style="display:flex;align-items:center;gap:8px;margin-top:14px;">
                    <input type="checkbox" name="is_published" value="1" style="width:auto;" <?= !empty($record['is_published']) ? 'checked' : '' ?>>
                    Published (visible on the site)
                </label>

                <div style="margin-top:20px;display:flex;gap:10px;">
                    <button class="admin-btn admin-btn-primary" type="submit"><?= $isEdit ? 'Save changes' : 'Create' ?></button>
                    <a class="admin-btn" href="reviews.php">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
