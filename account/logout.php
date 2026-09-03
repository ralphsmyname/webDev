<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['customer_id'], $_SESSION['customer_name']);
header('Location: ../merch.php');
exit;