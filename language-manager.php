<?php
declare(strict_types=1);

function languageRegistry(string $root): array
{
    return json_decode((string) file_get_contents(assetsRootForProjectPath($root) . '/languages.json'), true, 512, JSON_THROW_ON_ERROR);
}

function writeLanguageRegistry(string $root, array $languages): void
{
    $json = json_encode($languages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $assetsRoot = assetsRootForProjectPath($root);
    if (file_put_contents($assetsRoot . '/languages.json', $json . PHP_EOL, LOCK_EX) === false) throw new RuntimeException('Registry bahasa gagal disimpan.');
    $js = "// Registry bahasa bersama untuk Virtual Tour Jakarta dan CMS.\nwindow.GLOBAL_LANGUAGES = " . $json . ";\n";
    if (file_put_contents($assetsRoot . '/languages.js', $js, LOCK_EX) === false) throw new RuntimeException('Registry runtime gagal disimpan.');
}

function synchronizeHtmlSkinLanguages(string $root, array $registry): int
{
    $file = assetsRootForProjectPath($root) . '/skin-translations.js';
    if (!is_file($file)) return 0;
    $translations = languageReadJs($file, 'JAKARTA_SKIN_TRANSLATIONS');
    $source = (array) ($translations['id'] ?? []);
    foreach ($registry as $code => $_settings) if (!isset($translations[$code]) || !is_array($translations[$code])) $translations[$code] = $code === 'id' ? $source : [];
    foreach (array_keys($translations) as $code) if (!isset($registry[$code])) unset($translations[$code]);
    languageWriteJs($file, 'JAKARTA_SKIN_TRANSLATIONS', $translations);
    return count($source);
}

function languageReadJs(string $file, string $variable): array
{
    $content = (string) file_get_contents($file);
    if (!preg_match('/window\.' . preg_quote($variable, '/') . '\s*=\s*(\{.*?\}|\[.*?\])\s*;/s', $content, $matches)) throw new RuntimeException('Format ' . basename($file) . ' tidak dikenali.');
    return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
}

function languageWriteJs(string $file, string $variable, array $data): void
{
    $content = (string) file_get_contents($file);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $updated = preg_replace('/window\.' . preg_quote($variable, '/') . '\s*=\s*(?:\{.*?\}|\[.*?\])\s*;/s', 'window.' . $variable . ' = ' . $json . ';', $content, 1, $count);
    if ($updated === null || $count !== 1 || file_put_contents($file, $updated, LOCK_EX) === false) throw new RuntimeException('Gagal memperbarui ' . basename($file));
}

function languageJsonObject(string $content, int $start): ?array
{
    while (isset($content[$start]) && ctype_space($content[$start])) $start++;
    if (($content[$start] ?? '') !== '{') return null;
    $depth = 0; $string = false; $escaped = false; $length = strlen($content);
    for ($i = $start; $i < $length; $i++) {
        $char = $content[$i];
        if ($string) { if ($escaped) $escaped = false; elseif ($char === '\\') $escaped = true; elseif ($char === '"') $string = false; continue; }
        if ($char === '"') $string = true; elseif ($char === '{') $depth++; elseif ($char === '}' && --$depth === 0) return ['offset' => $start, 'length' => $i - $start + 1, 'json' => substr($content, $start, $i - $start + 1)];
    }
    return null;
}

function migrateTranslationFile(string $file, string $panoCode, bool $xml): bool
{
    $content = (string) file_get_contents($file);
    if ($xml) {
        if (!preg_match('/<translations>.*?<json>(.*?)<\/json>/s', $content, $match)) {
            $end = strrpos($content, '</tour>'); if ($end === false) return false;
            $payload = htmlspecialchars(json_encode(['languages' => [$panoCode], 'projectLanguage' => 'id-ID', 'translations' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_NOQUOTES | ENT_XML1, 'UTF-8');
            $block = "  <translations>\n    <json>{$payload}</json>\n    <projecttranslation langcode=\"id-ID\" langname=\"Indonesia\"/>\n  </translations>\n";
            $updated = substr_replace($content, $block, $end, 0);
        } else {
            $data = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_XML1, 'UTF-8'), true, 512, JSON_THROW_ON_ERROR);
            if (in_array($panoCode, $data['languages'] ?? [], true)) return false;
            $data['languages'][] = $panoCode; foreach (($data['translations'] ?? []) as &$values) $values[] = ''; unset($values);
            $payload = htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), ENT_NOQUOTES | ENT_XML1, 'UTF-8');
            $updated = substr_replace($content, $payload, strpos($content, $match[1]), strlen($match[1]));
        }
    } else {
        $marker = 'player.addTranslations('; $position = strpos($content, $marker); if ($position === false) return false;
        $object = languageJsonObject($content, $position + strlen($marker)); if ($object === null) return false;
        $data = json_decode($object['json'], true, 512, JSON_THROW_ON_ERROR); if (in_array($panoCode, $data['languages'] ?? [], true)) return false;
        $data['languages'][] = $panoCode; foreach (($data['translations'] ?? []) as &$values) $values[] = ''; unset($values);
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $updated = substr_replace($content, $payload, $object['offset'], $object['length']);
    }
    $backup = dirname($file) . '/.language-backups'; if (!is_dir($backup)) mkdir($backup, 0775, true);
    copy($file, $backup . '/' . date('Ymd-His') . '-' . basename($file));
    if (file_put_contents($file, $updated, LOCK_EX) === false) throw new RuntimeException('Gagal memperbarui ' . $file);
    return true;
}

function addProjectLanguage(string $root, array $language): array
{
    $code = $language['code'];
    $assetsRoot = assetsRootForProjectPath($root);
    $databaseFile = $assetsRoot . '/global-database.js'; $database = languageReadJs($databaseFile, 'GLOBAL_DATABASE');
    foreach ($database as &$location) if (!isset($location['bahasa'][$code])) $location['bahasa'][$code] = ['lokasi' => '', 'welcomeText' => '']; unset($location);
    languageWriteJs($databaseFile, 'GLOBAL_DATABASE', $database);
    $oiFile = $assetsRoot . '/oi-database.js'; $objects = languageReadJs($oiFile, 'TourDatabase');
    foreach ($objects as &$records) foreach ($records as &$record) if (!array_key_exists($code, $record['labelTeks'] ?? [])) $record['labelTeks'][$code] = ''; unset($records, $record);
    languageWriteJs($oiFile, 'TourDatabase', $objects);
    $pano = 0; $skin = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
        $path = str_replace('\\', '/', $file->getPathname()); if (!$file->isFile() || str_contains($path, '/.language-backups/')) continue;
        if (strtolower($file->getFilename()) === 'pano.xml' && migrateTranslationFile($file->getPathname(), $language['pano'], true)) $pano++;
        if (strtolower($file->getFilename()) === 'skin.js' && migrateTranslationFile($file->getPathname(), $language['pano'], false)) $skin++;
    }
    $htmlSkin = synchronizeHtmlSkinLanguages($root, languageRegistry($root) + [$code => $language]);
    return ['locations' => count($database), 'pano' => $pano, 'skin' => $skin, 'htmlSkin' => $htmlSkin];
}

function removeTranslationLanguage(string $file, string $panoCode, bool $xml): bool
{
    $content = (string) file_get_contents($file);
    if ($xml) {
        if (!preg_match('/<translations>.*?<json>(.*?)<\/json>/s', $content, $match)) return false;
        $data = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_XML1, 'UTF-8'), true, 512, JSON_THROW_ON_ERROR);
        $index = array_search($panoCode, $data['languages'] ?? [], true); if ($index === false) return false;
        array_splice($data['languages'], $index, 1);
        foreach (($data['translations'] ?? []) as &$values) if (array_key_exists($index, $values)) array_splice($values, $index, 1); unset($values);
        $payload = htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), ENT_NOQUOTES | ENT_XML1, 'UTF-8');
        $updated = substr_replace($content, $payload, strpos($content, $match[1]), strlen($match[1]));
    } else {
        $marker = 'player.addTranslations('; $position = strpos($content, $marker); if ($position === false) return false;
        $object = languageJsonObject($content, $position + strlen($marker)); if ($object === null) return false;
        $data = json_decode($object['json'], true, 512, JSON_THROW_ON_ERROR);
        $index = array_search($panoCode, $data['languages'] ?? [], true); if ($index === false) return false;
        array_splice($data['languages'], $index, 1);
        foreach (($data['translations'] ?? []) as &$values) if (array_key_exists($index, $values)) array_splice($values, $index, 1); unset($values);
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $updated = substr_replace($content, $payload, $object['offset'], $object['length']);
    }
    $backup = dirname($file) . '/.language-backups'; if (!is_dir($backup)) mkdir($backup, 0775, true);
    copy($file, $backup . '/' . date('Ymd-His') . '-before-remove-' . basename($file));
    if (file_put_contents($file, $updated, LOCK_EX) === false) throw new RuntimeException('Gagal memperbarui ' . $file);
    return true;
}

function removeProjectLanguage(string $root, string $code, array $language): array
{
    $assetsRoot = assetsRootForProjectPath($root);
    $databaseFile = $assetsRoot . '/global-database.js'; $database = languageReadJs($databaseFile, 'GLOBAL_DATABASE');
    foreach ($database as &$location) unset($location['bahasa'][$code]); unset($location);
    languageWriteJs($databaseFile, 'GLOBAL_DATABASE', $database);
    $oiFile = $assetsRoot . '/oi-database.js'; $objects = languageReadJs($oiFile, 'TourDatabase');
    foreach ($objects as &$records) foreach ($records as &$record) unset($record['labelTeks'][$code]); unset($records, $record);
    languageWriteJs($oiFile, 'TourDatabase', $objects);
    $pano = 0; $skin = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
        $path = str_replace('\\', '/', $file->getPathname()); if (!$file->isFile() || str_contains($path, '/.language-backups/')) continue;
        if (strtolower($file->getFilename()) === 'pano.xml' && removeTranslationLanguage($file->getPathname(), $language['pano'], true)) $pano++;
        if (strtolower($file->getFilename()) === 'skin.js' && removeTranslationLanguage($file->getPathname(), $language['pano'], false)) $skin++;
    }
    $remainingRegistry = languageRegistry($root); unset($remainingRegistry[$code]);
    $htmlSkin = synchronizeHtmlSkinLanguages($root, $remainingRegistry);
    return ['locations' => count($database), 'pano' => $pano, 'skin' => $skin, 'htmlSkin' => $htmlSkin];
}

function replaceTranslationLanguageCode(string $file, string $oldPanoCode, string $newPanoCode, bool $xml): bool
{
    if ($oldPanoCode === $newPanoCode) return false;
    $content = (string) file_get_contents($file);

    if ($xml) {
        if (!preg_match('/<translations>.*?<json>(.*?)<\/json>/s', $content, $match)) return false;
        $data = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_XML1, 'UTF-8'), true, 512, JSON_THROW_ON_ERROR);
        $index = array_search($oldPanoCode, $data['languages'] ?? [], true);
        if ($index === false) return false;
        if (in_array($newPanoCode, $data['languages'], true)) throw new RuntimeException('Kode Pano2VR ' . $newPanoCode . ' sudah ada di ' . $file);
        $data['languages'][$index] = $newPanoCode;
        $payload = htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), ENT_NOQUOTES | ENT_XML1, 'UTF-8');
        $updated = substr_replace($content, $payload, strpos($content, $match[1]), strlen($match[1]));
    } else {
        $marker = 'player.addTranslations('; $position = strpos($content, $marker); if ($position === false) return false;
        $object = languageJsonObject($content, $position + strlen($marker)); if ($object === null) return false;
        $data = json_decode($object['json'], true, 512, JSON_THROW_ON_ERROR);
        $index = array_search($oldPanoCode, $data['languages'] ?? [], true);
        if ($index === false) return false;
        if (in_array($newPanoCode, $data['languages'], true)) throw new RuntimeException('Kode Pano2VR ' . $newPanoCode . ' sudah ada di ' . $file);
        $data['languages'][$index] = $newPanoCode;
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $updated = substr_replace($content, $payload, $object['offset'], $object['length']);
    }

    $backup = dirname($file) . '/.language-backups';
    if (!is_dir($backup)) mkdir($backup, 0775, true);
    copy($file, $backup . '/' . date('Ymd-His') . '-before-language-edit-' . basename($file));
    if (file_put_contents($file, $updated, LOCK_EX) === false) throw new RuntimeException('Gagal memperbarui ' . $file);
    return true;
}

function updateProjectLanguage(string $root, string $code, array $oldLanguage, array $newLanguage): array
{
    $pano = 0; $skin = 0; $audio = 0;
    if (($oldLanguage['pano'] ?? $code) !== $newLanguage['pano']) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            if (!$file->isFile() || str_contains($path, '/.language-backups/')) continue;
            if (strtolower($file->getFilename()) === 'pano.xml' && replaceTranslationLanguageCode($file->getPathname(), (string) $oldLanguage['pano'], $newLanguage['pano'], true)) $pano++;
            if (strtolower($file->getFilename()) === 'skin.js' && replaceTranslationLanguageCode($file->getPathname(), (string) $oldLanguage['pano'], $newLanguage['pano'], false)) $skin++;
        }
    }

    $oldAudio = strtoupper((string) ($oldLanguage['audio'] ?? ''));
    $newAudio = strtoupper((string) $newLanguage['audio']);
    $audioRoot = dirname(assetsRootForProjectPath($root)) . '/audio';
    if ($oldAudio !== $newAudio && is_dir($audioRoot)) {
        foreach (new DirectoryIterator($audioRoot) as $directory) {
            if (!$directory->isDir() || $directory->isDot()) continue;
            $source = $directory->getPathname() . '/' . $oldAudio . '.mp3';
            $target = $directory->getPathname() . '/' . $newAudio . '.mp3';
            if (is_file($source) && !is_file($target) && copy($source, $target)) $audio++;
        }
    }

    return ['pano' => $pano, 'skin' => $skin, 'audio' => $audio];
}

function canonicalTranslationData(array $data, array $registry): array
{
    $oldLanguages = array_values(array_filter((array) ($data['languages'] ?? []), 'is_string'));
    $targetLanguages = [];
    foreach ($registry as $code => $language) {
        if ($code !== 'id') $targetLanguages[] = (string) ($language['pano'] ?? $code);
    }

    $normalized = [];
    foreach ((array) ($data['translations'] ?? []) as $source => $values) {
        if (!is_array($values)) continue;
        $row = [];
        foreach ($targetLanguages as $targetCode) {
            $index = array_search($targetCode, $oldLanguages, true);
            if ($index === false) {
                $targetBase = strtolower(explode('-', $targetCode)[0]);
                foreach ($oldLanguages as $oldIndex => $oldCode) {
                    if (strtolower(explode('-', $oldCode)[0]) === $targetBase) { $index = $oldIndex; break; }
                }
            }
            $row[] = $index === false ? '' : (string) ($values[$index] ?? '');
        }
        // Pano2VR generated translation arrays contain one reserved trailing value.
        $row[] = (string) ($values[count($oldLanguages)] ?? '');
        $normalized[(string) $source] = $row;
    }

    $data['languages'] = $targetLanguages;
    $data['projectLanguage'] = (string) ($registry['id']['pano'] ?? 'id-ID');
    $data['translations'] = $normalized;
    return $data;
}

function synchronizeTranslationFile(string $file, array $registry, bool $xml): bool
{
    $content = (string) file_get_contents($file);
    if ($xml) {
        if (!preg_match('/<translations>.*?<json>(.*?)<\/json>.*?<\/translations>/s', $content, $blockMatch)) return false;
        $data = json_decode(html_entity_decode($blockMatch[1], ENT_QUOTES | ENT_XML1, 'UTF-8'), true, 512, JSON_THROW_ON_ERROR);
        $data = canonicalTranslationData($data, $registry);
        $payload = htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), ENT_NOQUOTES | ENT_XML1, 'UTF-8');
        $indent = str_contains($blockMatch[0], "\n    <json>") ? '    ' : '  ';
        $metadata = [];
        foreach ($registry as $code => $language) {
            $panoCode = htmlspecialchars((string) ($language['pano'] ?? $code), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $name = htmlspecialchars((string) ($language['nativeLabel'] ?? $language['label'] ?? $code), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $metadata[] = $indent . '<projecttranslation langcode="' . $panoCode . '" langname="' . $name . '"/>';
        }
        $replacement = '<translations>' . "\n" . $indent . '<json>' . $payload . '</json>' . "\n" . implode("\n", $metadata) . "\n" . substr($indent, 0, max(0, strlen($indent) - 2)) . '</translations>';
        $updated = substr_replace($content, $replacement, strpos($content, $blockMatch[0]), strlen($blockMatch[0]));
    } else {
        $marker = 'player.addTranslations('; $position = strpos($content, $marker); if ($position === false) return false;
        $object = languageJsonObject($content, $position + strlen($marker)); if ($object === null) return false;
        $data = json_decode($object['json'], true, 512, JSON_THROW_ON_ERROR);
        $payload = json_encode(canonicalTranslationData($data, $registry), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $updated = substr_replace($content, $payload, $object['offset'], $object['length']);
    }

    if ($updated === $content) return false;
    $backup = dirname($file) . '/.language-backups';
    if (!is_dir($backup)) mkdir($backup, 0775, true);
    copy($file, $backup . '/' . date('Ymd-His') . '-before-language-sync-' . basename($file));
    if (file_put_contents($file, $updated, LOCK_EX) === false) throw new RuntimeException('Gagal menyinkronkan ' . $file);
    return true;
}

function synchronizeProjectLanguages(string $root, array $registry): array
{
    $pano = 0; $skin = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
        $path = str_replace('\\', '/', $file->getPathname());
        if (!$file->isFile() || str_contains($path, '/.language-backups/') || str_contains($path, '/.skin-backups/')) continue;
        if (strtolower($file->getFilename()) === 'pano.xml' && synchronizeTranslationFile($file->getPathname(), $registry, true)) $pano++;
        if (strtolower($file->getFilename()) === 'skin.js' && synchronizeTranslationFile($file->getPathname(), $registry, false)) $skin++;
    }
    $htmlSkin = synchronizeHtmlSkinLanguages($root, $registry);
    return ['pano' => $pano, 'skin' => $skin, 'htmlSkin' => $htmlSkin];
}

function languageCanAutoTranslate(string $text): bool
{
    $plain = trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($plain === '' || mb_strlen($plain) > 450) return false;
    if (preg_match('~(?:<iframe|<script|javascript:|https?://|player\.|function\s*\(|\$\()~i', $text)) return false;
    if (preg_match('/^[\d\s\p{P}\p{S}]+$/u', $plain)) return false;
    return true;
}

function translatePanoText(string $text, string $target): string
{
    static $cache = [];
    $key = $target . "\0" . $text;
    if (array_key_exists($key, $cache)) return $cache[$key];

    $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=id&tl=' . rawurlencode($target) . '&dt=t&q=' . rawurlencode($text);
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_USERAGENT => 'Mozilla/5.0 Jakarta Virtual Tour CMS']);
    $response = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    if (is_string($response) && $status >= 200 && $status < 300) {
        try {
            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            $translated = trim(implode('', array_map(static fn(array $part): string => (string) ($part[0] ?? ''), (array) ($data[0] ?? []))));
            if ($translated !== '') return $cache[$key] = $translated;
        } catch (Throwable) {
            // Continue with the fallback provider below.
        }
    }

    // Google may temporarily reject automated requests (HTTP 429). MyMemory
    // provides a compatible fallback for short CMS translation strings.
    $fallbackUrl = 'https://api.mymemory.translated.net/get?q=' . rawurlencode($text) . '&langpair=id|' . rawurlencode($target);
    $curl = curl_init($fallbackUrl);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_USERAGENT => 'Jakarta Virtual Tour CMS']);
    $response = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    if (!is_string($response) || $status < 200 || $status >= 300) return $cache[$key] = '';

    try {
        $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        if ((int) ($data['responseStatus'] ?? 0) !== 200) return $cache[$key] = '';
        return $cache[$key] = trim((string) ($data['responseData']['translatedText'] ?? ''));
    } catch (Throwable) {
        return $cache[$key] = '';
    }
}

function autoTranslateTranslationFile(string $file, string $panoCode, string $translateCode, bool $xml): int
{
    $content = (string) file_get_contents($file);
    if ($xml) {
        if (!preg_match('/<translations>.*?<json>(.*?)<\/json>/s', $content, $match)) return 0;
        $data = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_XML1, 'UTF-8'), true, 512, JSON_THROW_ON_ERROR);
    } else {
        $marker = 'player.addTranslations('; $position = strpos($content, $marker); if ($position === false) return 0;
        $object = languageJsonObject($content, $position + strlen($marker)); if ($object === null) return 0;
        $data = json_decode($object['json'], true, 512, JSON_THROW_ON_ERROR);
    }

    $index = array_search($panoCode, (array) ($data['languages'] ?? []), true);
    if ($index === false) return 0;
    $translatedCount = 0;
    foreach ($data['translations'] as $source => &$values) {
        if (!is_array($values) || trim((string) ($values[$index] ?? '')) !== '' || !languageCanAutoTranslate((string) $source)) continue;
        $translation = translatePanoText((string) $source, $translateCode);
        if ($translation === '') continue;
        $values[$index] = $translation;
        $translatedCount++;
    }
    unset($values);
    if ($translatedCount === 0) return 0;

    $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    if ($xml) {
        $encoded = htmlspecialchars($payload, ENT_NOQUOTES | ENT_XML1, 'UTF-8');
        $updated = substr_replace($content, $encoded, strpos($content, $match[1]), strlen($match[1]));
    } else {
        $updated = substr_replace($content, $payload, $object['offset'], $object['length']);
    }
    $backup = dirname($file) . '/.language-backups';
    if (!is_dir($backup)) mkdir($backup, 0775, true);
    copy($file, $backup . '/' . date('Ymd-His') . '-before-auto-translate-' . basename($file));
    if (file_put_contents($file, $updated, LOCK_EX) === false) throw new RuntimeException('Gagal menyimpan auto translate ' . $file);
    return $translatedCount;
}

function autoTranslateProjectLanguage(string $root, array $language): array
{
    $pano = 0; $skin = 0; $texts = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
        $path = str_replace('\\', '/', $file->getPathname());
        if (!$file->isFile() || str_contains($path, '/.language-backups/') || str_contains($path, '/.skin-backups/')) continue;
        if (strtolower($file->getFilename()) === 'pano.xml') {
            $count = autoTranslateTranslationFile($file->getPathname(), (string) $language['pano'], (string) $language['translate'], true);
            if ($count > 0) { $pano++; $texts += $count; }
        }
        if (strtolower($file->getFilename()) === 'skin.js') {
            $count = autoTranslateTranslationFile($file->getPathname(), (string) $language['pano'], (string) $language['translate'], false);
            if ($count > 0) { $skin++; $texts += $count; }
        }
    }
    return ['pano' => $pano, 'skin' => $skin, 'texts' => $texts];
}
