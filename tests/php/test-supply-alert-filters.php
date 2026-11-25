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

echo "Supply alert current-only filter tests passed.\n";

/*
CHANGELOG
2026-11-25 Codex
- Added coverage for current-only lifecycle filters and unchanged default behavior.
- Added low/empty boundary and completed-record rejection cases.
*/
