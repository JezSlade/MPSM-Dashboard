<?php
require 'config.php';
require 'functions.php';

requireAuth();
ensureDeviceCrudEnabled();
trackVisit('/device-lifecycle');

$prefillCustomerCode = isset($_GET['customerCode']) ? trim((string) $_GET['customerCode']) : '';
$prefillSearch = isset($_GET['search']) ? trim((string) $_GET['search']) : '';

// Allow iframe embedding from same origin (panel-message-monitor.php)
header('X-Frame-Options: SAMEORIGIN');
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
                    <input id="filter-customer" name="customerCode" type="text" placeholder="Default or leave blank for all" value="<?php echo htmlspecialchars($prefillCustomerCode, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label for="filter-search">Search</label>
                    <input id="filter-search" name="search" type="text" placeholder="Serial, asset, model..." value="<?php echo htmlspecialchars($prefillSearch, ENT_QUOTES, 'UTF-8'); ?>">
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
                <fieldset class="form-section">
                    <legend><i class="fas fa-info-circle"></i> Basic Information</legend>
                    <div class="form-grid">
                        <div class="field">
                            <label for="create-customer">Customer Code</label>
                            <input id="create-customer" name="customerCode" type="text" value="<?php echo htmlspecialchars(DEFAULT_CUSTOMER_CODE, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="field">
                            <label for="create-serial">Serial Number<span class="required">*</span></label>
                            <input id="create-serial" name="serialNumber" type="text" required placeholder="e.g., ABC123456">
                        </div>
                        <div class="field">
                            <label for="create-brand">Brand<span class="required">*</span></label>
                            <input id="create-brand" name="brand" type="text" required placeholder="e.g., HP, Canon, Xerox">
                        </div>
                        <div class="field">
                            <label for="create-model">Model<span class="required">*</span></label>
                            <input id="create-model" name="model" type="text" required placeholder="e.g., LaserJet Pro M404n">
                        </div>
                        <div class="field">
                            <label for="create-description">Product Description</label>
                            <input id="create-description" name="productDescription" type="text" placeholder="Optional description">
                        </div>
                        <div class="field">
                            <label for="create-asset">Asset Number</label>
                            <input id="create-asset" name="assetNumber" type="text" placeholder="Internal asset tag">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-section">
                    <legend><i class="fas fa-building"></i> Location & Contact</legend>
                    <div class="form-grid">
                        <div class="field">
                            <label for="create-department">Department</label>
                            <input id="create-department" name="department" type="text" placeholder="e.g., Finance, HR">
                        </div>
                        <div class="field">
                            <label for="create-contact">Contact</label>
                            <input id="create-contact" name="contact" type="text" placeholder="Primary contact name">
                        </div>
                        <div class="field">
                            <label for="create-ip">IP Address</label>
                            <input id="create-ip" name="ipAddress" type="text" placeholder="e.g., 192.168.1.100">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-section">
                    <legend><i class="fas fa-cog"></i> Settings</legend>
                    <div class="form-grid">
                        <div class="field checkbox-field">
                            <label>
                                <input id="create-managed" name="isManaged" type="checkbox" checked>
                                Managed Device
                            </label>
                            <span class="field-hint">Include in managed device reports</span>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-section">
                    <legend><i class="fas fa-sticky-note"></i> Notes</legend>
                    <div class="field full-width">
                        <label for="create-note">Note</label>
                        <textarea id="create-note" name="note" rows="3" placeholder="Additional notes about this device..."></textarea>
                    </div>
                </fieldset>

                <footer class="device-form-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close="device-create-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Device
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

                <fieldset class="form-section">
                    <legend><i class="fas fa-info-circle"></i> Device Info</legend>
                    <div class="device-info-banner">
                        <div class="device-info-item">
                            <span class="device-info-label">Serial</span>
                            <span id="update-device-serial" class="device-info-value">-</span>
                        </div>
                        <div class="device-info-item">
                            <span class="device-info-label">Device ID</span>
                            <span id="update-device-label" class="device-info-value">-</span>
                        </div>
                        <div class="device-info-item">
                            <span class="device-info-label">Current Brand/Model</span>
                            <span id="update-device-current" class="device-info-value">-</span>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-section">
                    <legend><i class="fas fa-exchange-alt"></i> Change Brand/Model</legend>
                    <div class="form-grid">
                        <div class="field">
                            <label for="update-brand">New Brand</label>
                            <input id="update-brand" name="newProductBrand" type="text" placeholder="Leave blank to keep current">
                        </div>
                        <div class="field">
                            <label for="update-model">New Model</label>
                            <input id="update-model" name="newProductModel" type="text" placeholder="Leave blank to keep current">
                        </div>
                        <div class="field">
                            <label for="update-standard-model-id">Standard Model ID</label>
                            <input id="update-standard-model-id" name="newStandardModelId" type="text" placeholder="MPS standard model ID">
                        </div>
                        <div class="field">
                            <label for="update-standard-model-clean">Standard Model Clean</label>
                            <input id="update-standard-model-clean" name="newStandardModelClean" type="text" placeholder="Cleaned model name">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-section">
                    <legend><i class="fas fa-folder"></i> Assignment</legend>
                    <div class="form-grid">
                        <div class="field">
                            <label for="update-project">Project ID</label>
                            <input id="update-project" name="projectId" type="text" placeholder="Assign to project">
                        </div>
                        <div class="field">
                            <label for="update-office">Office ID</label>
                            <input id="update-office" name="officeId" type="text" placeholder="Assign to office">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-section">
                    <legend><i class="fas fa-sliders-h"></i> Additional Fields (choose to edit)</legend>
                    <div class="form-grid checkbox-grid">
                        <div class="field checkbox-field">
                            <label>
                                <input type="checkbox" id="toggle-asset-number" data-toggle-field="assetNumber">
                                <span class="checkbox-label">
                                    <strong>Asset Tag</strong>
                                    <span class="field-hint">Internal asset or inventory number</span>
                                </span>
                            </label>
                        </div>
                        <div class="field checkbox-field">
                            <label>
                                <input type="checkbox" id="toggle-contact" data-toggle-field="contact">
                                <span class="checkbox-label">
                                    <strong>Contact</strong>
                                    <span class="field-hint">On-site point of contact</span>
                                </span>
                            </label>
                        </div>
                        <div class="field checkbox-field">
                            <label>
                                <input type="checkbox" id="toggle-department" data-toggle-field="department">
                                <span class="checkbox-label">
                                    <strong>Department / Location</strong>
                                    <span class="field-hint">Where the device physically lives</span>
                                </span>
                            </label>
                        </div>
                        <div class="field checkbox-field">
                            <label>
                                <input type="checkbox" id="toggle-ip-address" data-toggle-field="ipAddress">
                                <span class="checkbox-label">
                                    <strong>IP Address</strong>
                                    <span class="field-hint">Static or DHCP address</span>
                                </span>
                            </label>
                        </div>
                        <div class="field checkbox-field">
                            <label>
                                <input type="checkbox" id="toggle-note" data-toggle-field="note">
                                <span class="checkbox-label">
                                    <strong>Note</strong>
                                    <span class="field-hint">Operational notes or comments</span>
                                </span>
                            </label>
                        </div>
                        <div class="field checkbox-field">
                            <label>
                                <input type="checkbox" id="toggle-product-description" data-toggle-field="productDescription">
                                <span class="checkbox-label">
                                    <strong>Product Description</strong>
                                    <span class="field-hint">Friendly description shown in reports</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-section">
                    <legend><i class="fas fa-pen-square"></i> Optional Field Values</legend>
                    <div class="form-grid">
                        <div class="field full-width" data-field="assetNumber" hidden>
                            <label for="update-asset-number">Asset Tag</label>
                            <input id="update-asset-number" name="assetNumber" type="text" placeholder="e.g., IT-PRN-0042">
                            <span class="field-hint">Leave blank while checked to clear the value.</span>
                        </div>
                        <div class="field full-width" data-field="contact" hidden>
                            <label for="update-contact">Contact</label>
                            <input id="update-contact" name="contact" type="text" placeholder="Name or team">
                            <span class="field-hint">Will be sent when the Contact box is checked.</span>
                        </div>
                        <div class="field full-width" data-field="department" hidden>
                            <label for="update-department">Department / Location</label>
                            <input id="update-department" name="department" type="text" placeholder="e.g., Finance, Building A">
                        </div>
                        <div class="field full-width" data-field="ipAddress" hidden>
                            <label for="update-ip-address">IP Address</label>
                            <input id="update-ip-address" name="ipAddress" type="text" placeholder="e.g., 192.168.1.120">
                        </div>
                        <div class="field full-width" data-field="note" hidden>
                            <label for="update-note">Note</label>
                            <textarea id="update-note" name="note" rows="3" placeholder="Usage notes, maintenance instructions..."></textarea>
                        </div>
                        <div class="field full-width" data-field="productDescription" hidden>
                            <label for="update-product-description">Product Description</label>
                            <input id="update-product-description" name="productDescription" type="text" placeholder="Friendly description for reports">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-section">
                    <legend><i class="fas fa-cog"></i> Settings</legend>
                    <div class="form-grid checkbox-grid">
                        <div class="field checkbox-field">
                            <label>
                                <input id="update-managed" name="isManaged" type="checkbox">
                                <span class="checkbox-label">
                                    <strong>Managed Device</strong>
                                    <span class="field-hint">Include in managed device reports and billing</span>
                                </span>
                            </label>
                        </div>
                        <div class="field checkbox-field">
                            <label>
                                <input id="update-alerts" name="isGenerateAlert" type="checkbox">
                                <span class="checkbox-label">
                                    <strong>Generate Alerts</strong>
                                    <span class="field-hint">Create alerts when issues are detected</span>
                                </span>
                            </label>
                        </div>
                        <div class="field checkbox-field">
                            <label>
                                <input id="update-repeat-alerts" name="manageRepeatedAlerts" type="checkbox">
                                <span class="checkbox-label">
                                    <strong>Manage Repeated Alerts</strong>
                                    <span class="field-hint">Aggregate repeated alerts to reduce noise</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </fieldset>

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

<?php
/*
CHANGELOG
2025-11-25 Codex
- Added lifecycle filter prefills from query parameters to speed duplicate-IP remediation links.
2025-11-29 Codex
- Added dynamic optional-field controls for IP, department/location, asset, contact, note, and product description in the update modal.
*/
?>
