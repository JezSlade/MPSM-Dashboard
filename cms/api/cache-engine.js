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
            const response = await fetch('/cms/api/refresh-cache.php', {
                credentials: 'same-origin'
            });

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
        console.log('[CACHE ENGINE] Starting auto-refresh engine (every 5 minutes)');

        // Initial refresh on page load
        setTimeout(() => refreshCache(), 2000); // Wait 2s for page to settle

        // Schedule periodic refreshes
        refreshTimer = setInterval(() => {
            refreshCache();
        }, REFRESH_INTERVAL);

        // Refresh on page visibility change (user returns to tab)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                const lastRefresh = localStorage.getItem('cache_last_refresh');
                const now = Date.now();

                if (!lastRefresh || (now - parseInt(lastRefresh)) > REFRESH_INTERVAL) {
                    console.log('[CACHE ENGINE] Tab visible, triggering refresh');
                    refreshCache();
                    localStorage.setItem('cache_last_refresh', now.toString());
                }
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (refreshTimer) {
                clearInterval(refreshTimer);
            }
        });
    }

    // Auto-start on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startEngine);
    } else {
        startEngine();
    }

    // Expose manual refresh function
    window.refreshDeviceCache = refreshCache;

    console.log('[CACHE ENGINE] Loaded - No cron job needed!');
})();
