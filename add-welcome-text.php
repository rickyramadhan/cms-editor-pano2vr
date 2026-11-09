<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/language-manager.php';
require_once __DIR__ . '/project-manager.php';

function welcomeRespond(int $status, bool $success, string $message, array $extra = []): never
{
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') welcomeRespond(405, false, 'Metode tidak diizinkan.');
    $payload = json_decode((string) file_get_contents('php://input'), true, 32, JSON_THROW_ON_ERROR);
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) welcomeRespond(403, false, 'Sesi CMS tidak valid. Muat ulang halaman.');
    $root = activeProjectRoot();
    $locationId = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', trim((string) ($payload['id_lokasi'] ?? ''))));
    $languageCode = strtolower(trim((string) ($payload['language'] ?? '')));
    $registry = languageRegistry($root);
    if ($locationId === '' || !isset($registry[$languageCode])) welcomeRespond(422, false, 'Lokasi atau bahasa tidak valid.');
    if ($languageCode === 'id') welcomeRespond(422, false, 'Welcome text bahasa Indonesia harus diisi melalui editor lokasi.');
    $assetsRoot = activeProjectAssetsRoot();
    $databaseFile = $assetsRoot . '/global-database.js';
    $database = languageReadJs($databaseFile, 'GLOBAL_DATABASE');
    if (!isset($database[$locationId])) welcomeRespond(404, false, 'Lokasi tidak ditemukan dalam database.');
    $existing = trim((string) ($database[$locationId]['bahasa'][$languageCode]['welcomeText'] ?? ''));
    if ($existing !== '') welcomeRespond(200, true, 'Welcome text sudah tersedia.', ['text' => $existing, 'unchanged' => true]);
    $source = trim((string) ($database[$locationId]['bahasa']['id']['welcomeText'] ?? ''));
    if ($source === '') welcomeRespond(422, false, 'Isi welcome text bahasa Indonesia terlebih dahulu.');
    if (mb_strlen($source) > 10000) welcomeRespond(422, false, 'Welcome text Indonesia terlalu panjang.');
    $translation = translatePanoText($source, (string) $registry[$languageCode]['translate']);
    if ($translation === '') welcomeRespond(502, false, 'Layanan terjemahan tidak menghasilkan teks. Coba lagi.');
    if (!isset($database[$locationId]['bahasa'][$languageCode]) || !is_array($database[$locationId]['bahasa'][$languageCode])) $database[$locationId]['bahasa'][$languageCode] = ['lokasi' => '', 'welcomeText' => ''];
    $database[$locationId]['bahasa'][$languageCode]['welcomeText'] = $translation;
    $backupDirectory = $assetsRoot . '/.database-backups';
    if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) welcomeRespond(500, false, 'Folder backup database gagal dibuat.');
    $backupFile = $backupDirectory . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '-before-welcome-text.js';
    if (!copy($databaseFile, $backupFile)) welcomeRespond(500, false, 'Backup database gagal dibuat; data tidak diubah.');
    languageWriteJs($databaseFile, 'GLOBAL_DATABASE', $database);
    welcomeRespond(200, true, 'Welcome text berhasil diterjemahkan dan disimpan.', ['text' => $translation]);
} catch (JsonException) {
    welcomeRespond(400, false, 'Format permintaan tidak valid.');
} catch (Throwable $error) {
    welcomeRespond(500, false, $error->getMessage());
}
