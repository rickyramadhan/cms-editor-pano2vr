/* pano-thumb-select.js — daftar pilihan panorama dengan thumbnail saat disorot.
 *
 * <select> bawaan browser tidak bisa menampilkan gambar saat opsinya disorot, jadi <select data-pano-thumbs> diberi tampilan kustom:
 * tombol + daftar yang melayang, dan kartu thumbnail di sebelahnya untuk baris yang sedang disorot (mouse maupun panah keyboard).
 * <select> aslinya TETAP ada (disembunyikan secara visual) dan tetap menjadi sumber nilai: kode lain yang membaca `select.value`, memasang
 * pendengar `change`, atau mengisi `select.value = ...` (tanpa event) tetap bekerja; tampilan ikut diperbarui lewat pengait pada properti `value`.
 *
 * Pakai: <select data-pano-thumbs><option value="node1" data-thumb="https://.../thumb.jpg">Judul</option>…</select>
 */
(() => {
  'use strict';
  const valueDesc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
  const indexDesc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'selectedIndex');
  const el = (tag, cls, text) => { const e = document.createElement(tag); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; };
  const CHEVRON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
  const CARD_W = 232, GAP = 10, MARGIN = 8;
  let uid = 0;

  function enhance(select) {
    if (select.dataset.ptsReady) return;
    select.dataset.ptsReady = '1';
    const id = 'pts' + (++uid);
    const opts = Array.from(select.options).map((o) => { const label = o.textContent.trim(), sub = (o.dataset.sub || '').trim(); return { value: o.value, label, sub: sub && sub !== label ? sub : '', thumb: o.dataset.thumb || '' }; });   // sub = keterangan kecil (mis. node ID) di samping nama
    const cs = getComputedStyle(select);

    const root = el('div', 'pts');
    const btn = el('button', 'pts-btn'); btn.type = 'button';
    btn.setAttribute('role', 'combobox'); btn.setAttribute('aria-haspopup', 'listbox'); btn.setAttribute('aria-expanded', 'false'); btn.setAttribute('aria-controls', id + '-list');
    for (const p of ['height', 'minHeight', 'padding', 'borderRadius', 'fontSize', 'fontFamily', 'fontWeight', 'lineHeight']) if (cs[p] && cs[p] !== 'auto' && cs[p] !== 'normal') btn.style[p] = cs[p];   // samakan ukuran dengan <select> aslinya
    const label = el('span', 'pts-label'); btn.append(label); btn.insertAdjacentHTML('beforeend', CHEVRON);
    root.append(btn);

    const list = el('ul', 'pts-list'); list.id = id + '-list'; list.setAttribute('role', 'listbox'); list.hidden = true;
    const items = opts.map((o, i) => { const li = el('li', 'pts-opt'); li.id = id + '-o' + i; li.setAttribute('role', 'option'); li.dataset.i = String(i); li.title = o.sub ? o.label + ' \u00b7 ' + o.sub : o.label; li.append(el('span', 'pts-name', o.label)); if (o.sub) li.append(el('small', 'pts-sub', o.sub)); list.append(li); return li; });
    const card = el('div', 'pts-preview'); card.hidden = true; card.setAttribute('aria-hidden', 'true');
    const frame = el('div', 'pts-img'), img = el('img'), ph = el('span', 'pts-ph', 'Thumbnail belum tersedia'), cap = el('b', 'pts-cap'), capSub = el('small', 'pts-capsub');
    img.alt = ''; img.decoding = 'async'; frame.append(img, ph); card.append(frame, cap, capSub);
    document.body.append(list, card);

    select.before(root);
    select.classList.add('pts-native'); select.tabIndex = -1; select.setAttribute('aria-hidden', 'true');

    let active = -1, open = false, typed = '', typedAt = 0;
    const selected = () => Math.max(0, opts.findIndex((o) => o.value === valueDesc.get.call(select)));

    function refresh() {
      const s = selected();
      label.replaceChildren(); if (opts[s]) { label.append(el('span', 'pts-name', opts[s].label)); if (opts[s].sub) label.append(el('small', 'pts-sub', opts[s].sub)); }
      items.forEach((li, i) => li.setAttribute('aria-selected', String(i === s)));
    }
    function place() {
      const r = btn.getBoundingClientRect(), top = r.bottom + 4, room = innerHeight - top - MARGIN;
      list.style.left = r.left + 'px'; list.style.minWidth = Math.max(r.width, 190) + 'px'; list.style.top = top + 'px';
      list.style.maxHeight = Math.max(160, Math.min(room, 420)) + 'px';
    }
    function showCard(i) {
      const o = opts[i], li = items[i]; if (!o || !li || !open) { card.hidden = true; return; }
      cap.textContent = o.label; capSub.textContent = o.sub; capSub.hidden = !o.sub;
      ph.hidden = !!o.thumb; img.hidden = !o.thumb;
      if (o.thumb) { if (img.getAttribute('src') !== o.thumb) { img.removeAttribute('src'); img.src = o.thumb; } } else img.removeAttribute('src');
      img.onerror = () => { img.hidden = true; ph.hidden = false; };
      img.onload = () => { img.hidden = false; ph.hidden = true; };
      card.hidden = false;
      const lr = list.getBoundingClientRect(), ir = li.getBoundingClientRect(), ch = card.offsetHeight || 190;
      const fitsRight = lr.right + GAP + CARD_W <= innerWidth - MARGIN, fitsLeft = lr.left - GAP - CARD_W >= MARGIN;
      let left, top;
      if (fitsRight || fitsLeft) {   // di samping daftar, sejajar dengan baris yang disorot (kanan dulu, lalu kiri)
        left = fitsRight ? lr.right + GAP : lr.left - GAP - CARD_W;
        top = Math.min(Math.max(MARGIN, ir.top + ir.height / 2 - ch / 2), innerHeight - ch - MARGIN);
      } else {   // layar sempit: di bawah daftar, atau di atasnya bila bawah tidak muat (agar tidak menutupi daftar)
        left = Math.min(Math.max(MARGIN, lr.left), innerWidth - CARD_W - MARGIN);
        top = lr.bottom + GAP; if (top + ch > innerHeight - MARGIN) top = Math.max(MARGIN, lr.top - GAP - ch);
      }
      card.style.left = left + 'px'; card.style.top = top + 'px';
    }
    function setActive(i, scroll = true) {
      if (active >= 0 && items[active]) items[active].classList.remove('is-active');
      active = i;
      if (i < 0 || !items[i]) { btn.removeAttribute('aria-activedescendant'); return; }
      items[i].classList.add('is-active'); btn.setAttribute('aria-activedescendant', items[i].id);
      if (scroll) items[i].scrollIntoView({ block: 'nearest' });
      showCard(i);
    }
    function openList() {
      if (open) return; open = true; root.classList.add('is-open'); btn.setAttribute('aria-expanded', 'true');
      list.hidden = false; place(); setActive(selected());
    }
    function closeList(focus) {
      if (!open) return; open = false; root.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false');
      list.hidden = true; card.hidden = true; setActive(-1, false); if (focus) btn.focus();
    }
    function choose(i) {
      const o = opts[i]; if (!o) return;
      const changed = valueDesc.get.call(select) !== o.value;
      valueDesc.set.call(select, o.value); refresh(); closeList(true);
      if (changed) select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    btn.addEventListener('click', () => (open ? closeList() : openList()));
    btn.addEventListener('keydown', (e) => {
      const k = e.key;
      if (!open) { if (k === 'ArrowDown' || k === 'ArrowUp' || k === 'Enter' || k === ' ') { e.preventDefault(); openList(); } return; }
      const typing = k === ' ' && typed !== '' && Date.now() - typedAt < 700;   // spasi di tengah ketikan (mis. "Drone 2") ikut mencari, bukan memilih
      if (k === 'ArrowDown') { e.preventDefault(); setActive(Math.min(opts.length - 1, active + 1)); }
      else if (k === 'ArrowUp') { e.preventDefault(); setActive(Math.max(0, active - 1)); }
      else if (k === 'Home') { e.preventDefault(); setActive(0); }
      else if (k === 'End') { e.preventDefault(); setActive(opts.length - 1); }
      else if ((k === 'Enter' || k === ' ') && !typing) { e.preventDefault(); choose(active); }
      else if (k === 'Escape') { e.preventDefault(); closeList(true); }
      else if (k === 'Tab') closeList();
      else if (k.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
        if (k === ' ') e.preventDefault();   // ketik huruf/angka: loncat ke opsi yang diawali teks itu
        const now = Date.now(); typed = now - typedAt > 700 ? k.toLowerCase() : typed + k.toLowerCase(); typedAt = now;
        const hit = opts.findIndex((o) => o.label.toLowerCase().startsWith(typed) || o.sub.toLowerCase().startsWith(typed));   // cocok ke nama atau node ID if (hit >= 0) setActive(hit);
      }
    });
    list.addEventListener('mouseover', (e) => { const li = e.target.closest('.pts-opt'); if (li) setActive(Number(li.dataset.i), false); });
    list.addEventListener('mouseleave', () => { card.hidden = true; });
    list.addEventListener('click', (e) => { const li = e.target.closest('.pts-opt'); if (li) choose(Number(li.dataset.i)); });
    list.addEventListener('mousedown', (e) => e.preventDefault());   // fokus tetap di tombol
    document.addEventListener('pointerdown', (e) => { if (open && !root.contains(e.target) && !list.contains(e.target)) closeList(); });
    addEventListener('resize', () => closeList());
    addEventListener('scroll', (e) => { if (open && e.target !== list && !list.contains(e.target)) closeList(); }, true);
    select.addEventListener('change', refresh);

    // kode lain mengisi select.value / selectedIndex langsung (tanpa event): ikutkan tampilan
    Object.defineProperty(select, 'value', { configurable: true, get() { return valueDesc.get.call(this); }, set(v) { valueDesc.set.call(this, v); refresh(); } });
    Object.defineProperty(select, 'selectedIndex', { configurable: true, get() { return indexDesc.get.call(this); }, set(v) { indexDesc.set.call(this, v); refresh(); } });
    refresh();
  }

  const init = () => document.querySelectorAll('select[data-pano-thumbs]').forEach(enhance);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
  window.enhancePanoThumbSelect = enhance;
})();
