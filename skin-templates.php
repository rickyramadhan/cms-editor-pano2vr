<?php
declare(strict_types=1);

/**
 * Template Skin berbasis folder: skin-templates/<id>/skin.js (+ template.json).
 * Template dipilih dan disimpan eksplisit per project. Penerapan mengganti skin.js; bila template.json punya "loader",
 * satu tag <script src="loader"> disisipkan tepat setelah tag skin.js di index.html (bila belum ada). Selain itu tidak ada yang diubah.
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
            'loader' => skinTemplateLoader((string) ($meta['loader'] ?? '')),
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
 * Alamat pemuat dari template.json › loader. Hanya jalur relatif ke berkas .js di server yang sama
 * (mis. ../assets/template-skin/template-skin-loader.js?v=3); selain itu diabaikan.
 */
function skinTemplateLoader(string $value): string
{
    $value = trim($value);
    if ($value === '' || str_contains($value, '//') || str_contains($value, '..\\')) return '';
    return preg_match('~^(?:\.{1,2}/)*[A-Za-z0-9_][A-Za-z0-9_./-]*\.js(?:\?[A-Za-z0-9=&._-]*)?$~', $value) ? $value : '';
}

/** Apakah index.html sudah memuat pemuat ini (dibandingkan tanpa ?versi). */
function skinLoaderPresent(string $html, string $loader): bool
{
    return $loader !== '' && str_contains($html, (string) strtok($loader, '?'));
}

/** Sisipkan satu tag pemuat tepat setelah tag skin.js; selain baris itu, isi index.html tidak berubah. */
function skinInsertLoader(string $html, string $loader): string
{
    if (!preg_match('~<script\b[^>]*\bsrc=["\']skin\.js[^"\']*["\'][^>]*>\s*</script>~i', $html, $match, PREG_OFFSET_CAPTURE)) {
        throw new RuntimeException('Tag skin.js tidak ditemukan di index.html.');
    }
    $end = $match[0][1] + strlen($match[0][0]);
    $newline = str_contains($html, "\r\n") ? "\r\n" : "\n";
    $indent = preg_match('~([ \t]*)$~', substr($html, 0, $match[0][1]), $m) ? $m[1] : '';
    $line = $newline . $indent . '<script src="' . htmlspecialchars($loader, ENT_QUOTES) . '"></script>';
    $updated = substr($html, 0, $end) . $line . substr($html, $end);
    if (strlen($updated) !== strlen($html) + strlen($line) || !skinLoaderPresent($updated, $loader)) throw new RuntimeException('Penyisipan pemuat tidak terverifikasi.');
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

    $backupRoot = $target . '/.skin-backups';
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
        $loaderInserted = false;
        $loader = (string) ($template['loader'] ?? '');
        $indexFile = $target . '/index.html';
        if ($loader !== '' && is_file($indexFile)) {
            $html = file_get_contents($indexFile);
            if ($html === false) throw new RuntimeException('index.html tidak dapat dibaca untuk ' . $locationId . '.');
            if (!skinLoaderPresent($html, $loader)) {
                $updated = skinInsertLoader($html, $loader);
                $backup = $backupDirectory . '/index.html';
                if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) throw new RuntimeException('Folder backup gagal dibuat untuk ' . $locationId . '.');
                if (!copy($indexFile, $backup)) throw new RuntimeException('Backup index.html gagal untuk ' . $locationId . '.');
                $backedUp['index.html'] = $backup;
                if (file_put_contents($indexFile, $updated, LOCK_EX) === false || md5_file($indexFile) !== md5($updated)) throw new RuntimeException('Pemuat gagal dipasang di index.html ' . $locationId . '.');
                $loaderInserted = true;
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
    if (function_exists('cmsAudit')) cmsAudit('skin_template_applied', ['template' => $template['id'], 'location' => $locationId, 'files' => count($files), 'loader' => $loaderInserted ? $loader : '', 'backup' => $backedUp === [] ? '' : '.skin-backups/' . $stamp]);
    return ['files' => count($files), 'loader' => $loaderInserted, 'backup' => $backedUp === [] ? '' : $stamp];
}
