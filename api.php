<?php
declare(strict_types=1);

/**
 * Compatibility API router.
 *
 * New clients may use api.php?action=<name>. Existing endpoint filenames are
 * intentionally retained and execute the same controllers directly.
 */
$routes = [
    'add-language-progress' => 'add-language-progress.php',
    'add-welcome-text' => 'add-welcome-text.php',
    'ai-hub' => 'ai-hub.php',
    'bulk-action' => 'bulk-action.php',
    'generate-voice' => 'generate-voice.php',
    'health-check' => 'health-check.php',
    'image-optimizer' => 'image-optimizer.php',
    'launch-video360-encoder' => 'launch-video360-encoder.php',
    'open-location' => 'open-location.php',
    'project-category' => 'project-category.php',
    'save-pano' => 'save-pano.php',
    'smart-hub' => 'smart-hub.php',
    'smart-schema' => 'smart-schema.php',
    'smart-frontend' => 'smart-frontend.php',
    'optimizer' => 'optimizer.php',
    'translation-center' => 'translation-center.php',
    'upload-video' => 'upload-video.php',
    'upload-video360' => 'upload-video360.php',
    'zip-location' => 'zip-location.php',
];

$action = strtolower(trim((string) ($_GET['action'] ?? '')));
if (!isset($routes[$action])) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'API action tidak ditemukan.']);
    exit;
}

require __DIR__ . '/' . $routes[$action];
