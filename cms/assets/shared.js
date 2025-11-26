/**
 * Shared utilities for Command Center
 */

// Fetch JSON with timeout and sane defaults
async function fetchJson(url, options = {}) {
  const { timeoutMs = 10000, headers = {}, credentials = 'same-origin', signal, method, body } = options;
  const controller = signal ? null : new AbortController();
  const to = signal ? null : setTimeout(() => controller.abort(), timeoutMs);
  try {
    const res = await fetch(url, {
      method: method || 'GET',
      headers: Object.assign({ 'Accept': 'application/json' }, headers),
      credentials,
      body,
      signal: signal || (controller && controller.signal) || undefined,
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    return data;
  } finally {
    if (to) clearTimeout(to);
  }
}

function escapeHtml(value) {
  const div = document.createElement('div');
  div.textContent = value ?? '';
  return div.innerHTML;
}

function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <div class="toast-content">
      <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
      <span>${escapeHtml(message)}</span>
    </div>
  `;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'slideOut 0.3s ease-in-out';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

/*
CHANGELOG
2025-11-26 Codex
- Added shared.js with fetchJson, escapeHtml, and showToast used by Command Center tabs.
*/

