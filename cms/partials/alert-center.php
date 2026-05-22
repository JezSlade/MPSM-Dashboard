<div id="alert-center-root" class="alert-center-root">
    <section id="tab-notifications" class="alerts-console tab-panel active" data-tab="notifications">
        <header class="alerts-console__header">
            <h2 class="alerts-console__title">MPSP Alerts</h2>
            <div class="alerts-console__controls">
                <select id="notification-customer-filter" class="alerts-customer-select" aria-label="Customer">
                    <option value="">All Customers</option>
                </select>
                <button id="alerts-refresh" class="alerts-icon-btn" type="button" title="Refresh alerts">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button id="notification-column-settings" class="alerts-icon-btn" type="button" title="Columns">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
        </header>

        <div id="notifications-container" class="alerts-console__body">
            <div class="loading">Loading alerts...</div>
        </div>

        <div class="alerts-console__footer">
            <button id="notification-load-more" class="btn btn-secondary" style="display:none;">
                <i class="fas fa-plus"></i> Load More
            </button>
        </div>
    </section>

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
