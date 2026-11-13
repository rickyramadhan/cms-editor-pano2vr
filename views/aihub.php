<?php
/** @var array $aiHubState  (diisi index.php lewat ahState) */
?>
<section class="ah" id="aiHub" aria-label="AI Guide Hub">
    <div class="ah-head">
        <p class="ah-intro">Daftarkan setiap lokasi ke AI Guide Hub, sinkronkan nama dan teks sambutan dari CMS, lalu terbitkan paket AI ke folder tur. Setelah itu Jali menjawab pertanyaan pengunjung memakai data Anda.</p>
        <div class="ah-conn" id="ahConn" role="status"></div>
    </div>

    <div class="ah-pipeline" id="ahPipeline" aria-label="Sebaran tahap lokasi"></div>

    <section class="ah-list" aria-labelledby="ahListTitle">
        <div class="ah-toolbar">
            <h2 id="ahListTitle">Lokasi</h2>
            <label class="ah-search"><i data-lucide="search" aria-hidden="true"></i><input type="search" id="ahSearch" placeholder="Cari lokasi" aria-label="Cari lokasi" autocomplete="off"></label>
        </div>
        <div class="ah-tablewrap">
            <table class="ah-table">
                <thead><tr>
                    <th class="ah-col-check"><input type="checkbox" id="ahCheckAll" aria-label="Pilih semua lokasi yang tampil"></th>
                    <th>Lokasi</th>
                    <th>Tahap</th>
                    <th>Data</th>
                    <th class="ah-col-act"><span class="ah-sr">Aksi</span></th>
                </tr></thead>
                <tbody id="ahRows"><tr><td colspan="5" class="ah-empty">Memuat…</td></tr></tbody>
            </table>
        </div>
    </section>

    <div class="ah-bar" id="ahBar" hidden>
        <div class="ah-bar__main">
            <strong id="ahBarCount">0 lokasi dipilih</strong>
            <span class="ah-bar__hint" id="ahBarHint"></span>
        </div>
        <div class="ah-bar__actions">
            <details class="ah-manual">
                <summary>Pilih langkah sendiri</summary>
                <div class="ah-manual__box">
                    <label><input type="checkbox" data-ah-step="register"> Daftar</label>
                    <label><input type="checkbox" data-ah-step="sync"> Sinkron</label>
                    <label><input type="checkbox" data-ah-step="publish"> Terbitkan</label>
                </div>
            </details>
            <button type="button" class="ah-link" id="ahClear">Batal</button>
            <button type="button" class="ah-primary" id="ahRunSelected">Lanjutkan</button>
        </div>
    </div>

    <section class="ah-result" id="ahResult" aria-live="polite" hidden></section>

    <details class="ah-settings" id="ahSettings">
        <summary><i data-lucide="settings-2" aria-hidden="true"></i><span>Pengaturan koneksi</span><small>folder Hub, kota, PHP</small></summary>
        <form class="ah-form" id="ahForm" autocomplete="off" novalidate>
            <label><span>Folder AI Guide Hub di server</span><input name="path" required spellcheck="false" aria-describedby="ahPathHelp"><small id="ahPathHelp">Harus berisi <code>tools/cms-bridge.php</code>.</small></label>
            <label><span>Alamat web Hub</span><input name="url" required spellcheck="false" placeholder="/ai-guide-hub/"><small>Dipakai untuk tombol "Buka di Hub".</small></label>
            <label><span>Slug kota di Hub</span><input name="city" required spellcheck="false" placeholder="jakarta"><small>Huruf kecil, angka, tanda hubung.</small></label>
            <label><span>Nama kota di Hub</span><input name="cityName" required placeholder="Jakarta"><small>Dipakai bila kota belum ada di Hub.</small></label>
            <label class="ah-form__wide"><span>Jalur PHP <em>(opsional)</em></span><input name="php" spellcheck="false" placeholder="Kosongkan agar dicari otomatis"><small id="ahPhpHint"></small></label>
            <p class="ah-form__error" id="ahFormError" role="alert" hidden></p>
            <div class="ah-form__bar"><button type="submit" class="ah-primary" id="ahSaveSettings">Simpan pengaturan</button><span class="ah-muted" id="ahAdminNote"></span></div>
        </form>
    </details>
    <div class="ah-toast" id="ahToast" role="status" hidden></div>
</section>
<script id="aiHubBoot" type="application/json"><?= json_encode(['csrf' => $_SESSION['cms_csrf'], 'state' => $aiHubState], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="assets/js/ai-hub.js?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/js/ai-hub.js')) ?>"></script>
