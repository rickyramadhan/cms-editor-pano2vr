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
})();
