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
    <title>Panel Callback Payload Debugger - MPS Monitor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f6fa;
            color: #2c3e50;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .container {
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
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stat-card .label {
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .stat-card.success .value {
            color: #27ae60;
        }

        .stat-card.error .value {
            color: #e74c3c;
        }

        .controls {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .controls select {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .controls button {
            padding: 0.5rem 1.25rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.95rem;
            transition: background 0.3s;
        }

        .controls button:hover {
            background: #5568d3;
        }

        .controls .auto-refresh {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: auto;
        }

        .logs-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .log-entry {
            border-bottom: 1px solid #ecf0f1;
            padding: 1.5rem;
            transition: background 0.2s;
        }

        .log-entry:hover {
            background: #f8f9fa;
        }

        .log-entry:last-child {
            border-bottom: none;
        }

        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .log-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .log-status.SUCCESS {
            background: #d4edda;
            color: #155724;
        }

        .log-status.ERROR {
            background: #f8d7da;
            color: #721c24;
        }

        .log-status.PROCESSING {
            background: #fff3cd;
            color: #856404;
        }

        .log-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .log-meta-item {
            display: flex;
            flex-direction: column;
        }

        .log-meta-item .label {
            color: #7f8c8d;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .log-meta-item .value {
            color: #2c3e50;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .log-body {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
        }

        .log-body pre {
            margin: 0;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .expand-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .expand-btn:hover {
            background: #2980b9;
        }

        .collapsed {
            display: none;
        }

        .no-logs {
            text-align: center;
            padding: 3rem;
            color: #7f8c8d;
        }

        .no-logs i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
        }

        .loading i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-bug"></i> Panel Callback Payload Debugger</h1>
        <p>Real-time monitoring of all incoming MPSM panel message callbacks</p>
    </div>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Total Requests</div>
                <div class="value" id="stat-total">-</div>
            </div>
            <div class="stat-card success">
                <div class="label">Success</div>
                <div class="value" id="stat-success">-</div>
            </div>
            <div class="stat-card error">
                <div class="label">Errors</div>
                <div class="value" id="stat-errors">-</div>
            </div>
            <div class="stat-card">
                <div class="label">Last Request</div>
                <div class="value" id="stat-last" style="font-size: 1.2rem;">-</div>
            </div>
        </div>

        <div class="controls">
            <select id="filter-status">
                <option value="">All Requests</option>
                <option value="SUCCESS">Success Only</option>
                <option value="ERROR">Errors Only</option>
                <option value="PROCESSING">Processing</option>
            </select>

            <select id="filter-limit">
                <option value="50">Last 50</option>
                <option value="100" selected>Last 100</option>
                <option value="200">Last 200</option>
                <option value="500">Last 500</option>
            </select>

            <button onclick="loadLogs()">
                <i class="fas fa-sync"></i> Refresh
            </button>

            <div class="auto-refresh">
                <input type="checkbox" id="auto-refresh" checked>
                <label for="auto-refresh">Auto-refresh (5s)</label>
            </div>
        </div>

        <div class="logs-container">
            <div id="logs-content" class="loading">
                <i class="fas fa-spinner"></i>
                <p>Loading logs...</p>
            </div>
        </div>
    </div>

    <script>
        let autoRefreshInterval = null;

        async function loadLogs() {
            const status = document.getElementById('filter-status').value;
            const limit = document.getElementById('filter-limit').value;

            const params = new URLSearchParams({ limit });
            if (status) {
                params.append('status', status);
            }

            try {
                const response = await fetch(`api/get-payload-debug-logs.php?${params}`);
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Failed to load logs');
                }

                // Update stats
                document.getElementById('stat-total').textContent = data.stats.total;
                document.getElementById('stat-success').textContent = data.stats.success_count;
                document.getElementById('stat-errors').textContent = data.stats.error_count;
                document.getElementById('stat-last').textContent = data.stats.last_request || 'Never';

                // Render logs
                renderLogs(data.logs);

            } catch (error) {
                document.getElementById('logs-content').innerHTML = `
                    <div class="no-logs">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error loading logs: ${error.message}</p>
                    </div>
                `;
            }
        }

        function renderLogs(logs) {
            const container = document.getElementById('logs-content');

            if (logs.length === 0) {
                container.innerHTML = `
                    <div class="no-logs">
                        <i class="fas fa-inbox"></i>
                        <p>No callback requests logged yet</p>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem;">
                            Send test requests to <code>/mps-api/callbacks/panel-message-debug.php</code>
                        </p>
                    </div>
                `;
                return;
            }

            container.innerHTML = logs.map(log => `
                <div class="log-entry">
                    <div class="log-header">
                        <span class="log-status ${log.status}">${log.status}</span>
                        <span style="color: #7f8c8d; font-size: 0.9rem;">
                            ID: ${log.id} | ${log.timestamp}
                        </span>
                    </div>

                    <div class="log-meta">
                        <div class="log-meta-item">
                            <span class="label">IP Address</span>
                            <span class="value">${log.ip_address}</span>
                        </div>
                        <div class="log-meta-item">
                            <span class="label">HTTP Method</span>
                            <span class="value">${log.http_method}</span>
                        </div>
                        <div class="log-meta-item">
                            <span class="label">Content-Type</span>
                            <span class="value">${log.content_type || 'not set'}</span>
                        </div>
                        <div class="log-meta-item">
                            <span class="label">HTTP Code</span>
                            <span class="value">${log.http_code || 'pending'}</span>
                        </div>
                    </div>

                    ${log.message ? `
                        <div style="margin: 0.5rem 0; color: #2c3e50;">
                            <strong>Message:</strong> ${log.message}
                        </div>
                    ` : ''}

                    ${log.user_agent ? `
                        <div style="margin: 0.5rem 0; color: #7f8c8d; font-size: 0.85rem;">
                            <strong>User-Agent:</strong> ${log.user_agent}
                        </div>
                    ` : ''}

                    ${log.raw_body ? `
                        <button class="expand-btn" onclick="toggleBody(${log.id})">
                            <i class="fas fa-code"></i> View Payload
                        </button>
                        <div id="body-${log.id}" class="log-body collapsed">
                            <pre>${JSON.stringify(log.parsed_body || log.raw_body, null, 2)}</pre>
                        </div>
                    ` : ''}

                    ${log.headers ? `
                        <button class="expand-btn" onclick="toggleHeaders(${log.id})">
                            <i class="fas fa-list"></i> View Headers
                        </button>
                        <div id="headers-${log.id}" class="log-body collapsed">
                            <pre>${JSON.stringify(log.headers, null, 2)}</pre>
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        function toggleBody(id) {
            const element = document.getElementById(`body-${id}`);
            element.classList.toggle('collapsed');
        }

        function toggleHeaders(id) {
            const element = document.getElementById(`headers-${id}`);
            element.classList.toggle('collapsed');
        }

        // Auto-refresh toggle
        document.getElementById('auto-refresh').addEventListener('change', (e) => {
            if (e.target.checked) {
                autoRefreshInterval = setInterval(loadLogs, 5000);
            } else {
                clearInterval(autoRefreshInterval);
            }
        });

        // Filter change listeners
        document.getElementById('filter-status').addEventListener('change', loadLogs);
        document.getElementById('filter-limit').addEventListener('change', loadLogs);

        // Initial load
        loadLogs();

        // Start auto-refresh
        autoRefreshInterval = setInterval(loadLogs, 5000);
    </script>
</body>
</html>
