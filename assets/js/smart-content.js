/* smart-content.js — GUI "Isi Konten" (per lokasi) dan "Data Wilayah" di CMS › Smart Hub.
 * Definisi bidang diambil dari smart-kit/studio/ui/schema.js (api.php?action=smart-schema), jadi satu sumber dengan Studio:
 * menambah bidang di schema.js otomatis muncul di sini. Data disimpan lewat smart-hub.php (op content_*, region_*), dibersihkan & dicadangkan server. */
(() => {
  'use strict';
  const A = () => window.SmartHubApi;
  let SCHEMA = null;
  const get = (o, p) => String(p).split('.').reduce((a, k) => (a == null ? a : a[k]), o);
  const setp = (o, p, v) => { const ks = String(p).split('.'); let c = o; ks.slice(0, -1).forEach((k) => { c = c[k] ??= {}; }); if (v === undefined || v === '' || v === null) delete c[ks.at(-1)]; else c[ks.at(-1)] = v; };
  const ensure = (o, p, d) => { const ks = String(p).split('.'); let c = o; ks.slice(0, -1).forEach((k) => { c = c[k] ??= {}; }); return (c[ks.at(-1)] ??= d); };
  const loadSchema = async () => (SCHEMA ||= await import(new URL('api.php?action=smart-schema&v=' + Date.now(), location.href).href));

  /* ---------------- bidang ---------------- */
  const hoursText = (v) => (Array.isArray(v) ? (v[0] === 0 && v[1] === 24 ? '24 jam' : `${v[0]}-${v[1]}`) : '');
  const parseHours = (s) => { s = String(s).trim().toLowerCase(); if (!s) return undefined; if (s.includes('24')) return [0, 24]; const m = s.match(/^(\d{1,2})(?:[.:]\d{2})?\s*[-–]\s*(\d{1,2})(?:[.:]\d{2})?$/); return m ? [+m[1], +m[2]] : undefined; };
  const normWa = (s) => { let d = String(s).replace(/\D+/g, ''); if (d.startsWith('0')) d = '62' + d.slice(1); return d; };

  function control(f, obj, onChange, ctx) {
    const { h } = A();
    const val = get(obj, f.k), upd = (v) => { setp(obj, f.k, v); onChange(); };
    const names = ctx.scenes || [];
    let el;
    switch (f.type) {
      case 'textarea': el = h('textarea', { rows: 3, oninput: (e) => upd(e.target.value) }, val ?? ''); break;
      case 'number': el = h('input', { type: 'number', step: 'any', value: val ?? '', oninput: (e) => upd(e.target.value === '' ? undefined : +e.target.value) }); break;
      case 'select': el = h('select', { onchange: (e) => upd(f.num ? +e.target.value : e.target.value) }, h('option', { value: '' }, '—'), (f.opts || []).map(([v, t]) => h('option', { value: v, selected: String(val) === String(v) ? true : null }, t))); break;
      case 'tags': el = h('input', { value: (val || []).join(', '), placeholder: 'pisahkan dengan koma', oninput: (e) => upd(e.target.value.split(',').map((x) => x.trim()).filter(Boolean)) }); break;
      case 'lines': el = h('textarea', { rows: 4, placeholder: 'satu butir per baris', oninput: (e) => upd(e.target.value.split('\n').map((x) => x.trim()).filter(Boolean)) }, (val || []).join('\n')); break;
      case 'scene': {
        const th = h('img', { class: 'sc-thumb', alt: '', hidden: true });
        const show = (id) => { const s = names.find((x) => x.id === id); th.hidden = !(s && s.thumb); if (s?.thumb) th.src = s.thumb; };
        el = h('div', { class: 'sc-scene' }, h('select', { onchange: (e) => { upd(e.target.value); show(e.target.value); } }, h('option', { value: '' }, '— tidak ada —'), names.map((s) => h('option', { value: s.id, selected: val === s.id ? true : null }, `${s.name} (${s.id})`))), th);
        show(val); break;
      }
      case 'image': {
        const img = h('img', { class: 'sc-thumb', alt: '', hidden: !val, src: val ? ctx.assetUrl(val) : '' });
        const file = h('input', { type: 'file', accept: 'image/png,image/jpeg,image/webp', hidden: true, onchange: async (e) => {
          const picked = e.target.files?.[0]; if (!picked) return;
          if (picked.size > 5 * 1024 * 1024) { A().toast('Gambar maksimal 5 MB.', true); e.target.value = ''; return; }
          const reader = new FileReader(); reader.onload = async () => {
            try { const j = await A().post('content_upload_image', { location: ctx.location, data: reader.result }); upd(j.ref); img.src = j.url; img.hidden = false; A().toast(j.message); }
            catch (error) { A().toast(error.message, true); } finally { e.target.value = ''; }
          }; reader.readAsDataURL(picked);
        } });
        el = h('div', { class: 'sc-scene' }, img, h('div', { class: 'sh-actions' }, h('button', { type: 'button', class: 'translate', onclick: () => file.click() }, val ? 'Ganti gambar' : 'Unggah gambar'), val ? h('button', { type: 'button', class: 'danger', onclick: () => { upd(undefined); img.hidden = true; } }, 'Hapus') : null), file);
        break;
      }
      case 'scenes': el = scenesPicker(val || [], names, (v) => upd(v.length ? v : undefined)); break;
      case 'bool': return h('label', { class: 'sh-check sc-bool' }, h('input', { type: 'checkbox', checked: !!val, onchange: (e) => upd(e.target.checked ? true : (f.keepFalse ? false : undefined)) }), f.label);
      case 'color': el = h('input', { type: 'color', value: /^#[0-9a-f]{6}$/i.test(val || '') ? val : '#1a82d8', oninput: (e) => upd(e.target.value) }); break;
      case 'date': el = h('input', { type: 'date', value: val || '', oninput: (e) => upd(e.target.value) }); break;
      case 'hours': el = h('input', { value: hoursText(val), placeholder: '08-20 atau 24 jam', oninput: (e) => upd(parseHours(e.target.value)) }); break;
      case 'json': el = h('textarea', { rows: 3, spellcheck: 'false', oninput: (e) => { try { upd(JSON.parse(e.target.value || '{}')); e.target.classList.remove('is-bad'); } catch { e.target.classList.add('is-bad'); } } }, JSON.stringify(val ?? {})); break;
      default: el = h('input', { value: val ?? '', ...(f.k === 'wa' ? { inputmode: 'numeric', placeholder: '628123456789', onblur: (e) => { const n = normWa(e.target.value); e.target.value = n; upd(n || undefined); } } : {}), oninput: (e) => upd(f.k === 'wa' ? e.target.value.replace(/\D+/g, '') || undefined : e.target.value) });
    }
    return h('label', { class: 'sh-field sc-f' + (f.wide ? ' is-wide' : '') }, h('span', null, f.label), el, f.hint ? h('small', null, f.hint) : null);
  }

  function scenesPicker(list, names, onSet) {
    const { h } = A();
    const wrap = h('div', { class: 'sc-chips' });
    const nm = (id) => names.find((s) => s.id === id)?.name || id;
    const draw = () => {
      wrap.replaceChildren(
        h('ol', null, list.map((id, i) => h('li', null, h('span', null, nm(id), h('code', null, id)),
          h('span', { class: 'sh-actions' },
            h('button', { type: 'button', class: 'translate', disabled: i === 0 || null, 'aria-label': 'Naik', onclick: () => { list.splice(i - 1, 0, list.splice(i, 1)[0]); onSet([...list]); draw(); } }, '↑'),
            h('button', { type: 'button', class: 'translate', disabled: i === list.length - 1 || null, 'aria-label': 'Turun', onclick: () => { list.splice(i + 1, 0, list.splice(i, 1)[0]); onSet([...list]); draw(); } }, '↓'),
            h('button', { type: 'button', class: 'danger', 'aria-label': 'Hapus', onclick: () => { list.splice(i, 1); onSet([...list]); draw(); } }, '×'))))),
        h('select', { onchange: (e) => { if (e.target.value) { list.push(e.target.value); onSet([...list]); draw(); } } }, h('option', { value: '' }, '＋ tambah scene…'), names.map((s) => h('option', { value: s.id }, `${s.name} (${s.id})`))));
    };
    draw();
    return wrap;
  }

  const fieldsGrid = (fields, obj, onChange, ctx) => A().h('div', { class: 'sc-grid' }, fields.map((f) => control(f, obj, onChange, ctx)));

  const profileGroups = [
    ['Identitas Lokasi', 'Identitas utama yang digunakan di seluruh pengalaman tur.', 'map-pin', ['short', 'place', 'slug', 'type']],
    ['Wilayah', 'Administrasi wilayah lokasi.', 'landmark', ['kecamatan', 'desa']],
    ['Koordinat', 'Titik GPS utama untuk peta, jarak, dan informasi matahari.', 'locate-fixed', ['center.lat', 'center.lng']],
    ['Operasional', 'Informasi penting sebelum pengunjung datang.', 'clock-3', ['hours', 'ticket', 'best', 'contact']],
    ['Informasi Pengunjung', 'Akses, aturan, dan panduan singkat di lokasi.', 'circle-help', ['access', 'permit', 'tips', 'about.tag']],
    ['Tampilan', 'Gambar latar yang dipakai pada panel informasi.', 'image', ['infoImage']],
  ];
  const requiredProfile = ['short', 'slug', 'type', 'center.lat', 'center.lng', 'hours', 'ticket', 'best', 'contact', 'access', 'permit', 'about.text'];
  const fieldByKey = (fields, key) => fields.find((f) => f.k === key);
  const sectionCard = (title, desc, icon, content, extraClass = '') => A().h('section', { class: 'sc-profile-card ' + extraClass, 'data-profile-card': title },
    A().h('header', null, A().h('i', { 'data-lucide': icon, 'aria-hidden': 'true' }), A().h('div', null, A().h('h3', null, title), A().h('p', null, desc))), content);

  function profileField(f, obj, mark, ctx) {
    const { h } = A();
    const required = requiredProfile.includes(f.k);
    const node = control({ ...f, label: f.label.replace(/\s*\*.*$/, '') + (required ? ' *' : ''), wide: ['access', 'permit', 'tips'].includes(f.k) || f.wide }, obj, mark, ctx);
    node.dataset.fieldKey = f.k;
    if (required && (get(obj, f.k) === undefined || get(obj, f.k) === '')) node.classList.add('is-required-empty');
    const input = node.querySelector('input,select,textarea');
    input?.addEventListener('input', () => node.classList.toggle('is-required-empty', required && !String(input.value).trim()));
    if (f.k === 'hours' || f.k === 'ticket') {
      const preset = f.k === 'hours' ? '24 jam' : 'Gratis';
      node.append(h('button', { type: 'button', class: 'sc-preset', onclick: () => { input.value = preset; input.dispatchEvent(new Event('input', { bubbles: true })); } }, preset));
    }
    return node;
  }

  function droneCard(fields, obj, mark, ctx) {
    const { h } = A();
    const enabledKey = 'jakartaRuntime.has_drone', nodeKey = 'jakartaRuntime.drone';
    const enabled = !!get(obj, enabledKey);
    const body = h('div', { class: 'sc-drone-body', hidden: !enabled });
    const thumb = h('img', { class: 'sc-drone-thumb', alt: 'Pratinjau panorama drone', hidden: true });
    const showScene = (id) => { const scene = ctx.scenes.find((s) => s.id === id); thumb.hidden = !scene?.thumb; if (scene?.thumb) thumb.src = scene.thumb; };
    const select = h('select', { onchange: (e) => {
      const scene = ctx.scenes.find((s) => s.id === e.target.value); setp(obj, nodeKey, e.target.value || undefined);
      if (scene?.start) { setp(obj, 'jakartaRuntime.drone_pan', Number(scene.start.pan ?? 0)); setp(obj, 'jakartaRuntime.drone_tilt', Number(scene.start.tilt ?? 0)); setp(obj, 'jakartaRuntime.drone_fov', Number(scene.start.fov ?? 70)); }
      showScene(e.target.value); mark(); drawRanges();
    } }, h('option', { value: '' }, '— Pilih panorama drone —'), ctx.scenes.map((s) => h('option', { value: s.id, selected: get(obj, nodeKey) === s.id ? true : null }, `${s.name} (${s.id})`)));
    const ranges = h('div', { class: 'sc-drone-ranges' });
    const drawRanges = () => {
      ranges.replaceChildren(...[['drone_pan', 'Pan', -180, 180, 0], ['drone_tilt', 'Tilt', -90, 90, 0], ['drone_fov', 'FoV', 20, 120, 70]].map(([key, label, min, max, fallback]) => {
        const path = 'jakartaRuntime.' + key, value = Number(get(obj, path) ?? fallback), out = h('output', null, value.toFixed(1) + '°');
        return h('label', { class: 'sc-range' }, h('span', null, label, out), h('input', { type: 'range', min, max, step: '.1', value, oninput: (e) => { setp(obj, path, +e.target.value); out.textContent = (+e.target.value).toFixed(1) + '°'; mark(); } }));
      }));
    };
    body.append(h('label', { class: 'sc-f is-wide' }, h('span', null, 'Node drone'), h('div', { class: 'sc-drone-select' }, select, thumb), h('small', null, 'Pilihan panorama dibaca dari pano XML lokasi melalui paket scene Smart Kit.')), ranges);
    drawRanges(); showScene(get(obj, nodeKey));
    const toggle = h('label', { class: 'sc-toggle' }, h('input', { type: 'checkbox', checked: enabled, onchange: (e) => { setp(obj, enabledKey, e.target.checked); body.hidden = !e.target.checked; mark(); } }), h('i', null), h('span', null, 'Aktifkan drone'));
    return h('section', { class: 'sc-profile-card sc-drone-card' }, h('header', null, h('i', { 'data-lucide': 'navigation', 'aria-hidden': 'true' }), h('div', null, h('h3', null, 'Drone'), h('p', null, 'Pilih panorama dan atur arah tampilan drone.')), toggle), h('span', { class: 'sc-sync-badge' }, h('i', { 'data-lucide': 'refresh-cw' }), 'Tersinkron dengan Data Lokasi'), body);
  }

  function profileEditor(blockDef, root, mark, ctx) {
    const { h } = A(), obj = get(root, blockDef.path) || {}, fields = blockDef.fields;
    const changed = () => { if (!get(root, blockDef.path)) setp(root, blockDef.path, obj); mark(); };
    const cards = profileGroups.map(([title, desc, icon, keys]) => sectionCard(title, desc, icon, h('div', { class: 'sc-profile-grid' }, keys.map((key) => profileField(fieldByKey(fields, key), obj, changed, ctx)))));
    cards.push(droneCard(fields, obj, changed, ctx));
    const description = fieldByKey(fields, 'about.text'), descriptionField = profileField(description, obj, changed, ctx), textarea = descriptionField.querySelector('textarea');
    const counter = h('span', { class: 'sc-char-counter' });
    const count = () => { const n = textarea.value.length; counter.textContent = `${n} / 250–400`; counter.classList.toggle('is-bad', n < 250 || n > 400); };
    textarea.addEventListener('input', count); count(); descriptionField.append(counter);
    cards.push(sectionCard('Deskripsi', 'Ringkasan utama yang tampil pada panel Tentang dan sambutan lokasi.', 'align-left', descriptionField, 'sc-description-card'));
    queueMicrotask(() => window.lucide?.createIcons());
    return h('div', { class: 'sc-profile-sections' }, cards);
  }

  /* ---------------- blok: objek, daftar, kamus ---------------- */
  function block(b, root, mark, ctx, rerender) {
    const { h } = A();
    const wrap = h('div', { class: 'sc-block' });
    if (b.label) wrap.append(h('h4', null, b.label));
    if (b.kind === 'object') { const ex = get(root, b.path), o = ex || {}; wrap.append(fieldsGrid(b.fields, o, () => { if (!get(root, b.path)) setp(root, b.path, o); mark(); }, ctx)); return wrap; }
    const isDict = b.kind === 'dict', ex = get(root, b.path);
    const data = ex || (isDict ? {} : []);
    const attach = () => { if (!get(root, b.path)) setp(root, b.path, data); };
    const rows = isDict ? Object.entries(data).map(([k, v]) => ({ ...v, [b.key]: k })) : data;
    const toDict = () => { if (!isDict) return; const out = {}; rows.forEach((r) => { const { [b.key]: k, ...rest } = r; out[k || ''] = rest; }); const ks = b.path.split('.'); let c = root; ks.slice(0, -1).forEach((x) => { c = c[x] ??= {}; }); c[ks.at(-1)] = out; };
    const search = h('input', { type: 'search', class: 'sc-search', placeholder: 'Cari di daftar ini…', hidden: rows.length < 8 || null });
    const list = h('div', { class: 'sc-list' });
    const draw = () => {
      const q = search.value.trim().toLowerCase();
      list.replaceChildren();
      rows.forEach((r, i) => {
        const title = (b.title ? b.title(r) : '') || '(kosong)';
        if (q && !JSON.stringify(r).toLowerCase().includes(q)) return;
        const kf = isDict ? { k: b.key, label: b.key === 'scene' ? 'Scene' : 'ID', type: b.keyType } : null;
        const det = h('details', { class: 'sc-item' }, h('summary', null, h('span', { class: 'sc-item__t' }, title),
          h('span', { class: 'sc-item__a' }, h('button', { type: 'button', class: 'danger', title: 'Hapus baris', onclick: (e) => { e.preventDefault(); if (!confirm('Hapus baris ini?')) return; rows.splice(i, 1); if (isDict) toDict(); mark(); rerender(); } }, 'Hapus'))),
          h('div', { class: 'sc-item__b' }, fieldsGrid(kf ? [kf, ...b.fields] : b.fields, r, () => { if (isDict) toDict(); else attach(); mark(); const t = det.querySelector('.sc-item__t'); if (t) t.textContent = (b.title ? b.title(r) : '') || '(kosong)'; }, ctx)));
        if (q || (rows.length === 1)) det.open = true;
        list.append(det);
      });
      if (!rows.length) list.append(h('div', { class: 'sh-empty' }, 'Belum ada isi. Tekan "＋ Tambah".'));
    };
    search.addEventListener('input', draw);
    const add = h('button', { type: 'button', class: 'save', onclick: () => {
      let n = rows.length + 1; const ids = new Set(rows.map((r) => r.id)); let row = b.make(n); while (row.id && ids.has(row.id)) row = b.make(++n);
      if (isDict) { rows.push({ ...row, [b.key]: '' }); toDict(); } else { rows.push(row); attach(); }
      mark(); rerender(true);
    } }, '＋ Tambah');
    wrap.append(h('div', { class: 'sc-toolbar' }, search, add, b.path === 'SERVICES' ? csvTools(rows, attach, mark, ctx, rerender) : null), list);
    draw();
    return wrap;
  }

  /* ---------------- CSV layanan (Excel/Google Sheets) ---------------- */
  const CSV_COLS = ['id', 'cat', 'name', 'sub', 'price', 'hours', 'wa', 'addr', 'lat', 'lng', 'scene', 'langs', 'tags', 'verified'];
  const csvEsc = (v) => { const s = v == null ? '' : String(v); return /[",;\n\r]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s; };
  function parseCsv(text) {
    text = text.replace(/^﻿/, '');
    const first = text.split(/\r?\n/, 1)[0]; const sep = (first.match(/;/g) || []).length > (first.match(/,/g) || []).length ? ';' : ',';
    const rows = []; let row = [], cur = '', q = false;
    for (let i = 0; i < text.length; i++) {
      const c = text[i];
      if (q) { if (c === '"') { if (text[i + 1] === '"') { cur += '"'; i++; } else q = false; } else cur += c; }
      else if (c === '"') q = true; else if (c === sep) { row.push(cur); cur = ''; } else if (c === '\n') { row.push(cur); rows.push(row); row = []; cur = ''; } else if (c !== '\r') cur += c;
    }
    if (cur !== '' || row.length) { row.push(cur); rows.push(row); }
    return rows.filter((r) => r.some((x) => String(x).trim() !== ''));
  }
  function csvTools(rows, attach, mark, ctx, rerender) {
    const { h, toast } = A();
    const cats = ctx.svcCats || [];
    const download = (name, text) => { const a = h('a', { href: URL.createObjectURL(new Blob(['﻿' + text], { type: 'text/csv;charset=utf-8' })), download: name }); document.body.append(a); a.click(); a.remove(); };
    const toRow = (r) => CSV_COLS.map((k) => (k === 'hours' ? hoursText(r.hours) : Array.isArray(r[k]) ? r[k].join(', ') : k === 'verified' ? (r.verified ? 'ya' : '') : r[k])).map(csvEsc).join(',');
    const panel = h('div', { class: 'sc-csv', hidden: true });
    const file = h('input', { type: 'file', accept: '.csv,text/csv', hidden: true, onchange: async (e) => {
      const f = e.target.files[0]; e.target.value = ''; if (!f) return;
      const all = parseCsv(await f.text()); if (all.length < 2) return toast('CSV kosong atau tanpa baris data.', true);
      const head = all[0].map((x) => x.trim().toLowerCase());
      if (!head.includes('name')) return toast('Kolom "name" tidak ditemukan. Unduh template CSV untuk contoh format.', true);
      const catId = (v) => { v = String(v).trim(); const c = cats.find(([id, t]) => id === v || t.toLowerCase() === v.toLowerCase()); return c ? c[0] : ''; };
      const out = []; const bad = [];
      all.slice(1).forEach((r, i) => {
        const g = (k) => String(r[head.indexOf(k)] ?? '').trim(); if (!g('name')) return;
        const o = { id: g('id') || '', cat: catId(g('cat')), name: g('name') };
        if (g('cat') && !o.cat) bad.push(`baris ${i + 2}: kategori "${g('cat')}" tidak dikenal (pilihan: ${cats.map(([, t]) => t).join(', ')}); isi manual setelah impor`);
        if (g('sub')) o.sub = g('sub'); if (g('price')) o.price = g('price'); if (g('addr')) o.addr = g('addr'); if (g('scene')) o.scene = g('scene');
        const hrs = parseHours(g('hours')); if (hrs) o.hours = hrs; else if (g('hours')) bad.push(`baris ${i + 2}: jam "${g('hours')}" tidak dikenali (pakai 08-20 atau 24 jam)`);
        if (g('wa')) o.wa = normWa(g('wa'));
        for (const k of ['lat', 'lng']) if (g(k)) { const n = parseFloat(g(k).replace(',', '.')); if (Number.isFinite(n)) o[k] = n; else bad.push(`baris ${i + 2}: ${k} bukan angka`); }
        for (const k of ['langs', 'tags']) if (g(k)) o[k] = g(k).split(/[,;|]/).map((x) => x.trim()).filter(Boolean);
        if (/^(ya|y|true|1|x)$/i.test(g('verified'))) o.verified = true;
        out.push(o);
      });
      if (!out.length) return toast('Tidak ada baris dengan nama usaha.', true);
      const mode = { v: 'add' };
      panel.hidden = false;
      panel.replaceChildren(...[h('b', null, `${out.length} baris siap diimpor`), bad.length ? h('ul', { class: 'sc-csv__bad' }, bad.slice(0, 8).map((x) => h('li', null, x))) : null,
        h('label', { class: 'sh-check' }, h('input', { type: 'radio', name: 'csvmode', checked: true, onchange: () => (mode.v = 'add') }), `Tambahkan ke daftar yang ada (${rows.length} baris sekarang)`),
        h('label', { class: 'sh-check' }, h('input', { type: 'radio', name: 'csvmode', onchange: () => (mode.v = 'replace') }), 'Ganti seluruh daftar'),
        h('div', { class: 'sh-actions', style: 'justify-content:flex-start' },
          h('button', { type: 'button', class: 'save', onclick: () => {
            if (mode.v === 'replace') rows.splice(0, rows.length);
            const used = new Set(rows.map((r) => r.id).filter(Boolean)); let n = rows.length;
            out.forEach((o) => { if (!o.id || used.has(o.id)) { do { o.id = 'sv-' + (++n); } while (used.has(o.id)); } used.add(o.id); rows.push(o); });
            attach(); mark(); panel.hidden = true; toast(`${out.length} layanan diimpor. Periksa lalu tekan "Simpan konten".`); rerender(true);
          } }, 'Impor sekarang'), h('button', { type: 'button', class: 'translate', onclick: () => (panel.hidden = true) }, 'Batal'))].filter(Boolean));
    } });
    return h('span', { class: 'sc-csvbar' },
      h('button', { type: 'button', class: 'translate', title: 'Buka di Excel / Google Sheets, isi, lalu impor kembali', onclick: () => download('layanan-template.csv', CSV_COLS.join(',') + '\n' + ['sv-1', cats[0]?.[0] || 'food', 'Warung Contoh', 'Masakan rumahan', 'Rp15.000–35.000', '08-21', '6281234567890', 'Jl. Contoh No. 1', '-6.1755', '106.8286', '', 'ID, EN', 'makan siang, keluarga', 'ya'].map(csvEsc).join(',')) }, '⬇ Template CSV'),
      h('button', { type: 'button', class: 'translate', onclick: () => file.click() }, '⬆ Impor CSV'), file,
      h('button', { type: 'button', class: 'translate', disabled: !rows.length || null, onclick: () => download('layanan.csv', CSV_COLS.join(',') + '\n' + rows.map(toRow).join('\n')) }, '⬇ Ekspor CSV'), panel);
  }

  /* ---------------- tampilan: Isi Konten (per lokasi) ---------------- */
  const ST = { loc: '', data: null, sec: '', dirty: false, busy: false };
  window.__shContentDirty = () => ST.dirty;

  async function viewKonten(main) {
    const { h, post, toast, state } = A();
    const S = state();
    const locs = S.locations;
    if (!locs.length) return main.append(h('div', { class: 'card sh-card sh-warn' }, 'Belum ada lokasi (tur) di project ini.'));
    let schema;
    try { schema = await loadSchema(); } catch (e) { return main.append(h('div', { class: 'card sh-card sh-warn' }, 'Definisi formulir tidak termuat: ' + e.message)); }
    if (!ST.loc || !locs.some((l) => l.id === ST.loc)) ST.loc = A().previewLoc() || (locs.find((l) => l.pack.exists) || locs[0]).id;
    const box = h('div');
    if (A().tab() !== 'konten') return;
    main.append(box);
    box.append(h('div', { class: 'sh-empty' }, 'Memuat konten…'));
    let d;
    if (!ST.data || ST.data.loc !== ST.loc) {
      try { d = await post('content_get', { location: ST.loc }); if (A().tab() !== 'konten') return; } catch (e) { box.replaceChildren(headerBar(locs, box, main), h('div', { class: 'card sh-card sh-warn' }, e.message)); return; }
      ST.data = { loc: ST.loc, content: d.content, scenes: d.scenes, hasPack: d.hasPack, warnings: d.warnings }; ST.dirty = false;
    }
    d = ST.data;
    const ctx = { scenes: d.scenes, svcCats: schema.SVC_CATS, location: ST.loc, assetUrl: (ref) => /^https?:|^data:|^\//.test(ref || '') ? ref : S.settings.url + 'packs/' + S.settings.wilayah + '/' + ST.loc + '/' + String(ref || '').replace(/^\.\//, '') };
    const sections = schema.SECTIONS.filter((s) => !s.pins);
    if (!ST.sec || !sections.some((s) => s.id === ST.sec)) ST.sec = sections[0].id;
    let sec = sections.find((s) => s.id === ST.sec);
    const mark = () => { ST.dirty = true; saveBtn.disabled = false; saveBtn.classList.add('is-dirty'); saveBtn.textContent = 'Simpan konten'; box.querySelector('.sc-dirty-state')?.classList.add('is-dirty'); };
    const countOf = (s) => s.blocks.reduce((n, b) => { const v = get(d.content, b.path); return n + (Array.isArray(v) ? v.length : v && typeof v === 'object' ? (b.kind === 'dict' ? Object.keys(v).length : 1) : 0); }, 0);
    const saveBtn = h('button', { type: 'button', class: 'save', disabled: true, onclick: save }, 'Simpan konten');
    async function save() {
      saveBtn.disabled = true;
      try { const j = await post('content_save', { location: ST.loc, content: d.content }); d.content = j.content; d.warnings = j.warnings; ST.dirty = false; toast(j.message + (j.warnings.length ? ` ${j.warnings.length} catatan di bawah.` : ' Tidak ada catatan.')); A().reloadPreview(); box.replaceChildren(...build().filter(Boolean)); }
      catch (e) { toast(e.message, true); } finally { saveBtn.disabled = false; }
    }
    const panel = h('div', { class: 'sc-panel' });
    const paint = () => {
      panel.replaceChildren(h('p', { class: 'sc-hint' }, sec.hint || ''), ...sec.blocks.map((b) => sec.id === 'Profil' && b.kind === 'object' && b.path === 'REGION'
        ? profileEditor(b, d.content, mark, ctx)
        : h('div', { class: 'card sh-card' }, block(b, d.content, mark, ctx, () => { paint(); }))));
    };
    function build() {
      sec = sections.find((s) => s.id === ST.sec) || sections[0];
      const navIcons = ['user-round', 'lightbulb', 'store', 'badge-help', 'shield-check', 'route', 'calendar-days', 'images', 'stamp', 'layers-3'];
      const nav = h('nav', { class: 'sc-nav', 'aria-label': 'Bagian konten' }, sections.map((s, i) => { const n = countOf(s); return h('button', { type: 'button', class: s.id === ST.sec ? 'is-on' : '', onclick: () => { ST.sec = s.id; box.replaceChildren(...build().filter(Boolean)); } }, h('span', null, h('i', { 'data-lucide': navIcons[i] || 'file-text', 'aria-hidden': 'true' }), s.label), n ? h('b', null, String(n)) : null); }));
      paint();
      queueMicrotask(() => window.lucide?.createIcons());
      return [headerBar(locs, box, main, saveBtn, d), !d.hasPack ? h('div', { class: 'card sh-card sh-warn' }, 'Paket lokasi ini belum dibangun, jadi daftar scene kosong. Buka tab "Lokasi & Paket" lalu tekan "Bangun / perbarui paket".') : null, h('div', { class: 'sc-layout' }, nav, h('div', { class: 'sc-main' }, panel))];
    }
    box.replaceChildren(...build().filter(Boolean));
  }

  function headerBar(locs, box, main, saveBtn, d) {
    const { h } = A();
    const sel = h('select', { 'aria-label': 'Lokasi', onchange: (e) => { if (ST.dirty && !confirm('Ada perubahan yang belum disimpan. Pindah lokasi tanpa menyimpan?')) { e.target.value = ST.loc; return; } ST.dirty = false; ST.loc = e.target.value; ST.data = null; A().render(); } }, locs.map((l) => h('option', { value: l.id, selected: l.id === ST.loc ? true : null }, `${l.name}${l.pack.exists ? '' : ' (paket belum dibangun)'}`)));
    const requiredMissing = requiredProfile.filter((key) => { const value = get(d.content.REGION || {}, key); return value === undefined || value === ''; }).length;
    return h('div', { class: 'sc-head' }, h('div', { class: 'sc-head__location' }, h('span', null, 'Lokasi aktif'), sel), h('div', { class: 'sc-head__r' },
      h('button', { type: 'button', class: 'sc-completeness', onclick: () => document.querySelector('.is-required-empty')?.scrollIntoView({ behavior: 'smooth', block: 'center' }) }, requiredMissing ? `${requiredMissing} field wajib belum terisi` : 'Profil wajib lengkap'),
      h('span', { class: 'sc-dirty-state' }, h('i', null), 'Tidak ada perubahan'), saveBtn || null));
  }

  /* ---------------- tampilan: Data Wilayah ---------------- */
  const RG = { data: null, dirty: false };
  async function viewWilayah(main) {
    const { h, post, toast, state } = A();
    const S = state();
    let schema;
    try { schema = await loadSchema(); } catch (e) { return main.append(h('div', { class: 'card sh-card sh-warn' }, 'Definisi formulir tidak termuat: ' + e.message)); }
    if (!RG.data) { try { const j = await post('region_get'); RG.data = { region: j.region, exists: j.exists }; RG.dirty = false; } catch (e) { return main.append(h('div', { class: 'card sh-card sh-warn' }, e.message)); } }
    window.__shContentDirty = () => ST.dirty || RG.dirty;
    if (A().tab() !== 'wilayah') return;
    const reg = RG.data.region; const ctx = { scenes: [] };
    const mark = () => { RG.dirty = true; saveBtn.classList.add('is-dirty'); saveBtn.textContent = 'Simpan data wilayah ●'; };
    const saveBtn = h('button', { type: 'button', class: 'save', onclick: async () => { saveBtn.disabled = true; try { const j = await post('region_save', { region: reg }); RG.data.region = j.region; RG.dirty = false; toast(j.message); A().render(); } catch (e) { toast(e.message, true); } finally { saveBtn.disabled = false; } } }, 'Simpan data wilayah');
    const items = ensure(reg, 'DESTINATIONS.items', []);
    const destFields = [{ k: 'name', label: 'Nama destinasi', type: 'text' }, { k: 'cat', label: 'Jenis', type: 'select', opts: schema.DEST_CATS }, ...schema.DEST_FIELDS.filter((f) => !['name', 'cat'].includes(f.k))];
    const dest = h('div', { class: 'sc-list' });
    const drawDest = () => {
      dest.replaceChildren();
      items.forEach((dd, i) => dest.append(h('details', { class: 'sc-item' }, h('summary', null, h('span', { class: 'sc-item__t' }, `#${dd.n ?? i + 1} ${dd.name || '(tanpa nama)'}`), h('span', { class: 'sc-item__a' }, dd.lat == null ? h('em', null, 'koordinat belum ada') : h('em', null, dd.slug || ''))),
        h('div', { class: 'sc-item__b' }, fieldsGrid(destFields, dd, mark, ctx)))));
      if (!items.length) dest.append(h('div', { class: 'sh-empty' }, 'Belum ada destinasi. Tambahkan lewat tombol di bawah.'));
    };
    drawDest();
    const addDest = h('button', { type: 'button', class: 'save', onclick: () => { const n = Math.max(0, ...items.map((x) => +x.n || 0)) + 1; items.push({ n, name: '', cat: schema.DEST_CATS[0]?.[0] }); mark(); drawDest(); dest.lastElementChild.open = true; } }, '＋ Tambah destinasi');
    const blocks = h('div', { class: 'sc-panel' }, schema.REGION_BLOCKS.map((b) => h('div', { class: 'card sh-card' }, block(b, reg, mark, ctx, () => A().render()))));
    main.append(h('div', { class: 'card sh-card sc-head' }, h('div', null, h('h3', null, 'Data wilayah: ' + state().settings.wilayah), h('p', null, 'Dipakai bersama seluruh lokasi: daftar destinasi (menu Kategori), Trip Wizard, nomor darurat kabupaten, acara tingkat kabupaten. Tersimpan di _region.json.')), h('div', { class: 'sc-head__r' }, saveBtn)),
      blocks, h('div', { class: 'card sh-card' }, h('div', { class: 'sh-card__h' }, h('div', null, h('h3', null, `Destinasi (${items.length})`), h('p', null, 'Daftar yang tampil di tombol Kategori. Foto: letakkan di packs/<wilayah>/img/destinations/<nomor>.webp.')), addDest), dest));
  }

  window.SmartHubContent = { konten: viewKonten, wilayah: viewWilayah, reset: () => { ST.data = null; RG.data = null; } };
})();
