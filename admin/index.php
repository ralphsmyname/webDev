<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$regCount = (int) $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
$regPending = (int) $pdo->query("SELECT COUNT(*) FROM registrations WHERE status = 'pending'")->fetchColumn();
$msgCount = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
$msgUnread = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
$reviewCount = (int) $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
$reviewPending = (int) $pdo->query('SELECT COUNT(*) FROM reviews WHERE is_published = 0')->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | Hinlo Airsoft Zone</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">

    <?php include '_header.php'; ?>

    <div class="admin-wrap">
        <h1>Dashboard</h1>

        <div class="admin-grid">
            <a class="admin-stat" href="registrations.php">
                <span class="num"><?= $regCount ?></span>
                Registrations
                <?php if ($regPending): ?><br><small><?= $regPending ?> pending</small><?php endif; ?>
            </a>

            <a class="admin-stat" href="messages.php">
                <span class="num"><?= $msgCount ?></span>
                Contact messages
                <?php if ($msgUnread): ?><br><small><?= $msgUnread ?> unread</small><?php endif; ?>
            </a>

            <a class="admin-stat" href="reviews.php">
                <span class="num"><?= $reviewCount ?></span>
                Reviews
                <?php if ($reviewPending): ?><br><small><?= $reviewPending ?> awaiting approval</small><?php endif; ?>
            </a>
        </div>

        <div class="admin-card">
            <h2>Quick links</h2>
            <p><a href="registrations.php">Manage registrations</a> — view, edit, confirm, or delete.</p>
            <p><a href="messages.php">Manage contact messages</a> — read, mark read/unread, delete.</p>
            <p><a href="reviews.php">Manage reviews</a> — approve, edit, add, or delete.</p>
        </div>
    </div>

</body>
</html>
