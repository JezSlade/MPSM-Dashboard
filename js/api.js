// js/api.js

export async function fetchJson(url, opts = {}) {
  const res = await fetch(url, {
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    ...opts
  });
  if (!res.ok) throw new Error(`API error ${res.status}`);
  return await res.json();
}
