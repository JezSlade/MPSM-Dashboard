/**
 * Self-Refreshing Cache Engine
 * Runs in browser, refreshes cache every 5 minutes automatically
 * No cron job needed!
 */

(function() {
    'use strict';

    const REFRESH_INTERVAL = 5 * 60 * 1000; // 5 minutes
    let refreshTimer = null;
    let isRefreshing = false;

    async function refreshCache() {
        if (isRefreshing) {
            console.log('[CACHE ENGINE] Refresh already in progress, skipping');
            return;
        }

        isRefreshing = true;
        console.log('[CACHE ENGINE] Starting background cache refresh...');

        try {
            const response = await fetch('/cms/api/refresh-cache-enhanced.php', {
                credentials: 'same-origin'
            });

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Unexpected response type: ${contentType} :: ${text.slice(0, 120)}`);
            }

            const data = await response.json();

            if (data.success) {
                console.log(`[CACHE ENGINE] ✓ Cache refreshed: ${data.devices} devices in ${data.duration}s`);
            } else {
                console.warn('[CACHE ENGINE] Cache refresh failed:', data.error || data.reason);
            }
        } catch (error) {
            console.error('[CACHE ENGINE] Cache refresh error:', error);
        } finally {
            isRefreshing = false;
        }
    }

    function startEngine() {
        // DISABLED: Browser auto-refresh causes rate limit amplification with multiple users
        // Server cron handles cache population safely with rate limit management
        console.log('[CACHE ENGINE] Browser auto-refresh DISABLED - relying on server cron');

        // Keep manual refresh function available for testing
        window.refreshDeviceCache = refreshCache;

        // Log cache status on page load
        console.log('[CACHE ENGINE] Manual refresh available via: window.refreshDeviceCache()');
    }

    // Auto-start on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startEngine);
    } else {
        startEngine();
    }

    // Expose manual refresh function
    window.refreshDeviceCache = refreshCache;

    console.log('[CACHE ENGINE] Loaded - browser auto-refresh disabled; use cron or manual trigger');
})();

/*
CHANGELOG
2025-11-24 Codex
- Pointed cache engine to refresh-cache-enhanced.php with response-type guarding to eliminate 404/HTML parse errors.
*/
