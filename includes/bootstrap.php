<?php
declare(strict_types=1);

/**
 * Shared CMS runtime bootstrap.
 *
 * Keep this file dependency-free so page controllers and legacy endpoint
 * wrappers can load it before their feature-specific services.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Cookie sesi CMS: tidak terbaca JavaScript, tidak dikirim lintas situs, hanya HTTPS bila diakses lewat HTTPS.
    $cmsHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('GVCMSSESS');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $cmsHttps, 'httponly' => true, 'samesite' => 'Lax']);
    ini_set('session.use_strict_mode', '1');
    session_start();
}
require_once __DIR__ . '/auth.php';

function cmsEnsureCsrfToken(): string
{
    if (empty($_SESSION['cms_csrf'])) {
        $_SESSION['cms_csrf'] = bin2hex(random_bytes(24));
    }

    return (string) $_SESSION['cms_csrf'];
}

function cmsAssertCsrf(string $token, string $message = 'Sesi formulir tidak valid. Muat ulang halaman.'): void
{
    $expected = (string) ($_SESSION['cms_csrf'] ?? '');
    if ($expected === '' || !hash_equals($expected, $token)) {
        throw new RuntimeException($message);
    }
}

function cmsJsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function cmsJsonPayload(): array
{
    $payload = json_decode((string) file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($payload)) {
        throw new RuntimeException('Payload JSON tidak valid.');
    }

    return $payload;
}

function cmsCleanKey(string $value): string
{
    return strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', trim($value)));
}

function cmsEscape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cmsReadJsObject(string $file, string $variable): array
{
    $content = @file_get_contents($file);
    if ($content === false) {
        throw new RuntimeException('Asset project belum lengkap: ' . basename($file) . ' tidak ditemukan. Kembali ke pemilih project lalu buka project ini lagi untuk memasang asset bawaan.');
    }

    $pattern = '/window\.' . preg_quote($variable, '/') . '\s*=\s*(\{.*?\}|\[.*?\])\s*;/s';
    if (!preg_match($pattern, $content, $matches)) {
        throw new RuntimeException('Format ' . basename($file) . ' tidak dikenali.');
    }

    $data = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    return is_array($data) ? $data : [];
}

function cmsReplaceJsObject(string $file, string $variable, array $data): void
{
    $content = file_get_contents($file);
    if ($content === false) {
        throw new RuntimeException('Tidak dapat membaca ' . basename($file));
    }

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $replacement = 'window.' . $variable . ' = ' . $json . ';';
    $pattern = '/window\.' . preg_quote($variable, '/') . '\s*=\s*(?:\{.*?\}|\[.*?\])\s*;/s';
    $updated = preg_replace($pattern, $replacement, $content, 1, $count);

    if ($updated === null || $count !== 1) {
        throw new RuntimeException('Gagal memperbarui ' . basename($file));
    }
    if (file_put_contents($file, $updated, LOCK_EX) === false) {
        throw new RuntimeException('Gagal menyimpan ' . basename($file) . '. Periksa izin folder assets.');
    }
}
