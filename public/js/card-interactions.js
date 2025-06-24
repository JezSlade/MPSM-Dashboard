// /public/js/card-interactions.js

console.log('🃏 card-interactions.js loaded');
try {
  document.addEventListener('DOMContentLoaded', () => {
    const grid     = document.getElementById('cardGrid');
    const btnTitle = document.getElementById('sortTitle');
    const btnDate  = document.getElementById('sortDate');
    let   expanded = null;

    // Safety checks
    if (!grid) {
      console.warn('card-interactions: #cardGrid not found');
      return;
    }

    // 1) Sorting
    function sortCards(key) {
      const cards = Array.from(grid.children);
      cards.sort((a, b) => {
        const va = a.dataset[key]?.toLowerCase() || '';
        const vb = b.dataset[key]?.toLowerCase() || '';
        if (key === 'date') {
          return new Date(vb) - new Date(va);
        }
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

    // 3) Drill-in buttons inside cards
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

    // 4) Global table-row drill-down slide-out
    document.addEventListener('click', e => {
      const row = e.target.closest('.glass-card table tbody tr, #cardGrid .card table tbody tr');
      if (!row) return;

      // Identify key for title (data attributes on row)
      const key = row.dataset.customer || row.dataset.id || '';
      const panel   = document.getElementById('slideOutPanel');
      const titleEl = document.getElementById('slideoutTitle');
      const contEl  = document.getElementById('slideoutContent');

      titleEl.textContent = key ? `Details for ${key}` : 'Details';
      contEl.innerHTML    = `<p>🔥 Dummy drill-down content for <strong>${key}</strong>.</p>`;
      panel.classList.add('open');
    });

    // 5) Close button
    document.getElementById('slideoutClose')?.addEventListener('click', () => {
      document.getElementById('slideOutPanel')?.classList.remove('open');
    });
  });
} catch (err) {
  console.error('card-interactions.js runtime error:', err);
}
