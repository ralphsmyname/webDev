<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_customer_login(string $loginUrl): void
{
    if (empty($_SESSION['customer_id'])) {
        $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
        header('Location: ' . $loginUrl . '?redirect=' . $redirect);
        exit;
    }
}