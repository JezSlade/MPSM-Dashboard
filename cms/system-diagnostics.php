<?php
require 'config.php';
require 'functions.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Diagnostics - MPSM Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
            padding: 2rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        header p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .card h2 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: #667eea;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .stat:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .stat-value {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .stat-value.success {
            color: #27ae60;
        }

        .stat-value.error {
            color: #e74c3c;
        }

        .stat-value.warning {
            color: #f39c12;
        }

        .health-status {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 600;
            text-align: center;
        }

        .health-status.EXCELLENT {
            background: #d4edda;
            color: #155724;
        }

        .health-status.WARNING {
            background: #fff3cd;
            color: #856404;
        }

        .health-status.CRITICAL {
            background: #f8d7da;
            color: #721c24;
        }

        .issue-list, .rec-list {
            list-style: none;
            padding: 0;
        }

        .issue-list li, .rec-list li {
            padding: 0.5rem;
            margin: 0.5rem 0;
            border-left: 3px solid #e74c3c;
            background: #fff5f5;
            border-radius: 4px;
        }

        .rec-list li {
            border-left-color: #3498db;
            background: #f0f8ff;
        }

        .error-breakdown {
            margin-top: 1rem;
        }

        .error-item {
            background: #f8f9fa;
            padding: 0.75rem;
            margin: 0.5rem 0;
            border-radius: 6px;
            border-left: 4px solid #e74c3c;
        }

        .error-type {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .error-meta {
            font-size: 0.85rem;
            color: #7f8c8d;
        }

        .sample-code {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 1rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            overflow-x: auto;
            margin: 0.5rem 0;
        }

        .loading {
            text-align: center;
            padding: 3rem;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .refresh-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem auto;
        }

        .refresh-btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-stethoscope"></i> System Diagnostics</h1>
            <p>Comprehensive analysis of panel callbacks, cache system, and overall health</p>
        </header>

        <button class="refresh-btn" onclick="loadDiagnostics()">
            <i class="fas fa-sync-alt"></i> Refresh Data
        </button>

        <div id="content" class="loading">
            <div class="spinner"></div>
            <p>Loading diagnostics...</p>
        </div>
    </div>

    <script>
        async function loadDiagnostics() {
            const content = document.getElementById('content');
            content.innerHTML = '<div class="loading"><div class="spinner"></div><p>Loading diagnostics...</p></div>';

            try {
                const response = await fetch('api/get-system-diagnostics.php');

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();

                console.log('API Response:', result); // Debug log

                if (!result.success) {
                    throw new Error(result.error || 'Failed to load diagnostics');
                }

                const data = result.data;

                if (!data) {
                    throw new Error('No data returned from API');
                }

                renderDiagnostics(data);
            } catch (error) {
                console.error('Diagnostics Error:', error);
                content.innerHTML = `
                    <div class="card">
                        <h2 style="color: #e74c3c;"><i class="fas fa-exclamation-triangle"></i> Error</h2>
                        <p>${escapeHtml(error.message)}</p>
                        <p style="margin-top: 1rem; font-size: 0.9rem; color: #7f8c8d;">Check browser console for details</p>
                    </div>
                `;
            }
        }

        function renderDiagnostics(data) {
            const content = document.getElementById('content');

            let html = '';

            // Safely access nested properties
            const health = data.health || { status: 'UNKNOWN', issues: [], recommendations: [] };
            const panelCallbacks = data.panel_callbacks || { total: 0, success: 0, errors: 0, last_callback: 'Never', error_breakdown: [] };
            const panelMessages = data.panel_messages || { total_messages: 0, unique_devices: 0, unique_customers: 0, last_message: 'Never' };
            const cache = data.cache || { total_devices: 0, devices_with_drilldown: 0, coverage_percent: 0, freshness: { cached_within_hour: 0 } };

            // Health Status
            html += `
                <div class="card">
                    <h2><i class="fas fa-heartbeat"></i> System Health</h2>
                    <div class="health-status ${health.status}">${health.status}</div>
                    ${health.issues.length > 0 ? `
                        <h3 style="margin-top: 1rem; font-size: 1rem;">Issues Detected:</h3>
                        <ul class="issue-list">
                            ${health.issues.map(issue => `<li>${issue}</li>`).join('')}
                        </ul>
                    ` : ''}
                    ${health.recommendations.length > 0 ? `
                        <h3 style="margin-top: 1rem; font-size: 1rem;">Recommendations:</h3>
                        <ul class="rec-list">
                            ${health.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                        </ul>
                    ` : ''}
                </div>
            `;

            // Stats Grid
            html += '<div class="grid">';

            // Panel Callbacks Card
            html += `
                <div class="card">
                    <h2><i class="fas fa-exchange-alt"></i> Panel Callbacks</h2>
                    <div class="stat">
                        <span class="stat-label">Total Callbacks</span>
                        <span class="stat-value">${panelCallbacks.total.toLocaleString()}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Successful</span>
                        <span class="stat-value success">${panelCallbacks.success.toLocaleString()}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Errors</span>
                        <span class="stat-value error">${panelCallbacks.errors.toLocaleString()}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Last Callback</span>
                        <span class="stat-value">${panelCallbacks.last_callback || 'Never'}</span>
                    </div>
                </div>
            `;

            // Panel Messages Card
            html += `
                <div class="card">
                    <h2><i class="fas fa-comments"></i> Panel Messages</h2>
                    <div class="stat">
                        <span class="stat-label">Total Messages</span>
                        <span class="stat-value success">${panelMessages.total_messages.toLocaleString()}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Unique Devices</span>
                        <span class="stat-value">${panelMessages.unique_devices.toLocaleString()}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Unique Customers</span>
                        <span class="stat-value">${panelMessages.unique_customers.toLocaleString()}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Last Message</span>
                        <span class="stat-value">${panelMessages.last_message || 'Never'}</span>
                    </div>
                </div>
            `;

            // Cache Statistics Card
            html += `
                <div class="card">
                    <h2><i class="fas fa-database"></i> Drill-Down Cache</h2>
                    <div class="stat">
                        <span class="stat-label">Total Devices</span>
                        <span class="stat-value">${cache.total_devices.toLocaleString()}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">With Drill-Down Cache</span>
                        <span class="stat-value success">${cache.devices_with_drilldown.toLocaleString()}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Coverage</span>
                        <span class="stat-value ${cache.coverage_percent >= 80 ? 'success' : 'warning'}">
                            ${cache.coverage_percent}%
                        </span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Cached in Last Hour</span>
                        <span class="stat-value">${(cache.freshness?.cached_within_hour || 0).toLocaleString()}</span>
                    </div>
                </div>
            `;

            html += '</div>';

            // Error Breakdown
            if (panelCallbacks.error_breakdown && panelCallbacks.error_breakdown.length > 0) {
                html += `
                    <div class="card">
                        <h2><i class="fas fa-bug"></i> Error Breakdown</h2>
                        <div class="error-breakdown">
                            ${panelCallbacks.error_breakdown.map(err => `
                                <div class="error-item">
                                    <div class="error-type">${err.error_type}</div>
                                    <div class="error-meta">
                                        Count: ${err.count.toLocaleString()} |
                                        Last: ${err.last_occurrence}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            // Invalid JSON Analysis
            const jsonAnalysis = panelCallbacks.invalid_json_analysis || { total: 0, samples: [] };
            if (jsonAnalysis.total > 0) {
                html += `
                    <div class="card">
                        <h2><i class="fas fa-code"></i> Invalid JSON Analysis</h2>
                        <div class="stat">
                            <span class="stat-label">Total Analyzed</span>
                            <span class="stat-value">${jsonAnalysis.total}</span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Actually Invalid JSON</span>
                            <span class="stat-value error">${jsonAnalysis.actually_invalid}</span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Valid but Not Array</span>
                            <span class="stat-value warning">${jsonAnalysis.valid_but_not_array}</span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Valid Null</span>
                            <span class="stat-value warning">${jsonAnalysis.valid_null}</span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Empty Bodies</span>
                            <span class="stat-value">${jsonAnalysis.empty}</span>
                        </div>
                        ${jsonAnalysis.samples.length > 0 ? `
                            <h3 style="margin-top: 1rem; font-size: 1rem;">Sample Errors:</h3>
                            ${jsonAnalysis.samples.map(sample => `
                                <div style="margin: 0.5rem 0;">
                                    <strong>ID ${sample.id}</strong> (${sample.type})
                                    <div class="sample-code">${escapeHtml(sample.body || '')}</div>
                                    ${sample.error ? `<em style="color: #e74c3c;">Error: ${sample.error}</em>` : ''}
                                </div>
                            `).join('')}
                        ` : ''}
                    </div>
                `;
            }

            content.innerHTML = html;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load on page load
        loadDiagnostics();
    </script>
</body>
</html>
