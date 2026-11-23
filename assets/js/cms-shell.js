(() => {
    'use strict';

    const search = document.getElementById('locationSearch');
    const list = document.getElementById('sidebarLocationList');
    if (!search || !list) return;

    const locations = Array.from(list.querySelectorAll('.location'));
    const buttons = Array.from(document.querySelectorAll('.sidebar-category-tabs [data-sidebar-category]'));
    const emptyState = document.getElementById('sidebarLocationEmpty');
    const sortSelect = document.getElementById('locationSort');
    const filterSelect = document.getElementById('locationFilter');
    let category = 'all';

    // Urut & saring berdasarkan status kelengkapan (data-score, data-video, …) yang ditulis app.php.
    const num = (location, key) => {
        const value = location.dataset[key];
        return value === undefined || value === '' ? null : Number(value);
    };
    const below = (key) => (location) => { const value = num(location, key); return value !== null && value < 100; };
    const FILTERS = {
        all: () => true,
        complete: (location) => num(location, 'score') === 100,
        incomplete: (location) => { const score = num(location, 'score'); return score !== null && score < 100; },
        video: below('video'),
        audio: below('audio'),
        welcome: below('welcome'),
        translations: below('translations'),
        suspect: (location) => (num(location, 'suspect') || 0) > 0,
        links: below('links'),
        objects: below('objects'),
        unsaved: (location) => location.dataset.unsaved === '1',
    };
    const nameOf = (location) => (location.querySelector('.location-title strong')?.textContent || '').trim();
    const SORTS = {
        'score-asc': (a, b) => (num(a, 'score') ?? 101) - (num(b, 'score') ?? 101) || nameOf(a).localeCompare(nameOf(b), 'id'),
        'score-desc': (a, b) => (num(b, 'score') ?? -1) - (num(a, 'score') ?? -1) || nameOf(a).localeCompare(nameOf(b), 'id'),
        name: (a, b) => nameOf(a).localeCompare(nameOf(b), 'id', { numeric: true }),
    };
    const STORE_KEY = 'cms-sidebar-order';
    const remember = () => { try { localStorage.setItem(STORE_KEY, JSON.stringify({ sort: sortSelect?.value, filter: filterSelect?.value })); } catch (_) {} };
    try {
        const saved = JSON.parse(localStorage.getItem(STORE_KEY) || '{}');
        if (sortSelect && saved.sort && sortSelect.querySelector(`option[value="${saved.sort}"]`)) sortSelect.value = saved.sort;
        if (filterSelect && saved.filter && FILTERS[saved.filter]) filterSelect.value = saved.filter;
    } catch (_) {}
    const applyLocationSort = () => {
        const compare = SORTS[sortSelect?.value];
        const ordered = compare ? [...locations].sort(compare) : locations;   // `locations` = urutan bawaan dari server
        ordered.forEach(location => list.insertBefore(location, emptyState || null));
    };

    const applyLocationFilter = () => {
        const query = search.value.trim().toLocaleLowerCase('id');
        const statusMatches = FILTERS[filterSelect?.value] || FILTERS.all;
        let visible = 0;
        locations.forEach(location => {
            const categoryMatches = category === 'all' || location.dataset.locationCategory === category;
            const searchMatches = query === '' || (location.dataset.locationSearch || '').includes(query);
            const show = categoryMatches && searchMatches && statusMatches(location);
            location.hidden = !show;
            location.style.setProperty('display', show ? 'block' : 'none', 'important');
            if (show) visible++;
        });
        if (emptyState) emptyState.hidden = visible > 0;
    };

    buttons.forEach(button => button.addEventListener('click', event => {
        event.preventDefault();
        category = button.dataset.sidebarCategory || 'all';
        buttons.forEach(item => item.classList.toggle('active', item === button));
        applyLocationFilter();
    }));
    search.addEventListener('input', applyLocationFilter);
    sortSelect?.addEventListener('change', () => { applyLocationSort(); remember(); list.scrollTop = 0; });
    filterSelect?.addEventListener('change', () => { applyLocationFilter(); remember(); list.scrollTop = 0; });
    applyLocationSort();
    applyLocationFilter();
    // lokasi aktif tetap terlihat setelah daftar diurutkan ulang
    list.querySelector('.location.active:not([hidden])')?.scrollIntoView({ block: 'nearest' });

    // Tooltip nama lengkap: hanya muncul bila judul lokasi terpotong (ellipsis).
    const tooltip = document.createElement('div');
    tooltip.className = 'location-tooltip';
    tooltip.setAttribute('role', 'tooltip');
    document.body.appendChild(tooltip);
    const hideTooltip = () => tooltip.classList.remove('visible');
    const showTooltip = location => {
        const title = location.querySelector('.location-title strong');
        if (!title || title.scrollWidth <= title.clientWidth) return hideTooltip();
        tooltip.textContent = title.textContent;
        const box = location.getBoundingClientRect();
        tooltip.style.left = Math.round(box.right + 10) + 'px';
        tooltip.style.top = Math.round(box.top + box.height / 2) + 'px';
        tooltip.classList.add('visible');
        // Jika keluar layar di kanan, tampilkan di bawah kartu.
        const tip = tooltip.getBoundingClientRect();
        if (tip.right > window.innerWidth - 8) {
            tooltip.style.left = Math.round(box.left) + 'px';
            tooltip.style.top = Math.round(box.bottom + 6 + tip.height / 2) + 'px';
        }
    };
    locations.forEach(location => {
        location.addEventListener('mouseenter', () => showTooltip(location));
        location.addEventListener('mouseleave', hideTooltip);
        location.addEventListener('focus', () => showTooltip(location));
        location.addEventListener('blur', hideTooltip);
    });
    list.addEventListener('scroll', hideTooltip, { passive: true });
})();

/* Tooltip ringan untuk elemen [data-tip] (segmen status topik di daftar Lokasi dan garis di bar tab). */
(() => {
    const tip = document.createElement('div');
    tip.className = 'cms-tip'; tip.setAttribute('role', 'tooltip'); document.body.append(tip);
    let current = null;
    const place = element => {
        const box = element.getBoundingClientRect(), size = tip.getBoundingClientRect();
        let left, top;
        if (element.dataset.tipPos === 'bottom') { left = box.left + box.width / 2 - size.width / 2; top = box.bottom + 10; }
        else { left = box.right + 12; top = box.top + box.height / 2 - size.height / 2; if (left + size.width > innerWidth - 8) left = box.left - size.width - 12; }
        left = Math.max(8, Math.min(left, innerWidth - size.width - 8)); top = Math.max(8, Math.min(top, innerHeight - size.height - 8));
        tip.style.left = Math.round(left) + 'px'; tip.style.top = Math.round(top) + 'px';
    };
    const show = element => {
        current = element; tip.textContent = element.dataset.tip || ''; tip.dataset.state = element.dataset.state || '';
        tip.classList.add('show'); place(element);
        // tooltip isu milik item lokasi (cms-shell) disembunyikan selama menunjuk segmen
        element.closest('.location')?.dispatchEvent(new MouseEvent('mouseleave'));
    };
    const hide = () => { current = null; tip.classList.remove('show'); };
    document.addEventListener('pointerover', event => { const element = event.target.closest?.('[data-tip]'); if (element && element !== current) show(element); });
    document.addEventListener('pointerout', event => { const element = event.target.closest?.('[data-tip]'); if (element && !element.contains(event.relatedTarget)) hide(); });
    document.addEventListener('scroll', hide, { capture: true, passive: true });
})();

/* Daftar Lokasi: tombol lebarkan / kecilkan (agar nama lokasi terbaca penuh); pilihan diingat. */
(() => {
    const button = document.getElementById('railToggle'); if (!button) return;
    const root = document.documentElement;
    const apply = wide => {
        root.classList.toggle('rail-wide', wide);
        button.setAttribute('aria-pressed', wide ? 'true' : 'false');
        const label = wide ? 'Kecilkan daftar lokasi' : 'Lebarkan daftar lokasi';
        button.title = label; button.setAttribute('aria-label', label);
    };
    apply(root.classList.contains('rail-wide'));
    button.addEventListener('click', () => {
        const wide = !root.classList.contains('rail-wide');
        apply(wide);
        try { localStorage.setItem('cmsRailWide', wide ? '1' : '0'); } catch (_) {}
        setTimeout(() => window.dispatchEvent(new Event('resize')), 360);   // pemutar panorama & hitungan tinggi menyesuaikan lebar baru
    });
})();

/* Label topik di kartu Lokasi: klik langsung membuka tab topiknya (bukan sekadar membuka lokasi). */
(() => {
    const go = chip => { const href = chip?.dataset?.href; if (href) window.location.href = href; };
    document.addEventListener('click', event => {
        const chip = event.target.closest?.('.loc-chip[data-href]'); if (!chip) return;
        event.preventDefault(); event.stopPropagation(); go(chip);
    }, true);
    document.addEventListener('keydown', event => {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        const chip = event.target.closest?.('.loc-chip[data-href]'); if (!chip) return;
        event.preventDefault(); event.stopPropagation(); go(chip);
    }, true);
})();
