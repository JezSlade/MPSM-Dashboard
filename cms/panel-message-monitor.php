<?php
require 'config.php';
require 'functions.php';

requireAuth();
trackVisit('/panel-message-monitor');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Message Monitor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .monitor-container {
            margin: 2rem auto;
            max-width: 1200px;
        }
        .monitor-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: center;
        }
        .monitor-controls label {
            font-weight: 600;
            margin-right: 0.5rem;
        }
        .monitor-controls input,
        .monitor-controls select {
            padding: 0.35rem 0.5rem;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            min-width: 140px;
        }
        .monitor-table {
            width: 100%;
            border-collapse: collapse;
        }
        .monitor-table th,
        .monitor-table td {
            border-bottom: 1px solid var(--border-color);
            padding: 0.6rem;
            text-align: left;
            vertical-align: top;
        }
        .monitor-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }
        .payload-viewer {
            font-family: "Fira Code", "Courier New", monospace;
            font-size: 0.85rem;
            background: var(--card-bg-alt);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 1rem;
            max-height: 320px;
            overflow: auto;
            white-space: pre-wrap;
        }
        .modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.45);
            z-index: 9999;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: var(--card-bg);
            color: var(--text-color);
            border-radius: 8px;
            padding: 1.5rem;
            max-width: 80vw;
            max-height: 80vh;
            overflow: auto;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.25);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.15rem 0.45rem;
            border-radius: 999px;
            background: var(--accent-bg, #e3f2fd);
            color: var(--accent-text, #1565c0);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>Panel Message Monitor</h1>
            <div class="header-actions">
                <a class="btn-icon" href="index.php" title="Back to Dashboard">
                    <i class="fas fa-home"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="monitor-container">
        <section class="card">
            <div class="card-header">
                <h2>Live Callback Stream</h2>
                <span id="last-refresh" class="badge">Loading…</span>
            </div>
            <div class="monitor-controls">
                <div>
                    <label for="message-limit">Limit</label>
                    <select id="message-limit">
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200" selected>200</option>
                        <option value="300">300</option>
                        <option value="500">500</option>
                    </select>
                </div>
                <div>
                    <label for="hours-window">Hours</label>
                    <select id="hours-window">
                        <option value="">All</option>
                        <option value="1">Last 1 hour</option>
                        <option value="6">Last 6 hours</option>
                        <option value="12">Last 12 hours</option>
                        <option value="24">Last 24 hours</option>
                        <option value="48">Last 48 hours</option>
                        <option value="72">Last 72 hours</option>
                        <option value="168">Last 7 days</option>
                    </select>
                </div>
                <button id="refresh-btn" class="btn btn-primary" type="button">
                    <i class="fas fa-sync-alt"></i> Refresh Now
                </button>
                <span id="auto-refresh-indicator" class="badge">Auto refresh: 30s</span>
            </div>

            <div class="table-wrapper">
                <table class="monitor-table">
                    <thead>
                        <tr>
                            <th>Received</th>
                            <th>Customer</th>
                            <th>Device</th>
                            <th>Alert</th>
                            <th>Panel Config</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="message-table-body">
                        <tr>
                            <td colspan="6">Waiting for data…</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div id="payload-modal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Payload</h3>
                <button id="modal-close" class="btn-icon" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <pre id="payload-viewer" class="payload-viewer">{}</pre>
        </div>
    </div>

    <script src="assets/panel-messages.js"></script>
</body>
</html>
