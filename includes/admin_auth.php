<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
    exit;
}

$idleLimit = 1800;
if (!empty($_SESSION['admin_last_active']) && (time() - $_SESSION['admin_last_active']) > $idleLimit) {
    session_unset();
    session_destroy();
    header('Location: ../login.php?timeout=1');
    exit;
}
$_SESSION['admin_last_active'] = time();