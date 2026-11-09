<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';
cmsRequireAdmin('Membuka folder di server');
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Metode tidak diizinkan.');
    $payload = json_decode((string) file_get_contents('php://input'), true, 16, JSON_THROW_ON_ERROR);
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) throw new RuntimeException('Sesi CMS tidak valid.');
    $relative = trim(str_replace('\\', '/', (string) ($payload['path'] ?? '')), '/');
    if ($relative === '' || str_contains($relative, '..')) throw new RuntimeException('Path lokasi tidak valid.');
    $root = activeProjectRoot();
    $directory = realpath($root . '/' . str_replace('/', DIRECTORY_SEPARATOR, $relative));
    $realRoot = realpath($root);
    if ($directory === false || $realRoot === false || !is_dir($directory)) throw new RuntimeException('Folder lokasi tidak ditemukan.');
    $normalizedDirectory = strtolower(str_replace('\\', '/', $directory));
    $normalizedRoot = rtrim(strtolower(str_replace('\\', '/', $realRoot)), '/');
    if (!str_starts_with($normalizedDirectory . '/', $normalizedRoot . '/')) throw new RuntimeException('Folder berada di luar project aktif.');
    if (!is_file($directory . '/pano.xml')) throw new RuntimeException('Folder ini bukan lokasi virtual tour.');
    $command = 'start "" explorer.exe ' . escapeshellarg($directory);
    pclose(popen($command, 'r'));
    echo json_encode(['success' => true, 'message' => 'Folder dibuka di File Explorer.']);
} catch (Throwable $error) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
}
