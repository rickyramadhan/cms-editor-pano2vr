<?php
declare(strict_types=1);
/*
 * ai-hub.php — penghubung CMS ↔ AI Guide Hub (tab "AI Guide Hub") untuk SEMUA wilayah/project.
 *
 *  Satu lokasi = satu identitas (id folder tur). Hub memakai slug yang sama (huruf kecil, "_" → "-").
 *  Pengaturan per project disimpan di projects.json › aiHub:
 *      { path (folder ai-guide-hub), url (alamat web Hub), city (slug kota di Hub), cityName, php }
 *
 *  CMS tidak menulis ke database Hub secara langsung. CMS menjalankan `tools/cms-bridge.php` milik Hub (CLI, server yang sama),
 *  yang memakai fungsi asli Hub: daftar tour → impor pano → konten awal → build paket → deploy ke folder tur.
 *
 *  Langkah per lokasi:  register (daftar + konten awal)  →  sync (data CMS → knowledge Hub)  →  publish (build + pasang ke tur)
 *
 *  Dipanggil lewat api.php?action=ai-hub (POST JSON + csrf). Dapat di-include index.php (AI_HUB_INCLUDE_ONLY) untuk render awal.
 */
if (!defined('SMART_HUB_INCLUDE_ONLY')) define('SMART_HUB_INCLUDE_ONLY', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/project-manager.php';
require_once __DIR__ . '/smart-hub.php';
require_once __DIR__ . '/includes/pano-audit.php';   // auditTour(): kode yang sama dengan tab Audit

const AH_STEPS = ['register', 'sync', 'publish'];

/* ------------------------------ pengaturan ------------------------------ */
function ahSettings(array $project): array
{
    $s = (array) ($project['aiHub'] ?? []);
    $sk = shSettings($project);
    $path = rtrim(str_replace('\\', '/', (string) ($s['path'] ?? (cmsBaseDirectory() . '/ai-guide-hub'))), '/');
    return [
        'path' => $path,
        'url' => rtrim((string) ($s['url'] ?? '/ai-guide-hub'), '/') . '/',
        'city' => cmsCleanKey((string) ($s['city'] ?? '')) ?: $sk['wilayah'],
        'cityName' => trim((string) ($s['cityName'] ?? '')) ?: (string) ($project['name'] ?? shHumanize($sk['wilayah'])),
        'php' => trim((string) ($s['php'] ?? '')),
        'configured' => isset($project['aiHub']),
    ];
}

function ahSaveSettings(string $projectId, array $in): array
{
    $registry = projectRegistry();
    if (!isset($registry[$projectId])) throw new RuntimeException('Project tidak ditemukan.');
    $path = rtrim(str_replace('\\', '/', trim((string) ($in['path'] ?? ''))), '/');
    if ($path === '' || !is_file($path . '/tools/cms-bridge.php')) throw new RuntimeException('Folder AI Guide Hub tidak valid: tools/cms-bridge.php tidak ditemukan di ' . $path . '. Perbarui repo ai-guide-hub.');
    $url = trim((string) ($in['url'] ?? ''));
    if (!preg_match('~^(https?://[^\s"\'<>]+|/[^\s"\'<>]*)$~', $url)) throw new RuntimeException('Alamat web Hub harus diawali / atau http(s)://');
    $city = cmsCleanKey((string) ($in['city'] ?? ''));
    if ($city === '') throw new RuntimeException('Slug kota di Hub wajib diisi (huruf kecil, angka, - atau _), mis. jakarta.');
    $cityName = mb_substr(trim(strip_tags((string) ($in['cityName'] ?? ''))), 0, 80);
    if ($cityName === '') throw new RuntimeException('Nama kota di Hub wajib diisi.');
    $php = trim((string) ($in['php'] ?? ''));
    if ($php !== '' && !preg_match('~^[A-Za-z0-9 _:./\\\\()-]{1,200}$~', $php)) throw new RuntimeException('Jalur PHP tidak valid.');
    $registry[$projectId]['aiHub'] = ['path' => $path, 'url' => rtrim($url, '/') . '/', 'city' => $city, 'cityName' => $cityName, 'php' => $php];
    writeProjectRegistry($registry);
    return ahSettings($registry[$projectId] + ['id' => $projectId]);
}

function ahPhpBinary(array $st): string
{
    if ($st['php'] !== '') return $st['php'];
    if (PHP_SAPI === 'cli' && PHP_BINARY !== '') return PHP_BINARY;
    foreach ([dirname(cmsBaseDirectory()) . '/php/php.exe', dirname(cmsBaseDirectory()) . '/php/php'] as $c) if (is_file($c)) return $c;
    return 'php';   // Apache memuat PHP sebagai modul: PHP_BINARY menunjuk httpd, jadi jangan dipakai
}

/** Slug Hub dari id lokasi CMS — harus identik dengan slugify() milik Hub. */
function ahHubSlug(string $locationId): string
{
    $s = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($locationId))), '-');
    return $s !== '' ? $s : 'lokasi';
}

/* ------------------------------ jembatan ke Hub ------------------------------ */
function ahBridge(array $st, string $cmd, array $payload = [], int $timeout = 600): array
{
    $script = $st['path'] . '/tools/cms-bridge.php';
    if (!is_file($script)) throw new RuntimeException('AI Guide Hub belum terhubung: ' . $script . ' tidak ditemukan.');
    // stderr ke berkas sementara: pipa Windows tidak mendukung non-blocking, sehingga membaca dua pipa bisa saling mengunci.
    $errFile = tempnam(sys_get_temp_dir(), 'ahb');
    try {
        $proc = @proc_open([ahPhpBinary($st), $script, $cmd], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['file', (string) $errFile, 'w']], $pipes, $st['path'], null, ['bypass_shell' => true]);
        if (!is_resource($proc)) throw new RuntimeException('PHP tidak dapat dijalankan (' . ahPhpBinary($st) . '). Isi jalur PHP di pengaturan AI Guide Hub.');
        fwrite($pipes[0], json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        fclose($pipes[0]);
        $out = (string) stream_get_contents($pipes[1]);
        fclose($pipes[1]); proc_close($proc);
        $err = (string) @file_get_contents((string) $errFile);
    } finally { if ($errFile) @unlink($errFile); }
    $json = null;
    foreach (array_reverse(preg_split('/\R/', trim($out)) ?: []) as $line) { $line = trim($line); if ($line !== '' && $line[0] === '{') { $json = json_decode($line, true); break; } }
    if (!is_array($json)) throw new RuntimeException('Jawaban Hub tidak terbaca: ' . mb_substr(trim($err ?: $out), 0, 400));
    if (($json['ok'] ?? false) !== true) throw new RuntimeException((string) ($json['message'] ?? 'Hub menolak permintaan.'));
    return $json;
}

/* ------------------------------ data lokasi dari CMS ------------------------------ */
function ahDatabase(array $project): array
{
    $file = projectRuntimeDirectory($project) . '/global-database.js';
    if (!is_file($file)) return [];
    try { return cmsReadJsObject($file, 'GLOBAL_DATABASE'); } catch (Throwable) { return []; }
}

function ahContentFile(array $sk, string $location): string { return $sk['path'] . '/packs/' . $sk['wilayah'] . '/' . $location . '/content.json'; }

/** Nama + teks sambutan per bahasa (CMS) dan profil Smart Kit (REGION). */
function ahCmsData(array $project, array $sk, string $location, array $database): array
{
    $rec = (array) ($database[$location] ?? []);
    $names = []; $about = [];
    foreach ((array) ($rec['bahasa'] ?? []) as $code => $row) {
        $code = strtolower((string) $code);
        if (!preg_match('/^[a-z]{2,3}(-[a-z]{2})?$/', $code)) continue;
        if (trim((string) ($row['lokasi'] ?? '')) !== '') $names[$code] = trim((string) $row['lokasi']);
        if (trim((string) ($row['welcomeText'] ?? '')) !== '') $about[$code] = trim((string) $row['welcomeText']);
    }
    $f = ahContentFile($sk, $location);
    $c = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
    $region = is_array($c['REGION'] ?? null) ? $c['REGION'] : [];
    $profile = trim((string) ($region['about']['text'] ?? ''));
    if ($profile !== '') {
        $id = $about['id'] ?? '';
        if ($id === '') $about['id'] = $profile;
        elseif (!str_contains($id, mb_substr($profile, 0, 40))) $about['id'] = $id . "\n\n" . $profile;
    }
    $name = $names['id'] ?? (string) ($region['name'] ?? '') ?: ($names ? reset($names) : shHumanize($location));
    $center = is_array($region['center'] ?? null) ? $region['center'] : [];
    return ['name' => $name, 'names' => $names, 'about' => $about, 'type' => (string) ($region['type'] ?? ''),
        'lat' => $center['lat'] ?? null, 'lng' => $center['lng'] ?? null];
}

/**
 * Isi pano.xml persis seperti yang dideteksi tab Audit (panorama + userdata, hotspot beserta URL/target, terjemahan),
 * dikirim ke Hub agar tidak ada yang tertinggal. Memakai auditTour() yang sama dengan Audit.
 */
function ahPanoPayload(array $project, array $tour): array
{
    $audit = auditTour((string) $project['path'], ['path' => $tour['path']]);
    $xml = (string) @file_get_contents($tour['dir'] . '/pano.xml');
    $nodes = [];
    if (preg_match_all('/<panorama\b[^>]*\bid="([^"]+)"[^>]*>.*?<\/panorama>/is', $xml, $panos, PREG_SET_ORDER)) {
        foreach ($panos as $p) {
            $ud = preg_match('/<userdata\s+([^>]*)\/>/i', $p[0], $m) ? parseXmlAttributes($m[1]) : [];
            $nodes[] = ['id' => $p[1], 'customNodeId' => trim((string) ($ud['customnodeid'] ?? '')), 'title' => trim((string) ($ud['title'] ?? ''))];
        }
    }
    $langs = (array) $audit['languages']; $idx = (array) $audit['displayLanguageIndexes'];
    $codes = [];
    foreach ($idx as $i) $codes[$i] = strtolower(substr((string) $langs[$i], 0, 2) === 'zh' ? 'zh' : (string) $langs[$i]);
    $codes = array_map(static fn($c) => preg_match('/^[a-z]{2,3}-[a-z]{2}$/', $c) ? substr($c, 0, 2) : $c, $codes);
    $translations = [];
    foreach ((array) $audit['translations'] as $t) {
        $vals = [];
        foreach ($codes as $i => $code) { $v = trim((string) ($t['values'][$i] ?? '')); if ($v !== '') $vals[$code] = $v; }
        $translations[] = ['source' => $t['source'], 'values' => $vals];
    }
    return ['nodes' => $nodes, 'hotspots' => $audit['hotspots'], 'languages' => array_values($codes), 'translations' => $translations, 'stats' => $audit['stats']];
}

/** Catat tautan ke Hub di profil Smart Kit (REGION.aiGuide) agar tur, dasbor, dan pengembang front-end tahu pasangannya. */
function ahWriteRegionLink(array $sk, string $location, array $st, string $hubSlug): bool
{
    $f = ahContentFile($sk, $location);
    if (!is_file($f)) return false;
    $c = json_decode((string) file_get_contents($f), true);
    if (!is_array($c)) return false;
    $cur = is_array($c['REGION']['aiGuide'] ?? null) ? $c['REGION']['aiGuide'] : [];
    $new = array_merge($cur, ['hubSlug' => $hubSlug, 'hubCity' => $st['city']]);
    if ($new === $cur) return true;
    $c['REGION']['aiGuide'] = $new;
    shWriteJson($f, $c);
    return true;
}

/* ------------------------------ status ------------------------------ */
function ahState(array $project): array
{
    $st = ahSettings($project);
    $sk = shSettings($project);
    $tours = shTours((string) $project['path']);
    $hubOk = is_file($st['path'] . '/tools/cms-bridge.php');
    $state = ['project' => ['id' => $project['id'], 'name' => $project['name'] ?? $project['id']], 'settings' => $st,
        'smartkit' => ['wilayah' => $sk['wilayah'], 'enabled' => is_dir($sk['path'] . '/packs/' . $sk['wilayah'])],
        'cmsRuntime' => projectAssetsValid(projectAssetsDirectory($project)) || projectAssetsValid(projectRuntimeDirectory($project)),
        'isAdmin' => cmsIsAdmin(), 'hubOk' => $hubOk, 'hubError' => '', 'cities' => [], 'runtime' => false, 'locations' => [],
        'hubWeb' => $st['url'], 'php' => ahPhpBinary($st)];
    $smart = is_file($sk['path'] . '/hub-catalog.json') ? shStatus($sk, $tours) : [];
    $smartBy = []; foreach ($smart as $r) $smartBy[$r['id']] = $r;
    $db = ahDatabase($project);
    $hub = ['tours' => []];
    if ($hubOk && $tours) {
        try {
            $slugs = array_map('ahHubSlug', array_keys($tours));
            $hub = ahBridge($st, 'statuses', ['slugs' => $slugs], 120);
            $state['cities'] = $hub['cities'] ?? []; $state['runtime'] = (bool) ($hub['runtime'] ?? false);
        } catch (Throwable $e) { $state['hubError'] = $e->getMessage(); }
    } elseif (!$hubOk) $state['hubError'] = 'tools/cms-bridge.php tidak ditemukan di ' . $st['path'] . '. Perbarui repo ai-guide-hub atau ubah folder Hub.';
    foreach ($tours as $id => $t) {
        $slug = ahHubSlug($id);
        $h = (array) ($hub['tours'][$slug] ?? ['registered' => false]);
        $sm = $smartBy[$id] ?? null;
        $hasText = false;
        foreach ((array) ($db[$id]['bahasa'] ?? []) as $row) if (trim((string) ($row['welcomeText'] ?? '')) !== '') { $hasText = true; break; }
        $row = [
            'id' => $id, 'slug' => $slug, 'path' => $t['path'], 'name' => (string) ($sm['name'] ?? shHumanize($id)),
            'cms' => ['hasText' => $hasText, 'langs' => array_keys((array) ($db[$id]['bahasa'] ?? []))],
            'smart' => $sm ? ['pack' => (bool) $sm['pack']['exists'], 'applied' => (bool) $sm['applied']] : null,
            'hub' => $h,
            'hubUrl' => !empty($h['id']) ? $st['url'] . '?a=view&id=' . (int) $h['id'] : '',
        ];
        $row['stage'] = ahStage($row);
        $state['locations'][] = $row;
    }
    $state['summary'] = ['total' => count($state['locations'])] + array_count_values(array_column($state['locations'], 'stage'));
    return $state;
}

/** Tahap kesiapan: none → registered → synced → built → live. */
function ahStage(array $r): string
{
    $h = $r['hub'];
    if (empty($h['registered'])) return 'none';
    if (!empty($h['deployed'])) return 'live';
    if (!empty($h['packageBuilt'])) return 'built';
    if (!empty($h['cmsSyncedAt'])) return 'synced';
    return 'registered';
}

/* ------------------------------ aksi ------------------------------ */
function ahRunStep(array $project, array $st, array $sk, array $tour, string $step, array $database): array
{
    $id = (string) $tour['id']; $slug = ahHubSlug($id);
    switch ($step) {
        case 'register':
            $cms = ahCmsData($project, $sk, $id, $database);
            $r = ahBridge($st, 'register', ['slug' => $slug, 'name' => $cms['name'], 'cityName' => $st['cityName'], 'citySlug' => $st['city'], 'category' => '', 'exportPath' => $tour['dir']]);
            ahWriteRegionLink($sk, $id, $st, $slug);
            return ['message' => ($r['created'] ? 'Terdaftar di Hub' : 'Sudah terdaftar, data tur diperbarui') . ($r['steps'] ? ' · ' . implode(', ', $r['steps']) : '') . ($r['warnings'] ? ' · ⚠ ' . implode('; ', $r['warnings']) : '')];
        case 'sync':
            $cms = ahCmsData($project, $sk, $id, $database);
            $r = ahBridge($st, 'sync', ['slug' => $slug, 'cms' => $cms, 'pano' => ahPanoPayload($project, $tour)]);
            $p = $r['parity'] ?? null;
            $bits = [];
            if (!empty($r['text'])) $bits[] = 'teks ' . implode('/', $r['languages']);
            if ($p) {
                $bits[] = $p['nodes']['hub'] . '/' . $p['nodes']['cms'] . ' node';
                $bits[] = $p['hotspots']['hub'] . '/' . $p['hotspots']['cms'] . ' hotspot';
                $bits[] = $p['translations']['inKnowledge'] . '/' . $p['translations']['cms'] . ' terjemahan';
                if ($p['destinations']) $bits[] = $p['destinations'] . ' destinasi';
                if ($p['reimported']) $bits[] = 'pano diimpor ulang';
            }
            return ['message' => 'Data tersinkron: ' . implode(' · ', $bits) . (($p && !$p['complete']) ? ' ⚠ belum lengkap' : '')];
        case 'publish':
            // Hub menyisipkan blok <!-- GOVIRTUAL AI --> ke index.html tur; cadangkan dulu seperti operasi Smart Kit lainnya.
            if (is_file($tour['dir'] . '/index.html')) shBackup($tour['dir'], 'index.html');
            $r = ahBridge($st, 'publish', ['slug' => $slug, 'refresh' => false], 900);
            return ['message' => implode(' · ', $r['steps'])];
    }
    throw new RuntimeException('Langkah tidak dikenal: ' . $step);
}

/* ------------------------------ pengendali JSON ------------------------------ */
if (!defined('AI_HUB_INCLUDE_ONLY')) {
    try {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') cmsJsonResponse(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
        $payload = cmsJsonPayload();
        cmsAssertCsrf((string) ($payload['csrf'] ?? ''));
        if (function_exists('set_time_limit')) @set_time_limit(1800);
        $project = activeProject();
        $op = (string) ($payload['op'] ?? 'state');
        if ($op === 'state') cmsJsonResponse(['success' => true, 'state' => ahState($project)]);
        cmsRequireAdmin('Pengaturan dan operasi AI Guide Hub');
        if ($op === 'save_settings') {
            ahSaveSettings((string) $project['id'], (array) ($payload['settings'] ?? []));
            cmsAudit('aihub.pengaturan', ['project' => $project['id']]);
            $project = activeProject();
            cmsJsonResponse(['success' => true, 'message' => 'Pengaturan AI Guide Hub disimpan.', 'state' => ahState($project)]);
        }
        if ($op !== 'run') throw new RuntimeException('Operasi tidak dikenal: ' . $op);
        $steps = array_values(array_intersect(AH_STEPS, array_map('strval', (array) ($payload['steps'] ?? []))));
        if (!$steps) throw new RuntimeException('Pilih minimal satu langkah.');
        $st = ahSettings($project); $sk = shSettings($project);
        $tours = shTours((string) $project['path']);
        $picked = [];
        foreach ((array) ($payload['locations'] ?? []) as $id) { $id = cmsCleanKey((string) $id); if (!isset($tours[$id])) throw new RuntimeException('Lokasi tidak ditemukan: ' . $id); $picked[$id] = $tours[$id]; }
        if (!$picked) throw new RuntimeException('Pilih minimal satu lokasi.');
        $database = ahDatabase($project);
        $results = [];
        foreach ($picked as $id => $tour) {
            $log = []; $ok = true;
            foreach ($steps as $step) {
                try { $r = ahRunStep($project, $st, $sk, $tour, $step, $database); $log[] = ['step' => $step, 'ok' => true, 'message' => $r['message']]; }
                catch (Throwable $e) { $log[] = ['step' => $step, 'ok' => false, 'message' => $e->getMessage()]; $ok = false; break; }   // langkah berikutnya bergantung pada sebelumnya
            }
            cmsAudit('aihub.jalankan', ['pack' => $sk['wilayah'] . '/' . $id, 'steps' => implode(',', $steps), 'ok' => $ok]);
            $results[] = ['id' => $id, 'ok' => $ok, 'log' => $log];
        }
        $okN = count(array_filter($results, static fn($r) => $r['ok']));
        cmsJsonResponse(['success' => $okN > 0, 'message' => $okN . ' dari ' . count($results) . ' lokasi berhasil.', 'results' => $results, 'state' => ahState($project)]);
    } catch (Throwable $e) {
        cmsJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}
