// /public/js/card-interactions.js
// -------------------------------------------------------------------
// Global card & table interactions:
//  • Sorting & expand/collapse of cards
//  • Drilldown buttons inside cards
//  • Slide-out panel drilldowns
//  • **New:** Global table‐row click → set customer cookie & reload
// -------------------------------------------------------------------

console.log('🃏 card-interactions.js loaded');

try {
  document.addEventListener('DOMContentLoaded', () => {
    const grid     = document.getElementById('cardGrid');
    const btnTitle = document.getElementById('sortTitle');
    const btnDate  = document.getElementById('sortDate');
    let   expanded = null;

    // Safety
    if (!grid) {
      console.warn('card-interactions: #cardGrid not found');
    } else {

      // 1) Sorting
      function sortCards(key) {
        const cards = Array.from(grid.children);
        cards.sort((a, b) => {
          const va = (a.dataset[key] || '').toLowerCase();
          const vb = (b.dataset[key] || '').toLowerCase();
          if (key === 'date') return new Date(vb) - new Date(va);
          return va.localeCompare(vb);
        });
        cards.forEach(c => grid.appendChild(c));
      }
      btnTitle?.addEventListener('click', () => sortCards('title'));
      btnDate?.addEventListener('click',  () => sortCards('date'));

      // 2) Expand/collapse cards
      grid.addEventListener('click', e => {
        const card = e.target.closest('.card');
        if (!card) return;
        if (expanded && expanded !== card) expanded.classList.remove('expanded');
        card.classList.toggle('expanded');
        expanded = card.classList.contains('expanded') ? card : null;
      });

      // 3) Drilldown buttons inside cards
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
    }

    // 4) Slide-out panel drill-downs (any <tr> click)
    document.body.addEventListener('click', e => {
      const row = e.target.closest('tr[data-customer]');
      if (!row) return;
      const code     = row.dataset.customer;
      const panel    = document.getElementById('slideOutPanel');
      const titleEl  = document.getElementById('slideoutTitle');
      const content  = document.getElementById('slideoutContent');
      titleEl.textContent = `Details for ${code}`;
      content.innerHTML    = `<p>🔍 Drill-down for <strong>${code}</strong> (dummy content).</p>`;
      panel.classList.add('open');
    });

    // 5) Close slide-out panel
    document.getElementById('slideoutClose')?.addEventListener('click', () => {
      document.getElementById('slideOutPanel')?.classList.remove('open');
    });

    // 6) **NEW**: Global table‐row click → set customer + reload
    document.body.addEventListener('click', e => {
      const row = e.target.closest('tr[data-customer]');
      if (!row) return;
      const cust = row.dataset.customer;
      if (!cust) return;
      // Set cookie
      document.cookie = `customer=${encodeURIComponent(cust)};path=/`;
      // Reload page so PHP picks up $_COOKIE['customer']
      window.location.reload();
    });
  });
} catch (err) {
  console.error('card-interactions.js runtime error:', err);
}
