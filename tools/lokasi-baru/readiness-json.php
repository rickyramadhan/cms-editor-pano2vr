<?php
/**
 * readiness-json.php — kontrak CMS -> VT Inspector: kesiapan CMS Smart Hub per tur, sebagai SATU dokumen JSON di stdout.
 *   php tools/lokasi-baru/readiness-json.php <projectId> [slug]
 * Keluaran: {"project":"badung","tours":{"<slug>":{"score":0-100,"todo":["..."],"scenes":N,"type":"pantai","runtime":true}}}
 * Memakai shReadiness (lewat shStatus) yang sama dengan tab Smart Hub; rubrik TIDAK disalin di sini.
 * Kode keluar: 0 berhasil, 2 argumen kurang, 1 galat (pesan JSON {"error":"..."} di stdout, detail di stderr).
 */
ini_set('display_errors', '0');
ini_set('log_errors', '0');
define('SMART_HUB_INCLUDE_ONLY', true);
$CMS = dirname(__DIR__, 2);
if ($argc < 2) { fwrite(STDERR, "Pakai: php readiness-json.php <projectId> [slug]\n"); exit(2); }
chdir($CMS);
$_SESSION = []; $_GET['project'] = $argv[1];
try {
    require $CMS . '/smart-hub.php';
    $registry = projectRegistry();
    $pid = cmsCleanKey($argv[1]);
    if (!isset($registry[$pid])) throw new RuntimeException('Project tidak dikenal: ' . $argv[1]);
    $project = activeProject();
    $st = shSettings($project);
    if (!is_file($st['path'] . '/boot.js')) throw new RuntimeException('Folder Smart Kit tidak valid: ' . $st['path']);
    $only = isset($argv[2]) ? cmsCleanKey($argv[2]) : '';
    $tours = [];
    foreach (shStatus($st, shTours((string) $project['path'])) as $r) {
        $id = (string) $r['id'];
        if ($only !== '' && $id !== $only) continue;
        $tours[$id] = ['score' => (int) $r['ready']['score'], 'todo' => array_values($r['ready']['todo']), 'scenes' => (int) $r['ready']['scenes'], 'type' => (string) $r['pack']['type'], 'runtime' => (bool) $r['runtimeReady']];
    }
    echo json_encode(['project' => $project['id'], 'tours' => (object) $tours], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), "\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE), "\n";
    exit(1);
}
