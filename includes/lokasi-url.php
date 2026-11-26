<?php
declare(strict_types=1);

/**
 * URL Lokasi + hotspot "lokasi terdekat" di panorama drone (tab CMS "URL Lokasi").
 *
 * Data disimpan di <assets/main-loader>/lokasi-database.js (window.LOKASI_DATABASE), dimuat main-loader tiap tur:
 *   locations: <id lokasi> => { name: judul Indonesia (sumber terjemahan Pano2VR), url: alamat tur lokasi itu }
 *   links:     <id sumber>  => [ { to: <id tujuan>, min: menit, km: teks jarak, pan, tilt, hidden } ]   (pan/tilt = posisi di panorama drone)
 * Judul diterjemahkan lewat daftar Terjemahan pano.xml tiap tur (CMS › Audit › Terjemahan): lokasiSyncTranslations() memastikan
 * judul tujuan ada di daftar itu (nilai kosong = "Belum Diisi"; terjemahan yang sudah ada tidak diubah).
 * Runtime: smart-kit/frontends/badung-ui/js/nearby-locs.js.
 * Pemulihan (tombol "Load Titik URL"): bagian PEMULIHAN di bawah + lokasi-url-restore.php.
 */

require_once __DIR__ . '/pano-memory.php';   // createTranslationBlock(), panoMemory*()

const LOKASI_DB_VAR = 'LOKASI_DATABASE';

function lokasiCleanId(mixed $value): string
{
    return strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', (string) $value));
}

function lokasiDbFile(string $assetsRoot): string
{
    return rtrim($assetsRoot, '/\\') . '/lokasi-database.js';
}

function lokasiDbRead(string $file): array
{
    $empty = ['version' => 1, 'locations' => [], 'links' => []];
    if (!is_file($file)) return $empty;
    try { $data = cmsReadJsObject($file, LOKASI_DB_VAR); } catch (Throwable) { return $empty; }
    return ['version' => 1, 'locations' => is_array($data['locations'] ?? null) ? $data['locations'] : [], 'links' => is_array($data['links'] ?? null) ? $data['links'] : []];
}

/** Rapikan + validasi kiriman dari editor; melempar RuntimeException berpesan Indonesia bila ada yang tidak valid. */
function lokasiDbNormalize(array $in): array
{
    $locations = [];
    foreach ((array) ($in['locations'] ?? []) as $id => $row) {
        $id = lokasiCleanId($id);
        if ($id === '' || !is_array($row)) continue;
        $name = trim(strip_tags((string) ($row['name'] ?? '')));
        $url = trim((string) ($row['url'] ?? ''));
        if ($name === '') throw new RuntimeException('Judul lokasi "' . $id . '" tidak boleh kosong.');
        if (mb_strlen($name) > 120) throw new RuntimeException('Judul "' . mb_substr($name, 0, 30) . '…" terlalu panjang (maksimal 120 karakter).');
        if ($url !== '' && !preg_match('~^(https?://)?[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?(?:\.[A-Za-z0-9-]+)+(?::\d{2,5})?(?:[/?#][^\s<>"\'\\\\]*)?$~', $url)) {
            throw new RuntimeException('URL untuk "' . $name . '" tidak valid: ' . mb_substr($url, 0, 80));
        }
        $locations[$id] = ['name' => $name, 'url' => $url];
    }
    $links = [];
    foreach ((array) ($in['links'] ?? []) as $source => $list) {
        $source = lokasiCleanId($source);
        if (!isset($locations[$source]) || !is_array($list)) continue;
        $out = []; $seen = [];
        foreach ($list as $link) {
            if (!is_array($link)) continue;
            $to = lokasiCleanId($link['to'] ?? '');
            if ($to === '' || $to === $source || !isset($locations[$to]) || isset($seen[$to])) continue;
            $seen[$to] = true;
            $km = trim(strip_tags((string) ($link['km'] ?? '')));
            if ($km !== '' && !preg_match('/^[\d.,]+\s*(km|m)?$/i', $km)) throw new RuntimeException('Jarak "' . $km . '" tidak valid (contoh: 1.5 km atau 900 m).');
            $row = ['to' => $to, 'min' => max(0, min(999, (int) ($link['min'] ?? 0))), 'km' => $km];
            foreach (['pan' => [-180.0, 180.0], 'tilt' => [-90.0, 90.0]] as $field => [$low, $high]) {
                if (isset($link[$field]) && $link[$field] !== '' && is_numeric($link[$field])) $row[$field] = round(max($low, min($high, (float) $link[$field])), 2);
            }
            if (!empty($link['hidden'])) $row['hidden'] = true;
            $out[] = $row;
        }
        $links[$source] = $out;
    }
    return ['version' => 1, 'locations' => $locations, 'links' => $links];
}

function lokasiDbWrite(string $file, array $data): void
{
    $header = "// lokasi-database.js — DIKELOLA CMS (tab \"URL Lokasi\"): daftar URL lokasi Badung + hotspot \"lokasi terdekat\" di panorama drone.\n"
        . "// locations: <id lokasi> -> { name: judul (sumber terjemahan Pano2VR), url }.  links: <id sumber> -> [{ to, min (menit), km (teks jarak), pan, tilt }].\n";
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    if (is_file($file)) {
        $backup = cmsBackupEnsure(dirname($file), 'lokasi-url');
        if (!@copy($file, $backup . '/lokasi-database-' . date('Ymd-His') . '.js')) throw new RuntimeException('Cadangan lokasi-database.js gagal dibuat.');
    }
    if (file_put_contents($file, $header . 'window.' . LOKASI_DB_VAR . ' = ' . $json . ";\n", LOCK_EX) === false) throw new RuntimeException('lokasi-database.js gagal disimpan.');
    try { lokasiMemoryWrite(dirname($file), $data); } catch (Throwable) { /* memori hanya cadangan pemulihan; gagal mencatat tidak membatalkan simpan */ }
}

/** Folder tur yang punya pano.xml + skin.js: id (nama folder huruf kecil) => jalur pano.xml. */
function lokasiTourPanoFiles(string $projectRoot): array
{
    $found = [];
    foreach (new DirectoryIterator($projectRoot) as $entry) {
        if (!$entry->isDir() || $entry->isDot()) continue;
        $pano = $entry->getPathname() . '/pano.xml';
        if (!is_file($pano) || !is_file($entry->getPathname() . '/skin.js')) continue;
        $found[lokasiCleanId($entry->getFilename())] = $pano;
    }
    return $found;
}

/** Judul (Indonesia) tujuan hotspot sebuah lokasi sumber, tanpa duplikat, urut sesuai daftar. */
function lokasiTourNames(array $data, string $id): array
{
    $names = [];
    foreach ((array) ($data['links'][$id] ?? []) as $link) {
        $name = trim((string) ($data['locations'][$link['to'] ?? '']['name'] ?? ''));
        if ($name !== '') $names[$name] = true;
    }
    return array_keys($names);
}

/** Naikkan ?ts= di index.html agar pengunjung tidak memakai pano.xml lama dari cache (sama dengan save-pano.php); gagal tidak fatal. */
function lokasiBustTourCache(string $panoFile): void
{
    try {
        require_once __DIR__ . '/../skin-templates.php';
        $templateId = projectSkinTemplateId(activeProject(), skinTemplateList());
        $setup = $templateId !== '' ? skinTemplateSetup($templateId) : null;
        if ($setup !== null) skinRunSetup($setup, dirname($panoFile));
    } catch (Throwable) { /* gagal memperbarui cache tidak membatalkan simpan */ }
}

/**
 * Judul tujuan sebuah tur di daftar Terjemahan pano.xml (kunci = judul Indonesia). MENAMBAH kunci yang belum ada (nilai kosong) dan,
 * bila $restoreValues, mengisi nilai KOSONG dari memori pano.xml CMS (terjemahan yang dulu diisi di Audit); terjemahan yang sudah terisi tidak disentuh.
 * Mengembalikan ['keys' => jumlah kunci baru, 'values' => jumlah nilai dipulihkan].
 */
function lokasiTourTranslations(string $projectRoot, string $id, array $data, bool $restoreValues = false): array
{
    $result = ['keys' => 0, 'values' => 0];
    $panoFile = lokasiTourPanoFiles($projectRoot)[$id] ?? null;
    $names = lokasiTourNames($data, $id);
    if ($panoFile === null || $names === []) return $result;
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = true;
    $dom->formatOutput = false;
    if (!@$dom->load($panoFile)) return $result;
    $xpath = new DOMXPath($dom);
    $jsonNode = $xpath->query('//masternode/translations/json')?->item(0);
    if (!$jsonNode instanceof DOMElement) $jsonNode = createTranslationBlock($dom, $xpath, $projectRoot);
    if (!$jsonNode instanceof DOMElement) return $result;
    $payload = json_decode($jsonNode->textContent, true);
    if (!is_array($payload)) return $result;
    $width = count((array) ($payload['languages'] ?? [])) + 1;   // satu kolom per bahasa + satu kolom cadangan, sama dengan entri bawaan Pano2VR
    $existing = (array) ($payload['translations'] ?? []);
    foreach ($names as $name) {
        if (array_key_exists($name, $existing)) continue;
        $existing[$name] = array_fill(0, $width, '');
        $result['keys']++;
    }
    if ($result['keys'] > 0) {
        $payload['translations'] = $existing;
        $jsonNode->nodeValue = '';
        $jsonNode->appendChild($dom->createTextNode((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    }
    if ($restoreValues) {
        $plan = ['translations' => lokasiTranslationPlan($dom, $panoFile, $names), 'panoramas' => [], 'hotspots' => [], 'start' => []];
        if ($plan['translations'] !== []) $result['values'] = panoMemoryApply($dom, $plan, $projectRoot);
    }
    if ($result['keys'] === 0 && $result['values'] === 0) return $result;
    $backup = cmsBackupEnsure(dirname($panoFile), 'media');
    if (!@copy($panoFile, $backup . '/pano-before-lokasi-url-' . date('Ymd-His') . '.xml')) throw new RuntimeException('Cadangan pano.xml ' . $id . ' gagal dibuat.');
    if ($dom->save($panoFile) === false) throw new RuntimeException('pano.xml ' . $id . ' gagal disimpan.');
    lokasiBustTourCache($panoFile);
    return $result;
}

/** Terjemahan dari memori pano.xml CMS yang masih KOSONG di pano.xml ini, hanya untuk judul yang diminta (format rencana panoMemoryApply). */
function lokasiTranslationPlan(DOMDocument $dom, string $panoFile, array $names): array
{
    $memory = panoMemoryLoad(dirname($panoFile));
    if ($memory === null) return [];
    $state = panoMemoryTranslationState(new DOMXPath($dom));
    $plan = [];
    foreach ($names as $name) {
        foreach ((array) ($memory['translations'][$name] ?? []) as $code => $value) {
            $value = trim((string) $value);
            if ($value === '' || trim((string) ($state['map'][$name][$code] ?? '')) !== '') continue;
            if ($state['languages'] !== [] && !in_array((string) $code, $state['languages'], true)) continue;
            $plan[] = ['source' => (string) $name, 'code' => (string) $code, 'value' => $value];
        }
    }
    return $plan;
}

/**
 * Pastikan judul tujuan tiap lokasi sumber ada di daftar Terjemahan pano.xml tur sumber itu (dipakai saat Simpan).
 * Hanya MENAMBAH kunci yang belum ada; terjemahan yang sudah terisi tidak disentuh. Mengembalikan jumlah kunci baru per tur.
 */
function lokasiSyncTranslations(string $projectRoot, array $data): array
{
    $added = [];
    foreach (array_keys(lokasiTourPanoFiles($projectRoot)) as $id) {
        $r = lokasiTourTranslations($projectRoot, $id, $data, false);
        if ($r['keys'] > 0) $added[$id] = $r['keys'];
    }
    return $added;
}

/* ====================================================================================================================
 * PEMULIHAN ("Load Titik URL"): data titik/URL, pemuat tur, dan judul di Terjemahan bisa hilang (file assets diganti, main-loader.js
 * ditimpa, atau pano.xml di-export ulang dari Pano2VR). Posisi titik sendiri tersimpan di lokasi-database.js (di luar pano.xml), tetapi
 * judul di daftar Terjemahan hanya hidup di pano.xml. Sumber pemulihan data: memori CMS (salinan terakhir yang baik), lalu cadangan.
 * ==================================================================================================================== */

function lokasiMemoryBase(): string
{
    return defined('LOKASI_MEMORY_BASE') ? rtrim((string) LOKASI_MEMORY_BASE, '/\\') : str_replace('\\', '/', dirname(__DIR__)) . '/data/lokasi-url';
}

function lokasiMemoryFile(string $assetsRoot): string
{
    $src = rtrim(str_replace('\\', '/', $assetsRoot), '/');
    $real = @realpath($src);
    if ($real !== false) $src = rtrim(str_replace('\\', '/', $real), '/');
    $htdocs = str_replace('\\', '/', dirname(dirname(__DIR__)));
    $rel = stripos($src . '/', $htdocs . '/') === 0 ? trim(substr($src, strlen($htdocs)), '/') : 'luar-htdocs/' . substr(sha1($src), 0, 10) . '-' . basename($src);
    return lokasiMemoryBase() . '/' . (string) preg_replace('~[^A-Za-z0-9._/-]~', '_', $rel) . '/memory.json';
}

function lokasiDbCounts(array $data): array
{
    return [count((array) ($data['locations'] ?? [])), array_sum(array_map('count', array_filter((array) ($data['links'] ?? []), 'is_array')))];
}

/** Salinan terakhir yang baik: ['updated' => iso, 'data' => {locations, links}] atau null. */
function lokasiMemoryLoad(string $assetsRoot): ?array
{
    $file = lokasiMemoryFile($assetsRoot);
    if (!is_file($file)) return null;
    $raw = json_decode((string) file_get_contents($file), true);
    if (!is_array($raw) || !is_array($raw['data'] ?? null)) return null;
    try { $data = lokasiDbNormalize($raw['data']); } catch (Throwable) { return null; }
    return lokasiDbCounts($data)[0] > 0 ? ['updated' => (string) ($raw['updated'] ?? ''), 'data' => $data] : null;
}

function lokasiMemoryWrite(string $assetsRoot, array $data): void
{
    $file = lokasiMemoryFile($assetsRoot);
    $dir = dirname($file);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Folder memori URL Lokasi gagal dibuat.');
    $guard = lokasiMemoryBase() . '/.htaccess';
    if (!is_file($guard)) @file_put_contents($guard, "Require all denied\nDeny from all\n");
    if (file_put_contents($file, json_encode(['version' => 1, 'updated' => date('c'), 'data' => $data], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) throw new RuntimeException('Memori URL Lokasi gagal disimpan.');
}

/** Ingat keadaan file saat tab dibuka, tetapi tidak pernah menimpa salinan baik dengan file yang lebih kecil (rusak/diganti). */
function lokasiMemorySync(string $assetsRoot, array $fileData): void
{
    [$locations, $links] = lokasiDbCounts($fileData);
    if ($locations === 0) return;
    $memory = lokasiMemoryLoad($assetsRoot);
    if ($memory !== null) {
        [$memoryLocations, $memoryLinks] = lokasiDbCounts($memory['data']);
        if ($locations < $memoryLocations || $links < $memoryLinks || $memory['data'] == $fileData) return;
    }
    lokasiMemoryWrite($assetsRoot, $fileData);
}

/** Cadangan lokasi-database.js terbaru yang masih terbaca (dibuat tiap Simpan: keadaan SEBELUM simpan). */
function lokasiBackupLatest(string $assetsRoot): ?array
{
    $files = glob(cmsBackupPathFor($assetsRoot, 'lokasi-url') . '/lokasi-database-*.js') ?: [];
    rsort($files);
    foreach (array_slice($files, 0, 40) as $file) {
        try { $data = lokasiDbNormalize(cmsReadJsObject($file, LOKASI_DB_VAR)); } catch (Throwable) { continue; }
        if (lokasiDbCounts($data)[0] > 0) return ['updated' => date('c', (int) filemtime($file)), 'data' => $data];
    }
    return null;
}

/** Yang ada di $reference tetapi tidak ada di $current: ['locations' => [id...], 'links' => [[sumber, tujuan]...]]. */
function lokasiGaps(array $current, array $reference): array
{
    $gaps = ['locations' => [], 'links' => []];
    foreach ((array) $reference['locations'] as $id => $_row) if (!isset($current['locations'][$id])) $gaps['locations'][] = (string) $id;
    foreach ((array) $reference['links'] as $source => $list) {
        $have = [];
        foreach ((array) ($current['links'][$source] ?? []) as $link) $have[(string) ($link['to'] ?? '')] = true;
        foreach ((array) $list as $link) if (!isset($have[(string) $link['to']])) $gaps['links'][] = [(string) $source, (string) $link['to']];
    }
    return $gaps;
}

/** Tambahkan celah dari $reference ke $current (hanya yang hilang; isi yang sudah ada tidak diubah). */
function lokasiApplyGaps(array $current, array $reference, array $gaps): array
{
    $current['locations'] = (array) ($current['locations'] ?? []);
    $current['links'] = (array) ($current['links'] ?? []);
    foreach ($gaps['locations'] as $id) $current['locations'][$id] = $reference['locations'][$id];
    foreach ($gaps['links'] as [$source, $to]) {
        foreach ((array) $reference['links'][$source] as $link) if ($link['to'] === $to) { $current['links'][$source][] = $link; break; }
    }
    return $current;
}

function lokasiLoaderFile(string $assetsRoot): string
{
    return rtrim($assetsRoot, '/\\') . '/main-loader.js';
}

/** main-loader.js dengan lokasi-database.js terpasang di daftar DATA dan daftar scripts; null bila pola kodenya tidak dikenali. */
function lokasiLoaderPatched(string $source): ?string
{
    $patched = (string) preg_replace("/(var DATA = \\[(?:(?!lokasi-database\\.js)[^\\]])*'oi-database\\.js',\\s*)('oi-config\\.js')/", "\$1'lokasi-database.js', \$2", $source, 1);
    $patched = (string) preg_replace("/('oi-database\\.js',(\\r?\\n)([ \\t]*))(?!'lokasi-database\\.js')('oi-config\\.js')/", "\$1'lokasi-database.js',\$2\$3\$4", $patched, 1);
    return substr_count($patched, 'lokasi-database.js') >= 2 ? $patched : null;
}

/** ok | missing (bisa dipasang otomatis) | manual (pola tidak dikenali) | absent (tidak ada main-loader.js). */
function lokasiLoaderStatus(string $assetsRoot): string
{
    $file = lokasiLoaderFile($assetsRoot);
    if (!is_file($file)) return 'absent';
    $source = (string) file_get_contents($file);
    if (substr_count($source, 'lokasi-database.js') >= 2) return 'ok';
    return lokasiLoaderPatched($source) !== null ? 'missing' : 'manual';
}

function lokasiLoaderRepair(string $assetsRoot): bool
{
    $file = lokasiLoaderFile($assetsRoot);
    if (!is_file($file)) return false;
    $source = (string) file_get_contents($file);
    $patched = lokasiLoaderPatched($source);
    if ($patched === null || $patched === $source) return false;
    $backup = cmsBackupEnsure($assetsRoot, 'lokasi-url');
    if (!@copy($file, $backup . '/main-loader-' . date('Ymd-His') . '.js')) throw new RuntimeException('Cadangan main-loader.js gagal dibuat.');
    if (file_put_contents($file, $patched, LOCK_EX) === false) throw new RuntimeException('main-loader.js gagal diperbarui.');
    return true;
}

/** Pemeriksaan pemulihan untuk SATU lokasi (data global + pemuat + judul di pano.xml lokasi itu). */
function lokasiRestoreScan(string $projectRoot, string $assetsRoot, string $id, array $current): array
{
    $current = ['locations' => (array) ($current['locations'] ?? []), 'links' => (array) ($current['links'] ?? [])];
    $memory = lokasiMemoryLoad($assetsRoot);
    $source = $memory !== null ? ['label' => 'Memori CMS', 'updated' => $memory['updated'], 'data' => $memory['data']] : null;
    if ($source === null && ($backup = lokasiBackupLatest($assetsRoot)) !== null) $source = ['label' => 'Cadangan lokasi-database.js', 'updated' => $backup['updated'], 'data' => $backup['data']];
    $gaps = $source !== null ? lokasiGaps($current, $source['data']) : ['locations' => [], 'links' => []];
    [$locations, $links] = lokasiDbCounts($current);
    $dataState = $gaps['locations'] === [] && $gaps['links'] === [] ? ($locations > 0 ? 'ok' : 'lost') : 'gap';
    $loader = lokasiLoaderStatus($assetsRoot);
    // judul di Terjemahan pano.xml lokasi ini (memakai data setelah celah diisi, supaya judul yang akan kembali ikut dihitung)
    $after = $source !== null && $dataState === 'gap' ? lokasiApplyGaps($current, $source['data'], $gaps) : $current;
    $names = lokasiTourNames($after, $id);
    $tr = ['local' => false, 'names' => count($names), 'missingKeys' => 0, 'restorableValues' => 0];
    $panoFile = lokasiTourPanoFiles($projectRoot)[$id] ?? null;
    if ($panoFile !== null && $names !== []) {
        $tr['local'] = true;
        $dom = new DOMDocument('1.0', 'UTF-8');
        if (@$dom->load($panoFile)) {
            $state = panoMemoryTranslationState(new DOMXPath($dom));
            foreach ($names as $name) if (!isset($state['map'][$name])) $tr['missingKeys']++;
            $tr['restorableValues'] = count(lokasiTranslationPlan($dom, $panoFile, $names));
        }
    }
    $missing = count($gaps['locations']) + count($gaps['links']) + ($loader === 'missing' ? 1 : 0) + $tr['missingKeys'] + $tr['restorableValues'];
    return [
        'summary' => ['missing' => $missing, 'unrestorable' => ($dataState === 'lost' ? 1 : 0) + ($loader === 'manual' ? 1 : 0)],
        'data' => ['state' => $dataState, 'locations' => $locations, 'links' => $links, 'lostLocations' => count($gaps['locations']), 'lostLinks' => count($gaps['links']), 'source' => $source['label'] ?? null, 'when' => $source['updated'] ?? ''],
        'loader' => ['state' => $loader],
        'translations' => $tr,
    ];
}

/** Terapkan pemulihan untuk SATU lokasi. Mengembalikan ['done' => jumlah per bagian, 'data' => data terbaru, 'scan' => pemeriksaan ulang]. */
function lokasiRestoreApply(string $projectRoot, string $assetsRoot, string $id, array $current): array
{
    $file = lokasiDbFile($assetsRoot);
    $current = ['locations' => (array) ($current['locations'] ?? []), 'links' => (array) ($current['links'] ?? [])];
    $scan = lokasiRestoreScan($projectRoot, $assetsRoot, $id, $current);
    $done = ['locations' => 0, 'links' => 0, 'loader' => false, 'keys' => 0, 'values' => 0];
    if ($scan['data']['state'] === 'gap') {
        $memory = lokasiMemoryLoad($assetsRoot);
        $reference = $memory['data'] ?? (lokasiBackupLatest($assetsRoot)['data'] ?? null);
        if ($reference !== null) {
            $gaps = lokasiGaps($current, $reference);
            $current = lokasiDbNormalize(lokasiApplyGaps($current, $reference, $gaps));
            lokasiDbWrite($file, $current);
            $done['locations'] = count($gaps['locations']);
            $done['links'] = count($gaps['links']);
        }
    }
    if ($scan['loader']['state'] === 'missing') $done['loader'] = lokasiLoaderRepair($assetsRoot);
    $tr = lokasiTourTranslations($projectRoot, $id, $current, true);
    $done['keys'] = $tr['keys'];
    $done['values'] = $tr['values'];
    return ['done' => $done, 'data' => $current, 'scan' => lokasiRestoreScan($projectRoot, $assetsRoot, $id, $current)];
}
