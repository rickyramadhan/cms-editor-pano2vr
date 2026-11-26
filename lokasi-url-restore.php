<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
require_once __DIR__ . '/includes/backup-store.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';
require_once __DIR__ . '/language-manager.php';
require_once __DIR__ . '/includes/pano-memory.php';
require_once __DIR__ . '/includes/lokasi-url.php';

/**
 * "Load Titik URL" (tab URL Lokasi). POST JSON { csrf, action: 'scan' | 'apply', id_lokasi }.
 *   scan  : periksa data titik/URL, pemuat tur, dan judul di Terjemahan pano.xml lokasi itu (tidak mengubah apa pun).
 *   apply : pulihkan semua yang hilang (data dari memori CMS/cadangan, main-loader.js, judul + terjemahan di pano.xml).
 */
function respond(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, ['success' => false, 'message' => 'Metode tidak diizinkan.']);
    $payload = json_decode((string) file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($payload) || !hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) respond(403, ['success' => false, 'message' => 'Sesi formulir tidak valid.']);
    $id = lokasiCleanId($payload['id_lokasi'] ?? '');
    if ($id === '') respond(422, ['success' => false, 'message' => 'Lokasi tidak valid.']);
    $project = activeProject();
    $projectRoot = (string) $project['path'];
    $assetsRoot = projectRuntimeDirectory($project);
    $current = lokasiDbRead(lokasiDbFile($assetsRoot));
    $action = (string) ($payload['action'] ?? 'scan');
    if ($action === 'scan') respond(200, ['success' => true, 'scan' => lokasiRestoreScan($projectRoot, $assetsRoot, $id, $current)]);
    if ($action !== 'apply') respond(422, ['success' => false, 'message' => 'Aksi tidak dikenal.']);
    $result = lokasiRestoreApply($projectRoot, $assetsRoot, $id, $current);
    $d = $result['done'];
    $parts = [];
    if ($d['locations'] || $d['links']) $parts[] = ($d['locations'] ? $d['locations'] . ' lokasi' : '') . ($d['locations'] && $d['links'] ? ' + ' : '') . ($d['links'] ? $d['links'] . ' tautan hotspot' : '') . ' dikembalikan';
    if ($d['loader']) $parts[] = 'pemuat tur dipasang kembali';
    if ($d['keys']) $parts[] = $d['keys'] . ' judul masuk ke Terjemahan';
    if ($d['values']) $parts[] = $d['values'] . ' terjemahan dikembalikan';
    respond(200, ['success' => true, 'message' => $parts ? 'Pemulihan selesai: ' . implode(', ', $parts) . '.' : 'Tidak ada yang perlu dipulihkan.'] + $result);
} catch (Throwable $error) {
    respond(400, ['success' => false, 'message' => $error->getMessage()]);
}
