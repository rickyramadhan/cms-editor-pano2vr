<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
require_once __DIR__ . '/includes/backup-store.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';
require_once __DIR__ . '/language-manager.php';        // languageRegistry() untuk blok terjemahan baru
require_once __DIR__ . '/includes/pano-memory.php';    // createTranslationBlock()
require_once __DIR__ . '/includes/lokasi-url.php';

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
    $project = activeProject();
    $projectRoot = (string) $project['path'];
    $file = lokasiDbFile(projectRuntimeDirectory($project));
    $data = lokasiDbNormalize((array) ($payload['data'] ?? []));
    if ($data['locations'] === []) respond(422, ['success' => false, 'message' => 'Daftar URL Lokasi kosong; tidak disimpan.']);
    lokasiDbWrite($file, $data);
    $assetsRoot = projectRuntimeDirectory($project);
    $loaderFixed = false;
    try { $loaderFixed = lokasiLoaderRepair($assetsRoot); } catch (Throwable) { /* pemuat bisa dipasang lewat Load Titik URL */ }   // Simpan pertama di project baru: main-loader.js belum memuat lokasi-database.js
    $added = lokasiSyncTranslations($projectRoot, $data);
    $count = array_sum($added);
    respond(200, [
        'success' => true,
        'message' => 'URL Lokasi dan hotspot disimpan.' . ($loaderFixed ? ' Pemuat tur (main-loader.js) dipasang.' : '') . ($count > 0 ? ' ' . $count . ' judul baru masuk ke Audit › Terjemahan (' . count($added) . ' tur).' : ''),
        'translationsAdded' => $added,
        'data' => $data,
    ]);
} catch (Throwable $error) {
    respond(400, ['success' => false, 'message' => $error->getMessage()]);
}
