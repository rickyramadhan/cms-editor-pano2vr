/* lokasi-url.js — tab CMS "URL Lokasi": daftar URL tur tiap lokasi + hotspot "lokasi terdekat" yang bisa digeser di panorama drone.
 * Data: assets/main-loader/lokasi-database.js (disimpan lewat lokasi-url-save.php). Runtime: smart-kit/frontends/badung-ui/js/nearby-locs.js
 * (rumus posisi awal defaultPos HARUS sama dengan berkas itu). Judul tujuan ikut masuk ke Audit › Terjemahan (pano.xml). */
(() => {
    'use strict';
    const stateEl = document.getElementById('lkuState'), root = document.getElementById('lku');
    if (!stateEl || !root) return;
    const S0 = JSON.parse(stateEl.textContent);
    const $ = (selector, scope = root) => scope.querySelector(selector);
    const $$ = (selector, scope = root) => [...scope.querySelectorAll(selector)];
    const el = (tag, cls, text) => { const node = document.createElement(tag); if (cls) node.className = cls; if (text != null) node.textContent = text; return node; };
    const toast = (message, kind = 'success') => { try { (window.cmsToast?.[kind] || window.cmsToast)?.(message); } catch (e) { /* toast opsional */ } };

    const db = { locations: JSON.parse(JSON.stringify(S0.db?.locations || {})), links: JSON.parse(JSON.stringify(S0.db?.links || {})) };
    const tours = new Set((S0.tours || []).map(String)), selected = String(S0.selected || ''), drone = S0.drone || {};
    const baseUrl = S0.tourBase ? new URL(S0.tourBase, document.baseURI) : null;
    const saveButton = $('#lkuSave'), saveLabel = $('.oi-save-label', saveButton);
    let saved = JSON.stringify(db), dirty = false, selLink = 0, rescanBadge = () => {};

    /* ---------- posisi awal (sama dengan nearby-locs.js) ---------- */
    const DEF_SPREAD = 42, DEF_TILT = -12;
    const normPan = (value) => Math.round((((value + 180) % 360 + 360) % 360 - 180) * 100) / 100;
    const defaultPos = (i, n, base) => ({ pan: normPan((Number(base) || 0) + (i - (n - 1) / 2) * DEF_SPREAD), tilt: DEF_TILT });
    const myLinks = () => (db.links[selected] = db.links[selected] || []);
    const posOf = (i) => { const l = myLinks()[i]; return Number.isFinite(+l.pan) && Number.isFinite(+l.tilt) && l.pan != null && l.tilt != null ? { pan: +l.pan, tilt: +l.tilt, custom: true } : { ...defaultPos(i, myLinks().length, drone.pan), custom: false }; };
    const nameOf = (id) => db.locations[id]?.name || id;
    const fmtDist = (km) => String(km || '').trim();

    function setDirty() {
        dirty = JSON.stringify(db) !== saved;
        saveButton.disabled = !dirty;
        saveButton.classList.toggle('is-dirty', dirty);   // titik kuning di tombol, sama dengan tombol Simpan di tab Object Informasi
        saveLabel.textContent = 'Simpan';
        saveButton.classList.remove('is-saving');
    }
    window.addEventListener('beforeunload', (event) => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });

    /* ---------- sub-tab ---------- */
    $$('[data-lku-pane]').forEach((button) => button.addEventListener('click', () => {
        const pane = button.dataset.lkuPane;
        $$('[data-lku-pane]').forEach((b) => { const on = b === button; b.classList.toggle('is-on', on); b.setAttribute('aria-selected', String(on)); });
        $('#lkuPaneHs').hidden = pane !== 'hs'; $('#lkuPaneUrl').hidden = pane !== 'url';
        if (pane === 'hs') window.dispatchEvent(new Event('resize'));
    }));

    /* ---------- terjemahan pano.xml tur ini (hanya baca; ditulis server saat Simpan) ---------- */
    let panoXml = null, trLangs = [], trMap = {};
    function readTranslations() {
        trLangs = []; trMap = {};
        const node = panoXml?.querySelector('masternode > translations > json'); if (!node) return;
        try { const d = JSON.parse(node.textContent); trLangs = (d.languages || []).map((code) => String(code).split('-')[0].toUpperCase()); trMap = d.translations || {}; } catch (e) { /* tanpa terjemahan */ }
    }
    const trChips = (name) => {
        const box = el('div', 'lku-tr'); box.append(el('span', 'lku-tr-label', 'Terjemahan'));
        const row = trMap[name];
        (trLangs.length ? trLangs : ['EN', 'ZH', 'AR']).forEach((code, index) => { const ok = !!(row && String(row[index] || '').trim()); const chip = el('span', 'lku-chip ' + (ok ? 'is-ok' : 'is-miss'), code); chip.title = ok ? code + ': ' + row[index] : code + ': belum diisi'; box.append(chip); });
        if (!row) box.append(el('span', 'lku-tr-new', 'masuk daftar saat Simpan'));
        return box;
    };

    /* ---------- daftar hotspot di sisi kanan ---------- */
    const linksBox = $('#lkuLinks');
    function renderLinks() {
        linksBox.replaceChildren();
        const list = myLinks();
        $('#lkuSideTitle').textContent = 'Hotspot di ' + nameOf(selected);
        if (!list.length) { linksBox.append(el('p', 'lku-none', selected in db.locations ? 'Menurut tabel jarak, lokasi ini tidak punya tujuan terdekat yang bisa dihubungkan.' : 'Lokasi ini belum ada di Daftar URL.')); return; }
        list.forEach((lk, i) => {
            const pos = posOf(i), card = el('article', 'lku-link' + (i === selLink ? ' is-selected' : '') + (lk.hidden ? ' is-hidden' : '')); card.dataset.i = String(i);
            const head = el('header'); head.append(el('span', 'lku-badge', String(i + 1)), el('strong', null, nameOf(lk.to)));
            const eye = el('button', 'lku-icon'); eye.type = 'button'; eye.dataset.act = 'hide'; eye.title = lk.hidden ? 'Hotspot disembunyikan di tur — klik untuk menampilkan' : 'Sembunyikan hotspot ini di tur'; eye.setAttribute('aria-pressed', String(!!lk.hidden)); eye.textContent = lk.hidden ? 'Tampilkan' : 'Sembunyikan';
            head.append(eye); card.append(head);
            const fields = el('div', 'lku-fields');
            const mk = (label, field, attrs) => { const wrap = el('label'); wrap.append(el('span', null, label)); const input = el('input'); input.dataset.f = field; Object.assign(input, attrs); wrap.append(input); return wrap; };
            fields.append(mk('Menit', 'min', { type: 'number', min: '0', max: '999', value: String(lk.min ?? 0) }), mk('Jarak', 'km', { type: 'text', value: fmtDist(lk.km), placeholder: '1.5 km' }));
            card.append(fields, trChips(nameOf(lk.to)));
            const foot = el('div', 'lku-foot'); foot.append(el('span', 'lku-pos', (pos.custom ? '' : 'Otomatis · ') + 'Pan ' + pos.pan.toFixed(1) + '° · Tilt ' + pos.tilt.toFixed(1) + '°'));
            const reset = el('button', 'lku-text'); reset.type = 'button'; reset.dataset.act = 'reset'; reset.textContent = 'Atur ulang posisi'; reset.disabled = !pos.custom; foot.append(reset); card.append(foot);
            card.addEventListener('click', (event) => { if (event.target.closest('button,input')) return; choose(i); });
            linksBox.append(card);
        });
    }
    linksBox.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-act]'); if (!button) return;
        const i = +button.closest('.lku-link').dataset.i, lk = myLinks()[i];
        if (button.dataset.act === 'hide') { if (lk.hidden) delete lk.hidden; else lk.hidden = true; }
        if (button.dataset.act === 'reset') { delete lk.pan; delete lk.tilt; }
        setDirty(); renderLinks(); renderMarkers();
    });
    linksBox.addEventListener('input', (event) => {
        const input = event.target.closest('input[data-f]'); if (!input) return;
        const lk = myLinks()[+input.closest('.lku-link').dataset.i];
        if (input.dataset.f === 'min') lk.min = Math.max(0, Math.min(999, parseInt(input.value, 10) || 0)); else lk.km = input.value;
        setDirty(); refreshMarkerText();
    });
    function choose(i) { selLink = i; $$('.lku-link', linksBox).forEach((card) => card.classList.toggle('is-selected', +card.dataset.i === i)); $$('.lku-hs-root').forEach((node) => node.classList.toggle('is-selected', +node.dataset.i === i)); }

    /* ---------- panorama drone + penanda yang bisa digeser ---------- */
    const stage = $('#lkuStage'), host = $('#lkuPano'), loading = $('#lkuLoading'), empty = $('#lkuEmpty'), hint = $('#lkuHint');
    let player = null, ready = false, dragging = false;
    const showEmpty = (message) => { loading.hidden = true; host.hidden = true; empty.hidden = false; empty.textContent = message; hint.hidden = true; };
    const markerText = (lk) => 'est. ' + (lk.min ?? 0) + ' mnt | ' + fmtDist(lk.km);
    function buildMarker(lk, i) {
        const wrap = el('div', 'lku-hs-root' + (lk.hidden ? ' is-off' : '') + (i === selLink ? ' is-selected' : '')); wrap.dataset.i = String(i);
        const a = el('div', 'bdg-loc'); a.setAttribute('role', 'button'); a.tabIndex = 0;
        a.innerHTML = '<span class="bdg-loc__box"><span class="bdg-loc__t"><b></b><small></small></span><span class="bdg-loc__ph"><img alt="" draggable="false"></span></span><span class="bdg-loc__ln"></span><span class="bdg-loc__dot"></span>';
        $('b', a).textContent = nameOf(lk.to); $('small', a).textContent = markerText(lk);
        const img = $('img', a); img.addEventListener('error', () => a.classList.add('no-img')); if (baseUrl) img.src = new URL('../' + encodeURIComponent(lk.to) + '/preview.jpg', baseUrl).href;
        const badge = el('span', 'lku-pin-no', String(i + 1)); a.append(badge);
        a.addEventListener('pointerdown', (event) => beginDrag(event, i));
        wrap.append(a); return wrap;
    }
    function refreshMarkerText() { $$('.lku-hs-root').forEach((node) => { const lk = myLinks()[+node.dataset.i]; if (lk) $('small', node).textContent = markerText(lk); }); }
    function renderMarkers() {
        if (!player || !ready || dragging) return;
        player.removeHotspots();
        myLinks().forEach((lk, i) => { const p = posOf(i); player.addHotspot('lku_' + i, p.pan, p.tilt, buildMarker(lk, i)); });
    }
    function beginDrag(event, i) {
        if (event.button !== 0 || !ready || dragging) return;
        event.preventDefault(); event.stopPropagation(); choose(i);
        const rect = host.getBoundingClientRect(), first = player.getPositionAngles(event.clientX - rect.left, event.clientY - rect.top), p0 = posOf(i), lk = myLinks()[i];
        const startPan = p0.pan, tiltOffset = p0.tilt - Number(first.tilt);
        dragging = true; player.stopAutorotate?.(); player.setLocked(true);
        const move = (ev) => {
            ev.preventDefault();
            const area = host.getBoundingClientRect(), angle = player.getPositionAngles(ev.clientX - area.left, ev.clientY - area.top);
            lk.pan = normPan(startPan - normPan(Number(angle.pan) - Number(first.pan)));   // pan pemutar ini terbalik (pan naik = isi panorama bergeser ke kanan)
            lk.tilt = Math.round(Math.max(-90, Math.min(90, Number(angle.tilt) + tiltOffset)) * 100) / 100;
            player.updateHotspot('lku_' + i, lk.pan, lk.tilt);
            hint.textContent = nameOf(lk.to) + ' · Pan ' + lk.pan.toFixed(1) + '° · Tilt ' + lk.tilt.toFixed(1) + '°';
        };
        const end = () => {
            window.removeEventListener('pointermove', move); window.removeEventListener('pointerup', end); window.removeEventListener('pointercancel', end);
            dragging = false; player.setLocked(false); setDirty(); renderLinks();
            hint.textContent = 'Posisi ' + nameOf(lk.to) + ' diperbarui. Tekan Simpan untuk menerapkannya di tur.';
        };
        window.addEventListener('pointermove', move, { passive: false }); window.addEventListener('pointerup', end); window.addEventListener('pointercancel', end);
    }
    async function startPlayer() {
        if (!baseUrl) return showEmpty('Lokasi “' + nameOf(selected) + '” belum punya tur di folder project ini, jadi panorama drone tidak bisa ditampilkan. Daftar hotspot di samping tetap bisa diatur.');
        if (!drone.node) return showEmpty('Lokasi ini belum punya node panorama drone. Atur node drone di tab Data, lalu kembali ke sini.');
        if (!myLinks().length) return showEmpty('Tidak ada hotspot untuk lokasi ini: menurut tabel jarak, lokasi ini tidak punya tujuan terdekat yang bisa dihubungkan.');
        try {
            if (!window.pano2vrPlayer) await new Promise((resolve, reject) => { const script = document.createElement('script'); script.src = new URL('pano2vr_player.js', baseUrl).href; script.onload = resolve; script.onerror = () => reject(new Error('pano2vr_player.js tidak dapat dimuat.')); document.head.appendChild(script); });
            const response = await fetch(new URL('pano.xml?v=' + Date.now(), baseUrl), { cache: 'no-store' }); if (!response.ok) throw new Error('pano.xml tidak dapat dibaca.');
            panoXml = new DOMParser().parseFromString(await response.text(), 'application/xml'); if (panoXml.querySelector('parsererror')) throw new Error('pano.xml tidak valid.');
            readTranslations(); renderLinks();
            if (!panoXml.querySelector('panorama[id="' + drone.node + '"]')) throw new Error('Node drone ' + drone.node + ' tidak ada di pano.xml.');
            const config = panoXml.cloneNode(true); config.documentElement.setAttribute('start', drone.node);
            config.querySelectorAll('hotspots,media,animation,autorotate').forEach((node) => node.remove());
            config.querySelectorAll('input[leveltileurl],input[previewurl]').forEach((input) => ['leveltileurl', 'previewurl'].forEach((attribute) => { const value = input.getAttribute(attribute); if (value) input.setAttribute(attribute, new URL(value.replace(/^\.\//, ''), baseUrl).href); }));
            config.querySelectorAll('control').forEach((control) => { control.setAttribute('lockedmouse', '0'); control.setAttribute('lockedwheel', '0'); control.setAttribute('lockedkeyboard', '0'); });
            player = new window.pano2vrPlayer('lkuPano'); player.readConfigString(new XMLSerializer().serializeToString(config));
            window.setTimeout(() => {
                try { player.setPan(Number(drone.pan) || 0); player.setTilt(Number(drone.tilt) || 0); if (Number(drone.fov) > 0) player.setFov(Number(drone.fov)); } catch (e) { /* tampilan awal bawaan */ }
                ready = true; loading.hidden = true; renderMarkers();
            }, 450);
        } catch (error) { showEmpty('Panorama drone tidak dapat ditampilkan: ' + error.message); }
    }

    /* ---------- Daftar URL ---------- */
    const rowsBox = $('#lkuRows'), search = $('#lkuSearch'); let filter = 'all';
    const lastSegment = (url) => String(url || '').replace(/[?#].*$/, '').replace(/\/+$/, '').split('/').pop().toLowerCase();
    const checkOf = (id, loc) => { const issues = []; if (!String(loc.url || '').trim()) issues.push('URL kosong: hotspot menuju lokasi ini tidak muncul di tur'); else if (lastSegment(loc.url) !== id) issues.push('Ujung alamat (' + lastSegment(loc.url) + ') berbeda dari ID lokasi / nama folder tur (' + id + ')'); return issues; };
    function renderRows() {
        rowsBox.replaceChildren(); const needle = search.value.trim().toLowerCase(); let n = 0;
        for (const [id, loc] of Object.entries(db.locations)) {
            n++; const issues = checkOf(id, loc), local = tours.has(id);
            if (filter === 'local' && !local) continue; if (filter === 'check' && !issues.length) continue;
            if (needle && !(loc.name + ' ' + loc.url + ' ' + id).toLowerCase().includes(needle)) continue;
            const row = el('div', 'lku-row' + (id === selected ? ' is-current' : '')); row.dataset.id = id;
            row.append(el('span', 'lku-no', String(n)));
            const mk = (label, field, value, attrs) => { const wrap = el('label'); wrap.append(el('span', null, label)); const input = el('input'); input.dataset.f = field; input.value = value; Object.assign(input, attrs); wrap.append(input); return wrap; };
            row.append(mk('Judul', 'name', loc.name, { maxLength: 120, placeholder: 'Judul lokasi' }), mk('URL', 'url', loc.url, { spellcheck: false, placeholder: 'badung.govirtual.id/nama-lokasi', inputMode: 'url' }));
            const chips = el('div', 'lku-chips'); chips.append(el('span', 'lku-chip ' + (local ? 'is-ok' : 'is-muted'), local ? 'Tur lokal' : 'Belum ada tur lokal'));
            issues.forEach((text) => { const chip = el('span', 'lku-chip is-warn', 'Perlu dicek'); chip.title = text; chips.append(chip); });
            if (id === selected) chips.append(el('span', 'lku-chip is-here', 'Lokasi dibuka'));
            row.append(chips); rowsBox.append(row);
        }
        if (!rowsBox.children.length) rowsBox.append(el('p', 'lku-none', 'Tidak ada lokasi yang cocok.'));
    }
    rowsBox.addEventListener('input', (event) => {
        const input = event.target.closest('input[data-f]'); if (!input) return;
        const id = input.closest('.lku-row').dataset.id, loc = db.locations[id]; if (!loc) return;
        loc[input.dataset.f] = input.value;
        const chips = $('.lku-chips', input.closest('.lku-row')); const warn = $$('.is-warn', chips); const issues = checkOf(id, loc);
        warn.forEach((chip) => chip.remove());
        issues.forEach((text) => { const chip = el('span', 'lku-chip is-warn', 'Perlu dicek'); chip.title = text; chips.insertBefore(chip, $('.is-here', chips)); });
        setDirty();
        if (input.dataset.f === 'name') { $('#lkuSideTitle').textContent = 'Hotspot di ' + nameOf(selected); $$('.lku-link strong', linksBox).forEach((node, i) => { node.textContent = nameOf(myLinks()[i].to); }); $$('.lku-hs-root').forEach((node) => { const lk = myLinks()[+node.dataset.i]; if (lk) $('b', node).textContent = nameOf(lk.to); }); }
    });
    search.addEventListener('input', renderRows);
    $$('[data-lku-filter]').forEach((button) => button.addEventListener('click', () => { filter = button.dataset.lkuFilter; $$('[data-lku-filter]').forEach((b) => b.classList.toggle('active', b === button)); renderRows(); }));

    /* ---------- simpan ---------- */
    async function save() {
        if (!dirty) return;
        saveButton.disabled = true; saveButton.classList.add('is-saving'); saveLabel.textContent = 'Menyimpan…';
        try {
            const response = await fetch('lokasi-url-save.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf: S0.csrf, data: { locations: db.locations, links: db.links } }) });
            const result = await response.json().catch(() => ({ success: false, message: 'Respons server tidak valid.' }));
            if (!response.ok || !result.success) throw new Error(result.message || 'Gagal menyimpan.');
            db.locations = result.data.locations; db.links = result.data.links; saved = JSON.stringify(db);
            if (baseUrl) { try { const r = await fetch(new URL('pano.xml?v=' + Date.now(), baseUrl), { cache: 'no-store' }); panoXml = new DOMParser().parseFromString(await r.text(), 'application/xml'); readTranslations(); } catch (e) { /* chip terjemahan tetap versi lama */ } }
            setDirty(); renderLinks(); renderRows(); renderMarkers(); toast(result.message); rescanBadge();
        } catch (error) { setDirty(); toast('Gagal menyimpan: ' + error.message, 'error'); }
    }
    saveButton.addEventListener('click', save);
    document.addEventListener('keydown', (event) => { if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's' && root.isConnected) { event.preventDefault(); save(); } });

    /* ---------- Load Titik URL: pulihkan data titik/URL, pemuat tur, dan judul di Terjemahan yang hilang ---------- */
    (() => {
        const button = $('#lkuLoadBtn'); if (!button) return;
        const countEl = $('.oi-load-count', button), baseTitle = button.getAttribute('title') || '';
        const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const when = (iso) => { const date = new Date(iso); if (!iso || Number.isNaN(date.getTime())) return ''; try { return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(date); } catch (e) { return iso; } };
        let dialog = null, scan = S0.restore || null, ticket = 0;

        function setBadge(missing) {
            if (countEl) { countEl.hidden = !missing; countEl.textContent = missing ? String(missing) : ''; }
            button.classList.toggle('has-missing', missing > 0);
            button.setAttribute('aria-label', 'Load Titik URL' + (missing ? ', ' + missing + ' bagian perlu dipulihkan' : ''));
            button.title = missing ? missing + ' bagian titik URL perlu dipulihkan (data, pemuat tur, atau judul di Terjemahan). Klik untuk memulihkannya.' : baseTitle;
        }
        async function post(action) {
            const response = await fetch('lokasi-url-restore.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf: S0.csrf, action, id_lokasi: selected }) });
            let data = null;
            try { data = await response.json(); } catch (e) { throw new Error('Jawaban server tidak terbaca (HTTP ' + response.status + ').'); }
            if (!response.ok || !data.success) throw new Error(data.message || 'Server menolak permintaan.');
            return data;
        }

        function ensureDialog() {
            if (dialog) return dialog;
            dialog = document.createElement('dialog'); dialog.className = 'oi-load-dialog'; dialog.setAttribute('aria-labelledby', 'lkuLoadTitle');
            dialog.innerHTML = '<div class="oi-load-card"><header><h3 id="lkuLoadTitle">Load Titik URL</h3><button type="button" class="oi-load-x" data-lku-close aria-label="Tutup">×</button></header><div class="oi-load-body" aria-live="polite"></div><footer></footer></div>';
            document.body.append(dialog);
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog || event.target.closest('[data-lku-close]')) dialog.close();
                else if (event.target.closest('[data-lku-retry]')) run();
                else if (event.target.closest('[data-lku-apply]')) apply();
            });
            return dialog;
        }
        const body = () => dialog.querySelector('.oi-load-body'), foot = () => dialog.querySelector('footer');
        const closeBtn = '<button type="button" class="oi-load-secondary" data-lku-close>Tutup</button>';
        const check = (kind, title, text) => '<li class="lku-chk is-' + kind + '"><i class="lku-chk-dot" aria-hidden="true"></i><div><b>' + esc(title) + '</b><span>' + text + '</span></div></li>';

        function render(sc) {
            const d = sc.data, l = sc.loader, t = sc.translations, items = [], missing = sc.summary.missing, stuck = sc.summary.unrestorable;
            const stamp = when(d.when) ? ' · ' + esc(when(d.when)) : '';
            if (d.state === 'ok') items.push(check('ok', 'Titik hotspot dan URL', d.locations + ' lokasi · ' + d.links + ' tautan lengkap.'));
            else if (d.state === 'gap') items.push(check('miss', 'Titik hotspot dan URL', 'Hilang ' + [d.lostLocations && d.lostLocations + ' lokasi', d.lostLinks && d.lostLinks + ' tautan hotspot'].filter(Boolean).join(' dan ') + '. Dikembalikan dari ' + esc(d.source) + stamp + '; yang sudah ada tidak ditimpa.'));
            else items.push(check('bad', 'Titik hotspot dan URL', 'Datanya hilang dan tidak ada memori atau cadangan untuk memulihkannya.'));
            if (l.state === 'ok') items.push(check('ok', 'Pemuat tur (main-loader.js)', 'lokasi-database.js sudah dimuat tur.'));
            else if (l.state === 'missing') items.push(check('miss', 'Pemuat tur (main-loader.js)', 'Tidak memuat lokasi-database.js, jadi hotspot tidak tampil di tur. Akan dipasang kembali (cadangan dibuat lebih dulu).'));
            else if (l.state === 'manual') items.push(check('bad', 'Pemuat tur (main-loader.js)', 'Tidak memuat lokasi-database.js dan susunan kodenya tidak dikenali. Tambahkan <code>lokasi-database.js</code> setelah <code>oi-database.js</code> secara manual.'));
            else items.push(check('bad', 'Pemuat tur (main-loader.js)', 'main-loader.js tidak ditemukan di project ini.'));
            if (!t.local) items.push(check('info', 'Judul di Audit › Terjemahan', 'Lokasi ini belum punya tur di folder project atau belum punya hotspot, jadi tidak ada judul yang perlu diperiksa.'));
            else if (!t.missingKeys && !t.restorableValues) items.push(check('ok', 'Judul di Audit › Terjemahan', t.names + ' judul tujuan sudah ada di pano.xml tur ini.'));
            else items.push(check('miss', 'Judul di Audit › Terjemahan', [t.missingKeys && t.missingKeys + ' dari ' + t.names + ' judul belum ada di pano.xml tur ini (hilang setelah export ulang atau belum pernah disimpan)', t.restorableValues && t.restorableValues + ' terjemahan dapat dikembalikan dari memori pano.xml CMS'].filter(Boolean).join('; ') + '.'));
            const lead = missing ? '<p class="oi-load-lead"><b>' + missing + ' bagian perlu dipulihkan.</b> Biasanya terjadi setelah tur di-export ulang dari Pano2VR atau berkas assets diganti. Posisi hotspot sendiri tersimpan di lokasi-database.js, di luar pano.xml.</p>'
                : stuck ? '<p class="oi-load-error" role="alert"><b>Ada yang tidak bisa dipulihkan otomatis.</b> Lihat keterangan merah di bawah; bagian lain sudah lengkap.</p>'
                : '<p class="oi-load-ok"><b>Semuanya lengkap.</b> Tidak ada yang perlu dipulihkan. Titik dan URL diingat otomatis setiap Anda menekan Simpan atau membuka tab ini.</p>';
            body().innerHTML = lead + '<ul class="lku-chk-list">' + items.join('') + '</ul>'
                + (stuck ? '<p class="oi-load-note">' + stuck + ' bagian tidak bisa dipulihkan otomatis (lihat keterangan merah).</p>' : '')
                + (missing && dirty ? '<p class="oi-load-note"><b>Ada perubahan yang belum disimpan.</b> Simpan dulu agar tidak tertimpa, lalu buka Load Titik URL lagi.</p>' : '')
                + (missing ? '<p class="oi-load-note">Berkas yang diubah dicadangkan lebih dulu (data/backups). Terjemahan hanya mengisi yang kosong, tidak menimpa yang sudah ada.</p>' : '');
            foot().innerHTML = closeBtn + (missing ? '<button type="button" class="oi-load-primary" data-lku-apply' + (dirty ? ' disabled' : '') + '>Pulihkan sekarang</button>' : '');
        }
        async function run() {
            const mine = ++ticket;
            body().innerHTML = '<p class="oi-load-wait"><span class="oi-load-spin" aria-hidden="true"></span>Memeriksa titik URL…</p>'; foot().innerHTML = closeBtn;
            try { const data = await post('scan'); if (mine !== ticket) return; scan = data.scan; setBadge(scan.summary.missing); render(scan); }
            catch (error) { if (mine === ticket) { body().innerHTML = '<p class="oi-load-error" role="alert"><b>Gagal memeriksa.</b> ' + esc(error.message) + '</p>'; foot().innerHTML = closeBtn + '<button type="button" class="oi-load-primary" data-lku-retry>Coba lagi</button>'; } }
        }
        async function apply() {
            if (dirty) return;
            const go = dialog.querySelector('[data-lku-apply]'); if (go) { go.disabled = true; go.textContent = 'Memulihkan…'; }
            try {
                const data = await post('apply'), d = data.done;
                db.locations = data.data.locations; db.links = data.data.links; saved = JSON.stringify(db); scan = data.scan; setBadge(scan.summary.missing); setDirty();
                dialog.close(); toast(data.message, /^Tidak ada/.test(data.message) ? 'info' : 'success');
                if (d.locations || d.links) { setTimeout(() => location.reload(), 1100); return; }   // data berubah: muat ulang halaman supaya daftar, penanda, dan panorama memakai data terbaru
                if (baseUrl) { try { const r = await fetch(new URL('pano.xml?v=' + Date.now(), baseUrl), { cache: 'no-store' }); panoXml = new DOMParser().parseFromString(await r.text(), 'application/xml'); readTranslations(); } catch (e) { /* chip lama */ } }
                renderLinks(); renderRows();
            } catch (error) { toast('Gagal memulihkan: ' + error.message, 'error'); if (scan) render(scan); }
        }
        button.addEventListener('click', () => { ensureDialog(); if (!dialog.open) dialog.showModal(); run(); });
        document.addEventListener('close', (event) => { if (dialog && event.target === dialog) button.focus(); }, true);
        rescanBadge = () => post('scan').then((data) => { scan = data.scan; setBadge(scan.summary.missing); }).catch(() => {});

        if (scan && scan.summary) {   // keadaan awal dari server: lencana + satu pemberitahuan per sesi
            setBadge(scan.summary.missing);
            if (scan.summary.missing > 0) {
                let seen = false; const flag = 'lkuLoadHint:' + selected;
                try { seen = sessionStorage.getItem(flag) === '1'; sessionStorage.setItem(flag, '1'); } catch (e) { /* tanpa penyimpanan: tampilkan saja */ }
                if (!seen) toast(scan.summary.missing + ' bagian titik URL belum lengkap (data, pemuat tur, atau judul di Terjemahan). Klik Load Titik URL untuk memulihkannya.', 'warn');
            }
        }
    })();

    renderLinks(); renderRows(); setDirty(); startPlayer();
})();
