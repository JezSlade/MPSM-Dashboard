/**
 * api.js
 * v1.0.0
 * Centralized API client for backend calls.
 * Handles JSON fetch, error catching, and debug logging.
 */

import { debug } from '../contexts/DebugContext';

const BASE_URL = '/mpsm'; // Adjust as needed for your PHP backend base path

export async function apiPost(endpoint, payload) {
  const url = `${BASE_URL}/${endpoint}`;
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      throw new Error(`HTTP error ${response.status}`);
    }

    const data = await response.json();
    debug.log(`API POST ${endpoint} success`);
    return data;
  } catch (error) {
    debug.error(`API POST ${endpoint} failed: ${error.message}`);
    throw error;
  }
}
