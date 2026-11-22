<?php
declare(strict_types=1);

/**
 * Pustaka integrasi VT Inspector untuk CMS (tab Inspector, lencana Smart Hub, alat CLI).
 * Sengaja TIDAK bergantung pada smart-hub.php agar boleh di-include dari mana saja (smart-hub.php memakainya untuk lencana).
 *
 *  - Laporan Inspector dibaca langsung dari <inspector>/data/regions/<wilayah>/tours/<slug>.json (hanya baca).
 *  - Penanda "basi" ditulis di folder data CMS (data/inspector-stale.json), TIDAK pernah ke folder data Inspector.
 *  - Pengaturan global di cms-settings.json › inspector { path, node, url }.
 *  - Scan statis memanggil `node src/cli.mjs scan <wilayah> [slug...]` lewat proc_open (argumen berupa array, tanpa shell).
 */
require_once __DIR__ . '/bootstrap.php';

const IV_SETTINGS_FILE = __DIR__ . '/../cms-settings.json';
const IV_DEFAULT_PORT = 4780;

function ivNorm(string $p): string { return rtrim(str_replace('\\', '/', $p), '/'); }

function ivRawSettings(): array
{
    $all = is_file(IV_SETTINGS_FILE) ? json_decode((string) file_get_contents(IV_SETTINGS_FILE), true) : null;
    return is_array($all) && is_array($all['inspector'] ?? null) ? $all['inspector'] : [];
}

/** Folder Inspector: setting inspector.path, lalu sibling ../VT-Inspector relatif htdocs, lalu C:/xampp/htdocs/VT-Inspector. */
function ivSettings(): array
{
    $raw = ivRawSettings();
    $path = ivNorm((string) ($raw['path'] ?? ''));
    $candidates = array_values(array_filter([$path, dirname(__DIR__, 2) . '/VT-Inspector', 'C:/xampp/htdocs/VT-Inspector']));
    $found = '';
    foreach ($candidates as $c) { $c = ivNorm($c); if (is_file($c . '/src/cli.mjs')) { $found = $c; break; } }
    $port = IV_DEFAULT_PORT;
    if ($found !== '' && is_file($found . '/config/inspector.json')) {
        $cfg = json_decode((string) file_get_contents($found . '/config/inspector.json'), true);
        if (is_array($cfg) && (int) ($cfg['port'] ?? 0) > 0) $port = (int) $cfg['port'];
    }
    $url = trim((string) ($raw['url'] ?? ''));
    if (!ivValidUrl($url)) $url = 'http://127.0.0.1:' . $port;
    return ['path' => $found !== '' ? $found : ($path !== '' ? $path : ivNorm(dirname(__DIR__, 2) . '/VT-Inspector')), 'pathOk' => $found !== '', 'configuredPath' => $path,
        'configuredNode' => (string) ($raw['node'] ?? ''), 'node' => ivFindNode((string) ($raw['node'] ?? '')), 'url' => rtrim($url, '/'), 'configuredUrl' => trim((string) ($raw['url'] ?? ''))];
}

function ivValidUrl(string $u): bool { return (bool) preg_match('~^http://(127\.0\.0\.1|localhost)(:\d{2,5})?/?$~', $u); }

/** node.exe: setting inspector.node, PATH, lalu lokasi umum. Mengembalikan '' bila tidak ada. */
function ivFindNode(string $configured): string
{
    $try = [];
    if ($configured !== '') $try[] = $configured;
    foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $dir) {
        $dir = trim($dir, " \t\"");
        if ($dir === '') continue;
        foreach (['node.exe', 'node'] as $n) $try[] = $dir . '/' . $n;
    }
    foreach (['C:/Program Files/nodejs/node.exe', 'C:/Program Files (x86)/nodejs/node.exe'] as $c) $try[] = $c;
    foreach ($try as $t) { $t = str_replace('\\', '/', $t); if (is_file($t)) return $t; }
    return '';
}

function ivSaveSettings(array $in): array
{
    $path = ivNorm(trim((string) ($in['path'] ?? '')));
    $node = str_replace('\\', '/', trim((string) ($in['node'] ?? '')));
    $url = trim((string) ($in['url'] ?? ''));
    if ($path !== '' && !is_file($path . '/src/cli.mjs')) throw new RuntimeException('Folder VT Inspector tidak valid (src/cli.mjs tidak ditemukan di ' . $path . ').');
    if ($node !== '' && (!is_file($node) || !preg_match('~(^|/)node(\.exe)?$~i', $node))) throw new RuntimeException('Jalur node tidak valid (harus berkas node.exe yang ada).');
    if ($url !== '' && !ivValidUrl($url)) throw new RuntimeException('Alamat server Inspector harus http://127.0.0.1:PORT atau http://localhost:PORT.');
    $all = is_file(IV_SETTINGS_FILE) ? json_decode((string) file_get_contents(IV_SETTINGS_FILE), true) : [];
    $all = is_array($all) ? $all : [];
    $all['inspector'] = array_filter(['path' => $path, 'node' => $node, 'url' => $url], static fn($v) => $v !== '');
    if (!$all['inspector']) unset($all['inspector']);
    $json = json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    if (file_put_contents(IV_SETTINGS_FILE, $json . PHP_EOL, LOCK_EX) === false) throw new RuntimeException('Pengaturan tidak dapat disimpan.');
    return ivSettings();
}

/** Cek singkat server Inspector (GET /api/status, batas waktu 1 detik). */
function ivProbe(array $s): array
{
    $ctx = stream_context_create(['http' => ['timeout' => 1.0, 'ignore_errors' => true]]);
    $body = @file_get_contents($s['url'] . '/api/status', false, $ctx);
    $j = is_string($body) ? json_decode($body, true) : null;
    return ['up' => is_array($j) && ($j['app'] ?? '') === 'VT Inspector', 'version' => is_array($j) ? (string) ($j['version'] ?? '') : ''];
}

/** Berkas laporan satu tur; jalur dipastikan tetap di dalam <inspector>/data/regions. */
function ivReportFile(array $s, string $project, string $slug): ?string
{
    $project = cmsCleanKey($project); $slug = cmsCleanKey($slug);
    if ($project === '' || $slug === '' || !$s['pathOk']) return null;
    $file = $s['path'] . '/data/regions/' . $project . '/tours/' . $slug . '.json';
    if (!is_file($file)) return null;
    $real = str_replace('\\', '/', (string) realpath($file)); $root = str_replace('\\', '/', (string) realpath($s['path'] . '/data/regions'));
    return ($real !== '' && $root !== '' && str_starts_with($real, $root . '/')) ? $file : null;
}

function ivReadReport(array $s, string $project, string $slug): ?array
{
    $f = ivReportFile($s, $project, $slug);
    $r = $f ? json_decode((string) @file_get_contents($f), true) : null;
    return is_array($r) ? $r : null;
}

/* ------------------------------ penanda basi ------------------------------ */
function ivStaleFile(): string { return cmsDataDir() . '/inspector-stale.json'; }
function ivStaleMap(): array { $f = ivStaleFile(); $m = is_file($f) ? json_decode((string) file_get_contents($f), true) : null; return is_array($m) ? $m : []; }

/** Tandai laporan Inspector tur-tur ini 'basi' (konten/penerapan berubah sejak scan terakhir). Tidak pernah melempar. */
function ivMarkStale(string $project, array $slugs, string $reason): void
{
    try {
        $project = cmsCleanKey($project); if ($project === '') return;
        $map = ivStaleMap(); $now = gmdate('Y-m-d\TH:i:s\Z');
        foreach ($slugs as $slug) { $slug = cmsCleanKey((string) $slug); if ($slug !== '') $map[$project . '/' . $slug] = ['at' => $now, 'reason' => mb_substr($reason, 0, 80)]; }
        @file_put_contents(ivStaleFile(), json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    } catch (Throwable $e) { /* penanda bersifat tambahan; jangan menggagalkan aksi utama */ }
}

function ivIsStale(array $map, string $project, string $slug, ?array $report): bool
{
    $m = $map[$project . '/' . $slug] ?? null;
    if (!is_array($m)) return false;
    $scan = $report ? strtotime((string) ($report['static']['at'] ?? '')) : false;
    return $scan === false || strtotime((string) ($m['at'] ?? '')) > $scan;
}

/* ------------------------------ ringkasan ------------------------------ */
/** Ringkasan satu laporan; gerbang rilis memakai report.gate bila ada, kalau tidak dihitung dengan aturan UPGRADE-V3 (skor>=85, tanpa blocker, kesiapan CMS>=95). */
function ivSummarize(?array $r, ?int $cmsScore): ?array
{
    if (!$r) return null;
    $counts = array_merge(['blocker' => 0, 'major' => 0, 'minor' => 0, 'info' => 0], (array) ($r['counts'] ?? []));
    $score = (int) ($r['score'] ?? 0);
    $gate = is_array($r['gate'] ?? null) ? $r['gate'] : null;
    $reasons = $gate ? array_values(array_map('strval', (array) ($gate['reasons'] ?? []))) : [];
    if ($gate && array_key_exists('ready', $gate)) $ready = (bool) $gate['ready'];
    else {
        $ready = $score >= 85 && (int) $counts['blocker'] === 0 && ($cmsScore ?? 0) >= 95;
        if (!$ready) { if ($score < 85) $reasons[] = 'Skor Inspector ' . $score . ' (<85)'; if ($counts['blocker'] > 0) $reasons[] = $counts['blocker'] . ' blocker'; if (($cmsScore ?? 0) < 95) $reasons[] = 'Kesiapan CMS ' . ($cmsScore ?? 0) . '% (<95)'; }
    }
    $profile = (string) ($r['static']['info']['profile'] ?? '');
    return ['score' => $score, 'status' => (string) ($r['status'] ?? ''), 'counts' => $counts, 'at' => (string) ($r['static']['at'] ?? ''), 'ready' => $ready, 'reasons' => $reasons,
        'kit' => stripos($profile, 'v3') !== false || stripos($profile, 'smartkit') !== false ? 'v3' : ($profile !== '' ? 'v1' : ''), 'layers' => array_values(array_filter(['static', 'robot', 'links', 'ai'], static fn($l) => !empty($r[$l])))];
}

/** Lencana ringan untuk Smart Hub/CLI: slug => {score,status,ready,stale,at}. Hanya membaca JSON kecil per tur. */
function ivBadges(string $project, array $slugs, array $cmsScores = []): array
{
    $s = ivSettings(); if (!$s['pathOk']) return [];
    $map = ivStaleMap(); $out = [];
    foreach ($slugs as $slug) {
        $r = ivReadReport($s, $project, (string) $slug); if (!$r) continue;
        $sum = ivSummarize($r, $cmsScores[$slug] ?? null);
        $out[$slug] = ['score' => $sum['score'], 'status' => $sum['status'], 'ready' => $sum['ready'], 'stale' => ivIsStale($map, $project, (string) $slug, $r), 'at' => $sum['at']];
    }
    return $out;
}

/** Temuan semua lapisan, seragam untuk tampilan. */
function ivFindings(array $r): array
{
    $out = [];
    foreach (['static', 'robot', 'links', 'ai'] as $layer) foreach ((array) ($r[$layer]['findings'] ?? []) as $f) {
        if (!is_array($f) || !empty($f['dismissed'])) continue;
        $link = is_array($f['link'] ?? null) ? ['tab' => preg_replace('/[^a-z0-9_-]/i', '', (string) ($f['link']['tab'] ?? '')), 'tour' => cmsCleanKey((string) ($f['link']['tour'] ?? ''))] : null;
        $out[] = ['layer' => (string) ($f['layer'] ?? $layer), 'category' => (string) ($f['category'] ?? 'Lainnya'), 'severity' => (string) ($f['severity'] ?? 'info'), 'title' => (string) ($f['title'] ?? ''),
            'detail' => (string) ($f['detail'] ?? ''), 'items' => array_slice(array_map(static fn($i) => is_scalar($i) ? (string) $i : json_encode($i, JSON_UNESCAPED_UNICODE), (array) ($f['items'] ?? [])), 0, 30), 'fix' => (string) ($f['fix'] ?? ''), 'link' => $link && $link['tab'] !== '' ? $link : null];
    }
    $rank = ['blocker' => 0, 'major' => 1, 'minor' => 2, 'info' => 3];
    usort($out, static fn($a, $b) => ($rank[$a['severity']] ?? 9) <=> ($rank[$b['severity']] ?? 9));
    return $out;
}

/* ------------------------------ scan statis ------------------------------ */
/**
 * Jalankan `node src/cli.mjs scan <wilayah> [slug...]`. $slugs HARUS sudah divalidasi pemanggil terhadap daftar tur yang dikenal.
 * Argumen dikirim sebagai array (tanpa shell), jadi tidak ada interpolasi perintah dari input.
 */
function ivRunScan(string $project, array $slugs, int $timeout = 240): array
{
    $s = ivSettings();
    if (!$s['pathOk']) throw new RuntimeException('Folder VT Inspector tidak ditemukan. Atur jalurnya di bagian Pengaturan tab ini.');
    if ($s['node'] === '') throw new RuntimeException('node.exe tidak ditemukan. Pasang Node.js atau isi jalur node di Pengaturan tab ini.');
    $project = cmsCleanKey($project);
    if ($project === '') throw new RuntimeException('Project tidak valid.');
    $cmd = [$s['node'], 'src/cli.mjs', 'scan', $project];
    foreach ($slugs as $slug) { $slug = cmsCleanKey((string) $slug); if ($slug !== '') $cmd[] = $slug; }
    $proc = @proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $s['path'], null, ['bypass_shell' => true]);
    if (!is_resource($proc)) throw new RuntimeException('Proses node tidak dapat dijalankan.');
    fclose($pipes[0]); stream_set_blocking($pipes[1], false); stream_set_blocking($pipes[2], false);
    $out = ''; $err = ''; $t0 = time(); $timedOut = false;
    while (true) {
        $out .= (string) stream_get_contents($pipes[1]); $err .= (string) stream_get_contents($pipes[2]);
        $st = proc_get_status($proc);
        if (!$st['running']) break;
        if (time() - $t0 > $timeout) { $timedOut = true; proc_terminate($proc); break; }
        usleep(120000);
    }
    $out .= (string) stream_get_contents($pipes[1]); $err .= (string) stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    $code = $timedOut ? -1 : (int) ($st['exitcode'] ?? proc_close($proc));
    if ($timedOut) proc_close($proc);
    $log = trim(preg_replace('/\x1b\[[0-9;]*[A-Za-z]|\r/', "\n", $out . "\n" . $err));
    $log = trim(preg_replace("/\n{2,}/", "\n", $log));
    // jangan pernah meneruskan baris yang tampak seperti rahasia (.env)
    $log = implode("\n", array_filter(explode("\n", $log), static fn($l) => !preg_match('/(api[_-]?key|secret|token|password)\s*[=:]/i', $l)));
    return ['ok' => !$timedOut && $code === 0, 'timedOut' => $timedOut, 'code' => $code, 'log' => mb_substr($log, -2500), 'seconds' => time() - $t0];
}
