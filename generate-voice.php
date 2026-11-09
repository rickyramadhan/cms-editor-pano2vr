<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';

/** Kunci ElevenLabs TIDAK disimpan di kode. Isi lewat variabel lingkungan ELEVENLABS_API_KEY, atau data/secrets.json: {"elevenlabs_api_key":"..."} (folder data/ diblokir dari web dan tidak masuk git). */
function elevenLabsApiKey(): string
{
    $key = (string) (getenv('ELEVENLABS_API_KEY') ?: ($_SERVER['ELEVENLABS_API_KEY'] ?? ''));
    if ($key === '') {
        $f = cmsDataDir() . '/secrets.json';
        $j = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
        $key = (string) ($j['elevenlabs_api_key'] ?? '');
    }
    return trim($key);
}
const ELEVENLABS_MODEL_ID = 'eleven_v3';
const ELEVENLABS_VOICE_ID = '65oH2vf8DV9U5dqEre2g';
const MAX_CHUNK_LENGTH = 450;

function respond(int $status, bool $success, string $message, array $extra = []): never
{
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function splitVoiceText(string $text): array
{
    $sentences = preg_split('/(?<=[.!?。！？])\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $chunks = [];
    $current = '';

    foreach ($sentences as $sentence) {
        while (mb_strlen($sentence) > MAX_CHUNK_LENGTH) {
            $available = MAX_CHUNK_LENGTH - mb_strlen($current) - ($current === '' ? 0 : 1);
            if ($available <= 0) {
                $chunks[] = $current;
                $current = '';
                $available = MAX_CHUNK_LENGTH;
            }
            $piece = mb_substr($sentence, 0, $available);
            $spacePosition = mb_strrpos($piece, ' ');
            if ($spacePosition !== false && $spacePosition > (int) ($available * 0.6)) {
                $piece = mb_substr($piece, 0, $spacePosition);
            }
            $current = trim($current . ' ' . $piece);
            $sentence = trim(mb_substr($sentence, mb_strlen($piece)));
            if (mb_strlen($current) >= MAX_CHUNK_LENGTH * 0.8) {
                $chunks[] = $current;
                $current = '';
            }
        }

        $candidate = trim($current . ' ' . $sentence);
        if (mb_strlen($candidate) > MAX_CHUNK_LENGTH && $current !== '') {
            $chunks[] = $current;
            $current = $sentence;
        } else {
            $current = $candidate;
        }
    }

    if ($current !== '') $chunks[] = $current;
    return $chunks ?: [$text];
}

function generateAudioChunk(string $text): string
{
    if (elevenLabsApiKey() === '') throw new RuntimeException('Kunci ElevenLabs belum diatur. Isi variabel lingkungan ELEVENLABS_API_KEY atau data/secrets.json (lihat README).');
    $curl = curl_init('https://api.elevenlabs.io/v1/text-to-speech/' . ELEVENLABS_VOICE_ID);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_HTTPHEADER => ['xi-api-key: ' . elevenLabsApiKey(), 'Content-Type: application/json', 'Accept: audio/mpeg'],
        CURLOPT_POSTFIELDS => json_encode(['text' => $text, 'model_id' => ELEVENLABS_MODEL_ID], JSON_THROW_ON_ERROR),
    ]);
    $audio = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);
    if ($audio === false) throw new RuntimeException('ElevenLabs tidak dapat dihubungi: ' . $curlError);
    if ($status < 200 || $status >= 300) {
        $apiError = json_decode($audio, true);
        throw new RuntimeException((string) ($apiError['detail']['message'] ?? 'ElevenLabs menolak permintaan (HTTP ' . $status . ').'));
    }
    return $audio;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, false, 'Metode tidak diizinkan.');
    $input = json_decode((string) file_get_contents('php://input'), true, 32, JSON_THROW_ON_ERROR);
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($input['csrf'] ?? ''))) {
        respond(403, false, 'Sesi CMS tidak valid. Muat ulang halaman.');
    }

    $locationId = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', trim((string) ($input['id_lokasi'] ?? ''))));
    $language = (string) ($input['language'] ?? '');
    $text = trim((string) ($input['text'] ?? ''));
    $root = activeProjectRoot();
    $assetsRoot = activeProjectAssetsRoot();
    $registry = json_decode((string) file_get_contents($assetsRoot . '/languages.json'), true, 32, JSON_THROW_ON_ERROR);
    $audioCodes = array_map(static fn(array $item): string => (string) $item['audio'], $registry);
    if ($locationId === '' || !isset($audioCodes[$language]) || $text === '') respond(422, false, 'Data lokasi, bahasa, atau teks tidak valid.');
    if (mb_strlen($text) > 10000) respond(422, false, 'Teks terlalu panjang (maksimal 10.000 karakter).');

    $databaseContent = (string) file_get_contents($assetsRoot . '/global-database.js');
    if (!preg_match('/window\.GLOBAL_DATABASE\s*=\s*(\{.*?\});/s', $databaseContent, $matches)) respond(500, false, 'Database lokasi tidak dapat dibaca.');
    $database = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    if (!isset($database[$locationId])) respond(404, false, 'Lokasi tidak ditemukan dalam database.');

    $textChunks = splitVoiceText($text);
    $audio = '';
    foreach ($textChunks as $textChunk) {
        $audio .= generateAudioChunk($textChunk);
    }

    $assetBaseRoot = activeProjectAssetBaseRoot();
    $directory = $assetBaseRoot . '/audio/' . $locationId;
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) respond(500, false, 'Folder audio gagal dibuat.');
    $filename = $audioCodes[$language] . '.mp3';
    if (file_put_contents($directory . '/' . $filename, $audio, LOCK_EX) === false) respond(500, false, 'File audio gagal disimpan.');
    respond(200, true, 'Audio berhasil dibuat.', ['file' => basename($assetBaseRoot) . '/audio/' . $locationId . '/' . $filename, 'parts' => count($textChunks)]);
} catch (Throwable $error) {
    respond(500, false, $error->getMessage());
}
