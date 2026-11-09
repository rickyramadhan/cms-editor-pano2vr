<?php
declare(strict_types=1);
/*
 * smart-frontend.php — kelola PAKET FRONT-END KUSTOM (skin, menu, halaman popup buatan developer lain) untuk Smart Kit.
 * Paket tersimpan di <smart-kit>/frontends/<id>/ (manifest frontend.json). Dipilih per wilayah di Smart Hub (ui.frontend.id).
 *
 * Unggah tanpa ekstensi ZIP: browser mengirim berkas bertahap (JSON base64) ke folder staging, lalu "commit" memvalidasi manifest
 * dan memasangnya secara atomik (paket lama dicadangkan). Semua operasi tulis khusus ADMIN: kode paket dijalankan di halaman tur.
 *
 * ops: list | begin | put | commit | abort | delete | template
 */
define('SMART_HUB_INCLUDE_ONLY', true);
require_once __DIR__ . '/smart-hub.php';

const FE_ID_RE = '~^[a-z0-9][a-z0-9_-]{1,40}$~';
const FE_ALLOWED_EXT = ['html', 'htm', 'css', 'js', 'mjs', 'json', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'otf', 'mp4', 'webm', 'mp3', 'ogg', 'txt', 'md'];
const FE_MAX_FILE = 15 * 1024 * 1024;
const FE_MAX_TOTAL = 80 * 1024 * 1024;
const FE_MAX_FILES = 500;

function feRoot(array $st): string { return $st['path'] . '/frontends'; }

function feSafePath(string $p): string
{
    $p = str_replace('\\', '/', trim($p));
    if ($p === '' || $p[0] === '/' || preg_match('~(^|/)\.\.?(/|$)~', $p) || preg_match('/[\x00-\x1F:*?"<>|]/', $p) || strlen($p) > 180) throw new RuntimeException('Jalur berkas tidak valid: ' . mb_substr($p, 0, 80));
    foreach (explode('/', $p) as $seg) if ($seg === '' || $seg[0] === '.') throw new RuntimeException('Nama berkas/folder tidak boleh diawali titik: ' . $p);
    $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
    if (!in_array($ext, FE_ALLOWED_EXT, true)) throw new RuntimeException('Jenis berkas tidak diizinkan (.' . $ext . '): ' . $p . '. Diizinkan: ' . implode(', ', FE_ALLOWED_EXT));
    return $p;
}

function feRemoveTree(string $dir): void
{
    if (!is_dir($dir)) return;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $i) $i->isDir() && !$i->isLink() ? rmdir($i->getPathname()) : unlink($i->getPathname());
    rmdir($dir);
}

function feManifest(string $dir): ?array
{
    $f = $dir . '/frontend.json';
    $m = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
    return is_array($m) ? $m : null;
}

function feSize(string $dir): int
{
    $n = 0; if (!is_dir($dir)) return 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) if ($f->isFile()) $n += $f->getSize();
    return $n;
}

function feList(array $st): array
{
    $root = feRoot($st); $out = [];
    if (is_dir($root)) foreach (new DirectoryIterator($root) as $d) {
        if ($d->isDot() || !$d->isDir()) continue;
        $id = $d->getFilename(); if ($id[0] === '_' || $id[0] === '.' || !preg_match(FE_ID_RE, $id)) continue;
        $m = feManifest($d->getPathname()); if (!$m) continue;
        $out[] = ['id' => $id, 'name' => (string) ($m['name'] ?? $id), 'version' => (string) ($m['version'] ?? ''), 'author' => (string) ($m['author'] ?? ''), 'description' => (string) ($m['description'] ?? ''),
            'entry' => (string) ($m['entry'] ?? ''), 'pages' => array_keys((array) ($m['pages'] ?? [])), 'bytes' => feSize($d->getPathname()), 'updated' => date(DATE_ATOM, (int) @filemtime($d->getPathname() . '/frontend.json'))];
    }
    usort($out, static fn($a, $b) => strnatcasecmp($a['name'], $b['name']));
    return $out;
}

/** Validasi manifest + keberadaan berkas yang dirujuk. Kembalikan manifest bersih. */
function feValidateManifest(array $m, string $dir, array $features): array
{
    $id = (string) ($m['id'] ?? '');
    if (!preg_match(FE_ID_RE, $id)) throw new RuntimeException('frontend.json: "id" wajib (huruf kecil, angka, - atau _; 2–41 karakter).');
    $clean = ['id' => $id, 'name' => mb_substr(trim(strip_tags((string) ($m['name'] ?? $id))), 0, 80), 'version' => mb_substr(trim(strip_tags((string) ($m['version'] ?? '1.0.0'))), 0, 20)];
    foreach (['author', 'description'] as $k) if (!empty($m[$k])) $clean[$k] = mb_substr(trim(strip_tags((string) $m[$k])), 0, 300);
    $check = static function (string $rel, string $what) use ($dir): string { $rel = feSafePath($rel); if (!is_file($dir . '/' . $rel)) throw new RuntimeException('frontend.json: ' . $what . ' menunjuk berkas yang tidak ada: ' . $rel); return $rel; };
    if (!empty($m['entry'])) $clean['entry'] = $check((string) $m['entry'], 'entry');
    $pages = [];
    foreach ((array) ($m['pages'] ?? []) as $feat => $rel) {
        if (!in_array((string) $feat, $features, true)) throw new RuntimeException('frontend.json: halaman "' . $feat . '" bukan fitur Smart Kit. Fitur yang ada: ' . implode(', ', $features));
        $pages[(string) $feat] = $check((string) $rel, 'pages.' . $feat);
    }
    if (!isset($clean['entry']) && !$pages) throw new RuntimeException('frontend.json: isi minimal "entry" (shell) atau "pages".');
    if ($pages) $clean['pages'] = $pages;
    $css = [];
    foreach ((array) ($m['globalCss'] ?? []) as $c) $css[] = $check((string) $c, 'globalCss');
    if ($css) $clean['globalCss'] = $css;
    if (array_key_exists('companion', $m)) $clean['companion'] = (bool) $m['companion'];
    return $clean;
}

function feStagingDir(array $st, string $token): string
{
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) throw new RuntimeException('Token unggah tidak valid.');
    return feRoot($st) . '/.staging/' . $token;
}

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') cmsJsonResponse(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
    $payload = cmsJsonPayload();
    cmsAssertCsrf((string) ($payload['csrf'] ?? ''));
    if (function_exists('set_time_limit')) @set_time_limit(300);
    $project = activeProject();
    $st = shSettings($project);
    if (!is_file($st['path'] . '/hub-catalog.json')) throw new RuntimeException('Smart Kit belum terhubung (atur di Integrasi › Pengaturan project).');
    $op = (string) ($payload['op'] ?? 'list');
    $features = array_keys((array) (shCatalog($st)['features'] ?? []));
    $root = feRoot($st);

    if ($op === 'list') cmsJsonResponse(['success' => true, 'packs' => feList($st), 'allowedExt' => FE_ALLOWED_EXT, 'features' => $features]);
    if ($op === 'template') {
        $dir = $root . '/_template'; if (!is_dir($dir)) throw new RuntimeException('Folder template tidak ditemukan: frontends/_template');
        $files = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) {
            if (!$f->isFile()) continue;
            $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($dir) + 1));
            if (!in_array(strtolower($f->getExtension()), FE_ALLOWED_EXT, true) || $f->getSize() > 512 * 1024) continue;
            $files[] = ['path' => $rel, 'text' => (string) file_get_contents($f->getPathname())];
        }
        cmsJsonResponse(['success' => true, 'files' => $files]);
    }

    cmsRequireAdmin('Mengunggah atau menghapus paket front-end');   // kode paket berjalan di halaman tur
    switch ($op) {
        case 'begin':
            $token = bin2hex(random_bytes(16)); $dir = feStagingDir($st, $token);
            if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Folder staging gagal dibuat (izin tulis smart-kit/frontends).');
            file_put_contents($root . '/.htaccess', "# paket front-end: hanya aset statis\nOptions -Indexes -ExecCGI\n<FilesMatch \"\\.(php[0-9]?|phtml|phar|cgi|pl|py|sh|exe|bat|cmd)$\">\n    Require all denied\n</FilesMatch>\n");
            // bersihkan staging lama (> 6 jam)
            foreach (glob($root . '/.staging/*', GLOB_ONLYDIR) ?: [] as $old) if (@filemtime($old) < time() - 6 * 3600) feRemoveTree($old);
            cmsJsonResponse(['success' => true, 'token' => $token, 'limits' => ['files' => FE_MAX_FILES, 'file' => FE_MAX_FILE, 'total' => FE_MAX_TOTAL]]);
        case 'put':
            $dir = feStagingDir($st, (string) ($payload['token'] ?? '')); if (!is_dir($dir)) throw new RuntimeException('Sesi unggah tidak ditemukan; mulai ulang.');
            $count = 0; $total = feSize($dir);
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) if ($f->isFile()) $count++;
            foreach ((array) ($payload['files'] ?? []) as $f) {
                $rel = feSafePath((string) ($f['path'] ?? ''));
                $bin = base64_decode((string) ($f['data'] ?? ''), true);
                if ($bin === false) throw new RuntimeException('Isi berkas rusak: ' . $rel);
                if (strlen($bin) > FE_MAX_FILE) throw new RuntimeException('Berkas terlalu besar (maks 15 MB): ' . $rel);
                if (++$count > FE_MAX_FILES) throw new RuntimeException('Terlalu banyak berkas (maks ' . FE_MAX_FILES . ').');
                if (($total += strlen($bin)) > FE_MAX_TOTAL) throw new RuntimeException('Ukuran paket melebihi 80 MB.');
                if (preg_match('/\.(html?|css|js|mjs|json|svg|txt|md)$/i', $rel) && preg_match('/<\?(php|=)/i', $bin)) throw new RuntimeException('Kode server (PHP) tidak diizinkan di paket: ' . $rel);
                $target = $dir . '/' . $rel; $parent = dirname($target);
                if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) throw new RuntimeException('Folder gagal dibuat: ' . dirname($rel));
                if (file_put_contents($target, $bin, LOCK_EX) === false) throw new RuntimeException('Gagal menulis: ' . $rel);
            }
            cmsJsonResponse(['success' => true, 'count' => $count, 'bytes' => $total]);
        case 'commit':
            $dir = feStagingDir($st, (string) ($payload['token'] ?? '')); if (!is_dir($dir)) throw new RuntimeException('Sesi unggah tidak ditemukan; mulai ulang.');
            $m = feManifest($dir); if (!$m) throw new RuntimeException('frontend.json tidak ditemukan di akar paket (atau bukan JSON valid). Pilih folder yang berisi frontend.json.');
            $clean = feValidateManifest($m, $dir, $features);
            file_put_contents($dir . '/frontend.json', json_encode($clean + array_diff_key($m, $clean), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
            $final = $root . '/' . $clean['id'];
            if (is_dir($final)) {
                $bk = $root . '/.backup'; if (!is_dir($bk)) @mkdir($bk, 0775, true);
                if (!rename($final, $bk . '/' . $clean['id'] . '-' . date('Ymd-His'))) throw new RuntimeException('Paket lama gagal dicadangkan.');
                $old = glob($bk . '/' . $clean['id'] . '-*', GLOB_ONLYDIR) ?: []; sort($old); foreach (array_slice($old, 0, max(0, count($old) - 3)) as $o) feRemoveTree($o);
            }
            if (!rename($dir, $final)) throw new RuntimeException('Paket gagal dipasang (izin folder?).');
            cmsAudit('frontend.pasang', ['id' => $clean['id'], 'versi' => $clean['version']]);
            cmsJsonResponse(['success' => true, 'message' => 'Paket "' . $clean['name'] . '" (' . $clean['id'] . ') terpasang.', 'packs' => feList($st), 'id' => $clean['id']]);
        case 'abort':
            $dir = feStagingDir($st, (string) ($payload['token'] ?? '')); feRemoveTree($dir);
            cmsJsonResponse(['success' => true]);
        case 'delete':
            $id = (string) ($payload['id'] ?? ''); if (!preg_match(FE_ID_RE, $id) || $id[0] === '_') throw new RuntimeException('ID paket tidak valid.');
            $dir = $root . '/' . $id; if (!is_dir($dir)) throw new RuntimeException('Paket tidak ditemukan.');
            // jangan hapus paket yang sedang dipakai wilayah ini
            $hub = shReadHub($st); if ((string) ($hub['ui']['frontend']['id'] ?? '') === $id) throw new RuntimeException('Paket ini sedang dipakai wilayah ' . $st['wilayah'] . '. Lepas dulu di pengaturan lalu simpan.');
            $bk = $root . '/.backup'; if (!is_dir($bk)) @mkdir($bk, 0775, true);
            if (!rename($dir, $bk . '/' . $id . '-' . date('Ymd-His'))) throw new RuntimeException('Paket gagal dihapus.');
            cmsAudit('frontend.hapus', ['id' => $id]);
            cmsJsonResponse(['success' => true, 'message' => 'Paket ' . $id . ' dihapus (cadangan disimpan di frontends/.backup).', 'packs' => feList($st)]);
        default: throw new RuntimeException('Operasi tidak dikenal: ' . $op);
    }
} catch (Throwable $e) {
    cmsJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
