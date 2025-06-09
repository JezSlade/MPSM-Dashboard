/*!
 * js/app.js
 * ------------------------------------------------------
 * Renders cards from window.roleEndpoints,
 * handles DB/API checks, modals, and JS debug logging.
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // === Setup JS debug logging ===
  const debugMode = window.debugMode === true || window.debugMode === 'true';
  const debugPanel = debugMode ? document.getElementById('debug-panel') : null;
  if (debugMode && !debugPanel) {
    console.warn('Debug panel element missing!');
  }

  // Capture uncaught JS errors
  if (debugMode) {
    window.onerror = function(msg, src, ln, col, err) {
      jsLog(`Error: ${msg} at ${src}:${ln}:${col}`);
    };
    // Wrap console.error
    const origErr = console.error;
    console.error = function(...args) {
      jsLog('Console.error: ' + args.join(' '));
      origErr.apply(console, args);
    };
  }

  /** Append a line to the JS debug panel */
  function jsLog(message) {
    if (!debugPanel) return;
    const line = document.createElement('div');
    line.className = 'debug-log-line';
    line.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
    debugPanel.appendChild(line);
    debugPanel.scrollTop = debugPanel.scrollHeight;
  }

  // === DOM Refs ===
  const dbDot     = document.getElementById('dbStatus');
  const apiDot    = document.getElementById('apiStatus');
  const roleSel   = document.getElementById('roleSelect');
  const cardsView = document.getElementById('cardsViewport');
  const modal     = document.getElementById('modal');
  const modalBody = document.getElementById('modalBody');
  const modalClose= document.getElementById('modalClose');

  document.addEventListener('DOMContentLoaded', function(){
    populateRoles();
    jsLog('Roles dropdown initialized');

    checkConnectivity('db-status.php', dbDot, 'DB');
    checkConnectivity('api-status.php', apiDot, 'API');

    renderCards(roleSel.value);

    roleSel.addEventListener('change', function(){
      jsLog(`Role switched to ${this.value}`);
      renderCards(this.value);
    });

    modalClose.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => {
      if (e.target === modal) modal.style.display = 'none';
    });
  });

  /** Fill the role <select> */
  function populateRoles() {
    Object.keys(window.roleEndpoints).forEach(role => {
      const o = document.createElement('option');
      o.value = role;
      o.textContent = role;
      roleSel.appendChild(o);
    });
    roleSel.value = Object.keys(window.roleEndpoints)[0];
  }

  /**
   * Render all cards for a given role
   * @param {string} role
   */
  function renderCards(role) {
    cardsView.innerHTML = '';
    const cards = window.roleEndpoints[role] || [];
    jsLog(`Rendering ${cards.length} cards for ${role}`);
    cards.forEach(card => {
      const el = document.createElement('div');
      el.className = 'card';
      const h3 = document.createElement('h3'); h3.textContent = card.title;
      const ul = document.createElement('ul');
      card.endpoints.forEach(ep => {
        const li = document.createElement('li');
        li.textContent = `${ep.method} ${ep.path}`;
        ul.appendChild(li);
      });
      el.append(h3, ul);
      el.addEventListener('click', () => {
        jsLog(`Card opened: ${card.title}`);
        openModal(card);
      });
      cardsView.appendChild(el);
    });
  }

  /** Show drilldown modal */
  function openModal(card) {
    modalBody.innerHTML = `<h2>${card.title}</h2>`;
    const ul = document.createElement('ul');
    card.endpoints.forEach(ep => {
      const li = document.createElement('li');
      li.textContent = `${ep.method} ${ep.path}`;
      ul.appendChild(li);
    });
    modalBody.appendChild(ul);
    modal.style.display = 'flex';
  }

  /**
   * Generic HEAD‐request connectivity check
   * @param {string} url
   * @param {HTMLElement} dotEl
   * @param {string} name
   */
  function checkConnectivity(url, dotEl, name) {
    fetch(url, { method: 'HEAD' })
      .then(r => {
        if (r.ok) {
          dotEl.classList.add('ok');
          jsLog(`${name} status OK`);
        } else {
          throw new Error(`Status ${r.status}`);
        }
      })
      .catch(err => {
        dotEl.classList.add('error');
        jsLog(`${name} status ERROR: ${err.message}`);
      });
  }
})();
