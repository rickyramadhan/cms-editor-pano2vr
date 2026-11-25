<?php
/**
 * Panel "Daftar Konten" di tab Object Informasi (hanya baca): status data halaman webview tiap objek.
 * Variabel dari views/app.php: $oiKonten (hasil oiKontenReport), $oiLangs (kode => label).
 */
$okcSummary = $oiKonten['summary'];
$okcStatusLabel = ['lengkap' => 'Lengkap', 'sebagian' => 'Perlu dilengkapi', 'kosong' => 'Pemeliharaan'];
?>
<div id="oiPaneKonten" class="okc" role="tabpanel" aria-labelledby="oiTabKonten" hidden>
    <div class="okc-head">
        <p class="okc-lead">Isi kartu yang tampil saat pin Object Info diklik datang dari halaman webview <code>webview/objinfo/&lt;info-id&gt;</code>, yang membaca <code>webview/json/objinfo/&lt;info-id&gt;.json</code>. Daftar ini memeriksa apakah data tiap objek sudah ada; objek tanpa data menampilkan layar pemeliharaan di tur. Untuk mengubah Info ID atau posisi pin pakai tab <b>Editor</b>.</p>
        <div class="okc-tools">
            <div class="okc-filters" role="group" aria-label="Saring daftar konten">
                <button type="button" class="is-on" data-okc-filter="all" aria-pressed="true">Semua <b><?= (int) $okcSummary['total'] ?></b></button>
                <button type="button" data-okc-filter="lengkap" aria-pressed="false">Lengkap <b><?= (int) $okcSummary['lengkap'] ?></b></button>
                <button type="button" data-okc-filter="perlu" aria-pressed="false">Perlu dilengkapi <b><?= (int) $okcSummary['sebagian'] ?></b></button>
                <button type="button" data-okc-filter="pemeliharaan" aria-pressed="false">Pemeliharaan <b><?= (int) $okcSummary['kosong'] ?></b></button>
                <button type="button" data-okc-filter="nonaktif" aria-pressed="false">Tidak aktif <b><?= (int) $okcSummary['nonaktif'] ?></b></button>
            </div>
            <input type="search" id="okcSearch" class="okc-search" placeholder="Cari objek…" aria-label="Cari objek di daftar konten">
        </div>
    </div>

    <?php if ($oiKonten['items'] === []): ?>
        <p class="okc-blank">Lokasi ini belum punya record Object Info.</p>
    <?php else: ?>
    <div class="okc-list" id="okcList">
        <?php foreach ($oiKonten['items'] as $item):
            $q = mb_strtolower($item['title'] . ' ' . $item['scene'] . ' ' . $item['target'] . ' ' . $item['infoId']);
        ?>
        <article class="okc-item is-<?= e($item['status']) ?><?= $item['active'] ? '' : ' is-off' ?>" data-status="<?= e($item['status']) ?>" data-active="<?= $item['active'] ? '1' : '0' ?>" data-q="<?= e($q) ?>">
            <div class="okc-thumb">
                <?php if ($item['photo']['exists']): ?><img src="<?= e($item['photo']['url']) ?>" alt="" loading="lazy" decoding="async"><?php else: ?><span>Belum ada foto</span><?php endif; ?>
            </div>
            <div class="okc-main">
                <header class="okc-top">
                    <h4><?= e($item['title'] !== '' ? $item['title'] : $item['route_key']) ?></h4>
                    <span class="okc-badge is-<?= e($item['status']) ?>"><?= e($okcStatusLabel[$item['status']] ?? $item['status']) ?></span>
                    <?php if (!$item['active']): ?><span class="okc-badge is-off" title="Tidak dipakai di tur; hanya data pendukung">Tidak aktif</span><?php endif; ?>
                </header>
                <p class="okc-meta"><?= e($item['scene'] !== '' ? $item['scene'] : 'Panorama belum dipilih') ?> · <?= e($item['target'] !== '' ? $item['target'] : 'tanpa target') ?> ·
                    <?php if ($item['infoId'] !== ''): ?><a href="<?= e($item['pageUrl']) ?>" target="_blank" rel="noopener" title="Buka halaman webview objek ini">objinfo/<?= e($item['infoId']) ?> ↗</a><?php else: ?>Info ID kosong<?php endif; ?></p>
                <?php if (isset($item['texts']['id'])): ?><p class="okc-desc"><?= e($item['texts']['id']['desc']) ?></p><?php endif; ?>
                <div class="okc-langs" aria-label="Kelengkapan bahasa">
                    <?php foreach ($oiLangs as $code => $label): ?><span class="okc-lang<?= !empty($item['langs'][$code]) ? ' is-on' : '' ?>" title="<?= e($label) ?>: <?= !empty($item['langs'][$code]) ? 'terisi' : 'belum diisi' ?>"><?= e(strtoupper((string) $code)) ?></span><?php endforeach; ?>
                    <span class="okc-lang okc-foto<?= $item['photo']['exists'] ? ' is-on' : '' ?>" title="Foto: <?= $item['photo']['exists'] ? 'ada' : 'belum ada' ?>">FOTO</span>
                </div>
                <?php if ($item['notes'] !== []): ?><ul class="okc-notes"><?php foreach ($item['notes'] as $note): ?><li><?= e($note) ?></li><?php endforeach; ?></ul><?php endif; ?>
                <?php if ($item['texts'] !== []): ?>
                <details class="okc-more"><summary>Lihat isi tiap bahasa</summary>
                    <dl>
                        <?php foreach ($item['texts'] as $code => $text): ?>
                        <dt><?= e(strtoupper((string) $code)) ?></dt>
                        <dd dir="auto"><b><?= e($text['title']) ?></b><br><?= e($text['desc']) ?></dd>
                        <?php endforeach; ?>
                    </dl>
                </details>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <p class="okc-blank" id="okcEmpty" hidden>Tidak ada objek untuk saringan ini.</p>
    <?php endif; ?>
</div>
