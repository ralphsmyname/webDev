<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$notice = '';
$noticeType = 'success';

// ---- Handle Delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $notice = 'Session expired, please try again.';
        $noticeType = 'error';
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM registrations WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $notice = 'Registration deleted.';
    }
}

// ---- Handle status Update (pending <-> confirmed) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_status') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $notice = 'Session expired, please try again.';
        $noticeType = 'error';
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['pending', 'confirmed'], true)) {
            $stmt = $pdo->prepare('UPDATE registrations SET status = :s WHERE id = :id');
            $stmt->execute([':s' => $status, ':id' => $id]);
            $notice = 'Status updated.';
        }
    }
}

$rows = $pdo->query('SELECT * FROM registrations ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrations | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">

    <?php include '_header.php'; ?>

    <div class="admin-wrap">
        <div class="admin-toolbar">
            <h1>Registrations</h1>
            <a class="admin-btn admin-btn-primary" href="registration_form.php">+ Add registration</a>
        </div>

        <?php if ($notice): ?>
            <p class="admin-alert admin-alert-<?= $noticeType === 'error' ? 'error' : 'success' ?>">
                <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <div class="admin-card">
            <span class="admin-pill-count"><?= count($rows) ?> total</span>

            <?php if (empty($rows)): ?>
                <p class="admin-empty">No registrations yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($r['phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="admin-badge admin-badge-<?= $r['status'] ?>">
                                        <?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <a class="admin-btn" href="registration_form.php?id=<?= (int) $r['id'] ?>">Edit</a>

                                        <form method="post" action="registrations.php" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="set_status">
                                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                            <input type="hidden" name="status" value="<?= $r['status'] === 'pending' ? 'confirmed' : 'pending' ?>">
                                            <button class="admin-btn" type="submit">
                                                Mark <?= $r['status'] === 'pending' ? 'confirmed' : 'pending' ?>
                                            </button>
                                        </form>

                                        <form method="post" action="registrations.php" style="display:inline;" onsubmit="return confirm('Delete this registration?');">
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
