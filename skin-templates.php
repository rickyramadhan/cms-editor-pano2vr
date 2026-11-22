<?php
declare(strict_types=1);

/**
 * Template Skin berbasis folder: skin-templates/<id>/skin.js (+ template.json).
 * Template dipilih dan disimpan eksplisit per project. Penerapan mengganti skin.js, lalu merapikan tag di index.html menurut template.json:
 *   remove  : tag <script src>/<link href> lama yang dihapus (nilai atribut harus persis sama, ?versi boleh beda)
 *   replace : { "lama": "baru" } — alamat tag diganti (bila yang baru sudah ada, tag lama dihapus)
 *   loader  : pemuat yang disisipkan setelah tag skin.js bila belum ada
 * Selain tag-tag itu, isi index.html tidak berubah.
 */

const SKIN_TEMPLATE_ID_PATTERN = '/^[a-z0-9][a-z0-9_-]{0,63}$/';

function skinTemplatesRoot(): string
{
    return __DIR__ . '/skin-templates';
}

/** Berkas tunggal yang boleh diterapkan oleh Template Skin. */
function skinTemplateFiles(string $directory): array
{
    return is_file($directory . '/skin.js') ? ['skin.js' => $directory . '/skin.js'] : [];
}

function skinTemplateList(): array
{
    $root = skinTemplatesRoot();
    $templates = [];
    if (!is_dir($root)) return $templates;
    foreach (new DirectoryIterator($root) as $entry) {
        $id = $entry->getFilename();
        if ($entry->isDot() || !$entry->isDir() || !preg_match(SKIN_TEMPLATE_ID_PATTERN, $id)) continue;
        $directory = $entry->getPathname();
        if (!is_file($directory . '/skin.js')) continue;
        // template.json yang disimpan editor Windows sering ber-BOM; buang dulu agar json_decode tidak gagal diam-diam
        $meta = is_file($directory . '/template.json') ? (json_decode(preg_replace('/^\xEF\xBB\xBF/', '', (string) file_get_contents($directory . '/template.json')), true) ?: []) : [];
        $skinHead = (string) file_get_contents($directory . '/skin.js', false, null, 0, 400);
        $templates[$id] = [
            'id' => $id,
            'name' => trim((string) ($meta['name'] ?? '')) ?: ucwords(str_replace(['-', '_'], ' ', $id)),
            'description' => trim((string) ($meta['description'] ?? '')),
            'preview' => trim((string) ($meta['preview'] ?? '')),
            'loaders' => skinTemplateLoaders($meta['loader'] ?? []),
            'remove' => skinTemplatePaths($meta['remove'] ?? []),
            'replace' => skinTemplateReplacements($meta['replace'] ?? []),
            'directory' => $directory,
            'ggsk' => preg_match('~//\s*Filename:\s*(\S+)~', $skinHead, $m) ? $m[1] : '',
            'generated' => preg_match('~//\s*Generated\s+(\S+)~', $skinHead, $m) ? $m[1] : '',
            'skinHash' => (string) md5_file($directory . '/skin.js'),
            'fileCount' => count(skinTemplateFiles($directory)),
        ];
    }
    uasort($templates, static fn(array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));
    return $templates;
}

/**
 * Alamat pemuat dari template.json › loader: satu string atau daftar (urutan = urutan di index.html), maks. 5.
 * Hanya jalur relatif ke berkas .js di server yang sama (mis. ../assets/template-skin/template-skin-loader.js?v=3); selain itu diabaikan.
 */
function skinTemplateLoaders(mixed $value): array
{
    $valid = static function ($item): string {
        $item = trim((string) $item);
        if ($item === '' || str_contains($item, '//') || str_contains($item, '\\')) return '';
        return preg_match('~^(?:\.{1,2}/)*[A-Za-z0-9_][A-Za-z0-9_./-]*\.js(?:\?[A-Za-z0-9=&._-]*)?$~', $item) ? $item : '';
    };
    $loaders = [];
    foreach (is_array($value) ? $value : [$value] as $item) {
        if (!is_string($item)) continue;
        $item = $valid($item);
        if ($item !== '' && !in_array((string) strtok($item, '?'), array_map(static fn(string $l): string => (string) strtok($l, '?'), $loaders), true)) $loaders[] = $item;
    }
    return array_slice($loaders, 0, 5);
}

/** Jalur relatif .js/.css di server sendiri (untuk remove/replace); selain itu ''. */
function skinTemplatePath(mixed $item): string
{
    if (!is_string($item)) return '';
    $item = trim($item);
    if ($item === '' || str_contains($item, '//') || str_contains($item, '\\')) return '';
    return preg_match('~^(?:\.{1,2}/)*[A-Za-z0-9_][A-Za-z0-9_./-]*\.(?:js|css)(?:\?[A-Za-z0-9=&._-]*)?$~', $item) ? $item : '';
}

/** template.json › remove: daftar jalur (maks. 10). */
function skinTemplatePaths(mixed $value): array
{
    $out = [];
    foreach (is_array($value) ? $value : [$value] as $item) { $p = skinTemplatePath($item); if ($p !== '' && !in_array($p, $out, true)) $out[] = $p; }
    return array_slice($out, 0, 10);
}

/** template.json › replace: { "lama": "baru" } (maks. 10, jenis berkas harus sama). */
function skinTemplateReplacements(mixed $value): array
{
    $out = [];
    if (!is_array($value)) return $out;
    foreach ($value as $old => $new) {
        $old = skinTemplatePath((string) $old); $new = skinTemplatePath($new);
        if ($old === '' || $new === '' || pathinfo((string) strtok($old, '?'), PATHINFO_EXTENSION) !== pathinfo((string) strtok($new, '?'), PATHINFO_EXTENSION)) continue;
        $out[$old] = $new;
    }
    return array_slice($out, 0, 10, true);
}

/** Regex satu tag yang memuat jalur ini persis (src/href; ?versi boleh berbeda), termasuk baris & indentasinya. */
function skinTagPattern(string $path): string
{
    $p = preg_quote((string) strtok($path, '?'), '~');
    return '~(\r?\n[ \t]*)?(?:<script\b[^>]*\bsrc=(["\'])' . $p . '(?:\?[^"\']*)?\2[^>]*>\s*</script>|<link\b[^>]*\bhref=(["\'])' . $p . '(?:\?[^"\']*)?\3[^>]*/?>)~i';
}

/** Apakah index.html masih belum sesuai template (pemuat kurang, tag lama masih ada). */
function skinIndexNeedsFix(string $html, array $template): bool
{
    if (skinMissingLoaders($html, (array) ($template['loaders'] ?? [])) !== []) return true;
    foreach ((array) ($template['remove'] ?? []) as $p) if (preg_match(skinTagPattern($p), $html)) return true;
    foreach ((array) ($template['replace'] ?? []) as $old => $new) if (preg_match(skinTagPattern((string) $old), $html)) return true;
    return false;
}

/**
 * Rapikan index.html menurut template: hapus tag lama, ganti alamat tag, lalu sisipkan pemuat yang kurang.
 * Mengembalikan [html baru, daftar perubahan]. Hanya tag yang cocok persis yang disentuh.
 */
function skinFixIndex(string $html, array $template): array
{
    $changes = [];
    foreach ((array) ($template['remove'] ?? []) as $p) {
        $html = preg_replace(skinTagPattern($p), '', $html, -1, $n) ?? $html;
        if ($n) $changes[] = 'hapus ' . $p;
    }
    foreach ((array) ($template['replace'] ?? []) as $old => $new) {
        $old = (string) $old;
        if (!preg_match(skinTagPattern($old), $html)) continue;
        if (skinLoaderPresent($html, $new)) {   // yang baru sudah ada: cukup hapus yang lama agar tidak ganda
            $html = preg_replace(skinTagPattern($old), '', $html) ?? $html;
        } else {
            $html = preg_replace_callback(skinTagPattern($old), static function (array $m) use ($old, $new): string {
                return preg_replace('~(\b(?:src|href)=(["\']))' . preg_quote((string) strtok($old, '?'), '~') . '(?:\?[^"\']*)?(\2)~i', '${1}' . htmlspecialchars($new, ENT_QUOTES) . '${3}', $m[0], 1) ?? $m[0];
            }, $html, 1) ?? $html;
            $html = preg_replace(skinTagPattern($old), '', $html) ?? $html;   // sisa duplikat lama (bila ada) dihapus
        }
        $changes[] = 'ganti ' . $old . ' → ' . $new;
    }
    $missing = skinMissingLoaders($html, (array) ($template['loaders'] ?? []));
    if ($missing !== []) {
        $html = skinInsertLoaders($html, (array) $template['loaders']);
        foreach ($missing as $l) $changes[] = 'sisip ' . $l;
    }
    if (skinIndexNeedsFix($html, $template)) throw new RuntimeException('Perapian index.html tidak terverifikasi.');
    return [$html, $changes];
}

/** Apakah index.html sudah memuat pemuat ini (dibandingkan tanpa ?versi). */
function skinLoaderPresent(string $html, string $loader): bool
{
    return $loader !== '' && str_contains($html, (string) strtok($loader, '?'));
}

/** Pemuat template yang belum ada di index.html. */
function skinMissingLoaders(string $html, array $loaders): array
{
    return array_values(array_filter($loaders, static fn(string $l): bool => !skinLoaderPresent($html, $l)));
}

/**
 * Sisipkan tag pemuat yang belum ada setelah tag skin.js (dan setelah pemuat template yang sudah ada tepat di bawahnya),
 * masing-masing satu baris. Tag lain di index.html tidak dihapus atau diubah.
 */
function skinInsertLoaders(string $html, array $loaders): string
{
    $missing = skinMissingLoaders($html, $loaders);
    if ($missing === []) return $html;
    if (!preg_match('~<script\b[^>]*\bsrc=["\']skin\.js[^"\']*["\'][^>]*>\s*</script>~i', $html, $match, PREG_OFFSET_CAPTURE)) {
        throw new RuntimeException('Tag skin.js tidak ditemukan di index.html.');
    }
    $end = $match[0][1] + strlen($match[0][0]);
    // lewati pemuat template yang sudah ada tepat di bawah skin.js agar urutan tetap rapi
    while (preg_match('~\G\s*<script\b[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>\s*</script>~i', $html, $next, 0, $end)) {
        $src = html_entity_decode($next[1], ENT_QUOTES);
        $isTemplateLoader = false;
        foreach ($loaders as $l) if ((string) strtok($src, '?') === (string) strtok($l, '?')) $isTemplateLoader = true;
        if (!$isTemplateLoader) break;
        $end += strlen($next[0]);
    }
    $newline = str_contains($html, "\r\n") ? "\r\n" : "\n";
    $indent = preg_match('~([ \t]*)$~', substr($html, 0, $match[0][1]), $m) ? $m[1] : '';
    $lines = implode('', array_map(static fn(string $l): string => $newline . $indent . '<script src="' . htmlspecialchars($l, ENT_QUOTES) . '"></script>', $missing));
    $updated = substr($html, 0, $end) . $lines . substr($html, $end);
    if (strlen($updated) !== strlen($html) + strlen($lines) || skinMissingLoaders($updated, $loaders) !== [] || substr($updated, 0, $end) . substr($updated, $end + strlen($lines)) !== $html) {
        throw new RuntimeException('Penyisipan pemuat tidak terverifikasi.');
    }
    return $updated;
}

function skinTemplateGet(string $id): array
{
    $templates = skinTemplateList();
    if (!preg_match(SKIN_TEMPLATE_ID_PATTERN, $id) || !isset($templates[$id])) throw new RuntimeException('Template skin "' . $id . '" tidak ditemukan di folder skin-templates.');
    return $templates[$id];
}

/** Template terpilih untuk project: hanya pilihan yang sudah disimpan operator. */
function projectSkinTemplateId(array $project, array $templates): string
{
    $saved = (string) ($project['skin_template'] ?? '');
    if ($saved !== '' && isset($templates[$saved])) return $saved;
    return '';
}

/** Aksi setup yang dikenal. Template memilih aksi lewat skin-templates/<id>/setup.json › actions. */
const SKIN_SETUP_ACTIONS = ['cache_bust' => 'Perbarui versi pano.xml di index.html agar browser memuat ulang arah pandang/hotspot terbaru'];

/** Setup opsional sebuah template (setup.json); null bila template tidak punya setup. Tombol "Setup" di editor hotspot hanya muncul bila ada. */
function skinTemplateSetup(string $templateId): ?array
{
    if (!preg_match(SKIN_TEMPLATE_ID_PATTERN, $templateId)) return null;
    $file = skinTemplatesRoot() . '/' . $templateId . '/setup.json';
    if (!is_file($file)) return null;
    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data)) return null;
    $actions = array_values(array_filter((array) ($data['actions'] ?? []), static fn($a): bool => is_string($a) && isset(SKIN_SETUP_ACTIONS[$a])));
    if ($actions === []) return null;
    return [
        'id' => $templateId,
        'label' => mb_substr(trim(strip_tags((string) ($data['label'] ?? ''))), 0, 20) ?: 'Setup',
        'title' => mb_substr(trim(strip_tags((string) ($data['title'] ?? ''))), 0, 200),
        'actions' => $actions,
    ];
}

/** Ganti versi pada URL pano.xml di index.html (readConfigUrlAsync) dengan stempel baru; langsung menimpa index.html tanpa cadangan. */
function skinSetupCacheBust(string $tourDirectory): string
{
    $index = $tourDirectory . '/index.html';
    $html = is_file($index) ? file_get_contents($index) : false;
    if ($html === false) throw new RuntimeException('index.html lokasi tidak dapat dibaca.');
    // ts dihitung di browser (Date.now()) tiap tur dibuka: index.html sendiri bisa ikut ter-cache browser,
    // sehingga ts angka-mati di dalamnya tidak ikut baru dan pano.xml lama terus dipakai.
    $updated = preg_replace('/(readConfigUrlAsync\s*\(\s*)(["\'])pano\.xml(?:\?[^"\']*)?\2(?:\s*\+\s*Date\.now\(\))?/', '${1}"pano.xml?ts=" + Date.now()', $html, 1, $count);
    if ($updated === null || $count !== 1) throw new RuntimeException('Pemanggilan readConfigUrlAsync("pano.xml") tidak ditemukan di index.html.');
    if (file_put_contents($index, $updated, LOCK_EX) === false) throw new RuntimeException('index.html gagal diperbarui.');
    return 'pano.xml kini selalu dimuat ulang tanpa cache';
}

/** Jalankan semua aksi setup template pada satu folder tur; kembalikan daftar pesan hasil. */
function skinRunSetup(array $setup, string $tourDirectory): array
{
    $messages = [];
    foreach ($setup['actions'] as $action) {
        if ($action === 'cache_bust') $messages[] = skinSetupCacheBust($tourDirectory);
    }
    return $messages;
}

function updateProjectSkinTemplate(string $projectId, string $templateId): void
{
    skinTemplateGet($templateId);
    $registry = projectRegistry();
    if (!isset($registry[$projectId])) throw new RuntimeException('Project tidak ditemukan.');
    $registry[$projectId]['skin_template'] = $templateId;
    writeProjectRegistry($registry);
}

/**
 * Ganti skin.js pada satu folder lokasi, lalu (bila template punya "loader") sisipkan satu tag pemuat di index.html.
 * Lock per lokasi, backup ke .skin-backups/<stempel>/, verifikasi hash, dan rollback otomatis (skin.js + index.html) bila ada yang gagal.
 */
function applySkinTemplate(array $template, string $projectRoot, string $targetDirectory, string $locationId): array
{
    $root = realpath($projectRoot);
    $target = realpath($targetDirectory);
    if ($root === false || $target === false || !str_starts_with(strtolower(str_replace('\\', '/', $target)) . '/', strtolower(str_replace('\\', '/', $root)) . '/') || strcasecmp($root, $target) === 0) {
        throw new RuntimeException('Folder lokasi ' . $locationId . ' berada di luar project.');
    }
    if (!is_file($target . '/skin.js') || !is_file($target . '/pano.xml')) throw new RuntimeException('Folder ' . $locationId . ' bukan folder tur Pano2VR (skin.js/pano.xml tidak ada).');

    require_once __DIR__ . '/includes/backup-store.php';
    $backupRoot = cmsBackupEnsure($target, 'skin');   // cadangan di folder pusat CMS, bukan di folder tur
    if (!is_dir($backupRoot) && !mkdir($backupRoot, 0775, true) && !is_dir($backupRoot)) throw new RuntimeException('Folder backup gagal dibuat untuk ' . $locationId . '.');
    $lock = fopen($backupRoot . '/.lock', 'c');
    if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) throw new RuntimeException('Lokasi ' . $locationId . ' sedang diproses. Coba lagi sebentar.');

    $stamp = date('Ymd-His') . '-' . bin2hex(random_bytes(2));
    $backupDirectory = $backupRoot . '/' . $stamp;
    $backedUp = [];
    $created = [];
    try {
        $files = skinTemplateFiles($template['directory']);
        foreach ($files as $relative => $source) {
            $destination = $target . '/' . $relative;
            if (is_file($destination)) {
                $backup = $backupDirectory . '/' . $relative;
                if (!is_dir(dirname($backup)) && !mkdir(dirname($backup), 0775, true) && !is_dir(dirname($backup))) throw new RuntimeException('Folder backup gagal dibuat untuk ' . $locationId . '.');
                if (!copy($destination, $backup)) throw new RuntimeException('Backup ' . $relative . ' gagal untuk ' . $locationId . '.');
                $backedUp[$relative] = $backup;
            } else {
                $created[] = $relative;
            }
        }
        foreach ($files as $relative => $source) {
            $destination = $target . '/' . $relative;
            if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0775, true) && !is_dir(dirname($destination))) throw new RuntimeException('Folder ' . dirname($relative) . ' gagal dibuat untuk ' . $locationId . '.');
            if (!copy($source, $destination) || md5_file($source) !== md5_file($destination)) throw new RuntimeException($relative . ' gagal disalin ke ' . $locationId . ' (hash tidak cocok).');
        }
        // pemuat skin (mis. video intro Badung) dipasang lewat index.html, bukan dari skin.js: sisipkan satu tag bila template memintanya
        $inserted = [];
        $indexChanges = [];
        $indexFile = $target . '/index.html';
        $wantsIndex = ($template['loaders'] ?? []) !== [] || ($template['remove'] ?? []) !== [] || ($template['replace'] ?? []) !== [];
        if ($wantsIndex && is_file($indexFile)) {
            $html = file_get_contents($indexFile);
            if ($html === false) throw new RuntimeException('index.html tidak dapat dibaca untuk ' . $locationId . '.');
            $missing = skinMissingLoaders($html, (array) ($template['loaders'] ?? []));
            [$updated, $indexChanges] = skinFixIndex($html, $template);
            if ($updated !== $html) {
                $backup = $backupDirectory . '/index.html';
                if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) throw new RuntimeException('Folder backup gagal dibuat untuk ' . $locationId . '.');
                if (!copy($indexFile, $backup)) throw new RuntimeException('Backup index.html gagal untuk ' . $locationId . '.');
                $backedUp['index.html'] = $backup;
                if (file_put_contents($indexFile, $updated, LOCK_EX) === false || md5_file($indexFile) !== md5($updated)) throw new RuntimeException('Pemuat gagal dipasang di index.html ' . $locationId . '.');
                $inserted = $missing;
            }
        }
    } catch (Throwable $error) {
        foreach ($backedUp as $relative => $backup) @copy($backup, $target . '/' . $relative);
        foreach ($created as $relative) @unlink($target . '/' . $relative);
        if (function_exists('cmsAudit')) cmsAudit('skin_template_failed', ['template' => $template['id'], 'location' => $locationId, 'error' => $error->getMessage()]);
        throw $error;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
    if (function_exists('cmsAudit')) cmsAudit('skin_template_applied', ['template' => $template['id'], 'location' => $locationId, 'files' => count($files), 'loaders' => $inserted, 'index' => $indexChanges, 'backup' => $backedUp === [] ? '' : '.skin-backups/' . $stamp]);
    return ['files' => count($files), 'loader' => count($inserted), 'loaders' => $inserted, 'index' => $indexChanges, 'backup' => $backedUp === [] ? '' : $stamp];
}
