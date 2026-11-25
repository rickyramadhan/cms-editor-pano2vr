/* Object Informasi: sub-tab Editor / Daftar Konten + saringan daftar konten (hanya tampilan; tidak menulis data). */
(() => {
  'use strict';
  const tabs = [...document.querySelectorAll('[data-oi-pane]')];
  const editor = document.getElementById('oiPaneEditor');
  const konten = document.getElementById('oiPaneKonten');
  if (!tabs.length || !editor || !konten) return;
  const section = editor.closest('.object-editor');

  const show = (pane) => {
    tabs.forEach((t) => {
      const on = t.dataset.oiPane === pane;
      t.classList.toggle('is-on', on);
      t.setAttribute('aria-selected', on ? 'true' : 'false');
      t.tabIndex = on ? 0 : -1;
    });
    editor.hidden = pane !== 'editor';
    konten.hidden = pane !== 'konten';
    section?.classList.toggle('is-konten', pane === 'konten');
    // panorama editor menghitung ukuran saat tampil lagi
    if (pane === 'editor') requestAnimationFrame(() => window.dispatchEvent(new Event('resize')));
  };
  tabs.forEach((t, i) => {
    t.addEventListener('click', () => show(t.dataset.oiPane));
    t.addEventListener('keydown', (ev) => {
      if (ev.key !== 'ArrowRight' && ev.key !== 'ArrowLeft') return;
      ev.preventDefault();
      const n = tabs[(i + (ev.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
      n.focus(); show(n.dataset.oiPane);
    });
  });

  // saringan + pencarian daftar konten
  const items = [...konten.querySelectorAll('#okcList .okc-item')];
  const empty = konten.querySelector('#okcEmpty');
  const search = konten.querySelector('#okcSearch');
  const buttons = [...konten.querySelectorAll('[data-okc-filter]')];
  let filter = 'all', q = '';
  const pass = (el) => {
    const st = el.dataset.status, act = el.dataset.active === '1';
    const ok = filter === 'all' ? true
      : filter === 'lengkap' ? act && st === 'lengkap'
      : filter === 'perlu' ? act && st === 'sebagian'
      : filter === 'pemeliharaan' ? act && st === 'kosong'
      : filter === 'nonaktif' ? !act : true;
    return ok && (!q || (el.dataset.q || '').includes(q));
  };
  const apply = () => {
    let any = false;
    items.forEach((el) => { const v = pass(el); el.hidden = !v; if (v) any = true; });
    if (empty) empty.hidden = any || !items.length;
  };
  buttons.forEach((b) => b.addEventListener('click', () => {
    filter = b.dataset.okcFilter;
    buttons.forEach((x) => { const on = x === b; x.classList.toggle('is-on', on); x.setAttribute('aria-pressed', on ? 'true' : 'false'); });
    apply();
  }));
  search?.addEventListener('input', () => { q = search.value.trim().toLowerCase(); apply(); });

  // selalu mulai di Editor; tautan dengan #oi-konten langsung membuka Daftar Konten
  if (location.hash === '#oi-konten') show('konten');
})();
