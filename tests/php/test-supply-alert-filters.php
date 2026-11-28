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

$unverifiedHistory = [
    ['Code' => '1104', 'Description' => 'Low toner', 'Opened' => '2023-12-20T10:00:00Z'],
    ['Code' => '1104', 'Description' => 'Black and cyan toner low', 'Opened' => '2023-12-20T10:00:00Z'],
    ['Code' => '1104', 'Description' => 'Black toner low', 'Opened' => '2023-12-20 10:00:00'],
    ['Code' => '1104', 'Description' => 'Black toner low', 'Opened' => '2999-12-20T10:00:00Z'],
    ['Code' => '1104', 'Description' => 'Black toner low', 'Opened' => '2023-12-20T10:00:00Z', 'Closed' => '2023-12-21T10:00:00Z'],
];
if (tonerMaintenanceOpenedDate($unverifiedHistory, 'black', true) !== null
    || tonerMaintenanceOpenedDate(array_merge($unverifiedHistory, $history), 'black', true) !== '2025-12-01T08:00:00Z') {
    fwrite(STDERR, "Verified opening dates must be active, unambiguous, and timezone-qualified.\n");
    exit(1);
}

$datedReadings = [
    ['SerialNumber' => 'A', 'SupplyRecordOpenedDate' => '2023-12-20T10:00:00Z'],
    ['SerialNumber' => 'B', 'TonerReadingDate' => '2026-11-24T08:00:00Z'],
    ['SerialNumber' => 'C', 'TonerReadingDate' => '2026-11-25T08:00:00Z'],
];
sortTonerAlertsByReadingDate($datedReadings);
if (array_column($datedReadings, 'SerialNumber') !== ['C', 'B', 'A']) {
    fwrite(STDERR, "The grouped device list must sort by reading timestamps.\n");
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

$initialDate = '2026-03-10T08:00:00Z';
$actualDate = '2026-03-25T08:00:00Z';
$display = tonerAlertDisplayFields([
    'ColorType' => '4',
    'Department' => 'EB409 S 052015 Service Room',
    'Office' => ['Description' => 'DEFAULT'],
    'ActualResidualPercentage' => 0,
    'ActualDate' => $actualDate,
    'InitialDate' => $initialDate,
]);
if ($display !== [
    'TonerColor' => 'magenta',
    'TonerEquipmentId' => 'EB409',
    'TonerEquipmentIdFromDepartment' => true,
    'TonerLocation' => 'Service Room',
    'TonerReadingDate' => $actualDate,
    'SupplyRecordOpenedDate' => $initialDate,
]) {
    fwrite(STDERR, "Current alert metadata must retain the source of the zero-percent reading and tagged location.\n");
    exit(1);
}

$display = tonerAlertDisplayFields([
    'ColorType' => 'Black2',
    'AssetNumber' => ' ',
    'ExternalIdentifier' => 'A123',
    'Department' => 'EB409 S 052015',
    'Office' => ['Description' => 'default'],
    'InitialResidualPercentage' => 5,
    'ActualDate' => $actualDate,
    'InitialDate' => $initialDate,
]);
if ($display['TonerColor'] !== 'black 2' || $display['TonerEquipmentId'] !== 'A123'
    || $display['TonerEquipmentIdFromDepartment'] || $display['TonerLocation'] !== null
    || $display['TonerReadingDate'] !== $initialDate
    || tonerAlertDate('0001-01-01T00:00:00Z') !== null
    || tonerPanelEventOpenedDate($panelHistory, '3') !== '2026-03-20 08:00:00'
    || array_map('normalizeTonerColor', ['2', '3', '4', '5']) !== ['black', 'cyan', 'magenta', 'yellow']) {
    fwrite(STDERR, "Color slots, structured asset IDs, date provenance and vendor colors must remain accurate.\n");
    exit(1);
}

$sameDevice = ['CustomerCode' => 'A', 'SerialNumber' => 'S1'];
$toGroup = [
    $sameDevice + ['AlertOpenedDate' => '2026-03-18T08:00:00Z', 'TonerColor' => 'cyan', 'ActualResidualPercentage' => 0],
    $sameDevice + ['DeviceId' => 'one', 'SupplyRecordOpenedDate' => $initialDate, 'TonerColor' => 'magenta', 'InitialResidualPercentage' => 7],
    ['CustomerCode' => 'A', 'SerialNumber' => 'S2', 'DeviceId' => 'two'],
    ['CustomerCode' => 'A', 'SerialNumber' => 'S2', 'DeviceId' => 'three'],
    ['CustomerCode' => 'A', 'SerialNumber' => 'S2', 'TonerColor' => 'black'],
    ['CustomerCode' => 'B', 'SerialNumber' => 'S1', 'DeviceId' => 'one'],
    ['CustomerCode' => 'A', 'TonerColor' => 'yellow'],
    ['CustomerCode' => 'A', 'TonerColor' => 'cyan'],
];
sortTonerAlertsByOpenedDate($toGroup);
$groups = groupTonerAlertsByDevice($toGroup);
if (count($groups) !== 7
    || $groups[0]['DeviceId'] !== 'one'
    || ($groups[0]['SupplyRecordOpenedDate'] ?? null) !== $initialDate
    || array_column($groups[0]['TonerAlerts'], 'TonerColor') !== ['magenta', 'cyan']
    || array_column($groups[0]['TonerAlerts'], 'Percentage') !== [7, 0]) {
    fwrite(STDERR, "Group only alerts with the same device identity and preserve age order and every toner reading.\n");
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
2026-11-27 Codex
- Cover vendor color codes, equipment tags, reading and supply-record dates, and device grouping.
2026-11-28 Codex
- Cover strict opening-date evidence and separate reading-time ordering.
*/
