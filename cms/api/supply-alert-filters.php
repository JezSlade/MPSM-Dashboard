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

if (!function_exists('supplyAlertResponseRows')) {
    function supplyAlertResponseRows(array $response): array
    {
        $raw = $response['data'] ?? [];
        if (isset($raw['Items']) && is_array($raw['Items'])) {
            return $raw['Items'];
        }
        if (isset($raw['Result']) && is_array($raw['Result'])) {
            return $raw['Result'];
        }
        return is_array($raw) && array_values($raw) === $raw ? $raw : [];
    }
}

if (!function_exists('normalizeTonerColor')) {
    function normalizeTonerColor($color): string
    {
        $normalized = strtolower(trim((string)$color));
        if (preg_match('/^black\d*$/', $normalized)) {
            return 'black';
        }
        return in_array($normalized, ['cyan', 'magenta', 'yellow', 'white'], true) ? $normalized : '';
    }
}

if (!function_exists('tonerMaintenanceOpenedDate')) {
    function tonerMaintenanceOpenedDate(array $alerts, $color = ''): ?string
    {
        $targetColor = normalizeTonerColor($color);
        $exact = [];
        $generic = [];

        foreach ($alerts as $alert) {
            if (!is_array($alert)) {
                continue;
            }

            $closed = trim((string)($alert['Closed'] ?? ''));
            if ($closed !== '' && strpos($closed, '0001-01-01') !== 0) {
                continue;
            }

            $code = (string)($alert['Code'] ?? '');
            $configuration = is_array($alert['PanelMessageAlertConfiguration'] ?? null)
                ? $alert['PanelMessageAlertConfiguration']
                : [];
            $description = strtolower(trim(
                (string)($alert['Description'] ?? '') . ' '
                . (string)($configuration['Description'] ?? '')
            ));
            $isTonerEvent = preg_match('/(^|\D)(1101|1104)(\D|$)/', $code)
                || (strpos($description, 'toner') !== false
                    && preg_match('/\b(low|empty|depleted|exhausted|almost empty)\b/', $description));
            if (!$isTonerEvent) {
                continue;
            }

            $opened = trim((string)($alert['Opened'] ?? ''));
            $timestamp = $opened !== '' ? strtotime($opened) : false;
            if ($timestamp === false) {
                continue;
            }

            $describedColor = '';
            foreach (['cyan', 'magenta', 'yellow', 'black', 'white'] as $candidate) {
                if (preg_match('/\b' . preg_quote($candidate, '/') . '\b/', $description)) {
                    $describedColor = $candidate;
                    break;
                }
            }

            if ($targetColor !== '' && $describedColor !== '' && $describedColor !== $targetColor) {
                continue;
            }

            $entry = ['timestamp' => $timestamp, 'date' => $opened];
            if ($targetColor !== '' && $describedColor === $targetColor) {
                $exact[] = $entry;
            } else {
                $generic[] = $entry;
            }
        }

        $candidates = $exact ?: $generic;
        if (!$candidates) {
            return null;
        }
        usort($candidates, static fn(array $a, array $b): int => $a['timestamp'] <=> $b['timestamp']);
        return $candidates[0]['date'];
    }
}

if (!function_exists('sortTonerAlertsByOpenedDate')) {
    function sortTonerAlertsByOpenedDate(array &$alerts): void
    {
        usort($alerts, static function (array $a, array $b): int {
            $aTime = !empty($a['AlertOpenedDate']) ? strtotime((string)$a['AlertOpenedDate']) : false;
            $bTime = !empty($b['AlertOpenedDate']) ? strtotime((string)$b['AlertOpenedDate']) : false;
            if ($aTime === false && $bTime === false) {
                return strcmp((string)($a['SerialNumber'] ?? ''), (string)($b['SerialNumber'] ?? ''));
            }
            if ($aTime === false) {
                return 1;
            }
            if ($bTime === false) {
                return -1;
            }
            return $aTime <=> $bTime;
        });
    }
}

if (!function_exists('tonerPanelEventOpenedDate')) {
    function tonerPanelEventOpenedDate(array $rows, $color = ''): ?string
    {
        $targetColor = normalizeTonerColor($color);
        $exact = [];
        $generic = [];

        foreach ($rows as $row) {
            if (!is_array($row) || !preg_match('/(^|\D)(1101|1104)(\D|$)/', (string)($row['maintenance_alert_code'] ?? ''))) {
                continue;
            }
            $received = trim((string)($row['received_at'] ?? ''));
            $timestamp = $received !== '' ? strtotime($received) : false;
            if ($timestamp === false) {
                continue;
            }

            $payload = $row['payload'] ?? '';
            if (is_string($payload)) {
                $decoded = json_decode($payload, true);
                $payload = is_array($decoded) ? $decoded : $payload;
            }
            $searchText = strtolower(
                (string)($row['panel_configuration'] ?? '') . ' '
                . (is_array($payload) ? json_encode($payload) : (string)$payload)
            );

            $describedColor = '';
            foreach (['cyan', 'magenta', 'yellow', 'black', 'white'] as $candidate) {
                if (preg_match('/\b' . preg_quote($candidate, '/') . '\b/', $searchText)) {
                    $describedColor = $candidate;
                    break;
                }
            }
            if ($targetColor !== '' && $describedColor !== '' && $describedColor !== $targetColor) {
                continue;
            }

            $entry = ['timestamp' => $timestamp, 'date' => $received];
            if ($targetColor !== '' && $describedColor === $targetColor) {
                $exact[] = $entry;
            } else {
                $generic[] = $entry;
            }
        }

        $candidates = $exact ?: $generic;
        if (!$candidates) {
            return null;
        }
        usort($candidates, static fn(array $a, array $b): int => $b['timestamp'] <=> $a['timestamp']);
        return $candidates[0]['date'];
    }
}

/*
CHANGELOG
2026-11-25 Codex
- Added explicit upstream lifecycle filters for current, actionable supply alerts.
- Added a strict current toner-level predicate that excludes OK, installed, canceled, hidden, and non-toner records.
- Added active maintenance-history date matching and oldest-first event sorting.
- Added a fast panel-history event resolver for batched serial lookups.
*/
