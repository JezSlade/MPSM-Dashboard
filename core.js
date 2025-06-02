// Centralized utility functions to avoid redundancy
const Core = {
  // Debug logging
  logError(message, error) {
    const debugLogs = document.getElementById('debugLogs');
    const timestamp = new Date().toLocaleString();
    const logEntry = document.createElement('p');
    logEntry.textContent = `[${timestamp}] ${message}: ${error.message || error}`;
    debugLogs.appendChild(logEntry);
  },

  // Centralized fetch with authentication and retry logic
  async fetchWithAuth(endpoint, options = {}, retries = 3) {
    try {
      const token = await Core.getAccessToken();
      const headers = {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Cache-Control': 'no-cache',
        ...options.headers,
      };

      const response = await fetch(`${CONFIG.BASE_URL}${endpoint}`, {
        ...options,
        headers,
      });

      if (!response.ok) {
        if (response.status === 401 && retries > 0) {
          // Token expired, refresh and retry
          await Core.refreshAccessToken();
          return Core.fetchWithAuth(endpoint, options, retries - 1);
        }
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      return await response.json();
    } catch (error) {
      if (retries > 0) {
        await new Promise(resolve => setTimeout(resolve, 1000)); // Wait 1s before retry
        return Core.fetchWithAuth(endpoint, options, retries - 1);
      }
      Core.logError(`Fetch failed for ${endpoint}`, error);
      throw error;
    }
  },

  // Get access token
  async getAccessToken() {
    try {
      const cachedToken = localStorage.getItem('access_token');
      const expiresAt = localStorage.getItem('token_expires_at');
      if (cachedToken && expiresAt && Date.now() < parseInt(expiresAt)) {
        return cachedToken;
      }

      const response = await fetch(CONFIG.TOKEN_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          client_id: CONFIG.CLIENT_ID,
          client_secret: CONFIG.CLIENT_SECRET,
          grant_type: 'password',
          username: CONFIG.USERNAME,
          password: CONFIG.PASSWORD,
          scope: CONFIG.SCOPE,
        }),
      });

      if (!response.ok) throw new Error('Failed to obtain access token');
      const data = await response.json();
      localStorage.setItem('access_token', data.access_token);
      localStorage.setItem('token_expires_at', Date.now() + data.expires_in * 1000);
      return data.access_token;
    } catch (error) {
      Core.logError('Token fetch failed', error);
      throw error;
    }
  },

  // Refresh access token
  async refreshAccessToken() {
    try {
      const refreshToken = localStorage.getItem('refresh_token');
      if (!refreshToken) throw new Error('No refresh token available');

      const response = await fetch(CONFIG.TOKEN_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          client_id: CONFIG.CLIENT_ID,
          client_secret: CONFIG.CLIENT_SECRET,
          grant_type: 'refresh_token',
          refresh_token: refreshToken,
        }),
      });

      if (!response.ok) throw new Error('Failed to refresh token');
      const data = await response.json();
      localStorage.setItem('access_token', data.access_token);
      localStorage.setItem('refresh_token', data.refresh_token);
      localStorage.setItem('token_expires_at', Date.now() + data.expires_in * 1000);
      return data.access_token;
    } catch (error) {
      Core.logError('Token refresh failed', error);
      throw error;
    }
  },

  // Render error message in UI
  renderError(elementId, message) {
    const element = document.getElementById(elementId);
    element.innerHTML = `<p class="text-red-400">${message}</p>`;
  },
};