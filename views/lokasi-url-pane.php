<?php
/** Tab "URL Lokasi": daftar URL tur tiap lokasi + hotspot "lokasi terdekat" di panorama drone (lihat includes/lokasi-url.php, assets/js/lokasi-url.js). */
$lku = $lokasiUrlState ?? ['db' => ['locations' => [], 'links' => []], 'tours' => [], 'selected' => $selectedId, 'tourBase' => '', 'drone' => ['node' => '', 'pan' => 0, 'tilt' => 0, 'fov' => 0], 'csrf' => ''];
$lkuTotal = count((array) ($lku['db']['locations'] ?? []));
?>
<?php $lkuMarkerCss = is_file($projectRoot . '/smart-kit/frontends/badung-ui/css/badung-nearby.css') ? projectUrl('smart-kit/frontends/badung-ui/css/badung-nearby.css') : 'assets/css/lokasi-url-marker.css?v=' . (int) @filemtime(__DIR__ . '/../assets/css/lokasi-url-marker.css'); ?>
<link rel="stylesheet" href="<?= e($lkuMarkerCss) ?>">
<section class="card section lku" id="lku">
    <div class="section-heading">
        <div><span class="step">UL</span><div><h2>URL Lokasi</h2><p>Alamat tur tiap lokasi dan hotspot “lokasi terdekat” di panorama drone. Hotspot membuka tur tujuan di tab yang sama, dan bahasa pengunjung tetap terbawa.</p></div></div>
        <div class="object-head-actions"><button type="button" class="oi-load-btn" id="lkuLoadBtn" title="Pulihkan titik hotspot, URL, pemuat tur, dan judul di Terjemahan bila hilang (mis. setelah tur di-export ulang dari Pano2VR). Sumbernya memori CMS atau cadangan."><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v10"/><path d="m7.5 10 4.5 4.5 4.5-4.5"/><path d="M5 19h14"/></svg><span class="oi-load-label">Load Titik URL</span><b class="oi-load-count" hidden></b></button><button type="button" class="save object-save" id="lkuSave" title="Simpan URL Lokasi dan hotspot (Ctrl+S)" disabled><span class="oi-save-label">Simpan</span><kbd>Ctrl+S</kbd></button></div>
    </div>
    <div class="oi-subtabs" role="tablist" aria-label="Bagian URL Lokasi">
        <button type="button" role="tab" id="lkuTabHs" class="is-on" aria-selected="true" aria-controls="lkuPaneHs" data-lku-pane="hs">Hotspot Drone</button>
        <button type="button" role="tab" id="lkuTabUrl" aria-selected="false" aria-controls="lkuPaneUrl" data-lku-pane="url">Daftar URL<b><?= $lkuTotal ?></b></button>
    </div>

    <div class="lku-pane" id="lkuPaneHs" role="tabpanel" aria-labelledby="lkuTabHs">
        <div class="lku-hs">
            <div class="lku-stage-col">
                <div class="lku-stage" id="lkuStage">
                    <div class="lku-pano" id="lkuPano"></div>
                    <div class="lku-empty" id="lkuEmpty" hidden></div>
                    <div class="lku-loading" id="lkuLoading">Memuat panorama drone…</div>
                </div>
                <p class="lku-hint" id="lkuHint">Seret hotspot untuk memindahkannya di panorama drone, lalu tekan Simpan.</p>
            </div>
            <aside class="lku-side" aria-label="Hotspot lokasi terdekat">
                <h3 id="lkuSideTitle">Hotspot lokasi terdekat</h3>
                <form class="lku-add" id="lkuAdd" autocomplete="off">
                    <label><span>Nama lokasi tujuan</span><input type="text" id="lkuAddName" list="lkuAddNames" maxlength="120" placeholder="mis. Pantai Teluk Ninik"><datalist id="lkuAddNames"></datalist></label>
                    <label><span>URL</span><input type="text" id="lkuAddUrl" placeholder="contoh.govirtual.id/nama-lokasi" spellcheck="false" inputmode="url"></label>
                    <p class="lku-add-note" id="lkuAddNote" aria-live="polite"></p>
                    <div class="lku-add-row">
                        <label><span>Menit</span><input type="number" id="lkuAddMin" min="0" max="999" placeholder="0" inputmode="numeric"></label>
                        <label><span>Jarak</span><input type="text" id="lkuAddKm" placeholder="1.5 km" maxlength="16"></label>
                        <button type="submit" class="oi-load-secondary">Tambah</button>
                    </div>
                </form>
                <div class="lku-links" id="lkuLinks"></div>
                <p class="lku-note">Judul tiap hotspot masuk ke <a id="lkuAuditLink" href="?tab=audit&amp;lokasi=<?= e($selectedId) ?>">Audit › Terjemahan</a> untuk diterjemahkan ke bahasa lain.</p>
            </aside>
        </div>
    </div>

    <div class="lku-pane" id="lkuPaneUrl" role="tabpanel" aria-labelledby="lkuTabUrl" hidden>
        <div class="lku-bar">
            <input type="search" id="lkuSearch" placeholder="Cari judul atau alamat…" aria-label="Cari lokasi">
            <div class="lku-filters" role="group" aria-label="Saring daftar">
                <button type="button" class="active" data-lku-filter="all">Semua</button>
                <button type="button" data-lku-filter="local">Tur lokal</button>
                <button type="button" data-lku-filter="check">Perlu dicek</button>
            </div>
            <button type="button" class="oi-load-secondary lku-addloc" id="lkuAddLoc">+ Tambah lokasi</button>
        </div>
        <label class="lku-base"><span>Alamat server tur online</span><input type="text" id="lkuBase" placeholder="https://badung.govirtual.id" spellcheck="false" inputmode="url" maxlength="120"><small>Semua tur satu server, jadi URL lokasi cukup <code>/nama-lokasi</code>. Alamat ini dipakai CMS untuk mengambil pratinjau video dari tur online bila filenya tidak ada di komputer.</small></label>
        <div class="lku-table">
            <div class="lku-row lku-row-head" aria-hidden="true"><span>#</span><span>Judul</span><span>URL</span><span>Status</span></div>
            <div class="lku-rows" id="lkuRows"></div>
        </div>
    </div>

    <div class="voice-progress oi-toast" id="lkuProgress" data-cms-toast="only" role="status" aria-live="polite"></div>
    <script type="application/json" id="lkuState"><?= json_encode($lku, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <script src="assets/js/lokasi-url.js?v=<?= (int) @filemtime(__DIR__ . '/../assets/js/lokasi-url.js') ?>" defer></script>
</section>
