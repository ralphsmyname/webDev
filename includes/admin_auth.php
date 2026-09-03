<?php
/**
 * Guards every page under /admin. Include this at the very top of
 * any admin page (before any HTML output).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Simple session idle timeout (30 minutes).
$idleLimit = 1800;
if (!empty($_SESSION['admin_last_active']) && (time() - $_SESSION['admin_last_active']) > $idleLimit) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION['admin_last_active'] = time();
