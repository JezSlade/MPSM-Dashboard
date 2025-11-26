/**
 * Command Center - Notification Management System
 * Handles notification rules, active notifications, and alert statistics
 */

// State
let autoRefreshInterval = null;
let currentTab = 'notifications';
let notificationFilter = '';
let notificationCustomerFilter = '';
let _aggMap = null; // cache of alert aggregations keyed by device|alert
let _aggMapLoadedAt = 0;

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
    // Notification severity filter
    const filterSelect = document.getElementById('notification-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', (e) => {
            notificationFilter = e.target.value;
            loadNotifications();
        });
    }

    // Notification customer filter (populate with Names, value=Code)
    const customerFilterSelect = document.getElementById('notification-customer-filter');
    if (customerFilterSelect) {
        customerFilterSelect.addEventListener('change', (e) => {
            notificationCustomerFilter = e.target.value;
            loadNotifications();
        });
        loadCustomerOptionsForCC(customerFilterSelect);
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
        if (notificationCustomerFilter) {
            params.set('customerCode', notificationCustomerFilter);
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

        // Enrich with 1h aggregation counts for accurate last-hour tallies
        const agg = await ensureAggregationsMap();
        grouped.forEach(n => {
            const key = `${n.device_serial || ''}|${n.alert_code || ''}`;
            const a = agg.get(key);
            if (a) {
                n._count_1h = a.count_1h || 0;
            }
        });

        renderNotifications(grouped);
        populateCustomerFilter(data.notifications || []);
    } catch (error) {
        console.error('Error loading notifications:', error);
        const errorMsg = error.name === 'AbortError' ? 'Request timed out' : error.message;
        container.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ${errorMsg}</div>`;
    }
}

function populateCustomerFilter(notifications) {
    const customerFilter = document.getElementById('notification-customer-filter');
    if (!customerFilter) return;

    // Extract unique customers
    const customers = new Map();
    notifications.forEach(n => {
        if (n.customer_code) {
            customers.set(n.customer_code, n.customer_description || n.customer_code);
        }
    });

    // Sort by customer description
    const sortedCustomers = Array.from(customers.entries()).sort((a, b) => a[1].localeCompare(b[1]));

    // Preserve current selection
    const currentValue = customerFilter.value;

    // Rebuild options
    customerFilter.innerHTML = '<option value="">All Customers</option>' +
        sortedCustomers.map(([code, desc]) =>
            `<option value="${escapeHtml(code)}">${escapeHtml(desc)}</option>`
        ).join('');

    // Restore selection if still valid
    if (currentValue && customers.has(currentValue)) {
        customerFilter.value = currentValue;
    }
}

// Group notifications by device + alert to prevent duplicates
function groupNotificationsForDisplay(notifications) {
    if (!notifications || notifications.length === 0) return [];

    const grouped = new Map();

    notifications.forEach((notif) => {
        const key = `${notif.device_serial || ''}|${notif.alert_code || ''}`;
        const existing = grouped.get(key);

        if (!existing) {
            // First occurrence of this key
            grouped.set(key, { ...notif, _aggregatedTriggers: 1 });
        } else {
            // Duplicate found: prefer higher priority and increment count
            if ((notif.priority || 0) > (existing.priority || 0)) {
                // Replace with higher priority notification, carry over count
                grouped.set(key, { ...notif, _aggregatedTriggers: (existing._aggregatedTriggers || 1) + 1 });
            } else {
                // Keep existing, increment count
                existing._aggregatedTriggers = (existing._aggregatedTriggers || 1) + 1;
                grouped.set(key, existing);
            }
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
        const count1h = typeof notif._count_1h === 'number' ? notif._count_1h : null;

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
                ${count1h !== null ? `
                    <div class="notification-stats">
                        <span><i class="fas fa-chart-line"></i> ${count1h} occurrences</span>
                        <span><i class=\"fas fa-clock\"></i> Last 1h</span>
                    </div>
                ` : (triggerCount > 1 ? `
                    <div class="notification-stats">
                        <span><i class=\"fas fa-chart-line\"></i> ${triggerCount} occurrences</span>
                        ${notif.time_window_hours ? `<span><i class=\"fas fa-clock\"></i> Last ${notif.time_window_hours} hours</span>` : ''}
                    </div>
                ` : '')}
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
            const rule = (data.rules || []).find(r => Number(r.id) === Number(id));
            if (rule) {
                openRuleModal(rule);
            } else {
                console.warn('Rule not found for edit:', id);
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

    // Ensure pattern suggestions are loaded and datalists populated
    ensurePatternSuggestions().then(populatePatternDatalists).catch(() => {});

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

        const response = await fetch('api/command-center.php?action=get_aggregations&group_by=alert_only', {
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

    if (!Array.isArray(aggregations) || aggregations.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-chart-bar"></i> No alert data available</div>';
        return;
    }

    const sortBy = document.getElementById('stats-sort')?.value || 'recent';
    const sorted = [...aggregations].sort((a, b) => {
        if (sortBy === 'recent') return new Date(b.last_occurrence_ny) - new Date(a.last_occurrence_ny);
        if (sortBy === 'frequent') return (b.occurrence_count || 0) - (a.occurrence_count || 0);
        return 0;
    });

    const html = `
        <div class="stats-legend"><i class="fas fa-info-circle"></i> Counts show alert occurrences in the last 1h, 24h, 7d, and 30d windows.</div>
        <div class="stats-list">
            ${sorted.map(agg => `
                <div class="stat-row">
                    <div class="stat-main">
                        <div class="stat-title truncate">${escapeHtml(agg.alert_display_name || 'Unknown alert')} (${escapeHtml(agg.alert_code || '')})</div>
                        <div class="stat-subtitle truncate">${escapeHtml(agg.alert_category || '')}</div>
                    </div>
                    <div class="stat-badges">
                        <span class="count-badge"><span class="count-label">1h</span><span class="count-value">${agg.count_1h || 0}</span></span>
                        <span class="count-badge"><span class="count-label">24h</span><span class="count-value">${agg.count_24h || 0}</span></span>
                        <span class="count-badge"><span class="count-label">7d</span><span class="count-value">${agg.count_7d || 0}</span></span>
                        <span class="count-badge"><span class="count-label">30d</span><span class="count-value">${agg.count_30d || 0}</span></span>
                    </div>
                    <div class="stat-meta">
                        <span class="truncate"><i class="fas fa-microchip"></i> ${agg.device_count || 0} devices</span>
                        <span class="truncate"><i class="fas fa-chart-line"></i> ${agg.occurrence_count || 0} total</span>
                        <span class="truncate"><i class="fas fa-clock"></i> ${formatTimestamp(agg.last_occurrence_ny)}</span>
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

// Load and cache alert aggregations so notifications can show accurate 1h tallies
async function ensureAggregationsMap() {
    const now = Date.now();
    if (_aggMap && (now - _aggMapLoadedAt) < 30000) { // 30s cache
        return _aggMap;
    }

    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000);
        const res = await fetch('api/command-center.php?action=get_aggregations&limit=1000', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        const data = res.ok ? await res.json() : { success: false };
        const map = new Map();
        if (data.success && Array.isArray(data.aggregations)) {
            data.aggregations.forEach(a => {
                const key = `${a.device_serial || ''}|${a.alert_code || ''}`;
                map.set(key, a);
            });
        }
        _aggMap = map;
        _aggMapLoadedAt = now;
        return _aggMap;
    } catch (_e) {
        _aggMap = new Map();
        _aggMapLoadedAt = now;
        return _aggMap;
    }
}

// ================================
// Pattern Suggestions (Datalists)
// ================================
let _patternLoaded = false;
let _patternSuggestions = { alerts: [], devices: [], customers: [] };

async function ensurePatternSuggestions() {
    if (_patternLoaded) return _patternSuggestions;

    try {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 10000);

        // Fetch alert definitions, recent aggregations, and customers (for names)
        const [defsRes, aggsRes, custRes] = await Promise.all([
            fetch('api/command-center.php?action=get_alert_definitions&limit=500', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', signal: controller.signal }),
            fetch('api/command-center.php?action=get_aggregations&limit=500', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', signal: controller.signal }),
            fetch('api/get-customers.php', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', signal: controller.signal })
        ]);

        clearTimeout(timeout);

        const defs = defsRes.ok ? await defsRes.json() : { success: false };
        const aggs = aggsRes.ok ? await aggsRes.json() : { success: false };
        const cust = custRes.ok ? await custRes.json() : { success: false };

        const alertSet = new Map();
        if (defs.success && Array.isArray(defs.definitions)) {
            defs.definitions.forEach(d => {
                if (d.alert_code) alertSet.set(d.alert_code, d.display_name || d.alert_code);
            });
        }
        if (aggs.success && Array.isArray(aggs.aggregations)) {
            aggs.aggregations.forEach(a => {
                if (a.alert_code && !alertSet.has(a.alert_code)) alertSet.set(a.alert_code, a.alert_display_name || a.alert_code);
            });
        }

        const deviceSet = new Set();
        const customerPairs = new Map(); // code => name
        if (aggs.success && Array.isArray(aggs.aggregations)) {
            aggs.aggregations.forEach(a => {
                if (a.device_serial) deviceSet.add(a.device_serial);
                if (a.customer_code && !customerPairs.has(a.customer_code)) customerPairs.set(a.customer_code, a.customer_description || '');
            });
        }
        if (cust.success && Array.isArray(cust.customers)) {
            cust.customers.forEach(c => {
                const code = c.Code || c.code;
                const name = c.Description || c.description || '';
                if (code && !customerPairs.has(code)) customerPairs.set(code, name);
            });
        }

        _patternSuggestions = {
            alerts: Array.from(alertSet.entries()).map(([code, name]) => ({ code, name })),
            devices: Array.from(deviceSet.values()),
            customers: Array.from(customerPairs.entries()).map(([code, name]) => ({ code, name }))
        };
        _patternLoaded = true;
        return _patternSuggestions;
    } catch (_e) {
        // Ignore; leave suggestions empty
        _patternLoaded = true;
        return _patternSuggestions;
    }
}

function populatePatternDatalists() {
    try {
        const alertList = document.getElementById('alert-code-options');
        const deviceList = document.getElementById('device-serial-options');
        const customerList = document.getElementById('customer-code-options');
        if (!alertList || !deviceList || !customerList) return;

        alertList.innerHTML = _patternSuggestions.alerts.map(a => `<option value="${escapeHtml(a.code)}">${escapeHtml(a.code)} - ${escapeHtml(a.name)}</option>`).join('');
        deviceList.innerHTML = _patternSuggestions.devices.map(d => `<option value="${escapeHtml(d)}"></option>`).join('');
        customerList.innerHTML = _patternSuggestions.customers.map(c => `<option value="${escapeHtml(c.code)}">${escapeHtml(c.name || c.code)}</option>`).join('');
    } catch (_e) {
        // no-op
    }
}

// Populate Active Notifications customer filter with Names (value=Code)
async function loadCustomerOptionsForCC(selectEl) {
    try {
        // Keep first option (All Customers)
        while (selectEl.options.length > 1) selectEl.remove(1);

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000);
        const res = await fetch('api/get-customers.php', { credentials: 'same-origin', headers: { 'Accept': 'application/json' }, signal: controller.signal });
        clearTimeout(timeoutId);
        const data = res.ok ? await res.json() : { success: false };
        if (data.success && Array.isArray(data.customers)) {
            data.customers.forEach(c => {
                const code = c.Code || c.code;
                const name = c.Description || c.description || code;
                if (!code) return;
                const opt = document.createElement('option');
                opt.value = code;
                opt.textContent = name;
                selectEl.appendChild(opt);
            });
        }
    } catch (e) {
        console.warn('Failed to load customers for filter:', e);
    }
}

/*
CHANGELOG
2025-11-26 Claude
- Fixed trigger count aggregation: Changed from Math.max() to COUNT duplicates to show accurate occurrence numbers.

2025-11-26 Codex
- Collapsed duplicate notifications (same device + alert) into a single card; use MAX trigger count to avoid inflation.
- Switched "Alert Aggregations" to a list layout to prevent overflow and improve readability.
- Added searchable pattern suggestions (datalists) for alert codes, device serials, and customer codes in the rule modal.
- Show accurate 1h tallies in notifications using get_aggregations, with 30s client-side cache.
*/


// ========================================
// TAB: Panel Stream (Live Messages)
// ========================================
let panelTimerId = null;
let panelCache = [];

function initPanelTab() {
    const refreshBtn = document.getElementById('cc-panel-refresh');
    const limitSel = document.getElementById('cc-panel-limit');
    const hoursSel = document.getElementById('cc-panel-hours');
    const customerInput = document.getElementById('cc-panel-customer');

    if (!refreshBtn || !limitSel || !hoursSel) return;

    refreshBtn.onclick = loadPanelMessages;
    limitSel.onchange = loadPanelMessages;
    hoursSel.onchange = loadPanelMessages;
    if (customerInput) {
        customerInput.onchange = loadPanelMessages;
        customerInput.onkeyup = (e) => { if (e.key === 'Enter') loadPanelMessages(); };
    }

    if (customerInput && notificationCustomerFilter) {
        customerInput.value = notificationCustomerFilter;
    }

    loadPanelMessages();
}

function startPanelAutoRefresh() {
    stopPanelAutoRefresh();
    panelTimerId = setInterval(() => {
        if (currentTab === 'panel') {
            loadPanelMessages();
        }
    }, 30000);
}

function stopPanelAutoRefresh() {
    if (panelTimerId) {
        clearInterval(panelTimerId);
        panelTimerId = null;
    }
}

async function loadPanelMessages() {
    const tbody = document.getElementById('cc-panel-tbody');
    const last = document.getElementById('cc-panel-last-refresh');
    const limitSel = document.getElementById('cc-panel-limit');
    const hoursSel = document.getElementById('cc-panel-hours');
    const customerInput = document.getElementById('cc-panel-customer');
    if (!tbody) return;

    const params = new URLSearchParams({ limit: String(limitSel?.value || '200') });
    if (hoursSel?.value) params.set('hours', String(hoursSel.value));
    const customerCode = (customerInput?.value || notificationCustomerFilter || '').trim();
    if (customerCode) params.set('customerCode', customerCode);

    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 15000);
        const res = await fetch('api/get-panel-messages.php?' + params.toString(), {
            credentials: 'same-origin', headers: { 'Accept': 'application/json' }, signal: controller.signal
        });
        clearTimeout(timeoutId);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load panel messages');
        panelCache = data.messages || [];
        renderPanelRows(panelCache);
        if (last) last.textContent = 'Last refresh: ' + new Date().toLocaleTimeString() + ' · Auto 30s';
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6"><i class="fas fa-exclamation-triangle"></i> ${escapeHtml(err.message)}</td></tr>`;
        if (last) last.textContent = 'Error';
    }
}

function renderPanelRows(rows) {
    const tbody = document.getElementById('cc-panel-tbody');
    if (!tbody) return;
    if (!rows || rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6">No panel messages captured yet.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(row => {
        const customer = [
            row.customer_code ? `<strong>${escapeHtml(row.customer_code)}</strong>` : '',
            row.customer_description ? `<div>${escapeHtml(row.customer_description)}</div>` : ''
        ].join('');
        const displayName = row.display_name || row.panel_configuration || row.maintenance_alert_code || 'Alert';
        const alertHtml = `
            <div><strong>${escapeHtml(displayName)}</strong></div>
            ${row.maintenance_alert_code ? `<div style="font-size:.85em;color:#64748b;">Code: ${escapeHtml(row.maintenance_alert_code)}</div>` : ''}
            ${row.department ? `<div style=\"color:#334155;\">Dept: ${escapeHtml(row.department)}</div>` : ''}
        `;
        const received = row.received_at ? new Date(row.received_at).toLocaleString() : '';
        return `
            <tr data-id="${row.id}">
                <td>${received}</td>
                <td>${customer || ''}</td>
                <td>${escapeHtml(row.device_serial || '')}</td>
                <td>${alertHtml}</td>
                <td>${escapeHtml(row.panel_configuration || '')}</td>
                <td><button class="btn btn-secondary btn-small" data-action="view-payload"><i class="fas fa-eye"></i> View</button></td>
            </tr>
        `;
    }).join('');

    tbody.addEventListener('click', (ev) => {
        const btn = ev.target.closest('button[data-action="view-payload"]');
        if (!btn) return;
        const tr = btn.closest('tr');
        const id = tr?.getAttribute('data-id');
        if (id) ccShowPanelPayload(id);
    }, { once: true });
}

function ccShowPanelPayload(id) {
    const modal = buildPanelModal();
    const viewer = modal.querySelector('#cc-panel-payload');
    const message = panelCache.find(r => String(r.id) === String(id));
    viewer.textContent = message?.payload ? (typeof message.payload === 'object' ? JSON.stringify(message.payload, null, 2) : String(message.payload)) : 'No payload available';
    modal.classList.add('active');
}

function buildPanelModal() {
    let modal = document.getElementById('cc-panel-modal');
    if (modal) return modal;
    modal = document.createElement('div');
    modal.id = 'cc-panel-modal';
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Payload</h3>
                <button id="cc-panel-modal-close" class="btn-icon" title="Close"><i class="fas fa-times"></i></button>
            </div>
            <pre id="cc-panel-payload" class="payload-viewer">{}</pre>
        </div>
    `;
    document.body.appendChild(modal);
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('active'); });
    modal.querySelector('#cc-panel-modal-close')?.addEventListener('click', () => modal.classList.remove('active'));
    return modal;
}// ========================================
// TAB: Alert Definitions (Labels)
// ========================================
async function loadDefinitions() {
    const container = document.getElementById('definitions-container');
    if (!container) return;
    container.innerHTML = '<div class="loading">Loading alert labels...</div>';
    try {
        const res = await fetch('api/command-center.php?action=get_alert_definitions&limit=200', { credentials: 'same-origin', headers: { 'Accept':'application/json' } });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load alert labels');
        const defs = data.definitions || [];
        const rows = defs.map(d => `
            <tr>
                <td><code>${escapeHtml(d.alert_code)}</code></td>
                <td>${escapeHtml(d.display_name || '')}</td>
                <td>${escapeHtml(d.category || '')}</td>
                <td>${escapeHtml(d.severity_override || '')}</td>
            </tr>
        `).join('');
        container.innerHTML = `
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Severity</th></tr></thead>
                    <tbody>${rows || '<tr><td colspan="4">No alert labels yet.</td></tr>'}</tbody>
                </table>
            </div>
        `;
    } catch (err) {
        container.innerHTML = `<div class=\"error-message\"><i class=\"fas fa-exclamation-triangle\"></i> ${escapeHtml(err.message)}</div>`;
    }
}

document.addEventListener('visibilitychange', () => { if (document.hidden) stopPanelAutoRefresh(); else if (currentTab === 'panel') startPanelAutoRefresh(); });
/*\nCHANGELOG\n2025-11-26 Codex\n- Unified Command Center: added Panel Stream and Alert Labels tabs (lazy-loaded) and simple tools tab wiring.\n*/



async function ccShowPanelPayload(id) {
  const modal = buildPanelModal();
  const viewer = modal.querySelector('#cc-panel-payload');
  try {
    const data = await fetchJson(pi/get-panel-message.php?id=, { timeoutMs: 10000 });
    if (!data.success) throw new Error(data.error || 'Failed to fetch message');
    const msg = data.message || panelCache.find(r => String(r.id) === String(id));
    const pretty = msg?.payload ? (typeof msg.payload === 'object' ? JSON.stringify(msg.payload, null, 2) : String(msg.payload)) : 'No payload available';
    viewer.textContent = pretty;
  } catch (e) {
    const message = panelCache.find(r => String(r.id) === String(id));
    const fallback = message?.payload ? (typeof message.payload === 'object' ? JSON.stringify(message.payload, null, 2) : String(message.payload)) : '';
    viewer.textContent = fallback || Error loading payload: ;
  }
  modal.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function () {
  try {
    const params = new URLSearchParams(location.search);
    const t = (params.get('tab') || '').trim();
    const c = (params.get('customerCode') || '').trim();
    if (c) {
      notificationCustomerFilter = c;
      const sel = document.getElementById('notification-customer-filter');
      if (sel) sel.value = c;
    }
    if (t) {
      const btn = document.querySelector('.monitor-tab-btn[data-tab="' + t + '"]');
      if (btn) { setTimeout(() => btn.click(), 0); }
    }
  } catch (e) { /* ignore */ }
});
