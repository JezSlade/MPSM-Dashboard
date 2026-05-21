<?php
/**
 * Dealer Usage API (cache-first)
 * Returns monthly page-volume aggregates and top used devices from cache tables.
 */

require '../config.php';
require '../functions.php';

// Auth bypass: Allow secret parameter for programmatic access
$bypassSecret = 'DEALER_API_2025';
$providedSecret = $_GET['secret'] ?? $_POST['secret'] ?? '';
$authBypassed = ($providedSecret === $bypassSecret);

if (!$authBypassed) {
    requireAuth();
}

$forceRefresh = isset($_GET['force']) && $_GET['force'] === '1';

try {
    $cacheKey = 'dealer-usage-v1';
    $cacheTTL = 1800; // 30 minutes

    if (!$forceRefresh) {
        $cached = cacheGet($cacheKey);
        if ($cached !== null) {
            $decoded = is_string($cached) ? json_decode($cached, true) : $cached;
            if (is_array($decoded) && isset($decoded['timestamp'], $decoded['usage'])) {
                $age = time() - (int)$decoded['timestamp'];
                if ($age < $cacheTTL) {
                    jsonSuccess([
                        'usage' => $decoded['usage'],
                        'cached' => true,
                        'cache_age_seconds' => $age,
                        'source' => $decoded['source'] ?? 'cache'
                    ]);
                }
            }
        }
    }

    $pdo = getDatabase();
    $prefix = DB_PREFIX;
    $pageTable = $prefix . 'cache_page_volume';
    $deviceTable = $prefix . 'cache_devices';

    $usage = [
        'monthlyMonoManaged' => 0,
        'monthlyColorManaged' => 0,
        'monthlyMonoUnmanaged' => 0,
        'monthlyColorUnmanaged' => 0,
        'monthlyPagesManaged' => 0,
        'monthlyPagesUnmanaged' => 0,
        'monthlyPagesTotal' => 0,
        'topCustomersByUsage' => [],
        'topUsedDevices' => [],
        'topUsedDevicesSource' => 'cache_device_monthly_usage',
        'machineMetrics' => [
            'totalActiveDevices' => 0,
            'onlineDevices' => 0,
            'offlineDevices' => 0,
            'noContactOver7dDevices' => 0,
            'noContactDataDevices' => 0,
            'offlineRatePct' => 0,
            'noContactOver7dRatePct' => 0,
            'noContactDataRatePct' => 0,
            'customersWithDevices' => 0,
            'avgDevicesPerCustomer' => 0,
            'topCustomersByDevices' => [],
            'topBrands' => []
        ],
        'consumables' => [
            'totalAlerts' => 0,
            'devicesWithAlerts' => 0,
            'customersWithAlerts' => 0,
            'topFamilies' => [],
            'topCustomers' => [],
            'dataSource' => 'none'
        ],
        '_dataSource' => 'cache',
        '_tables' => [
            'pageVolume' => false,
            'devices' => false
        ]
    ];

    $hasPageTable = tableExists($pdo, $pageTable);
    $hasDeviceTable = tableExists($pdo, $deviceTable);
    $usage['_tables']['pageVolume'] = $hasPageTable;
    $usage['_tables']['devices'] = $hasDeviceTable;

    if ($hasPageTable) {
        $summary = $pdo->query("
            SELECT
                COALESCE(SUM(monthly_mono_managed), 0) AS mono_managed,
                COALESCE(SUM(monthly_color_managed), 0) AS color_managed,
                COALESCE(SUM(monthly_mono_unmanaged), 0) AS mono_unmanaged,
                COALESCE(SUM(monthly_color_unmanaged), 0) AS color_unmanaged
            FROM {$pageTable}
        ")->fetch(PDO::FETCH_ASSOC);

        $usage['monthlyMonoManaged'] = (int)($summary['mono_managed'] ?? 0);
        $usage['monthlyColorManaged'] = (int)($summary['color_managed'] ?? 0);
        $usage['monthlyMonoUnmanaged'] = (int)($summary['mono_unmanaged'] ?? 0);
        $usage['monthlyColorUnmanaged'] = (int)($summary['color_unmanaged'] ?? 0);
        $usage['monthlyPagesManaged'] = $usage['monthlyMonoManaged'] + $usage['monthlyColorManaged'];
        $usage['monthlyPagesUnmanaged'] = $usage['monthlyMonoUnmanaged'] + $usage['monthlyColorUnmanaged'];
        $usage['monthlyPagesTotal'] = $usage['monthlyPagesManaged'] + $usage['monthlyPagesUnmanaged'];

        $topCustomersSql = "
            SELECT
                pv.customer_code,
                pv.customer_code AS customer_name,
                COALESCE(SUM(pv.monthly_mono_managed + pv.monthly_color_managed), 0) AS managed_pages,
                COALESCE(SUM(pv.monthly_mono_unmanaged + pv.monthly_color_unmanaged), 0) AS unmanaged_pages,
                COALESCE(SUM(
                    pv.monthly_mono_managed + pv.monthly_color_managed +
                    pv.monthly_mono_unmanaged + pv.monthly_color_unmanaged
                ), 0) AS total_pages
            FROM {$pageTable} pv
            GROUP BY pv.customer_code
            ORDER BY total_pages DESC
            LIMIT 6
        ";

        if ($hasDeviceTable) {
            $topCustomersSql = "
                SELECT
                    pv.customer_code,
                    COALESCE(MAX(name_map.customer_name), pv.customer_code) AS customer_name,
                    COALESCE(SUM(pv.monthly_mono_managed + pv.monthly_color_managed), 0) AS managed_pages,
                    COALESCE(SUM(pv.monthly_mono_unmanaged + pv.monthly_color_unmanaged), 0) AS unmanaged_pages,
                    COALESCE(SUM(
                        pv.monthly_mono_managed + pv.monthly_color_managed +
                        pv.monthly_mono_unmanaged + pv.monthly_color_unmanaged
                    ), 0) AS total_pages
                FROM {$pageTable} pv
                LEFT JOIN (
                    SELECT
                        customer_code,
                        MAX(
                            COALESCE(
                                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerName')), ''),
                                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerDescription')), ''),
                                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Description')), ''),
                                customer_code
                            )
                        ) AS customer_name
                    FROM {$deviceTable}
                    WHERE is_uninstalled = 0
                      AND customer_code IS NOT NULL
                      AND customer_code != ''
                    GROUP BY customer_code
                ) name_map ON name_map.customer_code = pv.customer_code
                GROUP BY pv.customer_code
                ORDER BY total_pages DESC
                LIMIT 6
            ";
        }

        $topCustomerRows = $pdo->query($topCustomersSql)->fetchAll(PDO::FETCH_ASSOC);
        $usage['topCustomersByUsage'] = array_map(static function ($row) {
            return [
                'customerCode' => $row['customer_code'] ?? '',
                'customerName' => $row['customer_name'] ?? ($row['customer_code'] ?? ''),
                'managedPages' => (int)($row['managed_pages'] ?? 0),
                'unmanagedPages' => (int)($row['unmanaged_pages'] ?? 0),
                'totalPages' => (int)($row['total_pages'] ?? 0)
            ];
        }, $topCustomerRows);
    }

    if ($hasDeviceTable) {
        $lastContactExpr = "STR_TO_DATE(
            REPLACE(
                REPLACE(
                    SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.LastContact')), '.', 1),
                    'T',
                    ' '
                ),
                'Z',
                ''
            ),
            '%Y-%m-%d %H:%i:%s'
        )";
        $isOfflineExpr = "LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IsOffline')), ''))";
        $offlineFlagExpr = "({$isOfflineExpr} IN ('1','true','yes'))";

        $machineSummary = $pdo->query("
            SELECT
                COUNT(*) AS total_devices,
                SUM(
                    CASE
                        WHEN {$offlineFlagExpr} THEN 1
                        WHEN {$lastContactExpr} IS NOT NULL AND {$lastContactExpr} <= DATE_SUB(NOW(), INTERVAL 2 DAY) THEN 1
                        ELSE 0
                    END
                ) AS offline_devices,
                SUM(
                    CASE
                        WHEN {$lastContactExpr} IS NOT NULL AND {$lastContactExpr} <= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1
                        ELSE 0
                    END
                ) AS no_contact_over_7d_devices,
                SUM(CASE WHEN {$lastContactExpr} IS NULL THEN 1 ELSE 0 END) AS no_contact_data_devices,
                COUNT(DISTINCT customer_code) AS customers_with_devices
            FROM {$deviceTable}
            WHERE is_uninstalled = 0
              AND customer_code IS NOT NULL
              AND customer_code != ''
        ")->fetch(PDO::FETCH_ASSOC);

        $totalDevices = (int)($machineSummary['total_devices'] ?? 0);
        $offlineDevices = (int)($machineSummary['offline_devices'] ?? 0);
        $noContactOver7dDevices = (int)($machineSummary['no_contact_over_7d_devices'] ?? 0);
        $noContactDataDevices = (int)($machineSummary['no_contact_data_devices'] ?? 0);
        $customersWithDevices = (int)($machineSummary['customers_with_devices'] ?? 0);
        $onlineDevices = max(0, $totalDevices - $offlineDevices - $noContactDataDevices);

        $usage['machineMetrics']['totalActiveDevices'] = $totalDevices;
        $usage['machineMetrics']['onlineDevices'] = min($onlineDevices, $totalDevices);
        $usage['machineMetrics']['offlineDevices'] = $offlineDevices;
        $usage['machineMetrics']['noContactOver7dDevices'] = $noContactOver7dDevices;
        $usage['machineMetrics']['noContactDataDevices'] = $noContactDataDevices;
        $usage['machineMetrics']['offlineRatePct'] = percent($offlineDevices, $totalDevices);
        $usage['machineMetrics']['noContactOver7dRatePct'] = percent($noContactOver7dDevices, $totalDevices);
        $usage['machineMetrics']['noContactDataRatePct'] = percent($noContactDataDevices, $totalDevices);
        $usage['machineMetrics']['customersWithDevices'] = $customersWithDevices;
        $usage['machineMetrics']['avgDevicesPerCustomer'] = $customersWithDevices > 0
            ? round($totalDevices / $customersWithDevices, 1)
            : 0;

        $brandRows = $pdo->query("
            SELECT
                COALESCE(
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Brand')), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Maker')), ''),
                    'Unknown'
                ) AS brand,
                COUNT(*) AS count
            FROM {$deviceTable}
            WHERE is_uninstalled = 0
              AND customer_code IS NOT NULL
              AND customer_code != ''
            GROUP BY brand
            ORDER BY count DESC
            LIMIT 8
        ")->fetchAll(PDO::FETCH_ASSOC);

        $usage['machineMetrics']['topBrands'] = array_map(static function ($row) {
            $brand = trim((string)($row['brand'] ?? 'Unknown'));
            if ($brand === '' || strcasecmp($brand, 'null') === 0) {
                $brand = 'Unknown';
            }
            return [
                'brand' => $brand,
                'count' => (int)($row['count'] ?? 0)
            ];
        }, $brandRows);

        $topCustomersByDevicesRows = $pdo->query("
            SELECT
                customer_code,
                MAX(
                    COALESCE(
                        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerName')), ''),
                        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerDescription')), ''),
                        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Description')), ''),
                        customer_code
                    )
                ) AS customer_name,
                COUNT(*) AS count
            FROM {$deviceTable}
            WHERE is_uninstalled = 0
              AND customer_code IS NOT NULL
              AND customer_code != ''
            GROUP BY customer_code
            ORDER BY count DESC
            LIMIT 8
        ")->fetchAll(PDO::FETCH_ASSOC);

        $usage['machineMetrics']['topCustomersByDevices'] = array_map(static function ($row) {
            return [
                'customerCode' => $row['customer_code'] ?? '',
                'customerName' => $row['customer_name'] ?? ($row['customer_code'] ?? ''),
                'count' => (int)($row['count'] ?? 0)
            ];
        }, $topCustomersByDevicesRows);

        $monthlyMonoExpr = "CAST(REPLACE(COALESCE(
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.MonthlyMonoVolume')), ''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.monthlyMonoVolume')), ''),
            '0'
        ), ',', '') AS UNSIGNED)";
        $monthlyColorExpr = "CAST(REPLACE(COALESCE(
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.MonthlyColorVolume')), ''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.monthlyColorVolume')), ''),
            '0'
        ), ',', '') AS UNSIGNED)";
        $monthlyPagesExpr = "({$monthlyMonoExpr} + {$monthlyColorExpr})";

        $topDevicesSql = "
            SELECT
                serial_number,
                customer_code,
                COALESCE(
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerName')), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerDescription')), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Description')), ''),
                    customer_code
                ) AS customer_name,
                COALESCE(
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Brand')), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Maker')), ''),
                    ''
                ) AS brand_name,
                COALESCE(
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Model')), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.ModelName')), ''),
                    ''
                ) AS model_name,
                COALESCE(
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.AssetNumber')), ''),
                    ''
                ) AS asset_number,
                {$monthlyPagesExpr} AS monthly_usage
            FROM {$deviceTable}
            WHERE is_uninstalled = 0
              AND customer_code IS NOT NULL
              AND customer_code != ''
            HAVING monthly_usage > 0
            ORDER BY monthly_usage DESC
            LIMIT 10
        ";

        $topDeviceRows = $pdo->query($topDevicesSql)->fetchAll(PDO::FETCH_ASSOC);
        $usage['topUsedDevices'] = array_map(static function ($row) {
            $brand = trim((string)($row['brand_name'] ?? ''));
            $model = trim((string)($row['model_name'] ?? ''));
            $asset = trim((string)($row['asset_number'] ?? ''));
            $serial = trim((string)($row['serial_number'] ?? ''));
            if (strcasecmp($brand, 'null') === 0) $brand = '';
            if (strcasecmp($model, 'null') === 0) $model = '';
            if (strcasecmp($asset, 'null') === 0) $asset = '';

            $deviceLabel = trim($brand . ' ' . $model);
            if ($deviceLabel === '') {
                $deviceLabel = $asset !== '' ? "Asset {$asset}" : $serial;
            }

            return [
                'serialNumber' => $serial,
                'customerCode' => $row['customer_code'] ?? '',
                'customerName' => $row['customer_name'] ?? ($row['customer_code'] ?? ''),
                'deviceLabel' => $deviceLabel,
                'assetNumber' => $asset,
                'monthlyUsage' => (int)($row['monthly_usage'] ?? 0)
            ];
        }, $topDeviceRows);
    }

    $drilldownTable = $prefix . 'cache_device_drilldown';
    if (tableExists($pdo, $drilldownTable)) {
        $stmt = $pdo->query("
            SELECT serial_number, drilldown_data
            FROM {$drilldownTable}
            WHERE has_supplies = 1
        ");

        $supplyAlertsTotal = 0;
        $devicesWithAlerts = [];
        $customersWithAlerts = [];
        $familyCounts = [];
        $customerAlertCounts = [];
        $supplyImpressionBySerial = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $drilldown = json_decode((string)($row['drilldown_data'] ?? ''), true);
            if (!is_array($drilldown)) {
                continue;
            }

            $alerts = extractSupplyAlertsFromDrilldown($drilldown);
            if (empty($alerts)) {
                continue;
            }

            foreach ($alerts as $alert) {
                if (!is_array($alert)) {
                    continue;
                }

                $supplyAlertsTotal++;

                $serial = trim(extractStringField($alert, [
                    'InstalledProductSerialNumber',
                    'SerialNumber',
                    'DeviceSerialNumber',
                    'device_serial'
                ]));
                if ($serial === '') {
                    $serial = trim((string)($row['serial_number'] ?? ''));
                }
                if ($serial !== '') {
                    $devicesWithAlerts[$serial] = true;
                }

                $customerCode = trim(extractStringField($alert, [
                    'CustomerCode',
                    'customerCode'
                ]));
                $customerName = trim(extractStringField($alert, [
                    'CustomerDescription',
                    'CustomerName',
                    'customerName',
                    'customerDescription'
                ]));
                if ($customerCode !== '') {
                    $customersWithAlerts[$customerCode] = true;
                    if (!isset($customerAlertCounts[$customerCode])) {
                        $customerAlertCounts[$customerCode] = [
                            'customerCode' => $customerCode,
                            'customerName' => $customerName !== '' ? $customerName : $customerCode,
                            'count' => 0
                        ];
                    }
                    if ($customerName !== '' && $customerAlertCounts[$customerCode]['customerName'] === $customerCode) {
                        $customerAlertCounts[$customerCode]['customerName'] = $customerName;
                    }
                    $customerAlertCounts[$customerCode]['count']++;
                }

                $family = normalizeSupplyFamily($alert);
                if (!isset($familyCounts[$family])) {
                    $familyCounts[$family] = 0;
                }
                $familyCounts[$family]++;

                $impressions = extractIntegerField($alert, [
                    'TotalImpressions',
                    'totalImpressions',
                    'CounterTotal',
                    'TotalCounter'
                ]);

                if ($serial !== '' && $impressions > 0) {
                    $deviceLabel = trim(extractStringField($alert, ['Brand'])) . ' ' . trim(extractStringField($alert, ['Model']));
                    $deviceLabel = trim($deviceLabel);
                    if ($deviceLabel === '') {
                        $asset = trim(extractStringField($alert, ['AssetNumber']));
                        $deviceLabel = $asset !== '' ? "Asset {$asset}" : $serial;
                    }

                    $existing = $supplyImpressionBySerial[$serial]['totalImpressions'] ?? 0;
                    if ($impressions > $existing) {
                        $supplyImpressionBySerial[$serial] = [
                            'serialNumber' => $serial,
                            'customerCode' => $customerCode,
                            'customerName' => $customerName !== '' ? $customerName : ($customerCode !== '' ? $customerCode : 'Unknown customer'),
                            'deviceLabel' => $deviceLabel,
                            'assetNumber' => trim(extractStringField($alert, ['AssetNumber'])),
                            'totalImpressions' => $impressions
                        ];
                    }
                }
            }
        }

        $usage['consumables']['totalAlerts'] = $supplyAlertsTotal;
        $usage['consumables']['devicesWithAlerts'] = count($devicesWithAlerts);
        $usage['consumables']['customersWithAlerts'] = count($customersWithAlerts);
        $usage['consumables']['topFamilies'] = sortCountsToList($familyCounts, 'family');
        $usage['consumables']['topCustomers'] = array_values($customerAlertCounts);
        $usage['consumables']['dataSource'] = 'drilldown_cache';
        usort($usage['consumables']['topCustomers'], static function ($a, $b) {
            return ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
        });
        $usage['consumables']['topCustomers'] = array_slice($usage['consumables']['topCustomers'], 0, 8);

        // Keep supply-impression parsing for optional diagnostics only.
    }

    $panelMessagesTable = $prefix . 'panel_messages';
    if (($usage['consumables']['totalAlerts'] ?? 0) === 0) {
        try {
            $alertsResponse = callMPSQuery('SupplyAlert/List', [
                'DealerCode' => DEFAULT_DEALER_CODE,
                'PageNumber' => 1,
                'PageRows' => 500,
                'SortColumn' => 'Id',
                'SortOrder' => 0
            ]);

            $alerts = [];
            if (is_array($alertsResponse)) {
                if (isset($alertsResponse['Items']) && is_array($alertsResponse['Items'])) {
                    $alerts = $alertsResponse['Items'];
                } elseif (isset($alertsResponse['Result']) && is_array($alertsResponse['Result'])) {
                    $alerts = $alertsResponse['Result'];
                } else {
                    $alerts = $alertsResponse;
                }
            }

            $devicesWithAlerts = [];
            $customersWithAlerts = [];
            $familyCounts = [];
            $customerAlertCounts = [];

            foreach ($alerts as $alert) {
                if (!is_array($alert)) {
                    continue;
                }

                $serial = trim(extractStringField($alert, [
                    'InstalledProductSerialNumber',
                    'SerialNumber',
                    'DeviceSerialNumber'
                ]));
                if ($serial !== '') {
                    $devicesWithAlerts[$serial] = true;
                }

                $customerCode = trim(extractStringField($alert, ['CustomerCode', 'customerCode']));
                $customerName = trim(extractStringField($alert, ['CustomerDescription', 'CustomerName']));
                if ($customerCode !== '') {
                    $customersWithAlerts[$customerCode] = true;
                    if (!isset($customerAlertCounts[$customerCode])) {
                        $customerAlertCounts[$customerCode] = [
                            'customerCode' => $customerCode,
                            'customerName' => $customerName !== '' ? $customerName : $customerCode,
                            'count' => 0
                        ];
                    }
                    $customerAlertCounts[$customerCode]['count']++;
                }

                $family = normalizeSupplyFamily($alert);
                if (!isset($familyCounts[$family])) {
                    $familyCounts[$family] = 0;
                }
                $familyCounts[$family]++;
            }

            $usage['consumables']['totalAlerts'] = count($alerts);
            $usage['consumables']['devicesWithAlerts'] = count($devicesWithAlerts);
            $usage['consumables']['customersWithAlerts'] = count($customersWithAlerts);
            $usage['consumables']['topFamilies'] = sortCountsToList($familyCounts, 'family');
            $usage['consumables']['topCustomers'] = array_values($customerAlertCounts);
            $usage['consumables']['dataSource'] = 'live_supply_alerts';
            usort($usage['consumables']['topCustomers'], static function ($a, $b) {
                return ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
            });
            $usage['consumables']['topCustomers'] = array_slice($usage['consumables']['topCustomers'], 0, 8);
        } catch (Exception $e) {
            error_log('[Dealer Usage] SupplyAlert fallback failed: ' . $e->getMessage());
        }
    }

    if (($usage['consumables']['totalAlerts'] ?? 0) === 0 && tableExists($pdo, $panelMessagesTable)) {
        try {
            $summaryRow = $pdo->query("
                SELECT
                    COUNT(*) AS total_alerts,
                    COUNT(DISTINCT device_serial) AS devices_with_alerts,
                    COUNT(DISTINCT customer_code) AS customers_with_alerts
                FROM {$panelMessagesTable}
                WHERE received_at >= NOW() - INTERVAL 30 DAY
            ")->fetch(PDO::FETCH_ASSOC);

            $usage['consumables']['totalAlerts'] = (int)($summaryRow['total_alerts'] ?? 0);
            $usage['consumables']['devicesWithAlerts'] = (int)($summaryRow['devices_with_alerts'] ?? 0);
            $usage['consumables']['customersWithAlerts'] = (int)($summaryRow['customers_with_alerts'] ?? 0);
            $usage['consumables']['dataSource'] = 'panel_alert_proxy_30d';

            $familyRows = $pdo->query("
                SELECT
                    COALESCE(NULLIF(maintenance_alert_code, ''), 'Unknown') AS family,
                    COUNT(*) AS count
                FROM {$panelMessagesTable}
                WHERE received_at >= NOW() - INTERVAL 30 DAY
                GROUP BY family
                ORDER BY count DESC
                LIMIT 8
            ")->fetchAll(PDO::FETCH_ASSOC);
            $usage['consumables']['topFamilies'] = array_map(static function ($row) {
                return [
                    'family' => (string)($row['family'] ?? 'Unknown'),
                    'count' => (int)($row['count'] ?? 0)
                ];
            }, $familyRows);

            $customerRows = $pdo->query("
                SELECT
                    COALESCE(NULLIF(customer_description, ''), customer_code, 'Unknown customer') AS customer_name,
                    COALESCE(NULLIF(customer_code, ''), 'UNKNOWN') AS customer_code,
                    COUNT(*) AS count
                FROM {$panelMessagesTable}
                WHERE received_at >= NOW() - INTERVAL 30 DAY
                GROUP BY customer_code, customer_name
                ORDER BY count DESC
                LIMIT 8
            ")->fetchAll(PDO::FETCH_ASSOC);
            $usage['consumables']['topCustomers'] = array_map(static function ($row) {
                return [
                    'customerCode' => (string)($row['customer_code'] ?? 'UNKNOWN'),
                    'customerName' => (string)($row['customer_name'] ?? 'Unknown customer'),
                    'count' => (int)($row['count'] ?? 0)
                ];
            }, $customerRows);
        } catch (Exception $e) {
            error_log('[Dealer Usage] Panel consumables proxy fallback failed: ' . $e->getMessage());
        }
    }

    if (empty($usage['topUsedDevices']) && $hasDeviceTable) {
        try {
            $liveCandidates = [];
            foreach (['MonthlyMonoVolume', 'MonthlyColorVolume'] as $sortColumn) {
                $response = callMPSQuery('Device/List', [
                    'DealerCode' => DEFAULT_DEALER_CODE,
                    'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
                    'PageNumber' => 1,
                    'PageRows' => 100,
                    'SortColumn' => $sortColumn,
                    'SortOrder' => 1
                ]);

                $items = [];
                if (is_array($response)) {
                    if (isset($response['Items']) && is_array($response['Items'])) {
                        $items = $response['Items'];
                    } elseif (isset($response['Result']) && is_array($response['Result'])) {
                        $items = $response['Result'];
                    } else {
                        $items = $response;
                    }
                }

                foreach ($items as $device) {
                    if (!is_array($device) || !empty($device['Uninstall'])) {
                        continue;
                    }

                    $serial = trim((string)($device['SerialNumber'] ?? ''));
                    if ($serial === '') {
                        continue;
                    }

                    $monoMonthly = extractIntegerField($device, ['MonthlyMonoVolume', 'monthlyMonoVolume']);
                    $colorMonthly = extractIntegerField($device, ['MonthlyColorVolume', 'monthlyColorVolume']);
                    $monthlyUsage = $monoMonthly + $colorMonthly;
                    if ($monthlyUsage <= 0) {
                        continue;
                    }

                    $existing = $liveCandidates[$serial]['monthlyUsage'] ?? 0;
                    if ($monthlyUsage <= $existing) {
                        continue;
                    }

                    $customerCode = trim((string)($device['CustomerCode'] ?? ''));
                    $customerName = trim((string)($device['CustomerDescription'] ?? ''));
                    if ($customerName === '') {
                        $customerName = $customerCode !== '' ? $customerCode : 'Unknown customer';
                    }

                    $brand = trim((string)($device['Product']['Brand'] ?? ''));
                    if ($brand === '') {
                        $brand = trim((string)($device['Brand'] ?? ''));
                    }
                    $model = trim((string)($device['Product']['Model'] ?? ''));
                    if ($model === '') {
                        $model = trim((string)($device['SystemName'] ?? ''));
                    }
                    $asset = trim((string)($device['AssetNumber'] ?? ''));
                    $deviceLabel = trim($brand . ' ' . $model);
                    if ($deviceLabel === '') {
                        $deviceLabel = $asset !== '' ? "Asset {$asset}" : $serial;
                    }

                    $liveCandidates[$serial] = [
                        'serialNumber' => $serial,
                        'customerCode' => $customerCode,
                        'customerName' => $customerName,
                        'deviceLabel' => $deviceLabel,
                        'assetNumber' => $asset,
                        'monthlyUsage' => $monthlyUsage
                    ];
                }
            }

            if (!empty($liveCandidates)) {
                $top = array_values($liveCandidates);
                usort($top, static function ($a, $b) {
                    return ($b['monthlyUsage'] ?? 0) <=> ($a['monthlyUsage'] ?? 0);
                });
                $usage['topUsedDevices'] = array_slice($top, 0, 10);
                $usage['topUsedDevicesSource'] = 'live_device_monthly_usage';
            }
        } catch (Exception $e) {
            error_log('[Dealer Usage] Live device counter fallback failed: ' . $e->getMessage());
        }
    }

    if (empty($usage['topUsedDevices']) && $hasDeviceTable && tableExists($pdo, $panelMessagesTable)) {
        $fallbackSql = "
            SELECT
                cd.serial_number,
                cd.customer_code,
                COALESCE(
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.CustomerName')), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.CustomerDescription')), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.Description')), ''),
                    cd.customer_code
                ) AS customer_name,
                COALESCE(
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.Brand')), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.Maker')), ''),
                    ''
                ) AS brand_name,
                COALESCE(
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.Model')), ''),
                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.ModelName')), ''),
                    ''
                ) AS model_name,
                COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(cd.device_data, '$.AssetNumber')), ''), '') AS asset_number,
                COUNT(pm.id) AS panel_events_30d
            FROM {$panelMessagesTable} pm
            INNER JOIN {$deviceTable} cd ON pm.device_serial = cd.serial_number
            WHERE pm.received_at >= NOW() - INTERVAL 30 DAY
              AND cd.is_uninstalled = 0
            GROUP BY cd.serial_number, cd.customer_code, customer_name, brand_name, model_name, asset_number
            ORDER BY panel_events_30d DESC
            LIMIT 10
        ";

        $fallbackRows = $pdo->query($fallbackSql)->fetchAll(PDO::FETCH_ASSOC);
        $usage['topUsedDevices'] = array_map(static function ($row) {
            $brand = trim((string)($row['brand_name'] ?? ''));
            $model = trim((string)($row['model_name'] ?? ''));
            $asset = trim((string)($row['asset_number'] ?? ''));
            $serial = trim((string)($row['serial_number'] ?? ''));
            if (strcasecmp($brand, 'null') === 0) $brand = '';
            if (strcasecmp($model, 'null') === 0) $model = '';
            if (strcasecmp($asset, 'null') === 0) $asset = '';

            $deviceLabel = trim($brand . ' ' . $model);
            if ($deviceLabel === '') {
                $deviceLabel = $asset !== '' ? "Asset {$asset}" : $serial;
            }

            return [
                'serialNumber' => $serial,
                'customerCode' => $row['customer_code'] ?? '',
                'customerName' => $row['customer_name'] ?? ($row['customer_code'] ?? ''),
                'deviceLabel' => $deviceLabel,
                'assetNumber' => $asset,
                'panelEvents30d' => (int)($row['panel_events_30d'] ?? 0)
            ];
        }, $fallbackRows);
        $usage['topUsedDevicesSource'] = 'panel_activity_30d';
    }

    cacheStore($cacheKey, json_encode([
        'timestamp' => time(),
        'usage' => $usage,
        'source' => 'cache'
    ]));

    jsonSuccess([
        'usage' => $usage,
        'cached' => false,
        'cache_age_seconds' => 0,
        'source' => 'cache'
    ]);
} catch (Exception $e) {
    error_log('[Dealer Usage] ' . $e->getMessage());
    jsonError('Failed to load dealer usage metrics: ' . $e->getMessage());
}

function tableExists(PDO $pdo, string $tableName): bool
{
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($tableName));
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function percent(int $numerator, int $denominator): float
{
    if ($denominator <= 0) {
        return 0.0;
    }
    return round(($numerator / $denominator) * 100, 1);
}

function extractSupplyAlertsFromDrilldown(array $drilldown): array
{
    $keys = ['SupplyAlerts', 'supplyAlerts', 'Alerts', 'SupplyAlertList'];
    foreach ($keys as $key) {
        if (isset($drilldown[$key]) && is_array($drilldown[$key])) {
            return $drilldown[$key];
        }
    }
    if (isset($drilldown['Device']) && is_array($drilldown['Device'])) {
        foreach ($keys as $key) {
            if (isset($drilldown['Device'][$key]) && is_array($drilldown['Device'][$key])) {
                return $drilldown['Device'][$key];
            }
        }
    }
    return [];
}

function extractStringField(array $row, array $keys): string
{
    foreach ($keys as $key) {
        if (!array_key_exists($key, $row)) {
            continue;
        }
        $value = trim((string)$row[$key]);
        if ($value !== '' && strcasecmp($value, 'null') !== 0) {
            return $value;
        }
    }
    return '';
}

function extractIntegerField(array $row, array $keys): int
{
    foreach ($keys as $key) {
        if (!array_key_exists($key, $row)) {
            continue;
        }
        $raw = trim((string)$row[$key]);
        if ($raw === '' || strcasecmp($raw, 'null') === 0) {
            continue;
        }
        $normalized = preg_replace('/[^\d]/', '', $raw);
        if ($normalized === null || $normalized === '') {
            continue;
        }
        return (int)$normalized;
    }
    return 0;
}

function normalizeSupplyFamily(array $alert): string
{
    $explicit = extractStringField($alert, [
        'SupplyTypeDescription',
        'TypeDescription',
        'Description',
        'Title',
        'Code'
    ]);

    $haystack = strtolower(
        $explicit . ' ' .
        extractStringField($alert, ['PartDescription', 'PartNumber', 'SupplyType', 'ColorType'])
    );

    if (strpos($haystack, 'toner') !== false) return 'Toner';
    if (strpos($haystack, 'drum') !== false || strpos($haystack, 'imaging') !== false || strpos($haystack, 'image unit') !== false) return 'Drum/Imaging Unit';
    if (strpos($haystack, 'waste') !== false) return 'Waste Container';
    if (strpos($haystack, 'fuser') !== false || strpos($haystack, 'maintenance') !== false || strpos($haystack, 'kit') !== false) return 'Maintenance Kit';
    if (strpos($haystack, 'belt') !== false) return 'Transfer Belt';
    if (strpos($haystack, 'staple') !== false) return 'Staple';
    if (strpos($haystack, 'developer') !== false) return 'Developer';
    if (strpos($haystack, 'roller') !== false || strpos($haystack, 'feed') !== false) return 'Roller/Feed';

    if ($explicit !== '') {
        return $explicit;
    }
    return 'Other Consumable';
}

function sortCountsToList(array $counts, string $labelKey): array
{
    arsort($counts);
    $rows = [];
    foreach ($counts as $label => $count) {
        $rows[] = [
            $labelKey => $label,
            'count' => (int)$count
        ];
    }
    return array_slice($rows, 0, 8);
}
