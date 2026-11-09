<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Metode tidak diizinkan.');
    if (!class_exists('ZipArchive')) throw new RuntimeException('Ekstensi PHP ZipArchive belum aktif.');
    if (function_exists('set_time_limit')) @set_time_limit(0);
    $payload = json_decode((string) file_get_contents('php://input'), true, 16, JSON_THROW_ON_ERROR);
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) throw new RuntimeException('Sesi CMS tidak valid.');
    $id = strtolower(trim((string) ($payload['id_lokasi'] ?? '')));
    $relative = trim(str_replace('\\', '/', (string) ($payload['path'] ?? '')), '/');
    if (!preg_match('/^[a-z0-9_-]+$/', $id) || $relative === '' || str_contains($relative, '..')) throw new RuntimeException('Lokasi tidak valid.');
    $root = activeProjectRoot();
    $directory = realpath($root . '/' . str_replace('/', DIRECTORY_SEPARATOR, $relative));
    $realRoot = realpath($root);
    if ($directory === false || $realRoot === false || !is_dir($directory) || !is_file($directory . '/pano.xml')) throw new RuntimeException('Folder virtual tour tidak ditemukan.');
    $normalizedDirectory = strtolower(str_replace('\\', '/', $directory));
    $normalizedRoot = rtrim(strtolower(str_replace('\\', '/', $realRoot)), '/');
    if (!str_starts_with($normalizedDirectory . '/', $normalizedRoot . '/')) throw new RuntimeException('Folder berada di luar project aktif.');
    $zipDirectory = $root . '/zip';
    if (!is_dir($zipDirectory) && !mkdir($zipDirectory, 0775, true) && !is_dir($zipDirectory)) throw new RuntimeException('Folder zip gagal dibuat.');
    $fileName = $id . ' ' . date('ymd') . '.zip';
    $zipPath = $zipDirectory . '/' . $fileName;
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('File ZIP gagal dibuat.');
    $files = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        $local = str_replace('\\', '/', substr($file->getPathname(), strlen($directory) + 1));
        if (!$zip->addFile($file->getPathname(), $local)) { $zip->close(); throw new RuntimeException('Gagal memasukkan ' . $local . ' ke ZIP.'); }
        $files++;
    }
    if (!$zip->close()) throw new RuntimeException('File ZIP gagal diselesaikan.');
    echo json_encode(['success' => true, 'message' => $fileName . ' berhasil dibuat.', 'file' => 'zip/' . $fileName, 'files' => $files, 'bytes' => filesize($zipPath)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
}
