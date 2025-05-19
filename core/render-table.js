// v2.1.0 [Simplified Table: SEID Only Fields + LCARS Pagination]
import { eventBus } from './event-bus.js';
import { store } from './store.js';

const DISPLAY_COLUMNS = ['SEID', 'Product.Brand', 'Product.Model', 'SerialNumber', 'IpAddress'];
const SETTINGS_KEY = 'mpsm_table_prefs_v2';
const PAGE_SIZE_OPTIONS = [10, 25, 50, 100];

function getSEID(row) {
  return row.AssetNumber || row.ExternalIdentifier || '—';
}

function getPreferences() {
  const raw = localStorage.getItem(SETTINGS_KEY);
  if (!raw) {
    return {
      pageSize: 25,
      sort: { column: 'SEID', direction: 'asc' }
    };
  }
  return JSON.parse(raw);
}

function savePreferences(prefs) {
  localStorage.setItem(SETTINGS_KEY, JSON.stringify(prefs));
}

function getColumnValue(row, key) {
  if (key === 'SEID') return getSEID(row);
  const parts = key.split('.');
  return parts.reduce((acc, part) => acc?.[part], row) ?? '—';
}

export function renderTable(containerId, rawData) {
  if (!Array.isArray(rawData)) return;

  const prefs = getPreferences();
  const data = rawData.map(row => ({ ...row, SEID: getSEID(row) }));
  const container = document.getElementById(containerId);
  container.innerHTML = "";

  // Controls
  const controls = document.createElement('div');
  controls.className = 'table-controls';

  const versionTag = document.createElement('span');
  versionTag.textContent = 'MPSM v2.1.0';
  versionTag.style.marginRight = '1rem';
  versionTag.style.color = '#ffaa66';
  versionTag.style.fontWeight = 'bold';

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
  controls.appendChild(pageSizeSel);
  container.appendChild(controls);

  // Sort
  const sorted = [...data].sort((a, b) => {
    const col = prefs.sort.column;
    const dir = prefs.sort.direction === 'desc' ? -1 : 1;
    const aVal = getColumnValue(a, col).toString().toLowerCase();
    const bVal = getColumnValue(b, col).toString().toLowerCase();
    return aVal > bVal ? dir : aVal < bVal ? -dir : 0;
  });

  const pageSize = prefs.pageSize;
  let page = 0;
  const totalPages = Math.ceil(sorted.length / pageSize);

  const renderPage = () => {
    const table = document.createElement('table');
    table.className = 'mpsm-table';

    const thead = document.createElement('thead');
    const headRow = document.createElement('tr');
    DISPLAY_COLUMNS.forEach(key => {
      const th = document.createElement('th');
      th.textContent = key.replace('Product.', '');
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
      DISPLAY_COLUMNS.forEach(key => {
        const td = document.createElement('td');
        td.textContent = getColumnValue(row, key);
        tr.appendChild(td);
      });
      tbody.appendChild(tr);
    });

    table.appendChild(tbody);
    container.appendChild(table);

    const pager = document.createElement('div');
    pager.className = 'table-pager';
    pager.innerHTML = `
      <div style="margin-top:1rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:0 1rem;">
        <button ${page === 0 ? 'disabled' : ''}>⬅️ Prev</button>
        <span style="color:#cceeff;">Page ${page + 1} / ${totalPages}</span>
        <button ${page >= totalPages - 1 ? 'disabled' : ''}>Next ➡️</button>
      </div>
    `;
    const buttons = pager.querySelectorAll('button');
    if (buttons.length === 2) {
      const [prevBtn, nextBtn] = buttons;
      prevBtn.onclick = () => { page--; renderTable(containerId, rawData); };
      nextBtn.onclick = () => { page++; renderTable(containerId, rawData); };
    }
    container.appendChild(pager);
  };

  renderPage();
}
