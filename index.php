<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_once __DIR__ . '/language-manager.php';
require_once __DIR__ . '/project-manager.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['project']) && !isset($_GET['tab'])) {
    header('Location: projects.php');
    exit;
}

cmsEnsureCsrfToken();

$projectAction = (string) ($_POST['project_action'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $projectAction !== '') {
    try {
        if (!hash_equals((string) $_SESSION['cms_csrf'], (string) ($_POST['csrf'] ?? ''))) throw new RuntimeException('Sesi formulir tidak valid. Muat ulang halaman.');
        cmsRequireAdmin('Pengaturan project');
        if ($projectAction === 'register') {
            $projectId = registerProject((string) ($_POST['project_name'] ?? ''), (string) ($_POST['project_path'] ?? ''));
            $_SESSION['cms_project'] = $projectId;
            $_SESSION['cms_flash'] = 'Project berhasil didaftarkan.';
        } elseif ($projectAction === 'remove') {
            removeProject((string) ($_POST['project_id'] ?? ''));
            $_SESSION['cms_flash'] = 'Project dihapus dari daftar. File project tidak dihapus.';
        } elseif ($projectAction === 'apply_assets') {
            $project = activeProject();
            $copied = applyProjectAssets((string) $project['path']);
            $_SESSION['cms_flash'] = count($copied) . ' asset template diterapkan tanpa menimpa file yang sudah ada.';
        } elseif ($projectAction === 'set_template') {
            $project = activeProject();
            updateProjectTemplate((string) $project['id'], (string) ($_POST['template_tour'] ?? ''));
            $_SESSION['cms_flash'] = 'Sumber template project berhasil diperbarui.';
        }
        header('Location: ' . strtok((string) $_SERVER['REQUEST_URI'], '?'));
        exit;
    } catch (Throwable $projectException) {
        $error = $projectException->getMessage();
    }
}

$projects = projectRegistry();
$activeProject = activeProject();
$projectRoot = (string) $activeProject['path'];
$assetsBaseRoot = projectAssetsDirectory($activeProject);
$assetsRoot = projectRuntimeDirectory($activeProject);
$assetsDirectoryName = basename($assetsBaseRoot);
$runtimeDirectoryName = basename($assetsRoot);
$projectCategories = [];

function projectUrl(string $relative = ''): string
{
    global $projectRoot;
    $from = explode('/', trim(str_replace('\\', '/', __DIR__), '/'));
    $to = explode('/', trim(str_replace('\\', '/', $projectRoot), '/'));
    while ($from !== [] && $to !== [] && strcasecmp((string) $from[0], (string) $to[0]) === 0) {
        array_shift($from);
        array_shift($to);
    }
    return str_repeat('../', count($from)) . ($to !== [] ? implode('/', array_map('rawurlencode', $to)) . '/' : '') . ltrim($relative, '/');
}

function cmsLanguages(): array
{
    global $projectRoot;
    static $languages;
    if ($languages === null) {
        $registry = languageRegistry($projectRoot);
        $languages = array_map(static fn(array $language): string => (string) $language['label'], $registry);
    }
    return $languages;
}

const EST_DESTINATIONS = [
    'museum-sejarah-jakarta-fatahillah', 'museum-wayang', 'museum-tekstil', 'museum-seni-rupa-dan-keramik', 'museum-bahari', 'monumen-nasional', 'kawasan-kota-tua', 'kawasan-candra-naya', 'kawasan-house-of-tugu', 'museum-bank-mandiri', 'museum-bank-indonesia', 'pecinan-glodok', 'gpib-koinoia', 'pasar-batu-akik', 'setu-babakan', 'pulau-pramuka', 'pulau-tidung', 'pulau-macan', 'pulau-sepa', 'pulau-onrust', 'tebet-ecopark', 'taman-langsat', 'taman-ayodia-barito', 'urban-forest-cipete', 'sedayu-golf-island', 'batavia-the-cove', 'garden-by-the-sea', 'petak-enam', 'taman-literasi', 'central-market', 'east-coast', 'pantjoran-pik', 'si-mian-fo', 'lapangan-banteng', 'pantjoran-tea-house', 'taman-benyamin-sueb'
];

$databaseFile = $assetsRoot . '/global-database.js';
$configFile = $assetsRoot . '/global-config.js';
$oiDatabaseFile = $assetsRoot . '/oi-database.js';
$customCssFile = $assetsRoot . '/custom.css';
$skinTemplateId = (string) ($activeProject['template_tour'] ?? '');
$skinTemplateDirectory = projectTemplateDirectory((string) $activeProject['id']);
$message = '';
$error = $error ?? '';

function readJsObject(string $file, string $variable): array
{
    return cmsReadJsObject($file, $variable);
}

function replaceJsObject(string $file, string $variable, array $data): void
{
    cmsReplaceJsObject($file, $variable, $data);
}

function cleanKey(string $value): string
{
    return cmsCleanKey($value);
}

function humanizeLocationId(string $id): string
{
    $name = preg_replace('/[_-]+/', ' ', $id);
    $name = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', (string) $name);
    if (!str_contains((string) $name, ' ') && preg_match('/^(pulau|museum|monumen|taman|pantai|kota|desa|kampung|masjid|gereja|gedung|rumah|pasar)(.+)$/i', (string) $name, $parts)) {
        $name = $parts[1] . ' ' . $parts[2];
    }
    return mb_convert_case(trim((string) $name), MB_CASE_TITLE, 'UTF-8');
}

function locationCategory(string $id, array $location = []): string
{
    global $activeProject;
    $saved = trim((string) ($location['category'] ?? ''));
    if ($saved !== '') return $saved;
    if (($activeProject['id'] ?? '') !== 'jakarta-output') return 'uncategorized';
    $heritage = ['museum-sejarah-jakarta-fatahillah','museum-wayang','museum-tekstil','museum-seni-rupa-dan-keramik','museum-bahari','monumen-nasional','kawasan-kota-tua','kawasan-candra-naya','kawasan-house-of-tugu','museum-bank-mandiri','museum-bank-indonesia','pecinan-glodok','gpib-koinoia','pasar-batu-akik','setu-babakan'];
    $maritim = ['pulau-pramuka','pulau-tidung','pulau-macan','pulau-sepa','pulau-onrust'];
    return in_array($id, $heritage, true) ? 'heritage' : (in_array($id, $maritim, true) ? 'maritim' : 'urban');
}

function copyDirectoryContents(string $source, string $destination): void
{
    if (!is_dir($source)) return;
    if (!is_dir($destination) && !mkdir($destination, 0775, true) && !is_dir($destination)) throw new RuntimeException('Folder dependency gagal dibuat.');
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $item) {
        $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        if ($item->isDir()) {
            if (!is_dir($target) && !mkdir($target, 0775, true) && !is_dir($target)) throw new RuntimeException('Folder dependency gagal dibuat.');
        } elseif (!copy($item->getPathname(), $target)) {
            throw new RuntimeException('Dependency skin gagal disalin: ' . $item->getFilename());
        }
    }
}

function ensureTourBootstrap(string $indexFile): void
{
    global $assetsDirectoryName, $runtimeDirectoryName;
    $html = file_get_contents($indexFile);
    if ($html === false) throw new RuntimeException('index.html lokasi tidak dapat dibaca.');
    $cacheVersion = (string) time();
    $html = preg_replace('/src=(["\'])(pano2vr_player\.js|skin\.js)(?:\?[^"\']*)?\1/i', 'src=$1$2?v=' . $cacheVersion . '$1', $html) ?? $html;
    $html = preg_replace(
        '/pano\.readConfigUrlAsync\((["\'])pano\.xml(?:\?[^"\']*)?\1\)/i',
        'pano.readConfigUrlAsync($1pano.xml?v=' . $cacheVersion . '$1)',
        $html
    ) ?? $html;
    if (preg_match('/pano\.setQueryParameter\s*\(/i', $html)) {
        $html = preg_replace(
            '/pano\.setQueryParameter\s*\(\s*(["\'])[^"\']*\1\s*\)\s*;?/i',
            'pano.setQueryParameter("v=' . $cacheVersion . '");',
            $html,
            1
        ) ?? $html;
    } else {
        $html = preg_replace(
            '/(pano\.startView\s*=\s*startView\s*;)/i',
            '$1' . "\n\t\t\tpano.setQueryParameter(\"v=" . $cacheVersion . "\");",
            $html,
            1,
            $queryCount
        ) ?? $html;
        if (($queryCount ?? 0) !== 1) throw new RuntimeException('Inisialisasi Pano2VR pada index.html tidak ditemukan.');
    }
    $tags = [];
    $runtimePrefix = $runtimeDirectoryName === $assetsDirectoryName ? '' : $runtimeDirectoryName . '/';
    if (!str_contains($html, '../' . $assetsDirectoryName . '/' . $runtimePrefix . 'custom.css')) $tags[] = '<link rel="stylesheet" href="../' . $assetsDirectoryName . '/' . $runtimePrefix . 'custom.css"/>';
    if (!str_contains($html, '3rdparty/leaflet/leaflet.css')) $tags[] = '<link rel="stylesheet" href="3rdparty/leaflet/leaflet.css"/>';
    if (!str_contains($html, '../' . $assetsDirectoryName . '/' . $runtimePrefix . 'main-loader.js')) $tags[] = '<script src="../' . $assetsDirectoryName . '/' . $runtimePrefix . 'main-loader.js"></script>';
    if (!str_contains($html, '3rdparty/leaflet/leaflet.js')) $tags[] = '<script src="3rdparty/leaflet/leaflet.js"></script>';
    $updated = $html;
    if ($tags !== []) {
        $insertion = "\n\t\t" . implode("\n\t\t", $tags) . "\n\t";
        $updated = preg_replace('/(<script[^>]+src=["\']skin\.js[^>]*>\s*<\/script>)/i', '$1' . $insertion, $html, 1, $count);
        if ($count !== 1 || $updated === null) throw new RuntimeException('Tag skin.js pada index.html tidak ditemukan.');
    }
    if (file_put_contents($indexFile, $updated, LOCK_EX) === false) throw new RuntimeException('index.html gagal diperbarui.');
}

function enableTourSounds(string $panoFile): void
{
    $xml = file_get_contents($panoFile);
    if ($xml === false) throw new RuntimeException('pano.xml tidak dapat dibaca.');
    $updated = preg_replace('/(<tour\b[^>]*\bhassounds=")[01](")/i', '${1}1$2', $xml, 1, $count);
    if ($count === 0) $updated = preg_replace('/<tour\b/i', '<tour hassounds="1"', $xml, 1);
    if ($updated === null || file_put_contents($panoFile, $updated, LOCK_EX) === false) throw new RuntimeException('Status audio pano.xml gagal diperbarui.');
}

function ensureTourMap(string $panoFile): void
{
    $xml = file_get_contents($panoFile);
    if ($xml === false) throw new RuntimeException('pano.xml tidak dapat dibaca untuk konfigurasi map.');
    if (preg_match('/<map\b[^>]*\bid="Map01"[^>]*\/?\s*>/i', $xml)) return;
    $map = '  <map mapurltemplate="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" mapmaxzoom="18" type="web" index="0" mapprovider="openstreetmap" mapstyle="streets" title="" googlecustomstylecode="" id="Map01"/>';
    $updated = preg_replace('/\s*<\/tour>\s*$/i', "\n" . $map . "\n</tour>\n", $xml, 1, $count);
    if ($updated === null || $count !== 1 || file_put_contents($panoFile, $updated, LOCK_EX) === false) throw new RuntimeException('Konfigurasi OpenStreetMap pada pano.xml gagal dipasang.');
}

function scanTourDirectories(string $root): array
{
    $detected = [];
    $queue = [];
    foreach (new DirectoryIterator($root) as $entry) {
        if ($entry->isDot() || !$entry->isDir() || in_array($entry->getFilename(), ['assets', 'cms', '.git'], true)) continue;
        $queue[] = [$entry->getPathname(), $entry->getFilename(), 1];
    }

    while ($queue !== []) {
        [$directory, $relativePath, $depth] = array_shift($queue);
        $skinFile = $directory . '/skin.js';
        if (is_file($directory . '/pano.xml') && is_file($skinFile)) {
            $id = cleanKey(basename($directory));
            if ($id !== '') $detected[$id] = ['id_lokasi' => $id, 'path' => str_replace('\\', '/', $relativePath)];
            continue;
        }
        if ($depth >= 5) continue;
        foreach (new DirectoryIterator($directory) as $child) {
            $childName = strtolower($child->getFilename());
            if ($child->isDot() || !$child->isDir() || preg_match('/^assets(?:-\d+|-old(?:-\d+)?)?$/', $childName) || in_array($childName, ['cms','.git','tiles','images','video','audio','3rdparty','.skin-backups','.media-backups'], true)) continue;
            $queue[] = [$child->getPathname(), $relativePath . '/' . $child->getFilename(), $depth + 1];
        }
    }

    ksort($detected, SORT_NATURAL | SORT_FLAG_CASE);
    return $detected;
}

function readPanoramaNodes(string $root, array $tour): array
{
    $tourPath = $root . '/' . $tour['path'];
    $xml = file_get_contents($tourPath . '/pano.xml');
    if ($xml === false) return [];
    preg_match_all('/<userdata\s+([^>]*\bnodeid="[^"]+"[^>]*)\/>/i', $xml, $matches);
    $nodes = [];
    foreach ($matches[1] ?? [] as $attributeText) {
        preg_match_all('/([a-zA-Z0-9_-]+)="([^"]*)"/', $attributeText, $attributeMatches, PREG_SET_ORDER);
        $attributes = [];
        foreach ($attributeMatches as $attribute) $attributes[strtolower($attribute[1])] = html_entity_decode($attribute[2], ENT_QUOTES | ENT_XML1, 'UTF-8');
        $nodeId = trim((string) ($attributes['nodeid'] ?? ''));
        if ($nodeId === '') continue;
        $customNodeId = trim((string) ($attributes['customnodeid'] ?? ''));
        $thumbnail = '';
        foreach (['jpg', 'jpeg', 'webp', 'png'] as $extension) {
            $candidate = 'images/category_thumbnail_' . $nodeId . '.' . $extension;
            if (is_file($tourPath . '/' . $candidate)) { $thumbnail = projectUrl($tour['path'] . '/' . $candidate); break; }
        }
        if ($thumbnail === '') {
            $tilePatterns = [
                $tourPath . '/tiles/' . $nodeId . '/cf_*/l_0/c_0/tile_0.{jpg,jpeg,webp,png}',
                $tourPath . '/tiles/' . $nodeId . '/cf_*/l_1/c_0/tile_0.{jpg,jpeg,webp,png}',
            ];
            foreach ($tilePatterns as $tilePattern) {
                $tileMatches = glob($tilePattern, GLOB_BRACE) ?: [];
                if ($tileMatches === []) continue;
                sort($tileMatches, SORT_NATURAL | SORT_FLAG_CASE);
                $relativeTile = str_replace('\\', '/', substr($tileMatches[0], strlen($tourPath) + 1));
                $thumbnail = projectUrl($tour['path'] . '/' . $relativeTile);
                break;
            }
        }
        $nodes[] = [
            'nodeId' => $nodeId,
            'customNodeId' => $customNodeId,
            'value' => $customNodeId !== '' ? $customNodeId : $nodeId,
            'title' => trim((string) ($attributes['title'] ?? '')),
            'thumbnail' => $thumbnail,
        ];
    }
    return $nodes;
}

function readTourWebElements(string $root, array $tour): array
{
    $panoFile = $root . '/' . str_replace('/', DIRECTORY_SEPARATOR, $tour['path']) . '/pano.xml';
    $xml = file_get_contents($panoFile);
    if ($xml === false) return [];
    preg_match_all('/<panorama\b[^>]*\bid="([^"]+)"[^>]*>(.*?)<\/panorama>/is', $xml, $panoramas, PREG_SET_ORDER);
    $elements = [];
    foreach ($panoramas as $panorama) {
        $nodeId = $panorama[1];
        if (preg_match('/<userdata\s+([^>]*)\/>/i', $panorama[2], $userdataMatch)) {
            $userdata = parseXmlAttributes($userdataMatch[1]);
            $nodeId = trim((string) ($userdata['customnodeid'] ?? '')) ?: trim((string) ($userdata['nodeid'] ?? $nodeId));
        }
        preg_match_all('/<webelement\b([^>]*)\/>/is', $panorama[2], $webElements);
        foreach ($webElements[1] ?? [] as $attributes) {
            $decoded = html_entity_decode($attributes, ENT_QUOTES | ENT_XML1, 'UTF-8');
            if (!preg_match('/\bid\s*=\s*["\']([^"\']+)["\']/i', $decoded, $idMatch)) continue;
            $targetId = trim($idMatch[1]);
            if ($targetId === '') continue;
            $key = strtolower($nodeId . '|' . $targetId);
            $elements[$key] = ['nodeID' => $nodeId, 'targetID' => $targetId];
        }
    }
    return array_values($elements);
}

function parseXmlAttributes(string $text): array
{
    preg_match_all('/([a-zA-Z0-9_-]+)="([^"]*)"/', $text, $matches, PREG_SET_ORDER);
    $attributes = [];
    foreach ($matches as $match) {
        $attributes[strtolower($match[1])] = html_entity_decode($match[2], ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
    return $attributes;
}

function auditTour(string $root, array $tour): array
{
    $tourPath = $root . '/' . $tour['path'];
    $xml = file_get_contents($tourPath . '/pano.xml');
    $result = ['stats' => ['panoramas' => 0, 'hotspots' => 0, 'errors' => 0, 'warnings' => 0], 'issues' => [], 'hotspots' => [], 'languages' => [], 'displayLanguageIndexes' => [], 'translations' => []];
    if ($xml === false) {
        $result['issues'][] = ['level' => 'error', 'type' => 'File', 'location' => $tour['path'], 'message' => 'pano.xml tidak dapat dibaca.'];
        $result['stats']['errors'] = 1;
        return $result;
    }

    $addIssue = static function (string $level, string $type, string $location, string $message) use (&$result): void {
        $result['issues'][] = compact('level', 'type', 'location', 'message');
        $result['stats'][$level === 'error' ? 'errors' : 'warnings']++;
    };
    $isIgnoredTranslation = static function (string $source): bool {
        $normalized = strtolower(trim(html_entity_decode(strip_tags($source), ENT_QUOTES | ENT_XML1, 'UTF-8')));
        return in_array($normalized, ['govirtual', 'enter fullscreen', 'enter vr', 'exit fullscreen'], true);
    };

    preg_match_all('/<panorama\b[^>]*\bid="([^"]+)"[^>]*>.*?<\/panorama>/is', $xml, $panoramaMatches, PREG_SET_ORDER);
    $result['stats']['panoramas'] = count($panoramaMatches);
    $nodeIds = [];
    foreach ($panoramaMatches as $panorama) $nodeIds[$panorama[1]] = true;

    $translatedTexts = [];
    foreach ($panoramaMatches as $panorama) {
        $nodeId = $panorama[1];
        $body = $panorama[0];
        if (preg_match('/<userdata\s+([^>]*)\/>/i', $body, $userdataMatch)) {
            $userdata = parseXmlAttributes($userdataMatch[1]);
            $title = trim((string) ($userdata['title'] ?? ''));
            if ($title !== '') $translatedTexts[$title] = true;
        }

        preg_match_all('/<hotspot\s+([^>]*)\/>/i', $body, $hotspotMatches);
        foreach ($hotspotMatches[1] ?? [] as $hotspotText) {
            $result['stats']['hotspots']++;
            $hotspot = parseXmlAttributes($hotspotText);
            $hotspotId = trim((string) ($hotspot['id'] ?? 'hotspot'));
            $location = $nodeId . ' / ' . $hotspotId;
            $title = trim((string) ($hotspot['title'] ?? ''));
            $url = trim((string) ($hotspot['url'] ?? ''));
            $result['hotspots'][] = ['nodeId' => $nodeId, 'id' => $hotspotId, 'title' => $title, 'url' => $url, 'target' => trim((string) ($hotspot['target'] ?? '')), 'skinId' => trim((string) ($hotspot['skinid'] ?? ''))];
            if ($title !== '') $translatedTexts[$title] = true;
            if ($url === '') {
                $addIssue('error', 'Link kosong', $location, 'Hotspot “' . ($title !== '' ? $title : $hotspotId) . '” belum memiliki URL/link.');
            }
        }
    }

    $translations = [];
    $languages = [];
    if (preg_match('/<json>(.*?)<\/json>/is', $xml, $jsonMatch)) {
        $translationData = json_decode(html_entity_decode(trim($jsonMatch[1]), ENT_QUOTES | ENT_XML1, 'UTF-8'), true);
        if (is_array($translationData)) {
            $languages = array_values(array_filter((array) ($translationData['languages'] ?? []), 'is_string'));
            $translations = (array) ($translationData['translations'] ?? []);
        }
    }
    if ($languages === []) {
        $addIssue('error', 'Terjemahan', 'masternode', 'Data bahasa terjemahan tidak ditemukan atau tidak valid.');
    } else {
        $hasZhCn = in_array('zh-CN', $languages, true);
        foreach ($languages as $index => $language) {
            if ($hasZhCn && strtolower($language) === 'zh') continue;
            $result['displayLanguageIndexes'][] = $index;
        }
        foreach (array_keys($translatedTexts) as $sourceText) {
            if ($isIgnoredTranslation($sourceText)) continue;
            $values = $translations[$sourceText] ?? null;
            foreach ($result['displayLanguageIndexes'] as $index) {
                $language = $languages[$index];
                if (!is_array($values) || trim((string) ($values[$index] ?? '')) === '') {
                    $addIssue('warning', 'Belum diterjemahkan', $sourceText, 'Terjemahan bahasa ' . $language . ' masih kosong.');
                }
            }
        }
    }
    $result['languages'] = $languages;
    foreach ($translations as $source => $values) {
        if (!is_string($source) || !is_array($values)) continue;
        if ($isIgnoredTranslation($source)) continue;
        $plainSource = trim(strip_tags($source));
        $isDateOrTime = preg_match('/^(?:\d{1,4}[-\/.]\d{1,2}[-\/.]\d{1,4})(?:\s+\d{1,2}:\d{2}(?::\d{2})?(?:\s*[AP]M)?)?$/i', $plainSource)
            || preg_match('/^\d{1,2}:\d{2}(?::\d{2})?(?:\s*[AP]M)?$/i', $plainSource)
            || preg_match('/^\d{1,2}\s+(?:jan|feb|mar|apr|mei|may|jun|jul|agu|aug|sep|okt|oct|nov|des|dec)[a-z]*\s+\d{2,4}$/i', $plainSource);
        if (!$isDateOrTime) {
            $translationValues = array_values($values);
            $missingCount = 0;
            foreach ($result['displayLanguageIndexes'] as $index) {
                if (trim((string) ($translationValues[$index] ?? '')) === '') $missingCount++;
            }
            $result['translations'][] = ['source' => $source, 'values' => $translationValues, 'missingCount' => $missingCount];
        }
    }
    usort($result['hotspots'], static function (array $left, array $right): int {
        $leftMissing = trim((string) $left['url']) === '' ? 0 : 1;
        $rightMissing = trim((string) $right['url']) === '' ? 0 : 1;
        return $leftMissing <=> $rightMissing ?: strnatcasecmp($left['nodeId'] . $left['id'], $right['nodeId'] . $right['id']);
    });
    usort($result['translations'], static function (array $left, array $right): int {
        $leftComplete = (int) ($left['missingCount'] ?? 0) === 0 ? 1 : 0;
        $rightComplete = (int) ($right['missingCount'] ?? 0) === 0 ? 1 : 0;
        return $leftComplete <=> $rightComplete ?: strnatcasecmp((string) $left['source'], (string) $right['source']);
    });
    return $result;
}

function analyzeTourCompletion(string $root, string $id, array $tour, array $location, array $objects, bool $includeAssetAnalysis = true): array
{
    global $activeProject, $assetsRoot, $assetsBaseRoot;
    $audit = auditTour($root, $tour);
    $panoramas = readPanoramaNodes($root, $tour);
    $tourDirectory = $root . '/' . str_replace('/', DIRECTORY_SEPARATOR, $tour['path']);
    $missing = [];
    $assetBytes = 0;
    $largeAssets = [];
    if ($includeAssetAnalysis && is_dir($tourDirectory)) {
        $assetIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tourDirectory, FilesystemIterator::SKIP_DOTS));
        foreach ($assetIterator as $assetFile) {
            if (!$assetFile->isFile() || str_contains(str_replace('\\', '/', $assetFile->getPathname()), '/.skin-backups/')) continue;
            $size = $assetFile->getSize();
            $assetBytes += $size;
            $extension = strtolower($assetFile->getExtension());
            $limit = in_array($extension, ['webm', 'mp4'], true) ? 25 * 1024 * 1024 : (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'mp3'], true) ? 5 * 1024 * 1024 : 10 * 1024 * 1024);
            if ($size > $limit) $largeAssets[] = ['name' => str_replace('\\', '/', substr($assetFile->getPathname(), strlen($tourDirectory) + 1)), 'bytes' => $size];
        }
        usort($largeAssets, static fn(array $a, array $b): int => $b['bytes'] <=> $a['bytes']);
        $largeAssets = array_slice($largeAssets, 0, 5);
    }

    $panoramaScore = $panoramas === [] ? 0 : 100;
    if ($panoramaScore === 0) $missing[] = 'Panorama belum terdeteksi';

    $welcomeDone = 0;
    $welcomeTotal = count(cmsLanguages());
    $translationDone = 0;
    $translationTotal = count(cmsLanguages());
    foreach (cmsLanguages() as $code => $label) {
        if (trim((string) ($location['bahasa'][$code]['lokasi'] ?? '')) !== '') $translationDone++;
        if (trim((string) ($location['bahasa'][$code]['welcomeText'] ?? '')) !== '') $welcomeDone++;
    }
    foreach ($audit['translations'] as $translation) {
        foreach ($translation['values'] as $value) {
            $translationTotal++;
            if (trim((string) $value) !== '') $translationDone++;
        }
    }
    $welcomeScore = $welcomeTotal > 0 ? (int) round(($welcomeDone / $welcomeTotal) * 100) : 100;
    if ($welcomeScore < 100) $missing[] = ($welcomeTotal - $welcomeDone) . ' welcome text belum diisi';
    $translationScore = $translationTotal > 0 ? (int) round(($translationDone / $translationTotal) * 100) : 100;
    if ($translationScore < 100) $missing[] = ($translationTotal - $translationDone) . ' terjemahan belum diisi';

    $audioDone = 0;
    foreach (['ID', 'EN', 'CN', 'AR'] as $language) {
        if (is_file($assetsBaseRoot . '/audio/' . $id . '/' . $language . '.mp3')) $audioDone++;
    }
    $audioScore = $audioDone * 25;
    if ($audioDone < 4) $missing[] = (4 - $audioDone) . ' audio belum tersedia';

    $videoDone = (is_file($tourDirectory . '/video/mobile.webm') ? 1 : 0) + (is_file($tourDirectory . '/video/desktop.webm') ? 1 : 0);
    $videoScore = $videoDone * 50;
    if ($videoDone < 2) $missing[] = (2 - $videoDone) . ' video belum tersedia';

    $hotspotTotal = count($audit['hotspots']);
    $linkDone = max(0, $hotspotTotal - (int) $audit['stats']['errors']);
    $linkScore = $hotspotTotal > 0 ? (int) round(($linkDone / $hotspotTotal) * 100) : 100;
    if ($linkScore < 100) $missing[] = ($hotspotTotal - $linkDone) . ' link hotspot kosong';

    $webElements = readTourWebElements($root, $tour);
    $objectScore = null;
    if ($webElements !== []) {
        $objectDone = 0;
        $objectTotal = count($webElements) * (4 + count(cmsLanguages()));
        foreach ($webElements as $element) {
            $matchingObject = null;
            foreach ($objects as $object) {
                $objectNode = trim((string) ($object['nodeID'] ?? ''), '{} ');
                if (strcasecmp($objectNode, $element['nodeID']) === 0 && strcasecmp((string) ($object['targetID'] ?? ''), $element['targetID']) === 0) { $matchingObject = $object; break; }
            }
            if ($matchingObject === null) continue;
            foreach (['route_key', 'infoID', 'nodeID', 'targetID'] as $field) if (trim((string) ($matchingObject[$field] ?? '')) !== '') $objectDone++;
            foreach (array_keys(cmsLanguages()) as $language) if (trim((string) ($matchingObject['labelTeks'][$language] ?? '')) !== '') $objectDone++;
        }
        $objectScore = (int) round(($objectDone / $objectTotal) * 100);
        if ($objectScore < 100) $missing[] = ($objectTotal - $objectDone) . ' field Object Informasi belum lengkap';
    }

    $scores = ['welcome' => $welcomeScore, 'translations' => $translationScore, 'audio' => $audioScore, 'video' => $videoScore, 'links' => $linkScore, 'objects' => $objectScore];
    $category = locationCategory($id, $location);
    $searchParts = [$id, (string) ($location['bahasa']['id']['lokasi'] ?? ''), json_encode($location, JSON_UNESCAPED_UNICODE), json_encode($objects, JSON_UNESCAPED_UNICODE)];
    foreach ($panoramas as $panorama) $searchParts[] = implode(' ', array_map('strval', $panorama));
    foreach ($audit['hotspots'] as $hotspot) $searchParts[] = implode(' ', array_map('strval', $hotspot));
    return [
        'id' => $id,
        'name' => $location['bahasa']['id']['lokasi'] ?? humanizeLocationId($id),
        'path' => $tour['path'],
        'score' => (int) round(array_sum(array_filter($scores, static fn(mixed $score): bool => $score !== null)) / count(array_filter($scores, static fn(mixed $score): bool => $score !== null))),
        'scores' => $scores,
        'missing' => $missing,
        'panoramas' => count($panoramas),
        'objects' => count($webElements),
        'category' => $category,
        'local_url' => projectUrl(trim($tour['path'], '/') . '/'),
        'deploy_url' => rtrim((string) ($activeProject['deploy_url'] ?? ''), '/') . '/' . $id,
        'asset_bytes' => $assetBytes,
        'large_assets' => $largeAssets,
        'search_text' => strtolower(implode(' ', $searchParts)),
    ];
}

function tourCategory(string $id): string
{
    global $database;
    return locationCategory($id, $database[$id] ?? []);
}

// Smart Hub dan Optimasi Loading tidak memerlukan database CMS: project tanpa assets CMS (mis. Semarang) tetap dapat membukanya tanpa galat.
$lightTab = in_array((string) ($_GET['tab'] ?? ''), ['smart', 'loading'], true) && !projectAssetsValid($assetsBaseRoot);
try {
    if ($lightTab) {
        $database = []; $languageRegistry = []; $config = ['base_url' => '', 'links' => []]; $oiDatabase = []; $customCss = '';
        $detectedTours = scanTourDirectories($projectRoot);
    } else {
    $database = readJsObject($databaseFile, 'GLOBAL_DATABASE');
    $languageRegistry = languageRegistry($projectRoot);
    $config = readJsObject($configFile, 'GLOBAL_LINK_CONFIG');
    $oiDatabase = readJsObject($oiDatabaseFile, 'TourDatabase');
    $customCss = file_get_contents($customCssFile);
    if ($customCss === false) throw new RuntimeException('Tidak dapat membaca custom.css.');
    $detectedTours = scanTourDirectories($projectRoot);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && str_contains((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        $payload = json_decode((string) file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
        if (!hash_equals($_SESSION['cms_csrf'], (string) ($payload['csrf'] ?? ''))) throw new RuntimeException('Sesi formulir tidak valid.');
        $locationId = cleanKey((string) ($payload['id_lokasi'] ?? ''));
        if ($locationId === '' || !isset($database[$locationId])) throw new RuntimeException('Lokasi Object Informasi tidak valid.');
        if (!is_array($payload['records'] ?? null)) throw new RuntimeException('Data Object Informasi tidak valid.');
        $keys = []; $cleanRecords = [];
        foreach ($payload['records'] as $index => $record) {
            $routeKey = cleanKey((string) ($record['route_key'] ?? ''));
            if ($routeKey === '' || isset($keys[$routeKey])) throw new RuntimeException('Route key object #' . ($index + 1) . ' kosong atau duplikat.');
            $keys[$routeKey] = true; $labels = [];
            foreach (cmsLanguages() as $code => $label) $labels[$code] = trim((string) ($record['labelTeks'][$code] ?? ''));
            $cleanRecords[] = ['route_key' => $routeKey, 'infoID' => trim((string) ($record['infoID'] ?? '')), 'nodeID' => trim((string) ($record['nodeID'] ?? '')), 'targetID' => trim((string) ($record['targetID'] ?? '')), 'category' => trim((string) ($record['category'] ?? 'umum')), 'arah' => trim((string) ($record['arah'] ?? 'luruskanan')), 'versi' => '1', 'ukuran' => trim((string) ($record['ukuran'] ?? '500px')), 'labelTeks' => $labels, 'isActive' => (bool) ($record['isActive'] ?? true), 'title' => trim((string) ($record['title'] ?? ''))];
        }
        $oiDatabase[$locationId] = $cleanRecords;
        replaceJsObject($oiDatabaseFile, 'TourDatabase', $oiDatabase);
        echo json_encode(['success' => true, 'message' => count($cleanRecords) . ' object berhasil disimpan.']); exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['cms_csrf'], (string) ($_POST['csrf'] ?? ''))) {
            throw new RuntimeException('Sesi formulir tidak valid. Muat ulang halaman.');
        }

        $action = (string) ($_POST['action'] ?? 'save_location');
        // tindakan yang memengaruhi seluruh lokasi / keamanan: khusus admin
        if (in_array($action, ['remove_language', 'add_language', 'update_skin_template', 'sync_skin', 'save_custom_css', 'delete', 'save_config'], true)) cmsRequireAdmin('Tindakan "' . $action . '"');
        $originalId = cleanKey((string) ($_POST['original_id'] ?? ''));

        if ($action === 'remove_language') {
            $code = strtolower(cleanKey((string) ($_POST['language_code'] ?? '')));
            if ($code === 'id') throw new RuntimeException('Bahasa Indonesia adalah bahasa sumber dan tidak dapat dihapus.');
            if (!isset($languageRegistry[$code])) throw new RuntimeException('Bahasa yang akan dihapus tidak ditemukan.');
            $removedLabel = (string) $languageRegistry[$code]['label'];
            $result = removeProjectLanguage($projectRoot, $code, $languageRegistry[$code]);
            unset($languageRegistry[$code]); writeLanguageRegistry($projectRoot, $languageRegistry);
            synchronizeProjectLanguages($projectRoot, $languageRegistry);
            $_SESSION['cms_flash'] = 'Bahasa ' . $removedLabel . ' dihapus dari database, ' . $result['pano'] . ' pano.xml, dan ' . $result['skin'] . ' skin.js. File audio lama tetap disimpan.';
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?tab=languages'); exit;
        } elseif ($action === 'add_language') {
            $code = strtolower(cleanKey((string) ($_POST['language_code'] ?? '')));
            $panoCode = trim((string) ($_POST['pano_code'] ?? ''));
            $audioCode = strtoupper(cleanKey((string) ($_POST['audio_code'] ?? '')));
            if (!preg_match('/^[a-z]{2,3}$/', $code)) throw new RuntimeException('Kode internal harus 2–3 huruf kecil, misalnya ko.');
            if (!preg_match('/^[a-z]{2,3}(?:-[A-Z]{2})?$/', $panoCode)) throw new RuntimeException('Kode Pano2VR tidak valid, misalnya ko-KR.');
            if (!preg_match('/^[A-Z]{2,4}$/', $audioCode)) throw new RuntimeException('Kode audio harus 2–4 huruf, misalnya KR.');
            if (isset($languageRegistry[$code])) throw new RuntimeException('Bahasa dengan kode ' . $code . ' sudah tersedia.');
            $language = ['code' => $code, 'label' => trim((string) ($_POST['language_label'] ?? '')), 'nativeLabel' => trim((string) ($_POST['native_label'] ?? '')), 'flag' => trim((string) ($_POST['language_flag'] ?? '🌐')), 'pano' => $panoCode, 'translate' => trim((string) ($_POST['translate_code'] ?? $code)), 'webview' => trim((string) ($_POST['webview_code'] ?? $code)), 'audio' => $audioCode, 'direction' => 'ltr'];
            if ($language['label'] === '' || $language['nativeLabel'] === '') throw new RuntimeException('Nama bahasa dan nama asli wajib diisi.');
            $result = addProjectLanguage($projectRoot, $language);
            unset($language['code']); $languageRegistry[$code] = $language; writeLanguageRegistry($projectRoot, $languageRegistry);
            synchronizeProjectLanguages($projectRoot, $languageRegistry);
            $translationResult = autoTranslateProjectLanguage($projectRoot, $language);
            $_SESSION['cms_flash'] = 'Bahasa ' . $language['label'] . ' ditambahkan ke ' . $result['locations'] . ' lokasi, ' . $result['pano'] . ' pano.xml, dan ' . $result['skin'] . ' skin.js. Auto Translate mengisi ' . $translationResult['texts'] . ' teks Pano2VR.';
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?tab=languages'); exit;
        } elseif ($action === 'update_skin_template') {
            $modularSkinDirectory = $assetsBaseRoot . '/jkt';
            if (!is_file($modularSkinDirectory . '/jakarta-skin.html') || !is_file($modularSkinDirectory . '/jakarta-skin-loader.js')) throw new RuntimeException('Paket skin modular assets/jkt tidak lengkap.');
            if (!is_dir($skinTemplateDirectory) && !mkdir($skinTemplateDirectory, 0775, true) && !is_dir($skinTemplateDirectory)) throw new RuntimeException('Folder penyimpanan template gagal dibuat.');
            file_put_contents($skinTemplateDirectory . '/updated-at.txt', date(DATE_ATOM), LOCK_EX);
            $_SESSION['cms_flash'] = 'Paket skin modular assets/jkt berhasil divalidasi sebagai template aktif.';
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?tab=skin');
            exit;
        } elseif ($action === 'sync_skin') {
            // sumber skin = lokasi template project (Konfigurasi project › template_tour), bukan nama lokasi tertentu; bawaan lama: monumen-nasional
            $sourceId = $skinTemplateId !== '' ? $skinTemplateId : 'monumen-nasional';
            if (!isset($detectedTours[$sourceId])) throw new RuntimeException('Folder lokasi template "' . $sourceId . '" tidak ditemukan. Atur template project terlebih dahulu.');
            $sourceDirectory = $projectRoot . '/' . str_replace('/', DIRECTORY_SEPARATOR, $detectedTours[$sourceId]['path']);
            $sourceSkin = $sourceDirectory . '/skin.js';
            if (!is_file($sourceSkin)) throw new RuntimeException('skin.js lokasi template ' . $sourceId . ' tidak ditemukan.');
            $skinContent = (string) file_get_contents($sourceSkin);
            $targets = array_keys($detectedTours);
            $synced = 0;
            foreach ($targets as $targetId) {
                if ($targetId === $sourceId) continue;
                if (!isset($detectedTours[$targetId])) continue;
                $targetDirectory = $projectRoot . '/' . str_replace('/', DIRECTORY_SEPARATOR, $detectedTours[$targetId]['path']);
                $targetSkin = $targetDirectory . '/skin.js';
                if (!is_file($targetSkin)) continue;
                $backupDirectory = $targetDirectory . '/.skin-backups';
                if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) throw new RuntimeException('Folder backup gagal dibuat untuk ' . $targetId . '.');
                $stamp = date('Ymd-His');
                if (!copy($targetSkin, $backupDirectory . '/' . $stamp . '-skin.js')) throw new RuntimeException('Backup skin gagal untuk ' . $targetId . '.');
                if (file_put_contents($targetSkin, $skinContent, LOCK_EX) === false) throw new RuntimeException('skin.js gagal diterapkan ke ' . $targetId . '.');
                $sourcePlayer = $sourceDirectory . '/pano2vr_player.js';
                if (is_file($sourcePlayer)) {
                    $targetPlayer = $targetDirectory . '/pano2vr_player.js';
                    if (is_file($targetPlayer) && !copy($targetPlayer, $backupDirectory . '/' . $stamp . '-pano2vr_player.js')) throw new RuntimeException('Backup player gagal untuk ' . $targetId . '.');
                    if (!copy($sourcePlayer, $targetPlayer)) throw new RuntimeException('Player gagal diterapkan ke ' . $targetId . '.');
                }
                preg_match_all('~images/([A-Za-z0-9_.-]+\.(?:png|webp|json|svg|jpe?g|gif))~i', $skinContent, $skinImageMatches);
                $targetImages = $targetDirectory . '/images';
                if (!is_dir($targetImages) && !mkdir($targetImages, 0775, true) && !is_dir($targetImages)) throw new RuntimeException('Folder images gagal dibuat untuk ' . $targetId . '.');
                foreach (array_unique($skinImageMatches[1] ?? []) as $imageName) {
                    $sourceImage = $sourceDirectory . '/images/' . $imageName;
                    if (is_file($sourceImage) && !copy($sourceImage, $targetImages . '/' . $imageName)) throw new RuntimeException('Asset skin gagal disalin: ' . $imageName);
                }
                copyDirectoryContents($sourceDirectory . '/3rdparty', $targetDirectory . '/3rdparty');
                $targetIndex = $targetDirectory . '/index.html';
                if (is_file($targetIndex)) {
                    if (!copy($targetIndex, $backupDirectory . '/' . date('Ymd-His') . '-index.html')) throw new RuntimeException('Backup index.html gagal untuk ' . $targetId . '.');
                    ensureTourBootstrap($targetIndex);
                }
                $targetPano = $targetDirectory . '/pano.xml';
                if (is_file($targetPano)) {
                    if (!copy($targetPano, $backupDirectory . '/' . date('Ymd-His') . '-pano.xml')) throw new RuntimeException('Backup pano.xml gagal untuk ' . $targetId . '.');
                    enableTourSounds($targetPano);
                    ensureTourMap($targetPano);
                }
                $synced++;
            }
            $_SESSION['cms_flash'] = $synced . ' lokasi berhasil memakai skin.js ' . humanizeLocationId($sourceId) . '. File lama telah dicadangkan.';
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?tab=skin');
            exit;
        } elseif ($action === 'save_custom_css') {
            $submittedCss = (string) ($_POST['custom_css'] ?? '');
            if (str_contains($submittedCss, "\0")) {
                throw new RuntimeException('Isi Custom CSS tidak valid.');
            }
            if (file_put_contents($customCssFile, $submittedCss, LOCK_EX) === false) {
                throw new RuntimeException('Custom CSS gagal disimpan. Periksa izin file assets/custom.css.');
            }
            $_SESSION['cms_flash'] = 'Custom CSS berhasil disimpan.';
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?tab=custom');
            exit;
        } elseif ($action === 'delete') {
            if ($originalId === '' || !isset($database[$originalId])) {
                throw new RuntimeException('Lokasi yang akan dihapus tidak ditemukan.');
            }
            unset($database[$originalId]);
            replaceJsObject($databaseFile, 'GLOBAL_DATABASE', $database);
            $_SESSION['cms_flash'] = 'Lokasi berhasil dihapus.';
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?tab=lokasi');
            exit;
        } elseif ($action === 'save_location') {
            $id = cleanKey((string) ($_POST['id_lokasi'] ?? ''));
            if ($id === '') {
                throw new RuntimeException('ID lokasi wajib diisi dengan huruf, angka, tanda hubung, atau garis bawah.');
            }
            if ($originalId !== '' && $originalId !== $id) {
                unset($database[$originalId]);
            }

            $languageData = [];
            foreach (cmsLanguages() as $code => $label) {
                $languageData[$code] = [
                    'lokasi' => trim((string) ($_POST['lokasi'][$code] ?? '')),
                    'welcomeText' => trim((string) ($_POST['welcomeText'][$code] ?? '')),
                ];
            }

            $database[$id] = [
                'id_lokasi' => $id,
                'drone' => trim((string) ($_POST['drone'] ?? '')),
                'drone_pan' => round((float) ($_POST['drone_pan'] ?? 0), 2),
                'drone_tilt' => round((float) ($_POST['drone_tilt'] ?? 0), 2),
                'drone_fov' => max(1, min(179, round((float) ($_POST['drone_fov'] ?? 70), 2))),
                'has_drone' => isset($_POST['has_drone']) && $_POST['has_drone'] === '1',
                'bahasa' => $languageData,
            ];
            replaceJsObject($databaseFile, 'GLOBAL_DATABASE', $database);
            $_SESSION['cms_flash'] = 'Data lokasi berhasil disimpan.';
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?tab=lokasi&lokasi=' . rawurlencode($id));
            exit;
        } elseif ($action === 'save_config') {
            $baseUrl = filter_var(trim((string) ($_POST['base_url'] ?? '')), FILTER_VALIDATE_URL);
            if ($baseUrl === false || !in_array(parse_url($baseUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
                throw new RuntimeException('Base URL harus berupa URL HTTP/HTTPS yang valid.');
            }
            $config['base_url'] = rtrim($baseUrl, '/');

            $submittedLinks = (array) ($_POST['links'] ?? []);
            if (count($submittedLinks) > 50) throw new RuntimeException('Jumlah endpoint maksimal 50.');
            $cleanLinks = [];
            foreach ($submittedLinks as $submittedName => $link) {
                $name = cleanKey((string) $submittedName);
                $page = cleanKey((string) ($link['page'] ?? ''));
                $param = cleanKey((string) ($link['param'] ?? ''));
                $label = trim(strip_tags((string) ($link['label'] ?? humanizeLocationId($name))));
                if ($page === '' || $param === '') {
                    throw new RuntimeException('Nama halaman dan parameter link tidak boleh kosong.');
                }
                if ($name === '' || isset($cleanLinks[$name])) throw new RuntimeException('Nama variabel endpoint kosong atau duplikat.');
                $cleanLinks[$name] = ['page' => $page, 'param' => $param, 'label' => $label !== '' ? $label : humanizeLocationId($name)];
            }
            $config['links'] = $cleanLinks;

            $submittedCategories = (array) ($_POST['categories'] ?? []);
            $cleanCategories = [];
            foreach ($submittedCategories as $category) {
                $label = trim(strip_tags((string) $category));
                $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $label), '-'));
                if ($slug !== '' && $label !== '') $cleanCategories[$slug] = $label;
            }
            updateProjectCategories((string) $activeProject['id'], $cleanCategories);

            replaceJsObject($configFile, 'GLOBAL_LINK_CONFIG', $config);
            $_SESSION['cms_flash'] = 'Konfigurasi global berhasil disimpan.';
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?tab=config');
            exit;
        }
    }

    $message = (string) ($_SESSION['cms_flash'] ?? '');
    unset($_SESSION['cms_flash']);
} catch (Throwable $exception) {
    $error = $exception->getMessage();
    $database = $database ?? [];
    $config = $config ?? ['base_url' => '', 'links' => []];
    $languageRegistry = $languageRegistry ?? [];
    $detectedTours = $detectedTours ?? [];
    $oiDatabase = $oiDatabase ?? [];
    $customCss = $customCss ?? '';
}

$selectedId = cleanKey((string) ($_GET['lokasi'] ?? array_key_first($detectedTours) ?? array_key_first($database) ?? ''));
$requestedTab = (string) ($_GET['tab'] ?? 'dashboard');
$activeTab = in_array($requestedTab, ['dashboard', 'translations', 'lokasi', 'config', 'languages', 'custom', 'skin', 'object', 'voice', 'video', 'video360', 'optimizer', 'audit', 'smart', 'loading'], true) ? $requestedTab : 'dashboard';
$smartHubState = null;
if ($activeTab === 'smart') {
    // Smart Hub: integrasi Smart Kit untuk wilayah project aktif (lihat smart-hub.php)
    if (!defined('SMART_HUB_INCLUDE_ONLY')) define('SMART_HUB_INCLUDE_ONLY', true);
    require_once __DIR__ . '/smart-hub.php';
    try { $smartHubState = shState($activeProject); } catch (Throwable $smartError) { $error = $smartError->getMessage(); }
}
$selected = $database[$selectedId] ?? [
    'id_lokasi' => $selectedId === 'baru' ? '' : $selectedId,
    'drone' => '',
    'drone_pan' => 0,
    'drone_tilt' => 0,
    'drone_fov' => 70,
    'has_drone' => false,
    'category' => 'uncategorized',
    'bahasa' => [
        'id' => ['lokasi' => $selectedId === 'baru' ? '' : humanizeLocationId($selectedId), 'welcomeText' => ''],
    ],
];
$sidebarLocations = [];
foreach ($detectedTours as $id => $tour) {
    $sidebarLocations[$id] = $database[$id] ?? [
        'id_lokasi' => $id,
        'drone' => '',
        'has_drone' => false,
        'bahasa' => ['id' => ['lokasi' => humanizeLocationId($id), 'welcomeText' => '']],
    ];
}
uksort($sidebarLocations, 'strnatcasecmp');
$selectedPanoramas = isset($detectedTours[$selectedId]) ? readPanoramaNodes($projectRoot, $detectedTours[$selectedId]) : [];
$selectedWebElements = isset($detectedTours[$selectedId]) ? readTourWebElements($projectRoot, $detectedTours[$selectedId]) : [];
$oiConfigContent = is_file($assetsRoot . '/oi-config.js') ? (string) file_get_contents($assetsRoot . '/oi-config.js') : '';
$oiPreviewBaseUrl = preg_match("/baseUrl\s*:\s*['\"]([^'\"]+)['\"]/", $oiConfigContent, $oiBaseMatch) ? $oiBaseMatch[1] : 'https://webview-jakarta.govirtual.id/objinfo.php';
$auditId = $selectedId;
$auditResult = $activeTab === 'audit' && isset($detectedTours[$auditId]) ? auditTour($projectRoot, $detectedTours[$auditId]) : null;
$voiceAudioAvailability = [];
foreach ($languageRegistry as $code => $language) {
    $fileCode = $language['audio'];
    $voiceAudioAvailability[$code] = is_file($assetsBaseRoot . '/audio/' . $selectedId . '/' . $fileCode . '.mp3');
}
$skinTemplateFile = $assetsBaseRoot . '/jkt/jakarta-skin.html';
$skinTemplateUpdatedAt = is_file($skinTemplateDirectory . '/updated-at.txt') ? trim((string) file_get_contents($skinTemplateDirectory . '/updated-at.txt')) : '';
$skinStatuses = [];
$video360Items = [];
$video360Root = $assetsBaseRoot . '/360/videos';
if (is_dir($video360Root)) foreach (new DirectoryIterator($video360Root) as $entry) {
    if ($entry->isDot() || !$entry->isDir()) continue;
    $videoId = $entry->getFilename();
    if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/i', $videoId)) continue;
    $manifest = $entry->getPathname() . '/master.m3u8';
    $video360Items[$videoId] = ['ready' => is_file($manifest), 'bytes' => is_file($manifest) ? filesize($manifest) : 0, 'code' => 'javascript:window.GV360.open("' . $videoId . '");void(0)'];
}
uksort($video360Items, 'strnatcasecmp');
if ($activeTab === 'skin') {
    $modularPackageReady = is_file($skinTemplateFile) && is_file($assetsBaseRoot . '/jkt/jakarta-skin-loader.js') && is_dir($assetsBaseRoot . '/jkt/image-skin');
    foreach ($detectedTours as $id => $tour) {
        $tourDirectory = $projectRoot . '/' . str_replace('/', DIRECTORY_SEPARATOR, $tour['path']);
        $skinFile = $tourDirectory . '/skin.js';
        $indexHtml = is_file($tourDirectory . '/index.html') ? (string) file_get_contents($tourDirectory . '/index.html') : '';
        $panoXml = is_file($tourDirectory . '/pano.xml') ? (string) file_get_contents($tourDirectory . '/pano.xml') : '';
        $runtimeUrlPrefix = $runtimeDirectoryName === $assetsDirectoryName ? '' : $runtimeDirectoryName . '/';
        $bootstrapReady = str_contains($indexHtml, '../' . $assetsDirectoryName . '/' . $runtimeUrlPrefix . 'main-loader.js') && str_contains($indexHtml, '../' . $assetsDirectoryName . '/' . $runtimeUrlPrefix . 'custom.css') && str_contains($indexHtml, '3rdparty/leaflet/leaflet.js');
        $mapReady = (bool) preg_match('/<map\b[^>]*\bid="Map01"[^>]*\/?\s*>/i', $panoXml);
        $skinStatuses[$id] = ['same' => $modularPackageReady && $bootstrapReady && $mapReady, 'skinMatches' => $modularPackageReady, 'missingAssets' => 0, 'bootstrapReady' => $bootstrapReady, 'mapReady' => $mapReady, 'size' => is_file($skinFile) ? filesize($skinFile) : 0];
    }
}
$dashboardTours = [];
$sidebarTourStatus = [];
if ($activeTab === 'dashboard') {
    foreach ($detectedTours as $id => $tour) {
        // Tile panorama dapat berjumlah puluhan ribu. Jangan scan seluruh file
        // hanya untuk menghitung ukuran ketika dashboard dibuka.
        $dashboardTours[] = analyzeTourCompletion($projectRoot, $id, $tour, $database[$id] ?? [], $oiDatabase[$id] ?? [], false);
    }
    usort($dashboardTours, static fn(array $a, array $b): int => $a['score'] <=> $b['score'] ?: strnatcasecmp((string) $a['name'], (string) $b['name']));
} elseif (!in_array($activeTab, ['config', 'languages', 'translations', 'custom', 'skin', 'smart', 'loading', 'video360'], true)) {   // tab tanpa daftar status per lokasi: jangan mengurai pano.xml semua tur
    foreach ($detectedTours as $id => $tour) {
        $sidebarTourStatus[$id] = analyzeTourCompletion($projectRoot, $id, $tour, $database[$id] ?? [], $oiDatabase[$id] ?? [], false);
    }
}

function e(mixed $value): string
{
    return cmsEscape($value);
}
?>
<?php require __DIR__ . '/views/app.php';
