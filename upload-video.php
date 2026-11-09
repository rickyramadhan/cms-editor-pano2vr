<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';

// An empty POST commonly means the combined upload exceeded post_max_size.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    http_response_code(413);
    echo json_encode(['success' => false, 'message' => 'Ukuran video melebihi batas upload server. Periksa konfigurasi post_max_size dan upload_max_filesize.'], JSON_UNESCAPED_UNICODE);
    exit;
}

function videoResponse(int $status, bool $success, string $message, array $extra = []): never
{
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function findTourDirectory(string $root, string $targetId): ?string
{
    $queue = [];
    foreach (new DirectoryIterator($root) as $entry) {
        if ($entry->isDot() || !$entry->isDir() || in_array($entry->getFilename(), ['assets', 'cms', '.git'], true)) continue;
        $queue[] = $entry->getPathname();
        foreach (new DirectoryIterator($entry->getPathname()) as $child) {
            if (!$child->isDot() && $child->isDir()) $queue[] = $child->getPathname();
        }
    }
    foreach ($queue as $directory) {
        $skinFile = $directory . '/skin.js';
        if (!is_file($directory . '/pano.xml') || !is_file($skinFile)) continue;
        $id = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', basename($directory)));
        if ($id === $targetId) return $directory;
    }
    return null;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') videoResponse(405, false, 'Metode tidak diizinkan.');
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($_POST['csrf'] ?? ''))) videoResponse(403, false, 'Sesi CMS tidak valid. Muat ulang halaman.');
    $locationId = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', trim((string) ($_POST['id_lokasi'] ?? ''))));
    if ($locationId === '') videoResponse(422, false, 'ID lokasi tidak valid.');
    $tourDirectory = findTourDirectory(activeProjectRoot(), $locationId);
    if ($tourDirectory === null) videoResponse(404, false, 'Folder tur dengan id_lokasi tersebut tidak ditemukan.');

    $uploads = [];
    foreach (['mobile', 'desktop'] as $type) {
        if (!isset($_FILES[$type]) || $_FILES[$type]['error'] === UPLOAD_ERR_NO_FILE) continue;
        if ($_FILES[$type]['error'] !== UPLOAD_ERR_OK) videoResponse(422, false, 'Upload ' . $type . ' gagal dengan kode ' . $_FILES[$type]['error'] . '.');
        if (strtolower(pathinfo((string) $_FILES[$type]['name'], PATHINFO_EXTENSION)) !== 'webm') videoResponse(422, false, 'Video ' . $type . ' harus berformat WebM.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $_FILES[$type]['tmp_name']);
        if (!in_array($mime, ['video/webm', 'application/octet-stream'], true)) videoResponse(422, false, 'Isi file ' . $type . ' bukan video WebM yang valid.');
        $uploads[$type] = (string) $_FILES[$type]['tmp_name'];
    }
    if ($uploads === []) videoResponse(422, false, 'Pilih minimal satu video WebM.');

    $videoDirectory = $tourDirectory . '/video';
    if (!is_dir($videoDirectory) && !mkdir($videoDirectory, 0775, true) && !is_dir($videoDirectory)) videoResponse(500, false, 'Folder video gagal dibuat.');
    $saved = [];
    foreach ($uploads as $type => $temporaryFile) {
        $destination = $videoDirectory . '/' . $type . '.webm';
        if (!move_uploaded_file($temporaryFile, $destination)) videoResponse(500, false, 'Video ' . $type . ' gagal disimpan.');
        $saved[] = $type . '.webm';
    }
    videoResponse(200, true, implode(' dan ', $saved) . ' berhasil diunggah dan menimpa file lama.', ['files' => $saved]);
} catch (Throwable $error) {
    videoResponse(500, false, $error->getMessage());
}
