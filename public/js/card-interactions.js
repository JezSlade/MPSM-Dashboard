// /public/js/card-interactions.js

// → Load check
console.log('🃏 card-interactions.js loaded');

try {
  document.addEventListener('DOMContentLoaded', () => {
    const grid     = document.getElementById('cardGrid');
    const btnTitle = document.getElementById('sortTitle');
    const btnDate  = document.getElementById('sortDate');
    let   expanded = null;

    if (!grid) {
      console.warn('card-interactions: #cardGrid not found');
      return;
    }

    // Sort cards by data-title or data-date
    function sortCards(key) {
      const cards = Array.from(grid.children);
      cards.sort((a, b) => {
        const va = a.dataset[key].toLowerCase();
        const vb = b.dataset[key].toLowerCase();
        if (key === 'date') {
          return new Date(vb) - new Date(va);
        }
        return va.localeCompare(vb);
      });
      cards.forEach(c => grid.appendChild(c));
    }

    btnTitle?.addEventListener('click', () => sortCards('title'));
    btnDate?.addEventListener('click',  () => sortCards('date'));

    // Expand/collapse on card click
    grid.addEventListener('click', e => {
      const card = e.target.closest('.card');
      if (!card) return;

      if (expanded && expanded !== card) {
        expanded.classList.remove('expanded');
      }
      if (card.classList.contains('expanded')) {
        card.classList.remove('expanded');
        expanded = null;
      } else {
        card.classList.add('expanded');
        expanded = card;
      }
    });

    // Drill‐down buttons inside cards
    grid.addEventListener('click', e => {
      if (!e.target.matches('.drill-button')) return;
      e.stopPropagation();

      const card = e.target.closest('.card');
      const id   = card.dataset.id;
      const type = e.target.dataset.type;
      const area = card.querySelector('.drill-area');

      fetch(`?action=drilldown&id=${id}&type=${type}`)
        .then(r => r.text())
        .then(html => area.innerHTML = html)
        .catch(err => console.error('Drilldown fetch error:', err));
    });

    // → NEW: Customer‐row click selection in the CustomersCard
    grid.addEventListener('click', e => {
      // only fire if click inside the CustomersCard table
      const row = e.target.closest('#CustomersCard table tbody tr');
      if (!row) return;

      // assume first <td> holds the ExternalIdentifier / CustomerCode
      const codeCell = row.cells[0];
      if (!codeCell) return;

      const customerCode = codeCell.textContent.trim();
      console.log('Selecting customer:', customerCode);

      // helper to set or update the ?customer= query param
      function updateQS(uri, key, val) {
        const re = new RegExp('([?&])' + key + '=.*?(&|$)', 'i');
        const sep = uri.indexOf('?') !== -1 ? '&' : '?';
        if (uri.match(re)) {
          return uri.replace(re, '$1' + key + '=' + val + '$2');
        } else {
          return uri + sep + key + '=' + val;
        }
      }

      // reload page with new customer param
      window.location.href = updateQS(window.location.href, 'customer', encodeURIComponent(customerCode));
    });
  });
} catch (err) {
  console.error('card-interactions.js runtime error:', err);
}
