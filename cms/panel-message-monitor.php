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
        .monitor-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .monitor-tab-btn {
            appearance: none;
            border: 1px solid var(--border-color);
            border-radius: 999px;
            background: var(--card-bg);
            color: var(--text-color);
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .monitor-tab-btn i {
            font-size: 0.95rem;
        }
        .monitor-tab-btn:hover {
            background: var(--card-bg-alt);
        }
        .monitor-tab-btn.active {
            background: var(--accent-bg, #1a73e8);
            color: var(--on-accent-text, #fff);
            border-color: var(--accent-bg, #1a73e8);
        }
        .tab-panel {
            display: none;
        }
        .tab-panel.active {
            display: block;
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
        .debugger-wrapper {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            background: var(--card-bg);
            min-height: 70vh;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.08);
        }
        .debugger-frame {
            width: 100%;
            height: 100%;
            min-height: 70vh;
            border: none;
            background-color: var(--card-bg);
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
        <div class="monitor-tabs">
            <button class="monitor-tab-btn active" data-tab="messages">
                <i class="fas fa-satellite-dish"></i>
                Panel Messages
            </button>
            <?php if (defined('FEATURE_DEVICE_CRUD') && FEATURE_DEVICE_CRUD) : ?>
            <button class="monitor-tab-btn" data-tab="device-lifecycle">
                <i class="fas fa-microchip"></i>
                Device Lifecycle
            </button>
            <?php endif; ?>
            <button class="monitor-tab-btn" data-tab="debugger">
                <i class="fas fa-bug"></i>
                Payload Debugger
            </button>
        </div>

        <div id="tab-messages" class="tab-panel active" data-tab="messages">
        <section class="card">
            <div class="card-header">
                <h2>Live Callback Stream</h2>
                <span id="last-refresh" class="badge">Loading...</span>
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
                            <td colspan="6">Waiting for data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        </div>
        <?php if (defined('FEATURE_DEVICE_CRUD') && FEATURE_DEVICE_CRUD) : ?>
        <div id="tab-device-lifecycle" class="tab-panel" data-tab="device-lifecycle">
            <div class="debugger-wrapper">
                <iframe
                    class="debugger-frame"
                    src="device-lifecycle.php"
                    title="Device Lifecycle Management"
                    loading="lazy"
                    referrerpolicy="same-origin">
                </iframe>
            </div>
        </div>
        <?php endif; ?>
        <div id="tab-debugger" class="tab-panel" data-tab="debugger">
            <div class="debugger-wrapper">
                <iframe
                    class="debugger-frame"
                    src="payload-debugger.php"
                    title="Payload Debugger"
                    loading="lazy"
                    referrerpolicy="same-origin">
                </iframe>
            </div>
        </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('.monitor-tab-btn');
            const tabPanels = document.querySelectorAll('.tab-panel');

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const target = button.dataset.tab;

                    tabButtons.forEach((btn) => {
                        btn.classList.toggle('active', btn === button);
                    });

                    tabPanels.forEach((panel) => {
                        panel.classList.toggle('active', panel.dataset.tab === target);
                    });
                });
            });
        });
    </script>
</body>
</html>


