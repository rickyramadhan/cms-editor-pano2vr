<?php
declare(strict_types=1);

/**
 * Memori perubahan pano.xml (penamaan + terjemahan + isian hotspot).
 *
 * pano.xml dibuat ulang tiap tur di-export dari Pano2VR, sehingga hasil kerja di CMS (nama panorama, custom node ID,
 * koordinat, judul/tujuan hotspot, dan terjemahan) bisa tertimpa. Setiap penyimpanan lewat CMS dicatat ke sini
 * (hanya nilai yang BENAR-BENAR diubah di CMS). Saat pano.xml baru terdeteksi kehilangan catatan itu, Audit menawarkan
 * "Terapkan ulang": nilai dicocokkan lewat kunci yang sama (teks sumber / ID node / ID hotspot) lalu dilengkapi lagi.
 * Terjemahan hanya mengisi yang KOSONG, tidak menimpa terjemahan yang sudah ada di pano.xml baru.
 *
 * Lokasi: <CMS>/data/pano-memory/<jalur tur relatif htdocs>/memory.json (folder data/ tidak bisa diakses web, tidak ikut git).
 */
// Catatan: fungsi di berkas ini di-hoist PHP, sehingga require harus ada SEBELUM penjaga di bawah.
require_once dirname(__DIR__) . '/language-manager.php';
if (function_exists('panoMemoryFile')) return;

function panoMemoryFile(string $tourDir): string
{
    $src = rtrim(str_replace('\\', '/', $tourDir), '/');
    $real = @realpath($src);
    if ($real !== false) $src = rtrim(str_replace('\\', '/', $real), '/');
    $htdocs = str_replace('\\', '/', dirname(dirname(__DIR__)));
    $rel = stripos($src . '/', $htdocs . '/') === 0 ? trim(substr($src, strlen($htdocs)), '/') : 'luar-htdocs/' . substr(sha1($src), 0, 10) . '-' . basename($src);
    $rel = (string) preg_replace('~[^A-Za-z0-9._/-]~', '_', $rel);
    return str_replace('\\', '/', dirname(__DIR__)) . '/data/pano-memory/' . $rel . '/memory.json';
}

function panoMemoryEmpty(): array
{
    return ['version' => 1, 'updated' => '', 'translations' => [], 'panoramas' => [], 'hotspots' => [], 'ignored' => ''];
}

function panoMemoryLoad(string $tourDir): ?array
{
    $file = panoMemoryFile($tourDir);
    if (!is_file($file)) return null;
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? array_replace(panoMemoryEmpty(), $data) : null;
}

function panoMemoryWrite(string $tourDir, array $data): void
{
    $file = panoMemoryFile($tourDir);
    $dir = dirname($file);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Folder memori pano.xml gagal dibuat.');
    $guard = str_replace('\\', '/', dirname(__DIR__)) . '/data/pano-memory/.htaccess';
    if (!is_file($guard)) @file_put_contents($guard, "Require all denied\nDeny from all\n");
    $data['updated'] = date('c');
    if (file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) throw new RuntimeException('Memori pano.xml gagal disimpan.');
}

/** Catat perubahan yang dibuat di CMS: ['translations'=>[sumber=>[kode=>nilai]], 'panoramas'=>[node=>[atribut=>nilai]], 'hotspots'=>['node|id'=>[field=>nilai]]]. Nilai '' menghapus catatan. */
function panoMemoryRecord(string $tourDir, array $edits): void
{
    $data = panoMemoryLoad($tourDir) ?? panoMemoryEmpty();
    $touched = false;
    foreach (['translations', 'panoramas', 'hotspots'] as $section) {
        foreach ((array) ($edits[$section] ?? []) as $key => $fields) {
            if (!is_array($fields)) continue;
            foreach ($fields as $field => $value) {
                $value = (string) $value;
                if ($value === '') unset($data[$section][$key][$field]);
                else $data[$section][$key][$field] = $value;
                $touched = true;
            }
            if (isset($data[$section][$key]) && $data[$section][$key] === []) unset($data[$section][$key]);
        }
    }
    if (!$touched) return;
    $data['ignored'] = '';
    panoMemoryWrite($tourDir, $data);
}

/** Terjemahan di pano.xml saat ini: ['languages' => [kode...], 'map' => [sumber => [kode => nilai]]]. */
function panoMemoryTranslationState(DOMXPath $xpath): array
{
    $node = $xpath->query('//masternode/translations/json')?->item(0);
    if (!$node instanceof DOMElement) return ['languages' => [], 'map' => []];
    $data = json_decode($node->textContent, true);
    if (!is_array($data)) return ['languages' => [], 'map' => []];
    $languages = array_values(array_filter((array) ($data['languages'] ?? []), 'is_string'));
    $map = [];
    foreach ((array) ($data['translations'] ?? []) as $source => $values) {
        if (!is_array($values)) continue;
        foreach ($languages as $index => $code) $map[(string) $source][$code] = trim((string) ($values[$index] ?? ''));
    }
    return ['languages' => $languages, 'map' => $map];
}

/** Awal catatan untuk tur yang belum punya memori: terjemahan yang sudah ada di pano.xml (semuanya hasil kerja di CMS). */
function panoMemorySeed(string $tourDir, DOMDocument $dom): void
{
    if (panoMemoryLoad($tourDir) !== null) return;
    $state = panoMemoryTranslationState(new DOMXPath($dom));
    $edits = ['translations' => []];
    foreach ($state['map'] as $source => $codes) foreach ($codes as $code => $value) if ($value !== '') $edits['translations'][$source][$code] = $value;
    $data = panoMemoryEmpty();
    foreach ($edits['translations'] as $source => $codes) $data['translations'][$source] = $codes;
    panoMemoryWrite($tourDir, $data);
}

/** Teks yang dipakai sebagai sumber terjemahan: judul panorama dan judul hotspot. */
function panoMemoryUsedSources(DOMXPath $xpath): array
{
    $used = [];
    foreach ($xpath->query('//panorama/userdata/@title | //hotspot/@title') ?: [] as $attribute) {
        $text = trim((string) $attribute->nodeValue);
        if ($text !== '') $used[$text] = true;
    }
    return $used;
}

/** Daftar perubahan tersimpan yang belum ada di pano.xml ini. */
function panoMemoryPlan(DOMDocument $dom, array $data): array
{
    $xpath = new DOMXPath($dom);
    $plan = ['translations' => [], 'panoramas' => [], 'hotspots' => []];
    $used = panoMemoryUsedSources($xpath);
    $state = panoMemoryTranslationState($xpath);
    foreach ((array) $data['translations'] as $source => $codes) {
        if (!isset($used[(string) $source])) continue;   // teks sudah tidak dipakai tur ini
        foreach ((array) $codes as $code => $value) {
            if (trim((string) $value) === '') continue;
            if (trim((string) ($state['map'][(string) $source][(string) $code] ?? '')) === '') $plan['translations'][] = ['source' => (string) $source, 'code' => (string) $code, 'value' => (string) $value];
        }
    }
    foreach ((array) $data['panoramas'] as $nodeId => $attributes) {
        $element = $xpath->query('//panorama[@id=' . panoMemoryLiteral((string) $nodeId) . ']/userdata')?->item(0);
        if (!$element instanceof DOMElement) continue;
        foreach ((array) $attributes as $attribute => $value) {
            if (!in_array($attribute, ['title', 'customnodeid', 'latitude', 'longitude'], true)) continue;
            if ($element->getAttribute($attribute) !== (string) $value) $plan['panoramas'][] = ['node' => (string) $nodeId, 'field' => (string) $attribute, 'from' => $element->getAttribute($attribute), 'value' => (string) $value];
        }
    }
    foreach ((array) $data['hotspots'] as $key => $fields) {
        [$nodeId, $hotspotId] = array_pad(explode('|', (string) $key, 2), 2, '');
        $element = $xpath->query('//panorama[@id=' . panoMemoryLiteral($nodeId) . ']//hotspot[@id=' . panoMemoryLiteral($hotspotId) . ']')?->item(0);
        if (!$element instanceof DOMElement) continue;
        foreach ((array) $fields as $field => $value) {
            if (!in_array($field, ['title', 'url', 'target'], true)) continue;
            if ($element->getAttribute($field) !== (string) $value) $plan['hotspots'][] = ['node' => $nodeId, 'hotspot' => $hotspotId, 'field' => (string) $field, 'from' => $element->getAttribute($field), 'value' => (string) $value];
        }
    }
    $plan['count'] = count($plan['translations']) + count($plan['panoramas']) + count($plan['hotspots']);
    $plan['hash'] = sha1(json_encode([$plan['translations'], $plan['panoramas'], $plan['hotspots']], JSON_UNESCAPED_UNICODE));
    return $plan;
}

/** Terapkan rencana ke DOM; kembalikan jumlah perubahan. */
function panoMemoryApply(DOMDocument $dom, array $plan, string $root): int
{
    $xpath = new DOMXPath($dom);
    $applied = 0;
    if ($plan['translations'] !== []) {
        $jsonNode = $xpath->query('//masternode/translations/json')?->item(0);
        if (!$jsonNode instanceof DOMElement) $jsonNode = createTranslationBlock($dom, $xpath, $root);
        if ($jsonNode instanceof DOMElement) {
            $data = json_decode($jsonNode->textContent, true);
            $data = is_array($data) ? $data : [];
            $languages = array_values((array) ($data['languages'] ?? []));
            $translations = (array) ($data['translations'] ?? []);
            foreach ($plan['translations'] as $item) {
                $index = array_search($item['code'], $languages, true);
                if ($index === false) continue;
                $row = array_pad(array_values((array) ($translations[$item['source']] ?? [])), count($languages) + 1, '');
                if (trim((string) $row[$index]) !== '') continue;   // jangan menimpa terjemahan yang sudah ada
                $row[$index] = $item['value'];
                $translations[$item['source']] = $row;
                $applied++;
            }
            $data['translations'] = $translations === [] ? new stdClass() : $translations;
            $jsonNode->nodeValue = '';
            $jsonNode->appendChild($dom->createTextNode((string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
        }
    }
    foreach ($plan['panoramas'] as $item) {
        $element = $xpath->query('//panorama[@id=' . panoMemoryLiteral($item['node']) . ']/userdata')?->item(0);
        if ($element instanceof DOMElement) { $element->setAttribute($item['field'], $item['value']); $applied++; }
    }
    foreach ($plan['hotspots'] as $item) {
        $element = $xpath->query('//panorama[@id=' . panoMemoryLiteral($item['node']) . ']//hotspot[@id=' . panoMemoryLiteral($item['hotspot']) . ']')?->item(0);
        if ($element instanceof DOMElement) { $element->setAttribute($item['field'], $item['value']); $applied++; }
    }
    return $applied;
}

function panoMemoryLiteral(string $value): string
{
    if (!str_contains($value, "'")) return "'" . $value . "'";
    if (!str_contains($value, '"')) return '"' . $value . '"';
    return "concat('" . str_replace("'", "',\"'\",'", $value) . "')";
}

function translationPayloadHasValues(array $translations): bool
{
    foreach ($translations as $values) {
        if (!is_array($values)) continue;
        foreach ($values as $value) if (trim((string) $value) !== '') return true;
    }
    return false;
}

/** Buat <translations><json> di dalam <masternode> (format sama dengan hasil Pano2VR) dan kembalikan elemen <json>-nya. */
function createTranslationBlock(DOMDocument $dom, DOMXPath $xpath, string $root): ?DOMElement
{
    $master = $xpath->query('//masternode')?->item(0);
    if (!$master instanceof DOMElement) return null;
    $registry = languageRegistry($root);
    $languages = [];
    foreach ($registry as $code => $language) if ($code !== 'id') $languages[] = (string) ($language['pano'] ?? $code);
    if ($languages === []) return null;
    $block = $dom->createElement('translations');
    $json = $dom->createElement('json');
    $json->appendChild($dom->createTextNode(json_encode(['languages' => $languages, 'projectLanguage' => (string) ($registry['id']['pano'] ?? 'id-ID'), 'translations' => new stdClass()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)));
    $block->appendChild($dom->createTextNode("\n      "));
    $block->appendChild($json);
    foreach ($registry as $code => $language) {
        $meta = $dom->createElement('projecttranslation');
        $meta->setAttribute('langcode', (string) ($language['pano'] ?? $code));
        $meta->setAttribute('langname', (string) ($language['nativeLabel'] ?? $language['label'] ?? $code));
        $block->appendChild($dom->createTextNode("\n      "));
        $block->appendChild($meta);
    }
    $block->appendChild($dom->createTextNode("\n    "));
    $master->appendChild($dom->createTextNode('  '));
    $master->appendChild($block);
    $master->appendChild($dom->createTextNode("\n  "));
    return $json;
}
