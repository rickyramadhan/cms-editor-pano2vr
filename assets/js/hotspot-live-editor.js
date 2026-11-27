/* hotspot-live-editor.js — "Edit di panorama" (Audit › Hotspot): editor hotspot langsung di tur sungguhan dengan HUD asli Smart Kit.
 *
 * Tombol di tiap grup panorama membuka modal: kiri = tur asli (iframe 1366x768, HUD Smart Kit apa adanya) di panorama itu, kanan = daftar hotspot.
 * Di tur: klik memilih hotspot, tarik menggeser (pano.updateHotspot), hotspot baru tampil sebagai salinan HUD asli, hotspot yang dihapus disembunyikan,
 * judul diperbarui langsung. Di daftar: ubah judul, ganti panorama tujuan (bergambar), Fokus, Hapus, dan Tambah hotspot (jenis + tujuan + judul, dipasang di
 * tengah pandangan). Perubahan hanya ditahan di sini; Simpan mengirimnya ke hotspot-editor-save.php (pano.xml + cadangan), lalu tur dimuat ulang sebagai verifikasi.
 * Agen di dalam tur: assets/js/hotspot-live-agent.js (memakai assets/js/hotspot-calibration-engine.js).
 */
(() => {
  'use strict';
  if (window.__hlEditorLoaded) return;   // pemuatan ganda tidak boleh memasang pendengar dua kali
  window.__hlEditorLoaded = true;
  const stateEl = document.getElementById('hlState');
  if (!stateEl) return;
  const S = JSON.parse(stateEl.textContent || '{}');
  const toast = (message, kind = 'success') => { try { (window.cmsToast?.[kind] || window.cmsToast)?.(message); } catch (e) { /* toast opsional */ } };
  const el = (tag, cls, text) => { const e = document.createElement(tag); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; };
  const num = (n) => (Math.round(Number(n) * 100) / 100).toFixed(2);
  const VIEW = { w: 1366, h: 768 };
  const KIND = { land: 'Hotspot darat', dg: 'Drone ke darat', air: 'Drone' };
  const skinLabel = (skin) => (/drone/i.test(skin) ? 'Drone' : /darat/i.test(skin) ? 'Hotspot darat' : /360_?video/i.test(skin) ? 'Video 360' : skin);
  const panoOf = (url) => S.panoramas.find((p) => url === '{' + p.value + '}' || url === '{' + p.nodeId + '}') || null;

  let modal = null, els = {}, win = null, engineSrc = '', agentSrc = '', node = '', items = new Map(), order = [], selected = null, nextNew = 1, savedOnce = false, busy = false, seq = 0, addTitleTouched = false;

  /* ---------- modal ---------- */
  function ensureModal() {
    if (modal) return modal;
    modal = el('div', 'hl-modal'); modal.setAttribute('role', 'dialog'); modal.setAttribute('aria-modal', 'true'); modal.setAttribute('aria-hidden', 'true');
    modal.innerHTML = '<div class="hl-card">'
      + '<header class="hl-head"><div class="hl-title"><strong id="hlTitle">Edit hotspot</strong><small id="hlSub"></small></div>'
      + '<div class="hl-actions"><span class="hl-dirty" id="hlDirty" hidden></span><button type="button" class="hl-btn" id="hlReset" disabled>Batalkan perubahan</button><button type="button" class="hl-btn is-primary" id="hlSave" disabled>Simpan</button><button type="button" class="hl-close" id="hlClose" aria-label="Tutup">×</button></div></header>'
      + '<div class="hl-body"><div class="hl-stage-col"><div class="hl-stage"><div class="hl-frame-wrap" id="hlWrap"><iframe id="hlFrame" title="Tur untuk edit hotspot" allow="autoplay; fullscreen" src="about:blank"></iframe></div><div class="hl-loading" id="hlLoading" hidden></div></div>'
      + '<p class="hl-hint">Hotspot yang tampil memakai HUD asli Smart Kit; header, dock, dan panel lain disembunyikan agar tidak menutupi. <b>Klik</b> hotspot untuk memilih, <b>tarik</b> untuk memindahkan; geser atau zoom panorama seperti biasa. Perubahan baru tersimpan setelah menekan Simpan.</p></div>'
      + '<aside class="hl-side"><div class="hl-list" id="hlList" aria-label="Hotspot di panorama ini"></div>'
      + '<form class="hl-add" id="hlAdd" autocomplete="off"><h4>Tambah hotspot <small>dipasang di tengah pandangan, lalu tarik ke posisinya</small></h4><div class="hl-add-grid"><label><span>Jenis</span><select id="hlAddSkin"></select></label><label><span>Panorama tujuan</span><select id="hlAddTarget" data-pano-thumbs></select></label>'
      + '<label><span>Judul hotspot</span><input type="text" id="hlAddTitle" maxlength="120" placeholder="mis. Menuju area parkir"></label><button type="submit" class="hl-btn is-primary" id="hlAddBtn">Tambah</button></div></form></aside></div></div>';
    document.body.append(modal);
    for (const id of ['hlTitle', 'hlSub', 'hlDirty', 'hlReset', 'hlSave', 'hlClose', 'hlWrap', 'hlFrame', 'hlLoading', 'hlList', 'hlAdd', 'hlAddSkin', 'hlAddTarget', 'hlAddTitle', 'hlAddBtn']) els[id] = modal.querySelector('#' + id);
    els.hlClose.addEventListener('click', close);
    els.hlSave.addEventListener('click', save);
    els.hlReset.addEventListener('click', revertAll);
    els.hlAdd.addEventListener('submit', (e) => { e.preventDefault(); addHotspot(); });
    els.hlAddTitle.addEventListener('input', () => { addTitleTouched = true; });
    els.hlAddTarget.addEventListener('change', () => { if (!addTitleTouched) els.hlAddTitle.value = (panoOf(els.hlAddTarget.value)?.name) || ''; });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.classList.contains('open')) { e.preventDefault(); close(); } });
    const fit = () => els.hlWrap.style.setProperty('--s', String((els.hlWrap.clientWidth || 700) / VIEW.w));
    if ('ResizeObserver' in window) new ResizeObserver(fit).observe(els.hlWrap); window.addEventListener('resize', fit); fit();
    // pilihan jenis + tujuan untuk form tambah
    for (const s of S.skins || []) { const o = el('option', '', skinLabel(s.skin)); o.value = s.skin; els.hlAddSkin.append(o); }
    fillTargetOptions(els.hlAddTarget, '');
    if (window.enhancePanoThumbSelect) window.enhancePanoThumbSelect(els.hlAddTarget);
    if (!(S.skins || []).length) els.hlAdd.hidden = true;
    return modal;
  }

  function fillTargetOptions(select, current) {
    select.replaceChildren();
    if (current === '' || current === undefined) { const o = el('option', '', '— belum ada tujuan —'); o.value = ''; select.append(o); }
    let found = current === '' || current === undefined;
    for (const p of S.panoramas) {
      const o = el('option', '', p.name || ('Panorama ' + p.nodeId)); o.value = '{' + p.value + '}'; o.dataset.thumb = p.thumb || ''; o.dataset.sub = p.nodeId; select.append(o);
      if (o.value === current) found = true;
    }
    if (!found && current) { const o = el('option', '', current + ' (tidak dikenal)'); o.value = current; select.prepend(o); }
    select.value = current || (select.options[0] ? select.options[0].value : '');
  }

  const setLoading = (text, bad) => { els.hlLoading.hidden = !text; els.hlLoading.textContent = text || ''; els.hlLoading.classList.toggle('is-bad', !!bad); };
  const frameFit = () => els.hlWrap && els.hlWrap.style.setProperty('--s', String((els.hlWrap.clientWidth || 700) / VIEW.w));

  /* ---------- tur + agen ---------- */
  async function fetchText(path, ver) { const r = await fetch(path + '?v=' + (ver || ''), { cache: 'no-store' }); if (!r.ok) throw new Error(path.split('/').pop() + ' tidak dapat dimuat.'); return r.text(); }
  async function loadTour() {
    const frame = els.hlFrame;
    const w = await new Promise((resolve, reject) => {
      const timer = setTimeout(() => reject(new Error('Tur tidak selesai dimuat dalam 60 detik.')), 60000);
      const done = () => { clearTimeout(timer); frame.removeEventListener('load', done); resolve(frame.contentWindow); };
      frame.addEventListener('load', done); frame.src = S.tourBase + '?cms_preview=' + Date.now();
    });
    if (!engineSrc) engineSrc = await fetchText('assets/js/hotspot-calibration-engine.js', S.engineVer);
    if (!agentSrc) agentSrc = await fetchText('assets/js/hotspot-live-agent.js', S.agentVer);
    for (const src of [engineSrc, agentSrc]) { const s = w.document.createElement('script'); s.textContent = src; w.document.head.appendChild(s); }
    if (!w.__HSLIVE) throw new Error('Agen editor gagal dipasang di tur.');
    return w;
  }

  async function open(nodeId) {
    ensureModal();
    node = nodeId; const p = S.panoramas.find((x) => x.nodeId === nodeId) || { nodeId, name: nodeId };
    els.hlTitle.textContent = 'Edit hotspot · ' + (p.name || nodeId); els.hlSub.textContent = nodeId;
    modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); document.body.classList.add('hl-open'); frameFit();
    await reload();
  }

  async function reload() {
    const mine = ++seq; items = new Map(); order = []; selected = null; renderList(); refreshDirty(); els.hlAddTitle.value = ''; addTitleTouched = false;
    setLoading('Memuat tur dan panorama…'); setBusy(true);
    try {
      win = await loadTour(); if (mine !== seq) return;
      await win.__HSCAL.prepare();
      win.__HSLIVE.start({ select: onAgentSelect, move: onAgentMove, drop: onAgentDrop, sceneLost: onSceneLost });
      const list = await win.__HSLIVE.openScene(node); if (mine !== seq) return;
      for (const h of list) { items.set(h.id, { id: h.id, kind: h.kind, skin: h.skin, isNew: false, deleted: false, orig: { title: h.title, url: h.url, pan: h.pan, tilt: h.tilt }, cur: { title: h.title, url: h.url, pan: h.pan, tilt: h.tilt } }); order.push(h.id); }
      if (order.length) await win.__HSLIVE.focus(order[0]);   // arahkan kamera ke hotspot pertama supaya hotspot langsung terlihat
      if (mine !== seq) return;
      setLoading(''); renderList(); refreshDirty();
    } catch (e) { setLoading('Gagal memuat tur: ' + e.message, true); toast('Gagal memuat editor: ' + e.message, 'error'); }
    finally { if (mine === seq) setBusy(false); }
  }

  /* ---------- daftar hotspot ---------- */
  const changed = (it) => it.isNew || it.deleted || it.cur.title !== it.orig.title || it.cur.url !== it.orig.url || Math.abs(it.cur.pan - it.orig.pan) > 0.0005 || Math.abs(it.cur.tilt - it.orig.tilt) > 0.0005;
  const statusOf = (it) => (it.deleted ? ['Dihapus', 'is-del'] : it.isNew ? ['Baru', 'is-new'] : changed(it) ? ['Diubah', 'is-mod'] : ['', '']);
  function posText(it) { return 'pan ' + num(it.cur.pan) + '° · tilt ' + num(it.cur.tilt) + '°'; }

  function makeCard(it) {
    const a = el('article', 'hl-item' + (it.deleted ? ' is-deleted' : '') + (selected === it.id ? ' is-selected' : '')); a.dataset.id = it.id;
    const head = el('header'); head.append(el('span', 'hl-kind k-' + it.kind, KIND[it.kind] || skinLabel(it.skin)));
    const [flagText, flagCls] = statusOf(it); const flag = el('span', 'hl-flag ' + flagCls, flagText); flag.hidden = !flagText; head.append(flag);
    head.append(el('small', 'hl-pos', posText(it)));
    const focusBtn = el('button', 'hl-link', 'Fokus'); focusBtn.type = 'button'; focusBtn.dataset.act = 'focus'; focusBtn.disabled = it.deleted;
    const delBtn = el('button', 'hl-link is-danger', it.deleted ? 'Batal hapus' : 'Hapus'); delBtn.type = 'button'; delBtn.dataset.act = 'delete';
    head.append(focusBtn, delBtn); a.append(head);
    const fields = el('div', 'hl-fields');   // judul dan tujuan berdampingan supaya satu kartu pendek
    const lt = el('label'); lt.title = 'Judul hotspot'; const ti = el('input'); ti.type = 'text'; ti.maxLength = 120; ti.value = it.cur.title; ti.dataset.field = 'title'; ti.placeholder = 'Judul hotspot'; ti.setAttribute('aria-label', 'Judul hotspot'); ti.disabled = it.deleted; lt.append(ti);
    const lu = el('label'); lu.title = 'Panorama tujuan';
    if (it.cur.url === '' || it.cur.url.startsWith('{')) { const se = el('select'); se.dataset.field = 'url'; se.dataset.panoThumbs = ''; se.setAttribute('aria-label', 'Panorama tujuan'); se.disabled = it.deleted; fillTargetOptions(se, it.cur.url); lu.append(se); }
    else { const ui = el('input'); ui.type = 'text'; ui.value = it.cur.url; ui.dataset.field = 'url'; ui.placeholder = 'https://… (tujuan)'; ui.setAttribute('aria-label', 'URL tujuan'); ui.disabled = it.deleted; lu.append(ui); }
    fields.append(lt, lu); a.append(fields);
    return a;
  }
  function renderList() {
    els.hlList.replaceChildren();
    if (!order.length) { els.hlList.append(el('p', 'hl-empty', els.hlLoading && !els.hlLoading.hidden ? '' : 'Panorama ini belum punya hotspot. Tambahkan di bawah.')); return; }
    for (const id of order) { const card = makeCard(items.get(id)); els.hlList.append(card); const se = card.querySelector('select[data-field="url"]'); if (se && window.enhancePanoThumbSelect) window.enhancePanoThumbSelect(se); }
  }
  const cardOf = (id) => els.hlList && els.hlList.querySelector('.hl-item[data-id="' + CSS.escape(id) + '"]');
  function patchCard(it) {   // perbarui penanda status + teks posisi tanpa membangun ulang (supaya kolom isian tidak kehilangan fokus)
    const c = cardOf(it.id); if (!c) return;
    const [t, cls] = statusOf(it); const f = c.querySelector('.hl-flag'); f.textContent = t; f.hidden = !t; f.className = 'hl-flag ' + cls;
    c.querySelector('.hl-pos').textContent = posText(it);
  }
  function highlight(id) {
    selected = id;
    els.hlList.querySelectorAll('.hl-item').forEach((c) => c.classList.toggle('is-selected', c.dataset.id === id));
    const c = id && cardOf(id); if (c) c.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  }

  const dirtyCount = () => { let n = 0; for (const it of items.values()) { if (it.isNew && it.deleted) continue; if (changed(it)) n++; } return n; };
  function refreshDirty() {
    const n = dirtyCount();
    els.hlDirty.hidden = n === 0; els.hlDirty.textContent = n + ' perubahan belum disimpan';
    els.hlSave.disabled = busy || n === 0; els.hlReset.disabled = busy || n === 0;
  }
  function setBusy(on) { busy = on; els.hlAddBtn.disabled = on; refreshDirty(); }

  /* ---------- panggilan balik dari agen (di dalam tur) ---------- */
  const onAgentSelect = (id) => highlight(id);
  const onAgentMove = (id, pan, tilt) => { const it = items.get(id); if (!it) return; it.cur.pan = pan; it.cur.tilt = tilt; const c = cardOf(id); if (c) c.querySelector('.hl-pos').textContent = posText(it); };
  const onAgentDrop = (id) => { const it = items.get(id); if (it) { patchCard(it); refreshDirty(); } };
  function thumbFor(url) { const p = panoOf(url); if (!p) return ''; try { const s = win.SmartKit.scenes.list().find((x) => x.id === p.nodeId); return (s && s.thumb) || p.thumb || ''; } catch (e) { return p.thumb || ''; } }
  function reapply() {   // agen mengembalikan tur ke panorama ini (hotspot kembali ke posisi pano.xml): pasang ulang keadaan yang sedang disunting
    try {
      for (const it of items.values()) {
        if (it.isNew) { if (!it.deleted) win.__HSLIVE.addPreview(it.id, it.cur.pan, it.cur.tilt, it.cur.title, it.skin, thumbFor(it.cur.url)); continue; }
        win.__HSLIVE.setPos(it.id, it.cur.pan, it.cur.tilt); win.__HSLIVE.setTitle(it.id, it.cur.title); win.__HSLIVE.setHidden(it.id, it.deleted);
        if (it.cur.url !== it.orig.url) win.__HSLIVE.setThumb(it.id, thumbFor(it.cur.url));
      }
    } catch (e) { /* tur sedang berpindah */ }
  }
  const onSceneLost = () => { reapply(); toast('Tur sempat berpindah panorama; dikembalikan ke panorama yang sedang disunting.', 'info'); };

  /* ---------- aksi di daftar ---------- */
  els_delegate();
  function els_delegate() {
    document.addEventListener('input', (e) => {
      const f = e.target.closest && e.target.closest('.hl-modal .hl-item [data-field]'); if (!f) return;
      const it = items.get(f.closest('.hl-item').dataset.id); if (!it) return;
      if (f.dataset.field === 'title') { it.cur.title = f.value; try { win.__HSLIVE.setTitle(it.id, f.value); } catch (err) { /* tur sedang dimuat */ } }
      else { it.cur.url = f.value.trim(); }
      patchCard(it); refreshDirty();
    });
    document.addEventListener('change', (e) => {
      const f = e.target.closest && e.target.closest('.hl-modal .hl-item select[data-field="url"]'); if (!f) return;
      const it = items.get(f.closest('.hl-item').dataset.id); if (!it) return;
      it.cur.url = f.value; try { win.__HSLIVE.setThumb(it.id, thumbFor(f.value)); } catch (err) { /* abaikan */ }
      patchCard(it); refreshDirty();
    });
    document.addEventListener('click', (e) => {
      const trigger = e.target.closest && e.target.closest('[data-hotspot-live]');
      if (trigger) {
        e.preventDefault(); e.stopPropagation();
        const auditSave = document.getElementById('savePanoEditor');   // editor Audit punya perubahan belum tersimpan → halaman dimuat ulang setelah simpan di sini, perubahan itu hilang
        if (auditSave && !auditSave.disabled && !window.confirm('Editor pano.xml di halaman ini punya perubahan yang belum disimpan, dan akan hilang bila editor langsung menyimpan (halaman dimuat ulang). Tetap buka editor langsung?')) return;
        open(trigger.dataset.hotspotLive); return;
      }
      const b = e.target.closest && e.target.closest('.hl-modal .hl-item [data-act]'); if (b) { const id = b.closest('.hl-item').dataset.id; act(b.dataset.act, id); return; }
      const card = e.target.closest && e.target.closest('.hl-modal .hl-item');
      if (card && !e.target.closest('input,select,button,.pano-thumb-select,[class*="thumb-select"]')) { highlight(card.dataset.id); try { win.__HSLIVE.select(card.dataset.id); } catch (err) { /* abaikan */ } }
    }, true);
  }
  function act(kind, id) {
    const it = items.get(id); if (!it || !win) return;
    if (kind === 'focus') { highlight(id); win.__HSLIVE.select(id); win.__HSLIVE.focus(id); return; }
    if (kind === 'delete') {
      if (it.isNew) { win.__HSLIVE.removePreview(id); items.delete(id); order = order.filter((x) => x !== id); renderList(); }
      else { it.deleted = !it.deleted; win.__HSLIVE.setHidden(id, it.deleted); if (it.deleted && selected === id) { selected = null; win.__HSLIVE.select(null); } const c = cardOf(id); if (c) c.replaceWith(makeCard(it)), enhanceCard(id); }
      refreshDirty();
    }
  }
  function enhanceCard(id) { const c = cardOf(id); const se = c && c.querySelector('select[data-field="url"]'); if (se && window.enhancePanoThumbSelect) window.enhancePanoThumbSelect(se); }

  function addHotspot() {
    if (!win || busy) return;
    const skin = els.hlAddSkin.value, url = els.hlAddTarget.value, title = els.hlAddTitle.value.trim();
    if (!title) { toast('Isi judul hotspot dulu.', 'warn'); els.hlAddTitle.focus(); return; }
    const c = win.__HSLIVE.viewCenter(), id = '__n' + (nextNew++);
    for (let k = 0; k < 12 && [...items.values()].some((x) => !x.deleted && Math.abs(x.cur.pan - c.pan) < 7 && Math.abs(x.cur.tilt - c.tilt) < 6); k++) c.pan += 9;   // jangan menumpuk di atas hotspot lain (mis. hotspot yang baru ditambahkan di titik yang sama)
    const sameHere = [...items.values()].find((x) => !x.isNew && x.skin === skin);   // contoh terbaik = hotspot sejenis di panorama ini; kalau belum ada, contoh dari panorama lain
    const tpl = sameHere ? { node, id: sameHere.id } : ((S.skins || []).find((s) => s.skin === skin) || {});
    const kind = /drone/i.test(skin) ? 'air' : 'land';
    items.set(id, { id, kind, skin, isNew: true, deleted: false, tpl, orig: { title: '', url: '', pan: c.pan, tilt: c.tilt }, cur: { title, url, pan: c.pan, tilt: c.tilt } });
    order.push(id);
    win.__HSLIVE.addPreview(id, c.pan, c.tilt, title, skin, thumbFor(url));
    renderList(); highlight(id); win.__HSLIVE.select(id); refreshDirty();
    els.hlAddTitle.value = ''; addTitleTouched = false;
    const sameSkinHere = [...items.values()].some((x) => !x.isNew && x.skin === skin);
    toast(sameSkinHere ? 'Hotspot ditambahkan di tengah pandangan. Tarik untuk memindahkannya, lalu Simpan.' : 'Hotspot ditambahkan. Panorama ini belum punya hotspot sejenis, jadi tampilan sementara; HUD asli muncul setelah Simpan.', 'info');
  }

  function revertAll() {
    if (!win || busy) return;
    for (const it of [...items.values()]) {
      if (it.isNew) { try { win.__HSLIVE.removePreview(it.id); } catch (e) { /* abaikan */ } items.delete(it.id); order = order.filter((x) => x !== it.id); continue; }
      it.cur = { ...it.orig }; it.deleted = false;
      win.__HSLIVE.setPos(it.id, it.orig.pan, it.orig.tilt); win.__HSLIVE.setTitle(it.id, it.orig.title); win.__HSLIVE.setHidden(it.id, false); win.__HSLIVE.setThumb(it.id, thumbFor(it.orig.url));
    }
    selected = null; try { win.__HSLIVE.select(null); } catch (e) { /* abaikan */ }
    renderList(); refreshDirty(); toast('Perubahan dibatalkan.', 'info');
  }

  /* ---------- simpan ---------- */
  async function save() {
    if (busy) return;
    const update = [], del = [], add = [];
    for (const it of items.values()) {
      if (it.isNew) {
        if (it.deleted) continue;
        if (!it.cur.title.trim()) { toast('Hotspot baru belum diberi judul.', 'warn'); highlight(it.id); return; }
        add.push({ title: it.cur.title.trim(), url: it.cur.url, pan: it.cur.pan, tilt: it.cur.tilt, skinid: it.skin, copyFromNode: it.tpl && it.tpl.node, copyFromId: it.tpl && it.tpl.id });
      } else if (it.deleted) del.push(it.id);
      else {
        const f = {};
        if (it.cur.title !== it.orig.title) f.title = it.cur.title.trim();
        if (it.cur.url !== it.orig.url) f.url = it.cur.url;
        if (Math.abs(it.cur.pan - it.orig.pan) > 0.0005 || Math.abs(it.cur.tilt - it.orig.tilt) > 0.0005) { f.pan = it.cur.pan; f.tilt = it.cur.tilt; }
        if (Object.keys(f).length) update.push({ id: it.id, ...f });
      }
    }
    if (!update.length && !del.length && !add.length) return;
    setBusy(true); setLoading('Menyimpan…');
    try {
      const r = await fetch('hotspot-editor-save.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf: S.csrf, id_lokasi: S.location, nodeId: node, update, delete: del, add }) });
      let d; try { d = await r.json(); } catch (e) { throw new Error('Respons server tidak valid.'); }
      if (!d.success) throw new Error(d.message || 'Gagal menyimpan.');
      savedOnce = true; toast(d.message);
      await reload();   // muat ulang tur dengan pano.xml baru: hotspot tampil dengan HUD asli sebagai verifikasi
    } catch (e) { setLoading(''); toast('Gagal menyimpan: ' + e.message, 'error'); setBusy(false); }
  }

  function close() {
    if (!modal || !modal.classList.contains('open')) return;
    if (dirtyCount() > 0 && !window.confirm('Ada perubahan yang belum disimpan. Tutup dan buang perubahan itu?')) return;
    seq++; try { win && win.__HSLIVE && win.__HSLIVE.stop(); } catch (e) { /* abaikan */ }
    els.hlFrame.src = 'about:blank'; win = null;
    modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('hl-open');
    if (savedOnce) { savedOnce = false; window.location.reload(); }   // kartu Audit dirender server: muat ulang agar memuat hotspot terbaru
  }
})();
