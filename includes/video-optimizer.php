<?php
declare(strict_types=1);
/*
 * video-optimizer.php — optimasi video intro konsep baru (video/mobile.webm + video/desktop.webm).
 *
 * Konsep baru: preload dan pemutaran video sudah ditangani loader bersama (assets/main-loader/main-loader.js), jadi tidak ada
 * lagi yang perlu ditambal di skin.js/index.html. Bagian yang masih menentukan kecepatan buka adalah UKURAN video. Modul ini
 * mengompres video itu dengan FFmpeg (VP9, 720p mobile / 1280p desktop, 30 fps) segera setelah diunggah dari tab Video.
 *
 * Aman diulang: file yang sudah diproses dicatat di video/.optimized.json (ukuran + waktu ubah) dan dilewati. Video asli
 * disimpan di video/_original/ (jangan diupload ke server). Modul ini tidak bergantung pada kode CMS lain agar bisa dipakai
 * oleh worker CLI (tools/video-optimize-worker.php).
 */

const VO_KINDS = ['mobile', 'desktop'];

/** Cari ffmpeg + ffprobe. $dir = folder dari pengaturan CMS (boleh kosong). Kembalikan ['ffmpeg'=>..,'ffprobe'=>..] atau null. */
function voFfmpeg(string $dir = ''): ?array
{
    $win = PHP_OS_FAMILY === 'Windows';
    $exe = $win ? '.exe' : '';
    $dirs = [];
    if ($dir !== '') $dirs[] = rtrim(str_replace('\\', '/', $dir), '/');
    foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $p) if ($p !== '') $dirs[] = rtrim(str_replace('\\', '/', $p), '/');
    if ($win) {
        foreach (['C:/ffmpeg/bin', 'C:/Program Files/ffmpeg/bin'] as $p) $dirs[] = $p;
        foreach (glob('C:/Users/*/AppData/Local/Microsoft/WinGet/Packages/Gyan.FFmpeg*/*/bin', GLOB_ONLYDIR) ?: [] as $p) $dirs[] = $p;
    } else {
        foreach (['/usr/bin', '/usr/local/bin', '/opt/homebrew/bin'] as $p) $dirs[] = $p;
    }
    foreach (array_unique($dirs) as $d) {
        if (is_file($d . '/ffmpeg' . $exe) && is_file($d . '/ffprobe' . $exe)) return ['ffmpeg' => $d . '/ffmpeg' . $exe, 'ffprobe' => $d . '/ffprobe' . $exe];
    }
    return null;
}

function voDir(string $tourDir): string { return rtrim(str_replace('\\', '/', $tourDir), '/') . '/video'; }

function voMarks(string $tourDir): array
{
    $f = voDir($tourDir) . '/.optimized.json';
    return is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
}

function voSaveMarks(string $tourDir, array $marks): void
{
    @file_put_contents(voDir($tourDir) . '/.optimized.json', json_encode($marks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

/** Status satu video: ['exists','size','state'=>none|baru|sudah|asli','original'=>bool,'before','after'] */
function voFileInfo(string $tourDir, string $kind): array
{
    $f = voDir($tourDir) . '/' . $kind . '.webm';
    if (!is_file($f)) return ['exists' => false, 'size' => 0, 'state' => 'none', 'original' => false];
    clearstatcache(true, $f);
    $size = (int) filesize($f); $mark = voMarks($tourDir)[$kind] ?? null;
    $done = is_array($mark) && (int) ($mark['size'] ?? -1) === $size && (int) ($mark['mtime'] ?? -1) === (int) filemtime($f);
    $orig = is_file(voDir($tourDir) . '/_original/' . $kind . '.webm');
    return ['exists' => true, 'size' => $size, 'state' => !$done ? 'baru' : ($orig ? 'sudah' : 'asli'), 'original' => $orig,
        'before' => (int) ($mark['before'] ?? 0), 'after' => (int) ($mark['after'] ?? 0)];
}

function voRun(array $cmd, ?callable $tick = null, int $timeout = 3600): array
{
    $err = tempnam(sys_get_temp_dir(), 'vo');
    $proc = @proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['file', $err, 'w']], $pipes);
    if (!is_resource($proc)) { @unlink($err); return [-1, '', 'proses tidak dapat dijalankan']; }
    $out = ''; $t0 = time();
    // stdout kecil (ffprobe); untuk ffmpeg stdout tidak dipakai (-progress ke berkas), jadi membaca blok aman.
    if ($tick === null) { $out = (string) stream_get_contents($pipes[1]); }
    else {
        stream_set_blocking($pipes[1], false);
        while (true) {
            $out .= (string) stream_get_contents($pipes[1]);
            if (!proc_get_status($proc)['running']) break;
            $tick();
            if (time() - $t0 > $timeout) { proc_terminate($proc); break; }
            usleep(400000);
        }
    }
    fclose($pipes[1]); $code = proc_close($proc);
    $e = (string) @file_get_contents($err); @unlink($err);
    return [$code, $out, $e];
}

function voProbe(array $bin, string $file): array
{
    [$c, $o, $e] = voRun([$bin['ffprobe'], '-v', 'error', '-print_format', 'json', '-show_streams', '-show_format', $file]);
    $j = json_decode($o, true);
    if ($c !== 0 || !is_array($j)) throw new RuntimeException('ffprobe gagal: ' . trim($e));
    $v = null; $a = false;
    foreach ($j['streams'] ?? [] as $s) { if (($s['codec_type'] ?? '') === 'video' && !$v) $v = $s; if (($s['codec_type'] ?? '') === 'audio') $a = true; }
    if (!$v) throw new RuntimeException('Tidak ada aliran video di berkas.');
    $fps = 30.0;
    if (preg_match('~^(\d+)/(\d+)$~', (string) ($v['r_frame_rate'] ?? ''), $m) && (int) $m[2] > 0) $fps = (int) $m[1] / (int) $m[2];
    return ['w' => (int) $v['width'], 'h' => (int) $v['height'], 'fps' => $fps, 'dur' => (float) ($j['format']['duration'] ?? 0), 'audio' => $a];
}

/**
 * Kompres satu video. $report(array) dipanggil dengan ['pct'=>0..100,'msg'=>'..'] untuk kemajuan.
 * Hasil: ['status'=>'OK|TETAP|SUDAH|TIDAK ADA|GAGAL','before','after','message']
 */
function voCompress(array $bin, string $tourDir, string $kind, bool $force = false, ?callable $report = null): array
{
    $report ??= static function (array $x): void {};
    $dir = voDir($tourDir); $file = $dir . '/' . $kind . '.webm';
    if (!is_file($file)) return ['status' => 'TIDAK ADA', 'before' => 0, 'after' => 0, 'message' => $kind . '.webm belum ada.'];
    $info0 = voFileInfo($tourDir, $kind);
    if (!$force && $info0['state'] !== 'baru') return ['status' => 'SUDAH', 'before' => $info0['before'] ?: $info0['size'], 'after' => $info0['size'], 'message' => 'Sudah dioptimasi.'];

    $before = (int) filesize($file); $mtime0 = (int) filemtime($file);
    $p = voProbe($bin, $file);
    $portrait = $p['h'] > $p['w'];
    $limit = ($kind === 'mobile' && $portrait) ? 720 : 1280;   // sama dengan mesin lama
    $vf = "scale='min($limit,iw)':-2" . ($p['fps'] > 31 ? ',fps=30' : '');
    $tmp = $dir . '/' . $kind . '.tmp.webm'; $prog = $dir . '/' . $kind . '.progress.txt';
    @unlink($tmp); @unlink($prog);
    $cmd = [$bin['ffmpeg'], '-hide_banner', '-nostats', '-loglevel', 'error', '-progress', $prog, '-y', '-i', $file, '-vf', $vf,
        '-c:v', 'libvpx-vp9', '-crf', '40', '-b:v', '0', '-row-mt', '1', '-deadline', 'good', '-cpu-used', '2', '-pix_fmt', 'yuv420p'];
    array_push($cmd, ...($p['audio'] ? ['-c:a', 'libopus', '-b:a', '64k'] : ['-an']));
    $cmd[] = $tmp;
    $report(['pct' => 1, 'msg' => 'Mengompres ' . $kind . '.webm…']);
    $tick = static function () use ($prog, $p, $report): void {
        $t = @file_get_contents($prog);
        if ($t !== false && $p['dur'] > 0 && preg_match_all('~out_time_ms=(\d+)~', $t, $m) && $m[1]) {
            $report(['pct' => max(1, min(99, (int) round(((int) end($m[1])) / 1e6 / $p['dur'] * 100)))]);
        }
    };
    [$code, , $err] = voRun($cmd, $tick, 3 * 3600);
    @unlink($prog);
    if ($code !== 0 || !is_file($tmp)) {
        @unlink($tmp);
        return ['status' => 'GAGAL', 'before' => $before, 'after' => 0, 'message' => 'ffmpeg gagal: ' . trim(mb_substr($err, -300))];
    }
    clearstatcache(true, $file);
    if ((int) filesize($file) !== $before || (int) filemtime($file) !== $mtime0) {   // diunggah ulang saat dikompres: hasil basi
        @unlink($tmp);
        return ['status' => 'GAGAL', 'before' => $before, 'after' => 0, 'message' => 'Video diganti saat dikompres; hasil dibuang. Jalankan lagi.'];
    }
    $after = (int) filesize($tmp);
    $marks = voMarks($tourDir);
    if ($after > $before * 0.85) {        // hemat <15%: pertahankan asli, tandai agar tidak diulang
        @unlink($tmp); clearstatcache(true, $file);
        $marks[$kind] = ['size' => (int) filesize($file), 'mtime' => (int) filemtime($file), 'before' => $before, 'after' => $before, 'at' => time()];
        voSaveMarks($tourDir, $marks);
        return ['status' => 'TETAP', 'before' => $before, 'after' => $after, 'message' => 'Hasil kompresi kurang dari 15% lebih kecil, video asli dipertahankan.'];
    }
    $orig = $dir . '/_original';
    if (!is_dir($orig) && !mkdir($orig, 0775, true) && !is_dir($orig)) { @unlink($tmp); return ['status' => 'GAGAL', 'before' => $before, 'after' => 0, 'message' => 'Folder _original tidak dapat dibuat.']; }
    $keep = $orig . '/' . $kind . '.webm';
    if (is_file($keep)) @unlink($keep);            // unggahan baru = sumber baru; cadangan lama tidak relevan lagi
    if (!rename($file, $keep) || !rename($tmp, $file)) { @unlink($tmp); return ['status' => 'GAGAL', 'before' => $before, 'after' => $after, 'message' => 'Gagal mengganti berkas video.']; }
    clearstatcache(true, $file);
    $marks[$kind] = ['size' => (int) filesize($file), 'mtime' => (int) filemtime($file), 'before' => $before, 'after' => $after, 'at' => time()];
    voSaveMarks($tourDir, $marks);
    return ['status' => 'OK', 'before' => $before, 'after' => $after, 'message' => 'Hemat ' . round((1 - $after / $before) * 100) . '%.'];
}

/** Kembalikan video asli dari video/_original/. */
function voRestore(string $tourDir, string $kind): array
{
    $dir = voDir($tourDir); $keep = $dir . '/_original/' . $kind . '.webm'; $file = $dir . '/' . $kind . '.webm';
    if (!is_file($keep)) return ['status' => 'TIDAK ADA', 'message' => 'Tidak ada cadangan asli untuk ' . $kind . '.webm.'];
    if (is_file($file) && !@unlink($file)) return ['status' => 'GAGAL', 'message' => 'Berkas video tidak dapat ditimpa.'];
    if (!rename($keep, $file)) return ['status' => 'GAGAL', 'message' => 'Gagal memindahkan cadangan.'];
    clearstatcache(true, $file);
    // tandai sebagai "dilewati": pengguna sengaja memulihkan asli, jangan dikompres ulang otomatis
    $marks = voMarks($tourDir); $sz = (int) filesize($file);
    $marks[$kind] = ['size' => $sz, 'mtime' => (int) filemtime($file), 'before' => $sz, 'after' => $sz, 'at' => time()];
    voSaveMarks($tourDir, $marks);
    return ['status' => 'OK', 'message' => 'Video asli dipulihkan.'];
}

/* ---------------- status pekerjaan latar belakang ---------------- */

function voStatusFile(string $dataDir, string $tourDir): string
{
    $d = rtrim($dataDir, '/\\') . '/video-optimize';
    if (!is_dir($d)) @mkdir($d, 0775, true);
    return $d . '/' . md5(strtolower(str_replace('\\', '/', $tourDir))) . '.json';
}

function voReadStatus(string $file): ?array
{
    if (!is_file($file)) return null;
    $j = json_decode((string) file_get_contents($file), true);
    if (!is_array($j)) return null;
    if (($j['state'] ?? '') === 'running' && time() - (int) ($j['updated'] ?? 0) > 90) { $j['state'] = 'error'; $j['error'] = 'Proses berhenti tiba-tiba.'; }
    return $j;
}

/** Jalankan worker CLI di latar belakang. Kembalikan false bila PHP CLI tidak ditemukan. */
function voSpawn(string $workerPhp, string $statusFile, string $tourDir, array $kinds, string $ffmpegDir, bool $force): bool
{
    $win = PHP_OS_FAMILY === 'Windows';
    $php = rtrim(PHP_BINDIR, '/\\') . '/php' . ($win ? '.exe' : '');
    if (!is_file($php)) $php = $win ? 'php' : '/usr/bin/php';
    $args = [$workerPhp, $statusFile, $tourDir, implode(',', $kinds), $ffmpegDir === '' ? '-' : $ffmpegDir, $force ? '1' : '0'];
    // status awal supaya UI langsung melihat "mengantre"
    $init = ['state' => 'running', 'updated' => time(), 'started' => time(), 'kinds' => array_fill_keys($kinds, ['state' => 'queued', 'pct' => 0, 'message' => 'Mengantre…']), 'error' => ''];
    file_put_contents($statusFile, json_encode($init, JSON_UNESCAPED_UNICODE));
    if ($win) {
        $q = static fn(string $a): string => '"' . str_replace('/', '\\', $a) . '"';
        $cmd = 'start "" /B ' . $q($php) . ' ' . implode(' ', array_map($q, $args)) . ' > NUL 2>&1';
        pclose(popen($cmd, 'r'));
    } else {
        exec('nohup ' . escapeshellarg($php) . ' ' . implode(' ', array_map('escapeshellarg', $args)) . ' > /dev/null 2>&1 &');
    }
    return true;
}

/** Antrekan optimasi untuk satu tur. Kembalikan ['queued'=>bool,'reason'=>string]. Dipakai upload otomatis & menu Optimasi Loading. */
function voQueue(string $dataDir, string $ffmpegDir, string $tourDir, array $kinds, bool $force = false): array
{
    $kinds = array_values(array_intersect($kinds, VO_KINDS));
    if (!$kinds) return ['queued' => false, 'reason' => 'Tidak ada video untuk dioptimasi.'];
    if (!voFfmpeg($ffmpegDir)) return ['queued' => false, 'reason' => 'FFmpeg tidak ditemukan di server; video diunggah apa adanya.'];
    $sf = voStatusFile($dataDir, $tourDir); $cur = voReadStatus($sf);
    if ($cur && ($cur['state'] ?? '') === 'running') return ['queued' => false, 'reason' => 'Optimasi untuk lokasi ini masih berjalan.'];
    voSpawn(dirname(__DIR__) . '/tools/video-optimize-worker.php', $sf, $tourDir, $kinds, $ffmpegDir, $force);
    return ['queued' => true, 'reason' => ''];
}

function voTourRunning(string $dataDir, string $tourDir): bool
{
    $s = voReadStatus(voStatusFile($dataDir, $tourDir));
    return $s !== null && ($s['state'] ?? '') === 'running';
}
