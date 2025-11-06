<?php
require 'config.php';
require 'functions.php';

requireAuth();
ensureDeviceCrudEnabled();
trackVisit('/device-lifecycle');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Lifecycle Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/device-crud.css">
</head>
<body data-theme="light">
    <header class="monitor-header sticky">
        <div class="monitor-header-content">
            <div>
                <h1>Device Lifecycle Management</h1>
                <p class="subtitle">Create, update, and retire devices while keeping audit trails.</p>
            </div>
            <div class="header-actions">
                <a class="btn-icon" href="panel-message-monitor.php" title="Back to Panel Monitor">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>
    </header>

    <main id="device-crud-root" class="device-admin-container">
        <section class="card">
            <div class="card-header device-card-header">
                <div>
                    <h2>Device Inventory</h2>
                    <p class="subtitle">Filter by customer, search by serial, and manage pagination.</p>
                </div>
                <div class="device-card-actions">
                    <button id="device-refresh" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button id="device-create-open" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Device
                    </button>
                </div>
            </div>

            <form id="device-filter-form" class="device-filters">
                <div class="field">
                    <label for="filter-customer">Customer Code</label>
                    <input id="filter-customer" name="customerCode" type="text" placeholder="Default or leave blank for all">
                </div>
                <div class="field">
                    <label for="filter-search">Search</label>
                    <input id="filter-search" name="search" type="text" placeholder="Serial, asset, model...">
                </div>
                <div class="field">
                    <label for="filter-page-rows">Rows</label>
                    <select id="filter-page-rows" name="pageRows">
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                    </select>
                </div>
                <div class="field">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                </div>
                <div class="field">
                    <label>&nbsp;</label>
                    <button type="button" id="device-filter-reset" class="btn btn-link">
                        Reset
                    </button>
                </div>
            </form>

            <div class="table-wrapper">
                <table class="device-table">
                    <thead>
                        <tr>
                            <th>Serial</th>
                            <th>Customer</th>
                            <th>Brand / Model</th>
                            <th>Asset</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="device-table-body">
                        <tr>
                            <td colspan="7" class="text-muted">Loading devices...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <footer class="device-pagination">
                <div id="device-pagination-info" class="pagination-info">
                    Page 1
                </div>
                <div class="pagination-controls">
                    <button type="button" id="device-prev-page" class="btn btn-secondary" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" id="device-next-page" class="btn btn-secondary" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </footer>
        </section>

        <section class="card device-insight-card">
            <div class="card-header">
                <h2>Operational Guidance</h2>
            </div>
            <div class="device-tips">
                <article>
                    <h3><i class="fas fa-robot"></i> Automation Friendly</h3>
                    <p>All create, update, and delete actions proxy through <code>mps-api/query</code>, preserving the separation between the CMS and vendor API.</p>
                </article>
                <article>
                    <h3><i class="fas fa-database"></i> Cache Awareness</h3>
                    <p>Every mutation clears <code>all-devices-dealer</code> cache entries so the next refresh pulls fresh inventory. Schedule an enhanced cache refresh if bulk edits are performed.</p>
                </article>
                <article>
                    <h3><i class="fas fa-clipboard-check"></i> Audit Trail</h3>
                    <p>Each action appends to <code>cms/logs/device-crud-YYYY-MM-DD.log</code> with user, payload, and upstream responses for compliance reviews.</p>
                </article>
            </div>
        </section>
    </main>

    <div id="device-crud-toast" class="device-toast" role="status" aria-live="polite"></div>

    <div id="device-create-modal" class="device-modal" aria-hidden="true">
        <div class="device-modal-content" role="dialog" aria-modal="true">
            <header class="device-modal-header">
                <h3>Create Device</h3>
                <button type="button" class="btn-icon modal-close" data-modal-close="device-create-modal">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <form id="device-create-form" class="device-form">
                <div class="field">
                    <label for="create-customer">Customer Code</label>
                    <input id="create-customer" name="customerCode" type="text" value="<?php echo htmlspecialchars(DEFAULT_CUSTOMER_CODE, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label for="create-serial">Serial Number<span class="required">*</span></label>
                    <input id="create-serial" name="serialNumber" type="text" required>
                </div>
                <div class="field">
                    <label for="create-brand">Brand<span class="required">*</span></label>
                    <input id="create-brand" name="brand" type="text" required>
                </div>
                <div class="field">
                    <label for="create-model">Model<span class="required">*</span></label>
                    <input id="create-model" name="model" type="text" required>
                </div>
                <div class="field">
                    <label for="create-asset">Asset Number</label>
                    <input id="create-asset" name="assetNumber" type="text">
                </div>
                <div class="field">
                    <label for="create-contact">Contact</label>
                    <input id="create-contact" name="contact" type="text">
                </div>
                <div class="field">
                    <label for="create-note">Note</label>
                    <textarea id="create-note" name="note" rows="2"></textarea>
                </div>
                <footer class="device-form-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close="device-create-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create
                    </button>
                </footer>
            </form>
        </div>
    </div>

    <div id="device-update-modal" class="device-modal" aria-hidden="true">
        <div class="device-modal-content" role="dialog" aria-modal="true">
            <header class="device-modal-header">
                <h3>Update Device</h3>
                <button type="button" class="btn-icon modal-close" data-modal-close="device-update-modal">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <form id="device-update-form" class="device-form">
                <input id="update-device-id" name="deviceId" type="hidden">
                <div class="field">
                    <label for="update-device-label">Device</label>
                    <input id="update-device-label" type="text" disabled>
                </div>
                <div class="field">
                    <label for="update-brand">New Brand</label>
                    <input id="update-brand" name="newProductBrand" type="text">
                </div>
                <div class="field">
                    <label for="update-model">New Model</label>
                    <input id="update-model" name="newProductModel" type="text">
                </div>
                <div class="field">
                    <label for="update-project">Project ID</label>
                    <input id="update-project" name="projectId" type="text">
                </div>
                <div class="field">
                    <label for="update-office">Office ID</label>
                    <input id="update-office" name="officeId" type="text">
                </div>
                <div class="field checkbox-field">
                    <label>
                        <input id="update-managed" name="isManaged" type="checkbox">
                        Managed Device
                    </label>
                </div>
                <div class="field checkbox-field">
                    <label>
                        <input id="update-alerts" name="isGenerateAlert" type="checkbox">
                        Generate Alerts
                    </label>
                </div>
                <div class="field checkbox-field">
                    <label>
                        <input id="update-repeat-alerts" name="manageRepeatedAlerts" type="checkbox">
                        Manage Repeated Alerts
                    </label>
                </div>
                <footer class="device-form-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close="device-update-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </footer>
            </form>
        </div>
    </div>

    <script src="assets/device-crud.js"></script>
</body>
</html>
