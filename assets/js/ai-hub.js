/* ai-hub.js — tab "AI Guide Hub": daftar, sinkron, dan terbitkan lokasi ke AI Guide Hub lewat api.php?action=ai-hub. */
(() => {
    'use strict';
    const boot = JSON.parse(document.getElementById('aiHubBoot').textContent);
    const API = 'api.php?action=ai-hub';
    const csrf = boot.csrf;
    let state = boot.state;
    let filter = 'all';
    let query = '';
    let running = new Set();
    const selected = new Set();
    const $ = (s, r = document) => r.querySelector(s);

    const STAGES = [
        { id: 'none', label: 'Belum didaftarkan', short: 'Belum' },
        { id: 'registered', label: 'Terdaftar', short: 'Terdaftar' },
        { id: 'synced', label: 'Tersinkron', short: 'Tersinkron' },
        { id: 'built', label: 'Paket siap', short: 'Paket siap' },
        { id: 'live', label: 'Aktif di tur', short: 'Aktif' },
    ];
    const STEP_NAME = { register: 'Daftar', sync: 'Sinkron', publish: 'Terbitkan' };
    // Langkah yang dibutuhkan lokasi untuk maju. Lokasi yang sudah aktif cukup diperbarui dari CMS.
    const NEXT = {
        none: ['register', 'sync', 'publish'], registered: ['sync', 'publish'], synced: ['publish'],
        built: ['publish'], live: ['sync', 'publish'],
    };
    const ROW_ACTION = { none: 'Siapkan', registered: 'Lanjutkan', synced: 'Terbitkan', built: 'Pasang ke tur', live: 'Perbarui' };

    const h = (tag, attrs = {}, ...kids) => {
        const el = document.createElement(tag);
        for (const [k, v] of Object.entries(attrs || {})) {
            if (v === false || v == null) continue;
            if (k === 'class') el.className = v;
            else if (k.startsWith('on')) el.addEventListener(k.slice(2), v);
            else el.setAttribute(k, v === true ? '' : v);
        }
        for (const c of kids.flat()) if (c != null && c !== false) el.append(c.nodeType ? c : document.createTextNode(String(c)));
        return el;
    };
    const icon = (name, cls = '') => h('i', { 'data-lucide': name, class: cls, 'aria-hidden': 'true' });
    const refreshIcons = () => queueMicrotask(() => window.lucide?.createIcons({ attrs: { 'stroke-width': 2 } }));
    const setPicked = (id, on) => { if (on) selected.add(id); else selected.delete(id); };
    const stageOf = (id) => STAGES.find((s) => s.id === id) || STAGES[0];
    const stepsFor = (r) => NEXT[r.stage] || NEXT.none;

    function toast(msg, bad = false) {
        const t = $('#ahToast');
        t.textContent = msg; t.hidden = false; t.className = 'ah-toast' + (bad ? ' is-bad' : '');
        clearTimeout(toast.t); toast.t = setTimeout(() => { t.hidden = true; }, bad ? 8000 : 3500);
    }

    async function call(body) {
        const r = await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf, ...body }) });
        let j = null;
        try { j = await r.json(); } catch { /* bukan JSON */ }
        if (!j) throw new Error('Server tidak menjawab dengan benar (HTTP ' + r.status + '). Muat ulang halaman lalu coba lagi.');
        if (!j.success && !j.results) throw new Error(j.message || 'Permintaan gagal.');
        return j;
    }

    /* ------------------------------ tampilan ------------------------------ */
    function renderConn() {
        const box = $('#ahConn'); const ok = !state.hubError;
        const kids = [h('span', { class: 'ah-dot ' + (ok ? 'is-ok' : 'is-bad'), 'aria-hidden': 'true' })];
        if (ok) {
            kids.push(h('span', {}, 'Terhubung ke AI Guide Hub · kota ', h('b', {}, state.settings.city)));
            if (!state.cmsRuntime) kids.push(h('span', { class: 'ah-conn__note' }, 'Project ini belum memakai runtime CMS, jadi nama dan teks sambutan dikelola langsung di Hub.'));
            if (!state.runtime) kids.push(h('span', { class: 'ah-conn__warn' }, 'Runtime AI belum ditemukan di Hub, paket belum bisa diterbitkan.'));
        } else {
            kids.push(h('span', {}, h('b', {}, 'Belum terhubung. '), state.hubError));
            kids.push(h('button', { type: 'button', class: 'ah-link', onclick: () => { $('#ahSettings').open = true; $('#ahSettings').scrollIntoView({ behavior: 'smooth', block: 'center' }); } }, 'Buka pengaturan'));
        }
        box.className = 'ah-conn' + (ok ? '' : ' is-bad');
        box.replaceChildren(...kids);
        if (!ok) $('#ahSettings').open = true;
    }

    function renderPipeline() {
        const box = $('#ahPipeline'); const total = state.locations.length;
        const counts = Object.fromEntries(STAGES.map((s) => [s.id, state.locations.filter((l) => l.stage === s.id).length]));
        if (!total) { box.replaceChildren(); return; }
        const bar = h('div', { class: 'ah-bar-track', role: 'img', 'aria-label': STAGES.map((s) => counts[s.id] + ' ' + s.short.toLowerCase()).join(', ') },
            STAGES.filter((s) => counts[s.id]).map((s) => h('span', { class: 'ah-seg is-' + s.id, style: 'flex-grow:' + counts[s.id] })));
        const legend = h('div', { class: 'ah-legend', role: 'group', 'aria-label': 'Saring berdasarkan tahap' },
            h('button', { type: 'button', class: 'ah-fchip' + (filter === 'all' ? ' is-on' : ''), 'aria-pressed': String(filter === 'all'), onclick: () => setFilter('all') }, 'Semua ', h('b', {}, total)),
            STAGES.filter((s) => counts[s.id]).map((s) => h('button', { type: 'button', class: 'ah-fchip is-' + s.id + (filter === s.id ? ' is-on' : ''), 'aria-pressed': String(filter === s.id), onclick: () => setFilter(filter === s.id ? 'all' : s.id) },
                h('span', { class: 'ah-swatch', 'aria-hidden': 'true' }), s.short + ' ', h('b', {}, counts[s.id]))));
        box.replaceChildren(bar, legend);
        requestAnimationFrame(() => requestAnimationFrame(() => box.classList.add('is-in')));
    }

    function visibleRows() {
        const q = query.trim().toLowerCase();
        return state.locations.filter((r) => (filter === 'all' || r.stage === filter) && (!q || (r.name + ' ' + r.id).toLowerCase().includes(q)));
    }

    function stepper(r) {
        const order = STAGES.map((s) => s.id); const at = order.indexOf(r.stage);
        return h('span', { class: 'ah-steps', role: 'img', 'aria-label': 'Tahap ' + stageOf(r.stage).label },
            [1, 2, 3, 4].map((i) => h('span', { class: 'ah-step' + (i <= at ? ' is-done' : '') })));
    }

    function dataCell(r) {
        const x = r.hub || {};
        const bits = [];
        if (state.cmsRuntime) bits.push(r.cms.hasText ? 'Teks sambutan ' + r.cms.langs.length + ' bahasa' : 'Belum ada teks sambutan');
        else bits.push(x.registered ? 'Teks dikelola di Hub' : 'Belum ada di Hub');
        if (x.registered && (x.scenes || 0) > 0) bits.push(x.scenes + ' scene');
        const pr = x.parity;
        const parity = pr
            ? h('small', { class: pr.complete ? 'ah-ok' : 'ah-warn' }, icon(pr.complete ? 'circle-check' : 'triangle-alert'),
                pr.nodes.hub + '/' + pr.nodes.cms + ' node · ' + pr.hotspots.hub + '/' + pr.hotspots.cms + ' hotspot · ' + pr.translations.inKnowledge + '/' + pr.translations.cms + ' terjemahan')
            : (x.registered ? h('small', { class: 'ah-muted' }, 'Isi pano.xml belum disinkronkan') : null);
        const warn = state.cmsRuntime && !r.cms.hasText ? 'Isi teks sambutan di Data Lokasi agar Jali punya bahan jawaban.' : (x.registered && x.exportOk === false ? 'Folder tur tidak ditemukan di Hub. Jalankan lagi.' : '');
        return h('div', { class: 'ah-data' }, h('span', {}, bits.join(' · ')), parity, warn ? h('small', { class: 'ah-warn' }, icon('triangle-alert'), warn) : null,
            r.smart && state.smartkit.enabled ? h('small', { class: 'ah-muted' }, 'Smart Kit: ' + (r.smart.applied ? 'diterapkan' : (r.smart.pack ? 'paket belum diterapkan' : 'belum dibuat'))) : null);
    }

    function renderRows() {
        const body = $('#ahRows'); const rows = visibleRows(); const admin = state.isAdmin;
        if (!state.locations.length) {
            body.replaceChildren(h('tr', {}, h('td', { colspan: 5, class: 'ah-empty' }, h('strong', {}, 'Belum ada lokasi di project ini'), h('span', {}, 'Lokasi muncul otomatis begitu ada folder tur Pano2VR (berisi pano.xml) di dalam project.'))));
            return;
        }
        if (!rows.length) {
            body.replaceChildren(h('tr', {}, h('td', { colspan: 5, class: 'ah-empty' }, h('strong', {}, 'Tidak ada lokasi yang cocok'), h('button', { type: 'button', class: 'ah-link', onclick: () => { query = ''; $('#ahSearch').value = ''; setFilter('all'); } }, 'Tampilkan semua lokasi'))));
            return;
        }
        body.replaceChildren(...rows.map((r) => {
            const busy = running.has(r.id); const st = stageOf(r.stage);
            return h('tr', { 'data-id': r.id, class: busy ? 'is-busy' : '', 'aria-busy': busy ? 'true' : null },
                h('td', { class: 'ah-col-check' }, h('input', { type: 'checkbox', class: 'ah-pick', value: r.id, checked: selected.has(r.id), disabled: !admin || busy, 'aria-label': 'Pilih ' + r.name, onchange: (e) => { setPicked(r.id, e.target.checked); syncBar(); } })),
                h('td', { 'data-label': 'Lokasi' }, h('strong', {}, r.name), h('small', { class: 'ah-muted' }, r.id + (r.slug !== r.id ? ' → ' + r.slug : ''))),
                h('td', { 'data-label': 'Tahap' }, busy
                    ? h('span', { class: 'ah-stage is-busy' }, icon('loader-circle', 'ah-spin'), 'Memproses…')
                    : h('span', { class: 'ah-stage is-' + r.stage }, stepper(r), h('span', {}, st.label))),
                h('td', { 'data-label': 'Data' }, dataCell(r)),
                h('td', { class: 'ah-col-act' },
                    h('button', { type: 'button', class: 'ah-btn' + (r.stage === 'live' ? '' : ' is-primary'), disabled: !admin || busy || !!state.hubError, title: admin ? stepsFor(r).map((s) => STEP_NAME[s]).join(' → ') : 'Hanya admin yang dapat menjalankan langkah ini', onclick: () => runAuto([r.id]) }, ROW_ACTION[r.stage]),
                    r.hubUrl ? h('a', { class: 'ah-btn is-quiet', href: r.hubUrl, target: '_blank', rel: 'noopener', 'aria-label': 'Buka ' + r.name + ' di Hub (tab baru)' }, 'Buka di Hub', icon('external-link')) : null));
        }));
        const all = $('#ahCheckAll');
        const ids = rows.map((r) => r.id);
        all.checked = ids.length > 0 && ids.every((id) => selected.has(id));
        all.disabled = !admin;
        refreshIcons();
    }

    function manualSteps() { return [...document.querySelectorAll('[data-ah-step]')].filter((c) => c.checked).map((c) => c.dataset.ahStep); }

    function syncBar() {
        const bar = $('#ahBar'); const n = selected.size;
        bar.hidden = n === 0;
        $('#ahBarCount').textContent = n + ' lokasi dipilih';
        const manual = manualSteps();
        const busy = running.size > 0;
        $('#ahBarHint').textContent = manual.length ? 'Langkah: ' + manual.map((s) => STEP_NAME[s]).join(' → ') : 'Tiap lokasi dilanjutkan ke tahap berikutnya.';
        $('#ahRunSelected').disabled = busy || !state.isAdmin || n === 0 || !!state.hubError;
        const all = $('#ahCheckAll'); const ids = visibleRows().map((r) => r.id);
        all.checked = ids.length > 0 && ids.every((id) => selected.has(id));
    }

    function renderSettings() {
        const f = $('#ahForm'), s = state.settings;
        for (const k of ['path', 'url', 'city', 'cityName', 'php']) f.elements[k].value = s[k] || '';
        $('#ahPhpHint').textContent = 'Saat ini dipakai: ' + state.php;
        for (const el of f.elements) el.disabled = !state.isAdmin;
        $('#ahAdminNote').textContent = state.isAdmin ? '' : 'Hanya admin yang dapat mengubah pengaturan.';
    }

    function renderAll() {
        for (const id of Array.from(selected)) if (!state.locations.some((l) => l.id === id)) selected.delete(id);
        renderConn(); renderPipeline(); renderRows(); renderSettings(); syncBar(); refreshIcons();
    }

    function setFilter(f) { filter = f; renderPipeline(); renderRows(); syncBar(); refreshIcons(); }

    function showResult(results) {
        const box = $('#ahResult');
        const failed = results.filter((r) => !r.ok).length;
        box.replaceChildren(
            h('div', { class: 'ah-result__head' },
                h('strong', {}, failed ? (results.length - failed) + ' berhasil, ' + failed + ' gagal' : results.length + ' lokasi berhasil diproses'),
                h('button', { type: 'button', class: 'ah-link', onclick: () => { box.hidden = true; } }, 'Tutup')),
            h('ul', {}, results.map((r) => h('li', { class: r.ok ? 'is-ok' : 'is-bad' },
                icon(r.ok ? 'circle-check' : 'circle-alert'),
                h('div', {}, h('b', {}, (state.locations.find((l) => l.id === r.id)?.name || r.id)),
                    h('ul', { class: 'ah-result__steps' }, r.log.map((l) => h('li', { class: l.ok ? '' : 'is-bad' }, h('span', {}, STEP_NAME[l.step]), l.message)))))))
        );
        box.hidden = false; refreshIcons();
        if (failed) box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /* ------------------------------ aksi ------------------------------ */
    async function execute(plan) {
        // plan: [{ids, steps}] dijalankan berurutan; Hub memakai satu database sehingga tidak diparalelkan.
        if (running.size) return;
        const ids = plan.flatMap((p) => p.ids);
        running = new Set(ids); renderRows(); syncBar();
        const before = Object.fromEntries(state.locations.map((l) => [l.id, STAGES.findIndex((s) => s.id === l.stage)]));
        const all = [];
        try {
            for (const p of plan) {
                const j = await call({ op: 'run', steps: p.steps, locations: p.ids });
                state = j.state; all.push(...j.results);
            }
            selected.clear();
            running = new Set(); renderAll(); showResult(all); celebrate(before);
            const bad = all.filter((r) => !r.ok).length;
            toast(bad ? bad + ' lokasi gagal. Lihat rinciannya di bawah tabel.' : 'Selesai. ' + all.length + ' lokasi diproses.', bad > 0);
        } catch (e) {
            running = new Set(); renderAll();
            if (all.length) showResult(all);
            toast(e.message, true);
        }
    }

    // Baris yang naik tahap menyala sebentar: konfirmasi visual bahwa langkahnya berhasil.
    function celebrate(before) {
        for (const l of state.locations) {
            if (STAGES.findIndex((s) => s.id === l.stage) <= (before[l.id] ?? 0)) continue;
            const row = document.querySelector('tr[data-id="' + CSS.escape(l.id) + '"]');
            if (row) { row.classList.add('is-fresh'); setTimeout(() => row.classList.remove('is-fresh'), 2200); }
        }
    }

    function runAuto(ids, useManual = false) {
        const manual = useManual ? manualSteps() : [];
        const groups = new Map();
        for (const id of ids) {
            const r = state.locations.find((l) => l.id === id); if (!r) continue;
            const steps = manual.length ? manual : stepsFor(r);
            const key = steps.join(',');
            if (!groups.has(key)) groups.set(key, { steps, ids: [] });
            groups.get(key).ids.push(id);
        }
        execute([...groups.values()]);
    }

    /* ------------------------------ peristiwa ------------------------------ */
    $('#ahSearch').addEventListener('input', (e) => { query = e.target.value; renderRows(); syncBar(); refreshIcons(); });
    $('#ahCheckAll').addEventListener('change', (e) => {
        for (const r of visibleRows()) setPicked(r.id, e.target.checked);
        renderRows(); syncBar();
    });
    $('#ahRunSelected').addEventListener('click', () => runAuto([...selected], true));
    $('#ahClear').addEventListener('click', () => { selected.clear(); renderRows(); syncBar(); });
    document.querySelectorAll('[data-ah-step]').forEach((c) => c.addEventListener('change', syncBar));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && selected.size && !e.target.closest?.('input[type=search]')) { selected.clear(); renderRows(); syncBar(); } });

    $('#ahForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const f = e.target; const err = $('#ahFormError'); err.hidden = true;
        const settings = {};
        for (const k of ['path', 'url', 'city', 'cityName', 'php']) settings[k] = f.elements[k].value.trim();
        const btn = $('#ahSaveSettings'); btn.disabled = true; btn.textContent = 'Menyimpan…';
        try { const j = await call({ op: 'save_settings', settings }); state = j.state; renderAll(); toast(j.message); }
        catch (x) { err.textContent = x.message; err.hidden = false; }
        finally { btn.disabled = false; btn.textContent = 'Simpan pengaturan'; }
    });
    renderAll();
})();
