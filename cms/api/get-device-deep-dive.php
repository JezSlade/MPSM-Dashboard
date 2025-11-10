<?php
/**
 * Device Deep Dive API
 * Fetches ALL available data for a device from multiple API endpoints
 *
 * Endpoints called:
 * - Device/List (base device info)
 * - Counter/ListDetailed (detailed counter/meter data)
 * - SdsAction/GetDeviceActions (health, actions, firmware updates)
 * - SupplyAlert/List (supply alerts for this device)
 *
 * Parameters:
 * - deviceId: Device ID
 * - serialNumber: Device serial number
 * - customerCode: Customer code (optional, for optimization)
 */

require '../config.php';
require '../functions.php';

requireAuth();

set_time_limit(60);
ini_set('max_execution_time', '60');

$deviceId = $_GET['deviceId'] ?? '';
$serialNumber = $_GET['serialNumber'] ?? '';
$customerCode = $_GET['customerCode'] ?? '';

if (empty($deviceId) && empty($serialNumber)) {
    jsonError("Device ID or Serial Number required");
    exit;
}

// Helper function to call MPS API query endpoint
function callMpsApiQuery($action, $params) {
    $params = array_filter($params, static function ($value) {
        return $value !== null;
    });

    $payload = json_encode([
        'action' => $action,
        'params' => $params
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 20,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    if (!$data || !isset($data['success']) || !$data['success']) {
        return null;
    }

    return $data['data'] ?? [];
}

function extractDeviceFromDrilldown(array $drilldown): ?array
{
    $candidates = [
        'Device',
        'device',
        'DeviceSummary',
        'BaseDevice',
        'DeviceInfo',
    ];

    foreach ($candidates as $key) {
        if (isset($drilldown[$key]) && is_array($drilldown[$key]) && !empty($drilldown[$key])) {
            return $drilldown[$key];
        }
    }

    return null;
}

function extractFirstArray(array $source, array $keys): ?array
{
    foreach ($keys as $key) {
        if (isset($source[$key]) && is_array($source[$key]) && !empty($source[$key])) {
            return $source[$key];
        }
    }

    if (isset($source['Device']) && is_array($source['Device'])) {
        foreach ($keys as $key) {
            if (isset($source['Device'][$key]) && is_array($source['Device'][$key]) && !empty($source['Device'][$key])) {
                return $source['Device'][$key];
            }
        }
    }

    return null;
}

try {
    $result = [
        'device' => null,
        'counterDetails' => null,
        'deviceHealth' => null,
        'supplyAlerts' => null,
        'panelHistory' => null,
        'errors' => []
    ];

    $pdo = getDatabase();
    $prefix = DB_PREFIX;
    $cachedDrilldownData = null;

    // Attempt to hydrate from cache for instant responses
    $cachedDeviceJson = null;

    if (!empty($serialNumber)) {
        $stmt = $pdo->prepare("SELECT device_data FROM {$prefix}cache_devices WHERE serial_number = :serial LIMIT 1");
        $stmt->execute([':serial' => $serialNumber]);
        $cachedDeviceJson = $stmt->fetchColumn() ?: null;
    }

    if (!$cachedDeviceJson && !empty($deviceId)) {
        $stmt = $pdo->prepare("
            SELECT device_data
            FROM {$prefix}cache_devices
            WHERE JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Id')) = :id1
               OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.DeviceId')) = :id2
               OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IdInstalledProduct')) = :id3
            LIMIT 1
        ");
        $stmt->execute([
            ':id1' => $deviceId,
            ':id2' => $deviceId,
            ':id3' => $deviceId
        ]);
        $cachedDeviceJson = $stmt->fetchColumn() ?: null;
    }

    if ($cachedDeviceJson) {
        $cachedDevice = json_decode($cachedDeviceJson, true);
        if (is_array($cachedDevice) && !empty($cachedDevice)) {
            $result['device'] = $cachedDevice;
            if (empty($serialNumber) && !empty($cachedDevice['SerialNumber'])) {
                $serialNumber = $cachedDevice['SerialNumber'];
            }
            if (empty($customerCode) && !empty($cachedDevice['CustomerCode'])) {
                $customerCode = $cachedDevice['CustomerCode'];
            }
        }
    }

    if (!empty($serialNumber)) {
        $stmt = $pdo->prepare("SELECT drilldown_data FROM {$prefix}cache_device_drilldown WHERE serial_number = :serial LIMIT 1");
        $stmt->execute([':serial' => $serialNumber]);
        $cachedDrilldownJson = $stmt->fetchColumn();
        if ($cachedDrilldownJson) {
            $decodedDrilldown = json_decode($cachedDrilldownJson, true);
            if (is_array($decodedDrilldown) && !empty($decodedDrilldown)) {
                $cachedDrilldownData = $decodedDrilldown;
                if (!$result['device']) {
                    $deviceCandidate = extractDeviceFromDrilldown($decodedDrilldown);
                    if ($deviceCandidate) {
                        $result['device'] = $deviceCandidate;
                        if (empty($serialNumber) && !empty($deviceCandidate['SerialNumber'])) {
                            $serialNumber = $deviceCandidate['SerialNumber'];
                        }
                        if (empty($customerCode) && !empty($deviceCandidate['CustomerCode'])) {
                            $customerCode = $deviceCandidate['CustomerCode'];
                        }
                    }
                }
            }
        }
    }

    // Step 1: Get base device info
    // Try to get device by searching with FilterText if we have serial number
    if (!$result['device'] && !empty($serialNumber)) {
        $deviceData = callMpsApiQuery('Device/List', [
            'FilterDealerId' => DEFAULT_DEALER_ID,
            'FilterCustomerCodes' => !empty($customerCode) ? [$customerCode] : null,
            'FilterText' => $serialNumber,
            'PageNumber' => 1,
            'PageRows' => 10,
            'SortColumn' => 'Id',
            'SortOrder' => 0
        ]);

        if ($deviceData) {
            $devices = $deviceData['Items'] ?? $deviceData['Result'] ?? $deviceData;
            if (is_array($devices) && !empty($devices)) {
                $result['device'] = $devices[0];
            }
        }
    }

    // If we didn't find it by serial, try searching by FilterText with deviceId
    if (!$result['device'] && !empty($deviceId)) {
        $deviceData = callMpsApiQuery('Device/List', [
            'FilterDealerId' => DEFAULT_DEALER_ID,
            'FilterCustomerCodes' => !empty($customerCode) ? [$customerCode] : null,
            'FilterText' => $deviceId,
            'PageNumber' => 1,
            'PageRows' => 10,
            'SortColumn' => 'Id',
            'SortOrder' => 0
        ]);

        if ($deviceData) {
            $devices = $deviceData['Items'] ?? $deviceData['Result'] ?? $deviceData;
            if (is_array($devices)) {
                // Find exact match
                foreach ($devices as $dev) {
                    if (($dev['Id'] ?? '') === $deviceId ||
                        ($dev['IdInstalledProduct'] ?? '') === $deviceId ||
                        ($dev['DeviceId'] ?? '') === $deviceId) {
                        $result['device'] = $dev;
                        break;
                    }
                }
            }
        }
    }

    // Final fallback: fetch directly by device ID
    if (!$result['device'] && !empty($deviceId)) {
        $deviceById = callMpsApiQuery('Device/Get', [
            'Id' => $deviceId
        ]);

        if ($deviceById && is_array($deviceById)) {
            $result['device'] = $deviceById;
        }
    }

    if (!$result['device'] && $cachedDrilldownData) {
        $deviceCandidate = extractDeviceFromDrilldown($cachedDrilldownData);
        if ($deviceCandidate) {
            $result['device'] = $deviceCandidate;
            if (empty($serialNumber) && !empty($deviceCandidate['SerialNumber'])) {
                $serialNumber = $deviceCandidate['SerialNumber'];
            }
            if (empty($customerCode) && !empty($deviceCandidate['CustomerCode'])) {
                $customerCode = $deviceCandidate['CustomerCode'];
            }
        }
    }

    if (!$result['device']) {
        throw new Exception("Device not found");
    }

    // Extract info from found device
    $foundSerial = $result['device']['SerialNumber'] ?? $serialNumber;
    $foundCustomerCode = $result['device']['CustomerCode'] ?? $customerCode;

    if (!$cachedDrilldownData && !empty($foundSerial)) {
        $stmt = $pdo->prepare("SELECT drilldown_data FROM {$prefix}cache_device_drilldown WHERE serial_number = :serial LIMIT 1");
        $stmt->execute([':serial' => $foundSerial]);
        $cachedDrilldownJson = $stmt->fetchColumn();
        if ($cachedDrilldownJson) {
            $decodedDrilldown = json_decode($cachedDrilldownJson, true);
            if (is_array($decodedDrilldown) && !empty($decodedDrilldown)) {
                $cachedDrilldownData = $decodedDrilldown;
            }
        }
    }

    if ($cachedDrilldownData) {
        if (!$result['counterDetails']) {
            $counterArray = extractFirstArray($cachedDrilldownData, ['CounterDetails', 'Counters', 'counterDetails', 'Meters', 'MeterReadings']);
            if (is_array($counterArray)) {
                $result['counterDetails'] = [
                    'CounterDetails' => array_values($counterArray)
                ];
            }
        }

        if (!$result['deviceHealth']) {
            $actions = extractFirstArray($cachedDrilldownData, ['Actions', 'actions', 'DeviceActions']);
            if (is_array($actions)) {
                $result['deviceHealth'] = [
                    'Actions' => array_values($actions)
                ];
            }
        }

        if (empty($result['supplyAlerts'])) {
            $alerts = extractFirstArray($cachedDrilldownData, ['SupplyAlerts', 'Alerts', 'SupplyAlertList']);
            if (is_array($alerts)) {
                $result['supplyAlerts'] = array_values($alerts);
            }
        }
    }

    // Step 2: Get Counter/ListDetailed for detailed meter readings
    if (!$result['counterDetails'] && !empty($foundSerial) && !empty($foundCustomerCode)) {
        try {
            $counterData = callMpsApiQuery('Counter/ListDetailed', [
                'DealerCode' => DEFAULT_DEALER_CODE,
                'CustomerCode' => $foundCustomerCode,
                'SerialNumber' => $foundSerial,
                'AssetNumber' => null,
                'CounterDetaildTags' => null
            ]);

            if ($counterData && is_array($counterData)) {
                // Find matching device in counter data
                foreach ($counterData as $counter) {
                    if (($counter['SerialNumber'] ?? '') === $foundSerial) {
                        $result['counterDetails'] = $counter;
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            $result['errors'][] = "Counter details: " . $e->getMessage();
        }
    }

    // Step 3: Get SdsAction/GetDeviceActions for health and recommended actions
    if (!$result['deviceHealth'] && !empty($foundSerial)) {
        try {
            $healthData = callMpsApiQuery('SdsAction/GetDeviceActions', [
                'DealerCode' => DEFAULT_DEALER_CODE,
                'DeviceSerialNumber' => $foundSerial
            ]);

            if ($healthData) {
                $result['deviceHealth'] = $healthData;
            }
        } catch (Exception $e) {
            $result['errors'][] = "Device health: " . $e->getMessage();
        }
    }

    // Step 4: Get SupplyAlert/List for this device
    if (empty($result['supplyAlerts']) && !empty($foundCustomerCode)) {
        try {
            $alertData = callMpsApiQuery('SupplyAlert/List', [
                'DealerId' => DEFAULT_DEALER_ID,
                'CustomerCodes' => [$foundCustomerCode],
                'PageNumber' => 1,
                'PageRows' => 100,
                'SortColumn' => 'Id',
                'SortOrder' => 0
            ]);

            if ($alertData) {
                $alerts = $alertData['Items'] ?? $alertData['Result'] ?? $alertData;
                if (is_array($alerts)) {
                    // Filter alerts for this specific device
                    $deviceAlerts = array_filter($alerts, function($alert) use ($foundSerial, $deviceId) {
                        $alertSerial = $alert['SerialNumber'] ?? $alert['DeviceSerialNumber'] ?? '';
                        $alertDeviceId = $alert['DeviceId'] ?? $alert['IdInstalledProduct'] ?? '';
                        return $alertSerial === $foundSerial || $alertDeviceId === $deviceId;
                    });
                    $result['supplyAlerts'] = array_values($deviceAlerts);
                }
            }
        } catch (Exception $e) {
            $result['errors'][] = "Supply alerts: " . $e->getMessage();
        }
    }

    // Step 5: Get panel message history from database (most recent 100)
    if (!empty($foundSerial)) {
        try {
            $table = DB_PREFIX . 'panel_messages';

            $sql = "SELECT
                        id,
                        received_at,
                        customer_code,
                        customer_description,
                        maintenance_alert_code,
                        maintenance_alert_id,
                        panel_configuration,
                        payload
                    FROM {$table}
                    WHERE device_serial = :serial
                    ORDER BY received_at DESC
                    LIMIT 100";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':serial', $foundSerial, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $messages = [];
            foreach ($rows as $row) {
                $decodedPayload = json_decode($row['payload'], true);
                $messages[] = [
                    'id' => (int)$row['id'],
                    'received_at' => $row['received_at'],
                    'customer_code' => $row['customer_code'],
                    'customer_description' => $row['customer_description'],
                    'maintenance_alert_code' => $row['maintenance_alert_code'],
                    'maintenance_alert_id' => $row['maintenance_alert_id'],
                    'panel_configuration' => $row['panel_configuration'],
                    'payload' => $decodedPayload ?? $row['payload']
                ];
            }

            $result['panelHistory'] = [
                'total' => count($messages),
                'messages' => $messages
            ];
        } catch (Exception $e) {
            $result['errors'][] = "Panel history: " . $e->getMessage();
        }
    }

    if ($cachedDrilldownData) {
        $result['drilldownCache'] = $cachedDrilldownData;
    }

    jsonSuccess($result);

} catch (Exception $e) {
    jsonError("Failed to fetch device data: " . $e->getMessage());
}
