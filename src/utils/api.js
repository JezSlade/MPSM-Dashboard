// src/utils/api.js
import { useDebug } from '../contexts/DebugContext';

const BASE_URL = '/mpsm';

export async function apiPost(endpoint, payload) {
  // Note: Can't call useDebug() directly here — this is a non-hook module.
  // Instead, this util should be called inside components/hooks which handle debug.

  try {
    const response = await fetch(`${BASE_URL}/${endpoint}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    if (!response.ok) throw new Error(`HTTP error ${response.status}`);

    const data = await response.json();
    return data;
  } catch (error) {
    // Pass error to caller for debug logging
    throw error;
  }
}
