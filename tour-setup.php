<?php
declare(strict_types=1);
/*
 * tour-setup.php — tombol "Setup" di editor hotspot (Audit › pano.xml).
 * Menjalankan aksi dari skin-templates/<template project>/setup.json pada satu lokasi.
 * Template tanpa setup.json tidak punya tombol ini. Dipanggil lewat api.php?action=tour-setup (POST JSON + csrf).
 */
if (!defined('SMART_HUB_INCLUDE_ONLY')) define('SMART_HUB_INCLUDE_ONLY', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/project-manager.php';
require_once __DIR__ . '/skin-templates.php';
require_once __DIR__ . '/smart-hub.php';   // shTours()

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') cmsJsonResponse(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
    $payload = cmsJsonPayload();
    cmsAssertCsrf((string) ($payload['csrf'] ?? ''));
    $project = activeProject();
    $templateId = projectSkinTemplateId($project, skinTemplateList());
    $setup = $templateId !== '' ? skinTemplateSetup($templateId) : null;
    if ($setup === null) throw new RuntimeException('Template skin project ini tidak punya setup.json.');
    $id = cmsCleanKey((string) ($payload['id_lokasi'] ?? ''));
    $tours = shTours((string) $project['path']);
    if ($id === '' || !isset($tours[$id])) throw new RuntimeException('Lokasi tidak ditemukan.');
    $messages = [];
    // Verifikasi (opsional): pastikan arah yang baru dipilih benar-benar sudah ada di pano.xml di server.
    $verify = is_array($payload['verify'] ?? null) ? $payload['verify'] : null;
    if ($verify !== null) {
        $dom = new DOMDocument();
        if (!@$dom->load($tours[$id]['dir'] . '/pano.xml')) throw new RuntimeException('pano.xml tidak dapat dibaca untuk verifikasi.');
        $xp = new DOMXPath($dom);
        $lit = static fn(string $v): string => "'" . str_replace("'", '', $v) . "'";
        $el = $xp->query('//panorama[@id=' . $lit((string) ($verify['nodeId'] ?? '')) . ']//hotspot[@id=' . $lit((string) ($verify['id'] ?? '')) . ']')?->item(0);
        if (!$el instanceof DOMElement) throw new RuntimeException('Hotspot untuk verifikasi tidak ditemukan di pano.xml.');
        $saved = array_map('floatval', explode('/', $el->getAttribute('target')));
        $want = array_map('floatval', explode('/', (string) ($verify['target'] ?? '')));
        if (count($saved) < 3 || count($want) < 3 || abs($saved[0] - $want[0]) > 0.011 || abs($saved[1] - $want[1]) > 0.011 || abs($saved[2] - $want[2]) > 0.011) {
            throw new RuntimeException('Arah di pano.xml (' . $el->getAttribute('target') . ') tidak sama dengan arah yang dipilih (' . (string) ($verify['target'] ?? '') . '). Simpan ulang.');
        }
        $messages[] = 'arah terverifikasi di pano.xml: ' . $el->getAttribute('target');
    }
    $messages = array_merge($messages ?? [], skinRunSetup($setup, (string) $tours[$id]['dir']));
    cmsAudit('tour.setup', ['template' => $templateId, 'lokasi' => $id, 'aksi' => $setup['actions']]);
    cmsJsonResponse(['success' => true, 'message' => 'Setup ' . $setup['id'] . ' selesai: ' . implode('; ', $messages) . '. Muat ulang tur (Ctrl+F5) untuk melihat hasilnya.']);
} catch (Throwable $e) {
    cmsJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
