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
        $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $notice = 'Message deleted.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_read') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $notice = 'Session expired, please try again.';
        $noticeType = 'error';
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $isRead = (int) ($_POST['is_read'] ?? 0);
        $stmt = $pdo->prepare('UPDATE contact_messages SET is_read = :r WHERE id = :id');
        $stmt->execute([':r' => $isRead ? 1 : 0, ':id' => $id]);
        $notice = 'Message updated.';
    }
}

$rows = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Messages | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">

    <?php include '_header.php'; ?>

    <div class="admin-wrap">
        <h1>Contact messages</h1>

        <?php if ($notice): ?>
            <p class="admin-alert admin-alert-<?= $noticeType === 'error' ? 'error' : 'success' ?>">
                <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <div class="admin-card">
            <span class="admin-pill-count"><?= count($rows) ?> total</span>

            <?php if (empty($rows)): ?>
                <p class="admin-empty">No messages yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Received</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($m['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="max-width:320px;"><?= nl2br(htmlspecialchars($m['message'], ENT_QUOTES, 'UTF-8')) ?></td>
                                <td>
                                    <span class="admin-badge admin-badge-<?= $m['is_read'] ? 'read' : 'unread' ?>">
                                        <?= $m['is_read'] ? 'Read' : 'Unread' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($m['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <form method="post" action="messages.php" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle_read">
                                            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                            <input type="hidden" name="is_read" value="<?= $m['is_read'] ? 0 : 1 ?>">
                                            <button class="admin-btn" type="submit">
                                                Mark <?= $m['is_read'] ? 'unread' : 'read' ?>
                                            </button>
                                        </form>

                                        <form method="post" action="messages.php" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
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
