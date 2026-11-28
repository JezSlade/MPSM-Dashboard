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
        $codes = [2 => 'black', 3 => 'cyan', 4 => 'magenta', 5 => 'yellow'];
        if (isset($codes[$normalized])) {
            return $codes[$normalized];
        }
        if (preg_match('/^black\d*$/', $normalized)) {
            return 'black';
        }
        return in_array($normalized, ['cyan', 'magenta', 'yellow', 'white'], true) ? $normalized : '';
    }
}

function tonerAlertDate($value): ?string
{
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}(?:[T ]|$)/', $value)
        || str_starts_with($value, '0001-01-01') || strtotime($value) === false) {
        return null;
    }
    return $value;
}

function tonerHistoryLowRuns(array $points, float $threshold = 25): array
{
    $runs = [];
    $newestFirst = array_reverse($points);
    foreach (['black', 'cyan', 'magenta', 'yellow'] as $color) {
        $run = null;
        foreach ($newestFirst as $point) {
            $level = $point[$color] ?? null;
            if ($level === null) {
                continue;
            }
            $at = $point['at'];
            if ($run === null) {
                if ($level > $threshold) {
                    break;
                }
                $run = ['firstLowAt' => $at, 'lastLowAt' => $at, 'aboveBeforeAt' => null];
                continue;
            }
            if (strtotime($run['firstLowAt']) - strtotime($at) > 3 * 86400) {
                break;
            }
            if ($level > $threshold) {
                $run['aboveBeforeAt'] = $at;
                break;
            }
            $run['firstLowAt'] = $at;
        }
        if ($run !== null) {
            $runs[$color] = $run;
        }
    }
    return $runs;
}

function tonerAlertDisplayFields(array $alert): array
{
    $equipmentId = '';
    foreach (['AssetNumber', 'Asset', 'EquipmentId', 'ExternalIdentifier', 'ExternalId'] as $field) {
        $equipmentId = trim((string)($alert[$field] ?? ''));
        if ($equipmentId !== '') {
            break;
        }
    }

    $department = trim((string)($alert['Department'] ?? ''));
    $fromDepartment = false;
    if (preg_match('/^([A-Z]{2}-?\d{3})\s+S\s+\d{6}(?:\s+(.*))?$/i', $department, $matches)) {
        if ($equipmentId === '') {
            $equipmentId = strtoupper($matches[1]);
            $fromDepartment = true;
        }
        $department = trim($matches[2] ?? '');
    }

    $office = trim((string)($alert['Office']['Description'] ?? ''));
    $location = array_filter(
        [$office, $department],
        static fn(string $part): bool => $part !== '' && strcasecmp($part, 'DEFAULT') !== 0
    );
    $readingDateField = isset($alert['ActualResidualPercentage']) ? 'ActualDate' : 'InitialDate';
    $color = strtolower(trim((string)($alert['ColorType'] ?? $alert['SupplyTypeDescription'] ?? '')));
    $normalized = normalizeTonerColor($color);
    $displayColor = preg_match('/^black(\d+)$/', $color, $slot) ? 'black ' . $slot[1] : $normalized;

    return [
        'TonerColor' => $displayColor ?: null,
        'TonerEquipmentId' => $equipmentId !== '' ? $equipmentId : null,
        'TonerEquipmentIdFromDepartment' => $fromDepartment,
        'TonerLocation' => implode(' / ', array_unique($location)) ?: null,
        'TonerReadingDate' => tonerAlertDate($alert[$readingDateField] ?? null),
        'SupplyRecordOpenedDate' => tonerAlertDate($alert['InitialDate'] ?? null),
    ];
}

function groupTonerAlertsByDevice(array $alerts): array
{
    $deviceIdsBySerial = [];
    foreach ($alerts as $alert) {
        $customer = ($alert['CustomerCode'] ?? '') ?: ($alert['CustomerId'] ?? '');
        $serial = strtoupper(trim((string)($alert['SerialNumber'] ?? '')));
        $deviceId = trim((string)($alert['DeviceId'] ?? ''));
        if ($serial !== '' && $deviceId !== '') {
            $deviceIdsBySerial[$customer . "\0" . $serial][$deviceId] = true;
        }
    }

    $groups = [];
    foreach ($alerts as $index => $alert) {
        $customer = ($alert['CustomerCode'] ?? '') ?: ($alert['CustomerId'] ?? '');
        $deviceId = trim((string)($alert['DeviceId'] ?? ''));
        $serial = strtoupper(trim((string)($alert['SerialNumber'] ?? '')));
        $matches = $deviceIdsBySerial[$customer . "\0" . $serial] ?? [];
        if ($deviceId === '' && count($matches) === 1) {
            $deviceId = (string)array_key_first($matches);
        }
        $identity = $deviceId !== '' ? "id:$deviceId" : ($serial !== '' ? "serial:$serial" : "row:$index");
        $key = $customer . "\0" . $identity;

        if (!isset($groups[$key])) {
            $groups[$key] = $alert;
            $groups[$key]['DeviceId'] = $deviceId;
        }
        if (empty($groups[$key]['TonerEquipmentId']) || (empty($alert['TonerEquipmentIdFromDepartment'])
            && !empty($alert['TonerEquipmentId']) && !empty($groups[$key]['TonerEquipmentIdFromDepartment']))) {
            $groups[$key]['TonerEquipmentId'] = $alert['TonerEquipmentId'] ?? null;
            $groups[$key]['TonerEquipmentIdFromDepartment'] = $alert['TonerEquipmentIdFromDepartment'] ?? false;
        }
        $groups[$key]['TonerLocation'] = $groups[$key]['TonerLocation'] ?? $alert['TonerLocation'] ?? null;
        $groups[$key]['TonerAlerts'][] = [
            'TonerColor' => $alert['TonerColor'] ?? null,
            'Percentage' => $alert['ActualResidualPercentage'] ?? $alert['InitialResidualPercentage'] ?? null,
            'TonerReadingDate' => $alert['TonerReadingDate'] ?? null,
        ];
    }

    return array_values($groups);
}

if (!function_exists('tonerMaintenanceOpenedDate')) {
    function tonerMaintenanceOpenedDate(array $alerts, $color = '', bool $requireExactColor = false): ?string
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

            $opened = tonerAlertDate($alert['Opened'] ?? null);
            if ($opened === null) {
                continue;
            }
            $timestamp = strtotime($opened);
            if ($requireExactColor && (!preg_match('/(?:Z|[+-]\d{2}:\d{2})$/i', $opened) || $timestamp > time())) {
                continue;
            }

            preg_match_all('/\b(cyan|magenta|yellow|black|white)\b/', $description, $matches);
            $colors = array_values(array_unique($matches[0]));
            $describedColor = $colors[0] ?? '';
            if ($requireExactColor && (count($colors) !== 1 || $describedColor !== $targetColor)) {
                continue;
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

function sortTonerAlertsByReadingDate(array &$alerts): void
{
    usort($alerts, static function (array $a, array $b): int {
        $aTime = strtotime($a['TonerReadingDate'] ?? '') ?: PHP_INT_MIN;
        $bTime = strtotime($b['TonerReadingDate'] ?? '') ?: PHP_INT_MIN;
        return ($bTime <=> $aTime) ?: strcmp((string)($a['SerialNumber'] ?? ''), (string)($b['SerialNumber'] ?? ''));
    });
}

if (!function_exists('sortTonerAlertsByOpenedDate')) {
    function sortTonerAlertsByOpenedDate(array &$alerts): void
    {
        usort($alerts, static function (array $a, array $b): int {
            $aDate = tonerAlertDate($a['AlertOpenedDate'] ?? null) ?? ($a['SupplyRecordOpenedDate'] ?? null);
            $bDate = tonerAlertDate($b['AlertOpenedDate'] ?? null) ?? ($b['SupplyRecordOpenedDate'] ?? null);
            $aTime = $aDate === null ? false : strtotime($aDate);
            $bTime = $bDate === null ? false : strtotime($bDate);
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
2026-11-27 Codex
- Normalize vendor toner color codes and derive scoped display fields from current alerts.
- Use the supply record date for fallback ordering without changing the panel event date.
- Group current toner alerts by customer and device while retaining each cartridge reading.
2026-11-28 Codex
- Require an active, unambiguous color match and a timezone-qualified opening date when verifying age.
- Sort the device list by recent toner readings when the list lacks verified opening dates.
- Derive the latest observed low-toner run and its crossing bounds from normalized history.
*/
