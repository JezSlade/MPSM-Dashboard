/**
 * Error Log Viewer Module
 * Displays and manages PHP error logs in the Admin section
 */

(function() {
    'use strict';

    let autoRefreshInterval = null;

    // Load error logs
    async function loadErrorLogs() {
        const lines = document.getElementById('log-lines-count').value;
        const level = document.getElementById('log-level-filter').value;
        const filter = document.getElementById('log-search-filter').value;

        const container = document.getElementById('error-logs-display');
        container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading error logs...</div>';

        try {
            const url = new URL('api/get-error-logs.php', window.location.origin + window.location.pathname);
            url.searchParams.append('lines', lines);
            if (level) url.searchParams.append('level', level);
            if (filter) url.searchParams.append('filter', filter);

            const response = await fetch(url);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Failed to load error logs');
            }

            displayErrorLogs(data);
        } catch (error) {
            container.innerHTML = `<div class="error-message">
                <i class="fas fa-exclamation-circle"></i> Failed to load error logs: ${error.message}
            </div>`;
        }
    }

    // Display error logs
    function displayErrorLogs(data) {
        const container = document.getElementById('error-logs-display');

        if (!data.logs || data.logs.length === 0) {
            container.innerHTML = `
                <div class="no-logs">
                    <i class="fas fa-inbox"></i>
                    <p>No error logs found</p>
                    <small>${data.message || 'The error log file is empty or does not exist'}</small>
                </div>
            `;
            return;
        }

        // Build stats
        const stats = {
            total: data.total,
            errors: data.logs.filter(l => l.level === 'error' || l.level === 'fatal error').length,
            warnings: data.logs.filter(l => l.level === 'warning').length,
            searches: data.logs.filter(l => l.message.toLowerCase().includes('search')).length
        };

        // Build HTML
        let html = `
            <div class="log-stats">
                <div class="log-stats-item">
                    <span class="log-stats-label">Total Entries</span>
                    <span class="log-stats-value">${stats.total}</span>
                </div>
                <div class="log-stats-item">
                    <span class="log-stats-label">Errors</span>
                    <span class="log-stats-value" style="color: #ef4444;">${stats.errors}</span>
                </div>
                <div class="log-stats-item">
                    <span class="log-stats-label">Warnings</span>
                    <span class="log-stats-value" style="color: #f59e0b;">${stats.warnings}</span>
                </div>
                <div class="log-stats-item">
                    <span class="log-stats-label">Search Events</span>
                    <span class="log-stats-value" style="color: #22c55e;">${stats.searches}</span>
                </div>
                <div class="log-stats-item">
                    <span class="log-stats-label">File Size</span>
                    <span class="log-stats-value">${formatBytes(data.fileSize)}</span>
                </div>
                <div class="log-stats-item">
                    <span class="log-stats-label">Last Modified</span>
                    <span class="log-stats-value">${formatRelativeTime(data.lastModified)}</span>
                </div>
            </div>
        `;

        html += '<div class="log-entries">';
        data.logs.forEach(log => {
            const levelClass = log.level.toLowerCase().replace(/\s+/g, '-');
            html += `
                <div class="log-entry log-${levelClass}">
                    <div>
                        ${log.timestamp ? `<span class="log-timestamp">${log.timestamp}</span>` : ''}
                        <span class="log-level ${log.level.toLowerCase()}">${log.level}</span>
                        <span class="log-message">${escapeHtml(log.message)}</span>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;
    }

    // Toggle auto-refresh
    function toggleAutoRefresh() {
        const button = document.getElementById('auto-refresh-logs');
        const isActive = button.getAttribute('data-active') === 'true';

        if (isActive) {
            // Stop auto-refresh
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
            button.setAttribute('data-active', 'false');
            button.classList.remove('active');
            button.innerHTML = '<i class="fas fa-play"></i> Auto-Refresh';
        } else {
            // Start auto-refresh (every 5 seconds)
            loadErrorLogs();
            autoRefreshInterval = setInterval(loadErrorLogs, 5000);
            button.setAttribute('data-active', 'true');
            button.classList.add('active');
            button.innerHTML = '<i class="fas fa-pause"></i> Auto-Refresh';
        }
    }

    // Format bytes
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Format relative time
    function formatRelativeTime(timestamp) {
        const now = Math.floor(Date.now() / 1000);
        const diff = now - timestamp;

        if (diff < 60) return 'Just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Load logs on page load if admin tab is active
        if (document.getElementById('admin-logs')) {
            // Wait a bit for tab switching
            setTimeout(() => {
                const adminSection = document.getElementById('admin-logs');
                if (adminSection && adminSection.classList.contains('active')) {
                    loadErrorLogs();
                }
            }, 500);
        }

        // Event listeners
        const refreshButton = document.getElementById('refresh-logs');
        if (refreshButton) {
            refreshButton.addEventListener('click', loadErrorLogs);
        }

        const autoRefreshButton = document.getElementById('auto-refresh-logs');
        if (autoRefreshButton) {
            autoRefreshButton.addEventListener('click', toggleAutoRefresh);
        }

        const logLinesCount = document.getElementById('log-lines-count');
        if (logLinesCount) {
            logLinesCount.addEventListener('change', loadErrorLogs);
        }

        const logLevelFilter = document.getElementById('log-level-filter');
        if (logLevelFilter) {
            logLevelFilter.addEventListener('change', loadErrorLogs);
        }

        const logSearchFilter = document.getElementById('log-search-filter');
        if (logSearchFilter) {
            let searchTimeout;
            logSearchFilter.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(loadErrorLogs, 500);
            });
        }

        // Load logs when switching to logs tab
        document.querySelectorAll('.admin-nav-btn[data-section="logs"]').forEach(btn => {
            btn.addEventListener('click', function() {
                setTimeout(loadErrorLogs, 100);
            });
        });
    });

    // Expose functions globally
    window.ErrorLogViewer = {
        load: loadErrorLogs,
        toggleAutoRefresh: toggleAutoRefresh
    };
})();
