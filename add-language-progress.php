<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
// Translation providers may require several sequential requests for large tours.
// Keep this JSON endpoint alive and prevent PHP diagnostics from corrupting JSON.
@set_time_limit(900);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';
require_once __DIR__ . '/language-manager.php';

function languageProgressRespond(int $status, bool $success, string $message, array $extra = []): never
{
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function languageProgressPayload(): array
{
    return json_decode((string) file_get_contents('php://input'), true, 64, JSON_THROW_ON_ERROR);
}

try {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') languageProgressRespond(405, false, 'Metode tidak diizinkan.');
    $payload = languageProgressPayload();
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) languageProgressRespond(403, false, 'Sesi CMS tidak valid. Muat ulang halaman.');

    $root = activeProjectRoot();
    $assetsRoot = assetsRootForProjectPath($root);
    $action = (string) ($payload['action'] ?? '');
    $code = strtolower((string) preg_replace('/[^a-z]/', '', (string) ($payload['code'] ?? '')));

    if ($action === 'initialize') {
        $pano = trim((string) ($payload['pano'] ?? ''));
        $audio = strtoupper((string) preg_replace('/[^a-z]/i', '', (string) ($payload['audio'] ?? '')));
        if (!preg_match('/^[a-z]{2,3}$/', $code) || !preg_match('/^[a-z]{2,3}(?:-[A-Z]{2})?$/', $pano) || !preg_match('/^[A-Z]{2,4}$/', $audio)) languageProgressRespond(422, false, 'Konfigurasi bahasa tidak valid.');
        $registry = languageRegistry($root);
        if (isset($registry[$code])) {
            $database = languageReadJs($assetsRoot . '/global-database.js', 'GLOBAL_DATABASE');
            languageProgressRespond(200, true, 'Melanjutkan proses bahasa yang sudah terdaftar.', ['locations' => array_keys($database), 'result' => ['resumed' => true]]);
        }
        $language = [
            'code' => $code,
            'label' => trim((string) ($payload['label'] ?? '')),
            'nativeLabel' => trim((string) ($payload['nativeLabel'] ?? '')),
            'flag' => trim((string) ($payload['flag'] ?? '🌐')),
            'pano' => $pano,
            'translate' => trim((string) ($payload['translate'] ?? $code)),
            'webview' => trim((string) ($payload['webview'] ?? $code)),
            'audio' => $audio,
            'direction' => in_array(($payload['direction'] ?? 'ltr'), ['ltr', 'rtl'], true) ? (string) $payload['direction'] : 'ltr',
        ];
        if ($language['label'] === '' || $language['nativeLabel'] === '') languageProgressRespond(422, false, 'Nama bahasa tidak lengkap.');
        $result = addProjectLanguage($root, $language);
        unset($language['code']);
        $registry[$code] = $language;
        writeLanguageRegistry($root, $registry);
        synchronizeProjectLanguages($root, $registry);
        $database = languageReadJs($assetsRoot . '/global-database.js', 'GLOBAL_DATABASE');
        languageProgressRespond(200, true, 'Bahasa terdaftar dan slot welcome text dibuat.', ['locations' => array_keys($database), 'result' => $result]);
    }

    $registry = languageRegistry($root);
    if ($code === 'id' || !isset($registry[$code])) languageProgressRespond(422, false, 'Bahasa target tidak ditemukan.');
    $target = (string) ($registry[$code]['translate'] ?? $code);

    if ($action === 'translate_location') {
        $locationId = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', (string) ($payload['location'] ?? '')));
        $databaseFile = $assetsRoot . '/global-database.js';
        $database = languageReadJs($databaseFile, 'GLOBAL_DATABASE');
        if (!isset($database[$locationId])) languageProgressRespond(404, false, 'Lokasi tidak ditemukan.');
        $database[$locationId]['bahasa'][$code] ??= ['lokasi' => '', 'welcomeText' => ''];
        $translated = 0;
        foreach (['lokasi', 'welcomeText'] as $field) {
            if (trim((string) ($database[$locationId]['bahasa'][$code][$field] ?? '')) !== '') continue;
            $source = trim((string) ($database[$locationId]['bahasa']['id'][$field] ?? ''));
            if ($source === '') continue;
            $value = translatePanoText($source, $target);
            if ($value !== '') { $database[$locationId]['bahasa'][$code][$field] = $value; $translated++; }
        }
        languageWriteJs($databaseFile, 'GLOBAL_DATABASE', $database);

        $oiFile = $assetsRoot . '/oi-database.js';
        $objectsTranslated = 0;
        if (is_file($oiFile)) {
            $objects = languageReadJs($oiFile, 'TourDatabase');
            if (isset($objects[$locationId]) && is_array($objects[$locationId])) {
                foreach ($objects[$locationId] as &$record) {
                    if (trim((string) ($record['labelTeks'][$code] ?? '')) !== '') continue;
                    $source = trim((string) ($record['labelTeks']['id'] ?? ''));
                    if ($source === '') continue;
                    $value = translatePanoText($source, $target);
                    if ($value !== '') { $record['labelTeks'][$code] = $value; $objectsTranslated++; }
                }
                unset($record);
            }
            languageWriteJs($oiFile, 'TourDatabase', $objects);
        }
        languageProgressRespond(200, true, 'Teks lokasi selesai.', ['translated' => $translated + $objectsTranslated]);
    }

    if ($action === 'translate_assets') {
        $result = autoTranslateProjectLanguage($root, $registry[$code]);
        $skinFile = $assetsRoot . '/skin-translations.js';
        $skinCount = 0;
        if (is_file($skinFile)) {
            $skin = languageReadJs($skinFile, 'JAKARTA_SKIN_TRANSLATIONS');
            $skin[$code] ??= [];
            foreach ((array) ($skin['id'] ?? []) as $key => $source) {
                if (trim((string) ($skin[$code][$key] ?? '')) !== '' || !languageCanAutoTranslate((string) $source)) continue;
                $value = translatePanoText((string) $source, $target);
                if ($value !== '') { $skin[$code][$key] = $value; $skinCount++; }
            }
            languageWriteJs($skinFile, 'JAKARTA_SKIN_TRANSLATIONS', $skin);
        }
        languageProgressRespond(200, true, 'Seluruh teks virtual tour selesai diterjemahkan.', ['texts' => $result['texts'] + $skinCount]);
    }

    languageProgressRespond(422, false, 'Tahap proses tidak valid.');
} catch (JsonException) {
    languageProgressRespond(400, false, 'Format permintaan tidak valid.');
} catch (Throwable $error) {
    languageProgressRespond(500, false, $error->getMessage());
}
