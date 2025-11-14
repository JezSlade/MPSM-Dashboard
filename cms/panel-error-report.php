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
    <title>Panel Callback Error Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-muted: #f3f4f6;
            --bg-panel: #0f172a;
            --card-bg: #ffffff;
            --border: #e5e7eb;
            --text: #1f2933;
            --text-muted: #5f6b7a;
            --accent: #1e3a8a;
            --success: #15803d;
            --error: #b91c1c;
            --warning: #d97706;
            --radius: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-muted);
            color: var(--text);
            line-height: 1.6;
        }

        header {
            background: var(--bg-panel);
            color: #f9fafb;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        header p {
            opacity: 0.7;
            font-size: 0.95rem;
        }

        main {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .stat-value.success {
            color: var(--success);
        }

        .stat-value.error {
            color: var(--error);
        }

        .stat-value.warning {
            color: var(--warning);
        }

        .section {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .section h2 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--accent);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        table th {
            background: #f8fafc;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--border);
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border);
        }

        table tr:hover {
            background: #f8fafc;
        }

        .code-block {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            margin: 1rem 0;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge.success {
            background: #dcfce7;
            color: #166534;
        }

        .badge.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge.warning {
            background: #fef3c7;
            color: #92400e;
        }

        .loading {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .spinner {
            border: 3px solid var(--border);
            border-top-color: var(--accent);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .recommendation {
            background: #fef3c7;
            border-left: 4px solid var(--warning);
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 4px;
        }

        .refresh-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .refresh-btn:hover {
            background: #1e40af;
        }

        pre {
            white-space: pre-wrap;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <header>
        <h1><i class="fas fa-bug"></i> Panel Callback Error Report</h1>
        <p>Comprehensive analysis of callback errors and debugging data</p>
    </header>

    <main>
        <button class="refresh-btn" onclick="loadReport()">
            <i class="fas fa-sync"></i> Refresh Report
        </button>

        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>Loading error report...</p>
        </div>

        <div id="report" style="display: none;">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Entries</div>
                    <div class="stat-value" id="stat-total">--</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Successful</div>
                    <div class="stat-value success" id="stat-success">--</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Errors</div>
                    <div class="stat-value error" id="stat-errors">--</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Error Rate</div>
                    <div class="stat-value warning" id="stat-rate">--</div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="section" id="recommendations-section" style="display: none;">
                <h2><i class="fas fa-lightbulb"></i> Recommendations</h2>
                <div id="recommendations"></div>
            </div>

            <!-- Error Breakdown -->
            <div class="section">
                <h2><i class="fas fa-chart-bar"></i> Error Breakdown by Type</h2>
                <table id="error-breakdown">
                    <thead>
                        <tr>
                            <th>Error Message</th>
                            <th>HTTP Code</th>
                            <th>Count</th>
                            <th>Unique Sources</th>
                            <th>First Seen</th>
                            <th>Last Seen</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Sample Errors -->
            <div class="section">
                <h2><i class="fas fa-clipboard-list"></i> Recent Error Samples</h2>
                <table id="sample-errors">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Timestamp</th>
                            <th>Error</th>
                            <th>Source</th>
                            <th>Payload Preview</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Test Data -->
            <div class="section" id="test-data-section" style="display: none;">
                <h2><i class="fas fa-flask"></i> Test/Junk Data Detected</h2>
                <p style="margin-bottom: 1rem;">Found <strong id="test-count">0</strong> entries that appear to be test data.</p>
                <div id="cleanup-sql"></div>
            </div>

            <!-- Source Analysis -->
            <div class="section">
                <h2><i class="fas fa-network-wired"></i> Source Analysis</h2>
                <table id="source-analysis">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>IP Address</th>
                            <th>Total</th>
                            <th>Success</th>
                            <th>Errors</th>
                            <th>First Seen</th>
                            <th>Last Seen</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', loadReport);

        async function loadReport() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('report').style.display = 'none';

            try {
                const response = await fetch('api/panel-error-report.php');
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Failed to load report');
                }

                renderReport(data);
            } catch (error) {
                document.getElementById('loading').innerHTML = `
                    <div style="color: var(--error);">
                        <i class="fas fa-exclamation-circle" style="font-size: 3rem;"></i>
                        <p>Error loading report: ${error.message}</p>
                    </div>
                `;
            }
        }

        function renderReport(data) {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('report').style.display = 'block';

            // Stats
            document.getElementById('stat-total').textContent = data.summary.total_entries.toLocaleString();
            document.getElementById('stat-success').textContent = data.summary.total_success.toLocaleString();
            document.getElementById('stat-errors').textContent = data.summary.total_errors.toLocaleString();
            document.getElementById('stat-rate').textContent = data.summary.error_rate_percent + '%';

            // Recommendations
            if (data.analysis.recommendations && data.analysis.recommendations.length > 0) {
                document.getElementById('recommendations-section').style.display = 'block';
                document.getElementById('recommendations').innerHTML = data.analysis.recommendations
                    .map(rec => `<div class="recommendation"><i class="fas fa-info-circle"></i> ${escapeHtml(rec)}</div>`)
                    .join('');
            }

            // Error Breakdown
            const errorTable = document.getElementById('error-breakdown').querySelector('tbody');
            errorTable.innerHTML = data.error_types.map(error => `
                <tr>
                    <td>${escapeHtml(error.message)}</td>
                    <td><span class="badge error">${error.http_code}</span></td>
                    <td><strong>${error.count}</strong></td>
                    <td>${error.unique_sources}</td>
                    <td>${error.first_seen}</td>
                    <td>${error.last_seen}</td>
                </tr>
            `).join('');

            // Sample Errors
            const sampleTable = document.getElementById('sample-errors').querySelector('tbody');
            sampleTable.innerHTML = data.sample_errors.map(error => `
                <tr>
                    <td>${error.id}</td>
                    <td>${error.timestamp}</td>
                    <td>${escapeHtml(error.message)}</td>
                    <td>${escapeHtml(error.unique_source || error.ip_address)}</td>
                    <td><code style="font-size: 0.75rem;">${escapeHtml(error.body_preview || 'N/A')}</code></td>
                </tr>
            `).join('');

            // Test Data
            if (data.test_data_detected.count > 0) {
                document.getElementById('test-data-section').style.display = 'block';
                document.getElementById('test-count').textContent = data.test_data_detected.count;

                if (data.cleanup_recommendations.sql) {
                    document.getElementById('cleanup-sql').innerHTML = `
                        <h3>Cleanup SQL</h3>
                        <p style="margin-bottom: 0.5rem; color: var(--text-muted);">${data.cleanup_recommendations.description}</p>
                        <div class="code-block"><pre>${escapeHtml(data.cleanup_recommendations.sql)}</pre></div>
                    `;
                }
            }

            // Source Analysis
            const sourceTable = document.getElementById('source-analysis').querySelector('tbody');
            sourceTable.innerHTML = data.source_analysis.map(source => `
                <tr>
                    <td>${escapeHtml(source.unique_source || 'Unknown')}</td>
                    <td>${escapeHtml(source.ip_address)}</td>
                    <td>${source.total_requests}</td>
                    <td><span class="badge success">${source.success_count}</span></td>
                    <td><span class="badge error">${source.error_count}</span></td>
                    <td>${source.first_seen}</td>
                    <td>${source.last_seen}</td>
                </tr>
            `).join('');
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
