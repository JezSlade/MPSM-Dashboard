<?php
/**
 * Device Age Report
 * Processes all devices via MPS API, decodes manufacture dates from serials,
 * and emits age-bucket metrics per customer and overall.
 */

require '../config.php';
require '../functions.php';

requireAuth();

header('Content-Type: application/json');

try {
    $report = buildDeviceAgeReport();
    jsonSuccess($report);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function buildDeviceAgeReport() {
    $warnings = [];
    $source = 'api';

    $devices = fetchAllDevices();
    if (empty($devices)) {
        $warnings[] = 'Primary API (OAuth) returned no devices; trying query endpoint';
        $devices = fetchAllDevicesViaQuery();
        if (!empty($devices)) {
            $source = 'api-query';
        }
    }
    if (empty($devices)) {
        $warnings[] = 'API returned no devices; falling back to cache';
        $source = 'cache';
        $devices = fetchDevicesFromCache();
    }
    if (empty($devices)) {
        $warnings[] = 'Cache is empty or unavailable; no devices to process';
    }
    $now = new DateTimeImmutable('now');

    $summary = [
        'totalDevices' => 0,
        'withAge' => 0,
        'avgAgeYears' => 0,
        'buckets' => [
            'under1' => 0,
            'oneTo3' => 0,
            'threeTo5' => 0,
            'over5' => 0,
            'unknown' => 0
        ],
        'unknownReasons' => []
    ];

    $customers = [];
    $ageSum = 0.0;

    foreach ($devices as $device) {
        $summary['totalDevices']++;

        $customerCode = $device['CustomerCode'] ?? $device['customerCode'] ?? 'UNKNOWN';
        $customerName = $device['CustomerName'] ?? $device['customerName'] ?? $customerCode;
        $manufacturer = strtoupper($device['Manufacturer'] ?? $device['Make'] ?? $device['make'] ?? '');
        if ($manufacturer === '' && isset($device['Product']['Brand'])) {
            $manufacturer = strtoupper($device['Product']['Brand']);
        }
        $serial = $device['SerialNumber'] ?? $device['serialNumber'] ?? $device['serial_number'] ?? '';

        if (!isset($customers[$customerCode])) {
            $customers[$customerCode] = [
                'code' => $customerCode,
                'name' => $customerName,
                'totalDevices' => 0,
                'withAge' => 0,
                'avgAgeYears' => 0,
                'buckets' => [
                    'under1' => 0,
                    'oneTo3' => 0,
                    'threeTo5' => 0,
                    'over5' => 0,
                    'unknown' => 0
                ],
                'ageSum' => 0.0
            ];
        }

        $customers[$customerCode]['totalDevices']++;

        $reason = null;
        $ageYears = attemptAgeDecode($manufacturer, $serial, $now, $reason);
        $bucket = getAgeBucket($ageYears);

        $summary['buckets'][$bucket]++;
        $customers[$customerCode]['buckets'][$bucket]++;

        if ($ageYears !== null) {
            $summary['withAge']++;
            $customers[$customerCode]['withAge']++;
            $ageSum += $ageYears;
            $customers[$customerCode]['ageSum'] += $ageYears;
        } else {
            $reasonKey = $reason ?? 'undetermined';
            $summary['unknownReasons'][$reasonKey] = ($summary['unknownReasons'][$reasonKey] ?? 0) + 1;
            $customers[$customerCode]['unknownReasons'][$reasonKey] = ($customers[$customerCode]['unknownReasons'][$reasonKey] ?? 0) + 1;
        }
    }

    if ($summary['withAge'] > 0) {
        $summary['avgAgeYears'] = round($ageSum / $summary['withAge'], 2);
    }

    foreach ($customers as &$customer) {
        if ($customer['withAge'] > 0) {
            $customer['avgAgeYears'] = round($customer['ageSum'] / $customer['withAge'], 2);
        }
        unset($customer['ageSum']);
        if (!isset($customer['unknownReasons'])) {
            $customer['unknownReasons'] = new stdClass();
        }
    }

    return [
        'generated_at' => (new DateTimeImmutable('now'))->format('c'),
        'total_devices_processed' => $summary['totalDevices'],
        'source' => $source,
        'warnings' => $warnings,
        'summary' => $summary,
        'customers' => array_values($customers)
    ];
}

function fetchAllDevices() {
    $devices = [];
    $pageRows = 100;
    $emptyStreak = 0;
    $dealerCode = defined('DEFAULT_DEALER_CODE') ? DEFAULT_DEALER_CODE : null;

    for ($page = 1; $page <= 500; $page++) {
        $response = callMPSAPI('Device/List', [
            'PageNumber' => $page,
            'PageRows' => $pageRows
        ]);

        $chunk = extractDevicesFromResponse($response);
        if (empty($chunk)) {
            $emptyStreak++;
            if ($emptyStreak >= 2) {
                break;
            }
            continue;
        }

        $emptyStreak = 0;
        $devices = array_merge($devices, $chunk);
    }

    return $devices;
}

function fetchAllDevicesViaQuery() {
    $devices = [];
    $pageRows = 100;
    $emptyStreak = 0;
    $dealerCode = defined('DEFAULT_DEALER_CODE') ? DEFAULT_DEALER_CODE : null;

    for ($page = 1; $page <= 500; $page++) {
        $response = callMPSQuery('Device/List', [
            'PageNumber' => $page,
            'PageRows' => $pageRows
        ]);

        $chunk = extractDevicesFromResponse($response);
        if (empty($chunk)) {
            $emptyStreak++;
            if ($emptyStreak >= 2) {
                break;
            }
            continue;
        }

        $emptyStreak = 0;
        $devices = array_merge($devices, $chunk);
    }

    return $devices;
}

function fetchDevicesFromCache() {
    $pdo = getDatabase();
    $exists = checkTableExistsLocal($pdo, 'mpsm_cache_devices');
    if (!$exists) {
        return [];
    }

    $stmt = $pdo->query("
        SELECT
            customer_code AS CustomerCode,
            COALESCE(
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerName')),
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.CustomerDescription')),
                customer_code
            ) AS CustomerName,
            serial_number AS SerialNumber,
            COALESCE(
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Manufacturer')),
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Make')),
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Vendor')),
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Brand'))
            ) AS Manufacturer
        FROM mpsm_cache_devices
        WHERE is_uninstalled = 0
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function checkTableExistsLocal(PDO $pdo, string $table): bool {
    try {
        $quoted = $pdo->quote($table);
        $stmt = $pdo->query("SHOW TABLES LIKE {$quoted}");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function attemptAgeDecode(string $manufacturer, string $serial, DateTimeImmutable $now, ?string &$reason): ?float {
    $manufacturer = trim($manufacturer);
    $serial = trim($serial);

    if ($serial === '') {
        $reason = 'missing_serial';
        return null;
    }

    if ($manufacturer === '') {
        $reason = 'missing_manufacturer';
        return null;
    }

    $manufactureDate = decodeManufactureDate($manufacturer, $serial, $reason);
    if (!$manufactureDate) {
        if ($reason === null) {
            $reason = 'decode_failed';
        }
        return null;
    }

    $interval = $manufactureDate->diff($now);
    $years = $interval->y + ($interval->m / 12) + ($interval->d / 365.25);
    return round($years, 2);
}

function calculateAgeYears(string $manufacturer, string $serial, DateTimeImmutable $now): ?float {
    $manufactureDate = decodeManufactureDate($manufacturer, $serial);
    if (!$manufactureDate) {
        return null;
    }

    $interval = $manufactureDate->diff($now);
    $years = $interval->y + ($interval->m / 12) + ($interval->d / 365.25);
    return round($years, 2);
}

function decodeManufactureDate(string $manufacturer, string $serial, ?string &$reason = null): ?DateTimeImmutable {
    $serial = trim($serial);
    if ($serial === '') {
        $reason = 'missing_serial';
        return null;
    }

    $manu = strtoupper($manufacturer);
    switch (true) {
        case str_starts_with($manu, 'HP'):
            return decodeHpDate($serial);
        case str_contains($manu, 'BROTHER'):
            return decodeBrotherDate($serial);
        case str_contains($manu, 'RICOH'):
            return decodeRicohDate($serial);
        case str_contains($manu, 'KONICA'):
        case str_contains($manu, 'MINOLTA'):
            return decodeKonicaDate($serial);
        case str_contains($manu, 'SHARP'):
            return decodeSharpDate($serial);
        default:
            $reason = 'unsupported_manufacturer';
            return null;
    }
}

function decodeHpDate(string $serial): ?DateTimeImmutable {
    if (strlen($serial) < 6) {
        return null;
    }

    $yearDigit = $serial[3];
    $week = substr($serial, 4, 2);
    if (!ctype_digit($week)) {
        return null;
    }

    $year = resolveYearFromDigit($yearDigit);
    $weekNum = (int)$week;
    if ($weekNum < 1 || $weekNum > 52) {
        return null;
    }

    $date = new DateTimeImmutable();
    $date = $date->setISODate($year, $weekNum, 1);
    return $date ?: null;
}

function decodeBrotherDate(string $serial): ?DateTimeImmutable {
    if (strlen($serial) < 8) {
        return null;
    }

    $monthChar = $serial[6];
    $yearDigit = $serial[7];

    $month = letterToMonthBrother($monthChar);
    if ($month === null) {
        return null;
    }

    $year = resolveYearFromDigit($yearDigit);
    return DateTimeImmutable::createFromFormat('Y-n-j', "{$year}-{$month}-1") ?: null;
}

function decodeRicohDate(string $serial): ?DateTimeImmutable {
    if (strlen($serial) < 6) {
        return null;
    }

    $yearDigit = $serial[3];
    $monthChar = $serial[5];

    $month = digitOrLetterToMonthRicoh($monthChar);
    if ($month === null) {
        return null;
    }

    $year = resolveYearFromDigit($yearDigit);
    return DateTimeImmutable::createFromFormat('Y-n-j', "{$year}-{$month}-1") ?: null;
}

function decodeKonicaDate(string $serial): ?DateTimeImmutable {
    if (strlen($serial) < 3) {
        return null;
    }

    $yearDigit = $serial[2];
    $monthPart = substr($serial, 0, 2);
    if (!ctype_digit($monthPart)) {
        return null;
    }

    $month = (int)$monthPart;
    if ($month < 1 || $month > 12) {
        return null;
    }

    $year = resolveYearFromDigit($yearDigit);
    return DateTimeImmutable::createFromFormat('Y-n-j', "{$year}-{$month}-1") ?: null;
}

function decodeSharpDate(string $serial): ?DateTimeImmutable {
    if (strlen($serial) < 3) {
        return null;
    }

    $yearDigit = $serial[0];
    $monthPart = substr($serial, 1, 2);
    if (!ctype_digit($monthPart)) {
        return null;
    }

    $month = (int)$monthPart;
    if ($month < 1 || $month > 12) {
        return null;
    }

    $year = resolveYearFromDigit($yearDigit);
    return DateTimeImmutable::createFromFormat('Y-n-j', "{$year}-{$month}-1") ?: null;
}

function resolveYearFromDigit(string $digit): int {
    $currentYear = (int)date('Y');
    $d = (int)$digit;
    $candidate = (int)(floor($currentYear / 10) * 10 + $d);
    if ($candidate > $currentYear) {
        $candidate -= 10;
    }
    return $candidate;
}

function letterToMonthBrother(string $char): ?int {
    $map = [
        'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4,
        'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8,
        'I' => 9, 'J' => 10, 'K' => 11, 'L' => 12
    ];
    $upper = strtoupper($char);
    return $map[$upper] ?? null;
}

function digitOrLetterToMonthRicoh(string $char): ?int {
    $upper = strtoupper($char);
    if (ctype_digit($upper)) {
        $month = (int)$upper;
        return ($month >= 1 && $month <= 9) ? $month : null;
    }

    $map = ['A' => 10, 'B' => 11, 'C' => 12];
    return $map[$upper] ?? null;
}

function getAgeBucket(?float $ageYears): string {
    if ($ageYears === null) {
        return 'unknown';
    }
    if ($ageYears < 1) {
        return 'under1';
    }
    if ($ageYears < 3) {
        return 'oneTo3';
    }
    if ($ageYears < 5) {
        return 'threeTo5';
    }
    return 'over5';
}

/*
CHANGELOG
2025-12-03 Codex
- Added device-age-report.php to calculate device age from serial numbers per manufacturer rules and emit age-bucket metrics per customer and overall using live API pagination only.
2025-12-03 Codex
- Added query-endpoint fallback, cache fallback, manufacturer extraction from Product.Brand, and diagnostics (source/warnings/unknownReasons) to handle empty cache/API responses and surface decode coverage.
*/
