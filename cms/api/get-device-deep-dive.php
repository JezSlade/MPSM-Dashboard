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

try {
    $result = [
        'device' => null,
        'counterDetails' => null,
        'deviceHealth' => null,
        'supplyAlerts' => null,
        'panelHistory' => null,
        'errors' => []
    ];

    // Step 1: Get base device info
    // Try to get device by searching with FilterText if we have serial number
    if (!empty($serialNumber)) {
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

    if (!$result['device']) {
        throw new Exception("Device not found");
    }

    // Extract info from found device
    $foundSerial = $result['device']['SerialNumber'] ?? $serialNumber;
    $foundCustomerCode = $result['device']['CustomerCode'] ?? $customerCode;

    // Step 2: Get Counter/ListDetailed for detailed meter readings
    if (!empty($foundSerial) && !empty($foundCustomerCode)) {
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
    if (!empty($foundSerial)) {
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
    if (!empty($foundCustomerCode)) {
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
            $pdo = getDatabase();
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
                    WHERE device_serial = :serialNumber
                    ORDER BY received_at DESC
                    LIMIT 100";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':serialNumber' => $foundSerial]);
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

    jsonSuccess($result);

} catch (Exception $e) {
    jsonError("Failed to fetch device data: " . $e->getMessage());
}
