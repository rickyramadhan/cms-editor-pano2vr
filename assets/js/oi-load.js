/*
 * oi-load.js: tombol "Load Objek Info" di editor Object Informasi.
 *
 * Memeriksa (lewat server) pin Objek Info AKTIF yang tidak ada di pano.xml, misalnya setelah tur di-export ulang dari Pano2VR,
 * lalu memuat posisinya kembali ke editor dari memori CMS, cadangan pano.xml, atau pin Smart Kit. Berdiri sendiri:
 * keadaan editor diberikan lewat OiLoad.init({ getRecords, getElements, load, ... }), jadi mudah diuji tanpa halaman CMS.
 */
(() => {
  'use strict';
  const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const keyOf = (item) => (String(item.nodeID || '').replace(/[{}]/g, '').trim() + '|' + String(item.targetID || '').trim()).toLowerCase();
  const SOURCE_TEXT = { memori: 'Memori CMS', backup: 'Cadangan pano.xml', pin: 'Pin Smart Kit' };
  const when = (iso) => {
    const date = new Date(iso);
    if (!iso || Number.isNaN(date.getTime())) return '';
    try { return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(date); } catch (e) { return iso; }
  };

  function init(opts) {
    const button = opts.button;
    if (!button || button.dataset.oiLoadReady) return null;
    button.dataset.oiLoadReady = '1';
    const countEl = button.querySelector('.oi-load-count');
    const toast = opts.toast || (() => {});
    let dialog = null, requestId = 0, scan = null;

    const baseTitle = button.getAttribute('title') || '';
    function setBadge(missing) {
      if (countEl) { countEl.hidden = !missing; countEl.textContent = missing ? String(missing) : ''; }
      button.classList.toggle('has-missing', missing > 0);
      button.setAttribute('aria-label', 'Load Objek Info' + (missing ? ', ' + missing + ' pin hilang dari pano.xml' : ''));
      button.title = missing ? missing + ' pin Objek Info aktif tidak ada di pano.xml. Klik untuk memuatnya kembali.' : baseTitle;
    }

    // ── dialog ─────────────────────────────────────────────────────────────
    function ensureDialog() {
      if (dialog) return dialog;
      dialog = document.createElement('dialog');
      dialog.className = 'oi-load-dialog';
      dialog.setAttribute('aria-labelledby', 'oiLoadTitle');
      dialog.innerHTML = '<div class="oi-load-card"><header><h3 id="oiLoadTitle">Load Objek Info</h3><button type="button" class="oi-load-x" data-oi-close aria-label="Tutup">×</button></header><div class="oi-load-body" aria-live="polite"></div><footer></footer></div>';
      document.body.append(dialog);
      dialog.addEventListener('click', (event) => {
        if (event.target === dialog || event.target.closest('[data-oi-close]')) dialog.close();
        else if (event.target.closest('[data-oi-retry]')) run();
        else if (event.target.closest('[data-oi-all]')) toggleAll();
        else if (event.target.closest('[data-oi-apply]')) apply();
      });
      dialog.addEventListener('change', (event) => { if (event.target.matches('.oi-load-pick')) refreshApply(); });
      return dialog;
    }
    const body = () => dialog.querySelector('.oi-load-body');
    const foot = () => dialog.querySelector('footer');
    const close = '<button type="button" class="oi-load-secondary" data-oi-close>Tutup</button>';

    function renderLoading() {
      body().innerHTML = '<p class="oi-load-wait"><span class="oi-load-spin" aria-hidden="true"></span>Memeriksa pin Objek Info di pano.xml…</p>';
      foot().innerHTML = close;
    }
    function renderError(message) {
      body().innerHTML = '<p class="oi-load-error" role="alert"><b>Gagal memeriksa.</b> ' + esc(message) + '</p>';
      foot().innerHTML = close + '<button type="button" class="oi-load-primary" data-oi-retry>Coba lagi</button>';
    }
    function renderResult(result) {
      const sum = result.summary, items = result.items || [];
      const inactiveNote = sum.inactiveRecords ? '<p class="oi-load-note">' + sum.inactiveRecords + ' record nonaktif tidak ikut diperiksa: record nonaktif memang tidak dipakai di tur sehingga tidak punya pin.</p>' : '';
      if (!sum.pins) {
        body().innerHTML = '<p class="oi-load-ok"><b>Belum ada pin yang perlu dimuat.</b> Belum ada Objek Info aktif dengan Node ID dan Target ID terisi.</p>' + inactiveNote;
        foot().innerHTML = close; return;
      }
      if (!sum.missing) {
        body().innerHTML = '<p class="oi-load-ok"><b>Semua pin lengkap.</b> ' + sum.present + ' pin Objek Info aktif sudah ada di pano.xml, tidak ada yang perlu dimuat.</p>' + inactiveNote +
          '<p class="oi-load-note">Posisi pin diingat otomatis setiap Anda menekan Simpan. Bila tur di-export ulang dari Pano2VR dan pin-nya hilang, tombol ini memuatnya kembali.</p>';
        foot().innerHTML = close; return;
      }
      const rows = items.map((item, index) => {
        const ok = !!item.element;
        const meta = '<span class="oi-load-meta">' + esc(item.nodeID) + ' · ' + esc(item.targetID) + (item.records > 1 ? ' · ' + item.records + ' record' : '') + '</span>';
        const source = ok ? '<span class="oi-load-tag">' + esc(SOURCE_TEXT[item.source] || item.sourceLabel || 'Tersimpan') + (when(item.when) ? ' · ' + esc(when(item.when)) : '') + '</span>' : '<span class="oi-load-why">' + esc(item.reason || 'Posisi tidak ditemukan.') + '</span>';
        return '<li class="' + (ok ? '' : 'is-off') + '"><label><input type="checkbox" class="oi-load-pick" data-i="' + index + '"' + (ok ? ' checked' : ' disabled') + '><span class="oi-load-name"><b>' + esc(item.title) + '</b>' + meta + source + '</span></label></li>';
      }).join('');
      body().innerHTML = '<p class="oi-load-lead"><b>' + sum.missing + ' dari ' + sum.pins + ' pin Objek Info aktif tidak ada di pano.xml.</b> Biasanya terjadi setelah tur di-export ulang dari Pano2VR. Data teksnya masih ada; hanya posisi pin yang perlu dimuat.</p>' +
        (sum.restorable ? '<div class="oi-load-bar"><button type="button" class="oi-load-link" data-oi-all></button></div>' : '') +
        '<ul class="oi-load-list">' + rows + '</ul>' + inactiveNote +
        '<p class="oi-load-note">Pin dimuat ke editor lebih dulu. Tekan <b>Simpan</b> untuk menuliskannya ke pano.xml; bisa dibatalkan dengan Urungkan (Ctrl+Z).</p>';
      foot().innerHTML = '<button type="button" class="oi-load-secondary" data-oi-close>Batal</button>' + (sum.restorable ? '<button type="button" class="oi-load-primary" data-oi-apply></button>' : '');
      refreshApply();
    }
    const picks = () => Array.from(dialog.querySelectorAll('.oi-load-pick:not(:disabled)'));
    function refreshApply() {
      const chosen = picks().filter((box) => box.checked).length, all = dialog.querySelector('[data-oi-all]'), go = dialog.querySelector('[data-oi-apply]');
      if (go) { go.disabled = chosen === 0; go.textContent = chosen ? 'Muat ' + chosen + ' pin' : 'Pilih pin dulu'; }
      if (all) all.textContent = chosen === picks().length ? 'Kosongkan pilihan' : 'Pilih semua';
    }
    function toggleAll() { const all = picks(), on = !all.every((box) => box.checked); all.forEach((box) => { box.checked = on; }); refreshApply(); }

    // ── pemindaian + muat ───────────────────────────────────────────────────
    async function fetchScan() {
      const response = await fetch(opts.url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf: opts.csrf, action: 'oi_scan', id_lokasi: opts.locationId, records: opts.getRecords(), webElements: opts.getElements() }) });
      let data = null;
      try { data = await response.json(); } catch (e) { throw new Error('Jawaban server tidak terbaca (HTTP ' + response.status + ').'); }
      if (!response.ok || !data.success) throw new Error(data.message || 'Server menolak permintaan.');
      return data;
    }
    async function run() {
      const mine = ++requestId;
      renderLoading();
      try {
        const data = await fetchScan();
        if (mine !== requestId) return;
        scan = data; setBadge(data.summary.missing); renderResult(data);
      } catch (error) { if (mine === requestId) renderError(error.message); }
    }
    function apply() {
      if (!scan) return;
      const chosen = picks().filter((box) => box.checked).map((box) => scan.items[Number(box.dataset.i)]).filter((item) => item && item.element);
      if (!chosen.length) return;
      const have = new Set(opts.getElements().map(keyOf)), fresh = [];
      chosen.forEach((item) => { const key = keyOf(item.element); if (!have.has(key)) { have.add(key); fresh.push(item.element); } });   // tanpa duplikat, juga di dalam satu kiriman
      const added = fresh.length ? opts.load(fresh, chosen) : 0;
      dialog.close();
      const left = Math.max(0, scan.summary.missing - chosen.length);
      setBadge(left);
      toast(added ? added + ' pin Objek Info dimuat ke editor. Tekan Simpan untuk menuliskannya ke pano.xml.' : 'Pin itu sudah ada di editor, tidak ada yang ditambahkan.', added ? 'success' : 'info');
    }
    button.addEventListener('click', () => {
      ensureDialog();
      if (!dialog.open) dialog.showModal();
      run();
    });
    // fokus kembali ke tombol saat dialog tertutup (pemakai keyboard tidak kehilangan tempat)
    document.addEventListener('close', (event) => { if (dialog && event.target === dialog) button.focus(); }, true);

    // ── keadaan awal dari server (lencana + pemberitahuan sekali per sesi) ──
    const initial = opts.initial;
    if (initial && initial.summary) {
      setBadge(initial.summary.missing);
      if (initial.summary.missing > 0) {
        let seen = false;
        const flag = 'oiLoadHint:' + opts.locationId;
        try { seen = sessionStorage.getItem(flag) === '1'; sessionStorage.setItem(flag, '1'); } catch (e) { /* penyimpanan tidak tersedia: tampilkan saja */ }
        if (!seen) toast(initial.summary.missing + ' Objek Info aktif belum punya pin di pano.xml (biasanya setelah export ulang). Klik Load Objek Info untuk memuatnya.', 'warn');
      }
    }
    return { rescan: run, setBadge };
  }

  window.OiLoad = { init };
})();
