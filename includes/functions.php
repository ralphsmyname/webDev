<?php

function sanitize_string($value): string
{
    $value = is_string($value) ? $value : '';
    $value = trim($value);
    $value = strip_tags($value);

    return $value;
}

function sanitize_email($value): ?string
{
    $value = trim(is_string($value) ? $value : '');
    $value = filter_var($value, FILTER_SANITIZE_EMAIL);

    return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
}


function sanitize_phone($value): ?string
{
    $value = sanitize_string($value);

    if ($value === '') {
        return '';
    }

    return preg_match('/^[0-9+()\-\s]{7,20}$/', $value) ? $value : null;
}


function sanitize_rating($value, int $min = 1, int $max = 5): int
{
    $value = (int) $value;

    return max($min, min($max, $value));
}


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


function format_price(float $amount): string
{
    return '₱' . number_format($amount, 2);
}


function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}