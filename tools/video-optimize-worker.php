<?php
declare(strict_types=1);
/* video-optimize-worker.php — worker CLI (latar belakang) untuk includes/video-optimizer.php.
 * php video-optimize-worker.php <statusFile> <tourDir> <kinds,koma> <ffmpegDir|-> <force 0|1> */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/video-optimizer.php';
@set_time_limit(0);
[, $statusFile, $tourDir, $kindsArg, $ffDir, $force] = array_pad($argv, 6, '');
$kinds = array_values(array_intersect(explode(',', $kindsArg), VO_KINDS));
$st = ['state' => 'running', 'updated' => time(), 'started' => time(), 'error' => '', 'kinds' => array_fill_keys($kinds, ['state' => 'queued', 'pct' => 0, 'message' => 'Mengantre…'])];
$save = static function () use (&$st, $statusFile): void { $st['updated'] = time(); @file_put_contents($statusFile, json_encode($st, JSON_UNESCAPED_UNICODE), LOCK_EX); };
$save();
try {
    if (!is_dir(voDir($tourDir))) throw new RuntimeException('Folder video tidak ditemukan.');
    $bin = voFfmpeg($ffDir === '-' ? '' : $ffDir);
    if (!$bin) throw new RuntimeException('FFmpeg tidak ditemukan. Pasang FFmpeg atau isi "Folder FFmpeg" di Optimasi Loading › Pengaturan.');
    foreach ($kinds as $k) {
        $st['kinds'][$k] = ['state' => 'running', 'pct' => 1, 'message' => 'Mengompres…']; $save();
        $last = 0;
        $r = voCompress($bin, $tourDir, $k, $force === '1', static function (array $x) use (&$st, $k, $save, &$last): void {
            if (isset($x['pct'])) $st['kinds'][$k]['pct'] = $x['pct'];
            if (isset($x['msg'])) $st['kinds'][$k]['message'] = $x['msg'];
            if (time() !== $last) { $last = time(); $save(); }
        });
        $st['kinds'][$k] = ['state' => 'done', 'pct' => 100, 'status' => $r['status'], 'before' => $r['before'], 'after' => $r['after'], 'message' => $r['message']]; $save();
    }
    $st['state'] = 'done';
} catch (Throwable $e) {
    $st['state'] = 'error'; $st['error'] = $e->getMessage();
}
$save();
