/* inspector.js — tab "Inspector": skor, temuan, dan scan statis VT Inspector per tur lewat api.php?action=inspector. */
(() => {
    'use strict';
    const boot = JSON.parse(document.getElementById('inspectorBoot').textContent);
    const API = 'api.php?action=inspector';
    const csrf = boot.csrf;
    let S = boot.state;
    let query = '';
    let current = boot.selected && S.tours.some((t) => t.id === boot.selected) ? boot.selected : '';
    const busy = new Set();
    const detailCache = {};
    const $ = (s, r = document) => r.querySelector(s);

    const STATUS = { pass: ['Lulus', 'is-ok'], warn: ['Perhatian', 'is-warn'], fail: ['Gagal', 'is-bad'] };
    const SEV = { blocker: 'Blocker', major: 'Major', minor: 'Minor', info: 'Info' };
    // Tab CMS yang boleh dituju tautan balik dari temuan Inspector.
    const TAB_OK = new Set(['smart', 'aihub', 'audit', 'lokasi', 'skin', 'object', 'voice', 'video', 'languages', 'translations', 'loading', 'custom']);
    const PER_LOKASI = new Set(['audit', 'lokasi', 'skin', 'object', 'voice', 'video']);

    const h = (tag, attrs = {}, ...kids) => {
        const el = document.createElement(tag);
        for (const [k, v] of Object.entries(attrs || {})) {
            if (v === false || v == null) continue;
            if (k === 'class') el.className = v;
            else if (k.startsWith('on')) el.addEventListener(k.slice(2), v);
            else el.setAttribute(k, v === true ? '' : v);
        }
        for (const c of kids.flat(Infinity)) if (c != null && c !== false) el.append(c.nodeType ? c : document.createTextNode(String(c)));
        return el;
    };
    const icon = (name) => h('i', { 'data-lucide': name, 'aria-hidden': 'true' });
    const refreshIcons = () => queueMicrotask(() => window.lucide?.createIcons({ attrs: { 'stroke-width': 2 } }));
    const fmtDate = (iso) => { const d = new Date(iso); return iso && !isNaN(d) ? d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'; };
    const scoreClass = (r) => 'iv-score is-' + (r.status || (r.score >= 85 ? 'pass' : r.score >= 60 ? 'warn' : 'fail'));
    const canScan = () => S.settings.pathOk && S.settings.nodeOk;

    function tabUrl(tab, tour, sub) {
        if (!TAB_OK.has(tab)) return null;
        let u = '?tab=' + tab;
        if (tour && PER_LOKASI.has(tab)) u += '&lokasi=' + encodeURIComponent(tour);
        if (tab === 'smart' && /^[a-z]{3,12}$/.test(sub || '')) u += '#sh=' + sub;
        return u;
    }
    const serverUrl = (slug) => S.settings.url + '/#/r/' + encodeURIComponent(S.project.id) + (slug ? '/t/' + encodeURIComponent(slug) : '');

    function toast(msg, bad = false) {
        if (window.cmsToast) return window.cmsToast(msg, bad ? 'error' : undefined);
        const t = $('#ivToast');
        t.textContent = msg; t.hidden = false; t.className = 'iv-toast' + (bad ? ' is-bad' : '');
        clearTimeout(toast.t); toast.t = setTimeout(() => { t.hidden = true; }, bad ? 8000 : 3500);
    }
    async function call(body) {
        const r = await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf, ...body }) });
        let j = null;
        try { j = await r.json(); } catch { /* bukan JSON */ }
        if (!j) throw new Error('Server tidak menjawab dengan benar (HTTP ' + r.status + '). Muat ulang halaman lalu coba lagi.');
        if (!j.success && !j.state) throw new Error(j.message || 'Permintaan gagal.');
        return j;
    }

    /* ---------- koneksi ---------- */
    function renderConn() {
        const box = $('#ivConn'); box.replaceChildren(); box.className = 'iv-conn';
        const s = S.settings;
        if (!s.pathOk) {
            box.classList.add('is-bad');
            box.append(h('span', { class: 'iv-dot is-bad' }), h('b', null, 'Folder VT Inspector tidak ditemukan'), h('span', null, s.path),
                h('span', { class: 'iv-conn__note' }, 'Pasang VT Inspector di folder htdocs (mis. C:/xampp/htdocs/VT-Inspector) atau isi jalurnya di bagian Pengaturan Inspector di bawah halaman ini.'));
            $('#ivSettings').open = true;
            return;
        }
        box.append(h('span', { class: 'iv-dot' + (S.server.up ? '' : ' is-off') }),
            h('b', null, S.server.up ? 'Server Inspector aktif' : 'Server Inspector belum berjalan'), h('span', null, S.settings.url + (S.server.version ? ' · v' + S.server.version : '')));
        if (!S.server.up) box.append(h('span', { class: 'iv-conn__note' }, 'Inspeksi statis tetap bisa dijalankan dari sini. Untuk uji robot, AI, dan tautan, jalankan start.cmd di folder VT Inspector (atau npm start), lalu muat ulang halaman ini.'));
        if (!s.nodeOk) { box.classList.add('is-warn'); box.append(h('span', { class: 'iv-conn__note iv-conn__warn' }, 'Node.js tidak ditemukan, sehingga tombol inspeksi statis nonaktif. Pasang Node.js atau isi jalurnya di Pengaturan Inspector.')); }
        const open = $('#ivOpenServer'); open.href = serverUrl(current);
    }

    /* ---------- ringkasan ---------- */
    function renderSummary() {
        const m = S.summary;
        const stat = (value, label, cls = '') => h('div', { class: 'iv-stat ' + cls }, h('strong', null, value), h('span', null, label));
        $('#ivSummary').replaceChildren(
            stat(m.tours, 'tur di ' + S.project.name),
            stat(m.avg == null ? '—' : m.avg, 'skor rata-rata (' + m.scanned + ' dari ' + m.tours + ' tur dipindai)'),
            stat(m.ready + ' / ' + m.tours, 'Siap rilis'),
            stat(m.tours - m.ready, 'belum siap rilis'),
            stat(m.stale, 'perlu scan ulang', m.stale ? 'is-warn' : ''),
            stat(fmtDate(m.lastScan), 'scan terakhir'),
        );
    }

    /* ---------- tabel ---------- */
    function gateCell(t) {
        const r = t.report;
        if (!r) return h('span', { class: 'iv-badge' }, 'Belum dipindai');
        if (t.stale) return h('span', { class: 'iv-badge is-warn', title: 'Berubah sejak scan terakhir' + (t.staleReason ? ' (' + t.staleReason + ')' : '') }, 'Perlu scan ulang');
        return r.ready ? h('span', { class: 'iv-badge is-ok' }, 'Siap rilis')
            : h('span', { class: 'iv-badge is-bad', title: r.reasons.length ? 'Belum siap:\n- ' + r.reasons.join('\n- ') : 'Belum memenuhi syarat' }, 'Belum siap');
    }
    function rowFor(t) {
        const r = t.report;
        const busyNow = busy.has(t.id) || busy.has('*');
        const st = r && STATUS[r.status];
        const cnt = r ? h('span', { class: 'iv-counts', title: 'Blocker / Major / Minor' }, ...['blocker', 'major', 'minor'].map((k) => h('span', null, h('i', { class: 'iv-sev-' + k }), r.counts[k]))) : '—';
        const links = h('div', { class: 'iv-rowact' },
            h('button', { type: 'button', class: 'iv-mini', onclick: () => select(t.id) }, 'Detail'),
            h('button', { type: 'button', class: 'iv-mini', disabled: !canScan() || busyNow || null, title: canScan() ? 'Jalankan inspeksi statis untuk tur ini' : 'Folder Inspector atau Node.js belum siap', onclick: () => scan([t.id]) }, 'Inspeksi'),
            h('a', { class: 'iv-mini', href: tabUrl('smart'), title: 'Buka tab Smart Hub' }, 'Smart Hub'),
            h('a', { class: 'iv-mini', href: tabUrl('aihub'), title: 'Buka tab AI Guide Hub' }, 'AI Guide'),
            h('a', { class: 'iv-mini', href: tabUrl('audit', t.id), title: 'Audit pano.xml tur ini' }, 'Audit'));
        return h('tr', { class: (t.id === current ? 'is-sel ' : '') + (busyNow ? 'is-busy' : ''), 'data-id': t.id },
            h('td', null, h('strong', null, t.name, r?.kit ? h('span', { class: 'iv-kit is-' + r.kit, title: r.kit === 'v3' ? 'GoVirtual v3 (Smart Kit)' : 'Skin Pano2VR v1' }, r.kit) : null), h('small', null, t.id)),
            h('td', null, r ? h('span', { class: scoreClass(r) }, r.score) : '—'),
            h('td', null, st ? h('span', { class: 'iv-badge ' + st[1] }, st[0]) : '—'),
            h('td', null, cnt),
            h('td', null, t.cms ? h('span', { class: 'iv-badge ' + (t.cms.score >= 95 ? 'is-ok' : t.cms.score >= 60 ? 'is-warn' : 'is-bad'), title: t.cms.todo.length ? 'Masih perlu:\n- ' + t.cms.todo.join('\n- ') : 'Lengkap' }, t.cms.score + '%') : h('span', { class: 'iv-muted' }, 'n/a')),
            h('td', null, gateCell(t)),
            h('td', null, r ? fmtDate(r.at) : '—'),
            h('td', { class: 'iv-col-act' }, links));
    }
    function renderRows() {
        const q = query.trim().toLowerCase();
        const list = S.tours.filter((t) => !q || (t.id + ' ' + t.name).toLowerCase().includes(q));
        $('#ivRows').replaceChildren(...(list.length ? list.map(rowFor)
            : [h('tr', null, h('td', { colspan: 8, class: 'iv-empty' }, h('strong', null, S.tours.length ? 'Tidak ada lokasi yang cocok' : 'Belum ada tur'), S.tours.length ? 'Coba kata kunci lain.' : 'Project ini belum punya folder tur (berisi pano.xml).'))]));
        $('#ivScanAll').disabled = !canScan() || busy.size > 0 || !S.tours.length;
    }

    /* ---------- detail ---------- */
    function select(id) {
        current = id;
        const u = new URL(location.href); u.searchParams.set('lokasi', id); history.replaceState(null, '', u);
        renderRows(); renderConn(); loadDetail(id); refreshIcons();
        $('#ivDetail').scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'nearest' });
    }
    async function loadDetail(id, force = false) {
        const box = $('#ivDetail'); box.hidden = false;
        if (force) delete detailCache[id];
        if (!detailCache[id]) {
            box.replaceChildren(h('p', { class: 'iv-muted' }, 'Memuat detail…'));
            try { detailCache[id] = await call({ op: 'detail', location: id }); }
            catch (e) { box.replaceChildren(h('p', { class: 'iv-muted' }, e.message)); return; }
        }
        if (current === id) renderDetail(id, detailCache[id]);
    }
    function findingEl(f, tour) {
        const url = f.link ? tabUrl(f.link.tab, f.link.tour || tour, f.link.sub) : null;
        return h('div', { class: 'iv-finding' },
            h('div', { class: 'iv-finding__title' }, h('span', { class: 'iv-badge ' + (f.severity === 'blocker' || f.severity === 'major' ? 'is-bad' : f.severity === 'minor' ? 'is-warn' : '') }, SEV[f.severity] || f.severity), f.title),
            f.detail ? h('p', null, f.detail) : null,
            f.items.length ? h('ul', null, f.items.map((i) => h('li', null, i))) : null,
            f.fix ? h('div', { class: 'iv-finding__fix' }, h('b', null, 'Saran:'), f.fix) : null,
            url ? h('a', { href: url }, 'Perbaiki di tab CMS terkait') : null);
    }
    function renderDetail(id, d) {
        const t = S.tours.find((x) => x.id === id); const box = $('#ivDetail');
        if (!t) { box.hidden = true; return; }
        const r = d.report;
        const head = h('div', { class: 'iv-detail__head' },
            h('div', null, h('h2', null, t.name), h('div', { class: 'iv-muted' }, r ? 'Skor ' + r.score + ' · scan ' + fmtDate(r.at) + (d.info?.profile ? ' · profil ' + d.info.profile : '') : 'Belum ada laporan Inspector untuk tur ini.'),
                t.stale ? h('p', { class: 'iv-badge is-warn' }, 'Perlu scan ulang: konten atau penerapan berubah sejak scan terakhir' + (t.staleReason ? ' (' + t.staleReason + ')' : '')) : null),
            h('div', { class: 'iv-detail__links' },
                h('button', { type: 'button', class: 'iv-primary', disabled: !canScan() || busy.has(id) || null, onclick: () => scan([id]) }, icon('scan-search'), 'Jalankan inspeksi statis (tur ini)'),
                h('a', { class: 'iv-ghost', target: '_blank', rel: 'noopener', href: serverUrl(id) }, icon('external-link'), 'Buka di VT Inspector'),
                h('a', { class: 'iv-ghost', href: tabUrl('smart') }, 'Smart Hub'), h('a', { class: 'iv-ghost', href: tabUrl('aihub') }, 'AI Guide Hub'), h('a', { class: 'iv-ghost', href: tabUrl('audit', id) }, 'Audit tur')));
        const parts = [head];
        if (r && !r.ready && r.reasons.length) parts.push(h('div', null, h('b', null, 'Belum siap rilis karena:'), h('ul', { class: 'iv-todo' }, r.reasons.map((x) => h('li', null, x)))));
        if (t.cms && t.cms.todo.length) parts.push(h('div', null, h('b', null, 'Kesiapan CMS ' + t.cms.score + '% — masih perlu diisi:'), h('ul', { class: 'iv-todo' }, t.cms.todo.map((x) => h('li', null, x))), h('a', { class: 'iv-mini', href: tabUrl('smart') }, 'Isi di Smart Hub')));
        if (r) {
            const groups = new Map();
            for (const f of d.findings) { if (!groups.has(f.category)) groups.set(f.category, []); groups.get(f.category).push(f); }
            if (!groups.size) parts.push(h('p', { class: 'iv-badge is-ok' }, 'Tidak ada temuan.'));
            else parts.push(h('div', { class: 'iv-cats' }, [...groups].map(([cat, list]) => {
                const worst = ['blocker', 'major', 'minor', 'info'].find((s) => list.some((f) => f.severity === s));
                return h('details', { class: 'iv-cat', open: worst === 'blocker' || worst === 'major' || null },
                    h('summary', null, cat, h('small', null, list.length + ' temuan')), list.map((f) => findingEl(f, id)));
            })));
            if (d.history?.length > 1) parts.push(h('p', { class: 'iv-muted' }, 'Riwayat skor: ' + d.history.map((x) => x.score).join(' → ')));
        }
        box.replaceChildren(...parts); refreshIcons();
    }

    /* ---------- scan ---------- */
    async function scan(slugs) {
        const all = slugs === 'all';
        if (all && !confirm('Jalankan inspeksi statis untuk semua tur di project ini? Proses beberapa detik hingga beberapa menit; tur tidak diubah.')) return;
        const keys = all ? ['*'] : slugs; keys.forEach((k) => busy.add(k)); renderRows();
        const res = $('#ivResult'); res.hidden = false; res.className = 'iv-result';
        res.replaceChildren(h('p', null, 'Menjalankan inspeksi statis… jangan tutup halaman ini.'));
        try {
            const j = await call({ op: 'scan', locations: all ? 'all' : slugs });
            S = j.state; for (const k of Object.keys(detailCache)) delete detailCache[k];
            res.className = 'iv-result' + (j.success ? '' : ' is-bad');
            res.replaceChildren(h('strong', null, j.message), j.log ? h('pre', null, j.log) : null);
            toast(j.message, !j.success);
        } catch (e) {
            res.className = 'iv-result is-bad'; res.replaceChildren(h('strong', null, e.message)); toast(e.message, true);
        } finally { keys.forEach((k) => busy.delete(k)); }
        renderAll();
        if (current) loadDetail(current, true);
    }

    /* ---------- pengaturan ---------- */
    function initSettings() {
        const f = $('#ivForm'); const s = S.settings;
        f.path.value = s.configuredPath; f.node.value = s.configuredNode; f.url.value = s.configuredUrl;
        f.path.placeholder = s.path; f.url.placeholder = s.url;
        $('#ivNodeHint').textContent = s.nodeOk ? 'Terdeteksi: ' + s.node : 'Node.js belum terdeteksi.';
        if (!S.isAdmin) { for (const el of f.elements) el.disabled = true; $('#ivAdminNote').textContent = 'Hanya admin yang dapat mengubah pengaturan.'; }
        f.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            const err = $('#ivFormError'); err.hidden = true;
            const btn = $('#ivSaveSettings'); btn.disabled = true;
            try {
                const j = await call({ op: 'save_settings', settings: { path: f.path.value, node: f.node.value, url: f.url.value } });
                S = j.state; toast(j.message); renderAll(); $('#ivNodeHint').textContent = S.settings.nodeOk ? 'Terdeteksi: ' + S.settings.node : 'Node.js belum terdeteksi.';
            } catch (e) { err.textContent = e.message; err.hidden = false; }
            finally { btn.disabled = false; }
        });
    }

    function renderAll() { renderConn(); renderSummary(); renderRows(); refreshIcons(); }

    $('#ivSearch').addEventListener('input', (e) => { query = e.target.value; renderRows(); refreshIcons(); });
    $('#ivScanAll').addEventListener('click', () => scan('all'));
    initSettings();
    renderAll();
    if (current) loadDetail(current);
})();
