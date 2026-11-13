<?php
declare(strict_types=1);

/**
 * Deteksi isi pano.xml (panorama, hotspot, terjemahan) untuk tab Audit CMS.
 * Dipakai bersama oleh index.php (tab Audit) dan ai-hub.php (sinkron data tur ke AI Guide Hub),
 * sehingga yang tampil di Audit dan yang dikirim ke Hub selalu berasal dari satu kode yang sama.
 */
if (function_exists('auditTour')) return;

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
