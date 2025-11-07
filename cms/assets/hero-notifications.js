/**
 * Hero Notifications Widget
 * Displays active dashboard notifications in the hero header section
 */

// State
let heroAutoRefreshInterval = null;
let activeNotificationsCount = 0;

// Severity Configuration
const HERO_SEVERITY_CONFIG = {
    critical: {
        gradient: 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)',
        icon: 'fire',
        label: 'Critical'
    },
    high: {
        gradient: 'linear-gradient(135deg, #f39c12 0%, #e67e22 100%)',
        icon: 'exclamation-circle',
        label: 'High Priority'
    },
    warning: {
        gradient: 'linear-gradient(135deg, #f39c12 0%, #d68910 100%)',
        icon: 'exclamation-triangle',
        label: 'Warning'
    },
    info: {
        gradient: 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)',
        icon: 'info-circle',
        label: 'Information'
    }
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
        const response = await fetch('api/command-center.php?action=get_notifications&status=active', {
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

        const notifications = data.notifications || [];
        activeNotificationsCount = notifications.length;

        // Update notification badge in header
        updateNotificationBadge(activeNotificationsCount);

        // Render hero notifications
        renderHeroNotifications(notifications);

    } catch (error) {
        console.error('Error loading hero notifications:', error);
    }
}

// Update notification badge count in header
function updateNotificationBadge(count) {
    const badge = document.getElementById('notification-badge');
    if (!badge) return;

    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count.toString();
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

// Render hero notifications
function renderHeroNotifications(notifications) {
    const container = document.getElementById('hero-notifications');
    if (!container) return;

    // Only show top 5 priority notifications
    const topNotifications = notifications
        .sort((a, b) => (b.priority || 0) - (a.priority || 0))
        .slice(0, 5);

    container.style.display = 'block';

    if (topNotifications.length === 0) {
        container.innerHTML = `
            <div class="hero-notification-empty">
                <div class="hero-empty-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="hero-empty-content">
                    <h3>No Active Alerts</h3>
                    <p>Monitoring system is active. Waiting for panel message notifications...</p>
                    <small>Create notification rules in <a href="command-center.php">Command Center</a> to start receiving alerts</small>
                </div>
            </div>
        `;
        return;
    }

    const html = topNotifications.map(notif => {
        const config = HERO_SEVERITY_CONFIG[notif.severity] || HERO_SEVERITY_CONFIG.info;

        return `
            <div class="hero-notification hero-notification-${notif.severity}"
                 style="background: ${config.gradient};"
                 data-id="${notif.id}">
                <div class="hero-notification-content">
                    <div class="hero-notification-icon">
                        <i class="fas fa-${config.icon}"></i>
                    </div>
                    <div class="hero-notification-body">
                        <div class="hero-notification-title">
                            ${escapeHtmlHero(notif.title)}
                        </div>
                        <div class="hero-notification-message">
                            ${escapeHtmlHero(notif.message)}
                        </div>
                        <div class="hero-notification-meta">
                            ${notif.device_serial ? `<span><i class="fas fa-hdd"></i> ${escapeHtmlHero(notif.device_serial)}</span>` : ''}
                            ${notif.trigger_count && notif.time_window_hours ? `<span><i class="fas fa-chart-line"></i> ${notif.trigger_count}x in ${notif.time_window_hours}h</span>` : ''}
                            <span><i class="fas fa-clock"></i> ${formatHeroTimestamp(notif.created_at_ny)}</span>
                        </div>
                    </div>
                    <div class="hero-notification-actions">
                        <button class="hero-btn" onclick="acknowledgeHeroNotification(${notif.id})" title="Acknowledge">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="hero-btn" onclick="dismissHeroNotification(${notif.id})" title="Dismiss">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = html;

    // Add slide-in animation
    container.querySelectorAll('.hero-notification').forEach((el, index) => {
        el.style.animation = `slideInFromTop 0.4s ease-out ${index * 0.1}s both`;
    });
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
