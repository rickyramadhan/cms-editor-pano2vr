<?php
declare(strict_types=1);

/**
 * Load Objek Info: pulihkan pin (WebElement) Object Informasi yang hilang dari pano.xml.
 *
 * Data Object Informasi (judul, teks, bahasa) disimpan di assets/oi-database.js dan tidak ikut hilang. Yang hilang saat tur
 * di-export ulang dari Pano2VR adalah PIN-nya: posisi pin hanya ada sebagai <webelement> di pano.xml. Berkas ini mencari
 * posisi itu dari tiga sumber, berurutan, hanya untuk record AKTIF yang pin-nya tidak ada di pano.xml:
 *   1. memori CMS  : setiap simpan di editor Object Info mencatat posisi semua pin (data/pano-memory/.../memory.json › webelements)
 *   2. cadangan    : pano.xml sebelum tiap simpan (data/backups/.../media-backups), yang terbaru lebih dulu
 *   3. pin Smart Kit: record bertanda konten.meta.pin → pan/tilt di packs/<wilayah>/<lokasi>/pins.js
 * Tidak ada posisi yang dikarang: record tanpa sumber dilaporkan "tidak bisa dimuat". Hasil hanya DIKIRIM ke editor;
 * pano.xml baru berubah saat pengguna menekan Simpan.
 */
require_once __DIR__ . '/backup-store.php';
require_once __DIR__ . '/pano-memory.php';
if (function_exists('oiRestoreScan')) return;

/** Atribut XML satu tag → [nama huruf kecil => nilai] (sama dengan parseXmlAttributes di pano-audit.php, dipisah agar berkas ini berdiri sendiri). */
function oiRestoreAttributes(string $text): array
{
    preg_match_all('/([a-zA-Z0-9_-]+)="([^"]*)"/', $text, $matches, PREG_SET_ORDER);
    $attributes = [];
    foreach ($matches as $match) $attributes[strtolower($match[1])] = html_entity_decode($match[2], ENT_QUOTES | ENT_XML1, 'UTF-8');
    return $attributes;
}

/** Kunci pin: "node|target" huruf kecil, tanpa kurung kurawal (sama dengan pencocokan record ↔ WebElement di editor). */
function oiRestoreKey(string $node, string $target): string
{
    return strtolower(trim(str_replace(['{', '}'], '', $node)) . '|' . trim($target));
}

/** WebElement di satu dokumen pano.xml → [kunci => elemen]. Node publik = customnodeid, lalu nodeid, lalu id panorama (sama dengan readTourWebElements). */
function oiRestoreParseElements(string $xml): array
{
    $out = [];
    if (!preg_match_all('/<panorama\b[^>]*\bid="([^"]+)"[^>]*>(.*?)<\/panorama>/is', $xml, $panoramas, PREG_SET_ORDER)) return $out;
    foreach ($panoramas as $panorama) {
        $panoId = $panorama[1];
        $public = $panoId;
        if (preg_match('/<userdata\s+([^>]*)\/>/i', $panorama[2], $userdata)) {
            $attributes = oiRestoreAttributes($userdata[1]);
            $public = trim((string) ($attributes['customnodeid'] ?? '')) ?: (trim((string) ($attributes['nodeid'] ?? '')) ?: $panoId);
        }
        if (!preg_match_all('/<webelement\b([^>]*)\/>/is', $panorama[2], $webElements)) continue;
        foreach ($webElements[1] as $attributeText) {
            $values = oiRestoreAttributes($attributeText);
            $target = trim((string) ($values['id'] ?? ''));
            if ($target === '') continue;
            $out[oiRestoreKey($public, $target)] = oiRestoreElement($public, $panoId, $target, (float) ($values['pan'] ?? 0), (float) ($values['tilt'] ?? 0), (float) ($values['fov'] ?? 21.02), (int) ($values['width'] ?? 400), (int) ($values['height'] ?? 600));
        }
    }
    return $out;
}

function oiRestoreElement(string $public, string $panoId, string $target, float $pan, float $tilt, float $fov, int $width, int $height): array
{
    return ['nodeID' => $public, 'panoNodeId' => $panoId, 'targetID' => $target, 'pan' => round($pan, 3), 'tilt' => round($tilt, 3), 'fov' => round($fov, 3), 'width' => max(1, $width), 'height' => max(1, $height)];
}

/** Catatan posisi pin dari memori CMS: [kunci => elemen + 'saved']. */
function oiRestoreMemoryLoad(string $tourDir): array
{
    $memory = panoMemoryLoad($tourDir);
    return is_array($memory['webelements'] ?? null) ? $memory['webelements'] : [];
}

/** Buka memori pano.xml tur ini (dibuat bila belum ada). */
function oiRestoreMemoryOpen(string $tourDir): array
{
    $memory = panoMemoryLoad($tourDir);
    if ($memory === null) {
        // berkas memori belum ada: awali seperti Audit (terjemahan yang sudah ada), supaya catatan terjemahan tidak terlewat karena berkas dibuat lebih dulu di sini
        $dom = new DOMDocument('1.0', 'UTF-8');
        if (@$dom->load($tourDir . '/pano.xml')) panoMemorySeed($tourDir, $dom);
        $memory = panoMemoryLoad($tourDir) ?? panoMemoryEmpty();
    }
    return $memory;
}

/**
 * Samakan memori dengan pin yang ada di pano.xml SEKARANG (hanya menambah/memperbarui, tidak pernah menghapus), dipanggil saat tab Object Info dibuka.
 * Gunanya: pin lama sudah diingat sebelum export ulang pertama, tanpa menunggu pengguna menekan Simpan. Menulis berkas hanya bila ada perbedaan.
 *
 * @param array[] $elements hasil readTourWebElements (nodeID publik, panoNodeId, targetID, pan, tilt, fov, width, height)
 */
function oiRestoreMemorySync(string $tourDir, array $elements): void
{
    if ($elements === []) return;
    $memory = oiRestoreMemoryOpen($tourDir);
    $store = is_array($memory['webelements'] ?? null) ? $memory['webelements'] : [];
    $changed = false;
    foreach ($elements as $item) {
        if (!is_array($item)) continue;
        $public = (string) ($item['nodeID'] ?? ''); $target = trim((string) ($item['targetID'] ?? ''));
        if ($public === '' || $target === '') continue;
        $key = oiRestoreKey($public, $target);
        $fresh = oiRestoreElement($public, (string) ($item['panoNodeId'] ?? $public), $target, (float) ($item['pan'] ?? 0), (float) ($item['tilt'] ?? 0), (float) ($item['fov'] ?? 21.02), (int) ($item['width'] ?? 400), (int) ($item['height'] ?? 600));
        $old = is_array($store[$key] ?? null) ? $store[$key] : null;
        if ($old !== null && array_diff_key($old, ['saved' => 1]) == $fresh) continue;
        $store[$key] = $fresh + ['saved' => (string) ($old['saved'] ?? date('c'))];
        $changed = true;
    }
    if (!$changed) return;
    $memory['webelements'] = $store;
    panoMemoryWrite($tourDir, $memory);
}

/**
 * Catat pin yang baru disimpan editor. Catatan HANYA dihapus untuk pin yang memang dihapus di editor (ada di pano.xml sebelum simpan,
 * tidak ada di kiriman); pin yang sudah hilang lebih dulu (export ulang) tetap diingat sampai dimuat ulang.
 *
 * @param string[] $beforeKeys   kunci pin di pano.xml SEBELUM simpan
 * @param array[]  $saved        elemen yang dikirim editor (panoNodeId, targetID, pan, tilt, fov, width, height)
 * @param array    $publicByPano id panorama (huruf kecil) → node publik
 */
function oiRestoreMemoryRemember(string $tourDir, array $beforeKeys, array $saved, array $publicByPano): void
{
    $memory = oiRestoreMemoryOpen($tourDir);
    $store = is_array($memory['webelements'] ?? null) ? $memory['webelements'] : [];
    $now = [];
    foreach ($saved as $item) {
        if (!is_array($item)) continue;
        $panoId = trim((string) ($item['panoNodeId'] ?? ''));
        $target = trim((string) ($item['targetID'] ?? ''));
        if ($panoId === '' || $target === '') continue;
        $public = (string) ($publicByPano[strtolower($panoId)] ?? $panoId);
        $key = oiRestoreKey($public, $target);
        $store[$key] = oiRestoreElement($public, $panoId, $target, (float) ($item['pan'] ?? 0), (float) ($item['tilt'] ?? 0), (float) ($item['fov'] ?? 21.02), (int) ($item['width'] ?? 400), (int) ($item['height'] ?? 600)) + ['saved' => date('c')];
        $now[$key] = true;
    }
    foreach ($beforeKeys as $key) if (!isset($now[$key])) unset($store[$key]);
    $memory['webelements'] = $store;
    panoMemoryWrite($tourDir, $memory);
}

/** Cari posisi pin di cadangan pano.xml (terbaru dulu, berhenti begitu semua kunci ketemu): [kunci => elemen + 'when']. */
function oiRestoreBackupElements(string $tourDir, array $wantKeys, int $limit = 80): array
{
    $found = [];
    if ($wantKeys === []) return $found;
    $dir = cmsBackupPathFor($tourDir, 'media');
    $files = is_dir($dir) ? (glob($dir . '/*.xml') ?: []) : [];
    usort($files, static fn(string $a, string $b): int => (filemtime($b) ?: 0) <=> (filemtime($a) ?: 0));
    $wanted = array_fill_keys($wantKeys, true);
    foreach (array_slice($files, 0, $limit) as $file) {
        $xml = @file_get_contents($file);
        if (!is_string($xml) || stripos($xml, '<webelement') === false) continue;
        foreach (oiRestoreParseElements($xml) as $key => $element) {
            if (!isset($wanted[$key]) || isset($found[$key])) continue;
            $found[$key] = $element + ['when' => date('c', (int) filemtime($file))];
        }
        if (count($found) === count($wanted)) break;
    }
    return $found;
}

/** Pin Smart Kit hasil build (pins.js): [id => pin]. */
function oiRestoreKitPins(string $pinsFile): array
{
    if ($pinsFile === '' || !is_file($pinsFile)) return [];
    $text = (string) file_get_contents($pinsFile);
    $at = strpos($text, 'SMART_PINS');
    $eq = $at === false ? false : strpos($text, '=', $at);
    if ($eq === false) return [];
    $list = json_decode(rtrim(trim(substr($text, $eq + 1)), ';'), true);
    $pins = [];
    foreach (is_array($list) ? $list : [] as $pin) if (is_array($pin) && isset($pin['id'])) $pins[(string) $pin['id']] = $pin;
    return $pins;
}

/**
 * Pindai record aktif yang pin-nya hilang dari pano.xml.
 *
 * @param array[] $records   record Object Informasi (keadaan editor atau database)
 * @param array[] $elements  WebElement yang ADA sekarang (keadaan editor atau pano.xml): minimal nodeID + targetID
 * @param array[] $panoramas hasil readPanoramaNodes (nodeId, value = node publik)
 * @return array{summary: array<string,int>, items: array[]}
 */
function oiRestoreScan(string $tourDir, array $records, array $elements, array $panoramas, string|callable $pinsFile = ''): array
{
    $present = [];
    foreach ($elements as $element) if (is_array($element)) $present[oiRestoreKey((string) ($element['nodeID'] ?? ''), (string) ($element['targetID'] ?? ''))] = true;
    $panoByPublic = [];
    foreach ($panoramas as $panorama) {
        foreach ([(string) ($panorama['nodeId'] ?? ''), (string) ($panorama['customNodeId'] ?? ''), (string) ($panorama['value'] ?? '')] as $id) if ($id !== '') $panoByPublic[strtolower($id)] = $panorama;
    }

    $inactive = 0; $activeRecords = 0; $groups = [];
    foreach ($records as $record) {
        if (!is_array($record)) continue;
        if (array_key_exists('isActive', $record) && !$record['isActive']) { $inactive++; continue; }   // nonaktif = tidak dipakai di tur, memang tanpa pin
        $activeRecords++;
        $node = trim(str_replace(['{', '}'], '', (string) ($record['nodeID'] ?? '')));
        $target = trim((string) ($record['targetID'] ?? ''));
        if ($node === '' || $target === '') continue;
        $key = oiRestoreKey($node, $target);
        $groups[$key]['node'] = $node;
        $groups[$key]['target'] = $target;
        $groups[$key]['records'][] = $record;
    }

    $missing = array_filter($groups, static fn(string $key): bool => !isset($present[$key]), ARRAY_FILTER_USE_KEY);
    $memory = $missing !== [] ? oiRestoreMemoryLoad($tourDir) : [];
    $needBackup = [];
    foreach ($missing as $key => $group) if (!isset($memory[$key]) && isset($panoByPublic[strtolower($group['node'])])) $needBackup[] = $key;
    $backups = oiRestoreBackupElements($tourDir, $needBackup);
    $pins = $needBackup !== [] ? oiRestoreKitPins(is_callable($pinsFile) ? (string) $pinsFile() : $pinsFile) : [];   // jalur pins.js baru dicari bila perlu (memuat Smart Hub itu berat)

    $items = []; $restorable = 0;
    foreach ($missing as $key => $group) {
        $first = $group['records'][0];
        $item = ['key' => $key, 'title' => trim((string) ($first['title'] ?? '')) ?: $group['target'], 'records' => count($group['records']), 'nodeID' => $group['node'], 'targetID' => $group['target'], 'source' => '', 'sourceLabel' => '', 'when' => '', 'reason' => '', 'element' => null];
        $panorama = $panoByPublic[strtolower($group['node'])] ?? null;
        if ($panorama === null) {
            $item['reason'] = 'Panorama {' . $group['node'] . '} tidak ada di pano.xml ini (nomor node berubah setelah export ulang?).';
        } else {
            $public = (string) $panorama['value']; $panoId = (string) $panorama['nodeId'];
            $from = null;
            if (isset($memory[$key]) && is_array($memory[$key])) { $from = $memory[$key]; $item['source'] = 'memori'; $item['sourceLabel'] = 'Memori CMS'; $item['when'] = (string) ($from['saved'] ?? ''); }
            elseif (isset($backups[$key])) { $from = $backups[$key]; $item['source'] = 'backup'; $item['sourceLabel'] = 'Cadangan pano.xml'; $item['when'] = (string) ($from['when'] ?? ''); }
            else {
                $pinId = '';
                foreach ($group['records'] as $record) { $pinId = (string) ($record['konten']['meta']['pin'] ?? ''); if ($pinId !== '') break; }
                $pin = $pinId !== '' ? ($pins[$pinId] ?? null) : null;
                if (is_array($pin) && isset($pin['pan'], $pin['tilt'])) { $from = ['pan' => $pin['pan'], 'tilt' => $pin['tilt'], 'fov' => 31.53, 'width' => 3200, 'height' => 2400]; $item['source'] = 'pin'; $item['sourceLabel'] = 'Pin Smart Kit'; }
            }
            if ($from === null) $item['reason'] = 'Posisi pin tidak ditemukan di memori CMS, cadangan, maupun pin Smart Kit. Tambahkan pin-nya dengan tombol Objek Info.';
            else { $item['element'] = oiRestoreElement($public, $panoId, $group['target'], (float) ($from['pan'] ?? 0), (float) ($from['tilt'] ?? 0), (float) ($from['fov'] ?? 21.02), (int) ($from['width'] ?? 400), (int) ($from['height'] ?? 600)); $restorable++; }
        }
        $items[] = $item;
    }
    usort($items, static fn(array $a, array $b): int => ($a['element'] === null) <=> ($b['element'] === null) ?: strnatcasecmp($a['nodeID'] . $a['targetID'], $b['nodeID'] . $b['targetID']));

    return ['summary' => ['pins' => count($groups), 'present' => count($groups) - count($missing), 'missing' => count($missing), 'restorable' => $restorable, 'unrecoverable' => count($missing) - $restorable, 'activeRecords' => $activeRecords, 'inactiveRecords' => $inactive], 'items' => $items];
}
