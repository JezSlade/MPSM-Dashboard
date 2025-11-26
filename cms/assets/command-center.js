/**
 * Command Center - Notification Management System
 * Handles notification rules, active notifications, and alert statistics
 */

// State
let autoRefreshInterval = null;
let currentTab = 'notifications';
let notificationFilter = '';

// Severity Configuration
const SEVERITY_CONFIG = {
    critical: {
        color: '#e74c3c',
        icon: 'fire',
        label: 'Critical'
    },
    high: {
        color: '#f39c12',
        icon: 'exclamation-circle',
        label: 'High'
    },
    warning: {
        color: '#f39c12',
        icon: 'exclamation-triangle',
        label: 'Warning'
    },
    info: {
        color: '#3498db',
        icon: 'info-circle',
        label: 'Info'
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    initializeTabs();
    initializeControls();
    loadNotifications();
    startAutoRefresh();
});

// Tab Switching
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.monitor-tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const target = button.dataset.tab;
            currentTab = target;

            tabButtons.forEach((btn) => {
                btn.classList.toggle('active', btn === button);
            });

            tabPanels.forEach((panel) => {
                panel.classList.toggle('active', panel.dataset.tab === target);
            });

            // Load data for the selected tab
            if (target === 'notifications') {
                loadNotifications();
            } else if (target === 'rules') {
                loadRules();
            } else if (target === 'statistics') {
                loadStatistics();
            }
        });
    });
}

// Initialize Controls
function initializeControls() {
    // Notification filter
    const filterSelect = document.getElementById('notification-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', (e) => {
            notificationFilter = e.target.value;
            loadNotifications();
        });
    }

    // Auto-refresh toggle
    const autoRefreshCheckbox = document.getElementById('notification-auto-refresh');
    if (autoRefreshCheckbox) {
        autoRefreshCheckbox.addEventListener('change', (e) => {
            if (e.target.checked) {
                startAutoRefresh();
            } else {
                stopAutoRefresh();
            }
        });
    }

    // Create rule button
    const createRuleBtn = document.getElementById('create-rule-btn');
    if (createRuleBtn) {
        createRuleBtn.addEventListener('click', () => {
            openRuleModal();
        });
    }

    // Rule form submission
    const ruleForm = document.getElementById('rule-form');
    if (ruleForm) {
        ruleForm.addEventListener('submit', handleRuleSubmit);
    }

    // Statistics sort
    const statsSort = document.getElementById('stats-sort');
    if (statsSort) {
        statsSort.addEventListener('change', () => {
            loadStatistics();
        });
    }
}

// Auto-refresh Management
function startAutoRefresh() {
    stopAutoRefresh();
    autoRefreshInterval = setInterval(() => {
        if (currentTab === 'notifications') {
            loadNotifications(true);
        } else if (currentTab === 'rules') {
            loadRules(true);
        } else if (currentTab === 'statistics') {
            loadStatistics(true);
        }
    }, 10000); // 10 seconds
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// ========================================
// TAB 1: Active Notifications
// ========================================

async function loadNotifications(silent = false) {
    const container = document.getElementById('notifications-container');

    if (!silent) {
        container.innerHTML = '<div class="loading">Loading notifications...</div>';
    }

    try {
        const params = new URLSearchParams({ action: 'get_notifications' });
        if (notificationFilter) {
            params.set('severity', notificationFilter);
        }

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

        const response = await fetch(`api/command-center.php?${params.toString()}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to load notifications');
        }

        // Group duplicate notifications (same device + alert) into a single card
        const notifications = Array.isArray(data.notifications) ? data.notifications : [];
        const grouped = groupNotificationsForDisplay(notifications);
        renderNotifications(grouped);
    } catch (error) {
        console.error('Error loading notifications:', error);
        const errorMsg = error.name === 'AbortError' ? 'Request timed out' : error.message;
        container.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ${errorMsg}</div>`;
    }
}

// Group notifications by device + alert to prevent duplicates
function groupNotificationsForDisplay(notifications) {
    if (!notifications || notifications.length === 0) return [];

    const grouped = new Map();

    notifications.forEach((notif) => {
        const key = `${notif.device_serial || ''}|${notif.alert_code || ''}|${notif.alert_display_name || ''}`;
        const existing = grouped.get(key);

        // Prefer the notification with the highest priority
        if (!existing || (notif.priority || 0) > (existing.priority || 0)) {
            const aggregatedCount = existing
                ? (existing._aggregatedTriggers || existing.trigger_count || 1) + (notif.trigger_count || 1)
                : (notif.trigger_count || 1);

            grouped.set(key, { ...notif, _aggregatedTriggers: aggregatedCount });
        } else {
            // Same key, lower or equal priority: accumulate triggers only
            existing._aggregatedTriggers = (existing._aggregatedTriggers || existing.trigger_count || 1) + (notif.trigger_count || 1);
            grouped.set(key, existing);
        }
    });

    // Preserve order by severity/priority then time similar to API default
    return Array.from(grouped.values()).sort((a, b) => (b.priority || 0) - (a.priority || 0));
}

function renderNotifications(notifications) {
    const container = document.getElementById('notifications-container');

    if (notifications.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle"></i> No active notifications</div>';
        return;
    }

    const html = notifications.map(notif => {
        const config = SEVERITY_CONFIG[notif.severity] || SEVERITY_CONFIG.info;
        const statusClass = notif.status === 'acknowledged' ? 'acknowledged' : '';
        const triggerCount = notif._aggregatedTriggers || notif.trigger_count || 0;

        return `
            <div class="notification-card ${statusClass}" data-id="${notif.id}" data-severity="${notif.severity}">
                <div class="notification-header">
                    <div class="notification-icon" style="color: ${config.color}">
                        <i class="fas fa-${config.icon}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${escapeHtml(notif.title)}</div>
                        <div class="notification-meta">
                            <span class="badge badge-${notif.severity}">${config.label}</span>
                            <span class="notification-time">${formatTimestamp(notif.created_at_ny)}</span>
                            ${notif.device_serial ? `<span class="notification-device"><i class="fas fa-hdd"></i> ${escapeHtml(notif.device_serial)}</span>` : ''}
                        </div>
                    </div>
                    <div class="notification-actions">
                        ${notif.status === 'active' ? `
                            <button class="btn-icon" onclick="acknowledgeNotification(${notif.id})" title="Acknowledge">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn-icon" onclick="dismissNotification(${notif.id})" title="Dismiss">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
                <div class="notification-message">${escapeHtml(notif.message)}</div>
                ${triggerCount > 1 ? `
                    <div class="notification-stats">
                        <span><i class="fas fa-chart-line"></i> ${triggerCount} occurrences</span>
                        ${notif.time_window_hours ? `<span><i class=\"fas fa-clock\"></i> Last ${notif.time_window_hours} hours</span>` : ''}
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');

    container.innerHTML = html;
}

async function acknowledgeNotification(id) {
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

        showToast('Notification acknowledged', 'success');
        loadNotifications(true);
    } catch (error) {
        console.error('Error acknowledging notification:', error);
        showToast(error.message, 'error');
    }
}

async function dismissNotification(id) {
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

        showToast('Notification dismissed', 'success');
        loadNotifications(true);
    } catch (error) {
        console.error('Error dismissing notification:', error);
        showToast(error.message, 'error');
    }
}

// ========================================
// TAB 2: Notification Rules
// ========================================

async function loadRules(silent = false) {
    const container = document.getElementById('rules-container');

    if (!silent) {
        container.innerHTML = '<div class="loading">Loading rules...</div>';
    }

    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

        const response = await fetch('api/command-center.php?action=get_rules', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to load rules');
        }

        renderRules(data.rules || []);
    } catch (error) {
        console.error('Error loading rules:', error);
        const errorMsg = error.name === 'AbortError' ? 'Request timed out' : error.message;
        container.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ${errorMsg}</div>`;
    }
}

function renderRules(rules) {
    const container = document.getElementById('rules-container');

    if (rules.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-cog"></i> No notification rules configured. Create your first rule to get started!</div>';
        return;
    }

    const html = rules.map(rule => {
        const config = SEVERITY_CONFIG[rule.severity] || SEVERITY_CONFIG.info;
        const enabledClass = rule.enabled ? 'enabled' : 'disabled';

        return `
            <div class="rule-card ${enabledClass}" data-id="${rule.id}">
                <div class="rule-header">
                    <div class="rule-icon" style="color: ${config.color}">
                        <i class="fas fa-${config.icon}"></i>
                    </div>
                    <div class="rule-info">
                        <div class="rule-name">${escapeHtml(rule.name)}</div>
                        ${rule.description ? `<div class="rule-description">${escapeHtml(rule.description)}</div>` : ''}
                        <div class="rule-meta">
                            <span class="badge badge-${rule.severity}">${config.label}</span>
                            ${rule.last_triggered_at ? `<span class="rule-last-trigger"><i class="fas fa-bolt"></i> Last triggered: ${formatTimestamp(rule.last_triggered_at)}</span>` : '<span class="text-muted">Never triggered</span>'}
                            ${rule.trigger_count > 0 ? `<span class="rule-trigger-count"><i class="fas fa-chart-line"></i> ${rule.trigger_count} times</span>` : ''}
                        </div>
                    </div>
                    <div class="rule-actions">
                        <label class="toggle-switch" title="${rule.enabled ? 'Disable' : 'Enable'} rule">
                            <input type="checkbox" ${rule.enabled ? 'checked' : ''} onchange="toggleRule(${rule.id}, this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                        <button class="btn-icon" onclick="editRule(${rule.id})" title="Edit rule">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-danger" onclick="deleteRule(${rule.id})" title="Delete rule">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="rule-patterns">
                    ${rule.alert_code_pattern ? `<div class="pattern-tag"><i class="fas fa-tag"></i> Alert: ${escapeHtml(rule.alert_code_pattern)}</div>` : ''}
                    ${rule.device_serial_pattern ? `<div class="pattern-tag"><i class="fas fa-hdd"></i> Device: ${escapeHtml(rule.device_serial_pattern)}</div>` : ''}
                    ${rule.customer_code_pattern ? `<div class="pattern-tag"><i class="fas fa-building"></i> Customer: ${escapeHtml(rule.customer_code_pattern)}</div>` : ''}
                    ${rule.frequency_count && rule.frequency_window_hours ? `<div class="pattern-tag"><i class="fas fa-clock"></i> ${rule.frequency_count}x in ${rule.frequency_window_hours}h (${rule.frequency_type})</div>` : ''}
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = html;
}

async function toggleRule(id, enabled) {
    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                action: 'toggle_rule',
                id: id.toString()
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to toggle rule');
        }

        showToast(`Rule ${enabled ? 'enabled' : 'disabled'}`, 'success');
        loadRules(true);
    } catch (error) {
        console.error('Error toggling rule:', error);
        showToast(error.message, 'error');
        loadRules(true); // Reload to reset toggle state
    }
}

async function deleteRule(id) {
    if (!confirm('Are you sure you want to delete this rule? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                action: 'delete_rule',
                id: id.toString()
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to delete rule');
        }

        showToast('Rule deleted successfully', 'success');
        loadRules();
    } catch (error) {
        console.error('Error deleting rule:', error);
        showToast(error.message, 'error');
    }
}

function editRule(id) {
    // Find rule data
    fetch(`api/command-center.php?action=get_rules`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const rule = data.rules.find(r => r.id === id);
            if (rule) {
                openRuleModal(rule);
            }
        }
    })
    .catch(error => {
        console.error('Error loading rule:', error);
        showToast('Failed to load rule data', 'error');
    });
}

// Rule Modal Management
function openRuleModal(rule = null) {
    const modal = document.getElementById('rule-modal');
    const form = document.getElementById('rule-form');
    const title = document.getElementById('rule-modal-title');

    // Reset form
    form.reset();

    if (rule) {
        // Edit mode
        title.textContent = 'Edit Notification Rule';
        document.getElementById('rule-id').value = rule.id;
        document.getElementById('rule-name').value = rule.name || '';
        document.getElementById('rule-description').value = rule.description || '';
        document.getElementById('rule-severity').value = rule.severity || 'warning';
        document.getElementById('rule-alert-pattern').value = rule.alert_code_pattern || '';
        document.getElementById('rule-device-pattern').value = rule.device_serial_pattern || '';
        document.getElementById('rule-customer-pattern').value = rule.customer_code_pattern || '';
        document.getElementById('rule-freq-count').value = rule.frequency_count || '';
        document.getElementById('rule-freq-window').value = rule.frequency_window_hours || '';
        document.getElementById('rule-freq-type').value = rule.frequency_type || 'same_device';
        document.getElementById('rule-title').value = rule.notification_title || '';
        document.getElementById('rule-message').value = rule.notification_message || '';
        document.getElementById('rule-show-dashboard').checked = rule.show_dashboard == 1;
        document.getElementById('rule-auto-dismiss').value = rule.auto_dismiss_hours || '';
    } else {
        // Create mode
        title.textContent = 'Create Notification Rule';
        document.getElementById('rule-id').value = '';
        document.getElementById('rule-show-dashboard').checked = true;
    }

    modal.style.display = 'flex';
}

function closeRuleModal() {
    const modal = document.getElementById('rule-modal');
    modal.style.display = 'none';
}

async function handleRuleSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const ruleId = formData.get('id');
    const action = ruleId ? 'update_rule' : 'create_rule';

    // Build request body
    const body = {
        action: action,
        name: formData.get('name'),
        description: formData.get('description') || null,
        severity: formData.get('severity'),
        alert_code_pattern: formData.get('alert_code_pattern') || null,
        device_serial_pattern: formData.get('device_serial_pattern') || null,
        customer_code_pattern: formData.get('customer_code_pattern') || null,
        frequency_count: formData.get('frequency_count') || null,
        frequency_window_hours: formData.get('frequency_window_hours') || null,
        frequency_type: formData.get('frequency_type') || 'same_device',
        notification_title: formData.get('notification_title') || null,
        notification_message: formData.get('notification_message') || null,
        show_dashboard: formData.get('show_dashboard') ? 1 : 0,
        auto_dismiss_hours: formData.get('auto_dismiss_hours') || null
    };

    if (ruleId) {
        body.id = ruleId;
    }

    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to save rule');
        }

        showToast(`Rule ${ruleId ? 'updated' : 'created'} successfully`, 'success');
        closeRuleModal();
        loadRules();
    } catch (error) {
        console.error('Error saving rule:', error);
        showToast(error.message, 'error');
    }
}

// ========================================
// TAB 3: Alert Statistics
// ========================================

async function loadStatistics(silent = false) {
    const container = document.getElementById('statistics-container');

    if (!silent) {
        container.innerHTML = '<div class="loading">Loading statistics...</div>';
    }

    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

        const response = await fetch('api/command-center.php?action=get_aggregations', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to load statistics');
        }

        renderStatistics(data.aggregations || []);
    } catch (error) {
        console.error('Error loading statistics:', error);
        const errorMsg = error.name === 'AbortError' ? 'Request timed out' : error.message;
        container.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ${errorMsg}</div>`;
    }
}

function renderStatistics(aggregations) {
    const container = document.getElementById('statistics-container');

    if (aggregations.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-chart-bar"></i> No alert data available</div>';
        return;
    }

    // Sort based on user preference
    const sortBy = document.getElementById('stats-sort')?.value || 'recent';
    const sorted = [...aggregations].sort((a, b) => {
        if (sortBy === 'recent') {
            return new Date(b.last_occurrence_ny) - new Date(a.last_occurrence_ny);
        } else if (sortBy === 'frequent') {
            return b.occurrence_count - a.occurrence_count;
        }
        return 0;
    });

    const html = `
        <div class="stats-grid">
            ${sorted.map(agg => `
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-device"><i class="fas fa-hdd"></i> ${escapeHtml(agg.device_serial)}</div>
                        <div class="stat-alert">${escapeHtml(agg.alert_code)}</div>
                    </div>
                    ${agg.customer_code ? `<div class="stat-customer"><i class="fas fa-building"></i> ${escapeHtml(agg.customer_code)}</div>` : ''}
                    <div class="stat-counts">
                        <div class="count-badge">
                            <div class="count-label">1 Hour</div>
                            <div class="count-value">${agg.count_1h || 0}</div>
                        </div>
                        <div class="count-badge">
                            <div class="count-label">24 Hours</div>
                            <div class="count-value">${agg.count_24h || 0}</div>
                        </div>
                        <div class="count-badge">
                            <div class="count-label">7 Days</div>
                            <div class="count-value">${agg.count_7d || 0}</div>
                        </div>
                        <div class="count-badge">
                            <div class="count-label">30 Days</div>
                            <div class="count-value">${agg.count_30d || 0}</div>
                        </div>
                    </div>
                    <div class="stat-meta">
                        <span><i class="fas fa-clock"></i> Last: ${formatTimestamp(agg.last_occurrence_ny)}</span>
                        <span><i class="fas fa-chart-line"></i> Total: ${agg.occurrence_count}</span>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    container.innerHTML = html;
}

// ========================================
// Utility Functions
// ========================================

function formatTimestamp(timestamp) {
    if (!timestamp) return 'N/A';

    const date = new Date(timestamp + ' GMT-0500'); // Assuming NY time
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`;

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
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
- Collapsed duplicate notifications (same device + alert) into a single card with aggregated trigger count.
- Updated render to display aggregated occurrences consistently with hero header behavior.
*/
