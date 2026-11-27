<?php
declare(strict_types=1);

/**
 * Edit Hotspot langsung (tombol "Edit di panorama" di Audit › Hotspot): menyimpan perubahan dari editor visual
 * (assets/js/hotspot-live-editor.js + assets/js/hotspot-live-agent.js, berjalan di dalam tur sungguhan dengan HUD Smart Kit asli).
 *
 * Satu panorama per panggilan. Operasi (semua opsional):
 *   update : [ { id, title?, url?, pan?, tilt? } ]   ubah hotspot yang sudah ada (judul, tujuan "{node}" atau URL, posisi)
 *   delete : [ id, ... ]                              hapus hotspot
 *   add    : [ { title, url, pan, tilt, skinid, copyFromNode?, copyFromId? } ]   hotspot baru = salinan atribut sebuah hotspot contoh
 *            berjenis (skinid) yang sama (gambar, ukuran, jarak 3D ikut), dengan id baru PointNN, judul/tujuan/posisi baru, tanpa "target" (arah tiba)
 * Semua sasaran divalidasi dulu: satu yang tidak valid / tidak ditemukan = tidak ada yang ditulis. pano.xml dicadangkan dulu
 * (data/backups, jenis "media"). Perubahan pada hotspot yang sudah ada (judul/tujuan/posisi) dicatat ke memori pano.xml supaya bisa
 * dipulihkan bila tur di-export ulang dari Pano2VR; hotspot BARU dan penghapusan tidak ikut dipulihkan otomatis.
 */

require_once __DIR__ . '/hotspot-calibration.php';   // hcFindPano, hcLiteral, hcFmt, hcSame
require_once __DIR__ . '/pano-memory.php';

function heId(mixed $value, string $label): string
{
    $value = trim((string) $value);
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,63}$/D', $value)) throw new RuntimeException($label . ' tidak valid.');
    return $value;
}

function heTitle(mixed $value): string
{
    $text = trim(strip_tags((string) $value));
    $text = (string) (preg_replace('/\s+/u', ' ', $text) ?? $text);
    if (mb_strlen($text) > 120) throw new RuntimeException('Judul hotspot terlalu panjang (maksimal 120 karakter).');
    return $text;
}

/** Tujuan hotspot: kosong, "{id-panorama}", atau alamat web http(s). */
function heUrl(mixed $value): string
{
    $url = trim((string) $value);
    if ($url === '' || preg_match('/^\{[A-Za-z0-9_-]{1,100}\}$/D', $url)) return $url;
    if (preg_match('#^https?://[^\s<>"\']{1,500}$#i', $url)) return $url;
    throw new RuntimeException('Tujuan hotspot tidak valid: ' . mb_substr($url, 0, 60) . ' (pakai {id panorama} atau alamat https://…).');
}

function heAngle(mixed $value, string $label, float $limit): float
{
    if (!is_numeric($value) || !is_finite((float) $value) || abs((float) $value) > $limit) throw new RuntimeException($label . ' tidak valid.');
    return (float) $value;
}

/** Hotspot contoh untuk membuat yang baru: pilihan klien (bila jenisnya cocok), lalu panorama ini, lalu tur mana pun dengan skinid yang sama. */
function heTemplate(DOMXPath $xpath, DOMElement $pano, string $skin, string $fromNode, string $fromId): ?DOMElement
{
    if ($fromNode !== '' && $fromId !== '') {
        $hit = $xpath->query('//panorama[@id=' . hcLiteral($fromNode) . ']/hotspots/hotspot[@id=' . hcLiteral($fromId) . ']')?->item(0);
        if ($hit instanceof DOMElement && $hit->getAttribute('skinid') === $skin) return $hit;
    }
    foreach (['./hotspots/hotspot[@skinid=' . hcLiteral($skin) . ']', '//hotspot[@skinid=' . hcLiteral($skin) . ']'] as $query) {
        $hit = $xpath->query($query, $query[0] === '.' ? $pano : null)?->item(0);
        if ($hit instanceof DOMElement) return $hit;
    }
    return null;
}

/** Terapkan operasi ke satu panorama. Mengembalikan ['updated' => n, 'deleted' => n, 'added' => [id baru...], 'changed' => bool]. */
function heApply(string $projectRoot, string $locationId, array $in): array
{
    $nodeId = heId($in['nodeId'] ?? '', 'Panorama');
    $updates = array_slice((array) ($in['update'] ?? []), 0, 300);
    $deletes = array_slice((array) ($in['delete'] ?? []), 0, 300);
    $adds = array_slice((array) ($in['add'] ?? []), 0, 100);
    if ($updates === [] && $deletes === [] && $adds === []) throw new RuntimeException('Tidak ada perubahan untuk disimpan.');

    $panoFile = hcFindPano($projectRoot, $locationId);
    if ($panoFile === null) throw new RuntimeException('pano.xml lokasi tidak ditemukan.');
    $tourDir = dirname($panoFile);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = true;
    if (!@$dom->load($panoFile)) throw new RuntimeException('pano.xml tidak valid atau tidak dapat dibaca.');
    $xpath = new DOMXPath($dom);
    $pano = $xpath->query('//panorama[@id=' . hcLiteral($nodeId) . ']')?->item(0);
    if (!$pano instanceof DOMElement) throw new RuntimeException('Panorama ' . $nodeId . ' tidak ditemukan di pano.xml.');
    $container = $xpath->query('./hotspots', $pano)?->item(0);
    $find = static fn(string $id): ?DOMElement => ($container instanceof DOMElement && ($el = $xpath->query('./hotspot[@id=' . hcLiteral($id) . ']', $container)?->item(0)) instanceof DOMElement) ? $el : null;

    // ---- validasi semua dulu (tidak ada yang ditulis sebelum semuanya lolos)
    $plan = ['update' => [], 'delete' => [], 'add' => []];
    $touched = [];
    foreach ($updates as $i => $u) {
        if (!is_array($u)) throw new RuntimeException('Perubahan hotspot #' . ($i + 1) . ' tidak valid.');
        $id = heId($u['id'] ?? '', 'ID hotspot');
        if ($find($id) === null) throw new RuntimeException('Hotspot ' . $id . ' tidak ditemukan di ' . $nodeId . ' (mungkin tur sudah di-export ulang). Tidak ada yang diubah.');
        $fields = [];
        if (array_key_exists('title', $u)) $fields['title'] = heTitle($u['title']);
        if (array_key_exists('url', $u)) $fields['url'] = heUrl($u['url']);
        if (array_key_exists('pan', $u)) $fields['pan'] = heAngle($u['pan'], 'Pan hotspot ' . $id, 3600);
        if (array_key_exists('tilt', $u)) $fields['tilt'] = heAngle($u['tilt'], 'Tilt hotspot ' . $id, 90);
        if ($fields !== []) { $plan['update'][$id] = $fields; $touched[$id] = true; }
    }
    foreach ($deletes as $id) {
        $id = heId($id, 'ID hotspot');
        if ($find($id) === null) throw new RuntimeException('Hotspot ' . $id . ' tidak ditemukan di ' . $nodeId . '. Tidak ada yang diubah.');
        $plan['delete'][$id] = true;
    }
    foreach (array_keys($plan['delete']) as $id) unset($plan['update'][$id]);   // dihapus = perubahan lain pada hotspot itu tidak berarti
    foreach ($adds as $i => $a) {
        if (!is_array($a)) throw new RuntimeException('Hotspot baru #' . ($i + 1) . ' tidak valid.');
        $skin = trim((string) ($a['skinid'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9_-]{1,80}$/D', $skin)) throw new RuntimeException('Jenis hotspot baru #' . ($i + 1) . ' tidak valid.');
        $row = ['skin' => $skin, 'title' => heTitle($a['title'] ?? ''), 'url' => heUrl($a['url'] ?? ''), 'pan' => heAngle($a['pan'] ?? null, 'Pan hotspot baru', 3600), 'tilt' => heAngle($a['tilt'] ?? null, 'Tilt hotspot baru', 90),
            'fromNode' => preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,63}$/D', (string) ($a['copyFromNode'] ?? '')) ? (string) $a['copyFromNode'] : '', 'fromId' => preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,63}$/D', (string) ($a['copyFromId'] ?? '')) ? (string) $a['copyFromId'] : ''];
        if ($row['title'] === '') throw new RuntimeException('Hotspot baru #' . ($i + 1) . ' belum diberi judul.');
        $row['template'] = heTemplate($xpath, $pano, $skin, $row['fromNode'], $row['fromId']);
        if (!$row['template'] instanceof DOMElement) throw new RuntimeException('Belum ada hotspot contoh berjenis "' . $skin . '" di tur ini, jadi hotspot baru tidak bisa dibuat dari editor. Buat satu lewat Pano2VR dulu.');
        $plan['add'][] = $row;
    }
    if ($plan['update'] === [] && $plan['delete'] === [] && $plan['add'] === []) return ['updated' => 0, 'deleted' => 0, 'added' => [], 'changed' => false];

    // pastikan ada wadah <hotspots> bila perlu menambah
    if ($plan['add'] !== [] && !$container instanceof DOMElement) {
        $source = $xpath->query('//panorama/hotspots')?->item(0);
        if (!$source instanceof DOMElement) throw new RuntimeException('Tur ini belum punya blok hotspot sama sekali.');
        $container = $source->cloneNode(false);
        foreach ($xpath->query('./*[not(self::hotspot)]', $source) as $child) $container->appendChild($child->cloneNode(true));
        $pano->appendChild($container);
    }

    // ---- terapkan ke DOM
    $memory = [];
    foreach ($plan['update'] as $id => $fields) {
        $el = $find($id);
        foreach ($fields as $name => $value) {
            $text = in_array($name, ['pan', 'tilt'], true) ? hcFmt((float) $value) : (string) $value;
            if (in_array($name, ['pan', 'tilt'], true) ? hcSame((float) $el->getAttribute($name), (float) $value) : $el->getAttribute($name) === $text) continue;
            $el->setAttribute($name, $text);
            $memory[$nodeId . '|' . $id][$name] = $text;   // teks kosong = hapus catatan memori untuk field itu (aturan panoMemoryRecord)
        }
    }
    foreach (array_keys($plan['delete']) as $id) {
        $el = $find($id);
        $previous = $el->previousSibling;
        if ($previous instanceof DOMText && trim($previous->nodeValue ?? '') === '') $previous->parentNode?->removeChild($previous);
        $el->parentNode?->removeChild($el);
    }
    $added = [];
    foreach ($plan['add'] as $row) {
        $used = []; $last = null;
        foreach ($xpath->query('./hotspot', $container) as $existing) { $used[strtolower($existing->getAttribute('id'))] = true; $last = $existing; }
        $n = 1; while (isset($used[strtolower(sprintf('Point%02d', $n))])) $n++;
        $newId = sprintf('Point%02d', $n);
        $new = $row['template']->cloneNode(false);
        $new->setAttribute('id', $newId);
        $new->setAttribute('title', $row['title']);
        $new->setAttribute('url', $row['url']);
        $new->setAttribute('pan', hcFmt($row['pan']));
        $new->setAttribute('tilt', hcFmt($row['tilt']));
        $new->removeAttribute('target');   // hotspot baru memakai arah bawaan panorama tujuan; bisa diatur lewat kartu Audit
        $indent = ($last instanceof DOMElement && $last->previousSibling instanceof DOMText && trim($last->previousSibling->nodeValue ?? '') === '') ? $last->previousSibling->nodeValue : "\n      ";
        $anchor = $last instanceof DOMElement ? $last->nextSibling : null;
        $container->insertBefore($dom->createTextNode((string) $indent), $anchor);
        $container->insertBefore($new, $anchor);
        $added[] = $newId;
    }

    // ---- tulis: cadangan dulu, lalu pano.xml
    $backupDirectory = cmsBackupEnsure($tourDir, 'media');
    if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) throw new RuntimeException('Folder cadangan pano.xml gagal dibuat.');
    if (!copy($panoFile, $backupDirectory . '/pano-before-hotspot-editor-' . date('Ymd-His') . '.xml')) throw new RuntimeException('Cadangan pano.xml gagal dibuat.');
    $xml = $dom->saveXML();
    if ($xml === false || file_put_contents($panoFile, $xml, LOCK_EX) === false) throw new RuntimeException('pano.xml gagal disimpan.');
    try { if ($memory !== []) panoMemoryRecord($tourDir, ['hotspots' => $memory]); }   // judul/tujuan/posisi hotspot yang sudah ada, supaya bisa dipulihkan setelah export ulang
    catch (Throwable) { /* memori hanya cadangan pemulihan; gagal mencatat tidak membatalkan simpan */ }
    return ['updated' => count($plan['update']), 'deleted' => count($plan['delete']), 'added' => $added, 'changed' => true];
}
