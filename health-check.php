<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';

try {
    $payload = json_decode((string) file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) {
        throw new RuntimeException('Sesi tidak valid. Muat ulang CMS.');
    }

    $locationId = strtolower(trim((string) ($payload['id_lokasi'] ?? '')));
    if (!preg_match('/^[a-z0-9_-]+$/', $locationId)) {
        throw new RuntimeException('ID lokasi tidak valid.');
    }

    $deployUrl = rtrim((string) (activeProject()['deploy_url'] ?? ''), '/');
    if ($deployUrl === '') throw new RuntimeException('URL deploy project belum dikonfigurasi.');
    $baseUrl = $deployUrl . '/' . rawurlencode($locationId) . '/';
    $targets = [
        'Halaman' => $baseUrl,
        'pano.xml' => $baseUrl . 'pano.xml',
        'skin.js' => $baseUrl . 'skin.js',
        'Pano2VR player' => $baseUrl . 'pano2vr_player.js',
    ];
    $checks = [];
    $startedAt = microtime(true);

    foreach ($targets as $label => $url) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'GovirtualTourCMS/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $itemStartedAt = microtime(true);
        curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        $checks[] = [
            'label' => $label,
            'url' => $url,
            'status' => $status,
            'ok' => $status >= 200 && $status < 400,
            'latency_ms' => (int) round((microtime(true) - $itemStartedAt) * 1000),
            'error' => $error,
        ];
    }

    $healthy = count(array_filter($checks, static fn(array $check): bool => $check['ok'])) === count($checks);
    echo json_encode([
        'success' => true,
        'healthy' => $healthy,
        'base_url' => $baseUrl,
        'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        'checked_at' => date(DATE_ATOM),
        'checks' => $checks,
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
}
