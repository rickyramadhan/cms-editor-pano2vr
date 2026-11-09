<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';

function video360Response(int $status, bool $success, string $message, array $extra = []): never
{
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function promoteVideo360Package(string $videoRoot): bool
{
    if (is_file($videoRoot . '/master.m3u8')) return true;
    $masters = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($videoRoot, FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->isFile() && strtolower($file->getFilename()) === 'master.m3u8') $masters[] = $file->getPathname();
    }
    if (count($masters) !== 1) return false;
    $packageRoot = dirname($masters[0]);
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($packageRoot, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $item) {
        $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($packageRoot) + 1));
        $target = $videoRoot . '/' . $relative;
        if ($item->isDir()) {
            if (!is_dir($target) && !mkdir($target, 0775, true) && !is_dir($target)) throw new RuntimeException('Struktur paket HLS gagal dinormalisasi.');
        } else {
            $parent = dirname($target);
            if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) throw new RuntimeException('Folder kualitas gagal dibuat.');
            if ($item->getPathname() !== $target && !copy($item->getPathname(), $target)) throw new RuntimeException('Paket HLS gagal dipindahkan ke folder video.');
        }
    }
    return is_file($videoRoot . '/master.m3u8');
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') video360Response(405, false, 'Metode tidak diizinkan.');
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($_POST['csrf'] ?? ''))) video360Response(403, false, 'Sesi CMS tidak valid.');
    $id = strtolower(trim((string) ($_POST['video_id'] ?? '')));
    if (!preg_match('/^[a-z0-9](?:[a-z0-9_-]{0,62}[a-z0-9])?$/', $id)) video360Response(422, false, 'ID video hanya boleh berisi huruf kecil, angka, dash, dan underscore.');
    $action = (string) ($_POST['action'] ?? 'upload');
    $videosRoot = activeProjectAssetBaseRoot() . '/360/videos';
    $videoRoot = $videosRoot . '/' . $id;
    if ($action === 'delete') {
        if (!is_dir($videoRoot)) video360Response(404, false, 'Video 360 tidak ditemukan.');
        removeDirectoryTree($videoRoot);
        video360Response(200, true, 'Video 360 ' . $id . ' berhasil dihapus.');
    }
    if ($action === 'rename') {
        $newId = strtolower(trim((string) ($_POST['new_id'] ?? '')));
        if (!preg_match('/^[a-z0-9](?:[a-z0-9_-]{0,62}[a-z0-9])?$/', $newId)) video360Response(422, false, 'ID video baru tidak valid.');
        if (!is_dir($videoRoot)) video360Response(404, false, 'Video 360 tidak ditemukan.');
        $destination = $videosRoot . '/' . $newId;
        if (file_exists($destination)) video360Response(409, false, 'ID video baru sudah digunakan.');
        if (!rename($videoRoot, $destination)) throw new RuntimeException('Folder video gagal diubah namanya.');
        video360Response(200, true, 'ID video berhasil diubah menjadi ' . $newId . '.', ['id' => $newId]);
    }
    $files = $_FILES['files'] ?? null;
    if (!is_array($files) || !isset($files['name']) || !is_array($files['name'])) video360Response(422, false, 'Pilih folder atau file HLS yang akan diunggah.');
    if (!is_dir($videoRoot) && !mkdir($videoRoot, 0775, true) && !is_dir($videoRoot)) throw new RuntimeException('Folder video 360 gagal dibuat.');
    $allowed = ['m3u8', 'ts', 'm4s', 'mp4', 'webm', 'mov', 'mkv', 'aac', 'key'];
    $saved = 0;
    foreach ($files['name'] as $index => $name) {
        $error = (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) continue;
        if ($error !== UPLOAD_ERR_OK) throw new RuntimeException('Upload gagal dengan kode ' . $error . '.');
        $relative = (string) (($files['full_path'][$index] ?? '') ?: $name);
        $relative = str_replace('\\', '/', $relative);
        $parts = array_values(array_filter(explode('/', $relative), static fn(string $part): bool => $part !== ''));
        if (count($parts) > 1) array_shift($parts);
        $relative = implode('/', $parts);
        // Folder encoder may contain spaces, parentheses, or Unicode labels.
        // Validate each segment rather than limiting names to ASCII. Traversal,
        // control bytes, and Windows-reserved filename characters remain blocked.
        if ($relative === '' || str_contains($relative, "\0")) throw new RuntimeException('Nama file upload tidak aman.');
        foreach (explode('/', $relative) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..' || preg_match('/[\x00-\x1F<>:"|?*]/u', $segment)) throw new RuntimeException('Nama file upload tidak aman: ' . $segment);
        }
        $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed, true)) throw new RuntimeException('Format .' . $extension . ' tidak didukung untuk paket HLS.');
        $destination = $videoRoot . '/' . $relative;
        $directory = dirname($destination);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) throw new RuntimeException('Subfolder video gagal dibuat.');
        if (!move_uploaded_file((string) $files['tmp_name'][$index], $destination)) throw new RuntimeException('File ' . basename($relative) . ' gagal disimpan.');
        $saved++;
    }
    if ($saved === 0) video360Response(422, false, 'Tidak ada file yang diunggah.');
    // Folder picker can include one or more wrapper directories. If there is a
    // single master manifest, promote that package to the ID root expected by
    // assets/360/player/player.js.
    $ready = promoteVideo360Package($videoRoot);
    video360Response(200, true, 'Folder ' . $id . ' dibuat dan ' . $saved . ' file berhasil diunggah.' . ($ready ? ' Video siap digunakan.' : ' Jalankan Encoder GUI untuk membuat master.m3u8.'), ['id' => $id, 'files' => $saved, 'ready' => $ready]);
} catch (Throwable $error) {
    video360Response(500, false, $error->getMessage());
}
