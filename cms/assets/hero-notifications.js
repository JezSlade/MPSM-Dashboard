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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    loadHeroNotifications();
    startHeroAutoRefresh();
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

        // Fallback client filter if server filter not yet applied
        if (customerCode) {
            notifications = notifications.filter(n => (n.customer_code || '').toString() === customerCode.toString());
        }

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
        const title = notif.title || config.label;

        const device = notif.device_identifier || notif.device_serial || '';
        const customer = notif.customer_code || '';
        const alertCode = notif.alert_code || '';
        const secondary = notif.message
            || [device, customer].filter(Boolean).join(' • ')
            || 'New alert';

        const metaParts = [];
        if (alertCode) {
            metaParts.push(`<span class="meta-pill"><i class="fas fa-bell"></i> ${escapeHtmlHero(alertCode)}</span>`);
        }
        if (device) {
            metaParts.push(`<span class="meta-pill"><i class="fas fa-hdd"></i> ${escapeHtmlHero(device)}</span>`);
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
                        <div class="hero-chip-title">
                            ${escapeHtmlHero(title)}
                        </div>
                        <div class="hero-chip-subtitle">
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
function formatHeroTimestamp(timestamp) {
    if (!timestamp) return 'N/A';

    const date = new Date(timestamp + ' GMT-0500'); // NY time
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`;
    if (diffMins < 10080) return `${Math.floor(diffMins / 1440)}d ago`;

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
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
*/
