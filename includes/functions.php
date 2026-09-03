<?php
/**
 * Shared sanitization / validation helpers used by every form handler.
 *
 * Philosophy:
 *  - Sanitize on the way in (trim + strip tags) so nothing malformed
 *    or script-bearing ever reaches the database.
 *  - Always use prepared statements (never string-build SQL) so
 *    sanitization is a defense-in-depth measure, not the only defense.
 *  - Escape on the way out with htmlspecialchars() wherever data is
 *    echoed back into HTML (see partials/text.php for the existing
 *    pattern already used on this site).
 */

/**
 * Trim whitespace and strip any HTML/script tags from a raw string input.
 */
function sanitize_string($value): string
{
    $value = is_string($value) ? $value : '';
    $value = trim($value);
    $value = strip_tags($value);

    return $value;
}

/**
 * Sanitize + validate an email address. Returns null if invalid.
 */
function sanitize_email($value): ?string
{
    $value = trim(is_string($value) ? $value : '');
    $value = filter_var($value, FILTER_SANITIZE_EMAIL);

    return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
}

/**
 * Basic phone number validation. Digits, spaces, +, -, () only, 7-20 chars.
 * Returns the cleaned value, or null if the (optional) field was invalid.
 */
function sanitize_phone($value): ?string
{
    $value = sanitize_string($value);

    if ($value === '') {
        return '';
    }

    return preg_match('/^[0-9+()\-\s]{7,20}$/', $value) ? $value : null;
}

/**
 * Clamp an integer rating between $min and $max.
 */
function sanitize_rating($value, int $min = 1, int $max = 5): int
{
    $value = (int) $value;

    return max($min, min($max, $value));
}

/**
 * Stash old input + validation errors into the session so the page
 * that redirected here (a plain, non-AJAX fallback) can re-populate
 * the form and show messages after a redirect.
 */
function flash_set(array $errors, array $oldInput): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['flash_errors'] = $errors;
    $_SESSION['flash_old']    = $oldInput;
}

function flash_errors(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $errors = $_SESSION['flash_errors'] ?? [];
    unset($_SESSION['flash_errors']);

    return $errors;
}

function flash_old(string $key, string $default = ''): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $old = $_SESSION['flash_old'][$key] ?? $default;

    return htmlspecialchars($old, ENT_QUOTES, 'UTF-8');
}

function clear_flash_old(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    unset($_SESSION['flash_old']);
}

/**
 * Sends a JSON response and stops execution. Used by the AJAX action
 * scripts (actions/*.php) that main.js posts to.
 */
function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}
