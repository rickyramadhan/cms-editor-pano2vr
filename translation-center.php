<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
require_once __DIR__ . '/language-manager.php';
require_once __DIR__ . '/project-manager.php';
header('Content-Type: application/json; charset=utf-8');

function tcRespond(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function tcTourFiles(string $root): array
{
    $result = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
        if (!$file->isFile() || strtolower($file->getFilename()) !== 'pano.xml') continue;
        $path = str_replace('\\', '/', $file->getPathname());
        if (str_contains($path, '/cms/') || str_contains($path, '/assets/') || str_contains($path, '/.language-backups/') || str_contains($path, '/.skin-backups/')) continue;
        $directory = dirname($file->getPathname());
        if (!is_file($directory . '/skin.js')) continue;
        $relative = str_replace('\\', '/', substr($directory, strlen($root) + 1));
        $id = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', basename($directory)));
        if ($id !== '') $result[$id] = ['id' => $id, 'path' => $relative, 'pano' => $file->getPathname(), 'skin' => $directory . '/skin.js'];
    }
    uksort($result, 'strnatcasecmp');
    return $result;
}

function tcReadTranslation(string $file, bool $xml): ?array
{
    $content = file_get_contents($file);
    if ($content === false) return null;
    if ($xml) {
        if (!preg_match('/<translations>.*?<json>(.*?)<\/json>/s', $content, $match)) return null;
        $data = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_XML1, 'UTF-8'), true, 512, JSON_THROW_ON_ERROR);
        return ['content' => $content, 'data' => $data, 'offset' => strpos($content, $match[1]), 'length' => strlen($match[1]), 'xml' => true];
    }
    $marker = 'player.addTranslations(';
    $position = strpos($content, $marker);
    if ($position === false) return null;
    $object = languageJsonObject($content, $position + strlen($marker));
    if ($object === null) return null;
    return ['content' => $content, 'data' => json_decode($object['json'], true, 512, JSON_THROW_ON_ERROR), 'offset' => $object['offset'], 'length' => $object['length'], 'xml' => false];
}

function tcLanguageIndex(array $data, string $panoCode): int|false
{
    $languages = array_values((array) ($data['languages'] ?? []));
    $index = array_search($panoCode, $languages, true);
    if ($index !== false) return $index;
    $base = strtolower(explode('-', $panoCode)[0]);
    foreach ($languages as $candidateIndex => $candidate) if (strtolower(explode('-', (string) $candidate)[0]) === $base) return $candidateIndex;
    return false;
}

function tcBackupAndWrite(string $file, string $content): void
{
    $backupDirectory = dirname($file) . '/.language-backups';
    if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) throw new RuntimeException('Folder backup gagal dibuat.');
    $backup = $backupDirectory . '/' . date('Ymd-His') . '-translation-center-' . basename($file);
    if (!copy($file, $backup)) throw new RuntimeException('Backup gagal dibuat untuk ' . basename($file));
    // LOCK_EX is portable on the Windows/XAMPP deployment; the verified backup
    // makes the operation recoverable if the write is interrupted.
    if (file_put_contents($file, $content, LOCK_EX) === false) throw new RuntimeException('Gagal menyimpan ' . basename($file));
}

try {
    $root = activeProjectRoot();
    $registry = languageRegistry($root);
    $assetsRoot = activeProjectAssetsRoot();
    $databaseFile = $assetsRoot . '/global-database.js';
    $database = languageReadJs($databaseFile, 'GLOBAL_DATABASE');
    $skinTranslationFile = $assetsRoot . '/skin-translations.js';
    $skinTranslations = is_file($skinTranslationFile) ? languageReadJs($skinTranslationFile, 'JAKARTA_SKIN_TRANSLATIONS') : [];
    $tours = tcTourFiles($root);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = json_decode((string) file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
        if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) tcRespond(403, ['success' => false, 'message' => 'Sesi formulir tidak valid.']);
        $languageCode = strtolower((string) ($payload['language'] ?? ''));
        if (!isset($registry[$languageCode])) tcRespond(422, ['success' => false, 'message' => 'Bahasa tidak valid.']);
        $changes = (array) ($payload['changes'] ?? []);
        if (count($changes) > 500) tcRespond(422, ['success' => false, 'message' => 'Maksimal 500 perubahan per penyimpanan.']);
        $databaseChanged = false; $skinChanged = false; $fileChanges = []; $saved = 0;
        foreach ($changes as $change) {
            $sourceType = (string) ($change['sourceType'] ?? ''); $location = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', (string) ($change['location'] ?? ''))); $key = (string) ($change['key'] ?? ''); $value = trim((string) ($change['value'] ?? ''));
            if ($sourceType === 'database' && isset($database[$location]) && in_array($key, ['lokasi', 'welcomeText'], true)) {
                $database[$location]['bahasa'][$languageCode][$key] = $value; $databaseChanged = true; $saved++; continue;
            }
            if ($sourceType === 'skin' && $location === '__skin__' && array_key_exists($key, (array) ($skinTranslations['id'] ?? []))) {
                if (!isset($skinTranslations[$languageCode]) || !is_array($skinTranslations[$languageCode])) $skinTranslations[$languageCode] = [];
                $skinTranslations[$languageCode][$key] = $value; $skinChanged = true; $saved++; continue;
            }
            if (!in_array($sourceType, ['pano', 'skin'], true) || !isset($tours[$location])) continue;
            $file = $tours[$location][$sourceType]; $cacheKey = $sourceType . ':' . $location;
            if (!isset($fileChanges[$cacheKey])) $fileChanges[$cacheKey] = tcReadTranslation($file, $sourceType === 'pano');
            $entry =& $fileChanges[$cacheKey]; if ($entry === null) continue;
            $index = tcLanguageIndex($entry['data'], (string) $registry[$languageCode]['pano']); if ($index === false || !isset($entry['data']['translations'][$key]) || !is_array($entry['data']['translations'][$key])) continue;
            // Only the requested language slot is changed; Pano2VR's reserved trailing value is untouched.
            $entry['data']['translations'][$key][$index] = $value; $saved++;
        }
        if ($databaseChanged) { $backup = $assetsRoot . '/.language-backups'; if (!is_dir($backup)) mkdir($backup, 0775, true); copy($databaseFile, $backup . '/' . date('Ymd-His') . '-translation-center-global-database.js'); languageWriteJs($databaseFile, 'GLOBAL_DATABASE', $database); }
        if ($skinChanged) { $backup = $assetsRoot . '/.language-backups'; if (!is_dir($backup)) mkdir($backup, 0775, true); copy($skinTranslationFile, $backup . '/' . date('Ymd-His') . '-translation-center-skin-translations.js'); languageWriteJs($skinTranslationFile, 'JAKARTA_SKIN_TRANSLATIONS', $skinTranslations); }
        // Write only after every submitted source has been parsed and validated.
        foreach ($fileChanges as $cacheKey => $entry) {
            if ($entry === null) continue;
            [$sourceType, $location] = explode(':', $cacheKey, 2); $file = $tours[$location][$sourceType];
            $json = json_encode($entry['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $replacement = $entry['xml'] ? htmlspecialchars($json, ENT_NOQUOTES | ENT_XML1, 'UTF-8') : $json;
            tcBackupAndWrite($file, substr_replace($entry['content'], $replacement, $entry['offset'], $entry['length']));
        }
        tcRespond(200, ['success' => true, 'message' => $saved . ' terjemahan berhasil disimpan. Backup otomatis telah dibuat.']);
    }

    $languageCode = strtolower((string) ($_GET['language'] ?? array_key_first($registry)));
    if (!isset($registry[$languageCode])) $languageCode = (string) array_key_first($registry);
    $sourceFilter = (string) ($_GET['source'] ?? 'all'); $locationFilter = (string) ($_GET['location'] ?? 'all'); $search = mb_strtolower(trim((string) ($_GET['search'] ?? '')), 'UTF-8'); $missingOnly = ($_GET['missing'] ?? '') === '1';
    $rows = [];
    foreach ($database as $id => $location) foreach (['lokasi' => 'Nama lokasi', 'welcomeText' => 'Welcome text'] as $field => $label) {
        $value = (string) ($location['bahasa'][$languageCode][$field] ?? ''); $source = (string) ($location['bahasa']['id'][$field] ?? '');
        $rows[] = ['sourceType' => 'database', 'location' => $id, 'locationLabel' => (string) ($location['bahasa']['id']['lokasi'] ?? $id), 'key' => $field, 'label' => $label, 'source' => $source, 'value' => $value];
    }
    foreach ((array) ($skinTranslations['id'] ?? []) as $key => $source) {
        $rows[] = ['sourceType' => 'skin', 'location' => '__skin__', 'locationLabel' => 'Skin Jakarta HTML', 'key' => (string) $key, 'label' => 'assets/jkt HTML skin', 'source' => (string) $source, 'value' => (string) ($skinTranslations[$languageCode][$key] ?? '')];
    }
    foreach ($tours as $id => $tour) foreach (['pano' => true, 'skin' => false] as $sourceType => $xml) {
        $entry = tcReadTranslation($tour[$sourceType], $xml); if ($entry === null) continue; $index = tcLanguageIndex($entry['data'], (string) $registry[$languageCode]['pano']);
        foreach ((array) ($entry['data']['translations'] ?? []) as $key => $values) if (is_array($values)) $rows[] = ['sourceType' => $sourceType, 'location' => $id, 'locationLabel' => (string) ($database[$id]['bahasa']['id']['lokasi'] ?? $id), 'key' => (string) $key, 'label' => $sourceType === 'pano' ? 'pano.xml' : 'skin.js', 'source' => (string) $key, 'value' => $index === false ? '' : (string) ($values[$index] ?? '')];
    }
    $total = count($rows); $completed = count(array_filter($rows, static fn(array $row): bool => trim($row['value']) !== ''));
    $sourceCounts = ['database' => 0, 'pano' => 0, 'skin' => 0]; foreach ($rows as $row) $sourceCounts[$row['sourceType']]++;
    $rows = array_values(array_filter($rows, static function (array $row) use ($sourceFilter, $locationFilter, $search, $missingOnly): bool {
        if ($sourceFilter !== 'all' && $row['sourceType'] !== $sourceFilter) return false;
        if ($locationFilter !== 'all' && $row['location'] !== $locationFilter) return false;
        if ($missingOnly && trim($row['value']) !== '') return false;
        return $search === '' || str_contains(mb_strtolower($row['locationLabel'] . ' ' . $row['location'] . ' ' . $row['source'] . ' ' . $row['value'], 'UTF-8'), $search);
    }));
    $page = max(1, (int) ($_GET['page'] ?? 1)); $perPage = 100; $filtered = count($rows); $rows = array_slice($rows, ($page - 1) * $perPage, $perPage);
    tcRespond(200, ['success' => true, 'rows' => $rows, 'stats' => compact('total', 'completed', 'filtered', 'sourceCounts'), 'page' => $page, 'pages' => max(1, (int) ceil($filtered / $perPage)), 'locations' => array_map(static fn(array $tour): string => $tour['path'], $tours)]);
} catch (Throwable $error) {
    tcRespond(400, ['success' => false, 'message' => $error->getMessage()]);
}
