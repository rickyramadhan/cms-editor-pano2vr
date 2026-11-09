<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Metode tidak diizinkan.');
    $payload = json_decode((string) file_get_contents('php://input'), true, 32, JSON_THROW_ON_ERROR);
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) throw new RuntimeException('Sesi CMS tidak valid.');
    $label = trim(strip_tags((string) ($payload['label'] ?? '')));
    $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $label), '-'));
    if ($label === '' || $slug === '') throw new RuntimeException('Nama kategori tidak valid.');
    $project = activeProject();
    $categories = (array) ($project['categories'] ?? []);
    $categories[$slug] = $label;
    updateProjectCategories((string) $project['id'], $categories);
    echo json_encode(['success' => true, 'slug' => $slug, 'label' => $label], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
}
