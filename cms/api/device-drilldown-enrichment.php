<?php
/**
 * Shared device drill-down enrichment and normalization helpers.
 *
 * The dashboard reads from cache first. These helpers are used only by targeted
 * refreshes and background enrichment so modal opens are not blocked by vendor
 * API fan-out.
 */

if (!function_exists('mpsm_dd_is_list_array')) {
    function mpsm_dd_is_list_array(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }
}

if (!function_exists('mpsm_dd_clean_params')) {
    function mpsm_dd_clean_params(array $params): array
    {
        $clean = [];

        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $nested = mpsm_dd_clean_params($value);
                if ($nested === []) {
                    continue;
                }
                $clean[$key] = $nested;
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }
}

if (!function_exists('mpsm_dd_device_id')) {
    function mpsm_dd_device_id(array $device, string $fallback = ''): string
    {
        foreach (['Id', 'id', 'IdInstalledProduct', 'DeviceId', 'deviceId'] as $key) {
            if (!empty($device[$key])) {
                return (string)$device[$key];
            }
        }

        return $fallback;
    }
}

if (!function_exists('mpsm_dd_device_serial')) {
    function mpsm_dd_device_serial(array $device, string $fallback = ''): string
    {
        foreach (['SerialNumber', 'serialNumber', 'DeviceSerialNumber', 'device_serial', 'serial_number'] as $key) {
            if (!empty($device[$key])) {
                return (string)$device[$key];
            }
        }

        return $fallback;
    }
}

if (!function_exists('mpsm_dd_customer_code')) {
    function mpsm_dd_customer_code(array $device, string $fallback = ''): string
    {
        if (!empty($device['CustomerCode'])) {
            return (string)$device['CustomerCode'];
        }

        if (!empty($device['Customer']['Code'])) {
            return (string)$device['Customer']['Code'];
        }

        return $fallback;
    }
}

if (!function_exists('mpsm_dd_unwrap_result')) {
    function mpsm_dd_unwrap_result($response)
    {
        if (!is_array($response)) {
            return $response;
        }

        foreach (['Result', 'Items', 'data'] as $key) {
            if (array_key_exists($key, $response)) {
                return $response[$key];
            }
        }

        return $response;
    }
}

if (!function_exists('mpsm_dd_as_rows')) {
    function mpsm_dd_as_rows($value): array
    {
        $value = mpsm_dd_unwrap_result($value);

        if (!is_array($value) || $value === []) {
            return [];
        }

        if (mpsm_dd_is_list_array($value)) {
            return array_values($value);
        }

        foreach ([
            'CounterDetails',
            'Counters',
            'MaintenanceKitLevels',
            'MaintenanceKitCounters',
            'SupplyAlerts',
            'Alerts',
            'Actions',
            'DetailsBySupply',
            'SuppliesInfo',
            'CurrentSupplies',
            'AvailableSupplies',
            'SuggestedSupplies',
        ] as $key) {
            if (isset($value[$key]) && is_array($value[$key])) {
                return array_values($value[$key]);
            }
        }

        return [$value];
    }
}

if (!function_exists('mpsm_dd_section_data')) {
    function mpsm_dd_section_data(?array $drilldown, string $section)
    {
        if (!$drilldown) {
            return null;
        }

        $sections = $drilldown['_mpsm']['sections'] ?? null;
        if (is_array($sections) && isset($sections[$section]) && is_array($sections[$section])) {
            if (array_key_exists('data', $sections[$section])) {
                return $sections[$section]['data'];
            }
        }

        return null;
    }
}

if (!function_exists('mpsm_dd_first_array')) {
    function mpsm_dd_first_array(array $sources, array $keys): ?array
    {
        foreach ($sources as $source) {
            if (!is_array($source)) {
                continue;
            }

            foreach ($keys as $key) {
                if (isset($source[$key]) && is_array($source[$key]) && $source[$key] !== []) {
                    return $source[$key];
                }
            }

            if (isset($source['Device']) && is_array($source['Device'])) {
                foreach ($keys as $key) {
                    if (isset($source['Device'][$key]) && is_array($source['Device'][$key]) && $source['Device'][$key] !== []) {
                        return $source['Device'][$key];
                    }
                }
            }
        }

        return null;
    }
}

if (!function_exists('mpsm_dd_extract_device_from_drilldown')) {
    function mpsm_dd_extract_device_from_drilldown(array $drilldown): ?array
    {
        foreach (['Device', 'device', 'DeviceSummary', 'BaseDevice', 'DeviceInfo'] as $key) {
            if (isset($drilldown[$key]) && is_array($drilldown[$key]) && $drilldown[$key] !== []) {
                return $drilldown[$key];
            }
        }

        $deviceGet = mpsm_dd_section_data($drilldown, 'deviceGet');
        $deviceGet = mpsm_dd_unwrap_result($deviceGet);
        if (is_array($deviceGet) && !mpsm_dd_is_list_array($deviceGet)) {
            return $deviceGet;
        }

        if (!empty($drilldown['SerialNumber']) || !empty($drilldown['Id']) || !empty($drilldown['IdInstalledProduct'])) {
            return $drilldown;
        }

        return null;
    }
}

if (!function_exists('mpsm_dd_call_query')) {
    function mpsm_dd_call_query(string $section, string $action, array $params, array &$sectionErrors): ?array
    {
        if (!function_exists('callMPSQuery')) {
            $sectionErrors[$section] = 'MPS query helper is unavailable';
            return null;
        }

        try {
            $result = callMPSQuery($action, mpsm_dd_clean_params($params));
            if (!is_array($result)) {
                $sectionErrors[$section] = "No response from {$action}";
                return null;
            }

            if (isset($result['IsValid']) && !$result['IsValid']) {
                $errors = $result['Errors'] ?? [];
                $sectionErrors[$section] = $errors ? json_encode($errors) : "{$action} returned invalid response";
            }

            return $result;
        } catch (Throwable $e) {
            $sectionErrors[$section] = $e->getMessage();
            return null;
        }
    }
}

if (!function_exists('mpsm_dd_add_section')) {
    function mpsm_dd_add_section(array &$sections, string $name, string $action, ?array $data): void
    {
        if ($data === null) {
            return;
        }

        $sections[$name] = [
            'action' => $action,
            'status' => 'ok',
            'fetchedAt' => date('c'),
            'data' => $data
        ];
    }
}

if (!function_exists('mpsm_dd_wrap_device_get_payload')) {
    function mpsm_dd_wrap_device_get_payload(array $device, array $deviceGet, array $sectionErrors = []): array
    {
        $deviceId = mpsm_dd_device_id($device);
        $serial = mpsm_dd_device_serial($device);
        $customerCode = mpsm_dd_customer_code($device);
        $base = mpsm_dd_unwrap_result($deviceGet);

        if (!is_array($base) || mpsm_dd_is_list_array($base)) {
            $base = $device;
        }

        unset($base['_mpsm']);
        $base['_mpsm'] = [
            'schemaVersion' => 2,
            'enrichedAt' => date('c'),
            'identity' => [
                'deviceId' => $deviceId,
                'serialNumber' => $serial,
                'customerCode' => $customerCode
            ],
            'sections' => [
                'deviceGet' => [
                    'action' => 'Device/Get',
                    'status' => 'ok',
                    'fetchedAt' => date('c'),
                    'data' => $deviceGet
                ]
            ],
            'sectionErrors' => $sectionErrors
        ];

        return $base;
    }
}

if (!function_exists('mpsm_dd_enrich_device_payload')) {
    function mpsm_dd_enrich_device_payload(array $device, array $identity = []): array
    {
        $deviceId = mpsm_dd_device_id($device, (string)($identity['deviceId'] ?? ''));
        $serial = mpsm_dd_device_serial($device, (string)($identity['serialNumber'] ?? ''));
        $customerCode = mpsm_dd_customer_code($device, (string)($identity['customerCode'] ?? ''));
        $sectionErrors = [];
        $sections = [];
        $base = $device;

        if ($deviceId !== '') {
            $deviceGet = mpsm_dd_call_query('deviceGet', 'Device/Get', ['id' => $deviceId], $sectionErrors);
            if ($deviceGet) {
                mpsm_dd_add_section($sections, 'deviceGet', 'Device/Get', $deviceGet);
                $deviceGetResult = mpsm_dd_unwrap_result($deviceGet);
                if (is_array($deviceGetResult) && !mpsm_dd_is_list_array($deviceGetResult)) {
                    $base = $deviceGetResult;
                    $deviceId = mpsm_dd_device_id($base, $deviceId);
                    $serial = mpsm_dd_device_serial($base, $serial);
                    $customerCode = mpsm_dd_customer_code($base, $customerCode);
                }
            }
        }

        if ($customerCode !== '' && $serial !== '') {
            $counterDetails = mpsm_dd_call_query('counterDetails', 'Counter/ListDetailed', [
                'DealerCode' => DEFAULT_DEALER_CODE,
                'CustomerCode' => $customerCode,
                'SerialNumber' => $serial,
                'CounterDetaildTags' => null
            ], $sectionErrors);

            if ($deviceId !== '' && empty(mpsm_dd_as_rows($counterDetails))) {
                $counterDetails = mpsm_dd_call_query('counterDetails', 'Counter/ListDetailed', [
                    'DeviceId' => $deviceId
                ], $sectionErrors);
            }

            mpsm_dd_add_section($sections, 'counterDetails', 'Counter/ListDetailed', $counterDetails);
        } elseif ($deviceId !== '') {
            $counterDetails = mpsm_dd_call_query('counterDetails', 'Counter/ListDetailed', [
                'DeviceId' => $deviceId
            ], $sectionErrors);
            mpsm_dd_add_section($sections, 'counterDetails', 'Counter/ListDetailed', $counterDetails);
        }

        if ($deviceId !== '') {
            $maintenanceCounters = mpsm_dd_call_query('maintenanceCounters', 'Counter/ListMaintenanceKitCounters', [
                'id' => $deviceId
            ], $sectionErrors);
            mpsm_dd_add_section($sections, 'maintenanceCounters', 'Counter/ListMaintenanceKitCounters', $maintenanceCounters);

            $suppliesDetails = mpsm_dd_call_query('suppliesDetails', 'Device/GetSuppliesDetails', [
                'id' => $deviceId
            ], $sectionErrors);
            mpsm_dd_add_section($sections, 'suppliesDetails', 'Device/GetSuppliesDetails', $suppliesDetails);

            $suppliesSummary = mpsm_dd_call_query('suppliesSummary', 'Device/GetSuppliesDetailsSummary', [
                'id' => $deviceId
            ], $sectionErrors);
            mpsm_dd_add_section($sections, 'suppliesSummary', 'Device/GetSuppliesDetailsSummary', $suppliesSummary);

            $maintenanceAlerts = mpsm_dd_call_query('maintenanceAlerts', 'Device/MaintenanceAlerts/List', [
                'IdInstalledProduct' => $deviceId,
                'PageNumber' => 1,
                'PageRows' => 100,
                'SortColumn' => 'Id',
                'SortOrder' => 'Desc'
            ], $sectionErrors);
            mpsm_dd_add_section($sections, 'maintenanceAlerts', 'Device/MaintenanceAlerts/List', $maintenanceAlerts);

            $maintenanceHistory = mpsm_dd_call_query('maintenanceHistory', 'Device/ListMaintenanceKitMessagesDataHistory', [
                'DeviceId' => $deviceId,
                'FromDate' => gmdate('c', strtotime('-365 days')),
                'ToDate' => gmdate('c'),
                'ShowNoChanges' => false,
                'PageNumber' => 1,
                'PageRows' => 100,
                'SortColumn' => 'DateUTC',
                'SortOrder' => 'Desc'
            ], $sectionErrors);
            mpsm_dd_add_section($sections, 'maintenanceHistory', 'Device/ListMaintenanceKitMessagesDataHistory', $maintenanceHistory);
        }

        if ($deviceId !== '' || $customerCode !== '') {
            $sdsActions = mpsm_dd_call_query('sdsActions', 'SdsAction/GetDeviceActions', [
                'deviceId' => $deviceId,
                'dealerCode' => DEFAULT_DEALER_CODE,
                'customerCode' => $customerCode,
                'pageNumber' => 1,
                'pageRows' => 100,
                'sortColumn' => 'ActionDateUtc',
                'sortOrder' => 'Desc'
            ], $sectionErrors);
            mpsm_dd_add_section($sections, 'sdsActions', 'SdsAction/GetDeviceActions', $sdsActions);
        }

        if ($customerCode !== '' || $serial !== '' || $deviceId !== '') {
            $supplyAlerts = mpsm_dd_call_query('supplyAlerts', 'SupplyAlert/List', [
                'DealerCode' => DEFAULT_DEALER_CODE,
                'CustomerCode' => $customerCode,
                'DeviceId' => $deviceId,
                'SerialNumber' => $serial,
                'OutputType' => 'DeviceDetail',
                'PageNumber' => 1,
                'PageRows' => 100,
                'SortColumn' => 'Id',
                'SortOrder' => 'Desc'
            ], $sectionErrors);
            mpsm_dd_add_section($sections, 'supplyAlerts', 'SupplyAlert/List', $supplyAlerts);
        }

        unset($base['_mpsm']);
        $base['_mpsm'] = [
            'schemaVersion' => 2,
            'enrichedAt' => date('c'),
            'identity' => [
                'deviceId' => $deviceId,
                'serialNumber' => $serial,
                'customerCode' => $customerCode
            ],
            'sections' => $sections,
            'sectionErrors' => $sectionErrors
        ];

        return [
            'drilldown' => $base,
            'sectionErrors' => $sectionErrors,
            'sections' => $sections
        ];
    }
}

if (!function_exists('mpsm_dd_toner_levels')) {
    function mpsm_dd_toner_levels(array $device): array
    {
        $groups = [
            ['key' => 'black', 'label' => 'Black Toner', 'fields' => ['BlackToner', 'BlackToner1', 'BlackToner2', 'BlackToner3']],
            ['key' => 'cyan', 'label' => 'Cyan Toner', 'fields' => ['CyanToner', 'CyanToner1']],
            ['key' => 'magenta', 'label' => 'Magenta Toner', 'fields' => ['MagentaToner', 'MagentaToner1']],
            ['key' => 'yellow', 'label' => 'Yellow Toner', 'fields' => ['YellowToner', 'YellowToner1']],
        ];
        $levels = [];

        foreach ($groups as $group) {
            foreach ($group['fields'] as $field) {
                if (isset($device[$field]) && is_numeric($device[$field])) {
                    $levels[] = [
                        'type' => 'toner',
                        'color' => $group['key'],
                        'Key' => $group['label'],
                        'label' => $group['label'],
                        'value' => (float)$device[$field],
                        'field' => $field
                    ];
                    break;
                }
            }
        }

        return $levels;
    }
}

if (!function_exists('mpsm_dd_text_value')) {
    function mpsm_dd_text_value($value): string
    {
        if (is_scalar($value)) {
            return trim((string)$value);
        }

        if (is_array($value)) {
            foreach (['Description', 'description', 'Name', 'name', 'Desc', 'Code', 'code', 'Key', 'Value'] as $key) {
                if (isset($value[$key]) && is_scalar($value[$key])) {
                    return trim((string)$value[$key]);
                }
            }
        }

        return '';
    }
}

if (!function_exists('mpsm_dd_text_from_keys')) {
    function mpsm_dd_text_from_keys(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }
            $value = mpsm_dd_text_value($row[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}

if (!function_exists('mpsm_dd_numeric_value')) {
    function mpsm_dd_numeric_value($value): ?float
    {
        if ($value === null || $value === '' || is_array($value) || is_object($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (float)$value;
        }

        $text = str_replace(',', '', trim((string)$value));
        if ($text === '') {
            return null;
        }

        if (!preg_match('/-?\d+(?:\.\d+)?/', $text, $matches)) {
            return null;
        }

        return (float)$matches[0];
    }
}

if (!function_exists('mpsm_dd_numeric_from_keys')) {
    function mpsm_dd_numeric_from_keys(array $row, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }
            $value = mpsm_dd_numeric_value($row[$key]);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }
}

if (!function_exists('mpsm_dd_counter_summary')) {
    function mpsm_dd_counter_summary(array $device, array $counterRows): array
    {
        $summary = [
            'monoTotal' => mpsm_dd_numeric_from_keys($device, ['CounterMono', 'MonoCounter', 'TotalMono', 'MonoTotal', 'BlackCounter', 'TotalBlack', 'CounterBlack', 'BlackAndWhiteCounter', 'BWCounter', 'BwCounter', 'TotalBW', 'MeterMono']),
            'colorTotal' => mpsm_dd_numeric_from_keys($device, ['CounterColor', 'ColorCounter', 'TotalColor', 'ColorTotal', 'ColourCounter', 'CounterColour', 'MeterColor']),
            'monoMonthly' => mpsm_dd_numeric_from_keys($device, ['MonthlyMonoVolume', 'MonthlyMonoPages', 'MonthlyMono', 'CounterMonoMonthly', 'CounterMonoDelta']),
            'colorMonthly' => mpsm_dd_numeric_from_keys($device, ['MonthlyColorVolume', 'MonthlyColorPages', 'MonthlyColor', 'CounterColorMonthly', 'CounterColorDelta']),
        ];

        foreach ($counterRows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $directMono = mpsm_dd_numeric_from_keys($row, ['CounterMono', 'MonoCounter', 'TotalMono', 'MonoTotal', 'BlackCounter', 'TotalBlack', 'CounterBlack', 'BWCounter', 'TotalBW']);
            if ($directMono !== null && ($summary['monoTotal'] === null || $summary['monoTotal'] <= 0)) {
                $summary['monoTotal'] = $directMono;
            }

            $directColor = mpsm_dd_numeric_from_keys($row, ['CounterColor', 'ColorCounter', 'TotalColor', 'ColorTotal', 'ColourCounter', 'CounterColour']);
            if ($directColor !== null && ($summary['colorTotal'] === null || $summary['colorTotal'] <= 0)) {
                $summary['colorTotal'] = $directColor;
            }

            $labelParts = [];
            foreach ([
                'Key',
                'key',
                'CounterTypeDescription',
                'CounterDescription',
                'Description',
                'Name',
                'Type',
                'CounterType',
                'Counter',
                'Label',
                'label'
            ] as $labelKey) {
                if (!array_key_exists($labelKey, $row)) {
                    continue;
                }
                $labelText = mpsm_dd_text_value($row[$labelKey]);
                if ($labelText !== '') {
                    $labelParts[] = $labelText;
                }
            }
            $label = strtolower(implode(' ', array_unique($labelParts)));
            $counterType = strtoupper(mpsm_dd_text_from_keys($row, ['CounterType', 'Type', 'Code']));
            $value = mpsm_dd_numeric_from_keys($row, ['value', 'Value', 'CounterValue', 'ValueCounter', 'CurrentCounter', 'CurrentValue', 'Total', 'Pages', 'MeterValue', 'Reading', 'CounterReading', 'Count']);

            if ($value === null) {
                continue;
            }

            $isMonthly = preg_match('/\b(month|monthly|period|delta)\b/', $label) === 1;
            $isMono = preg_match('/\b(mono|monochrome|black|b\/w|b&w|bw|gray|grey)\b/', $label) === 1
                || in_array($counterType, ['M', 'MA3'], true);
            $isColor = preg_match('/\b(color|colour|cmyk|cyan|magenta|yellow)\b/', $label) === 1
                || in_array($counterType, ['C', 'CA3'], true);

            if ($isMonthly && $isMono && ($summary['monoMonthly'] === null || $summary['monoMonthly'] <= 0)) {
                $summary['monoMonthly'] = $value;
            } elseif ($isMonthly && $isColor && ($summary['colorMonthly'] === null || $summary['colorMonthly'] <= 0)) {
                $summary['colorMonthly'] = $value;
            } elseif (!$isMonthly && $isMono && ($summary['monoTotal'] === null || $summary['monoTotal'] <= 0)) {
                $summary['monoTotal'] = $value;
            } elseif (!$isMonthly && $isColor && ($summary['colorTotal'] === null || $summary['colorTotal'] <= 0)) {
                $summary['colorTotal'] = $value;
            }
        }

        return $summary;
    }
}

if (!function_exists('mpsm_dd_consumable_color')) {
    function mpsm_dd_consumable_color(string $label): string
    {
        $text = strtolower($label);
        if (strpos($text, 'black') !== false || strpos($text, 'mono') !== false) return 'black';
        if (strpos($text, 'cyan') !== false) return 'cyan';
        if (strpos($text, 'magenta') !== false) return 'magenta';
        if (strpos($text, 'yellow') !== false) return 'yellow';
        if (strpos($text, 'drum') !== false || strpos($text, 'imaging') !== false || strpos($text, 'image unit') !== false) return 'drum';
        if (strpos($text, 'fuser') !== false) return 'fuser';
        if (strpos($text, 'waste') !== false) return 'waste';
        return 'neutral';
    }
}

if (!function_exists('mpsm_dd_consumable_label')) {
    function mpsm_dd_consumable_label(array $row): string
    {
        $parts = [];
        foreach ([
            'Key',
            'key',
            'label',
            'Description',
            'SupplyName',
            'Name',
            'TypeDescription',
            'SupplyTypeDescription',
            'SupplyType',
            'MaintenanceKitType',
            'MaintenanceKitColor',
            'ColorTypeDescription',
            'ColorType',
            'CounterName',
            'CounterTypeDescription'
        ] as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }
            $text = mpsm_dd_text_value($row[$key]);
            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return trim(implode(' ', array_unique($parts)));
    }
}

if (!function_exists('mpsm_dd_consumable_levels')) {
    function mpsm_dd_consumable_levels(array $device, array $sources): array
    {
        $levels = mpsm_dd_toner_levels($device);
        $byKey = [];

        foreach ($levels as $index => $level) {
            $byKey[strtolower(($level['Key'] ?? $level['label'] ?? 'toner') . '|' . ($level['color'] ?? $index))] = $level;
        }

        foreach ($sources as $sourceName => $source) {
            foreach (mpsm_dd_as_rows($source) as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $label = mpsm_dd_consumable_label($row);
                $value = mpsm_dd_numeric_from_keys($row, [
                    'value',
                    'Value',
                    'LevelValue',
                    'Level',
                    'CurrentProgressPercentage',
                    'RemainingPercentage',
                    'Percentage',
                    'Percent',
                    'Life',
                    'LifeRemaining',
                    'RemainingLife',
                    'Remaining',
                    'CurrentLevel',
                    'SupplyLevel',
                    'CounterValue'
                ]);

                if ($label === '' || $value === null) {
                    continue;
                }

                $haystack = strtolower($label . ' ' . json_encode($row));
                $looksLikeConsumable = preg_match('/toner|drum|imaging|image unit|fuser|maintenance|kit|belt|waste|developer|roller|feed|staple|cartridge|transfer/', $haystack) === 1
                    || strpos((string)$sourceName, 'maintenance') !== false;

                if (!$looksLikeConsumable) {
                    continue;
                }

                $color = mpsm_dd_consumable_color($label);
                $normalized = [
                    'type' => strpos(strtolower($label), 'drum') !== false || strpos(strtolower($label), 'imaging') !== false ? 'drum' : 'consumable',
                    'color' => $color,
                    'Key' => $label,
                    'label' => $label,
                    'value' => $value,
                    'source' => (string)$sourceName
                ];

                $key = strtolower($label . '|' . $color);
                $byKey[$key] = $normalized;
            }
        }

        return array_values($byKey);
    }
}

if (!function_exists('mpsm_dd_normalize_payload')) {
    function mpsm_dd_normalize_payload(array $device, ?array $drilldown): array
    {
        $deviceGet = mpsm_dd_section_data($drilldown, 'deviceGet');
        $counterDetails = mpsm_dd_section_data($drilldown, 'counterDetails');
        $maintenanceCounters = mpsm_dd_section_data($drilldown, 'maintenanceCounters');
        $suppliesDetails = mpsm_dd_section_data($drilldown, 'suppliesDetails');
        $suppliesSummary = mpsm_dd_section_data($drilldown, 'suppliesSummary');
        $maintenanceAlerts = mpsm_dd_section_data($drilldown, 'maintenanceAlerts');
        $maintenanceHistory = mpsm_dd_section_data($drilldown, 'maintenanceHistory');
        $sdsActions = mpsm_dd_section_data($drilldown, 'sdsActions');
        $supplyAlerts = mpsm_dd_section_data($drilldown, 'supplyAlerts');

        $sources = array_filter([
            $drilldown,
            is_array(mpsm_dd_unwrap_result($deviceGet)) ? mpsm_dd_unwrap_result($deviceGet) : null,
            is_array(mpsm_dd_unwrap_result($suppliesDetails)) ? mpsm_dd_unwrap_result($suppliesDetails) : null,
            is_array(mpsm_dd_unwrap_result($suppliesSummary)) ? mpsm_dd_unwrap_result($suppliesSummary) : null,
        ], 'is_array');

        if (!$counterDetails) {
            $counterArray = mpsm_dd_first_array($sources, ['CounterDetails', 'Counters', 'counterDetails', 'Meters', 'MeterReadings']);
            $counterDetails = $counterArray ? ['Result' => $counterArray] : null;
        }

        if (!$sdsActions) {
            $actions = mpsm_dd_first_array($sources, ['Actions', 'actions', 'DeviceActions']);
            $sdsActions = $actions ? ['Result' => $actions] : null;
        }

        if (!$supplyAlerts) {
            $alerts = mpsm_dd_first_array($sources, ['SupplyAlerts', 'Alerts', 'SupplyAlertList']);
            $supplyAlerts = $alerts ? ['Result' => $alerts] : null;
        }

        $maintenanceLevels = [];
        foreach ([$maintenanceCounters, $suppliesDetails, $suppliesSummary, $deviceGet, $drilldown] as $candidate) {
            $candidate = mpsm_dd_unwrap_result($candidate);
            if (!is_array($candidate)) {
                continue;
            }
            foreach (['MaintenanceKitLevels', 'MaintenanceKitCounters', 'MaintenanceLevels', 'MaintenanceKits'] as $key) {
                if (isset($candidate[$key]) && is_array($candidate[$key])) {
                    foreach (array_values($candidate[$key]) as $row) {
                        $maintenanceLevels[] = $row;
                    }
                }
            }
        }

        $supplyDetailsResult = mpsm_dd_unwrap_result($suppliesDetails);
        $supplySummaryResult = mpsm_dd_unwrap_result($suppliesSummary);
        $counterRows = mpsm_dd_as_rows($counterDetails);
        $counterDetailRows = $counterRows;
        $deviceSerial = mpsm_dd_device_serial($device);
        foreach ($counterRows as $counterRow) {
            if (!is_array($counterRow) || empty($counterRow['CounterDetails']) || !is_array($counterRow['CounterDetails'])) {
                continue;
            }
            $rowSerial = (string)($counterRow['SerialNumber'] ?? $counterRow['DeviceSerialNumber'] ?? '');
            if ($deviceSerial === '' || $rowSerial === '' || strcasecmp($rowSerial, $deviceSerial) === 0) {
                $counterDetailRows = array_values($counterRow['CounterDetails']);
                break;
            }
        }
        $counterSummary = mpsm_dd_counter_summary($device, $counterDetailRows);
        $consumableLevels = mpsm_dd_consumable_levels($device, [
            'suppliesDetails' => $supplyDetailsResult,
            'suppliesSummary' => $supplySummaryResult,
            'maintenanceCounters' => $maintenanceCounters,
            'maintenanceLevels' => $maintenanceLevels
        ]);

        return [
            'counters' => [
                'summary' => $counterSummary,
                'details' => $counterDetailRows,
                'raw' => $counterDetails
            ],
            'supplies' => [
                'levels' => $consumableLevels,
                'details' => mpsm_dd_as_rows($supplyDetailsResult),
                'summary' => $supplySummaryResult,
                'alerts' => mpsm_dd_as_rows($supplyAlerts),
                'raw' => [
                    'details' => $suppliesDetails,
                    'summary' => $suppliesSummary
                ]
            ],
            'maintenance' => [
                'levels' => $maintenanceLevels,
                'counters' => mpsm_dd_as_rows($maintenanceCounters),
                'alerts' => mpsm_dd_as_rows($maintenanceAlerts),
                'history' => mpsm_dd_as_rows($maintenanceHistory),
                'raw' => [
                    'counters' => $maintenanceCounters,
                    'alerts' => $maintenanceAlerts,
                    'history' => $maintenanceHistory
                ]
            ],
            'alerts' => [
                'supply' => mpsm_dd_as_rows($supplyAlerts),
                'maintenance' => mpsm_dd_as_rows($maintenanceAlerts),
                'sdsActions' => mpsm_dd_as_rows($sdsActions),
            ],
            'deviceHealth' => [
                'Actions' => mpsm_dd_as_rows($sdsActions)
            ],
            'counterDetails' => [
                'CounterDetails' => $counterDetailRows
            ],
            'supplyAlerts' => mpsm_dd_as_rows($supplyAlerts),
            'sectionErrors' => is_array($drilldown) ? ($drilldown['_mpsm']['sectionErrors'] ?? []) : []
        ];
    }
}

if (!function_exists('mpsm_dd_drilldown_flags')) {
    function mpsm_dd_drilldown_flags(array $drilldown): array
    {
        $normalized = mpsm_dd_normalize_payload($drilldown, $drilldown);
        $hasAlerts = !empty($normalized['alerts']['supply'])
            || !empty($normalized['alerts']['maintenance'])
            || !empty($normalized['alerts']['sdsActions']);
        $hasSupplies = !empty($normalized['supplies']['levels'])
            || !empty($normalized['supplies']['details'])
            || !empty($normalized['maintenance']['levels'])
            || !empty($normalized['maintenance']['counters']);

        return [
            'has_alerts' => $hasAlerts ? 1 : 0,
            'has_supplies' => $hasSupplies ? 1 : 0
        ];
    }
}

if (!function_exists('mpsm_dd_save_drilldown')) {
    function mpsm_dd_save_drilldown(PDO $pdo, string $table, string $serialColumn, string $serial, array $drilldown): void
    {
        $safeTable = preg_replace('/[^a-z0-9_]/i', '', $table);
        $safeSerialColumn = preg_replace('/[^a-z0-9_]/i', '', $serialColumn);
        if ($safeTable === '' || $safeSerialColumn === '') {
            throw new Exception('Invalid drilldown table or serial column');
        }

        $flags = mpsm_dd_drilldown_flags($drilldown);
        $stmt = $pdo->prepare("
            INSERT INTO {$safeTable}
            ({$safeSerialColumn}, drilldown_data, has_alerts, has_supplies, cached_at)
            VALUES (:serial, :data, :has_alerts, :has_supplies, NOW())
            ON DUPLICATE KEY UPDATE
                drilldown_data = VALUES(drilldown_data),
                has_alerts = VALUES(has_alerts),
                has_supplies = VALUES(has_supplies),
                cached_at = VALUES(cached_at)
        ");

        $stmt->execute([
            ':serial' => $serial,
            ':data' => json_encode($drilldown, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION),
            ':has_alerts' => $flags['has_alerts'],
            ':has_supplies' => $flags['has_supplies']
        ]);
    }
}
