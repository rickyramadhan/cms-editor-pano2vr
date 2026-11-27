<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
require_once __DIR__ . '/includes/backup-store.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';
require_once __DIR__ . '/language-manager.php';
require_once __DIR__ . '/includes/pano-memory.php';
require_once __DIR__ . '/includes/hotspot-editor.php';

function respond(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Simpan perubahan editor hotspot langsung (Audit › Hotspot › "Edit di panorama") untuk satu panorama. */
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, ['success' => false, 'message' => 'Metode tidak diizinkan.']);
    $payload = json_decode((string) file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($payload) || !hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) respond(403, ['success' => false, 'message' => 'Sesi formulir tidak valid.']);
    $locationId = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', (string) ($payload['id_lokasi'] ?? '')));
    if ($locationId === '') respond(422, ['success' => false, 'message' => 'Lokasi tidak valid.']);
    $project = activeProject();
    $result = heApply((string) $project['path'], $locationId, $payload);

    $setupNote = '';
    if ($result['changed']) {
        // Template dengan setup.json (mis. badung): naikkan ?ts= di index.html supaya pengunjung tidak memakai pano.xml lama dari cache.
        try {
            $panoFile = hcFindPano((string) $project['path'], $locationId);
            require_once __DIR__ . '/skin-templates.php';
            $templateId = projectSkinTemplateId(activeProject(), skinTemplateList());
            $setup = $templateId !== '' ? skinTemplateSetup($templateId) : null;
            if ($setup !== null && $panoFile !== null) { skinRunSetup($setup, dirname($panoFile)); $setupNote = ' Cache tur diperbarui otomatis.'; }
        } catch (Throwable $setupError) {
            $setupNote = ' (Cache tur belum diperbarui: ' . $setupError->getMessage() . ')';
        }
    }
    $parts = [];
    if ($result['updated'] > 0) $parts[] = $result['updated'] . ' diubah';
    if ($result['deleted'] > 0) $parts[] = $result['deleted'] . ' dihapus';
    if ($result['added'] !== []) $parts[] = count($result['added']) . ' ditambah';
    respond(200, [
        'success' => true,
        'message' => $parts === [] ? 'Tidak ada hotspot yang berubah.' : 'Hotspot disimpan (' . implode(', ', $parts) . ').' . $setupNote,
        'result' => $result,
    ]);
} catch (Throwable $error) {
    respond(400, ['success' => false, 'message' => $error->getMessage()]);
}
