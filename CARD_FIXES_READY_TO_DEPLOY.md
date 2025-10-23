# Dashboard Card Fixes - Ready to Deploy

## Status
- ✅ table-utils.js added to index.php
- ✅ Infrastructure complete
- 🔄 Card functions need updates

## Card Fix Implementation (Copy & Paste)

### 1. TONER LEVELS CARD (loadTonerLevels function)

Replace the existing `loadTonerLevels()` function in app.js with:

```javascript
async function loadTonerLevels() {
    const container = document.getElementById('toner-list');
    container.innerHTML = '<div class="loading">Loading toner status...</div>';

    try {
        const devices = state.devices;

        // Extract devices with toner data
        const tonerData = devices.filter(d =>
            d.BlackToner !== null || d.CyanToner !== null ||
            d.MagentaToner !== null || d.YellowToner !== null
        ).map(d => ({
            id: d.Id,
            identifier: d.ExternalIdentifier || d.AssetNumber || 'Unknown',
            black: d.BlackToner,
            cyan: d.CyanToner,
            magenta: d.MagentaToner,
            yellow: d.YellowToner,
            lowCount: [d.BlackToner, d.CyanToner, d.MagentaToner, d.YellowToner]
                .filter(t => t !== null && t < 20).length
        }));

        // Summary stats
        const lowToner = tonerData.filter(d => d.lowCount > 0).length;
        const critical = tonerData.filter(d =>
            [d.black, d.cyan, d.magenta, d.yellow].some(t => t !== null && t < 10)
        ).length;

        // Snapshot HTML
        const snapshotHtml = `
            <div class="snapshot-grid">
                <div class="snapshot-item">
                    <div class="snapshot-value">${tonerData.length}</div>
                    <div class="snapshot-label">Total Devices</div>
                </div>
                <div class="snapshot-item">
                    <div class="snapshot-value" style="color: #ffc107;">${lowToner}</div>
                    <div class="snapshot-label">Low Toner (&lt;20%)</div>
                </div>
                <div class="snapshot-item">
                    <div class="snapshot-value" style="color: #dc3545;">${critical}</div>
                    <div class="snapshot-label">Critical (&lt;10%)</div>
                </div>
            </div>
        `;

        // Table columns
        const columns = [
            {key: 'identifier', label: 'Device'},
            {key: 'black', label: 'Black', render: TableUtils.renderTonerLevel},
            {key: 'cyan', label: 'Cyan', render: TableUtils.renderTonerLevel},
            {key: 'magenta', label: 'Magenta', render: TableUtils.renderTonerLevel},
            {key: 'yellow', label: 'Yellow', render: TableUtils.renderTonerLevel}
        ];

        const table = TableUtils.createPaginatedTable(tonerData, columns, {
            pageSize: 25,
            className: 'toner-table'
        });

        container.innerHTML = TableUtils.createExpandableCard(
            'Toner Levels',
            snapshotHtml,
            table.html
        );

        TableUtils.setupExpandable(container);

        const detailsSection = container.querySelector('.card-details');
        if (detailsSection) {
            table.setup(detailsSection, {
                onRowClick: (device) => openDeviceModal(device.id)
            });
        }

    } catch (error) {
        console.error('Failed to load toner levels:', error);
        container.innerHTML = '<div class="empty-state">Failed to load toner data</div>';
    }
}
```

### 2. METER READS CARD (loadMeterReads function)

Replace `loadMeterReads()` with:

```javascript
async function loadMeterReads() {
    const container = document.getElementById('meter-list');
    container.innerHTML = '<div class="loading">Loading meter data...</div>';

    try {
        const devices = state.devices;

        // Extract counter data
        const meterData = devices.map(d => ({
            id: d.Id,
            identifier: d.ExternalIdentifier || d.AssetNumber || 'Unknown',
            monoCounter: d.CounterMono || 0,
            colorCounter: d.CounterColor || 0,
            monoMonthly: d.MonthlyMonoVolume || 0,
            colorMonthly: d.MonthlyColorVolume || 0,
            totalMonthly: (d.MonthlyMonoVolume || 0) + (d.MonthlyColorVolume || 0)
        })).sort((a, b) => b.totalMonthly - a.totalMonthly);

        // Summary stats
        const totalMono = meterData.reduce((sum, d) => sum + d.monoMonthly, 0);
        const totalColor = meterData.reduce((sum, d) => sum + d.colorMonthly, 0);
        const top3 = meterData.slice(0, 3);

        const snapshotHtml = `
            <div class="snapshot-grid">
                <div class="snapshot-item">
                    <div class="snapshot-value">${totalMono.toLocaleString()}</div>
                    <div class="snapshot-label">Mono Pages (Month)</div>
                </div>
                <div class="snapshot-item">
                    <div class="snapshot-value">${totalColor.toLocaleString()}</div>
                    <div class="snapshot-label">Color Pages (Month)</div>
                </div>
                <div class="snapshot-item">
                    <div class="snapshot-value">${(totalMono + totalColor).toLocaleString()}</div>
                    <div class="snapshot-label">Total Pages</div>
                </div>
            </div>
            <div style="margin-top: 1rem;">
                <strong>Top 3 Devices:</strong>
                <ul style="margin: 0.5rem 0; padding-left: 1.5rem;">
                    ${top3.map(d => `
                        <li>${d.identifier}: ${d.totalMonthly.toLocaleString()} pages/month</li>
                    `).join('')}
                </ul>
            </div>
        `;

        const columns = [
            {key: 'identifier', label: 'Device'},
            {key: 'monoCounter', label: 'Mono Total', render: TableUtils.renderCounter},
            {key: 'colorCounter', label: 'Color Total', render: TableUtils.renderCounter},
            {key: 'monoMonthly', label: 'Mono/Month', render: TableUtils.renderCounter},
            {key: 'colorMonthly', label: 'Color/Month', render: TableUtils.renderCounter}
        ];

        const table = TableUtils.createPaginatedTable(meterData, columns, {pageSize: 25});

        container.innerHTML = TableUtils.createExpandableCard(
            'Meter Reads',
            snapshotHtml,
            table.html
        );

        TableUtils.setupExpandable(container);

        const detailsSection = container.querySelector('.card-details');
        if (detailsSection) {
            table.setup(detailsSection, {
                onRowClick: (device) => openDeviceModal(device.id)
            });
        }

    } catch (error) {
        console.error('Failed to load meters:', error);
        container.innerHTML = '<div class="empty-state">Failed to load meter data</div>';
    }
}
```

### 3. ERRORS & ALERTS CARD (loadErrorsAlerts function)

Replace `loadErrorsAlerts()` with:

```javascript
async function loadErrorsAlerts() {
    const container = document.getElementById('error-list');
    const countEl = document.getElementById('error-count');

    container.innerHTML = '<div class="loading">Loading alerts...</div>';

    try {
        const devices = state.devices;

        // Extract devices with issues
        const alerts = devices.filter(d =>
            d.IsOffline ||
            (d.BlackToner !== null && d.BlackToner < 20) ||
            (d.CyanToner !== null && d.CyanToner < 20) ||
            (d.MagentaToner !== null && d.MagentaToner < 20) ||
            (d.YellowToner !== null && d.YellowToner < 20)
        ).map(d => {
            const issues = [];
            if (d.IsOffline) issues.push('Offline');
            if (d.BlackToner !== null && d.BlackToner < 20) issues.push('Low Black Toner');
            if (d.CyanToner !== null && d.CyanToner < 20) issues.push('Low Cyan Toner');
            if (d.MagentaToner !== null && d.MagentaToner < 20) issues.push('Low Magenta Toner');
            if (d.YellowToner !== null && d.YellowToner < 20) issues.push('Low Yellow Toner');

            return {
                id: d.Id,
                identifier: d.ExternalIdentifier || d.AssetNumber || 'Unknown',
                issues: issues.join(', '),
                severity: d.IsOffline ? 'Critical' : 'Warning',
                lastUpdate: d.LastUpdate
            };
        });

        countEl.textContent = alerts.length;

        const critical = alerts.filter(a => a.severity === 'Critical').length;
        const warnings = alerts.filter(a => a.severity === 'Warning').length;

        const snapshotHtml = `
            <div class="snapshot-grid">
                <div class="snapshot-item">
                    <div class="snapshot-value">${alerts.length}</div>
                    <div class="snapshot-label">Total Alerts</div>
                </div>
                <div class="snapshot-item">
                    <div class="snapshot-value" style="color: #dc3545;">${critical}</div>
                    <div class="snapshot-label">Critical</div>
                </div>
                <div class="snapshot-item">
                    <div class="snapshot-value" style="color: #ffc107;">${warnings}</div>
                    <div class="snapshot-label">Warnings</div>
                </div>
            </div>
        `;

        const columns = [
            {key: 'identifier', label: 'Device'},
            {key: 'issues', label: 'Issues'},
            {key: 'severity', label: 'Severity', render: (v) => `<span class="status-badge ${v.toLowerCase()}">${v}</span>`},
            {key: 'lastUpdate', label: 'Last Update', render: TableUtils.renderDate}
        ];

        const table = TableUtils.createPaginatedTable(alerts, columns, {pageSize: 25});

        container.innerHTML = TableUtils.createExpandableCard(
            'Errors & Alerts',
            snapshotHtml,
            table.html
        );

        TableUtils.setupExpandable(container);

        const detailsSection = container.querySelector('.card-details');
        if (detailsSection) {
            table.setup(detailsSection, {
                onRowClick: (alert) => openDeviceModal(alert.id)
            });
        }

    } catch (error) {
        console.error('Failed to load alerts:', error);
        container.innerHTML = '<div class="empty-state">Failed to load alerts</div>';
        countEl.textContent = '0';
    }
}
```

### 4. RECENT ACTIVITY CARD (loadRecentActivity function)

Replace `loadRecentActivity()` with:

```javascript
async function loadRecentActivity() {
    const container = document.getElementById('activity-list');
    container.innerHTML = '<div class="loading">Loading activity...</div>';

    try {
        // Get recent device updates
        const devices = state.devices;
        const recentUpdates = devices
            .filter(d => d.LastUpdate)
            .map(d => ({
                id: d.Id,
                identifier: d.ExternalIdentifier || d.AssetNumber || 'Unknown',
                action: 'Counter Updated',
                timestamp: new Date(d.LastUpdate),
                details: `Mono: ${(d.CounterMono || 0).toLocaleString()}, Color: ${(d.CounterColor || 0).toLocaleString()}`
            }))
            .sort((a, b) => b.timestamp - a.timestamp)
            .slice(0, 50);

        const snapshotHtml = `
            <div style="max-height: 200px; overflow-y: auto;">
                <strong>Last 5 Activities:</strong>
                <ul style="margin: 0.5rem 0; padding-left: 1.5rem; list-style: none;">
                    ${recentUpdates.slice(0, 5).map(a => `
                        <li style="margin: 0.5rem 0; padding: 0.5rem; background: var(--bg-secondary); border-radius: 4px;">
                            <strong>${a.identifier}</strong> - ${a.action}
                            <br><small style="color: var(--text-secondary);">${a.timestamp.toLocaleString()}</small>
                        </li>
                    `).join('')}
                </ul>
            </div>
        `;

        const columns = [
            {key: 'identifier', label: 'Device'},
            {key: 'action', label: 'Action'},
            {key: 'details', label: 'Details'},
            {key: 'timestamp', label: 'Time', render: (v) => v.toLocaleString()}
        ];

        const table = TableUtils.createPaginatedTable(recentUpdates, columns, {pageSize: 25});

        container.innerHTML = TableUtils.createExpandableCard(
            'Recent Activity',
            snapshotHtml,
            table.html
        );

        TableUtils.setupExpandable(container);

        const detailsSection = container.querySelector('.card-details');
        if (detailsSection) {
            table.setup(detailsSection, {
                onRowClick: (activity) => openDeviceModal(activity.id)
            });
        }

    } catch (error) {
        console.error('Failed to load activity:', error);
        container.innerHTML = '<div class="empty-state">Failed to load activity</div>';
    }
}
```

## Deployment Instructions

1. Open `cms/assets/js/app.js`
2. Find each function listed above
3. Replace with the new implementation (copy entire function)
4. Save file
5. Commit and push:

```bash
cd /path/to/MPSM-Dashboard
git add cms/assets/js/app.js cms/index.php
git commit -m "Fix all dashboard cards with pagination and expandable sections"
git push
```

6. Hard refresh CMS (Ctrl+F5)

## What Each Card Now Has

✅ **Snapshot view** - Summary stats always visible
✅ **Expand button** - "Show Details" / "Hide Details"
✅ **Sortable table** - Click headers to sort
✅ **Pagination** - 25/50/100/All selector
✅ **Drill-down** - Click any row to see device details
✅ **Color coding** - Visual status indicators

## Testing Checklist

- [ ] Toner Levels card shows low toner count
- [ ] Toner table has color-coded percentages (red/yellow/green)
- [ ] Meter Reads shows monthly volume totals
- [ ] Meter table sorts by usage
- [ ] Errors & Alerts shows critical/warning counts
- [ ] Errors table shows severity badges
- [ ] Recent Activity shows last 5 updates
- [ ] Activity table shows all recent changes
- [ ] All cards have expand/collapse working
- [ ] All pagination controls work (25/50/100/All)
- [ ] Clicking rows opens device modal
- [ ] Clicking column headers sorts table

## Token Usage: 123K/200K (77K remaining)
