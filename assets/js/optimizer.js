/* optimizer.js — menu "Optimasi Loading" CMS. Memanggil optimizer.php; pekerjaan berjalan di latar belakang dan dipantau tiap detik. */
(() => {
  'use strict';
  const CSRF = JSON.parse(document.getElementById('opBoot').textContent).csrf;
  const $ = (id) => document.getElementById(id);
  const h = (tag, props, ...kids) => {
    const e = document.createElement(tag);
    for (const [k, v] of Object.entries(props || {})) { if (v == null || v === false) continue; if (k === 'class') e.className = v; else if (k.startsWith('on')) e.addEventListener(k.slice(2), v); else if (k === 'value') e.value = v; else if (k === 'checked') e.checked = !!v; else e.setAttribute(k, v === true ? '' : v); }
    for (const c of kids.flat(Infinity)) if (c != null && c !== false) e.append(c.nodeType ? c : document.createTextNode(String(c)));
    return e;
  };
  const toast = (m, bad) => { const t = $('opToast'); t.textContent = m; t.className = 'sh-toast' + (bad ? ' is-bad' : ''); t.hidden = false; clearTimeout(t._t); t._t = setTimeout(() => (t.hidden = true), bad ? 8000 : 3600); };
  async function call(op, data = {}) {
    const r = await fetch('api.php?action=optimizer', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf: CSRF, op, ...data }) });
    let j; try { j = await r.json(); } catch { throw new Error('Respons server tidak valid (' + r.status + ')'); }
    if (r.status === 401) { location.href = 'login.php'; throw new Error(j.message); }
    if (!j.success) throw new Error(j.message || 'Gagal');
    return j;
  }
  const LBL = { belum: 'Belum dioptimasi', lama: 'Versi lama (upgrade)', sudah: 'Sudah dioptimasi', sebagian: 'Setengah (perlu undo)' };
  const S = { tours: [], sel: new Set(), q: '', st: 'all', isAdmin: false, busy: false, state: null };

  function fmtBytes(n) { return n >= 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.round(n / 1024) + ' KB'; }

  /* ---------- status lingkungan ---------- */
  function paintEnv(d) {
    const pill = (ok, t) => h('span', { class: 'op-pill ' + (ok ? 'is-ok' : 'is-bad') }, (ok ? '✓ ' : '✗ ') + t);
    $('opEnv').replaceChildren(
      pill(!!d.node.path, d.node.path ? 'Node.js ' + d.node.version : 'Node.js tidak ditemukan'),
      pill(!!d.engine.dir, d.engine.dir ? 'Mesin (' + d.engine.source + ')' : 'Mesin tidak ditemukan'),
      d.engine.dir ? pill(d.ffmpeg, d.ffmpeg ? 'FFmpeg siap' : 'FFmpeg belum ada (kompres video nonaktif)') : null,
      h('span', { class: 'op-pill' }, d.project.name));
  }

  /* ---------- daftar ---------- */
  function visible() { const q = S.q.toLowerCase(); return S.tours.filter((t) => (S.st === 'all' || t.status === S.st) && (!q || t.name.toLowerCase().includes(q))); }
  function paintChips() {
    const cnt = {}; S.tours.forEach((t) => (cnt[t.status] = (cnt[t.status] || 0) + 1));
    const chip = (k, t, n) => h('button', { type: 'button', class: 'op-chip' + (S.st === k ? ' is-on' : ''), onclick: () => { S.st = k; paintChips(); paintRows(); } }, t, h('i', null, String(n)));
    $('opChips').replaceChildren(chip('all', 'Semua', S.tours.length), ...['belum', 'lama', 'sebagian', 'sudah'].filter((k) => cnt[k]).map((k) => chip(k, LBL[k], cnt[k])));
  }
  function paintRows() {
    const rows = visible();
    $('opRows').replaceChildren(...rows.map((t) => h('tr', { class: S.sel.has(t.name) ? 'is-sel' : '' },
      h('td', null, h('input', { type: 'checkbox', checked: S.sel.has(t.name), 'aria-label': 'Pilih ' + t.name, onchange: (e) => { e.target.checked ? S.sel.add(t.name) : S.sel.delete(t.name); paintRows(); } })),
      h('td', null, h('b', null, t.name)),
      h('td', null, h('span', { class: 'op-badge s-' + t.status }, LBL[t.status] || t.status)),
      h('td', null, t.backup ? h('span', { class: 'op-mini' }, 'ada backup') : h('span', { class: 'op-mini is-muted' }, '—')))));
    if (!rows.length) $('opRows').append(h('tr', null, h('td', { colspan: 4, class: 'op-empty' }, 'Tidak ada lokasi yang cocok.')));
    const n = S.sel.size, dis = !n || S.busy || !S.isAdmin;
    $('opSum').textContent = `${n} dipilih dari ${S.tours.length} lokasi`;
    $('opCheck').disabled = !n || S.busy || !S.isAdmin; $('opRun').disabled = dis; $('opRestore').disabled = dis;
    $('opVideo').disabled = dis || !S.state?.engine?.video || !S.state?.ffmpeg;
    $('opVideo').title = !S.state?.ffmpeg ? 'FFmpeg belum terpasang (lihat Pengaturan mesin)' : 'Perkecil video teaser dengan FFmpeg (beberapa menit per lokasi)';
    const all = $('opAllBox'); all.checked = rows.length > 0 && rows.every((t) => S.sel.has(t.name)); all.indeterminate = !all.checked && rows.some((t) => S.sel.has(t.name));
  }

  /* ---------- pekerjaan ---------- */
  let poll = null;
  async function startJob(action, label) {
    const dirs = [...S.sel];
    const msg = { optimize: `Optimasi ${dirs.length} lokasi? index.html dan skin.js diubah (backup otomatis, bisa di-undo).`, restore: `Kembalikan ${dirs.length} lokasi ke versi SEBELUM optimasi?\nPerubahan lain sesudahnya pada index.html/skin.js (mis. Smart Kit, skin template) ikut hilang.`, video: `Kompres video teaser ${dirs.length} lokasi? Butuh beberapa menit per lokasi. Video asli dipindah ke media/_original/ (jangan diupload).`, check: '' }[action];
    if (msg && !confirm(msg)) return;
    try {
      const j = await call('start', { action, dirs, opts: { sw: $('opSw').checked, autoSkip: $('opSkip').checked } });
      S.busy = true; paintRows(); watch(j.id, action, label, j.total);
    } catch (e) { toast(e.message, true); }
  }
  function watch(id, action, label, total) {
    $('opJob').hidden = false; $('opJobTitle').textContent = label; $('opJobSub').textContent = 'Mempersiapkan…'; $('opLog').textContent = ''; $('opResults').replaceChildren(); $('opBar').style.width = '2%';
    $('opCancel').hidden = !S.isAdmin; $('opCancel').onclick = async () => { if (!confirm('Hentikan pekerjaan? Lokasi yang sedang diproses mungkin setengah selesai.')) return; try { const r = await call('cancel', { id }); toast(r.message); } catch (e) { toast(e.message, true); } };
    $('opJob').scrollIntoView({ behavior: 'smooth', block: 'start' });
    let since = 0, pct = 0; clearInterval(poll);
    const tick = async () => {
      let j; try { j = await call('job', { id, since }); } catch (e) { return; }
      since = j.next; if (j.lines.length) { const log = $('opLog'); log.textContent += j.lines.join('\n') + '\n'; log.scrollTop = log.scrollHeight; }
      const tot = j.total || total || 1;
      for (const l of j.lines) { const m = /^\s+(\d{1,3})%$/.exec(l); if (m) pct = +m[1]; else if (/^==\s/.test(l)) pct = 0; }   // kemajuan ffmpeg dalam satu lokasi
      $('opBar').style.width = Math.max(2, Math.min(99, Math.round(((j.done + (j.state === 'running' ? pct / 100 : 0)) / tot) * 100))) + '%';
      $('opJobSub').textContent = `${j.done}/${tot} lokasi` + (j.current && j.state === 'running' ? ' · sedang: ' + j.current : '');
      if (j.state !== 'running') { clearInterval(poll); finish(j, label); }
    };
    poll = setInterval(tick, 1000); tick();
  }
  async function finish(j, label) {
    S.busy = false; $('opCancel').hidden = true; $('opBar').style.width = '100%';
    const ok = j.state === 'done';
    $('opJobSub').textContent = ok ? 'Selesai.' : (j.state === 'cancelled' ? 'Dihentikan.' : 'Berhenti dengan galat: ' + (j.error || ''));
    const cls = (s) => (s === 'OK' || s === 'RESTORED' || s === 'BISA' ? 's-sudah' : s === 'GAGAL' ? 's-sebagian' : 's-lama');
    if (j.results.length) $('opResults').replaceChildren(h('table', { class: 'op-table op-table--res' }, h('thead', null, h('tr', null, h('th', null, 'Lokasi'), h('th', null, 'Hasil'), h('th', null, 'Catatan'))),
      h('tbody', null, j.results.map((r) => h('tr', null, h('td', null, h('b', null, r.name)), h('td', null, h('span', { class: 'op-badge ' + cls(r.status) }, r.status)), h('td', null, (r.notes || []).map((n) => h('small', { class: 'op-note-line' }, n))))))));
    const c = (s) => j.results.filter((r) => r.status === s).length;
    toast(ok ? `${label}: ${c('OK') + c('RESTORED')} berhasil, ${c('SUDAH')} sudah, ${c('GAGAL')} gagal.` : $('opJobSub').textContent, !ok);
    await refresh();
  }

  /* ---------- pengaturan mesin ---------- */
  function paintSettings(d) {
    const box = $('opSettingsBox'); box.hidden = !d.isAdmin; if (!d.isAdmin) return;
    const s = { ...d.settings };
    const inp = (k, ph) => h('input', { value: s[k] || '', placeholder: ph, oninput: (e) => (s[k] = e.target.value) });
    $('opSettings').replaceChildren(
      h('p', { class: 'op-note' }, 'Kosongkan untuk deteksi otomatis. Isi bila CMS dijalankan sebagai layanan Windows/Apache yang tidak melihat PATH Anda.'),
      h('label', { class: 'sh-field' }, h('span', null, 'Jalur Node.js'), inp('node', 'C:/Program Files/nodejs/node.exe')),
      h('label', { class: 'sh-field' }, h('span', null, 'Folder FFmpeg (berisi ffmpeg.exe & ffprobe.exe)'), inp('ffmpeg_dir', 'C:/ffmpeg/bin')),
      h('label', { class: 'sh-field' }, h('span', null, 'Folder mesin optimasi (berisi optimize-tour.js)'), inp('engine', 'C:/xampp/htdocs/semarang')),
      h('div', { class: 'sh-bar' },
        h('button', { type: 'button', class: 'save', onclick: async () => { try { const r = await call('settings', { settings: s }); toast(r.message); await refresh(); } catch (e) { toast(e.message, true); } } }, 'Simpan pengaturan'),
        d.engine.dir && d.engine.source !== 'salinan di CMS' ? h('button', { type: 'button', class: 'translate', title: 'Salin optimize-tour.js dan optimize-video.js ke cms-virtual-tour/tools/optimizer agar CMS tidak bergantung pada folder sumber', onclick: async () => { try { const r = await call('install_engine'); toast(r.message); await refresh(); } catch (e) { toast(e.message, true); } } }, 'Salin mesin ke CMS') : null),
      d.engine.dir ? h('small', { class: 'op-mini' }, 'Mesin aktif: ' + d.engine.dir) : null);
  }

  /* ---------- muat ---------- */
  async function refresh() {
    let d; try { d = await call('state'); } catch (e) { $('opEnv').replaceChildren(h('span', { class: 'op-pill is-bad' }, e.message)); return; }
    S.state = d; S.tours = d.tours; S.isAdmin = d.isAdmin; S.busy = !!d.running;
    for (const n of [...S.sel]) if (!S.tours.some((t) => t.name === n)) S.sel.delete(n);
    paintEnv(d); paintSettings(d);
    $('opProblem').hidden = !d.problem; if (d.problem) $('opProblem').replaceChildren(h('b', null, 'Belum bisa dipakai'), h('p', null, d.problem));
    $('opMain').hidden = !!d.problem; $('opAdminNote').hidden = d.isAdmin;
    if (!d.problem) { paintChips(); paintRows(); }
    if (d.running && !poll) watch(d.running, 'optimize', 'Pekerjaan yang sedang berjalan', 0);
  }
  $('opSearch').addEventListener('input', (e) => { S.q = e.target.value; paintRows(); });
  $('opSelTodo').onclick = () => { S.sel = new Set(S.tours.filter((t) => t.status === 'belum' || t.status === 'lama').map((t) => t.name)); paintRows(); };
  $('opSelAll').onclick = () => { visible().forEach((t) => S.sel.add(t.name)); paintRows(); };
  $('opSelNone').onclick = () => { S.sel.clear(); paintRows(); };
  $('opAllBox').onchange = (e) => { visible().forEach((t) => (e.target.checked ? S.sel.add(t.name) : S.sel.delete(t.name))); paintRows(); };
  $('opCheck').onclick = () => startJob('check', 'Cek saja (tidak menulis apa pun)');
  $('opRun').onclick = () => startJob('optimize', 'Optimasi yang dipilih');
  $('opVideo').onclick = () => startJob('video', 'Kompres video teaser');
  $('opRestore').onclick = () => startJob('restore', 'Kembalikan (undo)');
  addEventListener('beforeunload', (e) => { if (S.busy) { e.preventDefault(); e.returnValue = ''; } });
  refresh();
})();
