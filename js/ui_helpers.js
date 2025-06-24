// js/ui_helpers.js
// -------------------------------------------------------------------
// Updated renderTable helper: accepts dynamic columns, now works
// with single-column tables for glassmorphic theme.
// -------------------------------------------------------------------

export function renderTable({ columns, rows, page, totalPages, onPageChange }) {
  // Build table head and body
  const thead = `
    <thead>
      <tr>${columns.map(c => `<th>${c}</th>`).join('')}</tr>
    </thead>
  `;
  const tbody = `
    <tbody>
      ${rows.map(r =>
        `<tr>${columns.map(c => `<td>${r[c] || ''}</td>`).join('')}</tr>`
      ).join('')}
    </tbody>
  `;

  // Pager controls
  const pager = `
    <div class="pager">
      <button ${page <= 1 ? 'disabled' : ''} data-page="${page - 1}">Prev</button>
      <span>Page ${page} of ${totalPages}</span>
      <button ${page >= totalPages ? 'disabled' : ''} data-page="${page + 1}">Next</button>
    </div>
  `;

  // Attach page-change listeners after DOM injection
  setTimeout(() => {
    document.querySelectorAll('.pager button').forEach(btn => {
      btn.addEventListener('click', () => onPageChange(+btn.dataset.page));
    });
  }, 0);

  return `<table>${thead}${tbody}</table>${pager}`;
}
