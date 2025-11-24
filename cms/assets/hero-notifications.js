/**
 * Hero Notifications Widget
 * Displays active dashboard notifications in the customer header (collapsible)
 */

// State
let heroAutoRefreshInterval = null;
let activeNotificationsCount = 0;
let heroExpanded = false;
let lastHeroNotifications = [];

// Severity Configuration
const HERO_SEVERITY_CONFIG = {
    critical: { gradient: 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)', icon: 'fire', label: 'Critical' },
    high: { gradient: 'linear-gradient(135deg, #f39c12 0%, #e67e22 100%)', icon: 'exclamation-circle', label: 'High Priority' },
    warning: { gradient: 'linear-gradient(135deg, #f39c12 0%, #d68910 100%)', icon: 'exclamation-triangle', label: 'Warning' },
    info: { gradient: 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)', icon: 'info-circle', label: 'Information' }
};

// Initialize on page load - defer to allow app.js to set window.currentCustomerCode first
document.addEventListener('DOMContentLoaded', function () {
    // Start auto-refresh timer
    startHeroAutoRefresh();

    // Fallback: if app.js hasn't called loadHeroNotifications() within 2 seconds, call it ourselves
    // This ensures the toggle bar always renders even if there's a timing issue
    setTimeout(function() {
        const container = document.getElementById('hero-notifications');
        if (container && !container.innerHTML.trim()) {
            loadHeroNotifications();
        }
    }, 2000);
});

// Auto-refresh every 30 seconds
function startHeroAutoRefresh() {
    if (heroAutoRefreshInterval) {
        clearInterval(heroAutoRefreshInterval);
    }
    heroAutoRefreshInterval = setInterval(loadHeroNotifications, 30000); // 30 seconds
}

// Load and display hero notifications
async function loadHeroNotifications() {
    try {
        const customerCode = getCurrentCustomerCode();
        const params = new URLSearchParams({ action: 'get_notifications', status: 'active' });
        if (customerCode) {
            params.append('customerCode', customerCode);
        }

        const response = await fetch('api/command-center.php?' + params.toString(), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            console.error('Failed to load notifications:', data.error);
            return;
        }

        let notifications = data.notifications || [];

        // Server already filters by customerCode via JOIN with panel_messages
        // Client filter removed - was incorrectly filtering out notifications where
        // customer_code was null but matched via pm_customer_code JOIN column

        activeNotificationsCount = notifications.length;
        lastHeroNotifications = notifications;

        // Update notification badge in header (hidden to avoid clutter)
        updateNotificationBadge();

        // Render hero notifications
        renderHeroNotifications(notifications);

    } catch (error) {
        console.error('Error loading hero notifications:', error);
    }
}

// Update notification badge count in header (hidden now)
function updateNotificationBadge() {
    const badge = document.getElementById('notification-badge');
    if (!badge) return;
    badge.style.display = 'none';
}

// Get currently selected customer code from banner or global
function getCurrentCustomerCode() {
    if (window.currentCustomerCode) {
        return window.currentCustomerCode;
    }
    const codeEl = document.querySelector('.customer-banner-code');
    return codeEl ? codeEl.textContent.trim() : '';
}

// Render hero notifications
function renderHeroNotifications(notifications) {
    const container = document.getElementById('hero-notifications');
    if (!container) return;

    // Group notifications by device_serial + alert_code to show unique alerts
    const grouped = new Map();
    notifications.forEach(notif => {
        const key = `${notif.device_serial || ''}|${notif.alert_code || ''}`;
        const existing = grouped.get(key);
        if (!existing || (notif.priority || 0) > (existing.priority || 0)) {
            // Keep highest priority version, aggregate trigger counts
            const aggregatedCount = existing
                ? (existing._aggregatedTriggers || existing.trigger_count || 1) + (notif.trigger_count || 1)
                : (notif.trigger_count || 1);
            grouped.set(key, { ...notif, _aggregatedTriggers: aggregatedCount });
        } else if (existing) {
            // Add to existing trigger count
            existing._aggregatedTriggers = (existing._aggregatedTriggers || existing.trigger_count || 1) + (notif.trigger_count || 1);
        }
    });

    const uniqueNotifications = Array.from(grouped.values());

    // Only show top 6 priority notifications to keep header compact
    const topNotifications = uniqueNotifications
        .sort((a, b) => (b.priority || 0) - (a.priority || 0))
        .slice(0, 6);

    container.style.display = 'block';

    const count = topNotifications.length;
    const configEmpty = `
        <div class="hero-notification-empty">
            <div class="hero-empty-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="hero-empty-content">
                <h3>No Active Alerts</h3>
                <p>Monitoring system is active.</p>
            </div>
        </div>
    `;

    const chipsHtml = topNotifications.map(notif => {
        const config = HERO_SEVERITY_CONFIG[notif.severity] || HERO_SEVERITY_CONFIG.info;
        const displayName = notif.display_name || notif.title || config.label || 'System Alert';
        const device = notif.device_identifier || notif.device_serial || '';
        const customer = notif.customer_description || notif.customer_code || '';
        const model = notif.model || '';
        const department = notif.department || notif.device_location || notif.device_department || '';
        const alertCode = notif.alert_code || '';
        const secondary = customer || model || device || 'Alert';

        const metaParts = [];
        if (alertCode) {
            metaParts.push(`<span class="meta-pill"><i class="fas fa-bell"></i> ${escapeHtmlHero(alertCode)}</span>`);
        }
        if (device) {
            metaParts.push(`<span class="meta-pill"><i class="fas fa-barcode"></i> ${escapeHtmlHero(device)}</span>`);
        }
        if (model) {
            metaParts.push(`<span class="meta-pill"><i class="fas fa-desktop"></i> ${escapeHtmlHero(model)}</span>`);
        }
        if (department) {
            metaParts.push(`<span class="meta-pill"><i class="fas fa-map-marker-alt"></i> ${escapeHtmlHero(department)}</span>`);
        }
        const triggerCount = notif._aggregatedTriggers || notif.trigger_count || 0;
        if (triggerCount > 1) {
            const windowText = notif.time_window_hours ? ` in ${notif.time_window_hours}h` : '';
            metaParts.push(`<span class="meta-pill"><i class="fas fa-chart-line"></i> ${triggerCount}x${windowText}</span>`);
        }
        const metaHtml = metaParts.join('');

        return `
            <div class="hero-notification hero-notification-${notif.severity} hero-chip"
                 style="background: ${config.gradient};"
                 data-id="${notif.id}">
                <div class="hero-chip-content">
                    <div class="hero-chip-icon" aria-hidden="true">
                        <i class="fas fa-${config.icon}"></i>
                    </div>
                    <div class="hero-chip-body">
                        <div class="hero-chip-title" title="${escapeHtmlHero(displayName)}">
                            ${escapeHtmlHero(displayName)}
                        </div>
                        <div class="hero-chip-subtitle" title="${escapeHtmlHero(secondary)}">
                            ${escapeHtmlHero(secondary)}
                        </div>
                        ${metaHtml ? `<div class="hero-chip-meta">${metaHtml}</div>` : ''}
                    </div>
                    <div class="hero-chip-actions">
                        <span class="hero-chip-time"><i class="fas fa-clock"></i> ${formatHeroTimestamp(notif.created_at_ny)}</span>
                        <div class="hero-chip-buttons">
                            <button class="hero-btn" onclick="acknowledgeHeroNotification(${notif.id})" title="Acknowledge">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="hero-btn" onclick="dismissHeroNotification(${notif.id})" title="Dismiss">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    const listHtml = heroExpanded
        ? (count ? `<div class="hero-chip-list">${chipsHtml}</div>` : configEmpty)
        : '';

    container.classList.toggle('expanded', heroExpanded);
    container.classList.toggle('collapsed', !heroExpanded);

    container.innerHTML = `
        <div class="hero-alert-toggle">
            <div class="hero-alert-heading">
                <span class="hero-alert-label"><i class="fas fa-bell"></i> System Alerts</span>
                <span class="hero-alert-count">${count ? `${count} active` : 'No active alerts'}</span>
            </div>
            <button class="hero-btn hero-toggle-btn" onclick="toggleHeroAlerts()">
                ${heroExpanded ? 'Hide' : 'Show'}
            </button>
        </div>
        ${listHtml}
    `;
}

function toggleHeroAlerts() {
    heroExpanded = !heroExpanded;
    renderHeroNotifications(lastHeroNotifications);
}

// Acknowledge notification
async function acknowledgeHeroNotification(id) {
    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                action: 'acknowledge_notification',
                id: id.toString()
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to acknowledge notification');
        }

        // Remove notification with animation
        const notifEl = document.querySelector(`.hero-notification[data-id="${id}"]`);
        if (notifEl) {
            notifEl.style.animation = 'slideOutToRight 0.3s ease-in-out';
            setTimeout(() => {
                loadHeroNotifications();
            }, 300);
        }

    } catch (error) {
        console.error('Error acknowledging notification:', error);
        alert('Failed to acknowledge notification: ' + error.message);
    }
}

// Dismiss notification
async function dismissHeroNotification(id) {
    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                action: 'dismiss_notification',
                id: id.toString()
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to dismiss notification');
        }

        // Remove notification with animation
        const notifEl = document.querySelector(`.hero-notification[data-id="${id}"]`);
        if (notifEl) {
            notifEl.style.animation = 'slideOutToRight 0.3s ease-in-out';
            setTimeout(() => {
                loadHeroNotifications();
            }, 300);
        }

    } catch (error) {
        console.error('Error dismissing notification:', error);
        alert('Failed to dismiss notification: ' + error.message);
    }
}

// Utility: Format timestamp for hero display
// Timestamps from server are in NY local time (America/New_York)
function formatHeroTimestamp(timestamp) {
    if (!timestamp) return 'N/A';

    // Parse the timestamp as NY time using Intl API for proper DST handling
    // Server sends timestamps in format "YYYY-MM-DD HH:MM:SS" in NY timezone
    try {
        // Create date object treating the timestamp as NY local time
        // We need to figure out the offset for NY at that specific time
        const nyTimeStr = timestamp.replace(' ', 'T');

        // Get current time in NY for comparison
        const nowInNY = new Date().toLocaleString('en-US', { timeZone: 'America/New_York' });
        const nowNYDate = new Date(nowInNY);

        // Parse the server timestamp (which is already in NY time)
        // Append timezone indicator for proper parsing
        const serverDate = new Date(nyTimeStr);

        // Calculate the difference using NY local times
        const diffMs = nowNYDate - serverDate;
        const diffMins = Math.floor(diffMs / 60000);

        if (diffMins < 0) {
            // Future time - likely timezone mismatch, show absolute time
            return serverDate.toLocaleString('en-US', {
                timeZone: 'America/New_York',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        }
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`;
        if (diffMins < 10080) return `${Math.floor(diffMins / 1440)}d ago`;

        return serverDate.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric'
        });
    } catch (e) {
        console.error('Error formatting timestamp:', e, timestamp);
        return timestamp;
    }
}

// Utility: Escape HTML
function escapeHtmlHero(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/*
CHANGELOG
2025-11-22 Codex
- Redesigned hero notifications into compact header chips using identifier/alert-friendly text and tighter layout.
- Scoped alerts to the current customer with collapsible header placement and badge suppression.
2025-11-23 Codex
- Renamed header toggle to "System Alerts" to distinguish from dashboard Maintenance Alerts metric.
- Group notifications by device+alert to show unique count (avoids "6 active" when there are 2 unique alerts triggered 3x each).
- Show aggregated trigger count when > 1.
- Fixed race condition: removed DOMContentLoaded auto-load, let app.js trigger loadHeroNotifications() after customer code is set.
- Removed redundant client-side customer filter that was incorrectly filtering out notifications matched via server-side JOIN.
- Added 2-second fallback to ensure toggle bar renders even if app.js initialization is delayed.
*/
