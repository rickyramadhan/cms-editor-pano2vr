<?php
/**
 * Tab "Kalibrasi Hotspot": memeriksa semua hotspot di tur lokasi ini dan menggeser yang saling menimpa ke posisi terdekat yang bebas tabrak.
 * Mesin ukur berjalan di browser, di dalam tur sungguhan (assets/js/hotspot-calibration-engine.js); tampilan + alur di assets/js/hotspot-calibration.js;
 * penyimpanan + urungkan di hotspot-calibration-apply.php (includes/hotspot-calibration.php).
 */
$hcal = $hcalState ?? null;
$hcalKinds = [
    ['land', 'Hotspot darat', 'darat ke darat · paling kokoh'],
    ['oi', 'Objek Info', 'pin informasi'],
    ['dg', 'Drone ke darat', 'dari panorama drone ke tempat di darat'],
    ['air', 'Drone', 'menuju dan antar panorama drone'],
    ['near', 'Lokasi terdekat', 'hotspot URL Lokasi · paling lentur'],
];
?>
<section class="card section hcal" id="hcal">
    <div class="section-heading">
        <div><span class="step">KH</span><div><h2>Kalibrasi Hotspot</h2><p>Memeriksa semua hotspot di panorama tur ini, lalu menggeser yang saling menimpa ke posisi terdekat yang bebas tabrak. Tidak ada yang tersimpan sebelum Anda menekan Terapkan.</p></div></div>
        <?php if ($hcal !== null): ?>
        <div class="object-head-actions">
            <button type="button" class="oi-load-btn" id="hcalUndo" title="Kembalikan hotspot ke posisi sebelum kalibrasi terakhir" <?= empty($hcal['undo']) ? 'disabled' : '' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg><span>Urungkan kalibrasi</span><b class="oi-load-count" id="hcalUndoCount" <?= empty($hcal['undo']) ? 'hidden' : '' ?>><?= (int) ($hcal['undo']['count'] ?? 0) ?></b></button>
            <button type="button" class="oi-load-btn" id="hcalCancel" hidden><span>Batalkan</span></button>
            <button type="button" class="save object-save" id="hcalApply" hidden disabled><span class="oi-save-label">Terapkan</span><b class="hcal-apply-count" id="hcalApplyCount">0</b></button>
            <button type="button" class="save object-save" id="hcalRun" title="Muat tur, ukur semua hotspot, lalu cari posisi yang bebas tabrak"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="7"/><path d="M12 2v4M12 18v4M2 12h4M18 12h4"/></svg><span class="oi-save-label">Kalibrasi</span></button>
        </div>
        <?php endif; ?>
    </div>

<?php if ($hcal === null): ?>
    <div class="hcal-empty"><strong>Lokasi ini belum punya tur lokal.</strong><span>Kalibrasi memeriksa hotspot langsung di tur yang ada di komputer ini. Pilih lokasi yang turnya sudah ada di sidebar.</span></div>
<?php else: ?>
    <div class="hcal-grid">
        <div class="hcal-stage-col">
            <div class="hcal-compare" id="hcalCompare" data-mode="single">
                <figure class="hcal-pane is-before" id="hcalPaneBefore" hidden>
                    <figcaption><b>Sebelum</b><span id="hcalCapBefore">posisi sekarang di tur</span></figcaption>
                    <div class="hcal-stage">
                        <div class="hcal-frame-wrap" id="hcalFrameWrapB"><iframe id="hcalFrameB" title="Tur: posisi hotspot sebelum kalibrasi" tabindex="-1" allow="autoplay; fullscreen" src="about:blank"></iframe></div>
                        <div class="hcal-cover" id="hcalCoverB" hidden><strong>Menyiapkan pembanding…</strong><span>Tur dimuat sekali lagi dengan posisi sekarang.</span></div>
                    </div>
                </figure>
                <figure class="hcal-pane is-after" id="hcalPaneAfter">
                    <figcaption id="hcalAfterCap" hidden><b>Sesudah</b><span id="hcalCapAfter">hasil kalibrasi</span></figcaption>
                    <div class="hcal-stage" id="hcalStage">
                        <div class="hcal-frame-wrap" id="hcalFrameWrap">
                            <iframe id="hcalFrame" title="Tur untuk kalibrasi hotspot" tabindex="-1" allow="autoplay; fullscreen" src="about:blank"></iframe>
                        </div>
                        <div class="hcal-cover" id="hcalCover"><strong><?= e($hcal['name']) ?></strong><span>Tur dimuat di sini saat kalibrasi dimulai.</span></div>
                    </div>
                </figure>
            </div>
            <p class="hcal-hint" id="hcalHint">Pratinjau tur sungguhan pada layar desktop 1366×768 (ukuran yang umum; layar lebih lebar memberi ruang lebih longgar). Biarkan tab ini terbuka selama kalibrasi berjalan. Tampilan ponsel belum diperiksa.</p>
        </div>
        <aside class="hcal-side" aria-label="Hasil kalibrasi">
            <ul class="hcal-kinds" aria-label="Jenis hotspot yang diperiksa">
                <?php foreach ($hcalKinds as [$key, $label, $note]): ?>
                <li><i class="hcal-dot k-<?= e($key) ?>" aria-hidden="true"></i><span><b><?= e($label) ?></b><small><?= e($note) ?></small></span></li>
                <?php endforeach; ?>
            </ul>
            <div class="hcal-status" id="hcalStatus" role="status" aria-live="polite">
                <p class="hcal-status-text" id="hcalStatusText">Tekan <b>Kalibrasi</b> untuk memeriksa. Yang lentur (lokasi terdekat, drone) digeser lebih dulu; hotspot darat paling terakhir dan paling sedikit.</p>
                <div class="hcal-bar" id="hcalBar" hidden><i id="hcalBarFill"></i></div>
            </div>
            <div class="hcal-result" id="hcalResult" hidden></div>
        </aside>
    </div>
    <div class="voice-progress oi-toast" id="hcalProgress" data-cms-toast="only" role="status" aria-live="polite"></div>
    <script type="application/json" id="hcalState"><?= json_encode($hcal + ['engineVer' => (int) @filemtime(__DIR__ . '/../assets/js/hotspot-calibration-engine.js')], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <script src="assets/js/hotspot-calibration.js?v=<?= (int) @filemtime(__DIR__ . '/../assets/js/hotspot-calibration.js') ?>" defer></script>
<?php endif; ?>
</section>
