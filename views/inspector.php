<?php
/** @var array $inspectorState  (diisi index.php lewat ivState) */
?>
<section class="iv" id="inspectorHub" aria-label="VT Inspector">
    <div class="iv-head">
        <p class="iv-intro">Pemeriksaan mutu setiap tur dari VT Inspector: Smart Kit, konten, AI Guide, deploy, dan antarmuka. Jalankan inspeksi statis langsung dari sini; uji robot, AI, dan tautan dijalankan di server Inspector.</p>
        <div class="iv-conn" id="ivConn" role="status"></div>
    </div>

    <div class="iv-summary" id="ivSummary" aria-label="Ringkasan wilayah"></div>

    <section class="iv-list" aria-labelledby="ivListTitle">
        <div class="iv-toolbar">
            <h2 id="ivListTitle">Tur di project ini</h2>
            <div class="iv-toolbar__actions">
                <label class="iv-search"><i data-lucide="search" aria-hidden="true"></i><input type="search" id="ivSearch" placeholder="Cari lokasi" aria-label="Cari lokasi" autocomplete="off"></label>
                <button type="button" class="iv-primary" id="ivScanAll"><i data-lucide="scan-search" aria-hidden="true"></i><span>Jalankan inspeksi statis (semua tur)</span></button>
                <a class="iv-ghost" id="ivOpenServer" target="_blank" rel="noopener" href="#"><i data-lucide="external-link" aria-hidden="true"></i><span>Buka VT Inspector</span></a>
            </div>
        </div>
        <div class="iv-tablewrap">
            <table class="iv-table">
                <thead><tr>
                    <th>Lokasi</th><th>Skor</th><th>Status</th><th>Temuan</th><th>Kesiapan CMS</th><th>Gerbang rilis</th><th>Scan terakhir</th><th class="iv-col-act"><span class="iv-sr">Aksi</span></th>
                </tr></thead>
                <tbody id="ivRows"><tr><td colspan="8" class="iv-empty">Memuat…</td></tr></tbody>
            </table>
        </div>
    </section>

    <section class="iv-detail" id="ivDetail" aria-live="polite" hidden></section>

    <section class="iv-result" id="ivResult" aria-live="polite" hidden></section>

    <details class="iv-settings" id="ivSettings">
        <summary><i data-lucide="settings-2" aria-hidden="true"></i><span>Pengaturan Inspector</span><small>folder, Node.js, alamat server</small></summary>
        <form class="iv-form" id="ivForm" autocomplete="off" novalidate>
            <label><span>Folder VT Inspector di server</span><input name="path" spellcheck="false" placeholder="C:/xampp/htdocs/VT-Inspector"><small>Harus berisi <code>src/cli.mjs</code>. Kosongkan untuk dicari otomatis (<code>../VT-Inspector</code> di htdocs).</small></label>
            <label><span>Jalur Node.js <em>(opsional)</em></span><input name="node" spellcheck="false" placeholder="Kosongkan agar dicari otomatis"><small id="ivNodeHint"></small></label>
            <label><span>Alamat server Inspector</span><input name="url" spellcheck="false" placeholder="http://127.0.0.1:4780"><small>Hanya <code>127.0.0.1</code> atau <code>localhost</code>. Dipakai untuk tombol "Buka VT Inspector".</small></label>
            <p class="iv-form__error" id="ivFormError" role="alert" hidden></p>
            <div class="iv-form__bar"><button type="submit" class="iv-primary" id="ivSaveSettings">Simpan pengaturan</button><span class="iv-muted" id="ivAdminNote"></span></div>
        </form>
    </details>
    <div class="iv-toast" id="ivToast" role="status" hidden></div>
</section>
<script id="inspectorBoot" type="application/json"><?= json_encode(['csrf' => $_SESSION['cms_csrf'], 'state' => $inspectorState, 'selected' => cleanKey((string) ($_GET['lokasi'] ?? ''))], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="assets/js/inspector.js?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/js/inspector.js')) ?>"></script>
