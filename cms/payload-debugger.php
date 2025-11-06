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
    <title>Payload Debugger</title>
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
            --accent-light: #e5ecff;
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
        }

        header {
            background: var(--bg-panel);
            color: #f9fafb;
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        header h1 {
            font-size: 1.6rem;
            font-weight: 600;
        }

        header p {
            opacity: 0.7;
            font-size: 0.9rem;
        }

        main {
            max-width: 1400px;
            margin: 1.5rem auto 2.5rem;
            padding: 0 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 600;
        }

        .stat-value.success {
            color: #15803d;
        }

        .stat-value.error {
            color: #b91c1c;
        }

        .controls {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 0.75rem 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, auto));
            gap: 0.75rem;
            align-items: center;
        }

        .controls label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--text-muted);
            margin-bottom: 0.3rem;
        }

        .controls select {
            width: 100%;
            padding: 0.35rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
            background: #fff;
        }

        .controls button {
            padding: 0.45rem 1rem;
            background: var(--bg-panel);
            color: #f9fafb;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .controls button:hover {
            background: #1f2a44;
        }

        .auto-refresh {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .auto-refresh label {
            margin-bottom: 0 !important;
        }

        .summary-panel {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .summary-panel h2 {
            font-size: 1rem;
            font-weight: 600;
        }

        .source-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 0.75rem;
        }

        .source-item {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.75rem;
            background: #fafbff;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            font-size: 0.88rem;
        }

        .source-count {
            font-weight: 600;
            color: var(--accent);
        }

        .logs-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .log-entry {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .log-status {
            display: inline-block;
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .log-status.SUCCESS {
            background: #dcfce7;
            color: #166534;
        }

        .log-status.ERROR {
            background: #fee2e2;
            color: #991b1b;
        }

        .log-status.PROCESSING {
            background: #fef3c7;
            color: #92400e;
        }

        .log-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.45rem 1rem;
            font-size: 0.85rem;
        }

        .log-meta .label {
            color: var(--text-muted);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            margin-bottom: 0.2rem;
        }

        .log-meta .value {
            font-family: 'Fira Code', 'Courier New', monospace;
            font-size: 0.82rem;
        }

        .log-body {
            background: #f7f9fc;
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #dbe3f0;
            overflow-x: auto;
            display: none;
        }

        .log-body pre {
            font-family: 'Fira Code', 'Courier New', monospace;
            font-size: 0.82rem;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .expand-btn {
            border: none;
            background: var(--accent-light);
            color: var(--accent);
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            margin-top: 0.4rem;
        }

        .no-logs {
            background: var(--card-bg);
            border: 1px dashed var(--border);
            border-radius: var(--radius);
            padding: 2.5rem 0;
            text-align: center;
            color: var(--text-muted);
        }

        .no-logs i {
            font-size: 3rem;
            margin-bottom: 0.75rem;
            color: #d1d9e6;
        }

        @media (max-width: 768px) {
            header {
                padding: 1rem 1.25rem;
            }

            main {
                padding: 0 1rem;
            }

            .log-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Payload Debugger</h1>
        <p>Authoritative log of every callback received by the platform.</p>
    </header>

    <main>
        <section class="stats-grid">
            <article class="stat-card">
                <span class="stat-label">Total Requests</span>
                <span id="stat-total" class="stat-value">--</span>
            </article>
            <article class="stat-card">
                <span class="stat-label">Successful</span>
                <span id="stat-success" class="stat-value success">--</span>
            </article>
            <article class="stat-card">
                <span class="stat-label">Errors</span>
                <span id="stat-errors" class="stat-value error">--</span>
            </article>
            <article class="stat-card">
                <span class="stat-label">Last Request</span>
                <span id="stat-last" class="stat-value" style="font-size:1rem;">--</span>
            </article>
        </section>

        <section class="controls">
            <div>
                <label for="filter-status">Status</label>
                <select id="filter-status">
                    <option value="">All</option>
                    <option value="SUCCESS">Success</option>
                    <option value="ERROR">Error</option>
                    <option value="PROCESSING">Processing</option>
                </select>
            </div>
            <div>
                <label for="filter-source">Source</label>
                <select id="filter-source">
                    <option value="">All Sources</option>
                </select>
            </div>
            <div>
                <label for="filter-limit">Limit</label>
                <select id="filter-limit">
                    <option value="50">50</option>
                    <option value="100" selected>100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                </select>
            </div>
            <div class="auto-refresh">
                <input type="checkbox" id="auto-refresh" checked>
                <label for="auto-refresh">Auto refresh (5s)</label>
            </div>
            <button id="refresh-now">Refresh Now</button>
        </section>

        <section class="logs-container" id="logs-container">
            <div class="no-logs">
                <i class="fas fa-inbox"></i>
                <p>No callback requests logged yet</p>
                <p style="margin-top:0.4rem;font-size:0.88rem;">Send test requests to <code>/mps-api/callbacks/panel-message-debug.php</code></p>
            </div>
        </section>
    </main>

    <script>
        let autoRefreshInterval = null;

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('refresh-now').addEventListener('click', loadLogs);

            document.getElementById('auto-refresh').addEventListener('change', (event) => {
                clearInterval(autoRefreshInterval);
                if (event.target.checked) {
                    autoRefreshInterval = setInterval(loadLogs, 5000);
                }
            });

            document.getElementById('filter-status').addEventListener('change', loadLogs);
            document.getElementById('filter-source').addEventListener('change', loadLogs);
            document.getElementById('filter-limit').addEventListener('change', loadLogs);

            loadLogs();
            autoRefreshInterval = setInterval(loadLogs, 5000);
        });

        async function loadLogs() {
            const status = document.getElementById('filter-status').value;
            const source = document.getElementById('filter-source').value;
            const limit = document.getElementById('filter-limit').value;

            const params = new URLSearchParams({ limit });
            if (status) {
                params.append('status', status);
            }
            if (source) {
                params.append('source', source);
            }

            try {
                const response = await fetch(`api/get-payload-debug-logs.php?${params.toString()}`);
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Failed to load logs');
                }

                updateStats(data.stats);
                updateSourceFilter(data.sources || []);
                renderLogs(data.logs || []);
            } catch (error) {
                const container = document.getElementById('logs-container');
                container.innerHTML = `
                    <div class="no-logs">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Error loading logs</p>
                        <p style="margin-top:0.4rem;font-size:0.88rem;">${error.message}</p>
                    </div>
                `;
            }
        }

        function updateStats(stats) {
            document.getElementById('stat-total').textContent = stats.total ?? 0;
            document.getElementById('stat-success').textContent = stats.success_count ?? 0;
            document.getElementById('stat-errors').textContent = stats.error_count ?? 0;
        const lastSeen = stats.last_completed || stats.last_request || 'Never';
        document.getElementById('stat-last').textContent = lastSeen;
        }

        function updateSourceFilter(sources) {
            const select = document.getElementById('filter-source');
            const currentValue = select.value;

            // Rebuild options but preserve selection
            const optionsHTML = ['<option value="">All Sources</option>'].concat(
                sources.map(source => {
                    const label = `${source.unique_source} (${source.count})`;
                    return `<option value="${escapeHtml(source.unique_source)}">${escapeHtml(label)}</option>`;
                })
            ).join('');

            select.innerHTML = optionsHTML;

            // Restore previous selection if it still exists
            if (currentValue && sources.some(s => s.unique_source === currentValue)) {
                select.value = currentValue;
            }
        }

        function renderLogs(logs) {
            const container = document.getElementById('logs-container');

            if (!logs.length) {
                container.innerHTML = `
                    <div class="no-logs">
                        <i class="fas fa-inbox"></i>
                        <p>No callback requests logged yet</p>
                        <p style="margin-top:0.4rem;font-size:0.88rem;">Send test requests to <code>/mps-api/callbacks/panel-message-debug.php</code></p>
                    </div>
                `;
                return;
            }

            container.innerHTML = logs.map((log) => `
                <article class="log-entry">
                    <header class="log-header">
                        <span class="log-status ${log.status}">${log.status}</span>
                        <span style="font-size:0.85rem;color:var(--text-muted);">ID ${log.id} â€¢ ${log.timestamp}</span>
                    </header>
                    <section class="log-meta">
                        <div>
                            <span class="label">Unique Source</span>
                            <span class="value">${escapeHtml(log.unique_source || log.ip_address || 'Unknown')}</span>
                        </div>
                        <div>
                            <span class="label">HTTP Method</span>
                            <span class="value">${escapeHtml(log.http_method)}</span>
                        </div>
                        <div>
                            <span class="label">Content Type</span>
                            <span class="value">${escapeHtml(log.content_type || 'not set')}</span>
                        </div>
                        <div>
                            <span class="label">HTTP Code</span>
                            <span class="value">${log.http_code ?? 'pending'}</span>
                        </div>
                        <div>
                            <span class="label">Completed</span>
                            <span class="value">${log.completed_at ? escapeHtml(log.completed_at) : 'Pending'}</span>
                        </div>
                        ${log.forwarded_for ? `
                        <div>
                            <span class="label">Forwarded For</span>
                            <span class="value">${escapeHtml(log.forwarded_for)}</span>
                        </div>` : ''}
                    </section>

                    ${log.message ? `<div style="font-size:0.88rem;"><strong>Message:</strong> ${escapeHtml(log.message)}</div>` : ''}
                    ${log.user_agent ? `<div style="font-size:0.82rem;color:var(--text-muted);">User-Agent: ${escapeHtml(log.user_agent)}</div>` : ''}

                    ${log.raw_body ? `
                        <button class="expand-btn" onclick="toggleBody(${log.id})">
                            <i class="fas fa-code"></i> View Payload
                        </button>
                        <div id="body-${log.id}" class="log-body">
                            <pre>${formatPayload(log.parsed_body, log.raw_body)}</pre>
                        </div>` : ''}

                    ${log.headers ? `
                        <button class="expand-btn" onclick="toggleHeaders(${log.id})">
                            <i class="fas fa-list"></i> View Headers
                        </button>
                        <div id="headers-${log.id}" class="log-body">
                            <pre>${JSON.stringify(log.headers, null, 2)}</pre>
                        </div>` : ''}
                </article>
            `).join('');
        }

        function toggleBody(id) {
            const element = document.getElementById(`body-${id}`);
            if (element) {
                element.style.display = element.style.display === 'block' ? 'none' : 'block';
            }
        }

        function toggleHeaders(id) {
            const element = document.getElementById(`headers-${id}`);
            if (element) {
                element.style.display = element.style.display === 'block' ? 'none' : 'block';
            }
        }

        function formatPayload(parsed, raw) {
            if (parsed) {
                return JSON.stringify(parsed, null, 2);
            }
            return raw || 'Payload not available';
        }

        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }
    </script>
</body>
</html>


