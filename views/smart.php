<?php
/** @var array $smartHubState  (diisi index.php lewat shState) */
?>
<section class="sh" id="smartHub" aria-labelledby="smartHubTitle">
    <header class="sh-hero card">
        <div>
            <span class="eyebrow">SMART TOURISM HUB</span>
            <h2 id="smartHubTitle">Sambungkan fitur &amp; front-end ke panorama</h2>
            <p>Atur tombol (aksi), menu, dan sumber data untuk <b>semua lokasi</b> di wilayah ini. Developer front-end cukup memakai ID yang dibuat di sini — lewat atribut HTML <code>data-sk-*</code>, SDK, atau REST.</p>
        </div>
        <div class="sh-hero__meta">
            <span class="sh-chip" id="shWilayah">wilayah: —</span>
            <span class="sh-chip sh-chip--soft" id="shHubFile" title="Berkas konfigurasi">_hub.json</span>
        </div>
    </header>

    <nav class="sh-tabs" role="tablist" aria-label="Bagian Smart Hub">
        <div class="sh-tabgroup"><small>Persiapan</small><div><button role="tab" data-sh-tab="lokasi" aria-selected="true"><i>1</i> Lokasi &amp; Paket</button></div></div>
        <div class="sh-tabgroup"><small>Konten</small><div><button role="tab" data-sh-tab="konten"><i>2</i> Isi Konten</button><button role="tab" data-sh-tab="wilayah"><i>3</i> Data Wilayah</button></div></div>
        <div class="sh-tabgroup sh-tabgroup--grow"><small>Pengalaman &amp; integrasi</small><div><button role="tab" data-sh-tab="aksi"><i>4</i> Aksi Tombol</button><button role="tab" data-sh-tab="menu"><i>5</i> Menu</button><button role="tab" data-sh-tab="data"><i>6</i> Sumber Data</button><button role="tab" data-sh-tab="integrasi"><i>7</i> Integrasi &amp; Tampilan</button><button role="tab" data-sh-tab="panduan"><i>8</i> Panduan Pengembang</button></div></div>
    </nav>

    <div class="sh-layout">
        <div class="sh-main" id="shMain" aria-live="polite"><div class="sh-empty">Memuat Smart Hub…</div></div>
        <aside class="sh-preview card" aria-label="Pratinjau tur">
            <div class="sh-preview__head">
                <div><strong>Pratinjau langsung</strong><small id="shPreviewStatus">belum terhubung</small></div>
                <select id="shPreviewLoc" aria-label="Lokasi pratinjau"></select>
            </div>
            <div class="sh-preview__frame"><iframe id="shPreviewFrame" title="Pratinjau tur" allow="fullscreen; autoplay; gyroscope; accelerometer" loading="lazy"></iframe><div class="sh-preview__ph" id="shPreviewPh">Pilih lokasi yang sudah diterapkan Smart Kit untuk menguji aksi &amp; data secara langsung.</div></div>
            <div class="sh-preview__bar"><button type="button" class="translate" id="shPreviewReload"><i data-lucide="refresh-cw" aria-hidden="true"></i>Muat ulang</button><a class="translate" id="shPreviewOpen" target="_blank" rel="noopener" href="#"><i data-lucide="external-link" aria-hidden="true"></i>Buka penuh</a></div>
            <div class="sh-log" id="shLog" aria-label="Log event"><small>Event dari tur akan tampil di sini.</small></div>
        </aside>
    </div>

    <div class="sh-savebar" id="shSaveBar" hidden>
        <span><i></i> Ada perubahan yang belum disimpan</span>
        <button type="button" class="translate" id="shDiscard">Batalkan</button>
        <button type="button" class="save" id="shSave">Simpan Smart Hub</button>
    </div>
    <div class="sh-toast" id="shToast" role="status" hidden></div>
</section>
<script id="smartHubBoot" type="application/json"><?= json_encode(['csrf' => $_SESSION['cms_csrf'], 'state' => $smartHubState], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="assets/js/smart-content.js?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/js/smart-content.js')) ?>"></script>
<script src="assets/js/smart-hub.js?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/js/smart-hub.js')) ?>"></script>
