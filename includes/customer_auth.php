<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_customer_login(
    string $loginUrl,
    ?string $redirectTo = null
): void {

    if (empty($_SESSION['user_id'])) {

        $redirectTo = $redirectTo ?? (
            $_SERVER['REQUEST_URI'] ?? ''
        );

        $redirect = urlencode($redirectTo);

        header(
            'Location: ' .
            $loginUrl .
            '?redirect=' .
            $redirect
        );

        exit;
    }

    if (
        isset($_SESSION['user_role']) &&
        $_SESSION['user_role'] !== 'customer'
    ) {

        http_response_code(403);

        exit('Access denied.');
    }
}