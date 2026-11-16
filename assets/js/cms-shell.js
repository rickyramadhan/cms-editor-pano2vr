(() => {
    'use strict';

    const search = document.getElementById('locationSearch');
    const list = document.getElementById('sidebarLocationList');
    if (!search || !list) return;

    const locations = Array.from(list.querySelectorAll('.location'));
    const buttons = Array.from(document.querySelectorAll('.sidebar-category-tabs [data-sidebar-category]'));
    const emptyState = document.getElementById('sidebarLocationEmpty');
    let category = 'all';

    const applyLocationFilter = () => {
        const query = search.value.trim().toLocaleLowerCase('id');
        let visible = 0;
        locations.forEach(location => {
            const categoryMatches = category === 'all' || location.dataset.locationCategory === category;
            const searchMatches = query === '' || (location.dataset.locationSearch || '').includes(query);
            const show = categoryMatches && searchMatches;
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
    applyLocationFilter();

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
