<?php
declare(strict_types=1);
/*
 * optimizer.php — backend menu "Optimasi Loading": menjalankan mesin optimasi virtual tour (optimize-tour.js / optimize-video.js)
 * dari CMS, tanpa .bat. Mesin tetap Node.js; CMS memanggilnya lewat tools/optimizer-bridge.js sebagai pekerjaan latar belakang
 * (kemajuan ditulis ke berkas job, GUI memantau tiap detik). Semua operasi tulis khusus ADMIN karena mengubah index.html/skin.js tur.
 *
 * Mesin dicari berurutan: pengaturan CMS (optimizer.engine) → tools/optimizer/ (salinan di CMS) → akar project aktif → <htdocs>/semarang.
 * Pengaturan mesin (jalur Node, folder FFmpeg, folder mesin) bersifat per-mesin: disimpan di cms-settings.json.
 *
 * ops: state | start | job | cancel | settings | install_engine
 */
define('SMART_HUB_INCLUDE_ONLY', true);
require_once __DIR__ . '/smart-hub.php';   // memuat gerbang login, project aktif, dan shTours()

const OPT_ACTIONS = ['check', 'optimize', 'restore', 'video'];

function optSettings(): array
{
    $f = PROJECT_SETTINGS_FILE;
    $s = is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
    return array_merge(['node' => '', 'ffmpeg_dir' => '', 'engine' => ''], (array) ($s['optimizer'] ?? []));
}

function optSaveSettings(array $in): array
{
    $f = PROJECT_SETTINGS_FILE;
    $all = is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
    $norm = static fn(string $p): string => rtrim(str_replace('\\', '/', trim($p)), '/');
    $node = trim((string) ($in['node'] ?? ''));
    if ($node !== '' && (!is_file($node) || !preg_match('~(^|[\\\\/])node(\.exe)?$~i', $node))) throw new RuntimeException('Jalur Node.js tidak valid (harus menunjuk ke berkas node/node.exe).');
    $ff = $norm((string) ($in['ffmpeg_dir'] ?? ''));
    if ($ff !== '' && !is_file($ff . '/ffmpeg.exe') && !is_file($ff . '/ffmpeg')) throw new RuntimeException('Folder FFmpeg tidak berisi ffmpeg(.exe).');
    $eng = $norm((string) ($in['engine'] ?? ''));
    if ($eng !== '' && !is_file($eng . '/optimize-tour.js')) throw new RuntimeException('Folder mesin tidak berisi optimize-tour.js.');
    $all['optimizer'] = ['node' => $node, 'ffmpeg_dir' => $ff, 'engine' => $eng];
    if (file_put_contents($f, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n", LOCK_EX) === false) throw new RuntimeException('cms-settings.json tidak dapat ditulis.');
    return optSettings();
}

function optNode(array $set): array
{
    $cands = [];
    if ($set['node'] !== '') $cands[] = $set['node'];
    $cands[] = 'node';
    foreach (['C:/Program Files/nodejs/node.exe', 'C:/Program Files (x86)/nodejs/node.exe', '/usr/bin/node', '/usr/local/bin/node'] as $p) $cands[] = $p;
    foreach ($cands as $c) {
        if ($c !== 'node' && !is_file($c)) continue;
        $proc = @proc_open([$c, '-v'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($proc)) continue;
        $v = trim((string) stream_get_contents($pipes[1])); fclose($pipes[1]); fclose($pipes[2]);
        if (proc_close($proc) === 0 && preg_match('/^v(\d+)\./', $v, $m) && (int) $m[1] >= 14) return ['path' => $c, 'version' => $v];
    }
    return ['path' => '', 'version' => ''];
}

function optEngine(array $set, string $projectRoot): array
{
    $cands = [];
    if ($set['engine'] !== '') $cands[] = ['dir' => $set['engine'], 'source' => 'pengaturan'];
    $cands[] = ['dir' => str_replace('\\', '/', __DIR__) . '/tools/optimizer', 'source' => 'salinan di CMS'];
    $cands[] = ['dir' => $projectRoot, 'source' => 'akar project'];
    $cands[] = ['dir' => rtrim(cmsBaseDirectory(), '/') . '/semarang', 'source' => 'folder semarang'];
    foreach ($cands as $c) if (is_file($c['dir'] . '/optimize-tour.js')) return ['dir' => rtrim(str_replace('\\', '/', $c['dir']), '/'), 'source' => $c['source'], 'video' => is_file($c['dir'] . '/optimize-video.js')];
    return ['dir' => '', 'source' => '', 'video' => false];
}

function optJobsDir(): string
{
    $d = cmsDataDir() . '/optimizer-jobs';
    if (!is_dir($d) && !mkdir($d, 0770, true) && !is_dir($d)) throw new RuntimeException('Folder pekerjaan tidak dapat dibuat: ' . $d);
    foreach (glob($d . '/*.json') ?: [] as $old) if (@filemtime($old) < time() - 3 * 86400) @unlink($old);
    return $d;
}

function optBridge(): string { return str_replace('\\', '/', __DIR__) . '/tools/optimizer-bridge.js'; }

function optReadJob(string $id): ?array
{
    if (!preg_match('/^[a-f0-9]{16}$/', $id)) throw new RuntimeException('ID pekerjaan tidak valid.');
    $f = optJobsDir() . '/' . $id . '.job.json';
    if (!is_file($f)) return null;
    for ($i = 0; $i < 3; $i++) { $j = json_decode((string) file_get_contents($f), true); if (is_array($j)) break; usleep(80000); }
    if (!is_array($j ?? null)) return ['state' => 'running', 'lines' => [], 'results' => [], 'total' => 0, 'done' => 0];
    if ($j['state'] === 'running' && (time() - (int) ($j['updated'] / 1000)) > 45) { $j['state'] = 'error'; $j['error'] = 'Proses berhenti tiba-tiba (tidak ada denyut > 45 detik).'; }
    return $j;
}

function optRunningJob(string $base): ?string
{
    foreach (glob(optJobsDir() . '/*.req.json') ?: [] as $rf) {
        $r = json_decode((string) file_get_contents($rf), true); if (!is_array($r) || ($r['base'] ?? '') !== $base) continue;
        $id = basename($rf, '.req.json'); $j = optReadJob($id);
        if ($j && $j['state'] === 'running') return $id;
    }
    return null;
}

/** Jalankan bridge sinkron (untuk daftar tur): kembalikan JSON hasilnya. */
function optBridgeSync(string $node, array $req): array
{
    $rf = optJobsDir() . '/' . bin2hex(random_bytes(8)) . '.list.json';
    file_put_contents($rf, json_encode($req, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    try {
        $proc = proc_open([$node, optBridge(), 'list', $rf], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($proc)) throw new RuntimeException('Node.js tidak dapat dijalankan.');
        stream_set_blocking($pipes[1], false); stream_set_blocking($pipes[2], false);
        $out = ''; $err = ''; $t0 = time();
        while (true) {
            $out .= (string) stream_get_contents($pipes[1]); $err .= (string) stream_get_contents($pipes[2]);
            $st = proc_get_status($proc); if (!$st['running']) { $out .= (string) stream_get_contents($pipes[1]); break; }
            if (time() - $t0 > 120) { proc_terminate($proc); throw new RuntimeException('Pembacaan daftar tur melebihi 120 detik.'); }
            usleep(60000);
        }
        fclose($pipes[1]); fclose($pipes[2]); proc_close($proc);
    } finally { @unlink($rf); }
    $j = json_decode($out, true);
    if (!is_array($j)) throw new RuntimeException('Jawaban mesin tidak terbaca. ' . trim(mb_substr($err ?: $out, 0, 300)));
    return $j;
}

function optSpawn(string $node, string $reqFile, string $jobFile, string $logFile): void
{
    $args = ['job', $reqFile, $jobFile];
    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = 'start "" /B "' . str_replace('/', '\\', $node) . '" "' . str_replace('/', '\\', optBridge()) . '" ' . implode(' ', array_map(static fn($a) => '"' . str_replace('/', '\\', $a) . '"', $args)) . ' > "' . str_replace('/', '\\', $logFile) . '" 2>&1';
        pclose(popen($cmd, 'r'));
    } else {
        $cmd = 'nohup ' . escapeshellarg($node) . ' ' . escapeshellarg(optBridge()) . ' ' . implode(' ', array_map('escapeshellarg', $args)) . ' > ' . escapeshellarg($logFile) . ' 2>&1 &';
        exec($cmd);
    }
}

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') cmsJsonResponse(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
    $payload = cmsJsonPayload();
    cmsAssertCsrf((string) ($payload['csrf'] ?? ''));
    if (function_exists('set_time_limit')) @set_time_limit(180);
    $project = activeProject();
    $root = rtrim(str_replace('\\', '/', (string) $project['path']), '/');
    $set = optSettings();
    $op = (string) ($payload['op'] ?? 'state');
    $isAdmin = cmsIsAdmin();

    if ($op === 'state') {
        $node = optNode($set); $eng = optEngine($set, $root);
        $out = ['success' => true, 'project' => ['id' => $project['id'], 'name' => $project['name'] ?? $project['id'], 'path' => $root], 'isAdmin' => $isAdmin,
            'node' => $node, 'engine' => $eng, 'ffmpeg' => false, 'tours' => [], 'running' => optRunningJob($root), 'settings' => $isAdmin ? $set : null, 'problem' => ''];
        if ($node['path'] === '') $out['problem'] = 'Node.js tidak ditemukan di server ini. Pasang Node.js (nodejs.org) atau isi jalur Node di Pengaturan mesin.';
        elseif ($eng['dir'] === '') $out['problem'] = 'Mesin optimasi (optimize-tour.js) tidak ditemukan. Isi folder mesin di Pengaturan, atau letakkan di akar project / folder semarang.';
        else {
            $dirs = array_values(array_map(static fn($t) => $t['dir'], shTours($root)));
            $r = optBridgeSync($node['path'], ['engine' => $eng['dir'], 'base' => $root, 'dirs' => $dirs, 'ffmpegDir' => $set['ffmpeg_dir']]);
            if (empty($r['ok'])) $out['problem'] = (string) ($r['error'] ?? 'Mesin gagal dibaca.');
            else { $out['tours'] = $r['tours']; $out['ffmpeg'] = (bool) $r['ffmpeg']; }
        }
        cmsJsonResponse($out);
    }
    if ($op === 'job') {
        $id = (string) ($payload['id'] ?? ''); $j = optReadJob($id);
        if (!$j) throw new RuntimeException('Pekerjaan tidak ditemukan.');
        $since = max(0, (int) ($payload['since'] ?? 0));
        $lines = array_slice((array) ($j['lines'] ?? []), $since);
        cmsJsonResponse(['success' => true, 'state' => $j['state'], 'action' => $j['action'] ?? '', 'total' => (int) ($j['total'] ?? 0), 'done' => (int) ($j['done'] ?? 0), 'current' => (string) ($j['current'] ?? ''),
            'lines' => $lines, 'next' => $since + count($lines), 'results' => $j['state'] === 'running' ? [] : (array) ($j['results'] ?? []), 'error' => (string) ($j['error'] ?? '')]);
    }

    cmsRequireAdmin('Optimasi loading (mengubah index.html dan skin.js tur)');
    switch ($op) {
        case 'settings':
            $s = optSaveSettings((array) ($payload['settings'] ?? []));
            cmsAudit('optimasi.pengaturan');
            cmsJsonResponse(['success' => true, 'message' => 'Pengaturan mesin disimpan.', 'settings' => $s]);
        case 'install_engine':
            $eng = optEngine($set, $root); if ($eng['dir'] === '') throw new RuntimeException('Mesin tidak ditemukan untuk disalin.');
            $dst = __DIR__ . '/tools/optimizer'; if (!is_dir($dst) && !mkdir($dst, 0775, true) && !is_dir($dst)) throw new RuntimeException('Folder tools/optimizer tidak dapat dibuat.');
            $n = 0; foreach (['optimize-tour.js', 'optimize-video.js'] as $f) if (is_file($eng['dir'] . '/' . $f)) { if (!copy($eng['dir'] . '/' . $f, $dst . '/' . $f)) throw new RuntimeException('Gagal menyalin ' . $f); $n++; }
            cmsAudit('optimasi.pasang-mesin', ['dari' => $eng['dir']]);
            cmsJsonResponse(['success' => true, 'message' => $n . ' berkas mesin disalin ke tools/optimizer (CMS kini mandiri dari folder sumber).']);
        case 'start':
            $action = (string) ($payload['action'] ?? ''); if (!in_array($action, OPT_ACTIONS, true)) throw new RuntimeException('Aksi tidak dikenal.');
            $node = optNode($set); if ($node['path'] === '') throw new RuntimeException('Node.js tidak ditemukan.');
            $eng = optEngine($set, $root); if ($eng['dir'] === '') throw new RuntimeException('Mesin optimasi tidak ditemukan.');
            if ($action === 'video' && !$eng['video']) throw new RuntimeException('optimize-video.js tidak ada di folder mesin.');
            if ($running = optRunningJob($root)) throw new RuntimeException('Masih ada pekerjaan berjalan untuk project ini (ID ' . $running . '). Tunggu atau hentikan dulu.');
            // folder dikirim sebagai nama relatif terhadap akar project; divalidasi ulang oleh PHP dan bridge
            $dirs = [];
            foreach ((array) ($payload['dirs'] ?? []) as $name) {
                $name = trim(str_replace('\\', '/', (string) $name), '/');
                if ($name === '' || preg_match('~(^|/)\.\.?(/|$)~', $name) || preg_match('/[\x00-\x1F:*?"<>|]/', $name)) throw new RuntimeException('Nama folder tidak valid: ' . $name);
                $abs = $root . '/' . $name; $real = realpath($abs);
                if ($real === false || !is_dir($real) || !str_starts_with(strtolower(str_replace('\\', '/', $real)) . '/', strtolower($root) . '/')) throw new RuntimeException('Folder di luar project: ' . $name);
                $dirs[] = str_replace('\\', '/', $real);
            }
            if (!$dirs) throw new RuntimeException('Pilih minimal satu lokasi.');
            if (count($dirs) > 300) throw new RuntimeException('Maksimal 300 lokasi per pekerjaan.');
            $opts = (array) ($payload['opts'] ?? []);
            $id = bin2hex(random_bytes(8)); $jd = optJobsDir();
            $req = ['id' => $id, 'engine' => $eng['dir'], 'base' => $root, 'dirs' => $dirs, 'action' => $action, 'opts' => ['sw' => ($opts['sw'] ?? true) !== false, 'autoSkip' => ($opts['autoSkip'] ?? true) !== false], 'ffmpegDir' => $set['ffmpeg_dir']];
            file_put_contents($jd . '/' . $id . '.req.json', json_encode($req, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            file_put_contents($jd . '/' . $id . '.job.json', json_encode(['id' => $id, 'state' => 'running', 'action' => $action, 'total' => count($dirs), 'done' => 0, 'lines' => [], 'results' => [], 'updated' => (int) (microtime(true) * 1000)]));
            optSpawn($node['path'], $jd . '/' . $id . '.req.json', $jd . '/' . $id . '.job.json', $jd . '/' . $id . '.log');
            cmsAudit('optimasi.mulai', ['aksi' => $action, 'project' => $project['id'], 'jumlah' => count($dirs)]);
            cmsJsonResponse(['success' => true, 'id' => $id, 'total' => count($dirs)]);
        case 'cancel':
            $id = (string) ($payload['id'] ?? ''); $j = optReadJob($id);
            if (!$j || $j['state'] !== 'running') throw new RuntimeException('Tidak ada pekerjaan berjalan dengan ID itu.');
            $pid = (int) ($j['pid'] ?? 0);
            if ($pid > 0) {
                if (PHP_OS_FAMILY === 'Windows') { $p = proc_open(['taskkill', '/PID', (string) $pid, '/T', '/F'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pp); if (is_resource($p)) { stream_get_contents($pp[1]); proc_close($p); } }
                else { @exec('kill -TERM ' . $pid); }
            }
            // hapus sisa berkas sementara ffmpeg (*.tmp.webm/mp4) yang dibuat sejak pekerjaan dimulai; video asli tidak disentuh
            $rq = json_decode((string) @file_get_contents(optJobsDir() . '/' . $id . '.req.json'), true); $cleaned = 0;
            if (($j['action'] ?? '') === 'video' && is_array($rq)) foreach ((array) ($rq['dirs'] ?? []) as $d) {
                foreach (['', '/media', '/videos', '/video'] as $sub) foreach (glob($d . $sub . '/*.tmp.{webm,mp4}', GLOB_BRACE) ?: [] as $tmp) if (is_file($tmp) && @filemtime($tmp) >= (int) (($j['started'] ?? 0) / 1000) - 5 && @unlink($tmp)) $cleaned++;
            }
            if ($cleaned) $j['lines'][] = 'Berkas sementara dibersihkan: ' . $cleaned;
            $f = optJobsDir() . '/' . $id . '.job.json'; $j['state'] = 'cancelled'; $j['lines'][] = 'Dihentikan oleh admin.'; $j['finished'] = (int) (microtime(true) * 1000); $j['updated'] = $j['finished'];
            file_put_contents($f, json_encode($j, JSON_UNESCAPED_UNICODE));
            cmsAudit('optimasi.batal', ['id' => $id]);
            cmsJsonResponse(['success' => true, 'message' => 'Pekerjaan dihentikan' . ($cleaned ? ' (sisa berkas sementara dibersihkan)' : '') . '. Lokasi yang sedang diproses mungkin setengah selesai: cek status lalu "Kembalikan" bila perlu.']);
        default: throw new RuntimeException('Operasi tidak dikenal: ' . $op);
    }
} catch (Throwable $e) {
    cmsJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
