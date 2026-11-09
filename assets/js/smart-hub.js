/* smart-hub.js — antarmuka tab "Smart Hub" CMS (berlaku untuk semua wilayah/project).
 * Mengelola: paket & penerapan Smart Kit per lokasi, aksi tombol, menu, sumber data (feed), integrasi API, panduan developer.
 * Data disimpan lewat api.php?action=smart-hub (lihat smart-hub.php). Pratinjau memakai SDK Smart Kit (postMessage) ke iframe tur. */
(() => {
  'use strict';
  const boot = JSON.parse(document.getElementById('smartHubBoot').textContent);
  const CSRF = boot.csrf;
  let S = boot.state;                    // state server terakhir
  let hub = null, saved = '';            // salinan kerja _hub.json
  let tab = (location.hash.match(/sh=([a-z]+)/) || [])[1] || 'lokasi';
  let selAction = null, selFeed = null, previewKit = null, previewLoc = '', scenesCache = {};
  const main = document.getElementById('shMain');

  /* ---------- util ---------- */
  const h = (tag, props, ...kids) => {
    const e = document.createElement(tag);
    for (const [k, v] of Object.entries(props || {})) {
      if (v == null || v === false) continue;
      if (k === 'class') e.className = v; else if (k === 'html') e.innerHTML = v; else if (k.startsWith('on')) e.addEventListener(k.slice(2), v);
      else if (k === 'value') e.value = v; else if (k === 'checked') e.checked = !!v; else e.setAttribute(k, v === true ? '' : v);
    }
    for (const c of kids.flat(Infinity)) if (c != null && c !== false) e.append(c.nodeType ? c : document.createTextNode(String(c)));
    return e;
  };
  const clone = (o) => JSON.parse(JSON.stringify(o));
  const toast = (msg, bad) => { const t = document.getElementById('shToast'); t.textContent = msg; t.className = 'sh-toast' + (bad ? ' is-bad' : ''); t.hidden = false; clearTimeout(t._t); t._t = setTimeout(() => (t.hidden = true), bad ? 7000 : 3600); };
  const copy = (text) => { navigator.clipboard?.writeText(text).then(() => toast('Disalin ke clipboard')).catch(() => prompt('Salin:', text)); };
  async function post(op, data = {}) {
    const r = await fetch('api.php?action=smart-hub', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf: CSRF, op, ...data }) });
    let j; try { j = await r.json(); } catch { throw new Error('Respons server tidak valid (' + r.status + ')'); }
    if (r.status === 401) { location.href = 'login.php?next=' + encodeURIComponent('index.php?tab=smart'); throw new Error(j.message); }
    if (!j.success && !j.results) throw new Error(j.message || 'Gagal');
    return j;
  }
  const field = (label, control, hint) => h('label', { class: 'sh-field' }, h('span', null, label), control, hint ? h('small', null, hint) : null);
  const select = (opts, value, onchange, attrs = {}) => h('select', { ...attrs, onchange: (e) => onchange(e.target.value) }, opts.map(([v, t]) => h('option', { value: v, selected: String(v) === String(value ?? '') ? true : null }, t)));
  const input = (value, oninput, attrs = {}) => h('input', { type: 'text', value: value ?? '', ...attrs, oninput: (e) => oninput(e.target.value) });
  const codeBox = (text, label) => h('div', { class: 'sh-code' }, h('div', { class: 'sh-code__h' }, h('span', null, label || 'Kode'), h('button', { type: 'button', class: 'translate', onclick: () => copy(text) }, 'Salin')), h('pre', null, text));

  const C = () => S.catalog || { features: {}, actionTypes: {}, sources: {}, slots: {}, menus: {}, events: {} };
  const EXTRA = { peta: { label: 'Peta', icon: 'map', type: 'peta' }, jali: { label: 'Tanya Jali', icon: 'chat', type: 'jali' }, 'sembunyikan-ui': { label: 'Sembunyikan tampilan', icon: 'eye', type: 'ui', op: 'sembunyikan' }, 'tutup-semua': { label: 'Tutup popup', icon: 'x', type: 'ui', op: 'tutup' } };
  const builtins = () => { const o = {}; for (const [f, m] of Object.entries(C().features)) o[f] = { label: m.label, icon: m.icon, type: 'fitur', feature: f, args: {} }; return { ...o, ...EXTRA }; };
  const allActions = () => ({ ...builtins(), ...(hub?.actions || {}) });
  const isBuiltin = (id) => id in builtins();
  const dirty = () => hub && JSON.stringify(hub) !== saved;
  const markDirty = () => { document.getElementById('shSaveBar').hidden = !dirty(); };
  function loadHub() {
    const raw = S.hub || {};
    hub = { actions: { ...(raw.actions || {}) }, menus: { ...(raw.menus || {}) }, feeds: { ...(raw.feeds || {}) }, api: { allowOrigins: [], bridge: true, rest: true, ...(raw.api || {}) }, ui: { ...(raw.ui || {}) } };
    for (const [k, m] of Object.entries(C().menus)) if (!hub.menus[k]) hub.menus[k] = [...(m.default || [])];
    saved = JSON.stringify(hub); markDirty();
  }

  /* ---------- tab bar ---------- */
  document.querySelectorAll('[data-sh-tab]').forEach((b) => b.addEventListener('click', () => { if (window.__shContentDirty?.() && !confirm('Ada perubahan konten yang belum disimpan. Pindah tab tanpa menyimpan?')) return; if (window.__shContentDirty?.()) window.SmartHubContent?.reset(); tab = b.dataset.shTab; history.replaceState(null, '', '#sh=' + tab); render(); }));
  addEventListener('beforeunload', (e) => { if (dirty() || window.__shContentDirty?.()) { e.preventDefault(); e.returnValue = ''; } });
  document.getElementById('shSave').addEventListener('click', saveHub);
  document.getElementById('shDiscard').addEventListener('click', () => { loadHub(); render(); toast('Perubahan dibatalkan'); });

  async function saveHub() {
    const btn = document.getElementById('shSave'); btn.disabled = true;
    try { const j = await post('save_hub', { hub }); S.hub = j.hub; S.hubExists = true; loadHub(); render(); toast(j.message); reloadPreview(); }
    catch (e) { toast(e.message, true); } finally { btn.disabled = false; }
  }

  function render() {
    document.querySelectorAll('[data-sh-tab]').forEach((b) => b.setAttribute('aria-selected', String(b.dataset.shTab === tab)));
    document.getElementById('shWilayah').textContent = 'wilayah: ' + (S.settings?.wilayah || '—');
    document.getElementById('shHubFile').textContent = S.hubExists ? '_hub.json tersimpan' : '_hub.json belum ada (memakai bawaan)';
    main.replaceChildren();
    if (!S.kitOk && tab !== 'integrasi') { main.append(h('div', { class: 'card sh-card sh-warn' }, h('h3', null, 'Smart Kit belum terhubung'), h('p', null, 'Folder Smart Kit tidak ditemukan di ', h('code', null, S.settings.path), '. Atur di tab "Integrasi & Tampilan" › Pengaturan project.'), h('button', { class: 'save', onclick: () => { tab = 'integrasi'; render(); } }, 'Buka pengaturan'))); return; }
    ({ lokasi: viewLokasi, konten: () => window.SmartHubContent.konten(main), wilayah: () => window.SmartHubContent.wilayah(main), aksi: viewAksi, menu: viewMenu, data: viewData, integrasi: viewIntegrasi, panduan: viewPanduan })[tab]();
    renderPreviewSelect();
  }

  /* ---------- 1. Lokasi & paket ---------- */
  function viewLokasi() {
    const chosen = new Set();
    const rows = S.locations.map((l) => {
      const cb = h('input', { type: 'checkbox', 'aria-label': 'Pilih ' + l.name, onchange: (e) => { e.target.checked ? chosen.add(l.id) : chosen.delete(l.id); } });
      const c = l.pack.counts;
      return h('tr', null,
        h('td', null, cb),
        h('td', null, h('b', null, l.name), h('small', null, l.id + ' · ' + l.path)),
        h('td', null, l.pack.exists ? h('span', { class: 'sh-badge is-ok' }, l.pack.scenes + ' scene') : h('span', { class: 'sh-badge' }, 'belum dibangun')),
        h('td', null, l.pack.content ? h('span', { class: 'sh-mini' }, `${c.layanan} layanan · ${c.fakta} fakta · ${c.acara} acara · ${c.rute} rute · ${c.stempel} stempel`) : h('span', { class: 'sh-mini is-muted' }, '—')),
        h('td', null, l.applied ? h('span', { class: 'sh-badge is-ok', title: l.appliedSrc }, 'HUD Smart Kit') : h('span', { class: 'sh-badge is-muted' }, 'skin bawaan')),
        h('td', { class: 'sh-actions' }, l.url ? h('a', { class: 'translate', href: l.url, target: '_blank', rel: 'noopener' }, 'Buka ↗') : null,
          h('button', { type: 'button', class: 'translate', disabled: !l.applied || null, onclick: () => setPreview(l.id) }, 'Pratinjau')));
    });
    const run = async (op, label) => {
      if (!chosen.size) return toast('Centang minimal satu lokasi.', true);
      if (op !== 'build' && !confirm(`${label} untuk ${chosen.size} lokasi? index.html dicadangkan otomatis ke .skin-backups.`)) return;
      main.querySelectorAll('button').forEach((b) => (b.disabled = true));
      try { const j = await post(op, { locations: [...chosen] }); S = j.state; window.SmartHubContent?.reset(); render(); showResults(j); }
      catch (e) { toast(e.message, true); render(); }
    };
    main.append(h('div', { class: 'card sh-card' },
      h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, 'Lokasi di project ini'), h('p', null, `${S.locations.length} tur terdeteksi di ${S.project.path}. Paket konten dibuat dari pano.xml (tur tidak diubah); "Terapkan" memasang HUD Smart Kit ke index.html.`)),
        h('label', { class: 'sh-selall' }, h('input', { type: 'checkbox', onchange: (e) => { main.querySelectorAll('tbody input[type=checkbox]').forEach((x) => { x.checked = e.target.checked; x.dispatchEvent(new Event('change')); }); } }), 'Pilih semua')),
      S.locations.length ? h('div', { class: 'sh-tablewrap' }, h('table', { class: 'sh-table' }, h('thead', null, h('tr', null, h('th', null, ''), h('th', null, 'Lokasi'), h('th', null, 'Paket'), h('th', null, 'Konten'), h('th', null, 'Tampilan'), h('th', null, ''))), h('tbody', null, rows)))
        : h('div', { class: 'sh-empty' }, 'Belum ada folder tur (berisi pano.xml) di project ini.'),
      h('div', { class: 'sh-bar' },
        h('button', { type: 'button', class: 'translate', onclick: () => run('build', 'Bangun paket') }, '⚙ Bangun / perbarui paket'),
        h('button', { type: 'button', class: 'save', onclick: () => run('apply', 'Terapkan Smart Kit') }, '✓ Terapkan Smart Kit'),
        h('button', { type: 'button', class: 'translate', onclick: () => run('unapply', 'Lepas Smart Kit') }, '↺ Lepas (kembali ke skin bawaan)')),
      h('div', { class: 'sh-note' }, h('b', null, 'Alur per lokasi: '), '① Bangun paket (scenes, thumbnail, kerangka content.json) → ② isi konten (Studio/Excel) → ③ isi konten di tab 2–3, atur aksi, menu & data di tab 4–6 → ④ Terapkan. Konten lokasi tetap di ', h('code', null, `smart-kit/packs/${S.settings.wilayah}/<lokasi>/content.json`), '.')));
    const res = h('div', { id: 'shResults' }); main.append(res);
  }
  function showResults(j) {
    const box = document.getElementById('shResults'); if (!box) return toast(j.message);
    box.replaceChildren(h('div', { class: 'card sh-card' }, h('h3', null, j.message), h('ul', { class: 'sh-results' }, (j.results || []).map((r) => h('li', { class: r.ok ? 'is-ok' : 'is-bad' }, h('b', null, r.id), h('pre', null, r.message))))));
    toast(j.message, !j.success);
  }

  /* ---------- 2. Aksi ---------- */
  function actionSummary(a) {
    const T = C().actionTypes;
    if (a.type === 'fitur') return 'Buka ' + (C().features[a.feature]?.label || a.feature) + (a.args && Object.keys(a.args).length ? ' ' + JSON.stringify(a.args) : '');
    if (a.type === 'scene') return 'Scene ' + a.scene + (a.view ? ` (pan ${a.view.pan ?? '–'}, tilt ${a.view.tilt ?? '–'})` : '');
    if (a.type === 'tautan') return (T.tautan?.targets?.[a.target] || '') + ' · ' + a.url;
    if (a.type === 'video360') return 'Video 360: ' + a.video;
    if (a.type === 'ui') return T.ui?.ops?.[a.op] || a.op;
    if (a.type === 'event') return 'Event: ' + a.name;
    if (a.type === 'bahasa') return 'Bahasa: ' + a.lang;
    return T[a.type]?.label || a.type;
  }
  const icoSel = (value, on) => select([['', '— tanpa ikon —'], ...S.icons.map((i) => [i, i])], value, on);
  function viewAksi() {
    const acts = allActions();
    const list = h('div', { class: 'sh-list' }, Object.entries(acts).map(([id, a]) => h('button', { type: 'button', class: 'sh-item' + (selAction === id ? ' is-on' : '') + (hub.actions[id] ? '' : ' is-builtin'), onclick: () => { selAction = id; render(); } },
      h('span', { class: 'sh-item__t' }, h('b', null, a.label || id), h('code', null, id)), h('small', null, actionSummary(a)), hub.actions[id] ? (isBuiltin(id) ? h('em', null, 'diubah') : h('em', null, 'kustom')) : h('em', { class: 'is-muted' }, 'bawaan'))));
    const add = h('button', { type: 'button', class: 'save', onclick: () => {
      const id = (prompt('ID aksi baru (huruf kecil, angka, - atau _). Dipakai developer: data-sk-action="…"', 'aksi-baru') || '').trim().toLowerCase();
      if (!id) return; if (!/^[a-z0-9][a-z0-9_-]{0,40}$/.test(id)) return toast('ID tidak valid.', true); if (acts[id]) return toast('ID sudah dipakai.', true);
      hub.actions[id] = { type: 'fitur', label: id, feature: Object.keys(C().features)[0], args: {} }; selAction = id; markDirty(); render();
    } }, '＋ Aksi baru');
    main.append(h('div', { class: 'sh-split' },
      h('div', { class: 'card sh-card' }, h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, 'Aksi tombol'), h('p', null, 'Setiap aksi = satu ID yang bisa dipasang ke tombol mana pun (HUD, menu, front-end luar).')), add), list),
      h('div', { class: 'card sh-card' }, selAction && acts[selAction] ? actionEditor(selAction) : h('div', { class: 'sh-empty' }, 'Pilih aksi di kiri untuk melihat/mengubah, atau buat aksi baru.'))));
  }
  function actionEditor(id) {
    const custom = !!hub.actions[id];
    const a = custom ? hub.actions[id] : clone(allActions()[id]);
    const edit = () => { if (!hub.actions[id]) hub.actions[id] = a; markDirty(); };
    const set = (k, v) => { a[k] = v; edit(); };
    const T = C().actionTypes;
    const body = h('div', { class: 'sh-form' });
    const typeFields = () => {
      const box = h('div', { class: 'sh-form' });
      if (a.type === 'fitur') {
        const f = C().features[a.feature] || {};
        box.append(field('Fitur', select(Object.entries(C().features).map(([k, m]) => [k, m.label + ' (' + k + ')']), a.feature, (v) => { set('feature', v); rerender(); }), f.desc),
          field('Argumen (JSON, opsional)', h('textarea', { rows: 3, spellcheck: 'false', oninput: (e) => { try { a.args = e.target.value.trim() ? JSON.parse(e.target.value) : {}; e.target.classList.remove('is-bad'); edit(); } catch { e.target.classList.add('is-bad'); } } }, JSON.stringify(a.args || {})), f.args?.length ? 'Argumen yang dikenali fitur ini: ' + f.args.join(', ') + '. Contoh: {"' + f.args[0] + '": "…"}' : 'Fitur ini tidak memakai argumen.'));
      } else if (a.type === 'scene') {
        const scenes = scenesCache[previewLoc] || [];
        const sc = scenes.length ? select([['', '— pilih scene —'], ...scenes.map((s) => [s.id, s.name + ' (' + s.id + ')'])], a.scene, (v) => set('scene', v)) : input(a.scene, (v) => set('scene', v), { placeholder: 'node12' });
        const v = a.view || (a.view = {});
        const num = (k) => h('input', { type: 'number', step: '0.1', value: v[k] ?? '', placeholder: k, oninput: (e) => { v[k] = e.target.value === '' ? undefined : +e.target.value; edit(); } });
        box.append(field('Scene', sc, scenes.length ? 'Daftar dari lokasi pratinjau: ' + previewLoc + '. Id scene berbeda tiap lokasi — aksi scene sebaiknya dibuat per lokasi.' : 'Pilih lokasi pratinjau (kanan) agar daftar scene muncul.'),
          h('div', { class: 'sh-row3' }, field('Pan', num('pan')), field('Tilt', num('tilt')), field('FOV', num('fov'))),
          h('button', { type: 'button', class: 'translate', disabled: !previewKit || null, onclick: async () => { try { const vw = await previewKit.view(); const cur = (await previewKit.slots())['scene.id']; Object.assign(v, { pan: Math.round(vw.pan * 10) / 10, tilt: Math.round(vw.tilt * 10) / 10, fov: Math.round(vw.fov) }); if (cur) a.scene = cur; edit(); rerender(); } catch (e) { toast(e.message, true); } } }, '◎ Ambil scene & arah pandang dari pratinjau'));
      } else if (a.type === 'tautan') {
        box.append(field('URL', input(a.url, (v) => set('url', v), { placeholder: 'https://… atau /halaman' }), 'Penanda yang diganti otomatis: ' + (T.tautan?.placeholders || []).join(' ')),
          field('Buka sebagai', select(Object.entries(T.tautan?.targets || {}), a.target || 'popup', (v) => set('target', v))));
      } else if (a.type === 'video360') {
        box.append(field('Video 360', S.videos.length ? select([['', '— pilih —'], ...S.videos.map((v) => [v, v])], a.video, (v) => set('video', v)) : input(a.video, (v) => set('video', v)), 'Dari folder assets/360/videos project (diunggah di menu Video 360).'));
      } else if (a.type === 'jali') {
        box.append(field('Pertanyaan otomatis (opsional)', input(a.q, (v) => set('q', v), { placeholder: 'Kosongkan = buka menu Jali' })));
      } else if (a.type === 'bahasa') {
        box.append(field('Kode bahasa', input(a.lang, (v) => set('lang', v), { placeholder: 'id / en / zh / ar' })));
      } else if (a.type === 'ui') {
        box.append(field('Operasi', select([['', '— pilih —'], ...Object.entries(T.ui?.ops || {})], a.op, (v) => set('op', v))));
      } else if (a.type === 'event') {
        box.append(field('Nama event', input(a.name, (v) => set('name', v), { placeholder: 'promo:buka' }), 'Front-end menerima lewat kit.on("custom") atau document "smartkit:<nama>".'),
          field('Data (JSON, opsional)', h('textarea', { rows: 3, spellcheck: 'false', oninput: (e) => { try { a.data = e.target.value.trim() ? JSON.parse(e.target.value) : undefined; e.target.classList.remove('is-bad'); edit(); } catch { e.target.classList.add('is-bad'); } } }, a.data ? JSON.stringify(a.data) : '')));
      }
      return box;
    };
    let tf = typeFields();
    const rerender = () => { const n = typeFields(); tf.replaceWith(n); tf = n; };
    body.append(
      h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, a.label || id), h('p', null, 'ID: ', h('code', null, id), custom ? '' : ' · bawaan (ubah apa pun untuk menimpanya)')),
        h('div', { class: 'sh-actions' },
          h('button', { type: 'button', class: 'translate', disabled: !previewKit || null, onclick: async () => { try { const r = await previewKit.run(clone(a)); log('aksi ' + id + ' → ' + JSON.stringify(r ?? 'ok')); } catch (e) { toast(e.message, true); } } }, '▶ Uji di pratinjau'),
          custom ? h('button', { type: 'button', class: 'danger', onclick: () => { if (!confirm(isBuiltin(id) ? 'Kembalikan aksi bawaan?' : 'Hapus aksi ' + id + '? Menu yang memakainya ikut dibersihkan.')) return; delete hub.actions[id]; if (!isBuiltin(id)) { for (const k in hub.menus) hub.menus[k] = hub.menus[k].filter((x) => x !== id); selAction = null; } markDirty(); render(); } }, isBuiltin(id) ? 'Kembalikan bawaan' : 'Hapus') : null)),
      h('div', { class: 'sh-row2' }, field('Label tombol', input(a.label, (v) => set('label', v))), field('Ikon', h('div', { class: 'sh-ico' }, icoSel(a.icon, (v) => set('icon', v))))),
      field('Jenis aksi', select(Object.entries(T).map(([k, t]) => [k, t.label]), a.type, (v) => { a.type = v; edit(); rerender(); })),
      tf,
      field('', h('label', { class: 'sh-check' }, h('input', { type: 'checkbox', checked: a.hidden, onchange: (e) => set('hidden', e.target.checked || undefined) }), 'Sembunyikan (nonaktif di semua tempat)')),
      codeBox(`<button data-sk-action="${id}">${a.label || id}</button>\n<!-- atau JavaScript: -->\nSmartKit.run('${id}')        // di dalam tur\nkit.run('${id}')             // front-end luar (SDK)`, 'Pemakaian oleh developer'));
    return body;
  }

  /* ---------- 3. Menu ---------- */
  function viewMenu() {
    const acts = allActions();
    const menuCard = (name, meta) => {
      const ids = hub.menus[name] || (hub.menus[name] = []);
      const chips = h('ol', { class: 'sh-menu' }, ids.map((id, i) => h('li', null, h('span', null, h('b', null, acts[id]?.label || id), h('code', null, id)),
        h('span', { class: 'sh-actions' },
          h('button', { type: 'button', class: 'translate', 'aria-label': 'Naik', disabled: i === 0 || null, onclick: () => { ids.splice(i - 1, 0, ids.splice(i, 1)[0]); markDirty(); render(); } }, '↑'),
          h('button', { type: 'button', class: 'translate', 'aria-label': 'Turun', disabled: i === ids.length - 1 || null, onclick: () => { ids.splice(i + 1, 0, ids.splice(i, 1)[0]); markDirty(); render(); } }, '↓'),
          h('button', { type: 'button', class: 'danger', 'aria-label': 'Hapus dari menu', onclick: () => { ids.splice(i, 1); markDirty(); render(); } }, '×')))));
      const pick = select([['', '＋ Tambah aksi ke menu…'], ...Object.entries(acts).filter(([id]) => !ids.includes(id)).map(([id, a]) => [id, (a.label || id) + ' (' + id + ')'])], '', (v) => { if (!v) return; ids.push(v); markDirty(); render(); });
      return h('div', { class: 'card sh-card' }, h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, meta?.label || 'Menu ' + name), h('p', null, 'ID menu: ', h('code', null, name), ' · item tampil sesuai urutan; aksi fitur tanpa data di suatu lokasi otomatis disembunyikan.')),
        meta?.default ? h('button', { type: 'button', class: 'translate', onclick: () => { hub.menus[name] = [...meta.default]; markDirty(); render(); } }, 'Reset bawaan') : h('button', { type: 'button', class: 'danger', onclick: () => { delete hub.menus[name]; markDirty(); render(); } }, 'Hapus menu')),
        ids.length ? chips : h('div', { class: 'sh-empty' }, 'Menu kosong.'), pick,
        codeBox(`<nav data-sk-menu="${name}">\n  <template><button><span data-sk-field="label"></span></button></template>\n</nav>`, 'Render menu ini di front-end'));
    };
    for (const [k, m] of Object.entries(C().menus)) main.append(menuCard(k, m));
    for (const k of Object.keys(hub.menus)) if (!C().menus[k]) main.append(menuCard(k, null));
    main.append(h('button', { type: 'button', class: 'save', onclick: () => { const n = (prompt('ID menu baru (mis. header, sidebar, beranda):') || '').trim().toLowerCase(); if (!/^[a-z0-9][a-z0-9_-]{0,40}$/.test(n)) return n && toast('ID tidak valid.', true); if (hub.menus[n]) return toast('Sudah ada.', true); hub.menus[n] = []; markDirty(); render(); } }, '＋ Menu baru untuk front-end'));
  }

  /* ---------- 4. Sumber data (feed) ---------- */
  function viewData() {
    const SRC = C().sources;
    const list = h('div', { class: 'sh-list' }, Object.entries(hub.feeds).map(([id, f]) => h('button', { type: 'button', class: 'sh-item' + (selFeed === id ? ' is-on' : ''), onclick: () => { selFeed = id; render(); } },
      h('span', { class: 'sh-item__t' }, h('b', null, f.label || id), h('code', null, id)), h('small', null, (SRC[f.source]?.label || f.source) + (f.filter ? ' · saring ' + Object.entries(f.filter).map(([k, v]) => k + '=' + v).join(', ') : '') + (f.limit ? ' · maks ' + f.limit : '')))));
    const add = h('button', { type: 'button', class: 'save', onclick: () => {
      const id = (prompt('ID feed baru (dipakai developer: data-sk-feed="…" / api/?feed=…)', 'layanan-kuliner') || '').trim().toLowerCase();
      if (!id) return; if (!/^[a-z0-9][a-z0-9_-]{0,40}$/.test(id)) return toast('ID tidak valid.', true); if (hub.feeds[id]) return toast('ID sudah dipakai.', true);
      hub.feeds[id] = { source: 'layanan', label: id }; selFeed = id; markDirty(); render();
    } }, '＋ Feed baru');
    const srcRef = h('details', { class: 'sh-ref' }, h('summary', null, 'Daftar sumber data yang tersedia'), h('ul', null, Object.entries(SRC).map(([k, s]) => h('li', null, h('code', null, k), ' — ', s.label, s.runtime ? h('em', null, ' (hanya di dalam tur)') : '', h('small', null, s.desc + ' · kolom: ' + s.fields.join(', '))))));
    main.append(h('div', { class: 'sh-split' },
      h('div', { class: 'card sh-card' }, h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, 'Sumber data (feed)'), h('p', null, 'Feed = data siap pakai untuk front-end: pilih sumber, saring, urutkan, batasi kolom.')), add),
        Object.keys(hub.feeds).length ? list : h('div', { class: 'sh-empty' }, 'Belum ada feed. Sumber mentah tetap bisa dipakai langsung (mis. data-sk-feed="layanan").'), srcRef),
      h('div', { class: 'card sh-card' }, selFeed && hub.feeds[selFeed] ? feedEditor(selFeed) : h('div', { class: 'sh-empty' }, 'Pilih atau buat feed.'))));
  }
  function feedEditor(id) {
    const f = hub.feeds[id], SRC = C().sources, src = SRC[f.source] || { fields: [] };
    const set = (k, v) => { if (v === '' || v == null || (Array.isArray(v) && !v.length)) delete f[k]; else f[k] = v; markDirty(); };
    const filt = Object.entries(f.filter || {});
    const filterRows = h('div', { class: 'sh-form' }, filt.map(([k, v], i) => h('div', { class: 'sh-row3' },
      select(src.fields.map((x) => [x, x]).concat(src.fields.includes(k) ? [] : [[k, k]]), k, (nk) => { const o = { ...f.filter }; delete o[k]; o[nk] = v; f.filter = o; markDirty(); render(); }),
      input(Array.isArray(v) ? v.join(',') : v, (nv) => { f.filter[k] = nv.includes(',') ? nv.split(',').map((x) => x.trim()).filter(Boolean) : nv; markDirty(); }, { placeholder: 'nilai (pisahkan koma untuk beberapa)' }),
      h('button', { type: 'button', class: 'danger', onclick: () => { delete f.filter[k]; if (!Object.keys(f.filter).length) delete f.filter; markDirty(); render(); } }, '×'))),
      h('button', { type: 'button', class: 'translate', onclick: () => { f.filter = { ...(f.filter || {}), [src.fields.find((x) => !(f.filter || {})[x]) || 'id']: '' }; markDirty(); render(); } }, '＋ Saringan'));
    const fields = h('div', { class: 'sh-checks' }, src.fields.map((x) => h('label', { class: 'sh-check' }, h('input', { type: 'checkbox', checked: (f.fields || []).includes(x), onchange: (e) => { const s = new Set(f.fields || []); e.target.checked ? s.add(x) : s.delete(x); set('fields', src.fields.filter((y) => s.has(y))); } }), x)));
    const out = h('div', { class: 'sh-previewdata' }, h('small', null, 'Tekan "Pratinjau data" untuk melihat hasil dari lokasi pratinjau.'));
    const rest = (S.settings.url.startsWith('http') ? '' : S.cmsOrigin) + S.settings.url + 'api/?pack=' + S.settings.wilayah + '/' + (previewLoc || '<lokasi>') + '&feed=' + id;
    return h('div', { class: 'sh-form' },
      h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, f.label || id), h('p', null, 'ID: ', h('code', null, id))),
        h('div', { class: 'sh-actions' }, h('button', { type: 'button', class: 'translate', onclick: async () => {
          if (!previewLoc) return toast('Pilih lokasi pratinjau dulu (panel kanan).', true);
          out.replaceChildren(h('small', null, 'Memuat…'));
          try { const j = await post('preview_feed', { location: previewLoc, feed: f }); out.replaceChildren(dataTable(j.items)); }
          catch (e) { out.replaceChildren(h('div', { class: 'sh-warn' }, e.message)); }
        } }, '👁 Pratinjau data'), h('button', { type: 'button', class: 'danger', onclick: () => { if (confirm('Hapus feed ' + id + '?')) { delete hub.feeds[id]; selFeed = null; markDirty(); render(); } } }, 'Hapus'))),
      h('div', { class: 'sh-row2' }, field('Label', input(f.label, (v) => set('label', v))), field('Sumber data', select(Object.entries(SRC).map(([k, s]) => [k, s.label + (s.runtime ? ' (tur)' : '')]), f.source, (v) => { f.source = v; delete f.filter; delete f.fields; markDirty(); render(); }), src.desc)),
      field('Saringan (kolom = nilai)', filterRows, 'Contoh: cat = food → hanya layanan kuliner. Kolom bertitik (mis. about.text) didukung.'),
      h('div', { class: 'sh-row3' },
        field('Urutkan', select([['', '— asli —'], ['jarak', 'Terdekat dari scene aktif'], ...src.fields.flatMap((x) => [[x, x + ' ↑'], ['-' + x, x + ' ↓']])], f.sort, (v) => set('sort', v))),
        field('Batas jumlah', h('input', { type: 'number', min: 0, max: 500, value: f.limit ?? '', oninput: (e) => set('limit', e.target.value === '' ? '' : +e.target.value) })),
        field('Hanya scene aktif', h('label', { class: 'sh-check' }, h('input', { type: 'checkbox', checked: f.scene === 'aktif', onchange: (e) => set('scene', e.target.checked ? 'aktif' : '') }), 'ya (di dalam tur)'))),
      field('Kolom yang dikirim (kosong = semua)', fields),
      out,
      codeBox(`<ul data-sk-feed="${id}" data-sk-limit="6">\n  <template>\n    <li data-sk-action="@item">\n      <img data-sk-field-src="${src.fields.includes('thumb') ? 'thumb' : src.fields.includes('photo') ? 'photo' : 'img'}" alt="">\n      <b data-sk-field="${src.fields.includes('name') ? 'name' : src.fields[0] || 'id'}"></b>\n    </li>\n  </template>\n  <p data-sk-empty hidden>Belum ada data.</p>\n</ul>`, 'HTML front-end (otomatis terisi)'),
      codeBox(`// di dalam tur\nSmartKit.feed('${id}')\n// front-end luar (iframe)\nconst rows = await kit.feed('${id}', { q: 'kopi', limit: 10 })\n// REST (tanpa tur)${SRC[f.source]?.runtime ? '  — tidak tersedia untuk sumber runtime' : ''}\nfetch('${rest}')`, 'JavaScript / REST'));
  }
  function dataTable(items) {
    if (!items?.length) return h('div', { class: 'sh-empty' }, 'Tidak ada data untuk saringan ini di lokasi ' + previewLoc + '.');
    const cols = Object.keys(items[0]).filter((k) => k !== '_action').slice(0, 7);
    const cell = (v) => (v == null ? '' : typeof v === 'object' ? JSON.stringify(v) : String(v)).slice(0, 90);
    return h('div', { class: 'sh-tablewrap' }, h('table', { class: 'sh-table sh-table--data' }, h('thead', null, h('tr', null, cols.map((c) => h('th', null, c)), h('th', null, 'aksi item'))),
      h('tbody', null, items.map((r) => h('tr', null, cols.map((c) => h('td', null, cell(r[c]))), h('td', null, h('code', null, r._action ? r._action.type + (r._action.feature ? ':' + r._action.feature : r._action.scene ? ':' + r._action.scene : '') : '')))))), h('small', null, items.length + ' baris (pratinjau maks. 20).'));
  }

  /* ---------- 5. Integrasi & tampilan ---------- */
  function viewIntegrasi() {
    const st = { ...S.settings };
    const settings = h('div', { class: 'card sh-card' }, h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, 'Pengaturan project › Smart Kit'), h('p', null, 'Satu project CMS = satu wilayah (mis. jakarta, badung, kota-baru). Disimpan di projects.json.'))),
      h('div', { class: 'sh-row2' }, field('ID wilayah', input(st.wilayah, (v) => (st.wilayah = v)), 'Nama folder paket: smart-kit/packs/<wilayah>/'), field('Python (untuk Bangun paket)', input(st.python, (v) => (st.python = v)), 'mis. python, atau C:/Python312/python.exe')),
      h('div', { class: 'sh-row2' }, field('Folder Smart Kit di server', input(st.path, (v) => (st.path = v))), field('Alamat publik Smart Kit', input(st.url, (v) => (st.url = v)), 'Dipakai di tag tur & REST, mis. /smart-kit/ atau https://cdn.domain/smart-kit/')),
      h('div', { class: 'sh-bar' }, h('button', { type: 'button', class: 'save', onclick: async () => { try { const j = await post('save_settings', { settings: st }); S = j.state; loadHub(); render(); toast(j.message); } catch (e) { toast(e.message, true); } } }, 'Simpan pengaturan project')),
      S.kitOk ? null : h('div', { class: 'sh-warn' }, 'Smart Kit belum ditemukan pada folder di atas.'));
    main.append(settings);
    if (!S.kitOk) return;
    const ui = hub.ui, api = hub.api;
    const setUi = (k, v) => { if (v) ui[k] = v; else delete ui[k]; markDirty(); };
    /* Identitas: logo + nama instansi di header kiri-atas HUD (semua lokasi wilayah ini) */
    const brand = ui.brand || (ui.brand = {});
    const setBrand = (k, v) => { if (v) brand[k] = v; else delete brand[k]; if (!Object.keys(brand).length) delete ui.brand; markDirty(); paintHeader(); };
    const logoUrl = (ref) => (/^https?:/.test(ref) ? ref : S.settings.url + 'packs/' + S.settings.wilayah + '/' + String(ref || '').replace(/^~\//, ''));
    const previewImg = h('img', { alt: '', class: 'sh-brand__img' });
    const previewWrap = h('div', { class: 'sh-brand__logo' }, previewImg);
    const pvOrg = h('small'), pvPlace = h('b'), pvScene = h('span');
    function paintHeader() {
      previewImg.src = brand.logo ? logoUrl(brand.logo) : ''; previewWrap.hidden = !brand.logo;
      pvOrg.textContent = (brand.name || S.project.name || '').toUpperCase(); pvPlace.textContent = brand.short || S.project.name || ''; pvScene.textContent = 'Nama scene aktif';
    }
    const file = h('input', { type: 'file', accept: 'image/png,image/jpeg,image/webp', hidden: true, onchange: async (e) => {
      const f = e.target.files[0]; if (!f) return;
      if (f.size > 1.5 * 1024 * 1024) return toast('Logo maksimal 1,5 MB. Perkecil dulu (mis. 512×512 px).', true);
      try {
        const data = await new Promise((res, rej) => { const r = new FileReader(); r.onload = () => res(r.result); r.onerror = () => rej(new Error('Berkas tidak terbaca')); r.readAsDataURL(f); });
        const j = await post('upload_logo', { data }); brand.logo = j.logo; markDirty(); paintHeader(); toast(j.message);
      } catch (err) { toast(err.message, true); } finally { e.target.value = ''; }
    } });
    paintHeader();
    main.append(h('div', { class: 'card sh-card' }, h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, 'Identitas (header kiri-atas)'), h('p', null, 'Logo dan nama instansi yang tampil di semua lokasi wilayah ini. Nama scene aktif mengikuti panorama secara otomatis.'))),
      h('div', { class: 'sh-brand' },
        h('div', { class: 'sh-brand__pv', 'aria-label': 'Pratinjau header' }, previewWrap, h('div', { class: 'sh-brand__t' }, pvOrg, pvPlace, pvScene)),
        h('div', { class: 'sh-form' },
          field('Logo', h('div', { class: 'sh-actions', style: 'justify-content:flex-start' }, h('button', { type: 'button', class: 'translate', onclick: () => file.click() }, brand.logo ? 'Ganti logo' : 'Unggah logo'), brand.logo ? h('button', { type: 'button', class: 'danger', onclick: () => setBrand('logo', '') }, 'Hapus logo') : null, file), 'PNG/WebP/JPG, maks 1,5 MB. Disarankan persegi dengan latar transparan (± 512×512 px).'),
          h('div', { class: 'sh-row2' },
            field('Nama instansi', input(brand.name || '', (v) => setBrand('name', v.trim()), { placeholder: 'mis. PEMERINTAH KABUPATEN BADUNG', maxlength: 90 }), 'Baris kecil di atas nama tempat.'),
            field('Nama singkat', input(brand.short || '', (v) => setBrand('short', v.trim()), { placeholder: 'mis. Badung', maxlength: 40 }), 'Dipakai di sapaan Jali & kartu pos.')))),
      S.kitOk ? null : null));
    /* Front-end kustom (skin, menu, halaman popup dari developer lain) */
    const feBox = h('div', { class: 'card sh-card' }, h('div', { class: 'sh-empty' }, 'Memuat paket front-end…'));
    main.append(feBox);
    (async () => {
      const call = async (op, data = {}) => {
        const r = await fetch('api.php?action=smart-frontend', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf: CSRF, op, ...data }) });
        let j; try { j = await r.json(); } catch { throw new Error('Respons server tidak valid (' + r.status + ')'); }
        if (r.status === 401) { location.href = 'login.php'; throw new Error(j.message); }
        if (!j.success) throw new Error(j.message || 'Gagal');
        return j;
      };
      let packs = [];
      try { packs = (await call('list')).packs; } catch (e) { feBox.replaceChildren(h('div', { class: 'sh-warn' }, e.message)); return; }
      const chosen = () => ui.frontend?.id || '';
      const pick = (id) => { if (id) ui.frontend = { id }; else delete ui.frontend; markDirty(); draw(); };
      const admin = !!S.isAdmin;

      /* zip sederhana (tanpa kompresi) untuk unduh template, dibuat di browser */
      const crcT = (() => { const t = []; for (let n = 0; n < 256; n++) { let c = n; for (let k = 0; k < 8; k++) c = c & 1 ? 0xEDB88320 ^ (c >>> 1) : c >>> 1; t[n] = c >>> 0; } return t; })();
      const crc32 = (b) => { let c = 0xFFFFFFFF; for (let i = 0; i < b.length; i++) c = crcT[(c ^ b[i]) & 255] ^ (c >>> 8); return (c ^ 0xFFFFFFFF) >>> 0; };
      const makeZip = (files) => {
        const enc = new TextEncoder(), parts = [], cen = []; let off = 0;
        const d = new Date(), dt = ((d.getFullYear() - 1980) << 9) | ((d.getMonth() + 1) << 5) | d.getDate(), tm = (d.getHours() << 11) | (d.getMinutes() << 5) | (d.getSeconds() >> 1);
        for (const f of files) {
          const name = enc.encode(f.path), data = enc.encode(f.text), crc = crc32(data);
          const lh = new DataView(new ArrayBuffer(30)); lh.setUint32(0, 0x04034b50, true); lh.setUint16(4, 20, true); lh.setUint16(6, 0x0800, true); lh.setUint16(8, 0, true); lh.setUint16(10, tm, true); lh.setUint16(12, dt, true); lh.setUint32(14, crc, true); lh.setUint32(18, data.length, true); lh.setUint32(22, data.length, true); lh.setUint16(26, name.length, true); lh.setUint16(28, 0, true);
          parts.push(lh.buffer, name, data);
          const ch = new DataView(new ArrayBuffer(46)); ch.setUint32(0, 0x02014b50, true); ch.setUint16(4, 20, true); ch.setUint16(6, 20, true); ch.setUint16(8, 0x0800, true); ch.setUint16(10, 0, true); ch.setUint16(12, tm, true); ch.setUint16(14, dt, true); ch.setUint32(16, crc, true); ch.setUint32(20, data.length, true); ch.setUint32(24, data.length, true); ch.setUint16(28, name.length, true); ch.setUint32(42, off, true);
          cen.push(ch.buffer, name); off += 30 + name.length + data.length;
        }
        const csz = cen.reduce((n, b) => n + (b.byteLength ?? b.length), 0), end = new DataView(new ArrayBuffer(22)); end.setUint32(0, 0x06054b50, true); end.setUint16(8, files.length, true); end.setUint16(10, files.length, true); end.setUint32(12, csz, true); end.setUint32(16, off, true);
        return new Blob([...parts, ...cen, end.buffer], { type: 'application/zip' });
      };
      const downloadTemplate = async () => {
        try { const j = await call('template'); const a = h('a', { href: URL.createObjectURL(makeZip(j.files.map((f) => ({ path: 'template-dasar/' + f.path, text: f.text })))), download: 'smartkit-frontend-template.zip' }); document.body.append(a); a.click(); a.remove(); toast('Template diunduh. Ubah, lalu unggah folder-nya di sini.'); }
        catch (e) { toast(e.message, true); }
      };

      /* unggah folder paket: dikirim bertahap (JSON base64), lalu divalidasi & dipasang server */
      const b64 = (buf) => { let s = ''; const u = new Uint8Array(buf); for (let i = 0; i < u.length; i += 0x8000) s += String.fromCharCode.apply(null, u.subarray(i, i + 0x8000)); return btoa(s); };
      const fileIn = h('input', { type: 'file', webkitdirectory: true, multiple: true, hidden: true, onchange: async (e) => {
        const all = [...e.target.files]; e.target.value = ''; if (!all.length) return;
        const rel = (f) => (f.webkitRelativePath || f.name).split('/').slice(1).join('/');
        const skip = (p) => /(^|\/)(\.|node_modules\/|__MACOSX\/)/.test(p) || /(^|\/)(Thumbs\.db|desktop\.ini)$/i.test(p);
        const files = all.map((f) => ({ f, path: rel(f) })).filter((x) => x.path && !skip(x.path));
        const man = files.find((x) => x.path === 'frontend.json');
        if (!man) return toast('frontend.json tidak ditemukan di akar folder yang dipilih. Pilih folder paket (yang berisi frontend.json), bukan folder induknya.', true);
        let meta; try { meta = JSON.parse(await man.f.text()); } catch { return toast('frontend.json bukan JSON yang valid.', true); }
        const total = files.reduce((n, x) => n + x.f.size, 0);
        if (!confirm(`Pasang paket "${meta.name || meta.id}" (${meta.id}, versi ${meta.version || '?'})?\n${files.length} berkas, ${(total / 1048576).toFixed(1)} MB.\nBila ID sudah ada, paket lama dicadangkan lalu diganti.`)) return;
        const status = h('div', { class: 'sh-note' }, 'Menyiapkan…'); feBox.append(status);
        let token = '';
        try {
          token = (await call('begin')).token;
          let batch = [], size = 0, done = 0;
          const flush = async () => { if (!batch.length) return; await call('put', { token, files: batch }); done += batch.length; status.textContent = `Mengunggah… ${done}/${files.length} berkas`; batch = []; size = 0; };
          for (const x of files) {
            const data = b64(await x.f.arrayBuffer());
            if (size + data.length > 3.5 * 1048576 && batch.length) await flush();
            batch.push({ path: x.path, data }); size += data.length;
          }
          await flush();
          const j = await call('commit', { token }); packs = j.packs; token = '';
          pick(j.id); toast(j.message + ' Tekan "Simpan Smart Hub" untuk memakainya.');
        } catch (err) { toast(err.message, true); if (token) { try { await call('abort', { token }); } catch {} } status.remove(); }
      } });

      const draw = () => {
        const sel = chosen();
        const row = (p) => h('label', { class: 'sh-fe' + (sel === p.id ? ' is-on' : '') },
          h('input', { type: 'radio', name: 'fe', checked: sel === p.id, onchange: () => pick(p.id) }),
          h('span', { class: 'sh-fe__t' }, h('b', null, p.name), h('small', null, `${p.id} · v${p.version || '?'}${p.author ? ' · ' + p.author : ''} · ${(p.bytes / 1024).toFixed(0)} KB`),
            h('small', null, 'Menyediakan: ' + [p.entry ? 'shell (header/menu)' : '', ...p.pages.map((x) => 'halaman ' + x)].filter(Boolean).join(', '))),
          admin ? h('button', { type: 'button', class: 'danger', onclick: async (ev) => { ev.preventDefault(); if (!confirm('Hapus paket ' + p.id + '? Cadangan tetap di frontends/.backup.')) return; try { const j = await call('delete', { id: p.id }); packs = j.packs; if (sel === p.id) pick(''); else draw(); toast(j.message); } catch (e) { toast(e.message, true); } } }, 'Hapus') : null);
        feBox.replaceChildren(
          h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, 'Front-end kustom (desain dari developer lain)'), h('p', null, 'Ganti seluruh skin (header, menu) dan/atau halaman popup seperti Informasi, Kategori, Tourism Guide dengan desain sendiri. Data & tombol tetap tersambung otomatis lewat atribut data-sk-*. Berlaku untuk semua lokasi wilayah ini.'))),
          h('div', { class: 'sh-fe-list' },
            h('label', { class: 'sh-fe' + (!sel ? ' is-on' : '') }, h('input', { type: 'radio', name: 'fe', checked: !sel, onchange: () => pick('') }), h('span', { class: 'sh-fe__t' }, h('b', null, 'HUD bawaan Smart Kit'), h('small', null, 'Tanpa front-end kustom (gaya diatur pada "Tampilan HUD" di bawah)'))),
            ...packs.map(row)),
          h('div', { class: 'sh-bar' },
            admin ? h('button', { type: 'button', class: 'save', onclick: () => fileIn.click() }, '⬆ Unggah folder paket') : null, fileIn,
            h('button', { type: 'button', class: 'translate', onclick: downloadTemplate }, '⬇ Unduh template (.zip)'),
            h('a', { class: 'translate', href: S.settings.url + 'frontends/_template/README.md', target: '_blank', rel: 'noopener' }, 'Baca panduan ↗')),
          admin ? null : h('small', null, 'Hanya admin yang dapat mengunggah/menghapus paket dan memilih front-end.'),
          sel && !packs.some((p) => p.id === sel) ? h('div', { class: 'sh-warn' }, `Paket "${sel}" yang dipilih tidak ada di server. Pilih paket lain atau unggah ulang.`) : null,
          h('div', { class: 'sh-note' }, 'Setelah memilih/mengunggah, tekan ', h('b', null, 'Simpan Smart Hub'), ', lalu muat ulang pratinjau. Bila paket gagal dimuat, tur otomatis kembali ke HUD bawaan (lihat konsol peramban).'));
      };
      draw();
    })();
    main.append(h('div', { class: 'card sh-card' }, h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, 'Tampilan HUD (semua lokasi wilayah ini)'), h('p', null, 'Pilih "Tanpa HUD" bila front-end dibangun sepenuhnya oleh developer lain (headless): fitur & popup tetap jalan lewat aksi.'))),
      h('div', { class: 'sh-row2' },
        field('Gaya HUD', select([['', 'Bawaan (Orbit)'], ['orbit', 'Orbit — bola kaca, menu kipas'], ['bar', 'Bar — bilah perintah'], ['klasik', 'Klasik — skin asli tur'], ['none', 'Tanpa HUD — front-end kustom']], ui.hud, (v) => setUi('hud', v))),
        field('Tema', select([['', 'Bawaan (gelap)'], ['dark', 'Gelap'], ['light', 'Terang']], ui.theme, (v) => setUi('theme', v))))));
    const origins = h('textarea', { rows: 4, spellcheck: 'false', placeholder: 'https://app.badungkab.go.id\nhttps://vt.domain.id', oninput: (e) => { api.allowOrigins = e.target.value.split(/\s+/).map((x) => x.trim()).filter(Boolean); markDirty(); } }, (api.allowOrigins || []).join('\n'));
    main.append(h('div', { class: 'card sh-card' }, h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, 'Akses front-end luar (API)'), h('p', null, 'Siapa yang boleh menyambung ke tur (iframe/postMessage) dan membaca REST. Origin yang sama dengan tur selalu diizinkan.'))),
      field('Origin yang diizinkan (satu per baris)', origins, 'Format https://domain[:port]. "*" = semua (hanya untuk uji, jangan di produksi).'),
      h('div', { class: 'sh-checks' },
        h('label', { class: 'sh-check' }, h('input', { type: 'checkbox', checked: api.bridge !== false, onchange: (e) => { api.bridge = e.target.checked; markDirty(); } }), 'Jembatan iframe (SDK postMessage) aktif'),
        h('label', { class: 'sh-check' }, h('input', { type: 'checkbox', checked: api.rest !== false, onchange: (e) => { api.rest = e.target.checked; markDirty(); } }), 'REST data (smart-kit/api/) aktif'))));
  }

  /* ---------- 6. Panduan developer ---------- */
  function viewPanduan() {
    const kitUrl = (S.settings.url.startsWith('http') ? '' : S.cmsOrigin) + S.settings.url;
    const loc = previewLoc || (S.locations.find((l) => l.applied) || S.locations[0] || {}).id || '<lokasi>';
    const tourUrl = (S.locations.find((l) => l.id === loc)?.url ? S.cmsOrigin + S.locations.find((l) => l.id === loc).url : 'https://domain/' + loc + '/');
    const acts = allActions();
    main.append(h('div', { class: 'card sh-card' }, h('h3', null, 'Mulai cepat untuk developer front-end'),
      h('p', null, 'Front-end bebas didesain. Semua tombol & data dihubungkan lewat ID dari CMS ini — tidak perlu menyentuh Pano2VR. Contoh lengkap: ', h('a', { href: kitUrl + 'sdk/contoh-frontend.html', target: '_blank', rel: 'noopener' }, 'sdk/contoh-frontend.html ↗'), '.'),
      codeBox(`<!-- 1. Tur di dalam iframe (origin halaman ini harus diizinkan di tab Integrasi) -->\n<iframe id="tur" src="${tourUrl}" allow="fullscreen; autoplay; gyroscope; accelerometer"></iframe>\n\n<!-- 2. Tombol & data: cukup atribut -->\n<button data-sk-action="guide">Tourism Guide</button>\n<h2 data-sk-bind="scene.nama"></h2>\n<nav data-sk-menu="bottom"></nav>\n\n<!-- 3. Sambungkan -->\n<script src="${kitUrl}sdk/smartkit-sdk.js"></script>\n<script>\n  SmartKitSDK.connect(document.getElementById('tur')).then((kit) => {\n    SmartKitSDK.bind(document, kit);                 // aktifkan data-sk-*\n    kit.on('scene', (e) => console.log('scene', e.id));\n  });\n</script>`, 'Halaman luar + iframe'),
      codeBox(`GET ${kitUrl}api/?pack=${S.settings.wilayah}/${loc}&source=layanan\nGET ${kitUrl}api/?pack=${S.settings.wilayah}/${loc}&feed=<id feed>\nGET ${kitUrl}api/?pack=${S.settings.wilayah}/${loc}&hub=1\nGET ${kitUrl}api/?catalog=1`, 'REST (situs/aplikasi tanpa tur)')));
    const tbl = (rows, cols) => h('div', { class: 'sh-tablewrap' }, h('table', { class: 'sh-table' }, h('thead', null, h('tr', null, cols.map((c) => h('th', null, c)))), h('tbody', null, rows)));
    main.append(h('div', { class: 'card sh-card' }, h('h3', null, 'Aksi (data-sk-action)'), tbl(Object.entries(acts).map(([id, a]) => h('tr', null, h('td', null, h('code', null, id)), h('td', null, a.label || ''), h('td', null, actionSummary(a)), h('td', null, h('button', { type: 'button', class: 'translate', onclick: () => copy(`data-sk-action="${id}"`) }, 'Salin')))), ['ID', 'Label', 'Fungsi', ''])));
    main.append(h('div', { class: 'card sh-card' }, h('h3', null, 'Slot nilai hidup (data-sk-bind)'), tbl(Object.entries(C().slots).map(([k, t]) => h('tr', null, h('td', null, h('code', null, k)), h('td', null, t), h('td', null, h('button', { type: 'button', class: 'translate', onclick: () => copy(`data-sk-bind="${k}"`) }, 'Salin')))), ['Slot', 'Isi', ''])));
    main.append(h('div', { class: 'card sh-card' }, h('h3', null, 'Event (kit.on / SmartKit.on)'), tbl(Object.entries(C().events).map(([k, t]) => h('tr', null, h('td', null, h('code', null, k)), h('td', null, t))), ['Event', 'Keterangan'])));
  }

  /* ---------- pratinjau (SDK) ---------- */
  function renderPreviewSelect() {
    const sel = document.getElementById('shPreviewLoc');
    const applied = S.locations.filter((l) => l.applied && l.url);
    if (!previewLoc && applied[0]) setPreview(applied[0].id, true);
    sel.replaceChildren(h('option', { value: '' }, applied.length ? '— pilih lokasi —' : 'Belum ada lokasi diterapkan'), ...applied.map((l) => h('option', { value: l.id, selected: l.id === previewLoc ? true : null }, l.name)));
  }
  document.getElementById('shPreviewLoc').addEventListener('change', (e) => setPreview(e.target.value));
  document.getElementById('shPreviewReload').addEventListener('click', reloadPreview);
  function reloadPreview() { if (previewLoc) setPreview(previewLoc, false, true); }
  async function loadSdk() {
    if (window.SmartKitSDK) return;
    await new Promise((res, rej) => { const s = document.createElement('script'); s.src = S.settings.url + 'sdk/smartkit-sdk.js?v=' + Date.now(); s.onload = res; s.onerror = () => rej(new Error('SDK Smart Kit tidak termuat dari ' + s.src)); document.head.append(s); });
  }
  async function setPreview(id, quiet, force) {
    const l = S.locations.find((x) => x.id === id);
    const frame = document.getElementById('shPreviewFrame'), ph = document.getElementById('shPreviewPh'), st = document.getElementById('shPreviewStatus');
    if (!l || !l.url) { previewLoc = ''; frame.removeAttribute('src'); ph.hidden = false; st.textContent = 'belum terhubung'; return; }
    if (previewLoc === id && !force && frame.getAttribute('src')) return;
    previewLoc = id; previewKit?.disconnect?.(); previewKit = null;
    document.getElementById('shPreviewOpen').href = l.url;
    ph.hidden = true; st.textContent = 'memuat tur…';
    frame.src = l.url + '?cms=' + Date.now();
    if (!scenesCache[id]) post('scenes', { location: id }).then((j) => { scenesCache[id] = j.scenes; if (tab === 'aksi') render(); }).catch(() => {});
    try {
      await loadSdk();
      const kit = await window.SmartKitSDK.connect(frame, { origin: new URL(l.url, location.href).origin });
      if (previewLoc !== id) return;
      previewKit = kit; st.textContent = 'terhubung · Smart Kit ' + (kit.info?.version || '');
      kit.on('scene', (e) => log('scene → ' + e.id)); kit.on('feature:open', (e) => log('popup dibuka: ' + e.id)); kit.on('action', (e) => log('aksi: ' + (e.id || e.type))); kit.on('custom', (e) => log('event kustom: ' + e.name));
      if (!quiet) toast('Pratinjau terhubung ke ' + l.name);
      if (tab === 'aksi') render();
    } catch (e) { st.textContent = 'gagal terhubung'; log(e.message); }
  }
  function log(msg) { const b = document.getElementById('shLog'); b.querySelector('small')?.remove(); b.prepend(h('div', null, h('time', null, new Date().toLocaleTimeString('id-ID')), ' ', msg)); while (b.children.length > 40) b.lastChild.remove(); }

  window.SmartHubApi = { h, post, toast, copy, state: () => S, previewLoc: () => previewLoc, reloadPreview, render: () => render(), tab: () => tab };
  loadHub();
  render();
})();
