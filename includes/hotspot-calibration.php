<?php
declare(strict_types=1);

/**
 * Kalibrasi Hotspot (tab CMS "Kalibrasi Hotspot"): menyimpan posisi baru hotspot hasil mesin ukur di sisi browser
 * (assets/js/hotspot-calibration-engine.js, dijalankan di dalam tur sungguhan) dan mengurungkannya.
 *
 * Sumber posisi tiap jenis hotspot:
 *   hotspot    -> <hotspot pan tilt> di pano.xml tur (hotspot darat, drone, drone ke darat); dicatat juga ke memori pano.xml (pano-memory.php)
 *                 supaya bisa dipulihkan bila tur di-export ulang dari Pano2VR
 *   webelement -> <webelement pan tilt> di pano.xml (Objek Info); memori "Load Objek Info" (oi-restore.php) ikut diperbarui
 *   lokasi     -> links[<lokasi sumber>][i] di assets/main-loader/lokasi-database.js (hotspot lokasi terdekat), ditulis lewat lokasiDbWrite
 *                 (cadangan + memori "Load Titik URL")
 * pano.xml dicadangkan dulu (data/backups, jenis "media"). Nilai lama tiap perpindahan dicatat di jurnal
 * data/hotspot-calibration/<tur>/last.json, sehingga "Urungkan" mengembalikan persis angka sebelumnya tanpa menimpa perubahan lain.
 * Tidak ada yang ditulis bila ada satu perpindahan yang tidak valid / tidak ditemukan sasarannya di pano.xml.
 */

require_once __DIR__ . '/pano-memory.php';
require_once __DIR__ . '/oi-restore.php';
require_once __DIR__ . '/lokasi-url.php';

const HCAL_SOURCES = ['hotspot', 'webelement', 'lokasi'];

/** pano.xml lokasi (folder tur berisi skin.js + pano.xml yang namanya sama dengan id lokasi), sama dengan save-pano.php. */
function hcFindPano(string $projectRoot, string $locationId): ?string
{
    foreach (new DirectoryIterator($projectRoot) as $entry) {
        if (!$entry->isDir() || $entry->isDot()) continue;
        $skinFile = $entry->getPathname() . '/skin.js';
        $candidate = $entry->getPathname() . '/pano.xml';
        if (!is_file($skinFile) || !is_file($candidate)) continue;
        if (strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', $entry->getFilename())) === $locationId) return $candidate;
    }
    return null;
}

function hcStoreBase(): string
{
    return defined('HCAL_STORE_BASE') ? rtrim((string) HCAL_STORE_BASE, '/\\') : str_replace('\\', '/', dirname(__DIR__)) . '/data/hotspot-calibration';
}

/** Jurnal terakhir tur ini: data/hotspot-calibration/<jalur tur relatif htdocs>/last.json (pola sama dengan memori URL Lokasi). */
function hcJournalFile(string $tourDir): string
{
    $src = rtrim(str_replace('\\', '/', $tourDir), '/');
    $real = @realpath($src);
    if ($real !== false) $src = rtrim(str_replace('\\', '/', $real), '/');
    $htdocs = str_replace('\\', '/', dirname(dirname(__DIR__)));
    $rel = stripos($src . '/', $htdocs . '/') === 0 ? trim(substr($src, strlen($htdocs)), '/') : 'luar-htdocs/' . substr(sha1($src), 0, 10) . '-' . basename($src);
    return hcStoreBase() . '/' . (string) preg_replace('~[^A-Za-z0-9._/-]~', '_', $rel) . '/last.json';
}

function hcJournalRead(string $tourDir): ?array
{
    $file = hcJournalFile($tourDir);
    if (!is_file($file)) return null;
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) && is_array($data['moves'] ?? null) && $data['moves'] !== [] ? $data : null;
}

/** Ringkasan jurnal untuk tombol "Urungkan": ['time' => iso, 'count' => n] atau null. */
function hcJournalSummary(string $tourDir): ?array
{
    $journal = hcJournalRead($tourDir);
    return $journal === null ? null : ['time' => (string) ($journal['time'] ?? ''), 'count' => count($journal['moves'])];
}

function hcJournalWrite(string $tourDir, array $journal): void
{
    $file = hcJournalFile($tourDir);
    $dir = dirname($file);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Folder jurnal kalibrasi gagal dibuat.');
    $guard = hcStoreBase() . '/.htaccess';
    if (!is_file($guard)) @file_put_contents($guard, "Require all denied\nDeny from all\n");
    if (file_put_contents($file, json_encode($journal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) throw new RuntimeException('Jurnal kalibrasi gagal disimpan.');
}

/** Rapikan + validasi kiriman dari CMS; melempar RuntimeException berpesan Indonesia bila ada yang tidak valid. */
function hcNormalizeMoves(array $in): array
{
    $out = [];
    foreach (array_slice($in, 0, 800) as $index => $m) {
        $nomor = (int) $index + 1;
        if (!is_array($m)) throw new RuntimeException('Perpindahan #' . $nomor . ' tidak valid.');
        $source = (string) ($m['source'] ?? '');
        if (!in_array($source, HCAL_SOURCES, true)) throw new RuntimeException('Jenis hotspot perpindahan #' . $nomor . ' tidak dikenal.');
        if (!isset($m['pan'], $m['tilt']) || !is_numeric($m['pan']) || !is_numeric($m['tilt'])) throw new RuntimeException('Posisi perpindahan #' . $nomor . ' bukan angka.');
        $pan = (float) $m['pan']; $tilt = (float) $m['tilt'];
        if (!is_finite($pan) || !is_finite($tilt) || abs($pan) > 3600 || $tilt < -90 || $tilt > 90) throw new RuntimeException('Posisi perpindahan #' . $nomor . ' di luar batas.');
        $row = ['source' => $source, 'pan' => $pan, 'tilt' => $tilt, 'label' => mb_substr(trim(strip_tags((string) ($m['label'] ?? ''))), 0, 120), 'kind' => preg_replace('/[^a-z]/', '', strtolower((string) ($m['kind'] ?? '')))];
        if (!empty($m['clear'])) $row['clear'] = true;   // hanya dipakai urungkan: hapus pan/tilt (posisi bawaan) bila sebelumnya tidak tersimpan
        if ($source === 'lokasi') {
            $row['sourceSlug'] = lokasiCleanId($m['sourceSlug'] ?? '');
            $row['id'] = lokasiCleanId($m['id'] ?? '');
            if ($row['sourceSlug'] === '' || $row['id'] === '') throw new RuntimeException('Lokasi perpindahan #' . $nomor . ' tidak valid.');
        } else {
            $row['scene'] = trim((string) ($m['scene'] ?? ''));
            $row['id'] = trim((string) ($m['id'] ?? ''));
            foreach (['scene', 'id'] as $field) if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,63}$/D', $row[$field])) throw new RuntimeException('Node atau ID hotspot perpindahan #' . $nomor . ' tidak valid.');
        }
        $out[] = $row;
    }
    if ($out === []) throw new RuntimeException('Tidak ada perubahan untuk diterapkan.');
    return $out;
}

function hcLiteral(string $value): string
{
    if (!str_contains($value, "'")) return "'" . $value . "'";
    if (!str_contains($value, '"')) return '"' . $value . '"';
    return "concat('" . str_replace("'", "',\"'\",'", $value) . "')";
}

function hcFmt(float $value): string
{
    return number_format($value, 3, '.', '');
}

function hcSame(float $a, float $b): bool
{
    return abs($a - $b) <= 0.0005;
}

/**
 * Terapkan perpindahan. $recordJournal=false dipakai urungkan. Mengembalikan ['applied' => n, 'missing' => [...], 'journal' => [...]].
 * Semua sasaran diperiksa dulu; bila ada yang tidak ditemukan, tidak ada yang ditulis (melempar RuntimeException).
 */
function hcApply(string $projectRoot, string $assetsRoot, string $locationId, array $moves, bool $recordJournal = true): array
{
    $moves = hcNormalizeMoves($moves);
    $panoFile = hcFindPano($projectRoot, $locationId);
    if ($panoFile === null) throw new RuntimeException('pano.xml lokasi tidak ditemukan.');
    $tourDir = dirname($panoFile);
    $panoMoves = array_values(array_filter($moves, static fn(array $m): bool => $m['source'] !== 'lokasi'));
    $lokasiMoves = array_values(array_filter($moves, static fn(array $m): bool => $m['source'] === 'lokasi'));
    $journal = []; $missing = [];

    // ---- siapkan perubahan pano.xml di DOM (belum disimpan)
    $dom = null; $xmlBefore = '';
    if ($panoMoves !== []) {
        $xmlBefore = (string) file_get_contents($panoFile);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = true;
        if (!@$dom->loadXML($xmlBefore)) throw new RuntimeException('pano.xml tidak valid atau tidak dapat dibaca.');
        $xpath = new DOMXPath($dom);
        foreach ($panoMoves as $m) {
            $tag = $m['source'] === 'hotspot' ? 'hotspot' : 'webelement';
            $element = $xpath->query('//panorama[@id=' . hcLiteral($m['scene']) . ']//' . $tag . '[@id=' . hcLiteral($m['id']) . ']')?->item(0);
            if (!$element instanceof DOMElement) { $missing[] = $m['scene'] . '/' . $m['id']; continue; }
            $fromPan = (float) $element->getAttribute('pan'); $fromTilt = (float) $element->getAttribute('tilt');
            if (hcSame($fromPan, $m['pan']) && hcSame($fromTilt, $m['tilt'])) continue;   // tidak berubah
            $element->setAttribute('pan', hcFmt($m['pan']));
            $element->setAttribute('tilt', hcFmt($m['tilt']));
            $journal[] = ['source' => $m['source'], 'scene' => $m['scene'], 'id' => $m['id'], 'label' => $m['label'], 'kind' => $m['kind'], 'from' => ['pan' => $fromPan, 'tilt' => $fromTilt], 'to' => ['pan' => $m['pan'], 'tilt' => $m['tilt']]];
        }
    }

    // ---- siapkan perubahan lokasi-database.js (divalidasi penuh sebelum apa pun ditulis)
    $lokasiFile = lokasiDbFile($assetsRoot); $newDb = null;
    if ($lokasiMoves !== []) {
        $db = lokasiDbRead($lokasiFile);
        foreach ($lokasiMoves as $m) {
            $found = false;
            foreach ((array) ($db['links'][$m['sourceSlug']] ?? []) as $k => $link) {
                if (!is_array($link) || ($link['to'] ?? '') !== $m['id']) continue;
                $found = true;
                $had = isset($link['pan'], $link['tilt']) && is_numeric($link['pan']) && is_numeric($link['tilt']);
                $fromPan = $had ? (float) $link['pan'] : null; $fromTilt = $had ? (float) $link['tilt'] : null;
                if (!empty($m['clear'])) { unset($db['links'][$m['sourceSlug']][$k]['pan'], $db['links'][$m['sourceSlug']][$k]['tilt']); $toPan = null; $toTilt = null; }
                else {
                    $toPan = hcWrapPan((float) $m['pan']); $toTilt = round((float) $m['tilt'], 2);
                    if ($had && hcSame($fromPan, $toPan) && hcSame($fromTilt, $toTilt)) break;
                    $db['links'][$m['sourceSlug']][$k]['pan'] = $toPan; $db['links'][$m['sourceSlug']][$k]['tilt'] = $toTilt;
                }
                $journal[] = ['source' => 'lokasi', 'sourceSlug' => $m['sourceSlug'], 'id' => $m['id'], 'label' => $m['label'], 'kind' => $m['kind'], 'from' => $had ? ['pan' => $fromPan, 'tilt' => $fromTilt] : null, 'to' => $toPan === null ? null : ['pan' => $toPan, 'tilt' => $toTilt]];
                break;
            }
            if (!$found) $missing[] = $m['sourceSlug'] . ' → ' . $m['id'];
        }
        $newDb = lokasiDbNormalize($db);
    }
    if ($missing !== []) throw new RuntimeException('Hotspot tidak ditemukan di data tur (mungkin tur sudah di-export ulang): ' . implode(', ', array_slice($missing, 0, 6)) . (count($missing) > 6 ? ' dan ' . (count($missing) - 6) . ' lainnya' : '') . '. Tidak ada yang diubah; jalankan kalibrasi lagi.');
    if ($journal === []) return ['applied' => 0, 'missing' => [], 'journal' => []];

    // ---- tulis: pano.xml (dengan cadangan), lalu lokasi-database.js (cadangan + memori ditangani lokasiDbWrite)
    if ($dom instanceof DOMDocument && array_filter($journal, static fn(array $j): bool => $j['source'] !== 'lokasi') !== []) {
        $backupDirectory = cmsBackupEnsure($tourDir, 'media');
        if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) throw new RuntimeException('Folder cadangan pano.xml gagal dibuat.');
        if (!copy($panoFile, $backupDirectory . '/pano-before-calibration-' . date('Ymd-His') . '.xml')) throw new RuntimeException('Cadangan pano.xml gagal dibuat.');
        $xml = $dom->saveXML();
        if ($xml === false || file_put_contents($panoFile, $xml, LOCK_EX) === false) throw new RuntimeException('pano.xml gagal disimpan.');
        try { hcRemember($tourDir, $xmlBefore, $xml, $journal); } catch (Throwable) { /* memori hanya cadangan pemulihan; gagal mencatat tidak membatalkan simpan */ }
    }
    if ($newDb !== null && array_filter($journal, static fn(array $j): bool => $j['source'] === 'lokasi') !== []) lokasiDbWrite($lokasiFile, $newDb);

    if ($recordJournal) hcJournalWrite($tourDir, ['version' => 1, 'time' => date('c'), 'location' => $locationId, 'moves' => $journal]);
    else { $file = hcJournalFile($tourDir); if (is_file($file)) @unlink($file); }
    return ['applied' => count($journal), 'missing' => [], 'journal' => $journal];
}

function hcWrapPan(float $pan): float
{
    $wrapped = fmod(fmod($pan + 180.0, 360.0) + 360.0, 360.0) - 180.0;
    return round($wrapped, 2);
}

/** Catat ke memori pano.xml (hotspot pan/tilt) dan memori Load Objek Info (posisi pin), agar sisa export ulang bisa dipulihkan. */
function hcRemember(string $tourDir, string $xmlBefore, string $xmlAfter, array $journal): void
{
    $edits = [];
    foreach ($journal as $j) if ($j['source'] === 'hotspot') $edits[$j['scene'] . '|' . $j['id']] = ['pan' => hcFmt((float) $j['to']['pan']), 'tilt' => hcFmt((float) $j['to']['tilt'])];
    if ($edits !== []) panoMemoryRecord($tourDir, ['hotspots' => $edits]);
    if (array_filter($journal, static fn(array $j): bool => $j['source'] === 'webelement') === []) return;
    $public = [];
    if (preg_match_all('/<panorama\b[^>]*\bid="([^"]+)"[^>]*>(.*?)<\/panorama>/is', $xmlAfter, $panoramas, PREG_SET_ORDER)) {
        foreach ($panoramas as $panorama) {
            $value = $panorama[1];
            if (preg_match('/<userdata\s+([^>]*)\/>/i', $panorama[2], $userdata)) { $a = oiRestoreAttributes($userdata[1]); $value = trim((string) ($a['customnodeid'] ?? '')) ?: (trim((string) ($a['nodeid'] ?? '')) ?: $panorama[1]); }
            $public[strtolower($panorama[1])] = $value;
        }
    }
    oiRestoreMemoryRemember($tourDir, array_keys(oiRestoreParseElements($xmlBefore)), array_values(oiRestoreParseElements($xmlAfter)), $public);
}

/** Urungkan kalibrasi terakhir: kembalikan angka lama dari jurnal. */
function hcUndo(string $projectRoot, string $assetsRoot, string $locationId): array
{
    $panoFile = hcFindPano($projectRoot, $locationId);
    if ($panoFile === null) throw new RuntimeException('pano.xml lokasi tidak ditemukan.');
    $journal = hcJournalRead(dirname($panoFile));
    if ($journal === null) throw new RuntimeException('Belum ada kalibrasi yang bisa diurungkan untuk lokasi ini.');
    $back = [];
    foreach ($journal['moves'] as $j) {
        $row = ['source' => $j['source'], 'label' => $j['label'] ?? '', 'kind' => $j['kind'] ?? '', 'id' => $j['id']];
        if ($j['source'] === 'lokasi') $row['sourceSlug'] = $j['sourceSlug'];
        else $row['scene'] = $j['scene'];
        if (is_array($j['from'] ?? null)) { $row['pan'] = $j['from']['pan']; $row['tilt'] = $j['from']['tilt']; }
        else { $row['pan'] = 0; $row['tilt'] = 0; $row['clear'] = true; }
        $back[] = $row;
    }
    return hcApply($projectRoot, $assetsRoot, $locationId, $back, false);
}
