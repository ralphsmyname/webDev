<?php $adminPage = basename($_SERVER['PHP_SELF']); ?>
<header class="admin-header">
    <div class="brand">Hinlo Admin</div>
    <nav class="admin-nav">
        <a href="index.php" style="<?= $adminPage === 'index.php' ? 'color:var(--green)' : '' ?>">Dashboard</a>
        <a href="registrations.php" style="<?= $adminPage === 'registrations.php' ? 'color:var(--green)' : '' ?>">Registrations</a>
        <a href="orders.php" style="<?= $adminPage === 'orders.php' ? 'color:var(--green)' : '' ?>">Orders</a>
        <a href="messages.php" style="<?= $adminPage === 'messages.php' ? 'color:var(--green)' : '' ?>">Messages</a>
        <a href="reviews.php" style="<?= in_array($adminPage, ['reviews.php', 'review_form.php']) ? 'color:var(--green)' : '' ?>">Reviews</a>
        <a href="../index.php" target="_blank">View site ↗</a>
        <a href="logout.php">Log out (<?= htmlspecialchars($_SESSION['admin_username'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</a>
    </nav>
</header>