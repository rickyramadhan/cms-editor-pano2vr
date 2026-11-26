<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
require_once __DIR__ . '/includes/backup-store.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';
require_once __DIR__ . '/language-manager.php';
require_once __DIR__ . '/includes/pano-memory.php';
require_once __DIR__ . '/includes/hotspot-calibration.php';

function respond(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Terapkan (action "apply", moves = perpindahan hasil mesin kalibrasi) atau urungkan kalibrasi terakhir (action "undo"). */
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, ['success' => false, 'message' => 'Metode tidak diizinkan.']);
    $payload = json_decode((string) file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($payload) || !hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) respond(403, ['success' => false, 'message' => 'Sesi formulir tidak valid.']);
    $action = (string) ($payload['action'] ?? 'apply');
    if (!in_array($action, ['apply', 'undo'], true)) respond(422, ['success' => false, 'message' => 'Aksi tidak dikenal.']);
    $locationId = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', (string) ($payload['id_lokasi'] ?? '')));
    if ($locationId === '') respond(422, ['success' => false, 'message' => 'Lokasi tidak valid.']);
    $project = activeProject();
    $projectRoot = (string) $project['path'];
    $assetsRoot = projectRuntimeDirectory($project);

    $result = $action === 'undo'
        ? hcUndo($projectRoot, $assetsRoot, $locationId)
        : hcApply($projectRoot, $assetsRoot, $locationId, (array) ($payload['moves'] ?? []));

    $panoFile = hcFindPano($projectRoot, $locationId);
    $tourDir = $panoFile !== null ? dirname($panoFile) : '';
    $panoChanged = array_filter((array) $result['journal'], static fn(array $j): bool => ($j['source'] ?? '') !== 'lokasi') !== [];
    $setupNote = '';
    if ($panoChanged && $tourDir !== '') {
        // Template dengan setup.json (mis. badung): naikkan ?ts= di index.html supaya pengunjung tidak memakai pano.xml lama dari cache.
        try {
            require_once __DIR__ . '/skin-templates.php';
            $templateId = projectSkinTemplateId(activeProject(), skinTemplateList());
            $setup = $templateId !== '' ? skinTemplateSetup($templateId) : null;
            if ($setup !== null) { skinRunSetup($setup, $tourDir); $setupNote = ' Cache tur diperbarui otomatis.'; }
        } catch (Throwable $setupError) {
            $setupNote = ' (Cache tur belum diperbarui: ' . $setupError->getMessage() . ')';
        }
    }
    $count = (int) $result['applied'];
    $verb = $action === 'undo' ? 'dikembalikan' : 'diterapkan';
    respond(200, [
        'success' => true,
        'message' => $count === 0 ? 'Tidak ada posisi yang berubah.' : $count . ' hotspot ' . $verb . '.' . $setupNote,
        'applied' => $count,
        'undo' => $tourDir !== '' ? hcJournalSummary($tourDir) : null,
    ]);
} catch (Throwable $error) {
    respond(400, ['success' => false, 'message' => $error->getMessage()]);
}
