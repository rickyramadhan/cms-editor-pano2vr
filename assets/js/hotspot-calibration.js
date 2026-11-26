/* hotspot-calibration.js — tab CMS "Kalibrasi Hotspot".
 *
 * Alur: (1) tur sungguhan dimuat di iframe 1366x768 (?cms_preview melewati intro) dan mesin assets/js/hotspot-calibration-engine.js disuntikkan ke dalamnya;
 * (2) mesin mengukur semua hotspot per panorama dan menggeser yang saling menimpa ke posisi terdekat yang bebas tabrak, hanya di tur (belum ada yang ditulis);
 * (3) hasil ditampilkan; tiap perubahan bisa dilihat BERDAMPINGAN: Sebelum (tur kedua, posisi sekarang) di atas dan Sesudah (hasil kalibrasi) di bawah, diperbesar di sekitar hotspot; dicentang per perubahan; (4) "Terapkan" mengirim yang dicentang ke
 * hotspot-calibration-apply.php (pano.xml + lokasi-database.js, dengan cadangan + jurnal), lalu tur dimuat ulang dan diperiksa lagi sebagai verifikasi;
 * (5) "Urungkan kalibrasi" mengembalikan angka lama dari jurnal.
 */
(() => {
  'use strict';
  const root = document.getElementById('hcal'), stateEl = document.getElementById('hcalState');
  if (!root || !stateEl) return;
  const S = JSON.parse(stateEl.textContent || '{}');
  const $ = (id) => document.getElementById(id);
  const toast = (message, kind = 'success') => { try { (window.cmsToast?.[kind] || window.cmsToast)?.(message); } catch (e) { /* toast opsional */ } };
  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const num = (n) => (Math.round(Number(n) * 100) / 100).toFixed(2);
  const VIEW = { w: 1366, h: 768 };
  const KIND = { land: 'Hotspot darat', oi: 'Objek Info', dg: 'Drone ke darat', air: 'Drone', near: 'Lokasi terdekat' };
  const el = {
    frame: $('hcalFrame'), wrap: $('hcalFrameWrap'), cover: $('hcalCover'), frameB: $('hcalFrameB'), wrapB: $('hcalFrameWrapB'), coverB: $('hcalCoverB'), paneB: $('hcalPaneBefore'),
    compare: $('hcalCompare'), capA: $('hcalAfterCap'), capAText: $('hcalCapAfter'), capBText: $('hcalCapBefore'), hint: $('hcalHint'), run: $('hcalRun'), cancel: $('hcalCancel'), apply: $('hcalApply'), applyCount: $('hcalApplyCount'),
    undo: $('hcalUndo'), undoCount: $('hcalUndoCount'), status: $('hcalStatus'), statusText: $('hcalStatusText'), bar: $('hcalBar'), barFill: $('hcalBarFill'), result: $('hcalResult'),
  };
  let engineSrc = '', busy = false, cancelled = false, results = null, win = null, beforeWin = null, beforeLoading = null, lastPreview = -1;
  const HINT = el.hint ? el.hint.textContent : '';

  /* iframe berukuran tetap 1366x768 (tampilan desktop). Satu pratinjau: diperkecil agar seluruh bingkai muat di kartu. Mode banding (is-crop): diperbesar 70% dan digeser
     sehingga titik tengah bingkai (tempat kamera mengarah ke hotspot) jatuh di 50% lebar / 62% tinggi jendela. */
  const ZOOM = 0.7, FOCUS = { x: 0.5, y: 0.62 };
  const fitWrap = (wrap) => {
    const w = wrap.clientWidth || 700;
    if (wrap.classList.contains('is-crop')) {
      const h = wrap.clientHeight || 300;
      wrap.style.setProperty('--s', String(ZOOM));
      wrap.style.setProperty('--tx', (w * FOCUS.x - VIEW.w / 2 * ZOOM).toFixed(1) + 'px');
      wrap.style.setProperty('--ty', (h * FOCUS.y - VIEW.h / 2 * ZOOM).toFixed(1) + 'px');
    } else wrap.style.setProperty('--s', String(w / VIEW.w));
  };
  const fit = () => { fitWrap(el.wrap); fitWrap(el.wrapB); };
  if ('ResizeObserver' in window) { const ro = new ResizeObserver(fit); ro.observe(el.wrap); ro.observe(el.wrapB); }
  window.addEventListener('resize', fit); fit();
  const setCompare = (on) => {
    el.compare.dataset.mode = on ? 'compare' : 'single';
    el.wrap.classList.toggle('is-crop', on); el.wrapB.classList.toggle('is-crop', on);
    el.paneB.hidden = !on; el.capA.hidden = !on;
    if (el.hint) el.hint.textContent = on ? 'Menampilkan area di sekitar hotspot yang digeser (diperbesar 70%). Panorama boleh digeser atau di-zoom: kedua panel ikut bergerak bersama. Tekan Lihat pada perubahan lain untuk membandingkan.' : HINT;
    fit();
  };
  /* kamera kedua panel bergerak bersama: geser/zoom di satu panel diteruskan ke panel lain sebesar SELISIHNYA (bukan nilai mutlak, karena tiap panel membidik posisi hotspot
     yang berbeda). Hanya panel yang sedang disentuh yang jadi penggerak, supaya putaran otomatis/animasi tur tidak saling menggandakan. */
  const drive = { a: false, b: false }; let lastCam = null, syncRaf = 0;
  const cam = (w) => ({ pan: w.pano.getPan(), tilt: w.pano.getTilt(), fov: w.pano.getVFov() });
  const wrap180 = (d) => ((((d + 180) % 360) + 360) % 360) - 180;
  const moved = (d) => Math.abs(d.pan) + Math.abs(d.tilt) + Math.abs(d.fov) > 0.01;
  const delta = (now, then) => ({ pan: wrap180(now.pan - then.pan), tilt: now.tilt - then.tilt, fov: now.fov - then.fov });
  const follow = (w, c, d) => { w.pano.setPan(c.pan + d.pan); w.pano.setTilt(c.tilt + d.tilt); w.pano.setVFov(c.fov + d.fov); };
  function bindDrive(w, key) {
    if (!w || w.__hcalSyncBound) return; w.__hcalSyncBound = true; let timer = 0;
    // penggerak dilepas dengan jeda: setelah tombol dilepas Pano2VR masih meluncur (inersia), dan luncurannya ikut diteruskan ke panel lain
    const hold = (ms) => { drive[key] = true; clearTimeout(timer); timer = setTimeout(() => { drive[key] = false; }, ms); };
    w.document.addEventListener('pointerdown', () => { drive[key] = true; clearTimeout(timer); }, true);
    ['pointerup', 'pointercancel'].forEach((ev) => w.document.addEventListener(ev, () => hold(1100), true));
    w.document.addEventListener('wheel', () => hold(700), { capture: true, passive: true });
  }
  function syncLoop() {
    syncRaf = 0;
    if (el.compare.dataset.mode !== 'compare' || !win || !beforeWin) return;
    try {
      const a = cam(win), b = cam(beforeWin);
      if (lastCam) {
        const da = delta(a, lastCam.a), db = delta(b, lastCam.b);
        if (drive.a && !drive.b && moved(da)) follow(beforeWin, b, da);
        else if (drive.b && !drive.a && moved(db)) follow(win, a, db);
      }
      lastCam = { a: cam(win), b: cam(beforeWin) };
    } catch (e) { lastCam = null; /* salah satu tur sedang berpindah panorama */ }
    syncRaf = requestAnimationFrame(syncLoop);
  }
  function startSync() { bindDrive(win, 'a'); bindDrive(beforeWin, 'b'); lastCam = null; if (!syncRaf) syncRaf = requestAnimationFrame(syncLoop); }
  const disposeBefore = () => { beforeWin = null; beforeLoading = null; try { el.frameB.src = 'about:blank'; } catch (e) { /* abaikan */ } };

  const setStatus = (html, tone = '', pct = null) => {
    el.statusText.innerHTML = html; el.status.className = 'hcal-status' + (tone ? ' is-' + tone : '');
    if (pct === null) el.bar.hidden = true; else { el.bar.hidden = false; el.barFill.style.width = Math.round(Math.max(0, Math.min(1, pct)) * 100) + '%'; }
  };
  const picked = () => (results ? [...el.result.querySelectorAll('input[data-i]:checked')].map((c) => results.moves[Number(c.dataset.i)]).filter(Boolean) : []);
  function refresh() {
    el.run.hidden = busy; el.cancel.hidden = !busy; root.classList.toggle('is-busy', busy);
    const n = picked().length, can = !busy && !!results && results.moves.length > 0;
    el.apply.hidden = !can; el.apply.disabled = !can || n === 0; el.applyCount.textContent = String(n);
    el.undo.disabled = busy || !S.undo; el.undoCount.hidden = !S.undo; if (S.undo) el.undoCount.textContent = String(S.undo.count);
    el.run.querySelector('.oi-save-label').textContent = results || S.ran ? 'Kalibrasi ulang' : 'Kalibrasi';
  }
  const setBusy = (on) => { busy = on; refresh(); };

  /* muat tur sungguhan di sebuah iframe lalu pasang mesin di dalamnya */
  async function loadInto(frame) {
    const w = await new Promise((resolve, reject) => {
      const timer = setTimeout(() => reject(new Error('Tur tidak selesai dimuat dalam 60 detik.')), 60000);
      const done = () => { clearTimeout(timer); frame.removeEventListener('load', done); resolve(frame.contentWindow); };
      frame.addEventListener('load', done); frame.src = S.tourBase + '?cms_preview=' + Date.now();
    });
    if (!engineSrc) {
      const r = await fetch('assets/js/hotspot-calibration-engine.js?v=' + (S.engineVer || ''), { cache: 'no-store' });
      if (!r.ok) throw new Error('Mesin kalibrasi tidak dapat dimuat.');
      engineSrc = await r.text();
    }
    const script = w.document.createElement('script'); script.textContent = engineSrc; w.document.head.appendChild(script);
    if (!w.__HSCAL) throw new Error('Mesin kalibrasi gagal dipasang di tur.');
    return w;
  }
  async function openTour() { el.cover.hidden = true; win = await loadInto(el.frame); return win; }
  /* tur kedua untuk "Sebelum": dimuat sekali (posisi di data sekarang, belum diubah), dipakai ulang untuk perubahan berikutnya */
  function ensureBefore() {
    if (beforeWin && beforeWin.__HSCAL) return Promise.resolve(beforeWin);
    if (!beforeLoading) {
      el.coverB.hidden = false;
      beforeLoading = loadInto(el.frameB).then(async (w) => { await w.__HSCAL.prepare(); beforeWin = w; el.coverB.hidden = true; return w; })
        .catch((e) => { beforeLoading = null; el.coverB.hidden = true; throw e; });
    }
    return beforeLoading;
  }
  const progress = (i, n, name) => setStatus('Memeriksa panorama <b>' + Math.min(i + 1, n) + '/' + n + '</b> · ' + esc(name), '', n ? i / n : 0);

  async function post(extra) {
    const r = await fetch('hotspot-calibration-apply.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf: S.csrf, id_lokasi: S.location, ...extra }) });
    let d; try { d = await r.json(); } catch (e) { throw new Error('Respons server tidak valid.'); }
    if (!d.success) throw new Error(d.message || 'Gagal.');
    return d;
  }

  async function runCalibration() {
    if (busy) return;
    cancelled = false; results = null; lastPreview = -1; el.result.hidden = true; setCompare(false); setBusy(true);
    try {
      setStatus('Memuat tur…', '', 0.02);
      const w = await openTour();
      const out = await w.__HSCAL.run({ solve: true, isCancelled: () => cancelled, onProgress: progress });
      if (out.cancelled) { setStatus('Dibatalkan. Tidak ada yang diubah.'); toast('Kalibrasi dibatalkan.', 'info'); return; }
      results = JSON.parse(JSON.stringify(out)); S.ran = true;
      render(false);
    } catch (e) { setStatus('Gagal: ' + esc(e.message), 'bad'); toast('Kalibrasi gagal: ' + e.message, 'error'); }
    finally { setBusy(false); }
    if (results && results.moves.length) preview(0);   // langsung tampilkan perbandingan perubahan pertama
  }

  /* hasil: ringkasan, daftar perubahan per panorama (dicentang), dan tabrakan yang belum bisa dibereskan */
  function render(verifying) {
    const st = results.stats, moves = results.moves, bad = results.failed;
    const tile = (n, label, cls) => '<div class="' + (cls || '') + '"><b>' + n + '</b><small>' + label + '</small></div>';
    let html = '<div class="hcal-sum">' + tile(st.scenes, 'panorama') + tile(st.items, 'hotspot') + tile(st.before, 'bertabrakan', st.before ? 'is-warn' : '') + tile(bad.length, verifying ? 'masih tabrak' : 'belum teratasi', bad.length ? 'is-bad' : '') + '</div>';
    const byScene = new Map();
    const bucket = (id, name) => { if (!byScene.has(id)) byScene.set(id, { name, moves: [], bad: [] }); return byScene.get(id); };
    moves.forEach((m, i) => bucket(m.scene, m.sceneName).moves.push([m, i]));
    bad.forEach((f) => bucket(f.scene, f.sceneName).bad.push(f));
    const kind = (k) => '<i class="hcal-kind k-' + esc(k) + '">' + esc(KIND[k] || k) + '</i>';
    html += '<div class="hcal-list">';
    for (const [id, g] of byScene) {
      html += '<section class="hcal-scene"><h4>' + esc(g.name) + '<small>' + esc(id) + '</small></h4>';
      for (const [m, i] of g.moves) {
        html += '<div class="hcal-move"><input type="checkbox" id="hcalM' + i + '" data-i="' + i + '" checked><label class="hcal-top" for="hcalM' + i + '">' + kind(m.kind) + '<b>' + esc(m.label) + '</b></label>'
          + '<span class="hcal-delta">pan ' + num(m.from.pan) + '° → ' + num(m.to.pan) + '° · tilt ' + num(m.from.tilt) + '° → ' + num(m.to.tilt) + '°</span>'
          + '<span class="hcal-why">Menimpa: ' + esc(m.reason.join(', ')) + '</span><button type="button" class="hcal-see" data-see="' + i + '">Lihat</button></div>';
      }
      for (const f of g.bad) html += '<div class="hcal-bad"><div class="hcal-top">' + kind(f.a.kind) + '<b>' + esc(f.a.label) + '</b><span aria-hidden="true">↔</span>' + kind(f.b.kind) + '<b>' + esc(f.b.label) + '</b></div><p>' + (verifying ? 'Masih saling menimpa.' : 'Belum ada posisi bebas tabrak dalam batas geser yang aman.') + ' Geser salah satunya manual di tab Object Informasi (pin dan hotspot) atau URL Lokasi.</p></div>';
      html += '</section>';
    }
    html += '</div>';
    el.result.innerHTML = byScene.size ? html : html.replace('<div class="hcal-list"></div>', ''); el.result.hidden = false;
    if (verifying) {
      if (!st.before) setStatus('<b>Terverifikasi.</b> Tur dimuat ulang dengan posisi yang baru disimpan dan tidak ada hotspot yang saling menimpa.', 'ok');
      else setStatus('<b>' + st.before + ' tabrakan masih tersisa</b> setelah diterapkan. Jalankan Kalibrasi lagi atau geser manual.', 'warn');
    } else if (!st.before) setStatus('<b>Tidak ada hotspot yang saling menimpa</b> di ' + st.scenes + ' panorama tur ini. Tidak ada yang perlu diubah.', 'ok');
    else if (!bad.length) setStatus('<b>' + st.before + ' tabrakan ditemukan</b> dan semuanya bisa dibereskan dengan menggeser ' + moves.length + ' hotspot. Lihat tiap perubahan di pratinjau, lalu tekan <b>Terapkan</b>.', 'ok');
    else setStatus('<b>' + st.before + ' tabrakan ditemukan.</b> ' + moves.length + ' hotspot bisa digeser; ' + bad.length + ' tabrakan belum teratasi dalam batas geser yang aman (lihat daftar).', 'warn');
    refresh();
  }

  /* pratinjau satu perubahan BERDAMPINGAN: Sebelum (tur kedua, posisi sekarang) di atas, Sesudah (tur ini, hasil kalibrasi) di bawah; keduanya dibuka di panorama yang sama dan
     kameranya diarahkan ke hotspot yang digeser */
  async function preview(index) {
    const m = results && results.moves[index]; if (!m || busy || !win || !win.__HSCAL) return;
    lastPreview = index; setBusy(true); setCompare(true);
    el.result.querySelectorAll('.hcal-move').forEach((row) => row.classList.toggle('is-viewing', row.querySelector('[data-see]')?.dataset.see === String(index)));
    el.capBText.textContent = m.label + ' menimpa ' + (m.reason.join(', ') || 'hotspot lain');
    el.capAText.textContent = m.label + ' digeser: pan ' + num(m.from.pan) + '° → ' + num(m.to.pan) + '°, tilt ' + num(m.from.tilt) + '° → ' + num(m.to.tilt) + '°';
    const keepHtml = el.statusText.innerHTML, keepClass = el.status.className, firstTime = !beforeWin;
    if (firstTime) setStatus('Menyiapkan tampilan pembanding… (tur dimuat sekali lagi)', '', null);
    try {
      const scene = results.moves.filter((x) => x.scene === m.scene);
      const wb = await ensureBefore();
      lastCam = null;
      await Promise.all([wb.__HSCAL.show(m.scene, scene, 'before'), win.__HSCAL.show(m.scene, scene, 'after')]);
      startSync();
      el.compare.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    } catch (e) { toast('Pembanding gagal ditampilkan: ' + e.message, 'error'); }
    finally { if (firstTime) { el.statusText.innerHTML = keepHtml; el.status.className = keepClass; el.bar.hidden = true; } setBusy(false); }
  }

  async function apply() {
    const chosen = picked(); if (!chosen.length || busy) return;
    setBusy(true); setStatus('Menyimpan ' + chosen.length + ' perubahan…', '', 0.4);
    try {
      const res = await post({ action: 'apply', moves: chosen.map((m) => ({ source: m.source, scene: m.scene, id: m.id, sourceSlug: m.sourceSlug, pan: m.to.pan, tilt: m.to.tilt, label: m.label, kind: m.kind })) });
      S.undo = res.undo; toast(res.message); results = null; el.result.hidden = true; disposeBefore(); setCompare(false); refresh();
      setStatus('Tersimpan. Memuat ulang tur untuk verifikasi…', '', 0);
      const w = await openTour();
      const out = await w.__HSCAL.run({ solve: false, isCancelled: () => cancelled, onProgress: progress });
      results = JSON.parse(JSON.stringify(out)); results.moves = []; render(true);
    } catch (e) { setStatus('Gagal: ' + esc(e.message), 'bad'); toast('Gagal menerapkan: ' + e.message, 'error'); }
    finally { setBusy(false); }
  }

  async function undo() {
    if (!S.undo || busy) return;
    if (!window.confirm('Kembalikan ' + S.undo.count + ' hotspot ke posisi sebelum kalibrasi terakhir?')) return;
    setBusy(true);
    try {
      const res = await post({ action: 'undo' });
      S.undo = res.undo; results = null; el.result.hidden = true; disposeBefore(); setCompare(false);
      el.frame.src = 'about:blank'; el.cover.hidden = false; win = null;
      setStatus('Posisi hotspot dikembalikan. Tekan <b>Kalibrasi</b> untuk memeriksa lagi.'); toast(res.message, 'info');
    } catch (e) { setStatus('Gagal: ' + esc(e.message), 'bad'); toast('Gagal mengurungkan: ' + e.message, 'error'); }
    finally { setBusy(false); }
  }

  el.run.addEventListener('click', runCalibration);
  el.cancel.addEventListener('click', () => { cancelled = true; setStatus('Membatalkan setelah panorama ini selesai…'); });
  el.apply.addEventListener('click', apply);
  el.undo.addEventListener('click', undo);
  el.result.addEventListener('change', (e) => { if (e.target.matches('input[data-i]')) refresh(); });
  el.result.addEventListener('click', (e) => { const b = e.target.closest('[data-see]'); if (b) preview(Number(b.dataset.see)); });
  refresh();
})();
