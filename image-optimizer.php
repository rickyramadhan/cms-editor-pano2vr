<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';

function respond(int $status, bool $success, string $message, array $extra = []): never
{
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function cleanId(string $value): string
{
    return strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', trim($value)));
}

function findTourDirectory(string $root, string $id): ?string
{
    foreach (new DirectoryIterator($root) as $entry) {
        if ($entry->isDot() || !$entry->isDir() || in_array($entry->getFilename(), ['assets', 'cms', '.git'], true)) continue;
        $candidates = [$entry->getPathname()];
        foreach (new DirectoryIterator($entry->getPathname()) as $child) if (!$child->isDot() && $child->isDir()) $candidates[] = $child->getPathname();
        foreach ($candidates as $candidate) if (cleanId(basename($candidate)) === $id && is_file($candidate . '/pano.xml')) return $candidate;
    }
    return null;
}

function scanImages(string $directory): array
{
    $images = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($directory) + 1));
        if (preg_match('~(^|/)(tiles|3rdparty|\.media-backups|\.skin-backups)(/|$)~i', $relative)) continue;
        $extension = strtolower($file->getExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) continue;
        $size = $file->getSize();
        $info = @getimagesize($file->getPathname());
        if ($info === false) continue;
        $images[] = ['path' => $relative, 'bytes' => $size, 'width' => (int) $info[0], 'height' => (int) $info[1], 'recommended' => $size >= 250 * 1024 || (int) $info[0] > 1920 || (int) $info[1] > 1920];
    }
    usort($images, static fn(array $a, array $b): int => $b['bytes'] <=> $a['bytes']);
    return $images;
}

function safeImagePath(string $directory, string $relative): string
{
    $relative = str_replace('\\', '/', trim($relative, '/ '));
    if ($relative === '' || str_contains($relative, '../') || !preg_match('/\.(?:jpe?g|png)$/i', $relative) || preg_match('~(^|/)(tiles|3rdparty|\.media-backups|\.skin-backups)(/|$)~i', $relative)) throw new RuntimeException('Path gambar tidak valid.');
    $path = $directory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $realDirectory = realpath(dirname($path));
    if ($realDirectory === false || !str_starts_with(strtolower($realDirectory), strtolower((string) realpath($directory))) || !is_file($path)) throw new RuntimeException('Gambar tidak ditemukan: ' . $relative);
    return $path;
}

function updateReferences(string $directory, string $oldRelative, string $newRelative): int
{
    $updatedFiles = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($directory) + 1));
        if (preg_match('~(^|/)(tiles|\.media-backups|\.skin-backups)(/|$)~i', $relative) || !preg_match('/\.(?:xml|js|html?|json|css)$/i', $relative)) continue;
        $content = file_get_contents($file->getPathname());
        if ($content === false || !str_contains($content, $oldRelative)) continue;
        $updated = str_replace($oldRelative, $newRelative, $content);
        if ($updated !== $content && file_put_contents($file->getPathname(), $updated, LOCK_EX) !== false) $updatedFiles++;
    }
    return $updatedFiles;
}

function optimizeImage(string $directory, string $relative, int $maxWidth, int $quality, string $backupRoot): array
{
    $source = safeImagePath($directory, $relative);
    $info = getimagesize($source);
    if ($info === false) throw new RuntimeException('Format gambar tidak dapat dibaca.');
    $image = match ($info[2]) { IMAGETYPE_JPEG => @imagecreatefromjpeg($source), IMAGETYPE_PNG => @imagecreatefrompng($source), default => false };
    if (!$image) throw new RuntimeException('Gambar gagal dibuka: ' . $relative);
    $sourceWidth = imagesx($image); $sourceHeight = imagesy($image);
    $targetWidth = min($sourceWidth, $maxWidth);
    $targetHeight = (int) max(1, round($sourceHeight * ($targetWidth / $sourceWidth)));
    $output = imagecreatetruecolor($targetWidth, $targetHeight);
    imagealphablending($output, false); imagesavealpha($output, true);
    $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127); imagefilledrectangle($output, 0, 0, $targetWidth, $targetHeight, $transparent);
    imagecopyresampled($output, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
    $newRelative = preg_replace('/\.(?:jpe?g|png)$/i', '.webp', $relative);
    $destination = $directory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $newRelative);
    if (is_file($destination)) throw new RuntimeException('File tujuan sudah ada: ' . $newRelative);
    $backup = $backupRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_dir(dirname($backup)) && !mkdir(dirname($backup), 0775, true) && !is_dir(dirname($backup))) throw new RuntimeException('Folder backup gagal dibuat.');
    if (!copy($source, $backup)) throw new RuntimeException('Backup gambar gagal dibuat.');
    $before = filesize($source) ?: 0;
    if (!imagewebp($output, $destination, $quality)) throw new RuntimeException('WebP gagal dibuat.');
    imagedestroy($image); imagedestroy($output);
    $referenceCount = updateReferences($directory, $relative, (string) $newRelative);
    if (!unlink($source)) throw new RuntimeException('File sumber gagal dihapus setelah backup.');
    clearstatcache(true, $destination);
    return ['path' => $relative, 'output' => $newRelative, 'before' => $before, 'after' => filesize($destination) ?: 0, 'width' => $targetWidth, 'height' => $targetHeight, 'references' => $referenceCount];
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, false, 'Metode tidak diizinkan.');
    $payload = json_decode((string) file_get_contents('php://input'), true, 128, JSON_THROW_ON_ERROR);
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) respond(403, false, 'Sesi CMS tidak valid.');
    if (!extension_loaded('gd') || !function_exists('imagewebp')) respond(500, false, 'PHP GD dengan dukungan WebP tidak tersedia.');
    $id = cleanId((string) ($payload['id_lokasi'] ?? ''));
    $directory = findTourDirectory(activeProjectRoot(), $id);
    if ($directory === null) respond(404, false, 'Folder tur tidak ditemukan.');
    if (($payload['action'] ?? '') === 'scan') respond(200, true, 'Pemindaian selesai.', ['images' => scanImages($directory)]);
    if (($payload['action'] ?? '') !== 'optimize') respond(422, false, 'Aksi tidak dikenali.');
    $files = array_values(array_unique(array_map('strval', (array) ($payload['files'] ?? []))));
    if ($files === []) respond(422, false, 'Pilih minimal satu gambar.');
    $maxWidth = max(320, min(4096, (int) ($payload['max_width'] ?? 1920)));
    $quality = max(40, min(95, (int) ($payload['quality'] ?? 82)));
    $backupRoot = $directory . '/.media-backups/' . date('Ymd-His');
    $results = []; $errors = [];
    foreach ($files as $relative) {
        try { $results[] = optimizeImage($directory, $relative, $maxWidth, $quality, $backupRoot); }
        catch (Throwable $error) { $errors[] = ['path' => $relative, 'message' => $error->getMessage()]; }
    }
    $saved = array_sum(array_column($results, 'before')) - array_sum(array_column($results, 'after'));
    respond(200, true, count($results) . ' gambar berhasil dioptimasi.', ['results' => $results, 'errors' => $errors, 'saved_bytes' => max(0, $saved), 'backup' => str_replace('\\', '/', substr($backupRoot, strlen($directory) + 1))]);
} catch (Throwable $error) {
    respond(500, false, $error->getMessage());
}
