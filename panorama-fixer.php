<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panorama Fixer — Govirtual</title>
    <link rel="stylesheet" href="assets/css/cms-components.css?v=<?= (int) filemtime(__DIR__ . '/assets/css/cms-components.css') ?>">
    <link rel="stylesheet" href="assets/css/panorama-fixer.css?v=<?= (int) filemtime(__DIR__ . '/assets/css/panorama-fixer.css') ?>">
    <link rel="stylesheet" href="assets/css/panorama-360-preview.css?v=<?= (int) filemtime(__DIR__ . '/assets/css/panorama-360-preview.css') ?>">
</head>
<body>
<main class="shell">
    <header class="topbar">
        <div><span class="eyebrow">GOVIRTUAL TOOLS</span><h1>Panorama Fixer</h1><p>Koreksi distorsi panorama secara lokal tanpa mengubah data project atau lokasi.</p></div>
        <div class="topbar-actions"><a class="preview" href="projects.php">← Kembali ke CMS</a></div>
    </header>
    <section class="panorama-fixer" id="panoramaFixer">
        <header class="panorama-fixer-hero">
            <div><span class="eyebrow">BROWSER-LOCAL TOOL</span><h2>Luruskan distorsi panorama sebelum masuk Pano2VR</h2><p>Gambar diproses langsung di browser dan tidak diunggah ke server. Gunakan grid untuk merapikan garis arsitektur, lalu download hasilnya.</p></div>
            <div class="panorama-fixer-privacy"><strong>🔒 Privat</strong><span>File tetap berada di perangkat ini</span></div>
        </header>
        <div class="panorama-fixer-layout">
            <aside class="card panorama-fixer-controls">
                <label class="panorama-fixer-upload" for="panoramaFixerInput"><span>＋</span><strong>Pilih panorama</strong><small>JPEG, PNG, WebP · disarankan equirectangular 2:1</small><input type="file" id="panoramaFixerInput" accept="image/jpeg,image/png,image/webp"></label>
                <div id="panoramaFixerMeta" class="panorama-fixer-meta">Belum ada gambar dipilih.</div>
                <fieldset id="panoramaFixerSettings" disabled>
                    <legend>Koreksi geometris</legend>
                    <label><span><b>Dewarp</b><output data-output="distortion">0</output></span><input type="range" data-fixer-control="distortion" min="-50" max="50" value="0"></label>
                    <label><span><b>Lengkung horizontal</b><output data-output="bow">0</output></span><input type="range" data-fixer-control="bow" min="-50" max="50" value="0"></label>
                    <label><span><b>Perspective vertikal</b><output data-output="vertical">0</output></span><input type="range" data-fixer-control="vertical" min="-40" max="40" value="0"></label>
                    <label><span><b>Putar horizon</b><output data-output="rotation">0°</output></span><input type="range" data-fixer-control="rotation" min="-10" max="10" step="0.1" value="0"></label>
                    <label><span><b>Zoom aman</b><output data-output="zoom">100%</output></span><input type="range" data-fixer-control="zoom" min="100" max="135" value="100"></label>
                </fieldset>
                <div class="panorama-fixer-presets"><button type="button" data-fixer-preset="architecture" disabled>Auto Arsitektur</button><button type="button" data-fixer-reset disabled>Reset</button></div>
                <label class="panorama-grid-toggle"><input type="checkbox" id="panoramaFixerGrid" checked> Tampilkan grid panduan</label>
                <div class="panorama-fixer-export">
                    <label><span>Format hasil</span><select id="panoramaFixerFormat"><option value="image/jpeg">JPEG</option><option value="image/png">PNG</option><option value="image/webp">WebP</option></select></label>
                    <label><span>Kualitas</span><select id="panoramaFixerQuality"><option value="0.92">92% · Disarankan</option><option value="0.98">98% · Maksimal</option><option value="0.85">85% · Lebih kecil</option></select></label>
                    <button type="button" class="save" id="panoramaFixerDownload" disabled>↓ Download panorama hasil</button>
                </div>
                <p class="panorama-fixer-warning">Koreksi ini memperbaiki distorsi global. Kesalahan stitching lokal atau objek patah tetap perlu diperbaiki di PTGui/Hugin.</p>
            </aside>
            <section class="card panorama-fixer-stage">
                <div class="panorama-fixer-stage-head"><div><strong>Preview koreksi</strong><small id="panoramaFixerStatus">Pilih panorama untuk mulai</small></div><div><button type="button" data-fixer-view="before" disabled>Sebelum</button><button type="button" class="active" data-fixer-view="after" disabled>Sesudah</button></div></div>
                <div class="panorama-fixer-canvas-wrap" id="panoramaFixerCanvasWrap"><canvas id="panoramaFixerCanvas"></canvas><div class="panorama-fixer-empty"><span>⌗</span><strong>Panorama belum dimuat</strong><small>Preview akan muncul di area ini</small></div><div class="panorama-fixer-grid-overlay" id="panoramaFixerGridOverlay"></div></div>
            </section>
            <section class="card panorama-fixer-stage panorama-fixer-360">
                <div class="panorama-fixer-stage-head"><div><strong>Preview 360° interaktif</strong><small>Geser untuk melihat sekeliling · scroll untuk zoom</small></div><span class="panorama-360-angle" id="panorama360Angle">Pan 0° · Tilt 0° · FoV 75°</span></div>
                <div class="panorama-360-wrap" id="panorama360Wrap"><canvas id="panorama360Canvas"></canvas><div class="panorama-360-empty">Preview 360° muncul setelah panorama dipilih</div></div>
            </section>
        </div>
    </section>
</main>
<script src="assets/js/panorama-fixer.js?v=<?= (int) filemtime(__DIR__ . '/assets/js/panorama-fixer.js') ?>"></script>
<script src="assets/js/panorama-360-preview.js?v=<?= (int) filemtime(__DIR__ . '/assets/js/panorama-360-preview.js') ?>"></script>
</body>
</html>
