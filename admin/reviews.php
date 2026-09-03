<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$notice = '';
$noticeType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $notice = 'Session expired, please try again.';
        $noticeType = 'error';
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM reviews WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $notice = 'Review deleted.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_publish') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $notice = 'Session expired, please try again.';
        $noticeType = 'error';
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $publish = (int) ($_POST['is_published'] ?? 0);
        $stmt = $pdo->prepare('UPDATE reviews SET is_published = :p WHERE id = :id');
        $stmt->execute([':p' => $publish ? 1 : 0, ':id' => $id]);
        $notice = 'Review updated.';
    }
}

$rows = $pdo->query('SELECT * FROM reviews ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reviews | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">

    <?php include '_header.php'; ?>

    <div class="admin-wrap">
        <div class="admin-toolbar">
            <h1>Reviews</h1>
            <a class="admin-btn admin-btn-primary" href="review_form.php">+ Add review</a>
        </div>

        <?php if ($notice): ?>
            <p class="admin-alert admin-alert-<?= $noticeType === 'error' ? 'error' : 'success' ?>">
                <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <div class="admin-card">
            <span class="admin-pill-count"><?= count($rows) ?> total</span>

            <?php if (empty($rows)): ?>
                <p class="admin-empty">No reviews yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Reviewer</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['reviewer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating']) ?></td>
                                <td style="max-width:320px;"><?= nl2br(htmlspecialchars($r['review_text'], ENT_QUOTES, 'UTF-8')) ?></td>
                                <td>
                                    <span class="admin-badge admin-badge-<?= $r['is_published'] ? 'published' : 'unpublished' ?>">
                                        <?= $r['is_published'] ? 'Published' : 'Unpublished' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <a class="admin-btn" href="review_form.php?id=<?= (int) $r['id'] ?>">Edit</a>

                                        <form method="post" action="reviews.php" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle_publish">
                                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                            <input type="hidden" name="is_published" value="<?= $r['is_published'] ? 0 : 1 ?>">
                                            <button class="admin-btn" type="submit">
                                                <?= $r['is_published'] ? 'Unpublish' : 'Publish' ?>
                                            </button>
                                        </form>

                                        <form method="post" action="reviews.php" style="display:inline;" onsubmit="return confirm('Delete this review?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                            <button class="admin-btn admin-btn-danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
