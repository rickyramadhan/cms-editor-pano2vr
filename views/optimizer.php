<?php /** Menu "Optimasi Loading" — antarmuka; logika di assets/js/optimizer.js dan optimizer.php */ ?>
<section class="op" id="opApp" aria-labelledby="opTitle">
    <header class="op-hero card">
        <div>
            <span class="eyebrow">PERFORMA</span>
            <h2 id="opTitle">Optimasi Loading Virtual Tour</h2>
            <p>Mempercepat pembukaan awal tur: video teaser diunduh lebih awal, audio dan Guide AI ditunda, mode jaringan lambat otomatis, dan cache untuk kunjungan berikutnya. Hanya <code>index.html</code> dan <code>skin.js</code> yang diubah (ditambah <code>sw.js</code>); tile dan gambar tidak disentuh. Backup dibuat otomatis dan bisa dikembalikan.</p>
        </div>
        <div class="op-hero__stats" id="opEnv" aria-live="polite"><span class="op-pill">Memuat…</span></div>
    </header>

    <div class="card op-card" id="opProblem" hidden></div>

    <div class="card op-card" id="opMain" hidden>
        <div class="op-toolbar">
            <input type="search" id="opSearch" placeholder="Cari lokasi…" aria-label="Cari lokasi">
            <div class="op-chips" id="opChips" role="group" aria-label="Saring status"></div>
            <div class="op-sel">
                <button type="button" class="translate" id="opSelTodo">Pilih yang belum</button>
                <button type="button" class="translate" id="opSelAll">Pilih semua tampil</button>
                <button type="button" class="translate" id="opSelNone">Kosongkan</button>
            </div>
        </div>
        <div class="op-opts">
            <label class="sh-check"><input type="checkbox" id="opSw" checked> Cache kunjungan berikutnya (service worker)</label>
            <label class="sh-check"><input type="checkbox" id="opSkip" checked> Jaringan lambat: lewati video otomatis</label>
        </div>
        <div class="op-tablewrap"><table class="op-table"><thead><tr><th style="width:36px"><input type="checkbox" id="opAllBox" aria-label="Pilih semua"></th><th>Lokasi</th><th>Status</th><th>Backup</th></tr></thead><tbody id="opRows"></tbody></table></div>
        <div class="op-bar">
            <span id="opSum" class="op-sum">0 dipilih</span>
            <button type="button" class="translate" id="opCheck" disabled title="Hanya memeriksa; tidak menulis apa pun">Cek saja</button>
            <button type="button" class="save" id="opRun" disabled>⚡ Optimasi yang dipilih</button>
            <button type="button" class="translate" id="opVideo" disabled title="Perkecil video teaser dengan FFmpeg (beberapa menit per lokasi)">Kompres video</button>
            <button type="button" class="danger" id="opRestore" disabled title="Kembalikan index.html & skin.js dari backup">↺ Kembalikan (undo)</button>
        </div>
        <div class="op-note" id="opAdminNote" hidden>Hanya admin yang dapat menjalankan optimasi. Anda dapat melihat status lokasi.</div>
    </div>

    <div class="card op-card" id="opJob" hidden>
        <div class="op-job__h"><div><h3 id="opJobTitle">Pekerjaan</h3><small id="opJobSub"></small></div><button type="button" class="danger" id="opCancel" hidden>■ Hentikan</button></div>
        <div class="op-progress" aria-hidden="true"><i id="opBar"></i></div>
        <pre class="op-log" id="opLog" aria-live="polite"></pre>
        <div id="opResults"></div>
    </div>

    <details class="card op-card op-help">
        <summary>Cara kerja, batasan, dan setelah dioptimasi</summary>
        <div class="op-help__b">
            <h4>Yang dilakukan</h4>
            <ul>
                <li>Pano.xml dibaca langsung (tanpa menunggu semua skrip), spinner CSS ringan, preload gambar loading screen.</li>
                <li>Video teaser diunduh lebih awal dan dipakai ulang oleh skin; audio narasi dan Guide AI ditunda sampai video berjalan.</li>
                <li>Mode jaringan lambat (2G/3G/Data Saver, atau <code>?net=slow</code>): video tidak diunduh, teaser dilewati. Tile resolusi rendah node awal di-prefetch.</li>
                <li>Service worker (<code>sw.js</code>) menyimpan tile, gambar, dan JS untuk kunjungan berikutnya.</li>
                <li><b>Kompres video</b> (opsional, butuh FFmpeg): 720p/30 fps; video asli dipindah ke <code>media/_original/</code>.</li>
            </ul>
            <h4>Hasil terukur (Lawang Sewu, cache dingin, jaringan disimulasikan)</h4>
            <p>Langsung Skip di 4G: 16,0 → 4,0 dtk (−75%); di 3G dengan mode jaringan lambat: 93,5 → 21,5 dtk (−77%); data diunduh sampai panorama siap di 3G: 16,8 → 4,2 MB. Angka produksi (HTTP/3, cache server) dapat berbeda; kunjungan berulang oleh service worker belum diukur. Sumber: <code>LAPORAN-OPTIMASI-VIRTUAL-TOUR.md</code>.</p>
            <h4>Perlu diperhatikan</h4>
            <ul>
                <li>Mesin hanya cocok untuk tur ber-skin GoVirtual dengan video teaser (<code>ggVideoSource</code> di <code>skin.js</code>). Tur lain berstatus <b>GAGAL</b> dan <b>tidak ada file yang diubah</b>.</li>
                <li><b>Kembalikan (undo)</b> menimpa <code>index.html</code> dan <code>skin.js</code> dengan backup sebelum optimasi. Perubahan yang dibuat sesudahnya (mis. "Terapkan Smart Kit", "Terapkan skin") ikut hilang dan perlu diterapkan ulang.</li>
                <li>Jalankan ulang optimasi setelah <b>export ulang dari Pano2VR</b> atau setelah "Terapkan skin ke semua lokasi" menimpa <code>skin.js</code>.</li>
                <li>Upload ke server: <code>index.html</code>, <code>skin.js</code>, <code>sw.js</code> (dan video baru bila dikompres). <b>Jangan upload</b> <code>*.pre-opt.bak</code> dan <code>media/_original/</code>.</li>
                <li>Uji dulu di beberapa lokasi (auto-skip jaringan lambat, Guide AI) sebelum penerapan massal. Service worker perlu HTTPS untuk diuji di Chrome biasa.</li>
            </ul>
        </div>
    </details>

    <details class="card op-card" id="opSettingsBox" hidden>
        <summary>Pengaturan mesin (admin)</summary>
        <div class="op-set" id="opSettings"></div>
    </details>
    <div class="sh-toast" id="opToast" role="status" hidden></div>
</section>
<script id="opBoot" type="application/json"><?= json_encode(['csrf' => $_SESSION['cms_csrf']], JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="assets/js/optimizer.js?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/js/optimizer.js')) ?>"></script>
