<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';

function respond(int $status, bool $success, string $message): never
{
    http_response_code($status);
    echo json_encode(compact('success', 'message'), JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, false, 'Metode tidak diizinkan.');
    $payload = json_decode((string) file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) respond(403, false, 'Sesi formulir tidak valid.');
    $locationId = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', (string) ($payload['id_lokasi'] ?? '')));
    if ($locationId === '') respond(422, false, 'Lokasi tidak valid.');

    $root = activeProjectRoot();
    $panoFile = null;
    foreach (new DirectoryIterator($root) as $entry) {
        if (!$entry->isDir() || $entry->isDot()) continue;
        $skinFile = $entry->getPathname() . '/skin.js';
        $candidate = $entry->getPathname() . '/pano.xml';
        if (!is_file($skinFile) || !is_file($candidate)) continue;
        $folderId = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', $entry->getFilename()));
        if ($folderId === $locationId) { $panoFile = $candidate; break; }
    }
    if ($panoFile === null) respond(404, false, 'pano.xml lokasi tidak ditemukan.');

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = true;
    $dom->formatOutput = false;
    if (!$dom->load($panoFile)) respond(422, false, 'pano.xml tidak valid.');
    $xpath = new DOMXPath($dom);
    $customNodeIds = [];
    foreach ((array) ($payload['panoramas'] ?? []) as $item) {
        if (!is_array($item)) continue;
        $nodeId = trim((string) ($item['nodeId'] ?? ''));
        $customNodeId = trim((string) ($item['customNodeId'] ?? ''));
        $title = trim(strip_tags((string) ($item['title'] ?? '')));
        $latitude = trim((string) ($item['latitude'] ?? ''));
        $longitude = trim((string) ($item['longitude'] ?? ''));
        if ($customNodeId !== '' && !preg_match('/^[A-Za-z0-9_-]{1,100}$/D', $customNodeId)) throw new RuntimeException('Custom Node ID hanya boleh berisi huruf, angka, dash, dan underscore.');
        $customKey = strtolower($customNodeId);
        if ($customKey !== '' && isset($customNodeIds[$customKey])) throw new RuntimeException('Custom Node ID "' . $customNodeId . '" digunakan lebih dari sekali.');
        if ($customKey !== '') $customNodeIds[$customKey] = true;
        if ($latitude !== '' && (!is_numeric($latitude) || (float) $latitude < -90 || (float) $latitude > 90)) throw new RuntimeException('Latitude ' . $nodeId . ' tidak valid.');
        if ($longitude !== '' && (!is_numeric($longitude) || (float) $longitude < -180 || (float) $longitude > 180)) throw new RuntimeException('Longitude ' . $nodeId . ' tidak valid.');
        $userdata = $xpath->query('//panorama[@id=' . xpathLiteral($nodeId) . ']/userdata')?->item(0);
        if (!$userdata instanceof DOMElement) continue;
        $userdata->setAttribute('title', $title);
        $userdata->setAttribute('customnodeid', $customNodeId);
        $userdata->setAttribute('latitude', $latitude === '' ? '' : number_format((float) $latitude, 7, '.', ''));
        $userdata->setAttribute('longitude', $longitude === '' ? '' : number_format((float) $longitude, 7, '.', ''));
    }
    foreach ((array) ($payload['hotspots'] ?? []) as $item) {
        $nodeId = (string) ($item['nodeId'] ?? ''); $id = (string) ($item['id'] ?? '');
        $query = '//panorama[@id=' . xpathLiteral($nodeId) . ']//hotspot[@id=' . xpathLiteral($id) . ']';
        $element = $xpath->query($query)?->item(0);
        if (!$element instanceof DOMElement) continue;
        foreach (['title', 'url', 'target'] as $field) $element->setAttribute($field, trim((string) ($item[$field] ?? '')));
    }
    $jsonNode = $xpath->query('//masternode/translations/json')?->item(0);
    if ($jsonNode instanceof DOMElement && is_array($payload['translations'] ?? null)) {
        $data = json_decode($jsonNode->textContent, true, 512, JSON_THROW_ON_ERROR);
        $languageCount = count((array) ($data['languages'] ?? []));
        $existingTranslations = (array) ($data['translations'] ?? []);
        foreach ($payload['translations'] as $source => $submittedValues) {
            if (!is_string($source) || !is_array($submittedValues)) continue;
            $values = array_values(array_map(static fn(mixed $value): string => trim((string) $value), $submittedValues));
            $values = array_slice(array_pad($values, $languageCount, ''), 0, $languageCount);
            $values[] = (string) (($existingTranslations[$source][$languageCount] ?? ''));
            $existingTranslations[$source] = $values;
        }
        $data['translations'] = $existingTranslations;
        $jsonNode->nodeValue = '';
        $jsonNode->appendChild($dom->createTextNode((string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    }
    $backupDirectory = dirname($panoFile) . '/.media-backups';
    if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) throw new RuntimeException('Folder backup gagal dibuat.');
    if (!copy($panoFile, $backupDirectory . '/pano-before-audit-editor-' . date('Ymd-His') . '.xml')) throw new RuntimeException('Backup pano.xml gagal dibuat.');
    if ($dom->save($panoFile) === false) respond(500, false, 'Gagal menyimpan pano.xml.');
    respond(200, true, 'Panorama, hotspot, dan terjemahan pano.xml berhasil disimpan.');
} catch (Throwable $error) {
    respond(400, false, $error->getMessage());
}

function xpathLiteral(string $value): string
{
    if (!str_contains($value, "'")) return "'" . $value . "'";
    if (!str_contains($value, '"')) return '"' . $value . '"';
    return "concat('" . str_replace("'", "',\"'\",'", $value) . "')";
}
