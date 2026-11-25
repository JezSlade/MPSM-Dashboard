<?php
/**
 * Pure PHP coverage for current-only SupplyAlert/List request filters.
 */

require_once dirname(__DIR__, 2) . '/cms/api/supply-alert-filters.php';

$base = ['D1', null, 'Toner', 1, 100, 'InitialDate', 'Desc'];
$all = buildSupplyAlertListParams(...[...$base, false]);
$current = buildSupplyAlertListParams(...[...$base, true]);

$expected = [
    'ManageOption' => 'All',
    'InstallationOption' => 'NotInstalled',
    'CancelOption' => 'NotCanceled',
    'HiddenOption' => 'NotHidden',
    'ExcludeForStockShippedSupplies' => true,
];

foreach ($expected as $key => $value) {
    if (($current[$key] ?? null) !== $value) {
        fwrite(STDERR, "Current-only filter failed for {$key}.\n");
        exit(1);
    }
}

foreach (array_keys($expected) as $key) {
    if ($key !== 'ManageOption' && array_key_exists($key, $all)) {
        fwrite(STDERR, "Default request unexpectedly filters {$key}.\n");
        exit(1);
    }
}

if ($all['ManageOption'] !== null || $current['SupplyType'] !== 'Toner') {
    fwrite(STDERR, "Base supply-alert parameters changed unexpectedly.\n");
    exit(1);
}

$active = ['SupplyType' => 'Toner', 'ActualResidualPercentage' => 25];
$cases = [
    [['SupplyType' => 'Toner', 'ActualResidualPercentage' => 0], true, 'empty toner'],
    [$active, true, 'low toner boundary'],
    [['SupplyType' => 'Toner', 'ActualResidualPercentage' => 26], false, 'OK toner level'],
    [$active + ['CanceledOn' => '2026-11-25T10:00:00Z'], false, 'canceled alert'],
    [$active + ['TonerInstalled' => '2026-11-25T10:00:00Z'], false, 'installed toner'],
    [$active + ['IsHidden' => true], false, 'hidden alert'],
    [['SupplyType' => 'PhotoConductor', 'ActualResidualPercentage' => 5], false, 'non-toner supply'],
    [['SupplyType' => 'Toner'], false, 'unknown current level'],
];

foreach ($cases as [$alert, $expectedResult, $label]) {
    if (isCurrentTonerAlert($alert) !== $expectedResult) {
        fwrite(STDERR, "Current toner predicate failed for {$label}.\n");
        exit(1);
    }
}

$history = [
    ['Code' => '1104', 'Description' => 'Cyan toner almost empty', 'Opened' => '2026-03-10T08:00:00Z', 'Closed' => null],
    ['Code' => '1101', 'Description' => 'Cyan toner empty', 'Opened' => '2026-03-20T08:00:00Z', 'Closed' => null],
    ['Code' => '1104', 'Description' => 'Black toner almost empty', 'Opened' => '2025-12-01T08:00:00Z', 'Closed' => null],
    ['Code' => '1104', 'Description' => 'Yellow toner almost empty', 'Opened' => '2025-11-01T08:00:00Z', 'Closed' => '2025-11-05T08:00:00Z'],
    ['Code' => '1104', 'Description' => 'Marker alert', 'PanelMessageAlertConfiguration' => ['Description' => 'Magenta toner almost empty'], 'Opened' => '2026-04-01T08:00:00Z', 'Closed' => null],
];

if (tonerMaintenanceOpenedDate($history, 'Cyan') !== '2026-03-10T08:00:00Z') {
    fwrite(STDERR, "Cyan alert should use the first active low/empty event.\n");
    exit(1);
}
if (tonerMaintenanceOpenedDate($history, 'Black1') !== '2025-12-01T08:00:00Z') {
    fwrite(STDERR, "Black variants should match the black maintenance event.\n");
    exit(1);
}
if (tonerMaintenanceOpenedDate($history, 'Yellow') !== null) {
    fwrite(STDERR, "Closed toner events must not supply Days Open.\n");
    exit(1);
}
if (tonerMaintenanceOpenedDate($history, 'Magenta') !== '2026-04-01T08:00:00Z') {
    fwrite(STDERR, "Nested panel configuration descriptions must identify toner color.\n");
    exit(1);
}

$sortable = [
    ['SerialNumber' => 'NO-DATE', 'AlertOpenedDate' => null],
    ['SerialNumber' => 'NEW', 'AlertOpenedDate' => '2026-03-20T08:00:00Z'],
    ['SerialNumber' => 'OLD', 'AlertOpenedDate' => '2026-03-10T08:00:00Z'],
];
sortTonerAlertsByOpenedDate($sortable);
if (array_column($sortable, 'SerialNumber') !== ['OLD', 'NEW', 'NO-DATE']) {
    fwrite(STDERR, "Toner alerts must sort by active event date with missing dates last.\n");
    exit(1);
}

$panelHistory = [
    ['maintenance_alert_code' => '1104', 'received_at' => '2025-12-01 08:00:00', 'panel_configuration' => 'Black toner almost empty', 'payload' => '{}'],
    ['maintenance_alert_code' => '1101', 'received_at' => '2026-03-20 08:00:00', 'panel_configuration' => 'Cyan toner empty', 'payload' => '{}'],
    ['maintenance_alert_code' => '1104', 'received_at' => '2026-03-10 08:00:00', 'panel_configuration' => 'Cyan toner almost empty', 'payload' => '{}'],
];
if (tonerPanelEventOpenedDate($panelHistory, 'Cyan') !== '2026-03-20 08:00:00') {
    fwrite(STDERR, "Panel history should use the latest matching low/empty event.\n");
    exit(1);
}
if (tonerPanelEventOpenedDate($panelHistory, 'Black') !== '2025-12-01 08:00:00') {
    fwrite(STDERR, "Panel history must remain color-specific.\n");
    exit(1);
}

echo "Supply alert current-only filter tests passed.\n";

/*
CHANGELOG
2026-11-25 Codex
- Added coverage for current-only lifecycle filters and unchanged default behavior.
- Added low/empty boundary and completed-record rejection cases.
- Added color-matched maintenance-event date and actual-date sorting coverage.
- Added batched panel-history event selection coverage.
*/
