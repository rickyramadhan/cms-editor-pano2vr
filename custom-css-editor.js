(function () {
  const form = document.querySelector('.custom-css-editor');
  if (!form) return;

  const source = document.getElementById('customCssSource');
  const raw = document.getElementById('customCssRaw');
  const visual = document.getElementById('cssVisualEditor');
  const search = document.getElementById('cssSearch');
  const rawPanel = document.getElementById('cssRawEditor');
  const toolbarActions = document.querySelector('.css-toolbar-actions');
  let activeGroup = 'general';
  let blocks = [];
  const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[character]);

  function closingBrace(text, opening) {
    let depth = 0, quote = '', comment = false;
    for (let index = opening; index < text.length; index++) {
      const character = text[index], next = text[index + 1];
      if (comment) { if (character === '*' && next === '/') { comment = false; index++; } continue; }
      if (!quote && character === '/' && next === '*') { comment = true; index++; continue; }
      if (quote) { if (character === '\\') index++; else if (character === quote) quote = ''; continue; }
      if (character === '"' || character === "'") quote = character;
      else if (character === '{') depth++;
      else if (character === '}' && --depth === 0) return index;
    }
    return -1;
  }

  function declarations(body) {
    const result = [];
    let part = '', depth = 0, quote = '', comment = false;
    const push = () => {
      const text = part.trim(); part = '';
      if (!text) return;
      const colon = text.indexOf(':');
      result.push(colon < 1 ? { property: '', value: text } : { property: text.slice(0, colon).trim(), value: text.slice(colon + 1).trim() });
    };
    for (let index = 0; index < body.length; index++) {
      const character = body[index], next = body[index + 1];
      if (comment) { part += character; if (character === '*' && next === '/') { part += next; index++; comment = false; } continue; }
      if (!quote && character === '/' && next === '*') { part += character + next; index++; comment = true; continue; }
      if (quote) { part += character; if (character === '\\' && next) part += body[++index]; else if (character === quote) quote = ''; continue; }
      if (character === '"' || character === "'") { quote = character; part += character; }
      else if (character === '(') { depth++; part += character; }
      else if (character === ')') { depth = Math.max(0, depth - 1); part += character; }
      else if (character === ';' && depth === 0) push();
      else part += character;
    }
    push(); return result;
  }

  function parse(text) {
    const result = [];
    let index = 0;
    while (index < text.length) {
      while (/\s/.test(text[index] || '')) index++;
      if (index >= text.length) break;
      if (text.startsWith('/*', index)) {
        const end = text.indexOf('*/', index + 2), stop = end < 0 ? text.length : end + 2;
        result.push({ type: 'raw', value: text.slice(index, stop) }); index = stop; continue;
      }
      if (text.startsWith('@import', index)) {
        const end = text.indexOf(';', index), stop = end < 0 ? text.length : end + 1;
        result.push({ type: 'import', value: text.slice(index + 7, end < 0 ? text.length : end).trim() }); index = stop; continue;
      }
      const opening = text.indexOf('{', index);
      if (opening < 0) { const tail = text.slice(index).trim(); if (tail) result.push({ type: 'raw', value: tail }); break; }
      const header = text.slice(index, opening).trim(), closing = closingBrace(text, opening);
      if (closing < 0) { result.push({ type: 'raw', value: text.slice(index) }); break; }
      const body = text.slice(opening + 1, closing);
      if (/^@media\b/i.test(header)) result.push({ type: 'media', query: header.replace(/^@media\s*/i, ''), children: parse(body) });
      else result.push({ type: 'rule', selector: header, declarations: declarations(body), font: /^@font-face$/i.test(header) || /(^|,)\s*\.font[-_]/i.test(header) });
      index = closing + 1;
    }
    return result;
  }

  function serialize(items, indent = '') {
    return items.map(block => {
      if (block.type === 'raw') return indent + block.value.trim();
      if (block.type === 'import') return indent + '@import ' + block.value.trim() + ';';
      if (block.type === 'media') return indent + '@media ' + block.query.trim() + ' {\n' + serialize(block.children, indent + '  ') + '\n' + indent + '}';
      const rows = block.declarations.filter(row => row.property.trim() || row.value.trim()).map(row => indent + '  ' + (row.property.trim() ? row.property.trim() + ': ' : '') + row.value.trim() + ';').join('\n');
      return indent + block.selector.trim() + ' {\n' + rows + '\n' + indent + '}';
    }).filter(Boolean).join('\n\n');
  }

  function group(block) {
    if (block.type === 'media') return 'media';
    if (block.type === 'import' || (block.type === 'rule' && block.font)) return 'fonts';
    return block.type === 'rule' ? 'general' : 'hidden';
  }

  function resolve(path) {
    const parts = String(path).split('.').map(Number);
    return parts.length === 1 ? blocks[parts[0]] : blocks[parts[0]].children[parts[1]];
  }

  function propertyRows(path, rows) {
    return rows.map((row, index) => '<div class="css-property-row" data-property-path="' + path + '" data-property-index="' + index + '"><input class="css-property-name" value="' + escapeHtml(row.property) + '" placeholder="property"><input class="css-property-value" value="' + escapeHtml(row.value) + '" placeholder="value"><button type="button" class="css-icon-button" data-remove-property title="Hapus properti">×</button></div>').join('');
  }

  function ruleCard(block, path) {
    const searchable = (block.selector + ' ' + block.declarations.map(row => row.property + ' ' + row.value).join(' ')).toLowerCase();
    return '<article class="css-rule-card" data-css-search="' + escapeHtml(searchable) + '"><header class="css-rule-head"><input class="css-selector" data-selector-path="' + path + '" value="' + escapeHtml(block.selector) + '"><div class="css-rule-tools"><span class="css-kind">' + (block.font ? 'Font' : 'Class') + '</span><button type="button" class="css-icon-button" data-remove-rule="' + path + '">×</button></div></header><div class="css-properties">' + propertyRows(path, block.declarations) + '<button type="button" class="css-add-property" data-add-property="' + path + '">＋ Tambah properti</button></div></article>';
  }

  function filter() {
    const query = search.value.trim().toLowerCase();
    visual.querySelectorAll('[data-css-search]').forEach(card => card.hidden = Boolean(query && !card.dataset.cssSearch.includes(query)));
  }

  function render() {
    const html = [];
    const counts = { general: 0, fonts: 0, media: 0 };
    blocks.forEach((block, index) => {
      const section = group(block); if (counts[section] !== undefined) counts[section]++;
      if (section !== activeGroup) return;
      if (block.type === 'rule') html.push(ruleCard(block, String(index)));
      if (block.type === 'import') html.push('<article class="css-import-card" data-css-search="' + escapeHtml(block.value.toLowerCase()) + '"><strong>@import</strong><input data-import-path="' + index + '" value="' + escapeHtml(block.value) + '"><button type="button" class="css-icon-button" data-remove-rule="' + index + '">×</button></article>');
      if (block.type === 'media') html.push('<section class="css-media-section" data-css-search="' + escapeHtml((block.query + ' ' + block.children.map(child => child.selector || '').join(' ')).toLowerCase()) + '"><header class="css-media-head"><strong>@media</strong><input data-media-path="' + index + '" value="' + escapeHtml(block.query) + '"><button type="button" class="css-icon-button" data-remove-rule="' + index + '">×</button></header><div class="css-media-rules">' + block.children.map((child, childIndex) => child.type === 'rule' ? ruleCard(child, index + '.' + childIndex) : '').join('') + '<button type="button" class="css-add-property" data-add-media-rule="' + index + '">＋ Tambah class di media query</button></div></section>');
    });
    document.getElementById('cssGeneralCount').textContent = counts.general;
    document.getElementById('cssFontCount').textContent = counts.fonts;
    document.getElementById('cssMediaCount').textContent = counts.media;
    visual.innerHTML = html.join('') || '<div class="css-empty">Belum ada item pada kelompok ini.</div>';
    filter();
  }

  blocks = parse(source.value);
  document.querySelectorAll('[data-css-group]').forEach(button => button.addEventListener('click', () => {
    document.querySelectorAll('[data-css-group]').forEach(item => item.classList.remove('active'));
    button.classList.add('active'); activeGroup = button.dataset.cssGroup;
    const showRaw = activeGroup === 'raw'; visual.hidden = showRaw; rawPanel.hidden = !showRaw; toolbarActions.hidden = showRaw;
    if (showRaw) raw.value = serialize(blocks) + '\n'; else render();
  }));
  search.addEventListener('input', filter);
  visual.addEventListener('input', event => {
    if (event.target.dataset.selectorPath !== undefined) resolve(event.target.dataset.selectorPath).selector = event.target.value;
    if (event.target.dataset.mediaPath !== undefined) blocks[Number(event.target.dataset.mediaPath)].query = event.target.value;
    if (event.target.dataset.importPath !== undefined) blocks[Number(event.target.dataset.importPath)].value = event.target.value;
    const row = event.target.closest('[data-property-path]');
    if (row) { const item = resolve(row.dataset.propertyPath).declarations[Number(row.dataset.propertyIndex)]; item.property = row.querySelector('.css-property-name').value; item.value = row.querySelector('.css-property-value').value; }
  });
  visual.addEventListener('click', event => {
    const removeRule = event.target.closest('[data-remove-rule]');
    if (removeRule && confirm('Hapus item CSS ini?')) { const parts = removeRule.dataset.removeRule.split('.').map(Number); if (parts.length === 1) blocks.splice(parts[0], 1); else blocks[parts[0]].children.splice(parts[1], 1); render(); return; }
    const addProperty = event.target.closest('[data-add-property]'); if (addProperty) { resolve(addProperty.dataset.addProperty).declarations.push({ property: '', value: '' }); render(); return; }
    const removeProperty = event.target.closest('[data-remove-property]'); if (removeProperty) { const row = removeProperty.closest('[data-property-path]'); resolve(row.dataset.propertyPath).declarations.splice(Number(row.dataset.propertyIndex), 1); render(); return; }
    const addMedia = event.target.closest('[data-add-media-rule]'); if (addMedia) { blocks[Number(addMedia.dataset.addMediaRule)].children.push({ type: 'rule', selector: '.class-baru', declarations: [{ property: 'property', value: 'value' }], font: false }); render(); }
  });
  document.getElementById('addCssRule').addEventListener('click', () => { blocks.push({ type: 'rule', selector: '.class-baru', declarations: [{ property: 'property', value: 'value' }], font: false }); document.querySelector('[data-css-group="general"]').click(); });
  document.getElementById('applyRawCss').addEventListener('click', () => { blocks = parse(raw.value); document.querySelector('[data-css-group="general"]').click(); });
  form.addEventListener('submit', () => { if (activeGroup === 'raw') blocks = parse(raw.value); source.value = serialize(blocks) + '\n'; });
  render();
}());
