// v2.0.1 [Fixed Template Literal + SEID + Full Table Engine]
import { eventBus } from './event-bus.js';
import { store } from './store.js';

const SETTINGS_KEY = 'mpsm_table_prefs_v2';
const PAGE_SIZE_OPTIONS = [10, 25, 50, 100];

function getSEID(row) {
  return row.AssetNumber || row.ExternalIdentifier || '—';
}

function getPreferences(columns) {
  const raw = localStorage.getItem(SETTINGS_KEY);
  if (!raw) {
    return {
      visible: Object.fromEntries(columns.map(k => [k, true])),
      order: ['SEID', ...columns.filter(c => c !== 'SEID')],
      pageSize: 25,
      sort: { column: 'SEID', direction: 'asc' }
    };
  }
  return JSON.parse(raw);
}

function savePreferences(prefs) {
  localStorage.setItem(SETTINGS_KEY, JSON.stringify(prefs));
}

export function renderTable(containerId, rawData) {
  if (!Array.isArray(rawData)) return;

  const data = rawData.map(row => ({ ...row, SEID: getSEID(row) }));
  const allKeys = Object.keys(data[0] || {});
  if (!allKeys.includes('SEID')) allKeys.unshift('SEID');

  const prefs = getPreferences(allKeys);
  const container = document.getElementById(containerId);
  container.innerHTML = "";

  // Controls
  const controls = document.createElement('div');
  controls.className = 'table-controls';

  const versionTag = document.createElement('span');
  versionTag.textContent = 'MPSM v2.0.1';
  versionTag.style.marginRight = '1rem';
  versionTag.style.color = '#ffaa66';
  versionTag.style.fontWeight = 'bold';

  const fieldToggle = document.createElement('select');
  fieldToggle.multiple = true;
  fieldToggle.size = 6;
  fieldToggle.className = 'field-selector';
  allKeys.forEach(key => {
    const opt = document.createElement('option');
    opt.value = key;
    opt.textContent = key;
    opt.selected = prefs.visible[key] !== false;
    fieldToggle.appendChild(opt);
  });
  fieldToggle.onchange = () => {
    const visible = {};
    Array.from(fieldToggle.options).forEach(opt => {
      visible[opt.value] = opt.selected;
    });
    prefs.visible = visible;
    savePreferences(prefs);
    renderTable(containerId, rawData);
  };

  const pageSizeSel = document.createElement('select');
  PAGE_SIZE_OPTIONS.forEach(size => {
    const opt = document.createElement('option');
    opt.value = size;
    opt.textContent = size + ' rows/page';
    if (size === prefs.pageSize) opt.selected = true;
    pageSizeSel.appendChild(opt);
  });
  pageSizeSel.onchange = () => {
    prefs.pageSize = parseInt(pageSizeSel.value, 10);
    savePreferences(prefs);
    renderTable(containerId, rawData);
  };

  controls.appendChild(versionTag);
  controls.appendChild(fieldToggle);
  controls.appendChild(pageSizeSel);
  container.appendChild(controls);

  // Sort
  const sorted = [...data].sort((a, b) => {
    const col = prefs.sort.column;
    const dir = prefs.sort.direction === 'desc' ? -1 : 1;
    const aVal = a[col]?.toString().toLowerCase() || '';
    const bVal = b[col]?.toString().toLowerCase() || '';
    return aVal > bVal ? dir : aVal < bVal ? -dir : 0;
  });

  const pageSize = prefs.pageSize;
  let page = 0;
  const totalPages = Math.ceil(sorted.length / pageSize);

  const renderPage = () => {
    const keys = prefs.order.filter(k => prefs.visible[k] !== false);
    const table = document.createElement('table');
    table.className = 'mpsm-table';

    const thead = document.createElement('thead');
    const headRow = document.createElement('tr');
    keys.forEach(key => {
      const th = document.createElement('th');
      th.textContent = key;
      th.style.cursor = 'pointer';
      th.onclick = () => {
        if (prefs.sort.column === key) {
          prefs.sort.direction = prefs.sort.direction === 'asc' ? 'desc' : 'asc';
        } else {
          prefs.sort.column = key;
          prefs.sort.direction = 'asc';
        }
        savePreferences(prefs);
        renderTable(containerId, rawData);
      };
      headRow.appendChild(th);
    });
    thead.appendChild(headRow);
    table.appendChild(thead);

    const tbody = document.createElement('tbody');
    const pageData = sorted.slice(page * pageSize, (page + 1) * pageSize);
    pageData.forEach(row => {
      const tr = document.createElement('tr');
      keys.forEach(key => {
        const td = document.createElement('td');
        td.textContent = row[key] ?? '—';
        tr.appendChild(td);
      });
      tbody.appendChild(tr);
    });

    table.appendChild(tbody);
    container.appendChild(table);

    const pager = document.createElement('div');
    pager.className = 'table-pager';
    pager.innerHTML = `
      <button ${page === 0 ? 'disabled' : ''}>⬅️ Prev</button>
      <span>Page ${page + 1} / ${totalPages}</span>
      <button ${page >= totalPages - 1 ? 'disabled' : ''}>Next ➡️</button>
    `;
    const [prevBtn, , nextBtn] = pager.querySelectorAll('button');
    prevBtn.onclick = () => { page--; renderTable(containerId, rawData); };
    nextBtn.onclick = () => { page++; renderTable(containerId, rawData); };
    container.appendChild(pager);
  };

  renderPage();
}
