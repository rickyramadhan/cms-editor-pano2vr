/* hotspot-live-agent.js — agen editor hotspot langsung.
 *
 * Dijalankan DI DALAM tur sungguhan (iframe satu-origin) oleh assets/js/hotspot-live-editor.js, SETELAH assets/js/hotspot-calibration-engine.js
 * (memakai prepare(), visibleRects(), blockNav() darinya). Tur ditampilkan apa adanya dengan HUD asli Smart Kit; agen hanya menambahkan:
 *   - memilih hotspot dengan klik (klik TIDAK memindahkan ke panorama tujuan), dan menggeser hotspot dengan menarik (posisi diubah langsung di tur lewat pano.updateHotspot);
 *   - kotak biru yang menandai hotspot terpilih (ikut bergerak saat panorama digeser);
 *   - pratinjau hotspot BARU dengan menyalin tampilan HUD asli sebuah hotspot sejenis di panorama itu (pano.addHotspot), dan mengganti judul/foto salinannya;
 *   - menyembunyikan hotspot yang ditandai hapus, dan memperbarui label judul secara langsung.
 * Tak ada yang ditulis ke berkas di sini: semua perubahan dikirim ke CMS lewat callback dan baru disimpan setelah pengguna menekan Simpan.
 *
 * API: window.__HSLIVE.start({ select(id), move(id, pan, tilt), drop(id, pan, tilt), sceneLost() }), openScene(id), list(), select(id), focus(id), setPos(id, pan, tilt),
 *      setTitle(id, text), setHidden(id, on), addPreview(tempId, pan, tilt, title, skin, thumb), removePreview(tempId), viewCenter(), stop()
 */
(function () {
  'use strict';
  if (window.__HSLIVE && window.__HSLIVE.version >= 1) return;
  const E = window.__HSCAL;
  if (!E) throw new Error('hotspot-live-agent.js membutuhkan hotspot-calibration-engine.js.');
  const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
  const frames = (n) => new Promise((r) => { let k = n; const f = () => { if (--k <= 0) r(); else requestAnimationFrame(f); }; requestAnimationFrame(f); });
  const P = () => window.pano;
  const norm = (p) => ((p + 180) % 360 + 360) % 360 - 180;
  const clamp = (v, a, b) => Math.max(a, Math.min(b, v));

  let cb = {}, scene = '', selected = null, drag = null, selRaf = 0, watch = 0, started = false;
  const previews = new Set();   // id hotspot pratinjau (belum ada di pano.xml)

  const ids = () => P().getPointHotspotIds();
  const divOf = (id) => { try { return P().getHotspot(id)?.div || null; } catch (e) { return null; } };
  const idFromPath = (path) => { for (const id of ids()) { const d = divOf(id); if (d && path.includes(d)) return id; } return null; };

  /* label judul pada HUD: Badung (.bl/.tt di .bdg-hs), Jakarta (.jk-hs__name/.jk-lh__title), dan elemen teks bawaan skin Pano2VR */
  function setLabel(root, text) {
    if (!root) return;
    root.querySelectorAll('.bl, .tt, .jk-hs__name, .jk-lh__title').forEach((n) => { n.textContent = text; });
    root.querySelectorAll('.ggskin_text').forEach((t) => { const d = t.ggTextDiv || t.firstElementChild; if (d) d.textContent = text; });
  }
  function setThumb(root, thumb) {
    if (!root || !thumb) return;
    root.querySelectorAll('.bi').forEach((n) => { n.style.backgroundImage = 'url("' + thumb + '")'; });
    root.querySelectorAll('.jk-hs__thumb img, .jk-lh__thumb img').forEach((n) => n.setAttribute('src', thumb));
  }

  function kindOf(div, skin) {
    const tag = div && div.querySelector(':scope > .bdg-hs');
    if (tag) return tag.classList.contains('t-air') ? 'air' : (tag.classList.contains('t-dg') ? 'dg' : 'land');
    return /drone/i.test(skin || '') ? 'air' : 'land';
  }

  function list() {
    const out = [];
    for (const id of ids()) {
      if (previews.has(id)) continue;
      const h = P().getHotspot(id); if (!h) continue;
      out.push({ id, title: String(h.title || ''), url: String(h.url || ''), target: String(h.target || ''), skin: String(h.skinid || ''), pan: Number(h.pan), tilt: Number(h.tilt), kind: kindOf(h.div, h.skinid) });
    }
    return out;
  }

  /* ---------- kotak pemilihan (ikut bergerak) ---------- */
  function layer() {
    let l = document.getElementById('__hslive_sel');
    if (!l) {
      l = document.createElement('div'); l.id = '__hslive_sel'; l.style.cssText = 'position:fixed;left:0;top:0;right:0;bottom:0;z-index:2147483000;pointer-events:none';
      const b = document.createElement('div'); b.style.cssText = 'position:absolute;box-sizing:border-box;display:none;border:2.5px solid #1a5cff;border-radius:12px;box-shadow:0 0 0 3px rgba(26,92,255,.28)';
      l.append(b); document.body.append(l);
    }
    return l;
  }
  function selLoop() {
    const box = layer().firstElementChild;
    const d = selected ? divOf(selected) : null;
    let u = null;
    if (d) { try { const rs = E._visibleRects(d); if (rs.length) u = rs.reduce((a, r) => ({ l: Math.min(a.l, r.l), t: Math.min(a.t, r.t), r: Math.max(a.r, r.r), b: Math.max(a.b, r.b) })); } catch (e) { /* hotspot sedang dibangun ulang */ } }
    if (!u) box.style.display = 'none';
    else { box.style.display = 'block'; box.style.left = (u.l - 6) + 'px'; box.style.top = (u.t - 6) + 'px'; box.style.width = (u.r - u.l + 12) + 'px'; box.style.height = (u.b - u.t + 12) + 'px'; }
    selRaf = requestAnimationFrame(selLoop);
  }
  function select(id, silent) {
    selected = id || null;
    if (!silent && cb.select) cb.select(selected);
  }

  /* ---------- klik = pilih, tarik = geser ---------- */
  const container = () => document.getElementById('container') || document.body;
  function angles(x, y) { const r = container().getBoundingClientRect(); return P().getPositionAngles(x - r.left, y - r.top); }
  function onDown(e) {
    if (!started || (e.button != null && e.button !== 0)) return;
    const id = idFromPath(e.composedPath ? e.composedPath() : []); if (!id) return;
    e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation();
    select(id);
    if (e.type !== 'pointerdown') return;   // mousedown/touchstart susulan hanya ditahan agar panorama tidak ikut bergeser
    const h = P().getHotspot(id), first = angles(e.clientX, e.clientY);
    drag = { id, x: e.clientX, y: e.clientY, pan: Number(h.pan), tilt: Number(h.tilt), first, tiltOffset: Number(h.tilt) - Number(first.tilt), moved: false, cur: null };
    try { P().stopAutorotate && P().stopAutorotate(); P().setLocked(true); } catch (err) { /* lanjut */ }
    document.body.classList.add('__hslive_drag');
  }
  function onMove(e) {
    if (!drag) return;
    if (!drag.moved && Math.hypot(e.clientX - drag.x, e.clientY - drag.y) < 4) return;   // ambang kecil: ketukan biasa tidak menggeser
    drag.moved = true; e.preventDefault(); e.stopPropagation();
    const a = angles(e.clientX, e.clientY);
    const pan = drag.pan - norm(Number(a.pan) - Number(drag.first.pan)), tilt = clamp(Number(a.tilt) + drag.tiltOffset, -90, 90);
    drag.cur = { pan, tilt };
    P().updateHotspot(drag.id, pan, tilt);
    if (cb.move) cb.move(drag.id, pan, tilt);
  }
  function onUp() {
    if (!drag) return;
    const d = drag; drag = null;
    try { P().setLocked(false); } catch (err) { /* lanjut */ }
    document.body.classList.remove('__hslive_drag');
    if (d.moved && d.cur && cb.drop) cb.drop(d.id, d.cur.pan, d.cur.tilt);
  }
  function swallow(e) {   // klik/ketuk lanjutan pada hotspot: jangan pindah ke panorama tujuan
    if (!started) return;
    if (idFromPath(e.composedPath ? e.composedPath() : [])) { e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation(); }
  }
  const OPT = { capture: true, passive: false };
  const bound = [['pointerdown', onDown], ['mousedown', onDown], ['touchstart', onDown], ['pointermove', onMove], ['pointerup', onUp], ['pointercancel', onUp], ['click', swallow], ['dblclick', swallow], ['auxclick', swallow], ['contextmenu', swallow]];

  /* HUD bawaan (header, cuaca, dock, minimap, chat, kartu sambutan) disembunyikan: yang dibutuhkan editor hanya panorama + hotspot-nya.
     Hotspot (darat/drone/Objek Info/lokasi terdekat) ada di #container, bukan di anak <body> lain, jadi aman. */
  const KEEP = (e) => e.id === 'container' || /^(SCRIPT|LINK|STYLE|NOSCRIPT|AUDIO|VIDEO|TEMPLATE|META)$/.test(e.tagName) || /^__(hcal|hslive)/.test(e.id);
  let chromeObs = null;
  function hideChrome() { for (const c of document.body.children) if (!KEEP(c)) c.setAttribute('data-hslive-chrome', '1'); }

  function start(callbacks) {
    cb = callbacks || {};
    if (!started) {
      started = true;
      const css = document.createElement('style'); css.id = '__hslive_css';
      css.textContent = '.ggskin_hotspot, .ggskin_hotspot * { cursor: grab !important; } body.__hslive_drag, body.__hslive_drag * { cursor: grabbing !important; } [data-hslive-hidden="1"], [data-hslive-chrome="1"] { display: none !important; }';
      document.head.append(css);
      hideChrome(); chromeObs = new MutationObserver(hideChrome); chromeObs.observe(document.body, { childList: true });
      for (const [ev, fn] of bound) window.addEventListener(ev, fn, OPT);
      E._blockNav();
    }
    cancelAnimationFrame(selRaf); selRaf = requestAnimationFrame(selLoop);
    clearInterval(watch);
    watch = setInterval(() => {   // pindah panorama lewat jalan lain (menu, tautan): kembalikan ke panorama yang sedang disunting
      try { if (scene && window.SmartKit.scenes.cur() !== scene) { window.SmartKit.scenes.go(scene); setTimeout(() => cb.sceneLost && cb.sceneLost(), 2600); } } catch (e) { /* abaikan */ }
    }, 900);
  }
  function stop() {
    started = false; clearInterval(watch); cancelAnimationFrame(selRaf);
    if (chromeObs) { chromeObs.disconnect(); chromeObs = null; }
    for (const [ev, fn] of bound) window.removeEventListener(ev, fn, OPT);
    const l = document.getElementById('__hslive_sel'); if (l) l.remove();
  }

  async function openScene(id) {
    scene = id; selected = null; previews.clear();
    window.SmartKit.scenes.go(id); await sleep(2600);
    for (let i = 0; i < 10 && window.SmartKit.scenes.cur() !== id; i++) await sleep(400);
    try { if (P().stopAutorotate) P().stopAutorotate(); } catch (e) { /* lanjut */ }
    return list();
  }

  async function focus(id) {
    const h = P().getHotspot(id); if (!h) return;
    P().setPan(Number(h.pan)); P().setTilt(Number(h.tilt)); await frames(3);
  }
  function setPos(id, pan, tilt) { P().updateHotspot(id, pan, tilt); }
  function setTitle(id, text) { setLabel(divOf(id), text); }
  function setHidden(id, on) { const d = divOf(id); if (d) d.dataset.hsliveHidden = on ? '1' : ''; }

  /* pratinjau hotspot baru: salinan tampilan HUD asli sebuah hotspot sejenis di panorama ini (jenis = skin) */
  function addPreview(tempId, pan, tilt, title, skin, thumb) {
    let tpl = null;
    for (const id of ids()) { if (previews.has(id)) continue; const h = P().getHotspot(id); if (h && h.div && String(h.skinid || '') === skin) { tpl = h.div; break; } }
    if (!tpl) for (const id of ids()) { if (previews.has(id)) continue; const h = P().getHotspot(id); if (h && h.div) { tpl = h.div; break; } }
    let node;
    if (tpl) {
      node = tpl.cloneNode(true); node.removeAttribute('id'); node.querySelectorAll('[id]').forEach((n) => n.removeAttribute('id')); node.dataset.hsliveHidden = '';
      // gaya posisi akar hotspot contoh ikut tersalin (mis. left:-1000px; visibility:hidden saat contohnya sedang di luar layar): atur ulang agar tampil tepat di titik hotspot baru
      node.style.cssText = 'position:absolute;left:0;top:0;width:1px;height:1px;visibility:visible;z-index:1;pointer-events:auto';
      setLabel(node, title); setThumb(node, thumb);
    }
    else { node = document.createElement('div'); node.style.cssText = 'width:44px;height:44px;margin:-22px 0 0 -22px;border-radius:50%;background:#1a5cff;border:3px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,.4)'; node.title = title; }
    previews.add(tempId);
    P().addHotspot(tempId, pan, tilt, node);
    return !!tpl;
  }
  function removePreview(tempId) { previews.delete(tempId); try { P().removeHotspot(tempId); } catch (e) { /* sudah hilang */ } if (selected === tempId) selected = null; }
  function viewCenter() { return { pan: Number(P().getPan()), tilt: Number(P().getTilt()) }; }

  window.__HSLIVE = { version: 1, start, stop, openScene, list, select: (id) => select(id, true), focus, setPos, setTitle, setHidden, setThumb: (id, thumb) => setThumb(divOf(id), thumb), addPreview, removePreview, viewCenter };
})();
