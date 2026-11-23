<?php
declare(strict_types=1);
/*
 * welcome-preview.php — pratinjau kartu sambutan (welcome text) persis seperti di virtual tour.
 * Dipakai sebagai <iframe> di CMS › Data lokasi (api.php?action=welcome-preview&id_lokasi=...).
 *
 * Halaman ini memuat CSS asli Smart Kit milik project (smart.css + css/welcome.css) dan tema front-end wilayahnya
 * (smart-kit/frontends/<id>/frontend.json › globalCss, mis. badung-ui), sehingga tampilannya otomatis mengikuti skin:
 * wilayah lain dengan front-end lain akan tampil sesuai front-end itu. Bendera bahasa sengaja disembunyikan.
 * Teks, nama, dan bahasa dikirim dari halaman CMS lewat postMessage (live saat mengetik).
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/project-manager.php';

/** Jalur URL relatif dari folder CMS ke jalur berkas/folder lain (sama dengan cara projectUrl() di index.php). */
function welcomePreviewUrl(string $target): string
{
    $from = explode('/', trim(str_replace('\\', '/', __DIR__), '/'));
    $to = explode('/', trim(str_replace('\\', '/', $target), '/'));
    while ($from !== [] && $to !== [] && strcasecmp((string) $from[0], (string) $to[0]) === 0) { array_shift($from); array_shift($to); }
    return str_repeat('../', count($from)) . implode('/', array_map('rawurlencode', $to));
}

header('Content-Type: text/html; charset=utf-8');
header('X-Frame-Options: SAMEORIGIN');

try {
    $project = activeProject();
    $root = rtrim(str_replace('\\', '/', (string) $project['path']), '/');
    $region = basename($root);
    $locationId = strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', (string) ($_GET['id_lokasi'] ?? '')));

    $kit = is_dir($root . '/smart-kit') ? $root . '/smart-kit' : dirname($root) . '/smart-kit';
    $kitReady = is_file($kit . '/smart.css') && is_file($kit . '/css/welcome.css');
    $hub = [];
    if (is_file($kit . '/packs/' . $region . '/_hub.json')) $hub = json_decode((string) file_get_contents($kit . '/packs/' . $region . '/_hub.json'), true) ?: [];
    $frontendId = preg_replace('/[^a-z0-9_-]/i', '', (string) ($hub['ui']['frontend']['id'] ?? ''));
    $theme = ($hub['ui']['theme'] ?? 'dark') === 'light' ? 'light' : 'dark';

    $styles = [];
    if ($kitReady) {
        $styles[] = welcomePreviewUrl($kit . '/smart.css');
        $styles[] = welcomePreviewUrl($kit . '/css/welcome.css');
        if ($frontendId !== '' && is_file($kit . '/frontends/' . $frontendId . '/frontend.json')) {
            $frontend = json_decode((string) file_get_contents($kit . '/frontends/' . $frontendId . '/frontend.json'), true) ?: [];
            foreach ((array) ($frontend['globalCss'] ?? []) as $css) {
                $path = $kit . '/frontends/' . $frontendId . '/' . ltrim((string) $css, '/');
                if (is_file($path)) $styles[] = welcomePreviewUrl($path);
            }
        }
    }

    // latar: gambar scene awal dari paket Smart Kit lokasi ini (packs/<wilayah>/<lokasi>/img/scenes/<scene awal>.webp), sama dengan yang dipakai tur
    $background = '';
    $pack = $locationId !== '' ? $kit . '/packs/' . $region . '/' . $locationId : '';
    if ($pack !== '' && is_file($pack . '/scenes.js') && preg_match('/"start"\s*:\s*"([A-Za-z0-9_-]+)"/', (string) file_get_contents($pack . '/scenes.js'), $startMatch)) {
        foreach (['webp', 'jpg', 'png'] as $extension) {
            if (is_file($pack . '/img/scenes/' . $startMatch[1] . '.' . $extension)) { $background = welcomePreviewUrl($pack . '/img/scenes/' . $startMatch[1] . '.' . $extension); break; }
        }
    }
    // cadangan: preview.jpg di folder tur
    if ($background === '' && $locationId !== '' && is_dir($root)) {
        foreach (new DirectoryIterator($root) as $entry) {
            if (!$entry->isDir() || $entry->isDot()) continue;
            if (strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', $entry->getFilename())) !== $locationId) continue;
            foreach (['preview.jpg', 'preview.webp', 'preview.png'] as $candidate) {
                if (is_file($entry->getPathname() . '/' . $candidate)) { $background = welcomePreviewUrl($entry->getPathname() . '/' . $candidate); break 2; }
            }
        }
    }
} catch (Throwable $error) {
    http_response_code(400);
    echo '<!doctype html><meta charset="utf-8"><body style="font:14px system-ui;padding:20px;color:#b42318">Pratinjau tidak tersedia: ' . htmlspecialchars($error->getMessage(), ENT_QUOTES) . '</body>';
    exit;
}
// teks kartu per bahasa dibaca dari smart-kit/welcome.js (objek T) agar selalu sama dengan tur; cadangan bawaan bila gagal dibaca
$strings = [
    'id' => ['k' => 'Selamat datang di', 'go' => 'Mulai jelajahi', 'hint' => 'Ketuk area di luar kartu untuk menutup'],
    'en' => ['k' => 'Welcome to', 'go' => 'Start exploring', 'hint' => 'Tap outside the card to close'],
    'zh' => ['k' => '欢迎来到', 'go' => '开始探索', 'hint' => '点击卡片外部即可关闭'],
    'ar' => ['k' => 'مرحبًا بكم في', 'go' => 'ابدأ الاستكشاف', 'hint' => 'اضغط خارج البطاقة للإغلاق', 'rtl' => true],
];
if ($kitReady && is_file($kit . '/welcome.js')) {
    $welcomeSource = (string) file_get_contents($kit . '/welcome.js');
    if (preg_match('/const T = (\{.*?\n\});/s', $welcomeSource, $block)) {
        $json = preg_replace(["/'/", '/(?<=[{,\s])(id|en|zh|ar|k|go|hint|rtl)\s*:/', '/,(\s*[}\]])/'], ['"', '"$1":', '$1'], $block[1]);
        $parsed = json_decode((string) $json, true);
        if (is_array($parsed) && isset($parsed['id']['k'], $parsed['en']['k'])) $strings = array_replace($strings, $parsed);
    }
}
$e = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="id" data-smt-frontend="<?= $e($frontendId) ?>" data-smt-theme="<?= $e($theme) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pratinjau welcome text</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<?php foreach ($styles as $href): ?><link rel="stylesheet" href="<?= $e($href) ?>">
<?php endforeach; ?>
<style>
    html, body { margin: 0; height: 100%; overflow: hidden; background: #0b1624; font-family: "Plus Jakarta Sans", system-ui, sans-serif; }
    .wp-bg { position: fixed; inset: 0; background: <?= $background !== '' ? 'url("' . $e($background) . '") center / cover no-repeat' : 'linear-gradient(160deg,#27425f,#0e1b2b)' ?>; }
    .wp-bg::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(5,12,22,.12), rgba(5,12,22,.38)); }
    /* pratinjau: kartu selalu tampil, tanpa animasi masuk, dan tanpa bendera bahasa */
    .ob-wel { opacity: 1 !important; visibility: visible !important; transform: translate(-50%, 0) !important; transition: none !important; }
    .ob-wel *, .ob-wel::before, .ob-wel::after { animation: none !important; }
    .ob-wel__lang { display: none !important; }
    .ob-wel.ob-wel { bottom: 62px !important; }   /* ruang untuk ornamen di bawah kartu (di tur nyata ada tinggi layar) */
    .ob-wel__dim { pointer-events: none !important; }
    .wp-empty { position: fixed; inset: 0; display: grid; place-items: center; color: #cbd5e1; font-size: 14px; }
</style>
</head>
<body>
<div class="wp-bg"></div>
<?php if (!$kitReady): ?>
<div class="wp-empty">Smart Kit belum terpasang di project ini, jadi pratinjau tampilan tur tidak tersedia.</div>
<?php else: ?>
<div class="ob-wel__dim is-on"></div>
<section class="ob-wel is-on" id="welCard" role="dialog" aria-label="Selamat datang">
    <header class="ob-wel__h"><div class="ob-wel__t"><small id="welKicker">Selamat datang di</small><h2 id="welTitle"></h2></div></header>
    <p class="ob-wel__p" id="welText"></p>
    <footer class="ob-wel__f"><button type="button" class="ob-wel__go"><span id="welGo">Mulai jelajahi</span><span class="ob-wel__arr"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></button><span class="ob-wel__hint" id="welHint"></span></footer>
</section>
<script>
(() => {
    const T = <?= json_encode($strings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const card = document.getElementById('welCard'), text = document.getElementById('welText'), title = document.getElementById('welTitle');
    const kicker = document.getElementById('welKicker'), go = document.getElementById('welGo'), hint = document.getElementById('welHint');
    const apply = data => {
        const lang = T[data.lang] ? data.lang : 'id', copy = T[lang];
        card.dir = copy.rtl ? 'rtl' : 'ltr';
        kicker.textContent = copy.k; go.textContent = copy.go; hint.textContent = copy.hint;
        title.textContent = String(data.name || ''); text.textContent = String(data.text || '').trim() || '—';
    };
    window.addEventListener('message', event => { if (event.origin === location.origin && event.data && event.data.type === 'welcome-preview') apply(event.data); });
    apply({lang: 'id', text: '', name: ''});
    parent.postMessage({type: 'welcome-preview-ready'}, location.origin);
})();
</script>
<?php endif; ?>
</body>
</html>
