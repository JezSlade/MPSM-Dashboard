/**
 * MPSM Dashboard – Main JS
 *
 * Handles:
 *  - Theme toggle
 *  - Customer dropdown “search filter”
 *  - Debug panel UI
 */

document.addEventListener('DOMContentLoaded', () => {
  console.log('Initializing Dashboard JS…');

  // --- Theme Toggle ---
  const body = document.body;
  const themeBtn = document.getElementById('theme-toggle');
  themeBtn?.addEventListener('click', () => {
    const next = body.classList.toggle('theme-light') ? 'light' : 'dark';
    console.log('Switched to', next);
    localStorage.setItem('dashboardTheme', next);
  });
  // Apply saved theme
  if (localStorage.getItem('dashboardTheme') === 'light') {
    body.classList.add('theme-light');
  }

  // --- Customer Search Filter ---
  const select = document.getElementById('customer-select');
  const search = document.getElementById('customer-search');
  if (select && search) {
    // Cache text/value pairs
    const opts = Array.from(select.options).map(o => ({ value: o.value, text: o.textContent.trim() }));

    // On input, rebuild <select>
    search.addEventListener('input', () => {
      const term = search.value.toLowerCase();
      select.innerHTML = '';
      opts.forEach(o => {
        if (!term || o.text.toLowerCase().includes(term)) {
          const opt = document.createElement('option');
          opt.value = o.value;
          opt.textContent = o.text;
          select.appendChild(opt);
        }
      });
    });
  }

  // --- Debug Panel (untouched) ---
  // … your existing debug-panel toggle/drag logic …

  console.log('JS setup complete.');
});
