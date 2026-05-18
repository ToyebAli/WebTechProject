<?php

function start_session_if_needed(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function app_entry_path(): string
{
    if (defined('APP_ENTRY_PATH')) {
        return APP_ENTRY_PATH;
    }

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');

    if (preg_match('#^(.*)/(View|Controller|Model)/.+$#i', $script, $matches)) {
        return $matches[1] . '/Public/index.php';
    }

    return $script;
}

function url(string $path = '/'): string
{
    if (preg_match('#^(https?:)?//#', $path)) {
        return $path;
    }

    $parts = explode('?', $path, 2);
    $route = '/' . ltrim($parts[0], '/');
    $target = rtrim(app_entry_path(), '/') . $route;

    if (isset($parts[1])) {
        $target .= '?' . $parts[1];
    }

    return $target;
}

function asset_url(string $path): string
{
    $base = str_replace('\\', '/', dirname(app_entry_path()));
    $base = ($base === '/' || $base === '.') ? '' : rtrim($base, '/');

    return $base . '/' . ltrim($path, '/');
}

function product_image_url(?string $path): string
{
    $path = str_replace('\\', '/', trim((string) $path));

    if ($path === '') {
        return '';
    }

    if (str_starts_with(strtolower($path), 'public/')) {
        $path = substr($path, strlen('public/'));
    }

    return asset_url('/' . ltrim($path, '/'));
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash(string $key, ?string $message = null): ?string
{
    start_session_if_needed();

    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);

    return $value;
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function request_expects_json(): bool
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

    return str_contains($uri, '/api/')
        || str_contains($accept, 'application/json')
        || strtolower($xhr) === 'xmlhttprequest';
}

function session_guard(): void
{
    start_session_if_needed();

    if (!empty($_SESSION['user_id'])) {
        return;
    }

    if (request_expects_json()) {
        json_response(['ok' => false, 'message' => 'Please sign in first.'], 401);
    }

    redirect('/login');
}

function require_admin(): void
{
    session_guard();

    if (($_SESSION['role'] ?? '') === 'admin') {
        return;
    }

    if (request_expects_json()) {
        json_response(['ok' => false, 'message' => 'Admin access required.'], 403);
    }

    http_response_code(403);
    echo 'Access denied.';
    exit;
}

function csrf_token(): string
{
    start_session_if_needed();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_verify(): void
{
    start_session_if_needed();

    $posted = $_POST['csrf_token'] ?? '';
    $saved = $_SESSION['csrf_token'] ?? '';

    if ($posted === '' || $saved === '' || !hash_equals($saved, $posted)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}
