<?php
declare(strict_types=1);
/*
 * pano-memory-restore.php — terapkan ulang / abaikan perubahan tersimpan (penamaan + terjemahan) pada pano.xml satu lokasi.
 * Dipanggil lewat api.php?action=pano-memory (POST JSON: csrf, id_lokasi, mode = apply | ignore).
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/backup-store.php';
require_once __DIR__ . '/project-manager.php';
require_once __DIR__ . '/language-manager.php';
require_once __DIR__ . '/includes/pano-memory.php';

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') cmsJsonResponse(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
    $payload = cmsJsonPayload();
    cmsAssertCsrf((string) ($payload['csrf'] ?? ''));
    $mode = (string) ($payload['mode'] ?? '');
    if (!in_array($mode, ['apply', 'ignore'], true)) throw new RuntimeException('Mode tidak dikenal.');
    $locationId = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', (string) ($payload['id_lokasi'] ?? '')));
    if ($locationId === '') throw new RuntimeException('Lokasi tidak valid.');

    $root = activeProjectRoot();
    $panoFile = null;
    foreach (new DirectoryIterator($root) as $entry) {
        if (!$entry->isDir() || $entry->isDot()) continue;
        $candidate = $entry->getPathname() . '/pano.xml';
        if (!is_file($entry->getPathname() . '/skin.js') || !is_file($candidate)) continue;
        if (strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', $entry->getFilename())) === $locationId) { $panoFile = $candidate; break; }
    }
    if ($panoFile === null) throw new RuntimeException('pano.xml lokasi tidak ditemukan.');
    $tourDir = dirname($panoFile);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = true;
    $dom->formatOutput = false;
    if (!$dom->load($panoFile)) throw new RuntimeException('pano.xml tidak valid.');
    $data = panoMemoryLoad($tourDir);
    if ($data === null) throw new RuntimeException('Belum ada perubahan tersimpan untuk lokasi ini.');
    $plan = panoMemoryPlan($dom, $data);

    if ($mode === 'ignore') {
        $data['ignored'] = $plan['hash'];
        panoMemoryWrite($tourDir, $data);
        cmsJsonResponse(['success' => true, 'message' => 'Pemberitahuan disembunyikan sampai ada perbedaan baru.']);
    }

    if ($plan['count'] === 0) cmsJsonResponse(['success' => true, 'applied' => 0, 'message' => 'Tidak ada yang perlu diterapkan: pano.xml sudah memuat semua perubahan tersimpan.']);
    $applied = panoMemoryApply($dom, $plan, $root);
    $backupDirectory = cmsBackupEnsure($tourDir, 'media');
    if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) throw new RuntimeException('Folder backup gagal dibuat.');
    if (!copy($panoFile, $backupDirectory . '/pano-before-memory-restore-' . date('Ymd-His') . '.xml')) throw new RuntimeException('Backup pano.xml gagal dibuat.');
    if ($dom->save($panoFile) === false) throw new RuntimeException('Gagal menyimpan pano.xml.');
    $data['ignored'] = '';
    panoMemoryWrite($tourDir, $data);
    cmsAudit('pano.memory.restore', ['lokasi' => $locationId, 'diterapkan' => $applied]);
    cmsJsonResponse(['success' => true, 'applied' => $applied, 'message' => $applied . ' perubahan tersimpan diterapkan ulang ke pano.xml.']);
} catch (Throwable $e) {
    cmsJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
