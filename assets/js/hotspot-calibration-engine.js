/* hotspot-calibration-engine.js — mesin Kalibrasi Hotspot.
 *
 * Dijalankan DI DALAM jendela tur sungguhan (iframe satu-origin yang dibuka tab CMS "Kalibrasi Hotspot"), bukan di halaman CMS,
 * karena ukuran bingkai/label tiap hotspot hanya benar bila dihitung dari skin + Smart Kit yang sebenarnya.
 *
 * Yang diperiksa (lima jenis UI hotspot di panorama):
 *   land = hotspot darat (darat -> darat)           oi   = Objek Info (WebElement pano.xml)
 *   air  = hotspot menuju/antar panorama drone       dg   = drone ke darat
 *   near = hotspot lokasi terdekat (panorama drone, data CMS › URL Lokasi)
 * Cara kerja per panorama: kamera diarahkan ke tiap hotspot pada tiga tingkat zoom, kotak-kotak tampilan (bingkai, judul, titik, foto)
 * semua hotspot yang terlihat diukur, lalu pasangan yang saling menimpa dicatat. Untuk tiap pasangan, hotspot yang LEBIH LENTUR digeser ke
 * posisi terdekat yang bebas tabrak (diuji langsung di tur: pano.updateHotspot / elemen web / tautan lokasi). Hotspot darat paling kokoh
 * (menandai tempat nyata di foto), lokasi terdekat paling lentur; tiap jenis punya batas geser sendiri. Tak ada yang ditulis ke berkas di sini:
 * hasilnya (daftar perpindahan) dikembalikan ke CMS yang menampilkan pratinjau lalu menyimpan setelah disetujui.
 *
 * API: window.__HSCAL.run({ solve, scenes, onProgress, isCancelled }) -> { scenes, moves, failed, stats }
 *      window.__HSCAL.reset()  -> kembalikan semua posisi hotspot di tur ke posisi awal (sebelum digeser mesin ini)
 *      window.__HSCAL.show(sceneId, moves, 'before'|'after') -> buka panorama itu di tur dengan posisi awal / hasil kalibrasi (pratinjau)
 */
(function () {
  'use strict';
  if (window.__HSCAL && window.__HSCAL.version >= 1) return;

  const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
  const frames = (n) => new Promise((r) => { let k = n; const f = () => { if (--k <= 0) r(); else requestAnimationFrame(f); }; requestAnimationFrame(f); });

  /* rank = makin tinggi makin kokoh (yang dikalahkan adalah rank terendah); pan/tilt = batas geser maksimum dari posisi asli (derajat) */
  const KINDS = {
    land: { label: 'Hotspot darat', rank: 5, pan: 14, tilt: 10 },
    oi: { label: 'Objek Info', rank: 4, pan: 18, tilt: 12 },
    dg: { label: 'Drone ke darat', rank: 3, pan: 25, tilt: 12 },
    air: { label: 'Drone', rank: 2, pan: 45, tilt: 14 },
    near: { label: 'Lokasi terdekat', rank: 1, pan: 70, tilt: 16 },
  };
  const MARGIN = 8;        // celah minimal antar kotak (px)
  const MIN_AREA = 12;     // tumpang tindih yang lebih kecil dari ini (px persegi) diabaikan
  const VF = [0.8, 1, 1.2]; // tingkat zoom diuji relatif terhadap sudut pandang awal panorama
  const originals = new Map();   // key item -> { move, pan, tilt }: untuk reset()

  const vis = (e) => { try { return e.checkVisibility({ checkOpacity: true, checkVisibilityCSS: true, opacityProperty: true, visibilityProperty: true }); } catch (x) { return true; } };
  const rect = (e) => { const r = e.getBoundingClientRect(); return { l: r.left, t: r.top, r: r.right, b: r.bottom }; };
  const overlap = (a, b, m) => {
    const w = Math.min(a.r + m, b.r) - Math.max(a.l - m, b.l), h = Math.min(a.b + m, b.b) - Math.max(a.t - m, b.t);
    return w > 0 && h > 0 ? w * h : 0;
  };
  const onScreen = (r) => r.r > 0 && r.l < innerWidth && r.b > 0 && r.t < innerHeight;
  const normPan = (p) => { p = ((p + 180) % 360 + 360) % 360 - 180; return Math.round(p * 100) / 100; };
  const dirOf = (pan, tilt) => { const a = pan * Math.PI / 180, t = tilt * Math.PI / 180; return [-Math.sin(a) * Math.cos(t), Math.sin(t), Math.cos(a) * Math.cos(t)]; };
  const angSep = (p1, t1, p2, t2) => { const a = dirOf(p1, t1), b = dirOf(p2, t2); return Math.acos(Math.max(-1, Math.min(1, a[0] * b[0] + a[1] * b[1] + a[2] * b[2]))) * 180 / Math.PI; };

  /* JEJAK VISUAL, bukan kotak elemen. Hotspot di skin Pano2VR sering memakai elemen jauh lebih besar dari yang terlihat: gambar PNG 223x223 yang sebagian besar transparan, kotak teks
     selebar 300px untuk label pendek, pembungkus tanpa isi. Mengukur kotak elemen membuat "tabrakan" antar area transparan yang tidak pernah terlihat pengunjung. Karena itu yang diukur
     hanya yang benar-benar tergambar di layar (sama dengan HUD Smart Kit yang tampil): teks (kotak per baris, sepanjang hurufnya), gambar (kotak piksel tidak transparan yang padat,
     garis/titik tipis diabaikan), dan elemen berlatar / berbingkai yang terlihat. */
  const alphaOf = (c) => { if (!c || c === 'transparent') return 0; const m = String(c).match(/rgba?\(([^)]+)\)/); if (!m) return 1; const p = m[1].split(/[,\s/]+/).filter(Boolean); return p.length > 3 ? Math.max(0, Math.min(1, parseFloat(p[3]))) : 1; };
  const solid = (r) => ({ l: r.l, t: r.t, r: r.r, b: r.b });
  const alphaCache = new Map();
  /* kotak piksel tidak transparan yang PADAT (0..1): baris/kolom yang kurang dari 6% lebar/tinggi diabaikan, jadi garis tipis dan titik kecil di dalam gambar tidak melebarkan kotak */
  function massBox(img) {
    const src = img.currentSrc || img.src; if (!src) return null;
    if (alphaCache.has(src)) return alphaCache.get(src);
    const nw = img.naturalWidth, nh = img.naturalHeight; if (!nw || !nh) return null;   // belum dimuat: jangan di-cache
    let box = null;
    try {
      const k = Math.min(1, 160 / Math.max(nw, nh)), cw = Math.max(2, Math.round(nw * k)), ch = Math.max(2, Math.round(nh * k));
      const c = document.createElement('canvas'); c.width = cw; c.height = ch;
      const ctx = c.getContext('2d', { willReadFrequently: true }); ctx.drawImage(img, 0, 0, cw, ch);
      const d = ctx.getImageData(0, 0, cw, ch).data, rows = new Array(ch).fill(0);
      for (let y = 0; y < ch; y++) { let n = 0; for (let x = 0; x < cw; x++) if (d[(y * cw + x) * 4 + 3] > 40) n++; rows[y] = n; }
      const rt = Math.max(3, 0.06 * cw); let t = -1, bt = -1;
      for (let y = 0; y < ch; y++) if (rows[y] >= rt) { if (t < 0) t = y; bt = y; }
      if (t < 0) box = { empty: true };
      else {
        const hh = bt - t + 1, ct = Math.max(2, 0.06 * hh); let l = -1, r = -1;
        for (let x = 0; x < cw; x++) { let n = 0; for (let y = t; y <= bt; y++) if (d[(y * cw + x) * 4 + 3] > 40) n++; if (n >= ct) { if (l < 0) l = x; r = x; } }
        box = l < 0 ? { empty: true } : { l: l / cw, t: t / ch, r: (r + 1) / cw, b: (bt + 1) / ch };
      }
    } catch (e) { box = null; }   // kanvas tercemar (gambar lintas-origin): pakai kotak elemennya
    alphaCache.set(src, box); return box;
  }
  function imageRect(img, r, cs) {
    const box = massBox(img);
    if (box && box.empty) return null;
    if (!box) return solid(r);
    const nw = img.naturalWidth, nh = img.naturalHeight, iw0 = r.r - r.l, ih0 = r.b - r.t; let x = r.l, y = r.t, w = iw0, h = ih0;
    const fit = cs.objectFit;
    if (fit === 'contain' || fit === 'cover' || fit === 'scale-down') { const sc = fit === 'cover' ? Math.max(iw0 / nw, ih0 / nh) : Math.min(iw0 / nw, ih0 / nh); w = nw * sc; h = nh * sc; x += (iw0 - w) / 2; y += (ih0 - h) / 2; }
    const m = { l: x + box.l * w, t: y + box.t * h, r: x + box.r * w, b: y + box.b * h };
    return { l: Math.max(m.l, r.l), t: Math.max(m.t, r.t), r: Math.min(m.r, r.r), b: Math.min(m.b, r.b) };
  }
  /* jejak visual semua elemen di bawah root (termasuk root): daftar kotak */
  function visibleRects(root) {
    const out = [];
    if (!root) return out;
    for (const e of [root, ...root.querySelectorAll('*')]) {
      if (!vis(e)) continue;
      const cs = getComputedStyle(e), r = rect(e), w = r.r - r.l, h = r.b - r.t;
      // teks: kotak tiap baris sepanjang hurufnya (bukan kotak pembungkus yang melebar)
      if (parseFloat(cs.fontSize) >= 2 && alphaOf(cs.color) > 0.15 && alphaOf(cs.webkitTextFillColor) > 0.15) {
        for (const n of e.childNodes) if (n.nodeType === 3 && n.nodeValue.trim()) {
          const rg = document.createRange(); rg.selectNodeContents(n);
          for (const q of rg.getClientRects()) if (q.width >= 3 && q.height >= 6) out.push({ l: q.left, t: q.top, r: q.right, b: q.bottom });
        }
      }
      if (w < 6 || h < 6 || w > innerWidth * 0.8) continue;   // garis tipis, titik kecil, dan pembungkus selebar layar tidak dihitung
      if (e.tagName === 'IMG') { const q = imageRect(e, r, cs); if (q && q.r - q.l >= 4 && q.b - q.t >= 4) out.push(q); continue; }
      if (e.tagName === 'CANVAS' || e.tagName === 'VIDEO') { out.push(solid(r)); continue; }
      if (e instanceof SVGElement) {   // titik/lingkaran dan persegi berisi warna; garis (path/line) tidak dihitung
        if (/^(circle|ellipse|rect)$/i.test(e.tagName) && cs.fill !== 'none' && alphaOf(cs.fill) > 0.15 && parseFloat(cs.fillOpacity) > 0.15) out.push(solid(r));
        continue;
      }
      const hasBg = alphaOf(cs.backgroundColor) > 0.15 || (cs.backgroundImage && cs.backgroundImage !== 'none');
      const bw = parseFloat(cs.borderTopWidth) + parseFloat(cs.borderBottomWidth) + parseFloat(cs.borderLeftWidth) + parseFloat(cs.borderRightWidth);
      const hasBorder = bw > 0 && cs.borderTopStyle !== 'none' && (alphaOf(cs.borderTopColor) > 0.15 || alphaOf(cs.borderLeftColor) > 0.15);
      if (hasBg || hasBorder) out.push(solid(r));
    }
    return out;
  }
  const hotspotRects = (div) => visibleRects(div);
  const oiWrapper = (id) => { const c = document.getElementById(id); return c ? c.closest('.hotspot-wrapper') : null; };
  function oiRects(id) {
    const w = oiWrapper(id), out = [];
    if (!w) return out;
    for (const sel of ['.oi-plate', '.tour-svg-dot']) { const e = w.querySelector(sel); if (e) out.push(...visibleRects(e)); }
    return out;
  }
  function nearRects(name) {
    const out = [];
    for (const a of document.querySelectorAll('#bdg-locs .bdg-loc')) {
      if ((a.querySelector('.bdg-loc__t b')?.textContent || '').trim() !== name) continue;
      out.push(...visibleRects(a));
    }
    return out;
  }

  /* semua hotspot yang punya tampilan di panorama saat ini */
  function collect() {
    const items = [];
    const P = window.pano;
    for (const id of P.getPointHotspotIds()) {
      const h = P.getHotspot(id); if (!h || !h.div) continue;
      const tag = h.div.querySelector(':scope > .bdg-hs');
      const kind = tag ? (tag.classList.contains('t-air') ? 'air' : (tag.classList.contains('t-dg') ? 'dg' : 'land')) : 'land';
      const it = { key: 'h:' + id, kind, id, source: 'hotspot', label: String(h.title || (tag?.querySelector('.bl')?.textContent) || id).trim(), pan: Number(h.pan), tilt: Number(h.tilt) };
      it.move = (p, t) => { P.updateHotspot(id, p, t); it.pan = p; it.tilt = t; };
      it.rects = () => hotspotRects(P.getHotspot(id)?.div);
      items.push(it);
    }
    for (const m of (P.Rb || [])) {   // WebElement (Objek Info) panorama ini; pin tanpa data aktif tidak punya tampilan -> dilewati
      if (!m || !m.id || typeof m.pan !== 'number' || !oiWrapper(m.id)) continue;
      const it = { key: 'o:' + m.id, kind: 'oi', id: String(m.id), source: 'webelement', label: (oiWrapper(m.id).querySelector('.oi-plate')?.textContent || '').trim() || String(m.id), pan: m.pan, tilt: m.u };
      it.move = (p, t) => { m.pan = p; m.u = t; P.Ma = true; it.pan = p; it.tilt = t; };
      it.rects = () => oiRects(m.id);
      items.push(it);
    }
    const db = window.LOKASI_DATABASE, slug = (String(window.SMART_PACK_URL || '').split('/packs/')[1] || '').split('/')[1] || '';
    const gd = window.GLOBAL_DATABASE && window.GLOBAL_DATABASE[slug];
    if (db && gd && String(gd.drone || '') === String(window.SmartKit.scenes.cur()) && window.__bdgLocs) {
      for (const lk of (db.links?.[slug] || [])) {
        const loc = db.locations?.[lk.to]; if (!loc || lk.hidden || !Number.isFinite(+lk.pan) || !Number.isFinite(+lk.tilt) || lk.pan === null || lk.tilt === null) continue;
        const it = { key: 'n:' + lk.to, kind: 'near', id: lk.to, source: 'lokasi', sourceSlug: slug, label: loc.name, pan: +lk.pan, tilt: +lk.tilt };
        it.move = (p, t) => { lk.pan = normPan(p); lk.tilt = t; window.__bdgLocs.rebuild(); it.pan = lk.pan; it.tilt = t; };
        it.rects = () => nearRects(loc.name);
        items.push(it);
      }
    }
    for (const it of items) { if (!originals.has(it.key + '@' + window.SmartKit.scenes.cur())) originals.set(it.key + '@' + window.SmartKit.scenes.cur(), { move: it.move, pan: it.pan, tilt: it.tilt, scene: window.SmartKit.scenes.cur() }); }
    return items;   // tanpa menyaring tampilan: hotspot di luar pandangan awal kotaknya tersembunyi; yang tak punya tampilan terlewati sendiri saat kamera diarahkan ke sana
  }

  async function look(p, t, vf) { const P = window.pano; P.setPan(p); P.setTilt(t); P.setVFov(vf); await frames(4); }

  /* pasangan yang saling menimpa di panorama ini (diukur dengan kamera diarahkan ke tiap hotspot pada tiga zoom) */
  async function detect(items, vf0) {
    const pairs = new Map();
    for (const a of items) {
      const near = items.filter((b) => b !== a && angSep(a.pan, a.tilt, b.pan, b.tilt) < 75);
      if (!near.length) continue;
      for (const k of VF) {
        await look(a.pan, a.tilt, vf0 * k);
        const ra = a.rects(); if (!ra.length) continue;
        for (const b of near) {
          const rb = b.rects(); let area = 0;
          for (const x of ra) for (const y of rb) { const o = overlap(x, y, MARGIN); if (o > MIN_AREA && (onScreen(x) || onScreen(y))) area += o; }
          if (area > 0) { const key = [a.key, b.key].sort().join('||'); const prev = pairs.get(key); if (!prev || area > prev.area) pairs.set(key, { a: a.key < b.key ? a : b, b: a.key < b.key ? b : a, area: Math.round(area), vf: Math.round(vf0 * k) }); }
        }
      }
    }
    return [...pairs.values()];
  }

  /* apakah hotspot m (di posisi sekarang) bebas tabrak dari semua yang lain pada tiga zoom */
  async function clean(m, items, vf0) {
    const others = items.filter((b) => b !== m && angSep(m.pan, m.tilt, b.pan, b.tilt) < 75);
    for (const k of VF) {
      await look(m.pan, m.tilt, vf0 * k);
      const ra = m.rects(); if (!ra.length) return false;
      for (const b of others) for (const x of ra) for (const y of b.rects()) if (overlap(x, y, MARGIN) > MIN_AREA && (onScreen(x) || onScreen(y))) return false;
    }
    return true;
  }

  function candidates(m) {
    const lim = KINDS[m.kind], o = originals.get(m.key + '@' + window.SmartKit.scenes.cur()), out = [];
    const stepP = m.kind === 'near' ? 3 : 2.5, stepT = 2;
    for (let dp = -lim.pan; dp <= lim.pan + 1e-6; dp += stepP) for (let dt = -lim.tilt; dt <= lim.tilt + 1e-6; dt += stepT) {
      if (!dp && !dt) continue;
      const tilt = o.tilt + dt; if (tilt < -85 || tilt > 85 || (m.kind === 'near' && (tilt < -25 || tilt > 45))) continue;
      out.push({ pan: Math.round((o.pan + dp) * 100) / 100, tilt: Math.round(tilt * 100) / 100, d: Math.hypot(dp, dt * 1.6) });
    }
    return out.sort((a, b) => a.d - b.d);
  }

  /* beresin satu panorama: kembalikan { moves, failed, before, after } */
  async function solveScene(items, vf0, report) {
    const o = (m) => originals.get(m.key + '@' + window.SmartKit.scenes.cur());
    const before = await detect(items, vf0), moves = new Map(), failed = [];
    let pairs = before;
    for (let pass = 0; pass < 6 && pairs.length; pass++) {
      const count = new Map();   // berapa tabrakan tiap hotspot (yang paling banyak ditangani dulu)
      for (const p of pairs) for (const k of [p.a, p.b]) count.set(k, (count.get(k) || 0) + 1);
      const todo = new Set();
      for (const p of pairs) {
        const [x, y] = [p.a, p.b].sort((u, v) => KINDS[u.kind].rank - KINDS[v.kind].rank || (count.get(v) - count.get(u)));   // yang rank-nya lebih rendah (atau lebih sering menabrak) digeser
        todo.add(x);
      }
      let progressed = false;
      const attempt = async (m) => {
        if (moves.has(m.key)) return true;
        let found = null, tried = 0;
        for (const c of candidates(m)) {
          if (++tried > 140) break;
          m.move(c.pan, c.tilt);
          if (await clean(m, items, vf0)) { found = c; break; }
        }
        if (!found) { m.move(o(m).pan, o(m).tilt); return false; }
        moves.set(m.key, { item: m, from: { pan: o(m).pan, tilt: o(m).tilt }, to: { pan: found.pan, tilt: found.tilt }, reason: pairs.filter((p) => p.a === m || p.b === m).map((p) => (p.a === m ? p.b : p.a).label) });
        return true;
      };
      const retry = new Set();
      for (const m of [...todo].sort((u, v) => KINDS[u.kind].rank - KINDS[v.kind].rank)) {
        if (await attempt(m)) progressed = true;
        else for (const p of pairs) if (p.a === m || p.b === m) retry.add(p.a === m ? p.b : p.a);   // tak ada tempat untuk yang lentur: coba pasangannya
      }
      for (const m of retry) if (!todo.has(m) && await attempt(m)) progressed = true;
      pairs = await detect(items, vf0);
      if (!progressed) break;
    }
    for (const p of pairs) failed.push({ a: p.a, b: p.b, area: p.area });
    return { moves: [...moves.values()], failed, before: before.length, after: pairs.length };
  }

  async function prepare() {
    for (let i = 0; i < 160 && !(window.pano && window.SmartKit && window.SmartKit.scenes && window.SmartKit.scenes.cur && window.SmartKit.scenes.cur()); i++) await sleep(250);
    if (!(window.pano && window.SmartKit?.scenes?.cur?.())) throw new Error('Tur belum siap dimuat.');
    try { await window.SmartKit.ready; } catch (e) { /* lanjut */ }
    for (let i = 0; i < 24 && window.LOKASI_DATABASE === undefined; i++) await sleep(250);   // data lokasi terdekat dimuat main-loader belakangan; tur tanpa data itu menunggu paling lama 6 dtk
    // intro skin tur (pilihan suara + teaser): Badung = window.TemplateSkin (?cms_preview kadang sudah dibuang skin Pano2VR dari alamat sebelum dibaca), Jakarta = window.JakartaOnboarding
    // (tanpa lompatan cms_preview sama sekali). Keduanya dilewati di sini dengan langkah yang sama seperti lompatan aslinya: tanpa suara, splash suara dianggap sudah dijawab, finish().
    const intro = window.TemplateSkin || window.JakartaOnboarding, P = window.pano;
    if (intro && intro.getState && intro.getState() !== 'ready') {
      try { if (P.mute) P.mute('_all'); window.isUserManuallyMuted = true; P.setVariableValue('sounds_splashscreen_accepted', true); P.setVariableValue('vis_sounds_splashscreen', false); } catch (e) { /* lanjut */ }
      for (let i = 0; i < 10 && intro.getState() !== 'ready'; i++) { try { intro.finish(); } catch (e) { /* lanjut */ } await sleep(600); }
      await sleep(700);   // tirai intro menutup dengan jeda (Jakarta: 520 ms)
    }
    for (let i = 0; i < 20 && P.getVariableValue && P.getVariableValue('vis_skin') !== true; i++) await sleep(250);   // UI skin tampil (bila tidak, lapisan lokasi terdekat tersembunyi)
    for (let i = 0; i < 12; i++) {   // tutup sambutan Smart Kit bila ada
      if (!document.querySelector('.ob-wel.is-on') && !document.documentElement.classList.contains('ob-hide')) break;
      document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true })); await sleep(500);
    }
  }

  async function run(opt) {
    opt = opt || {};
    const report = opt.onProgress || (() => {}), cancelled = opt.isCancelled || (() => false);
    await prepare(); clearMarks();
    const TE = window.TourEngine, rapat = TE && TE.OI_RAPAT;
    if (TE) TE.OI_RAPAT = false;   // ukur papan Objek Info apa adanya (bukan yang sudah diringkas jadi titik)
    document.querySelectorAll('.hotspot-wrapper.oi-dot').forEach((w) => w.classList.remove('oi-dot'));
    const all = window.SmartKit.scenes.list().filter((s) => !opt.scenes || opt.scenes.includes(s.id));
    const out = { scenes: [], moves: [], failed: [], stats: { scenes: all.length, items: 0, before: 0, after: 0, moved: 0, unresolved: 0 } };
    try {
      for (let i = 0; i < all.length; i++) {
        if (cancelled()) { out.cancelled = true; break; }
        const sc = all[i]; report(i, all.length, sc.name || sc.id);
        window.SmartKit.scenes.go(sc.id); await sleep(2400);
        if (window.SmartKit.scenes.cur() !== sc.id) { await sleep(1500); }
        let items; try { items = collect(); } catch (e) { items = []; }
        const vf0 = window.pano.getVFov();
        const rec = { id: sc.id, name: sc.name || sc.id, count: items.length, before: 0, after: 0, moves: [], failed: [] };
        if (items.length > 1) {
          if (opt.solve === false) { const p = await detect(items, vf0); rec.before = rec.after = p.length; rec.failed = p.map((q) => ({ a: q.a, b: q.b, area: q.area })); }
          else { const r = await solveScene(items, vf0, report); rec.before = r.before; rec.after = r.after; rec.moves = r.moves; rec.failed = r.failed; }
        }
        out.stats.items += items.length; out.stats.before += rec.before; out.stats.after += rec.after; out.stats.moved += rec.moves.length; out.stats.unresolved += rec.failed.length;
        out.scenes.push(rec);
      }
    } finally { if (TE) TE.OI_RAPAT = rapat; }
    const plain = (it) => ({ key: it.key, kind: it.kind, id: it.id, source: it.source, sourceSlug: it.sourceSlug || '', label: it.label });
    for (const sc of out.scenes) {
      sc.moves = sc.moves.map((m) => ({ ...plain(m.item), scene: sc.id, sceneName: sc.name, from: m.from, to: m.to, reason: m.reason }));
      sc.failed = sc.failed.map((f) => ({ a: plain(f.a), b: plain(f.b), area: f.area, scene: sc.id, sceneName: sc.name }));
      out.moves.push(...sc.moves); out.failed.push(...sc.failed);
    }
    report(all.length, all.length, 'selesai');
    return out;
  }

  /* kembalikan semua posisi yang digeser mesin ini ke posisi awal (hanya yang di panorama aktif yang bisa langsung dilihat; sisanya sesuai tur dimuat ulang) */
  function reset() {
    for (const o of originals.values()) { try { if (o.scene === window.SmartKit.scenes.cur()) o.move(o.pan, o.tilt); } catch (e) { /* abaikan */ } }
  }

  /* penanda pratinjau: kotak putus-putus di atas tur, satu lapisan tetap yang tidak menerima klik dan IKUT BERGERAK (dihitung ulang tiap frame dari kotak hotspotnya), jadi
     panorama boleh digeser / di-zoom. Merah = hotspot yang bertabrakan (Sebelum), hijau = hotspot yang digeser (Sesudah). Berhenti sendiri bila pindah panorama. */
  let markRaf = 0, markState = null;
  const unionRect = (rs) => rs.reduce((a, r) => (a ? { l: Math.min(a.l, r.l), t: Math.min(a.t, r.t), r: Math.max(a.r, r.r), b: Math.max(a.b, r.b) } : { l: r.l, t: r.t, r: r.r, b: r.b }), null);
  function clearMarks() { markState = null; cancelAnimationFrame(markRaf); const old = document.getElementById('__hcal_mark'); if (old) old.remove(); }
  function startMarks(targets) {   // targets: [{ item, color, glow }]
    clearMarks(); if (!targets.length) return;
    const layer = document.createElement('div'); layer.id = '__hcal_mark'; layer.style.cssText = 'position:fixed;left:0;top:0;right:0;bottom:0;z-index:2147483000;pointer-events:none';
    const boxes = targets.map((t) => { const d = document.createElement('div'); d.style.cssText = 'position:absolute;box-sizing:border-box;display:none;border:2.5px dashed ' + t.color + ';border-radius:12px;box-shadow:0 0 0 3px ' + t.glow; layer.append(d); return d; });
    document.body.append(layer);
    const scene = window.SmartKit.scenes.cur(), state = markState = {};
    const tick = () => {
      if (markState !== state) return;
      if (window.SmartKit.scenes.cur() !== scene) { clearMarks(); return; }
      targets.forEach((t, i) => {
        let u = null; try { u = unionRect(t.item.rects()); } catch (e) { /* hotspot sedang dibangun ulang */ }
        const d = boxes[i];
        if (!u) { d.style.display = 'none'; return; }
        d.style.display = 'block'; d.style.left = (u.l - 6) + 'px'; d.style.top = (u.t - 6) + 'px'; d.style.width = (u.r - u.l + 12) + 'px'; d.style.height = (u.b - u.t + 12) + 'px';
      });
      markRaf = requestAnimationFrame(tick);
    };
    tick();
  }
  /* pratinjau boleh digeser, tetapi mengetuk hotspot lokasi terdekat tidak boleh memindahkan iframe ke tur lain (mesin ikut hilang) */
  let navBlocked = false;
  function blockNav() {
    if (navBlocked) return; navBlocked = true;
    document.addEventListener('click', (e) => {   // hotspot lokasi terdekat dan logo Beranda (#logoHome di Shadow DOM shell: lewat composedPath) membuka tur/peta lain
      const path = e.composedPath ? e.composedPath() : [];
      if (path.some((n) => n && n.matches && (n.matches('#bdg-locs a') || n.id === 'logoHome'))) { e.preventDefault(); e.stopPropagation(); }
    }, true);
  }

  /* pratinjau di tur: buka panorama sceneId, pasang posisi 'before' (awal) atau 'after' (hasil kalibrasi) untuk perpindahan di panorama itu, lalu arahkan kamera ke yang pertama */
  async function show(sceneId, moves, mode) {
    const TE = window.TourEngine, rapat = TE && TE.OI_RAPAT;
    if (TE) TE.OI_RAPAT = false;
    blockNav(); try { if (window.pano.stopAutorotate) window.pano.stopAutorotate(); } catch (e) { /* lanjut */ }
    window.SmartKit.scenes.go(sceneId); await sleep(2400);
    const items = collect();
    let focus = null;
    for (const m of moves || []) {
      if (m.scene !== sceneId) continue;
      const it = items.find((x) => x.key === m.key); if (!it) continue;
      const p = mode === 'before' ? m.from : m.to;
      it.move(p.pan, p.tilt); focus = focus || it;
    }
    clearMarks();
    if (focus) {
      await look(focus.pan, focus.tilt, window.pano.getVFov()); await frames(3);
      const mine = focus.rects();
      if (mine.length) {
        const red = { color: '#ff3b30', glow: 'rgba(255,59,48,.25)' }, green = { color: '#1f9d6a', glow: 'rgba(31,157,106,.28)' };
        if (mode === 'before') {   // yang menimpa / ditimpa: semua hotspot lain yang kotaknya bersentuhan dengan hotspot ini pada posisi awal
          const partners = items.filter((x) => x !== focus && x.rects().some((y) => mine.some((z) => overlap(z, y, MARGIN) > MIN_AREA)));
          startMarks([{ item: focus, ...red }].concat(partners.map((p) => ({ item: p, ...red }))));
        } else startMarks([{ item: focus, ...green }]);
      }
    }
    if (TE) TE.OI_RAPAT = rapat;
    return !!focus;
  }

  window.__HSCAL = { version: 1, KINDS, run, reset, show, prepare, _collect: collect, _look: look };   // _collect/_look: kait uji
})();
