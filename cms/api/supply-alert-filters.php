<?php
/**
 * Builds SupplyAlert/List requests without mixing current alerts with completed lifecycle records.
 */

if (!function_exists('buildSupplyAlertListParams')) {
    function buildSupplyAlertListParams(
        string $dealerCode,
        ?string $customerCode,
        ?string $supplyType,
        int $pageNumber,
        int $pageRows,
        string $sortColumn,
        string $sortOrder,
        bool $currentOnly = false
    ): array {
        $params = [
            'DealerCode' => $dealerCode,
            'CustomerCode' => $customerCode,
            'SupplyType' => $supplyType,
            'ManageOption' => $currentOnly ? 'All' : null,
            'PageNumber' => $pageNumber,
            'PageRows' => $pageRows,
            'SortColumn' => $sortColumn,
            'SortOrder' => $sortOrder,
        ];

        if ($currentOnly) {
            $params['InstallationOption'] = 'NotInstalled';
            $params['CancelOption'] = 'NotCanceled';
            $params['HiddenOption'] = 'NotHidden';
            $params['ExcludeForStockShippedSupplies'] = true;
        }

        return $params;
    }
}

if (!function_exists('isCurrentTonerAlert')) {
    function isCurrentTonerAlert(array $alert, float $maximumLevel = 25): bool
    {
        $supplyLabels = [
            $alert['SupplyType'] ?? '',
            $alert['SupplyTypeDescription'] ?? '',
            $alert['SupplyTypeDescriptionTranslated'] ?? '',
        ];
        $isToner = false;
        foreach ($supplyLabels as $label) {
            if (stripos((string)$label, 'toner') !== false) {
                $isToner = true;
                break;
            }
        }
        if (!$isToner || filter_var($alert['IsHidden'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        foreach (['CanceledOn', 'TonerInstalled'] as $field) {
            $value = trim((string)($alert[$field] ?? ''));
            if ($value !== '' && strpos($value, '0001-01-01') !== 0) {
                return false;
            }
        }

        $level = $alert['ActualResidualPercentage'] ?? $alert['InitialResidualPercentage'] ?? null;
        return is_numeric($level) && (float)$level >= 0 && (float)$level <= $maximumLevel;
    }
}

/*
CHANGELOG
2026-11-25 Codex
- Added explicit upstream lifecycle filters for current, actionable supply alerts.
- Added a strict current toner-level predicate that excludes OK, installed, canceled, hidden, and non-toner records.
*/
