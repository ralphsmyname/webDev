<?php
/**
 * Session bootstrap + CSRF helpers.
 * Include this on every page that renders a form, and on every
 * action script that processes one.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Returns the current CSRF token, generating one if needed.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Renders a ready-to-use hidden input for forms.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verifies a submitted token against the session token
 * using a timing-safe comparison.
 */
function verify_csrf(?string $token): bool
{
    return !empty($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
