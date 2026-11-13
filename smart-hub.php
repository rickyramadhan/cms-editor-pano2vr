<?php
declare(strict_types=1);
/*
 * smart-hub.php — backend tab "Smart Hub" (integrasi Smart Kit) untuk SEMUA wilayah/project, bukan khusus satu daerah.
 *
 *  - Satu project CMS = satu wilayah. Pengaturan Smart Kit per project disimpan di projects.json › smartkit:
 *      { wilayah, path (folder smart-kit di server), url (alamat publik smart-kit), python }
 *  - Konfigurasi Hub wilayah: <smart-kit>/packs/<wilayah>/_hub.json  (aksi tombol, menu, feed data, integrasi API, tampilan HUD)
 *  - Paket per lokasi:        <smart-kit>/packs/<wilayah>/<slug>/   (scenes.js, pins.js, content.json — dibuat "Bangun paket")
 *  - "Terapkan" menyisipkan satu tag <script type="module" src=".../boot.js?pack=wilayah/slug"> ke index.html lokasi (dengan cadangan .skin-backups).
 *
 * Dipanggil lewat api.php?action=smart-hub (POST JSON + csrf). Dapat di-include oleh index.php untuk render awal (SMART_HUB_INCLUDE_ONLY).
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/project-manager.php';

const SH_ID_RE = '~^[a-z0-9][a-z0-9_-]{0,40}$~';

function shSettings(array $project): array
{
    $s = (array) ($project['smartkit'] ?? []);
    $wil = cmsCleanKey((string) ($s['wilayah'] ?? '')) ?: cmsCleanKey((string) preg_replace('/-output$/', '', (string) $project['id']));
    return [
        'wilayah' => $wil,
        'path' => rtrim(str_replace('\\', '/', (string) ($s['path'] ?? (cmsBaseDirectory() . '/smart-kit'))), '/'),
        'url' => rtrim((string) ($s['url'] ?? '/smart-kit'), '/') . '/',
        'python' => (string) ($s['python'] ?? 'python'),
    ];
}

function shSaveSettings(string $projectId, array $in): array
{
    $registry = projectRegistry();
    if (!isset($registry[$projectId])) throw new RuntimeException('Project tidak ditemukan.');
    $wil = cmsCleanKey((string) ($in['wilayah'] ?? ''));
    if ($wil === '') throw new RuntimeException('ID wilayah wajib diisi (huruf kecil, angka, - atau _), mis. badung, jakarta, kota-baru.');
    $path = rtrim(str_replace('\\', '/', trim((string) ($in['path'] ?? ''))), '/');
    if ($path === '' || !is_file($path . '/boot.js') || !is_file($path . '/hub-catalog.json')) throw new RuntimeException('Folder Smart Kit tidak valid (boot.js / hub-catalog.json tidak ditemukan di ' . $path . ').');
    $url = trim((string) ($in['url'] ?? ''));
    if (!preg_match('~^(https?://[^\s"\'<>]+|/[^\s"\'<>]*)$~', $url)) throw new RuntimeException('Alamat Smart Kit harus diawali / atau http(s)://');
    $python = trim((string) ($in['python'] ?? 'python'));
    if (!preg_match('~^[A-Za-z0-9 _:./\\\\()-]{1,200}$~', $python)) throw new RuntimeException('Jalur Python tidak valid.');
    $registry[$projectId]['smartkit'] = ['wilayah' => $wil, 'path' => $path, 'url' => rtrim($url, '/') . '/', 'python' => $python];
    writeProjectRegistry($registry);
    return shSettings($registry[$projectId] + ['id' => $projectId]);
}

function shCatalog(array $st): array
{
    $c = json_decode((string) @file_get_contents($st['path'] . '/hub-catalog.json'), true);
    if (!is_array($c)) throw new RuntimeException('hub-catalog.json Smart Kit tidak terbaca di ' . $st['path']);
    return $c;
}

/** Nama ikon yang tersedia di Smart Kit (dibaca dari core.js agar selalu sinkron). */
function shIcons(array $st): array
{
    $core = (string) @file_get_contents($st['path'] . '/core.js');
    if (!preg_match('~const P = \{(.*?)\n\};~s', $core, $m)) return ['pin', 'info', 'grid', 'compass', 'sparkle'];
    preg_match_all('~^\s{2}([a-zA-Z0-9]+):~m', $m[1], $n);
    return array_values(array_unique($n[1]));
}

function shHubFile(array $st): string { return $st['path'] . '/packs/' . $st['wilayah'] . '/_hub.json'; }

function shReadHub(array $st): array
{
    $f = shHubFile($st);
    $h = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
    $h = is_array($h) ? $h : [];
    foreach (['actions', 'menus', 'feeds', 'api', 'ui'] as $k) $h[$k] = (array) ($h[$k] ?? []);
    return $h;
}

/** Lokasi tur di project: folder berisi pano.xml (kedalaman ≤ 4), id = nama folder seperti CMS. */
function shTours(string $root): array
{
    $out = []; $queue = [[$root, '', 0]];
    while ($queue) {
        [$dir, $rel, $depth] = array_shift($queue);
        if ($rel !== '' && is_file($dir . '/pano.xml')) { $id = cmsCleanKey(basename($dir)); if ($id !== '') $out[$id] = ['id' => $id, 'path' => $rel, 'dir' => str_replace('\\', '/', $dir)]; continue; }
        if ($depth >= 4) continue;
        foreach (new DirectoryIterator($dir) as $c) {
            $n = strtolower($c->getFilename());
            if ($c->isDot() || !$c->isDir() || $n[0] === '.' || preg_match('/^assets(?:-\d+|-old(?:-\d+)?)?$/', $n) || in_array($n, ['cms', 'tiles', 'images', 'video', 'audio', '3rdparty', 'media', 'smart-kit'], true)) continue;
            $queue[] = [$c->getPathname(), ltrim($rel . '/' . $c->getFilename(), '/'), $depth + 1];
        }
    }
    ksort($out, SORT_NATURAL | SORT_FLAG_CASE);
    return $out;
}

function shWebUrl(string $absDir): string
{
    $base = strtolower(rtrim(cmsBaseDirectory(), '/'));
    $d = str_replace('\\', '/', $absDir);
    return str_starts_with(strtolower($d) . '/', $base . '/') ? '/' . implode('/', array_map('rawurlencode', explode('/', trim(substr($d, strlen($base)), '/')))) . '/' : '';
}

function shStatus(array $st, array $tours): array
{
    $rows = [];
    foreach ($tours as $id => $t) {
        $packDir = $st['path'] . '/packs/' . $st['wilayah'] . '/' . $id;
        $content = is_file($packDir . '/content.json') ? json_decode((string) file_get_contents($packDir . '/content.json'), true) : null;
        $scenes = 0;
        if (is_file($packDir . '/scenes.js') && preg_match('~"order":\s*\[(.*?)\]~s', (string) file_get_contents($packDir . '/scenes.js'), $m)) $scenes = substr_count($m[1], '"') / 2;
        $html = is_file($t['dir'] . '/index.html') ? (string) file_get_contents($t['dir'] . '/index.html') : '';
        $applied = preg_match('~<script[^>]*src="([^"]*smart-kit/boot\.js[^"]*)"~i', $html, $am) ? $am[1] : '';
        $runtimeReady = (bool) preg_match('~<script[^>]+src=["\'][^"\']*main-loader\.js(?:\?[^"\']*)?["\']~i', $html)
            && (bool) preg_match('~<link[^>]+href=["\'][^"\']*custom\.css(?:\?[^"\']*)?["\']~i', $html);
        $count = static fn($k) => is_array($content[$k] ?? null) ? count($content[$k]) : 0;
        $rows[] = [
            'id' => $id, 'path' => $t['path'], 'name' => (string) ($content['REGION']['name'] ?? shHumanize($id)), 'url' => shWebUrl($t['dir']),
            'pack' => ['exists' => $scenes > 0, 'scenes' => (int) $scenes, 'content' => is_array($content), 'hubLocal' => is_file($packDir . '/hub.json'),
                'type' => (string) ($content['REGION']['type'] ?? ''), 'counts' => ['layanan' => $count('SERVICES'), 'fakta' => $count('FACTS'), 'acara' => $count('EVENTS'), 'rute' => $count('ROUTES'), 'stempel' => $count('STAMPS')]],
            'applied' => $applied !== '', 'appliedSrc' => $applied, 'runtimeReady' => $runtimeReady,
        ];
    }
    return $rows;
}

function shHumanize(string $id): string { return mb_convert_case(trim((string) preg_replace('/[_-]+/', ' ', $id)), MB_CASE_TITLE, 'UTF-8'); }

/* ------------------------------ validasi Hub ------------------------------ */
function shStr(mixed $v, int $max = 200): string { $s = trim(strip_tags((string) $v)); return mb_substr($s, 0, $max); }
function shNum(mixed $v): ?float { return (is_numeric($v) && is_finite((float) $v)) ? round((float) $v, 2) : null; }
function shJsonValue(mixed $v, string $what, int $max = 4000): mixed
{
    if (is_string($v)) { $v = trim($v); if ($v === '') return []; $d = json_decode($v, true); if (!is_array($d)) throw new RuntimeException($what . ' harus JSON objek, mis. {"cat":"food"}'); $v = $d; }
    if (!is_array($v)) return [];
    if (strlen((string) json_encode($v)) > $max) throw new RuntimeException($what . ' terlalu besar.');
    return $v;
}

function shValidateHub(array $in, array $cat, array $icons): array
{
    $features = array_keys((array) ($cat['features'] ?? []));
    $types = (array) ($cat['actionTypes'] ?? []);
    $builtin = array_merge($features, ['peta', 'jali', 'sembunyikan-ui', 'tutup-semua']);
    $actions = [];
    $rawActions = (array) ($in['actions'] ?? []);
    if (count($rawActions) > 120) throw new RuntimeException('Maksimal 120 aksi.');
    foreach ($rawActions as $id => $a) {
        $id = (string) $id; $a = (array) $a;
        if (!preg_match(SH_ID_RE, $id)) throw new RuntimeException('ID aksi "' . $id . '" tidak valid (huruf kecil, angka, - atau _).');
        $type = (string) ($a['type'] ?? '');
        if (!isset($types[$type])) throw new RuntimeException('Aksi ' . $id . ': jenis "' . $type . '" tidak dikenal.');
        $o = ['type' => $type, 'label' => shStr($a['label'] ?? $id, 60)];
        $icon = (string) ($a['icon'] ?? '');
        if ($icon !== '') { if (!in_array($icon, $icons, true)) throw new RuntimeException('Aksi ' . $id . ': ikon "' . $icon . '" tidak ada.'); $o['icon'] = $icon; }
        if (!empty($a['hidden'])) $o['hidden'] = true;
        switch ($type) {
            case 'fitur':
                if (!in_array((string) ($a['feature'] ?? ''), $features, true)) throw new RuntimeException('Aksi ' . $id . ': pilih fitur.');
                $o['feature'] = (string) $a['feature']; $args = shJsonValue($a['args'] ?? [], 'Argumen aksi ' . $id, 1500); if ($args) $o['args'] = $args;
                break;
            case 'scene':
                $sc = (string) ($a['scene'] ?? ''); if (!preg_match('~^[A-Za-z0-9_.-]{1,80}$~', $sc)) throw new RuntimeException('Aksi ' . $id . ': id scene tidak valid.');
                $o['scene'] = $sc; $view = array_filter(['pan' => shNum($a['view']['pan'] ?? null), 'tilt' => shNum($a['view']['tilt'] ?? null), 'fov' => shNum($a['view']['fov'] ?? null)], static fn($x) => $x !== null);
                if ($view) $o['view'] = $view;
                break;
            case 'tautan':
                $url = trim((string) ($a['url'] ?? ''));
                if (!preg_match('~^(https?://[^\s"\'<>]+|/[^\s"\'<>]*)$~', $url)) throw new RuntimeException('Aksi ' . $id . ': URL harus diawali http(s):// atau /');
                $target = (string) ($a['target'] ?? 'popup'); if (!isset($types['tautan']['targets'][$target])) $target = 'popup';
                $o['url'] = $url; $o['target'] = $target;
                break;
            case 'video360':
                $v = (string) ($a['video'] ?? ''); if (!preg_match('~^[a-z0-9][a-z0-9_-]{0,63}$~i', $v)) throw new RuntimeException('Aksi ' . $id . ': id video 360 tidak valid.');
                $o['video'] = $v; break;
            case 'jali': if (($q = shStr($a['q'] ?? '', 200)) !== '') $o['q'] = $q; break;
            case 'bahasa': $l = (string) ($a['lang'] ?? ''); if (!preg_match('~^[a-z]{2,3}(-[A-Za-z]{2})?$~', $l)) throw new RuntimeException('Aksi ' . $id . ': kode bahasa tidak valid.'); $o['lang'] = $l; break;
            case 'ui': $op = (string) ($a['op'] ?? ''); if (!isset($types['ui']['ops'][$op])) throw new RuntimeException('Aksi ' . $id . ': pilih operasi tampilan.'); $o['op'] = $op; break;
            case 'event':
                $n = (string) ($a['name'] ?? ''); if (!preg_match('~^[a-z0-9][a-z0-9:_.-]{0,60}$~i', $n)) throw new RuntimeException('Aksi ' . $id . ': nama event tidak valid.');
                $o['name'] = $n; $d = shJsonValue($a['data'] ?? [], 'Data event ' . $id); if ($d) $o['data'] = $d; break;
            case 'peta': break;
        }
        $actions[$id] = $o;
    }
    $known = array_merge($builtin, array_keys($actions));
    $menus = [];
    foreach ((array) ($in['menus'] ?? []) as $name => $ids) {
        $name = (string) $name; if (!preg_match(SH_ID_RE, $name)) throw new RuntimeException('Nama menu "' . $name . '" tidak valid.');
        $list = [];
        foreach ((array) $ids as $aid) { $aid = (string) $aid; if (!in_array($aid, $known, true)) throw new RuntimeException('Menu ' . $name . ': aksi "' . $aid . '" tidak ada.'); if (!in_array($aid, $list, true)) $list[] = $aid; }
        if (count($list) > 12) throw new RuntimeException('Menu ' . $name . ': maksimal 12 item.');
        $menus[$name] = $list;
    }
    $sources = array_keys((array) ($cat['sources'] ?? []));
    $feeds = [];
    foreach ((array) ($in['feeds'] ?? []) as $id => $f) {
        $id = (string) $id; $f = (array) $f;
        if (!preg_match(SH_ID_RE, $id)) throw new RuntimeException('ID feed "' . $id . '" tidak valid.');
        $src = (string) ($f['source'] ?? ''); if (!in_array($src, $sources, true)) throw new RuntimeException('Feed ' . $id . ': sumber data tidak dikenal.');
        $o = ['source' => $src, 'label' => shStr($f['label'] ?? $id, 60)];
        $flt = [];
        foreach ((array) ($f['filter'] ?? []) as $k => $v) {
            if (!preg_match('~^[A-Za-z0-9_.]{1,40}$~', (string) $k)) throw new RuntimeException('Feed ' . $id . ': nama kolom saring "' . $k . '" tidak valid.');
            $vals = array_values(array_filter(array_map(static fn($x) => shStr($x, 80), is_array($v) ? $v : explode(',', (string) $v)), 'strlen'));
            if ($vals) $flt[(string) $k] = count($vals) === 1 ? $vals[0] : $vals;
        }
        if ($flt) $o['filter'] = $flt;
        if (($q = shStr($f['q'] ?? '', 80)) !== '') $o['q'] = $q;
        $sort = trim((string) ($f['sort'] ?? '')); if ($sort !== '') { if (!preg_match('~^(jarak|-?[A-Za-z0-9_.]{1,40})$~', $sort)) throw new RuntimeException('Feed ' . $id . ': urutan tidak valid.'); $o['sort'] = $sort; }
        $lim = (int) ($f['limit'] ?? 0); if ($lim < 0 || $lim > 500) throw new RuntimeException('Feed ' . $id . ': batas 0–500.'); if ($lim) $o['limit'] = $lim;
        $fields = array_values(array_filter(array_map(static fn($x) => trim((string) $x), is_array($f['fields'] ?? null) ? $f['fields'] : explode(',', (string) ($f['fields'] ?? ''))), static fn($x) => $x !== ''));
        foreach ($fields as $x) if (!preg_match('~^[A-Za-z0-9_.]{1,40}$~', $x)) throw new RuntimeException('Feed ' . $id . ': kolom "' . $x . '" tidak valid.');
        if ($fields) $o['fields'] = $fields;
        if (($f['scene'] ?? '') === 'aktif') $o['scene'] = 'aktif';
        $feeds[$id] = $o;
    }
    $origins = [];
    foreach ((array) ($in['api']['allowOrigins'] ?? []) as $o) {
        $o = rtrim(trim((string) $o), '/'); if ($o === '') continue;
        if ($o !== '*' && !preg_match('~^https?://[a-z0-9.-]+(:\d{1,5})?$~i', $o)) throw new RuntimeException('Origin "' . $o . '" tidak valid. Contoh: https://app.badungkab.go.id');
        $origins[] = strtolower($o);
    }
    $api = ['allowOrigins' => array_values(array_unique($origins)), 'bridge' => ($in['api']['bridge'] ?? true) !== false, 'rest' => ($in['api']['rest'] ?? true) !== false];
    $ui = [];
    $br = (array) ($in['ui']['brand'] ?? []);
    $brand = [];
    if (($logo = trim((string) ($br['logo'] ?? ''))) !== '') {
        if (!preg_match('#^(~/img/brand/[A-Za-z0-9._-]{1,80}(\?v=\d{1,12})?|https?://[^\s"\'<>]+)$#', $logo)) throw new RuntimeException('Alamat logo tidak valid.');
        $brand['logo'] = $logo;
    }
    if (($n = shStr($br['name'] ?? '', 90)) !== '') $brand['name'] = $n;
    if (($n = shStr($br['short'] ?? '', 40)) !== '') $brand['short'] = $n;
    if ($brand) $ui['brand'] = $brand;
    $feId = trim((string) ($in['ui']['frontend']['id'] ?? ''));
    if ($feId !== '') { if (!preg_match('~^[a-z0-9][a-z0-9_-]{1,40}$~', $feId)) throw new RuntimeException('ID paket front-end tidak valid.'); $ui['frontend'] = ['id' => $feId]; }
    $hud = (string) ($in['ui']['hud'] ?? ''); if (in_array($hud, ['orbit', 'bar', 'klasik', 'none'], true)) $ui['hud'] = $hud;
    $theme = (string) ($in['ui']['theme'] ?? ''); if (in_array($theme, ['dark', 'light'], true)) $ui['theme'] = $theme;
    return ['version' => 1, 'actions' => (object) $actions, 'menus' => (object) $menus, 'feeds' => (object) $feeds, 'api' => $api, 'ui' => (object) $ui];
}

/** Unggah logo wilayah (PNG/WebP/JPG, maks 1,5 MB, dibaca ulang sebagai gambar agar berkas palsu ditolak) → packs/<wilayah>/img/brand/logo.<ext>. */
function shSaveLogo(array $st, string $dataUrl): string
{
    $pos = strpos($dataUrl, ';base64,');   // tanpa regex: aman untuk data besar
    $mime = ($pos !== false && str_starts_with($dataUrl, 'data:image/')) ? substr($dataUrl, 11, $pos - 11) : '';
    if (!in_array($mime, ['png', 'jpeg', 'webp'], true)) throw new RuntimeException('Logo harus berformat PNG, JPG, atau WebP.');
    $bin = base64_decode(substr($dataUrl, $pos + 8), true);
    if ($bin === false || $bin === '') throw new RuntimeException('Berkas logo rusak.');
    if (strlen($bin) > 1536 * 1024) throw new RuntimeException('Logo terlalu besar (maks 1,5 MB). Perkecil dulu, mis. 512×512 px.');
    $info = @getimagesizefromstring($bin);
    $map = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($map[$info[2]])) throw new RuntimeException('Berkas bukan gambar PNG/JPG/WebP yang valid.');
    if ($info[0] > 4000 || $info[1] > 4000) throw new RuntimeException('Dimensi logo terlalu besar (maks 4000 px).');
    $dir = $st['path'] . '/packs/' . $st['wilayah'] . '/img/brand';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Folder logo gagal dibuat.');
    foreach (glob($dir . '/logo.*') ?: [] as $old) @unlink($old);
    $name = 'logo.' . $map[$info[2]];
    if (file_put_contents($dir . '/' . $name, $bin, LOCK_EX) === false) throw new RuntimeException('Logo gagal disimpan (izin folder?).');
    return '~/img/brand/' . $name . '?v=' . time();
}

/** Unggah gambar latar Informasi per lokasi. */
function shSaveContentImage(array $st, string $location, string $dataUrl): array
{
    $pos = strpos($dataUrl, ';base64,');
    $mime = ($pos !== false && str_starts_with($dataUrl, 'data:image/')) ? substr($dataUrl, 11, $pos - 11) : '';
    if (!in_array($mime, ['png', 'jpeg', 'webp'], true)) throw new RuntimeException('Gambar harus berformat PNG, JPG, atau WebP.');
    $bin = base64_decode(substr($dataUrl, $pos + 8), true);
    if ($bin === false || $bin === '' || strlen($bin) > 5 * 1024 * 1024) throw new RuntimeException('Gambar rusak atau melebihi 5 MB.');
    $info = @getimagesizefromstring($bin); $map = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($map[$info[2]])) throw new RuntimeException('Berkas bukan gambar PNG/JPG/WebP yang valid.');
    if ($info[0] > 10000 || $info[1] > 10000) throw new RuntimeException('Dimensi gambar terlalu besar (maks 10.000 px).');
    $dir = $st['path'] . '/packs/' . $st['wilayah'] . '/' . $location . '/img';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Folder gambar lokasi gagal dibuat.');
    foreach (glob($dir . '/info-background.*') ?: [] as $old) @unlink($old);
    $name = 'info-background.' . $map[$info[2]];
    if (file_put_contents($dir . '/' . $name, $bin, LOCK_EX) === false) throw new RuntimeException('Gambar latar info gagal disimpan.');
    return ['ref' => 'img/' . $name . '?v=' . time(), 'url' => $st['url'] . 'packs/' . $st['wilayah'] . '/' . $location . '/img/' . $name . '?v=' . time()];
}

function shWriteHub(array $st, array $hub): void
{
    $file = shHubFile($st);
    $dir = dirname($file);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Folder paket wilayah gagal dibuat: ' . $dir);
    if (is_file($file)) {
        $hist = $dir . '/.history'; if (!is_dir($hist)) @mkdir($hist, 0775, true);
        @copy($file, $hist . '/_hub-' . date('Ymd-His') . '.json');
        $old = glob($hist . '/_hub-*.json') ?: []; sort($old); foreach (array_slice($old, 0, max(0, count($old) - 25)) as $o) @unlink($o);
    }
    $hub['_catatan'] = 'Ditulis CMS › Smart Hub. Ubah lewat CMS agar tervalidasi.';
    $hub['updated'] = date(DATE_ATOM);
    if (file_put_contents($file, json_encode($hub, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX) === false) throw new RuntimeException('_hub.json gagal disimpan (izin folder?).');
}

/* ------------------------------ bangun paket & terapkan ------------------------------ */
function shBuildPack(array $st, array $tour): string
{
    $script = $st['path'] . '/tools/build-pack.py';
    if (!is_file($script)) throw new RuntimeException('tools/build-pack.py tidak ditemukan.');
    // -I menonaktifkan user site-packages. Pada Python Microsoft Store, Pillow
    // terpasang di user site sehingga `from PIL import Image` selalu gagal.
    // -E tetap mengabaikan variabel lingkungan Python tanpa menyembunyikan
    // dependensi yang dipasang untuk pengguna Windows yang menjalankan PHP.
    $cmd = [$st['python'], '-E', $script, '--tour', $tour['dir'], '--pack', $st['wilayah'] . '/' . $tour['id'], '--init'];
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, ['PYTHONIOENCODING' => 'utf-8'] + getenv());
    if (!is_resource($proc)) throw new RuntimeException('Python tidak dapat dijalankan (' . $st['python'] . '). Atur jalur Python di Pengaturan.');
    $out = stream_get_contents($pipes[1]); $err = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    $code = proc_close($proc);
    if ($code !== 0) throw new RuntimeException('build-pack gagal (' . $code . '): ' . trim($err ?: $out));
    return trim($out);
}

/** URL relatif dari folder tour menuju berkas runtime project (aman untuk tour bersarang). */
function shRelativeFileUrl(string $fromDirectory, string $targetFile): string
{
    $from = explode('/', trim(str_replace('\\', '/', $fromDirectory), '/'));
    $target = explode('/', trim(str_replace('\\', '/', $targetFile), '/'));
    while ($from && $target && strcasecmp($from[0], $target[0]) === 0) { array_shift($from); array_shift($target); }
    return str_repeat('../', count($from)) . implode('/', array_map('rawurlencode', $target));
}

/**
 * Pasang runtime bersama Jakarta tanpa menyentuh skin.js.
 * main-loader.js juga melakukan preload video teaser desktop/mobile. index.html
 * selalu dicadangkan sebelum perubahan agar dapat dipulihkan secara manual.
 */
function shInstallSharedRuntime(array $project, array $tour): string
{
    $runtime = projectRuntimeDirectory($project);
    $loader = $runtime . '/main-loader.js';
    $css = $runtime . '/custom.css';
    if (!is_file($loader) || !is_file($css)) throw new RuntimeException('Runtime project tidak lengkap: main-loader.js atau custom.css tidak ditemukan di ' . $runtime);
    $index = $tour['dir'] . '/index.html';
    if (!is_file($index)) throw new RuntimeException('index.html tidak ada.');
    $html = (string) file_get_contents($index);
    $loaderUrl = shRelativeFileUrl($tour['dir'], $loader);
    $cssUrl = shRelativeFileUrl($tour['dir'], $css);
    $hasLoader = str_contains($html, $loaderUrl) || (bool) preg_match('~<script[^>]+src=["\'][^"\']*main-loader\.js(?:\?[^"\']*)?["\']~i', $html);
    $hasCss = str_contains($html, $cssUrl) || (bool) preg_match('~<link[^>]+href=["\'][^"\']*custom\.css(?:\?[^"\']*)?["\']~i', $html);
    if ($hasLoader && $hasCss) return 'runtime bersama + teaser sudah terpasang';
    $tags = [];
    if (!$hasCss) $tags[] = '<link rel="stylesheet" href="' . htmlspecialchars($cssUrl, ENT_QUOTES) . '" data-smart-runtime>';
    if (!$hasLoader) $tags[] = '<script src="' . htmlspecialchars($loaderUrl, ENT_QUOTES) . '?v=' . date('YmdHis') . '" data-smart-runtime></script>';
    $insertion = "\n\t\t" . implode("\n\t\t", $tags);
    $updated = preg_replace('~(<script[^>]+src=["\']skin\.js(?:\?[^"\']*)?["\'][^>]*>\s*</script>)~i', '$1' . $insertion, $html, 1, $count);
    if ($updated === null || $count !== 1) {
        $pos = stripos($html, '</head>');
        if ($pos === false) throw new RuntimeException('Tag skin.js dan </head> tidak ditemukan; runtime tidak dipasang.');
        $updated = substr($html, 0, $pos) . $insertion . "\n\t" . substr($html, $pos);
    }
    shBackup($tour['dir'], 'index.html');
    if (file_put_contents($index, $updated, LOCK_EX) === false) throw new RuntimeException('Runtime bersama gagal ditulis ke index.html.');
    return 'runtime bersama + teaser dipasang (backup index.html dibuat)';
}

function shTag(array $st, string $slug): string
{
    return '<script type="module" src="' . htmlspecialchars($st['url'] . 'boot.js?pack=' . $st['wilayah'] . '/' . $slug . '&v=' . date('YmdHis'), ENT_QUOTES) . '" data-smart-kit></script>';
}

function shBackup(string $dir, string $file): void
{
    $b = $dir . '/.skin-backups';
    if (!is_dir($b) && !mkdir($b, 0775, true) && !is_dir($b)) throw new RuntimeException('Folder cadangan gagal dibuat di ' . $dir);
    if (!copy($dir . '/' . $file, $b . '/' . date('Ymd-His') . '-' . $file)) throw new RuntimeException('Cadangan ' . $file . ' gagal.');
}

function shApply(array $st, array $tour, bool $on): string
{
    $index = $tour['dir'] . '/index.html';
    if (!is_file($index)) throw new RuntimeException('index.html tidak ada.');
    $html = (string) file_get_contents($index);
    $re = '~[ \t]*<script\b[^>]*smart-kit/boot\.js[^>]*>\s*</script>\s*\n?~i';
    $clean = (string) preg_replace($re, '', $html);
    if ($on) {
        if (!is_file($st['path'] . '/packs/' . $st['wilayah'] . '/' . $tour['id'] . '/scenes.js')) throw new RuntimeException('Paket belum dibangun; jalankan "Bangun paket" dulu.');
        $pos = strripos($clean, '</body>');
        $new = $pos === false ? $clean . "\n" . shTag($st, $tour['id']) . "\n" : substr($clean, 0, $pos) . "\t\t" . shTag($st, $tour['id']) . "\n\t" . substr($clean, $pos);
    } else {
        if ($clean === $html) return 'tidak ada tag Smart Kit';
        $new = $clean;
    }
    shBackup($tour['dir'], 'index.html');
    if (file_put_contents($index, $new, LOCK_EX) === false) throw new RuntimeException('index.html gagal ditulis.');
    return $on ? 'Smart Kit diterapkan' : 'Smart Kit dilepas (kembali ke skin bawaan)';
}

/* ------------------------------ pratinjau data ------------------------------ */
function shPreviewFeed(array $st, string $slug, array $spec): array
{
    require_once $st['path'] . '/api/lib.php';
    $P = sk_load_pack($st['path'], $st['wilayah'] . '/' . $slug, $st['url']);
    $rows = sk_feed($P, $spec, ['limit' => min(20, (int) ($spec['limit'] ?? 20) ?: 20)]);
    return ['items' => $rows, 'count' => count($rows)];
}

function shScenes(array $st, string $slug): array
{
    $f = $st['path'] . '/packs/' . $st['wilayah'] . '/' . $slug . '/scenes.js';
    if (!is_file($f) || !preg_match('~window\.SMART_SCENES\s*=\s*(\{.*\})\s*;?\s*$~s', (string) file_get_contents($f), $m)) return [];
    $d = json_decode($m[1], true); $out = [];
    foreach ((array) ($d['order'] ?? []) as $id) $out[] = ['id' => $id, 'name' => (string) ($d['byId'][$id]['name'] ?? $id), 'start' => $d['byId'][$id]['start'] ?? null];
    return $out;
}

function shVideos(array $project): array
{
    $root = projectAssetsDirectory($project) . '/360/videos';
    $out = [];
    if (is_dir($root)) foreach (new DirectoryIterator($root) as $e) if (!$e->isDot() && $e->isDir() && is_file($e->getPathname() . '/master.m3u8')) $out[] = $e->getFilename();
    sort($out, SORT_NATURAL);
    return $out;
}

function shState(array $project): array
{
    $st = shSettings($project);
    $kitOk = is_file($st['path'] . '/boot.js') && is_file($st['path'] . '/hub-catalog.json');
    $tours = shTours((string) $project['path']);
    return [
        'project' => ['id' => $project['id'], 'name' => $project['name'] ?? $project['id'], 'path' => $project['path']],
        'settings' => $st, 'kitOk' => $kitOk, 'isAdmin' => cmsIsAdmin(),
        'catalog' => $kitOk ? shCatalog($st) : null, 'icons' => $kitOk ? shIcons($st) : [],
        'hub' => $kitOk ? shReadHub($st) : null, 'hubFile' => shHubFile($st), 'hubExists' => is_file(shHubFile($st)),
        'locations' => $kitOk ? shStatus($st, $tours) : [], 'videos' => shVideos($project),
        'cmsOrigin' => ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
    ];
}

/* ------------------------------ konten lokasi & data wilayah (GUI "Isi Konten") ------------------------------ */
const SH_CONTENT_KEYS = ['REGION', 'ZONES', 'CATS', 'SERVICES', 'FACT_CATS', 'FACTS', 'ETIQUETTE', 'QUIZ', 'SAFETY', 'ROUTES', 'STAMPS', 'REWARDS', 'COLLECTIONS', 'THEMED', 'EVENTS', 'DULU', 'SEATS', 'WIZARD', 'DESTINATIONS', 'MENU', 'REELS', 'LANDMARK', 'ZONEOF'];
const SH_REGION_KEEP = ['brandLogo', 'menuArt', 'skin', 'brand', 'destImg', 'walk', 'aiGuide'];   // dikelola alat lain; tidak diterima dari GUI

function shJakartaDatabaseFile(array $project): string
{
    return projectRuntimeDirectory($project) . '/global-database.js';
}

function shJakartaRuntimeRecord(array $project, string $location): ?array
{
    $file = shJakartaDatabaseFile($project);
    if (!is_file($file)) return null;
    $database = cmsReadJsObject($file, 'GLOBAL_DATABASE');
    $record = $database[$location] ?? null;
    if (!is_array($record)) return null;
    return [
        'id_lokasi' => (string) ($record['id_lokasi'] ?? $location),
        'drone' => (string) ($record['drone'] ?? ''),
        'has_drone' => ($record['has_drone'] ?? false) === true,
        'drone_pan' => (float) ($record['drone_pan'] ?? 0),
        'drone_tilt' => (float) ($record['drone_tilt'] ?? 0),
        'drone_fov' => (float) ($record['drone_fov'] ?? 70),
    ];
}

function shSyncJakartaRuntime(array $project, string $location, array $runtime): void
{
    $file = shJakartaDatabaseFile($project);
    if (!is_file($file)) return;
    $database = cmsReadJsObject($file, 'GLOBAL_DATABASE');
    if (!isset($database[$location]) || !is_array($database[$location])) return;
    $database[$location]['id_lokasi'] = (string) ($runtime['id_lokasi'] ?? $location);
    $database[$location]['drone'] = trim((string) ($runtime['drone'] ?? ''));
    $database[$location]['has_drone'] = ($runtime['has_drone'] ?? false) === true;
    $database[$location]['drone_pan'] = round((float) ($runtime['drone_pan'] ?? 0), 2);
    $database[$location]['drone_tilt'] = round((float) ($runtime['drone_tilt'] ?? 0), 2);
    $database[$location]['drone_fov'] = max(1, min(179, round((float) ($runtime['drone_fov'] ?? 70), 2)));
    $backup = dirname($file) . '/.history'; if (!is_dir($backup)) @mkdir($backup, 0775, true);
    @copy($file, $backup . '/global-database-' . date('Ymd-His') . '.js');
    cmsReplaceJsObject($file, 'GLOBAL_DATABASE', $database);
}

/** Bersihkan data dari GUI: tanpa tag HTML, tanpa skema berbahaya, batas ukuran & kedalaman. */
function shClean(mixed $v, int $depth = 0): mixed
{
    if ($depth > 9) throw new RuntimeException('Struktur data terlalu dalam.');
    if (is_string($v)) {
        $v = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v);
        $v = (string) preg_replace('#</?[a-zA-Z!][^>]*>#', '', $v);
        if (preg_match('#^\s*(javascript|vbscript|data\s*:\s*text/html)#i', $v)) return '';
        return mb_substr(trim($v), 0, 4000);
    }
    if (is_array($v)) {
        if (count($v) > 3000) throw new RuntimeException('Terlalu banyak baris dalam satu daftar (maks 3000).');
        $out = []; $isList = array_is_list($v);
        foreach ($v as $k => $x) {
            if (!$isList && !preg_match('/^[A-Za-z0-9_.:\/ -]{1,90}$/u', (string) $k)) continue;
            $c = shClean($x, $depth + 1);
            if ($c === null) continue;
            if (!$isList && ($c === '' || $c === [])) continue;   // bidang kosong tidak disimpan
            if ($isList) $out[] = $c; else $out[$k] = $c;
        }
        return $out;
    }
    if (is_bool($v) || is_int($v)) return $v;
    if (is_float($v)) return is_finite($v) ? $v : null;
    return null;
}

/** Catatan (bukan galat) agar editor tahu apa yang perlu dibenahi. */
function shContentWarnings(array $c, array $sceneIds): array
{
    $w = []; $known = array_flip($sceneIds);
    $dups = static function (array $rows, string $label) use (&$w) { $seen = []; foreach ($rows as $r) { $id = (string) ($r['id'] ?? ''); if ($id === '') continue; if (isset($seen[$id])) $w[] = $label . ' memakai ID ganda: ' . $id; $seen[$id] = 1; } };
    $sc = static function ($id, string $where) use (&$w, $known, $sceneIds) { if ($id !== null && $id !== '' && $sceneIds && !isset($known[$id])) $w[] = $where . ': scene "' . $id . '" tidak ada di tur'; };
    $dups((array) ($c['SERVICES'] ?? []), 'Layanan'); $dups((array) ($c['FACTS'] ?? []), 'Fakta'); $dups((array) ($c['STAMPS'] ?? []), 'Stempel'); $dups((array) ($c['EVENTS'] ?? []), 'Acara'); $dups((array) ($c['ROUTES'] ?? []), 'Rute');
    foreach ((array) ($c['SERVICES'] ?? []) as $s) {
        $n = (string) ($s['name'] ?? ($s['id'] ?? '?'));
        if (($s['name'] ?? '') === '') $w[] = 'Layanan ' . ($s['id'] ?? '?') . ': nama usaha kosong';
        if (empty($s['scene']) && (!isset($s['lat']) || !isset($s['lng']))) $w[] = 'Layanan ' . $n . ': isi scene ATAU koordinat (lat/lng); tanpa itu layanan dibuang';
        if (isset($s['lat']) && ($s['lat'] < -90 || $s['lat'] > 90)) $w[] = 'Layanan ' . $n . ': lintang di luar rentang';
        if (isset($s['lng']) && ($s['lng'] < -180 || $s['lng'] > 180)) $w[] = 'Layanan ' . $n . ': bujur di luar rentang';
        if (!empty($s['wa']) && !preg_match('/^\d{8,15}$/', (string) $s['wa'])) $w[] = 'Layanan ' . $n . ': WhatsApp harus angka saja dengan kode negara (mis. 628123456789)';
        if (empty($s['cat'])) $w[] = 'Layanan ' . $n . ': kategori belum dipilih';
        $sc($s['scene'] ?? null, 'Layanan ' . $n);
    }
    foreach ((array) ($c['FACTS'] ?? []) as $f) { if (($f['text'] ?? '') === '') $w[] = 'Fakta ' . ($f['id'] ?? '?') . ': isi fakta kosong'; if (($f['src'] ?? '') === '') $w[] = 'Fakta ' . ($f['id'] ?? '?') . ': sumber wajib diisi'; $sc($f['scene'] ?? null, 'Fakta ' . ($f['id'] ?? '?')); }
    foreach ((array) ($c['ROUTES'] ?? []) as $r) { if (count((array) ($r['scenes'] ?? [])) < 2) $w[] = 'Rute ' . ($r['name'] ?? $r['id'] ?? '?') . ': butuh minimal 2 scene'; foreach ((array) ($r['scenes'] ?? []) as $i) $sc($i, 'Rute ' . ($r['name'] ?? '?')); }
    foreach ((array) ($c['EVENTS'] ?? []) as $e) if (!empty($e['date']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $e['date'])) $w[] = 'Acara ' . ($e['title'] ?? '?') . ': tanggal harus YYYY-MM-DD';
    foreach ((array) ($c['STAMPS'] ?? []) as $s) foreach ((array) ($s['scenes'] ?? []) as $i) $sc($i, 'Stempel ' . ($s['name'] ?? '?'));
    foreach ((array) ($c['QUIZ'] ?? []) as $q) if (count((array) ($q['a'] ?? [])) < 2) $w[] = 'Kuis "' . mb_substr((string) ($q['q'] ?? '?'), 0, 30) . '": minimal 2 pilihan jawaban';
    if (empty($c['REGION']['about']['text'])) $w[] = 'Profil: deskripsi (about.text) kosong, tab Tentang tidak terisi';
    if (empty($c['REGION']['center']['lat'])) $w[] = 'Profil: koordinat lokasi belum diisi (peta, jarak, dan matahari tidak akurat)';
    return array_slice($w, 0, 80);
}

function shBackupJson(string $file): void
{
    if (!is_file($file)) return;
    $hist = dirname($file) . '/.history'; if (!is_dir($hist)) @mkdir($hist, 0775, true);
    @copy($file, $hist . '/' . basename($file, '.json') . '-' . date('Ymd-His') . '.json');
    $old = glob($hist . '/' . basename($file, '.json') . '-*.json') ?: []; sort($old);
    foreach (array_slice($old, 0, max(0, count($old) - 25)) as $o) @unlink($o);
}

function shWriteJson(string $file, array $data): void
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    if (strlen($json) > 3 * 1024 * 1024) throw new RuntimeException('Data terlalu besar (maks 3 MB).');
    $dir = dirname($file); if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Folder paket tidak dapat dibuat.');
    shBackupJson($file);
    if (file_put_contents($file . '.tmp', $json . "\n", LOCK_EX) === false || !rename($file . '.tmp', $file)) throw new RuntimeException('Gagal menulis ' . basename($file) . ' (izin folder?).');
}

function shContentGet(array $st, array $tour): array
{
    $dir = $st['path'] . '/packs/' . $st['wilayah'] . '/' . $tour['id'];
    $scenes = shScenes($st, $tour['id']);
    $thumbs = [];
    $f = $dir . '/scenes.js';
    if (is_file($f) && preg_match('~window\.SMART_SCENES\s*=\s*(\{.*\})\s*;?\s*$~s', (string) file_get_contents($f), $m)) {
        $d = json_decode($m[1], true);
        foreach ((array) ($d['byId'] ?? []) as $id => $sc) $thumbs[$id] = $st['url'] . 'packs/' . $st['wilayah'] . '/' . $tour['id'] . '/' . ltrim((string) ($sc['thumb'] ?? ''), '/');
    }
    foreach ($scenes as &$s) $s['thumb'] = $thumbs[$s['id']] ?? '';
    unset($s);
    if (is_file($dir . '/data.js') && !is_file($dir . '/content.json')) throw new RuntimeException('Lokasi ini memakai format lama (data.js) yang tidak dapat disunting di GUI. Migrasikan ke content.json lebih dulu.');
    $content = is_file($dir . '/content.json') ? json_decode((string) file_get_contents($dir . '/content.json'), true) : null;
    $content = is_array($content) ? $content : ['REGION' => ['slug' => $tour['id']]];
    $runtime = shJakartaRuntimeRecord(activeProject(), $tour['id']);
    if ($runtime !== null) $content['REGION']['jakartaRuntime'] = $runtime;
    return ['content' => $content, 'scenes' => $scenes, 'hasPack' => $scenes !== [], 'warnings' => shContentWarnings($content, array_column($scenes, 'id'))];
}

/* ------------------------------ pengendali JSON ------------------------------ */
if (!defined('SMART_HUB_INCLUDE_ONLY')) {
    try {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') cmsJsonResponse(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
        $payload = cmsJsonPayload();
        cmsAssertCsrf((string) ($payload['csrf'] ?? ''));
        if (function_exists('set_time_limit')) @set_time_limit(900);
        $project = activeProject();
        $op = (string) ($payload['op'] ?? 'state');
        if ($op === 'state') cmsJsonResponse(['success' => true, 'state' => shState($project)]);
        if (in_array($op, ['save_settings', 'apply', 'unapply', 'upload_logo'], true)) cmsRequireAdmin('Pengaturan Smart Kit, logo, Terapkan, dan Lepas');
        if ($op === 'save_settings') { shSaveSettings((string) $project['id'], (array) ($payload['settings'] ?? [])); cmsJsonResponse(['success' => true, 'message' => 'Pengaturan Smart Kit project disimpan.', 'state' => shState(activeProject())]); }
        $st = shSettings($project);
        if (!is_file($st['path'] . '/hub-catalog.json')) throw new RuntimeException('Smart Kit belum terhubung: atur folder Smart Kit di Pengaturan.');
        $tours = shTours((string) $project['path']);
        $pick = static function (array $ids) use ($tours): array { $out = []; foreach ($ids as $id) { $id = cmsCleanKey((string) $id); if (!isset($tours[$id])) throw new RuntimeException('Lokasi tidak ditemukan: ' . $id); $out[$id] = $tours[$id]; } if (!$out) throw new RuntimeException('Pilih minimal satu lokasi.'); return $out; };
        switch ($op) {
            case 'save_hub':
                $in = (array) ($payload['hub'] ?? []);
                if (!cmsIsAdmin()) { $cur = shReadHub($st); $in['api'] = $cur['api']; $in['ui'] = $cur['ui']; }   // origin API & gaya HUD hanya diubah admin
                $hub = shValidateHub($in, shCatalog($st), shIcons($st));
                $fid = (string) (((array) $hub['ui'])['frontend']['id'] ?? '');
                if ($fid !== '' && !is_file($st['path'] . '/frontends/' . $fid . '/frontend.json')) throw new RuntimeException('Paket front-end "' . $fid . '" tidak ada di smart-kit/frontends. Unggah dulu atau pilih paket lain.');
                shWriteHub($st, $hub);
                cmsJsonResponse(['success' => true, 'message' => 'Smart Hub wilayah ' . $st['wilayah'] . ' disimpan. Berlaku untuk semua lokasi saat halaman tur dimuat ulang.', 'hub' => shReadHub($st)]);
            case 'upload_logo':
                $ref = shSaveLogo($st, (string) ($payload['data'] ?? ''));
                cmsJsonResponse(['success' => true, 'message' => 'Logo diunggah. Tekan "Simpan Smart Hub" agar dipakai di tur.', 'logo' => $ref, 'url' => $st['url'] . 'packs/' . $st['wilayah'] . '/' . substr($ref, 2)]);
            case 'content_get':
                $id = cmsCleanKey((string) ($payload['location'] ?? ''));
                if (!isset($tours[$id])) throw new RuntimeException('Lokasi tidak ditemukan.');
                cmsJsonResponse(['success' => true] + shContentGet($st, $tours[$id]));
            case 'content_upload_image':
                $id = cmsCleanKey((string) ($payload['location'] ?? ''));
                if (!isset($tours[$id])) throw new RuntimeException('Lokasi tidak ditemukan.');
                $image = shSaveContentImage($st, $id, (string) ($payload['data'] ?? ''));
                cmsAudit('konten.gambar', ['pack' => $st['wilayah'] . '/' . $id]);
                cmsJsonResponse(['success' => true, 'message' => 'Gambar latar info berhasil diunggah.'] + $image);
            case 'content_save':
                $id = cmsCleanKey((string) ($payload['location'] ?? ''));
                if (!isset($tours[$id])) throw new RuntimeException('Lokasi tidak ditemukan.');
                $raw = $payload['content'] ?? null;
                if (!is_array($raw)) throw new RuntimeException('Data konten tidak valid.');
                $clean = [];
                foreach ($raw as $k => $v) if (in_array($k, SH_CONTENT_KEYS, true)) $clean[$k] = shClean($v);
                $file = $st['path'] . '/packs/' . $st['wilayah'] . '/' . $id . '/content.json';
                $cur = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
                foreach (SH_REGION_KEEP as $k) if (isset($cur['REGION'][$k])) $clean['REGION'][$k] = $cur['REGION'][$k];
                foreach ($cur as $k => $v) if (!in_array($k, SH_CONTENT_KEYS, true)) $clean[$k] = $v;
                $clean['REGION']['slug'] = $clean['REGION']['slug'] ?? $id;
                $runtime = (array) ($clean['REGION']['jakartaRuntime'] ?? []);
                if (($runtime['has_drone'] ?? false) === true && trim((string) ($runtime['drone'] ?? '')) !== '') $clean['REGION']['aerial'] = trim((string) $runtime['drone']);
                else unset($clean['REGION']['aerial']);
                shWriteJson($file, $clean);
                if ($runtime) shSyncJakartaRuntime($project, $id, $runtime);
                cmsAudit('konten.simpan', ['pack' => $st['wilayah'] . '/' . $id]);
                cmsJsonResponse(['success' => true, 'message' => 'Konten ' . $id . ' disimpan.', 'content' => $clean, 'warnings' => shContentWarnings($clean, array_column(shScenes($st, $id), 'id'))]);
            case 'region_get':
                $f = $st['path'] . '/packs/' . $st['wilayah'] . '/_region.json';
                $r = is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
                cmsJsonResponse(['success' => true, 'region' => $r, 'exists' => is_file($f)]);
            case 'region_save':
                $raw = $payload['region'] ?? null;
                if (!is_array($raw)) throw new RuntimeException('Data wilayah tidak valid.');
                $f = $st['path'] . '/packs/' . $st['wilayah'] . '/_region.json';
                $cur = is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
                $clean = [];
                foreach ($raw as $k => $v) if (in_array($k, SH_CONTENT_KEYS, true)) $clean[$k] = shClean($v);
                foreach (SH_REGION_KEEP as $k) if (isset($cur['REGION'][$k])) $clean['REGION'][$k] = $cur['REGION'][$k];
                foreach ($cur as $k => $v) if (!in_array($k, SH_CONTENT_KEYS, true)) $clean[$k] = $v;
                shWriteJson($f, $clean);
                cmsAudit('wilayah.simpan', ['wilayah' => $st['wilayah']]);
                cmsJsonResponse(['success' => true, 'message' => 'Data wilayah ' . $st['wilayah'] . ' disimpan.', 'region' => $clean]);
            case 'scenes':
                cmsJsonResponse(['success' => true, 'scenes' => shScenes($st, cmsCleanKey((string) ($payload['location'] ?? '')))]);
            case 'preview_feed':
                $slug = cmsCleanKey((string) ($payload['location'] ?? ''));
                $spec = (array) ($payload['feed'] ?? []);
                $clean = shValidateHub(['feeds' => ['pratinjau' => $spec]], shCatalog($st), shIcons($st));
                cmsJsonResponse(['success' => true] + shPreviewFeed($st, $slug, (array) ((array) $clean['feeds'])['pratinjau']));
            case 'build':
            case 'apply':
            case 'unapply':
                $results = [];
                foreach ($pick((array) ($payload['locations'] ?? [])) as $id => $t) {
                    try {
                        if ($op === 'build') {
                            $buildMessage = shBuildPack($st, $t);
                            $runtimeMessage = shInstallSharedRuntime($project, $t);
                            $msg = trim($buildMessage . ($buildMessage !== '' ? ' · ' : '') . $runtimeMessage);
                        } else {
                            $msg = shApply($st, $t, $op === 'apply');
                        }
                        $results[] = ['id' => $id, 'ok' => true, 'message' => $msg];
                    } catch (Throwable $e) { $results[] = ['id' => $id, 'ok' => false, 'message' => $e->getMessage()]; }
                }
                $okN = count(array_filter($results, static fn($r) => $r['ok']));
                cmsJsonResponse(['success' => $okN > 0, 'message' => $okN . ' dari ' . count($results) . ' lokasi berhasil.', 'results' => $results, 'state' => shState($project)]);
            default:
                throw new RuntimeException('Operasi tidak dikenal: ' . $op);
        }
    } catch (Throwable $e) {
        cmsJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}
