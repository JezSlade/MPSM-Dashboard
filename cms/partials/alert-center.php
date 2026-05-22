<div id="alert-center-root" class="alert-center-root">
    <div class="monitor-container">
        <div class="monitor-tabs">
            <button class="monitor-tab-btn active" data-tab="notifications">
                <i class="fas fa-bell"></i> Alerts
            </button>
            <button class="monitor-tab-btn" data-tab="panel">
                <i class="fas fa-stream"></i> Panel Stream
            </button>
            <button class="monitor-tab-btn" data-tab="definitions">
                <i class="fas fa-tags"></i> Alert Labels
            </button>
            <button class="monitor-tab-btn" data-tab="statistics">
                <i class="fas fa-chart-bar"></i> Alert Statistics
            </button>
        </div>

        <div id="tab-notifications" class="tab-panel active" data-tab="notifications">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Alerts</h2>
                        <p class="text-muted">All alerts for the selected customer</p>
                    </div>
                    <div class="monitor-controls">
                        <select id="notification-filter" class="form-control">
                            <option value="">All Severities</option>
                            <option value="critical">Critical</option>
                            <option value="high">High</option>
                            <option value="warning">Warning</option>
                            <option value="info">Info</option>
                        </select>
                        <select id="notification-customer-filter" class="form-control">
                            <option value="">All Customers</option>
                        </select>
                        <label class="toggle-label">
                            <input type="checkbox" id="notification-auto-refresh" checked>
                            <span>Auto-refresh (10s)</span>
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div id="notifications-container">
                        <div class="loading">Loading notifications...</div>
                    </div>
                    <div class="section-actions" style="margin-top:1rem; display:flex; justify-content:flex-end;">
                        <button id="notification-load-more" class="btn btn-secondary" style="display:none;">
                            <i class="fas fa-plus"></i> Load More
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-panel" class="tab-panel" data-tab="panel">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Panel Stream</h2>
                        <p class="text-muted">Live panel message callbacks by time window and customer</p>
                    </div>
                    <div class="monitor-controls">
                        <div>
                            <label for="cc-panel-limit">Limit</label>
                            <select id="cc-panel-limit" class="form-control">
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200" selected>200</option>
                                <option value="300">300</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                        <div>
                            <label for="cc-panel-hours">Hours</label>
                            <select id="cc-panel-hours" class="form-control">
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
                        <div>
                            <label for="cc-panel-customer">Customer</label>
                            <select id="cc-panel-customer" class="form-control">
                                <option value="">All Customers</option>
                            </select>
                            <small class="text-muted">Filters table; leave blank for all</small>
                        </div>
                        <div class="panel-paging">
                            <label>&nbsp;</label>
                            <div class="panel-paging-controls">
                                <button id="cc-panel-prev" class="btn btn-secondary" type="button">
                                    <i class="fas fa-chevron-left"></i> Prev
                                </button>
                                <span id="cc-panel-page" class="badge">Page 1</span>
                                <button id="cc-panel-next" class="btn btn-secondary" type="button">
                                    Next <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <button id="cc-panel-refresh" class="btn btn-primary" type="button">
                            <i class="fas fa-sync-alt"></i> Refresh Now
                        </button>
                        <span id="cc-panel-last-refresh" class="badge">Auto refresh: 30s</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table class="table">
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
                            <tbody id="cc-panel-tbody">
                                <tr><td colspan="6">Waiting for data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-definitions" class="tab-panel" data-tab="definitions">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Alert Labels</h2>
                        <p class="text-muted">Interpret MPSM alert codes with human-readable names - edit directly in the table</p>
                    </div>
                </div>
                <div class="card-body">
                    <div id="definitions-container">
                        <div class="loading">Loading alert labels...</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-statistics" class="tab-panel" data-tab="statistics">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Alert Aggregations</h2>
                        <p class="text-muted">Frequency analysis of panel messages</p>
                    </div>
                    <div class="monitor-controls">
                        <select id="stats-sort" class="form-control">
                            <option value="recent">Most Recent</option>
                            <option value="frequent">Most Frequent</option>
                            <option value="critical">Critical First</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div id="statistics-container">
                        <div class="loading">Loading statistics...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="definition-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="definition-modal-title">Create Alert Label</h2>
                <button class="modal-close" onclick="closeDefinitionModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="definition-form">
                <div class="modal-body">
                    <input type="hidden" id="definition-id" name="id">

                    <div class="form-group">
                        <label for="definition-alert-code">Alert Code <span class="required">*</span></label>
                        <input type="text" id="definition-alert-code" name="alert_code" class="form-control" required placeholder="e.g., E-001">
                    </div>

                    <div class="form-group">
                        <label for="definition-display-name">Display Name <span class="required">*</span></label>
                        <input type="text" id="definition-display-name" name="display_name" class="form-control" required placeholder="e.g., Emergency Stop">
                    </div>

                    <div class="form-group">
                        <label for="definition-category">Category</label>
                        <input type="text" id="definition-category" name="category" class="form-control" placeholder="e.g., Safety">
                    </div>

                    <div class="form-group">
                        <label for="definition-severity">Severity Override</label>
                        <select id="definition-severity" name="severity_override" class="form-control">
                            <option value="">None</option>
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="definition-description">Description</label>
                        <textarea id="definition-description" name="description" class="form-control" rows="3" placeholder="Optional detailed description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDefinitionModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Label
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
