// js/ui_helpers.js

export function renderTable({ columns, rows, page, totalPages, onPageChange }) {
  const thead = `<thead><tr>${columns.map(c=>'<' + `th>${c}</th>`).join('')}</tr></thead>`;
  const tbody = `<tbody>${rows.map(r=>
    '<tr>' + columns.map(c=>`<td>${r[c] || ''}</td>`).join('') + '</tr>'
  ).join('')}</tbody>`;

  const pager = `
    <div class="pager">
      <button ${page<=1?'disabled':''} data-page="${page-1}">Prev</button>
      <span>Page ${page} of ${totalPages}</span>
      <button ${page>=totalPages?'disabled':''} data-page="${page+1}">Next</button>
    </div>
  `;

  const table = `<table class="compact-table">${thead}${tbody}</table>`;

  // attach page-change handlers
  setTimeout(() => {
    document.querySelectorAll('.pager button').forEach(btn => {
      btn.addEventListener('click', () => onPageChange(+btn.dataset.page));
    });
  }, 0);

  return table + pager;
}
