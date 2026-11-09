<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
cmsAuthGate();   // semua halaman & endpoint CMS wajib login (lihat includes/auth.php)

const PROJECT_REGISTRY_FILE = __DIR__ . '/projects.json';
const PROJECT_SETTINGS_FILE = __DIR__ . '/cms-settings.json';

function cmsBaseDirectory(): string
{
    static $base;
    if ($base !== null) return $base;
    if (is_file(PROJECT_SETTINGS_FILE)) {
        $settings = json_decode((string) file_get_contents(PROJECT_SETTINGS_FILE), true);
        $candidate = is_array($settings) ? (string) ($settings['projects_root'] ?? '') : '';
        if ($candidate !== '' && is_dir($candidate)) return $base = str_replace('\\', '/', (string) realpath($candidate));
    }
    return $base = str_replace('\\', '/', dirname(__DIR__));
}

function projectRegistry(): array
{
    if (!is_file(PROJECT_REGISTRY_FILE)) return [];
    $registry = json_decode((string) file_get_contents(PROJECT_REGISTRY_FILE), true);
    return is_array($registry) ? $registry : [];
}

function discoverProjects(): array
{
    $base = cmsBaseDirectory();
    $registered = projectRegistry();
    $registeredPaths = [];
    foreach ($registered as $project) {
        $real = realpath((string) ($project['path'] ?? ''));
        if ($real !== false) $registeredPaths[strtolower(str_replace('\\', '/', $real))] = true;
    }

    $projects = [];
    foreach (new DirectoryIterator($base) as $entry) {
        if ($entry->isDot() || !$entry->isDir()) continue;
        $name = $entry->getFilename();
        if ($name === basename(__DIR__) || str_starts_with($name, '.')) continue;
        $path = str_replace('\\', '/', $entry->getPathname());
        $hasAssets = is_dir($path . '/assets');
        $hasTours = false;
        if (!$hasAssets) {
            foreach (new DirectoryIterator($path) as $child) {
                if (!$child->isDot() && $child->isDir() && is_file($child->getPathname() . '/pano.xml')) { $hasTours = true; break; }
            }
        }
        if (!$hasAssets && !$hasTours) continue;
        $projects[] = [
            'name' => ucwords(str_replace(['-', '_'], ' ', $name)),
            'path' => $path,
            'registered' => isset($registeredPaths[strtolower($path)]),
        ];
    }
    usort($projects, static fn(array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));
    return $projects;
}

function writeProjectRegistry(array $registry): void
{
    ksort($registry, SORT_NATURAL | SORT_FLAG_CASE);
    $json = json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    if (file_put_contents(PROJECT_REGISTRY_FILE, $json . PHP_EOL, LOCK_EX) === false) throw new RuntimeException('Registry project tidak dapat disimpan.');
}

function normalizeProjectPath(string $path): string
{
    $path = trim(str_replace('\\', '/', $path));
    if ($path === '') throw new RuntimeException('Folder project wajib dipilih.');
    if (!preg_match('/^[A-Za-z]:\//', $path)) $path = rtrim(cmsBaseDirectory(), '/') . '/' . ltrim($path, '/');
    $real = realpath($path);
    if ($real === false || !is_dir($real)) throw new RuntimeException('Folder project tidak ditemukan.');
    $real = str_replace('\\', '/', $real);
    $base = rtrim(strtolower(cmsBaseDirectory()), '/');
    if ($real === __DIR__ || !str_starts_with(strtolower($real) . '/', $base . '/')) throw new RuntimeException('Folder harus berada di dalam ' . cmsBaseDirectory() . '.');
    if (strtolower($real) === strtolower(str_replace('\\', '/', __DIR__))) throw new RuntimeException('Folder CMS tidak dapat dijadikan project.');
    return $real;
}

function registerProject(string $name, string $path): string
{
    $root = normalizeProjectPath($path);
    $id = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '-', trim(basename($root))));
    $id = trim((string) preg_replace('/-+/', '-', $id), '-');
    if ($id === '') throw new RuntimeException('Nama folder project tidak valid.');
    $registry = projectRegistry();
    // Asset must be chosen explicitly after registration. Existing project files
    // are never adopted or overwritten without confirmation from the user.
    $registry[$id] = ['id' => $id, 'name' => trim($name) !== '' ? trim($name) : ucwords(str_replace(['-', '_'], ' ', $id)), 'path' => $root, 'template_tour' => '', 'deploy_url' => '', 'assets_dir' => ''];
    writeProjectRegistry($registry);
    return $id;
}

function removeProject(string $id): void
{
    $registry = projectRegistry();
    if (!isset($registry[$id])) throw new RuntimeException('Project tidak ditemukan.');
    unset($registry[$id]);
    writeProjectRegistry($registry);
    if (($_SESSION['cms_project'] ?? '') === $id) unset($_SESSION['cms_project']);
}

function updateProjectTemplate(string $id, string $tourId): void
{
    $registry = projectRegistry();
    if (!isset($registry[$id])) throw new RuntimeException('Project tidak ditemukan.');
    $tourId = strtolower(trim((string) preg_replace('/[^a-z0-9_-]/i', '', $tourId)));
    $registry[$id]['template_tour'] = $tourId;
    writeProjectRegistry($registry);
}

function updateProjectCategories(string $id, array $categories): void
{
    $registry = projectRegistry();
    if (!isset($registry[$id])) throw new RuntimeException('Project tidak ditemukan.');
    $clean = [];
    foreach ($categories as $key => $label) {
        $slug = strtolower(trim((string) preg_replace('/[^a-z0-9_-]/i', '-', is_string($key) ? $key : (string) $label), '-'));
        $name = trim(strip_tags((string) $label));
        if ($slug !== '' && $name !== '') $clean[$slug] = $name;
    }
    $registry[$id]['categories'] = $clean;
    writeProjectRegistry($registry);
}

function projectTemplateDirectory(string $projectId): string
{
    return __DIR__ . '/templates/' . preg_replace('/[^a-z0-9_-]/i', '', $projectId);
}

function activeProject(): array
{
    // hanya project yang boleh diakses akun ini (admin: semua; editor: sesuai daftar projects di akunnya)
    $registry = array_filter(projectRegistry(), static fn($p, $k) => PHP_SAPI === 'cli' || cmsCanAccessProject((string) $k), ARRAY_FILTER_USE_BOTH);
    $requested = (string) ($_GET['project'] ?? $_POST['project'] ?? $_SESSION['cms_project'] ?? '');
    if ($requested !== '' && isset($registry[$requested])) $_SESSION['cms_project'] = $requested;
    $id = isset($registry[$requested]) ? $requested : (string) array_key_first($registry);
    if ($id === '' || !isset($registry[$id])) throw new RuntimeException(projectRegistry() ? 'Akun Anda belum diberi akses ke project mana pun. Hubungi admin CMS.' : 'Belum ada project. Tambahkan project dari dashboard CMS.');
    $_SESSION['cms_project'] = $id;
    $project = $registry[$id];
    $project['path'] = normalizeProjectPath((string) $project['path']);
    return $project;
}

function activeProjectRoot(): string
{
    return (string) activeProject()['path'];
}

function projectAssetsDirectory(array $project): string
{
    $directory = trim((string) ($project['assets_dir'] ?? 'assets')) ?: 'assets';
    if (!preg_match('/^assets(?:-\d+)?$/', $directory)) throw new RuntimeException('Nama folder asset project tidak valid.');
    return rtrim((string) $project['path'], '/\\') . '/' . $directory;
}

function activeProjectAssetsRoot(): string
{
    return projectRuntimeDirectory(activeProject());
}

function projectRuntimeDirectory(array $project): string
{
    $assets = projectAssetsDirectory($project);
    $runtime = $assets . '/main-loader';
    return is_dir($runtime) ? $runtime : $assets;
}

function activeProjectAssetBaseRoot(): string
{
    return projectAssetsDirectory(activeProject());
}

function assetsRootForProjectPath(string $projectRoot): string
{
    $normalized = strtolower(str_replace('\\', '/', (string) realpath($projectRoot)));
    foreach (projectRegistry() as $project) {
        $path = realpath((string) ($project['path'] ?? ''));
        if ($path !== false && strtolower(str_replace('\\', '/', $path)) === $normalized) return projectRuntimeDirectory($project);
    }
    return rtrim($projectRoot, '/\\') . '/assets';
}

/** Folder assets dianggap lengkap bila punya database runtime CMS (struktur main-loader atau datar). */
function projectAssetsValid(string $assetsDir): bool
{
    return is_file($assetsDir . '/main-loader/global-database.js') || is_file($assetsDir . '/global-database.js');
}

/**
 * Sumber template assets untuk project BARU (wilayah mana pun). Urutan:
 *  1. cms-settings.json › assets_template (jalur folder assets yang dijadikan acuan)
 *  2. project "jakarta-output" (template asli) bila foldernya ada di server ini
 *  3. project terdaftar lain yang assets-nya lengkap (bukan $exceptId)
 * Nama fungsi dipertahankan agar kode lama tetap jalan.
 */
function jakartaAssetsTemplate(string $exceptId = ''): string
{
    $settings = is_file(PROJECT_SETTINGS_FILE) ? (json_decode((string) file_get_contents(PROJECT_SETTINGS_FILE), true) ?: []) : [];
    $configured = rtrim(strtr((string) ($settings['assets_template'] ?? ''), DIRECTORY_SEPARATOR, '/'), '/');
    if ($configured !== '' && projectAssetsValid($configured)) return $configured;
    $registry = projectRegistry();
    $candidates = [];
    if (isset($registry['jakarta-output'])) $candidates[] = $registry['jakarta-output'];
    foreach ($registry as $id => $p) if ($id !== 'jakarta-output' && $id !== $exceptId) $candidates[] = $p;
    foreach ($candidates as $p) {
        $dir = rtrim(strtr((string) ($p['path'] ?? ''), DIRECTORY_SEPARATOR, '/'), '/') . '/' . ((trim((string) ($p['assets_dir'] ?? '')) ?: 'assets'));
        if (($p['id'] ?? '') !== $exceptId && projectAssetsValid($dir)) return $dir;
    }
    throw new RuntimeException('Belum ada template assets di server ini. Daftarkan satu project yang assets-nya lengkap (mis. Jakarta), atau isi "assets_template" di cms-settings.json.');
}

function removeDirectoryTree(string $directory): void
{
    if (!is_dir($directory)) return;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $item) $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    rmdir($directory);
}

function installProjectAssets(string $projectId, string $strategy): array
{
    if (function_exists('set_time_limit')) @set_time_limit(0);
    $registry = projectRegistry();
    if (!isset($registry[$projectId])) throw new RuntimeException('Project tidak ditemukan.');
    $project = $registry[$projectId];
    $root = normalizeProjectPath((string) $project['path']);
    // Pakai folder assets yang sudah ada (mis. project Jakarta yang sudah berjalan): tidak menyalin, tidak menghapus apa pun.
    if ($strategy === 'adopt') {
        $name = trim((string) ($project['assets_dir'] ?? '')) ?: 'assets';
        if (!projectAssetsValid($root . '/' . $name)) throw new RuntimeException('Folder ' . $name . ' belum berisi runtime CMS (main-loader/global-database.js). Pilih opsi lain.');
        $registry[$projectId]['assets_dir'] = $name;
        writeProjectRegistry($registry);
        return ['directory' => $name, 'files' => 0, 'archived' => '', 'adopted' => true];
    }
    if ($projectId === 'jakarta-output') throw new RuntimeException('Asset Jakarta adalah template sumber dan tidak boleh diganti dari CMS.');
    $current = trim((string) ($project['assets_dir'] ?? '')) ?: 'assets';
    $targetName = 'assets';
    if ($strategy === 'alternate') {
        $number = 2;
        while (is_dir($root . '/assets-' . $number)) $number++;
        $targetName = 'assets-' . $number;
    } elseif ($strategy === 'replace') {
        $targetName = $current;
        removeDirectoryTree($root . '/' . $targetName);
    } elseif ($strategy === 'archive') {
        $targetName = 'assets';
        $existing = $root . '/assets';
        if (!is_dir($existing)) throw new RuntimeException('Folder assets lama tidak ditemukan.');
        $archiveName = 'assets-old';
        $number = 2;
        while (file_exists($root . '/' . $archiveName)) $archiveName = 'assets-old-' . $number++;
        if (!rename($existing, $root . '/' . $archiveName)) throw new RuntimeException('Folder assets lama gagal diubah menjadi ' . $archiveName . '.');
    } elseif ($strategy !== 'create') {
        throw new RuntimeException('Pilihan pemasangan asset tidak valid.');
    }
    $target = $root . '/' . $targetName;
    if (is_dir($target)) throw new RuntimeException('Folder ' . $targetName . ' sudah ada. Pilih Replace atau Buat assets-2.');
    $copied = copyMissingDirectory(jakartaAssetsTemplate($projectId), $target);
    resetCopiedAssetData($target);
    $registry[$projectId]['assets_dir'] = $targetName;
    writeProjectRegistry($registry);
    synchronizeProjectAssetReferences($root, $targetName);
    return ['directory' => $targetName, 'files' => count($copied), 'archived' => $archiveName ?? ''];
}

function resetCopiedJsAssignment(string $file, string $variable, string $emptyValue): void
{
    $content = file_get_contents($file);
    if ($content === false) throw new RuntimeException(basename($file) . ' hasil template tidak dapat dibaca.');
    $pattern = '/window\.' . preg_quote($variable, '/') . '\s*=\s*(?:\{.*?\}|\[.*?\])\s*;/s';
    $updated = preg_replace($pattern, 'window.' . $variable . ' = ' . $emptyValue . ';', $content, 1, $count);
    if ($updated === null || $count !== 1 || file_put_contents($file, $updated, LOCK_EX) === false) throw new RuntimeException('Data ' . basename($file) . ' gagal dikosongkan.');
}

function resetCopiedAssetData(string $assetsRoot): void
{
    // Keep every runtime/template file from Jakarta, but never copy Jakarta's
    // project records or URLs into a newly managed project.
    $runtimeRoot = is_dir($assetsRoot . '/main-loader') ? $assetsRoot . '/main-loader' : $assetsRoot;
    resetCopiedJsAssignment($runtimeRoot . '/global-database.js', 'GLOBAL_DATABASE', '{}');
    resetCopiedJsAssignment($runtimeRoot . '/oi-database.js', 'TourDatabase', '[]');
    resetCopiedJsAssignment($runtimeRoot . '/global-config.js', 'GLOBAL_LINK_CONFIG', json_encode([
        'base_url' => '',
        'links' => [],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
}

function synchronizeProjectAssetReferences(string $projectRoot, string $assetsDirectory): void
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || strtolower($file->getFilename()) !== 'index.html') continue;
        $path = str_replace('\\', '/', $file->getPathname());
        if (str_contains($path, '/assets') || str_contains($path, '/cms')) continue;
        $content = file_get_contents($file->getPathname());
        if ($content === false) continue;
        $updated = preg_replace('~\.\./assets(?:-\d+)?/~', '../' . $assetsDirectory . '/', $content);
        if ($updated !== null && $updated !== $content) file_put_contents($file->getPathname(), $updated, LOCK_EX);
    }
}

function copyMissingDirectory(string $source, string $destination): array
{
    if (!is_dir($source)) throw new RuntimeException('Template assets CMS belum tersedia.');
    $copied = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $item) {
        $relative = str_replace('\\', '/', $iterator->getSubPathName());
        $target = rtrim($destination, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if ($item->isDir()) {
            if (!is_dir($target) && !mkdir($target, 0775, true) && !is_dir($target)) throw new RuntimeException('Gagal membuat folder ' . $relative);
            continue;
        }
        if (is_file($target)) continue;
        $parent = dirname($target);
        if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) throw new RuntimeException('Gagal membuat folder assets.');
        if (!copy($item->getPathname(), $target)) throw new RuntimeException('Gagal menyalin ' . $relative);
        $copied[] = $relative;
    }
    return $copied;
}

function applyProjectAssets(string $projectRoot): array
{
    return copyMissingDirectory(jakartaAssetsTemplate(), rtrim($projectRoot, '/\\') . '/assets');
}

