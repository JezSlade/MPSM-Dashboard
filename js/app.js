/*!
 * js/app.js
 * ------------------------------------------------------
 * Renders one card per endpoint from window.allEndpoints,
 * handles DB/API HEAD checks, drill‐down modal, and
 * JS debug logging into the Debug Panel.
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  const debugMode  = window.debugMode === true || window.debugMode === 'true';
  const debugPanel = debugMode ? document.getElementById('debug-panel') : null;

  // Capture uncaught JS errors
  if (debugMode) {
    window.onerror = function(msg, src, ln, col) {
      jsLog(`Error: ${msg} at ${src}:${ln}:${col}`);
    };
    const origErr = console.error;
    console.error = function(...args) {
      jsLog('Console.error: ' + args.join(' '));
      origErr.apply(console, args);
    };
  }

  /**
   * Append a line to the JS Debug Panel.
   */
  function jsLog(msg) {
    if (!debugPanel) return;
    const line = document.createElement('div');
    line.className = 'debug-log-line';
    line.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
    debugPanel.appendChild(line);
    debugPanel.scrollTop = debugPanel.scrollHeight;
  }

  // DOM refs
  const dbDot     = document.getElementById('dbStatus');
  const apiDot    = document.getElementById('apiStatus');
  const cardsView = document.getElementById('cardsViewport');
  const modal     = document.getElementById('modal');
  const modalBody = document.getElementById('modalBody');
  const modalClose= document.getElementById('modalClose');

  document.addEventListener('DOMContentLoaded', function(){
    // Check connectivity
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // Render all endpoint cards
    renderAllCards();

    // Modal close handlers
    modalClose.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => {
      if (e.target === modal) modal.style.display = 'none';
    });
  });

  /**
   * Render one card per endpoint.
   */
  function renderAllCards() {
    cardsView.innerHTML = '';
    const endpoints = window.allEndpoints || [];
    jsLog(`Rendering ${endpoints.length} endpoint cards`);
    endpoints.forEach(op => {
      const card = document.createElement('div');
      card.className = 'card';
      card.innerHTML = `
        <h3>${op.method} ${op.path}</h3>
        <p class="summary">${op.summary || ''}</p>
      `;
      card.addEventListener('click', () => openModal(op));
      cardsView.appendChild(card);
    });
  }

  /**
   * Open the drill‐down modal for a single endpoint.
   */
  function openModal(op) {
    modalBody.innerHTML = `
      <h2>${op.method} ${op.path}</h2>
      <p><strong>Summary:</strong> ${op.summary || '(none)'}</p>
      <p><strong>Description:</strong> ${op.description || '(none)'}</p>
    `;
    modal.style.display = 'flex';
    jsLog(`Opened modal for ${op.method} ${op.path}`);
  }

  /**
   * Generic HEAD‐request connectivity check.
   */
  function checkConn(url, dotEl, name) {
    fetch(url, { method: 'HEAD' })
      .then(r => {
        if (r.ok) {
          dotEl.classList.add('ok');
          jsLog(`${name} HEAD OK`);
        } else {
          throw new Error(`HTTP ${r.status}`);
        }
      })
      .catch(err => {
        dotEl.classList.add('error');
        jsLog(`${name} HEAD ERROR: ${err.message}`);
      });
  }
})();
