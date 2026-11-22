<?php
declare(strict_types=1);
/*
 * inspector-hub.php — backend tab "Inspector" (integrasi VT Inspector v3) untuk project aktif.
 *
 *  - Membaca laporan Inspector per tur (hanya baca) dan menggabungkannya dengan rubrik kesiapan Smart Hub (shReadiness).
 *  - Menjalankan scan statis Inspector lewat CLI (aksi "scan"); robot/AI/tautan dijalankan dari server Inspector sendiri.
 *  - Dipanggil lewat api.php?action=inspector (POST JSON + csrf, pengguna login). Dapat di-include index.php untuk render awal (INSPECTOR_HUB_INCLUDE_ONLY).
 */
if (!defined('SMART_HUB_INCLUDE_ONLY')) define('SMART_HUB_INCLUDE_ONLY', true);   // pakai fungsi Smart Hub tanpa menjalankan pengendalinya
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/project-manager.php';
require_once __DIR__ . '/includes/inspector-lib.php';
require_once __DIR__ . '/smart-hub.php';

/** Kesiapan CMS per tur (rubrik shReadiness yang sama dengan tab Smart Hub). */
function ivReadinessRows(array $project): array
{
    $st = shSettings($project);
    if (!is_file($st['path'] . '/boot.js')) return [];
    $rows = [];
    foreach (shStatus($st, shTours((string) $project['path'])) as $r) $rows[(string) $r['id']] = $r;
    return $rows;
}

function ivState(array $project): array
{
    $s = ivSettings();
    $id = (string) $project['id'];
    $tours = shTours((string) $project['path']);
    $ready = ivReadinessRows($project);
    $stale = ivStaleMap();
    $rows = []; $scored = []; $latest = ''; $gateN = 0;
    foreach ($tours as $slug => $t) {
        $r = ivReadReport($s, $id, (string) $slug);
        $cms = $ready[$slug]['ready'] ?? null;
        $sum = ivSummarize($r, $cms['score'] ?? null);
        $isStale = ivIsStale($stale, $id, (string) $slug, $r);
        if ($sum) { $scored[] = $sum['score']; if ($sum['at'] > $latest) $latest = $sum['at']; if ($sum['ready'] && !$isStale) $gateN++; }
        $rows[] = ['id' => (string) $slug, 'name' => (string) ($ready[$slug]['name'] ?? shHumanize((string) $slug)), 'report' => $sum, 'stale' => $isStale, 'staleReason' => (string) ($stale[$id . '/' . $slug]['reason'] ?? ''),
            'cms' => $cms ? ['score' => (int) $cms['score'], 'todo' => $cms['todo']] : null, 'applied' => (bool) ($ready[$slug]['applied'] ?? false), 'runtime' => (bool) ($ready[$slug]['runtimeReady'] ?? false)];
    }
    usort($rows, static fn($a, $b) => strnatcasecmp($a['id'], $b['id']));
    $probe = ivProbe($s);
    return [
        'project' => ['id' => $id, 'name' => (string) ($project['name'] ?? $id)],
        'settings' => ['path' => $s['path'], 'pathOk' => $s['pathOk'], 'node' => $s['node'], 'nodeOk' => $s['node'] !== '', 'url' => $s['url'], 'configuredPath' => $s['configuredPath'], 'configuredNode' => $s['configuredNode'], 'configuredUrl' => $s['configuredUrl']],
        'server' => $probe, 'isAdmin' => cmsIsAdmin(), 'kitOk' => (bool) $ready || is_file(shSettings($project)['path'] . '/boot.js'),
        'summary' => ['tours' => count($rows), 'scanned' => count($scored), 'avg' => $scored ? (int) round(array_sum($scored) / count($scored)) : null, 'ready' => $gateN, 'stale' => count(array_filter($rows, static fn($r) => $r['stale'])), 'lastScan' => $latest],
        'tours' => $rows,
    ];
}

function ivDetail(array $project, string $slug): array
{
    $s = ivSettings(); $id = (string) $project['id'];
    $tours = shTours((string) $project['path']);
    if (!isset($tours[$slug])) throw new RuntimeException('Lokasi tidak ditemukan.');
    $r = ivReadReport($s, $id, $slug);
    if (!$r) return ['slug' => $slug, 'report' => null, 'findings' => [], 'history' => []];
    $ready = ivReadinessRows($project);
    $sum = ivSummarize($r, $ready[$slug]['ready']['score'] ?? null);
    $info = (array) ($r['static']['info'] ?? []);
    return ['slug' => $slug, 'report' => $sum, 'findings' => ivFindings($r), 'history' => array_slice(array_map(static fn($h) => ['at' => (string) ($h['at'] ?? ''), 'score' => (int) ($h['score'] ?? 0)], (array) ($r['history'] ?? [])), -12),
        'info' => ['profile' => (string) ($info['profileName'] ?? $info['profile'] ?? ''), 'scenes' => $info['scenes'] ?? null, 'hotspots' => $info['hotspots'] ?? null]];
}

if (!defined('INSPECTOR_HUB_INCLUDE_ONLY')) {
    try {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') cmsJsonResponse(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
        $payload = cmsJsonPayload();
        cmsAssertCsrf((string) ($payload['csrf'] ?? ''));
        if (function_exists('set_time_limit')) @set_time_limit(420);
        $project = activeProject();   // sudah memfilter project sesuai akses akun; pengguna belum login ditolak oleh gerbang auth
        $op = (string) ($payload['op'] ?? 'state');
        switch ($op) {
            case 'state':
                cmsJsonResponse(['success' => true, 'state' => ivState($project)]);
            case 'detail':
                cmsJsonResponse(['success' => true] + ivDetail($project, cmsCleanKey((string) ($payload['location'] ?? ''))));
            case 'save_settings':
                cmsRequireAdmin('Pengaturan VT Inspector');
                ivSaveSettings((array) ($payload['settings'] ?? []));
                cmsAudit('inspector.pengaturan', ['project' => $project['id']]);
                cmsJsonResponse(['success' => true, 'message' => 'Pengaturan VT Inspector disimpan.', 'state' => ivState($project)]);
            case 'scan':
                // slug hanya dari daftar tur project (shTours); "all" = semua tur
                $tours = shTours((string) $project['path']);
                $all = ($payload['locations'] ?? null) === 'all';
                $slugs = [];
                if (!$all) foreach ((array) ($payload['locations'] ?? []) as $x) { $x = cmsCleanKey((string) $x); if (!isset($tours[$x])) throw new RuntimeException('Lokasi tidak ditemukan: ' . $x); $slugs[] = $x; }
                if (!$all && !$slugs) throw new RuntimeException('Pilih minimal satu lokasi.');
                $run = ivRunScan((string) $project['id'], $slugs);
                cmsAudit('inspector.scan', ['project' => $project['id'], 'lokasi' => $all ? 'semua' : implode(',', $slugs), 'ok' => $run['ok']]);
                cmsJsonResponse(['success' => $run['ok'], 'message' => $run['ok'] ? 'Inspeksi statis selesai (' . $run['seconds'] . ' detik).' : ($run['timedOut'] ? 'Inspeksi melewati batas waktu dan dihentikan.' : 'Inspeksi gagal (kode ' . $run['code'] . ').'), 'log' => $run['log'], 'state' => ivState($project)]);
            default:
                throw new RuntimeException('Operasi tidak dikenal: ' . $op);
        }
    } catch (Throwable $e) {
        cmsJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}
