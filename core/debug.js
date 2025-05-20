/**
 * v1.1.0 [Fix: Export Default & Toggle]
 */
import { get } from './dom.js';

const debugPanel = get('debug-panel');
const debugLogs  = get('debug-logs');
const toggle     = get('debug-toggle');
const closeBtn   = get('debug-close');

let enabled = false;

function render(message, type = 'log') {
  if (!enabled) return;
  const entry = document.createElement('div');
  entry.classList.add('debug-entry', type);
  entry.textContent = `[${new Date().toISOString()}] [${type.toUpperCase()}] ${message}`;
  debugLogs.appendChild(entry);
  debugLogs.scrollTop = debugLogs.scrollHeight;
}

export const debug = {
  log: msg => render(msg, 'log'),
  warn: msg => render(msg, 'warn'),
  error: msg => render(msg, 'error'),
};

export default debug;

// Wire up toggle switch
toggle.addEventListener('change', () => {
  enabled = toggle.checked;
  debugPanel.classList.toggle('minimized', !enabled);
  if (enabled) {
    debug.log('Debug mode enabled');
  }
});

// Allow close button to turn it off
closeBtn.addEventListener('click', () => {
  toggle.checked = false;
  toggle.dispatchEvent(new Event('change'));
});
