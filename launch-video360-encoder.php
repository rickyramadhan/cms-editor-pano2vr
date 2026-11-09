<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';
cmsRequireAdmin('Menjalankan encoder');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Metode tidak diizinkan.');
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($_POST['csrf'] ?? ''))) throw new RuntimeException('Sesi CMS tidak valid.');
    $address = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if (!in_array($address, ['127.0.0.1', '::1'], true)) throw new RuntimeException('Encoder desktop hanya dapat dijalankan dari komputer server lokal.');
    $batch = activeProjectAssetBaseRoot() . '/360/tools/start-encode-gui.bat';
    if (!is_file($batch)) throw new RuntimeException('start-encode-gui.bat tidak ditemukan pada project aktif.');
    $command = 'cmd.exe /c start "Govirtual 360 Encoder" "' . str_replace('"', '""', str_replace('/', '\\', $batch)) . '"';
    $handle = popen($command, 'r');
    if ($handle === false) throw new RuntimeException('Windows gagal menjalankan encoder.');
    pclose($handle);
    echo json_encode(['success' => true, 'message' => 'GUI encoder dijalankan pada desktop Windows.'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
}
