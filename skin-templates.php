<?php
declare(strict_types=1);

/**
 * Template Skin berbasis folder: skin-templates/<id>/ (skin.js wajib; pano2vr_player.js, images/, 3rdparty/ opsional).
 * Tidak terikat ke lokasi atau wilayah tertentu — tambah template cukup dengan menambah folder (lihat skin-templates/README.md).
 */

const SKIN_TEMPLATE_ID_PATTERN = '/^[a-z0-9][a-z0-9_-]{0,63}$/';

function skinTemplatesRoot(): string
{
    return __DIR__ . '/skin-templates';
}

/** Daftar file yang akan disalin dari template: [relatif => path absolut]. */
function skinTemplateFiles(string $directory): array
{
    $files = [];
    foreach (['skin.js', 'pano2vr_player.js'] as $name) if (is_file($directory . '/' . $name)) $files[$name] = $directory . '/' . $name;
    foreach (['images', '3rdparty'] as $folder) {
        if (!is_dir($directory . '/' . $folder)) continue;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory . '/' . $folder, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $item) {
            if (!$item->isFile()) continue;
            $relative = $folder . '/' . str_replace('\\', '/', substr($item->getPathname(), strlen($directory . '/' . $folder) + 1));
            if (str_contains($relative, '..')) continue;
            $files[$relative] = $item->getPathname();
        }
    }
    return $files;
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
        $meta = is_file($directory . '/template.json') ? (json_decode((string) file_get_contents($directory . '/template.json'), true) ?: []) : [];
        $skinHead = (string) file_get_contents($directory . '/skin.js', false, null, 0, 400);
        $templates[$id] = [
            'id' => $id,
            'name' => trim((string) ($meta['name'] ?? '')) ?: ucwords(str_replace(['-', '_'], ' ', $id)),
            'description' => trim((string) ($meta['description'] ?? '')),
            'preview' => trim((string) ($meta['preview'] ?? '')),
            'directory' => $directory,
            'ggsk' => preg_match('~//\s*Filename:\s*(\S+)~', $skinHead, $m) ? $m[1] : '',
            'generated' => preg_match('~//\s*Generated\s+(\S+)~', $skinHead, $m) ? $m[1] : '',
            'skinHash' => (string) md5_file($directory . '/skin.js'),
            'hasPlayer' => is_file($directory . '/pano2vr_player.js'),
            'fileCount' => count(skinTemplateFiles($directory)),
        ];
    }
    uasort($templates, static fn(array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));
    return $templates;
}

function skinTemplateGet(string $id): array
{
    $templates = skinTemplateList();
    if (!preg_match(SKIN_TEMPLATE_ID_PATTERN, $id) || !isset($templates[$id])) throw new RuntimeException('Template skin "' . $id . '" tidak ditemukan di folder skin-templates.');
    return $templates[$id];
}

/** Template terpilih untuk project: pilihan tersimpan › template yang namanya cocok dengan id project › kosong. */
function projectSkinTemplateId(array $project, array $templates): string
{
    $saved = (string) ($project['skin_template'] ?? '');
    if ($saved !== '' && isset($templates[$saved])) return $saved;
    $projectId = strtolower((string) ($project['id'] ?? ''));
    foreach (array_keys($templates) as $id) if ($projectId === $id || str_starts_with($projectId, $id . '-')) return $id;
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
 * Salin file template ke satu folder lokasi.
 * Lock per lokasi, backup ke .skin-backups/<stempel>/, verifikasi hash, dan rollback otomatis bila ada yang gagal.
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
    } catch (Throwable $error) {
        foreach ($backedUp as $relative => $backup) @copy($backup, $target . '/' . $relative);
        foreach ($created as $relative) @unlink($target . '/' . $relative);
        if (function_exists('cmsAudit')) cmsAudit('skin_template_failed', ['template' => $template['id'], 'location' => $locationId, 'error' => $error->getMessage()]);
        throw $error;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
    if (function_exists('cmsAudit')) cmsAudit('skin_template_applied', ['template' => $template['id'], 'location' => $locationId, 'files' => count($files), 'backup' => $backedUp === [] ? '' : '.skin-backups/' . $stamp]);
    return ['files' => count($files), 'backup' => $backedUp === [] ? '' : $stamp];
}
