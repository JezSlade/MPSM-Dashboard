<?php
/**
 * Device Age Report
 * Processes all devices via MPS API, decodes manufacture dates from serials,
 * and emits age-bucket metrics per customer and overall.
 */

require '../config.php';
require '../functions.php';

// Optional secret for programmatic access (matches dealer API secret)
$bypassSecret = 'DEALER_API_2025';
$providedSecret = $_GET['secret'] ?? $_POST['secret'] ?? '';
$authBypassed = ($providedSecret === $bypassSecret);

if (!$authBypassed) {
    requireAuth();
}

header('Content-Type: application/json');

try {
    $forceRefresh = isset($_GET['force']) && $_GET['force'] === '1';
    $report = buildDeviceAgeReport($forceRefresh);
    jsonSuccess($report);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function buildDeviceAgeReport(bool $forceRefresh = false) {
    $warnings = [];
    $source = 'cache';
    $cacheAgeSeconds = null;

    // Prefer cache for completeness; fallback to live query/API
    if (!$forceRefresh) {
        $devices = fetchDevicesFromCache($cacheAgeSeconds);
    } else {
        $devices = [];
    }

    if (empty($devices)) {
        $warnings[] = 'Cache empty or force refresh requested; using live query endpoint';
        $source = 'live-query';
        $devices = fetchAllDevicesViaQuery();
    }

    if (empty($devices)) {
        $warnings[] = 'Query endpoint returned no devices; falling back to OAuth helper';
        $source = 'live-api';
        $devices = fetchAllDevices();
    }

    if (empty($devices)) {
        $warnings[] = 'No devices available from cache or live API; cannot compute age metrics';
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
    $deviceCountWithIp = 0;

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

        // Prefer install date if available; fall back to serial decode
        $installRaw = $device['Install'] ?? $device['install'] ?? $device['InstallDate'] ?? null;
        $ageYears = null;
        $reason = null;

        if ($installRaw) {
            try {
                $installDate = new DateTimeImmutable($installRaw);
                $interval = $installDate->diff($now);
                $ageYears = round($interval->y + ($interval->m / 12) + ($interval->d / 365.25), 2);
            } catch (Exception $e) {
                // Ignore parse errors and fall back to serial decode
                $ageYears = null;
            }
        }

        if ($ageYears === null) {
            $ageYears = attemptAgeDecode($manufacturer, $serial, $now, $reason);
        }
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
        'cache_age_seconds' => $cacheAgeSeconds,
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
            'DealerCode' => $dealerCode,
            'FilterDealerCodes' => $dealerCode ? [$dealerCode] : null,
            'PageNumber' => $page,
            'PageRows' => $pageRows,
            'SortColumn' => 'SerialNumber'
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
            'DealerCode' => $dealerCode,
            'FilterDealerCodes' => $dealerCode ? [$dealerCode] : null,
            'PageNumber' => $page,
            'PageRows' => $pageRows,
            'SortColumn' => 'SerialNumber'
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

function fetchDevicesFromCache(?int &$cacheAgeSeconds = null) {
    $pdo = getDatabase();
    $exists = checkTableExistsLocal($pdo, 'mpsm_cache_devices');
    if (!$exists) {
        return [];
    }

    try {
        $ageRow = $pdo->query("SELECT TIMESTAMPDIFF(SECOND, MAX(cached_at), NOW()) AS newest_age FROM mpsm_cache_devices")->fetch(PDO::FETCH_ASSOC);
        $cacheAgeSeconds = isset($ageRow['newest_age']) ? (int)$ageRow['newest_age'] : null;
    } catch (Exception $e) {
        $cacheAgeSeconds = null;
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
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Brand')),
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Product.Brand'))
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
            return decodeHpDate($serial, $reason);
        case str_contains($manu, 'BROTHER'):
            return decodeBrotherDate($serial, $reason);
        case str_contains($manu, 'RICOH'):
            return decodeRicohDate($serial, $reason);
        case str_contains($manu, 'KONICA'):
        case str_contains($manu, 'MINOLTA'):
            return decodeKonicaDate($serial, $reason);
        case str_contains($manu, 'SHARP'):
            return decodeSharpDate($serial, $reason);
        default:
            $reason = 'unsupported_manufacturer';
            return null;
    }
}

function decodeHpDate(string $serial, ?string &$reason = null): ?DateTimeImmutable {
    // HP 10-character format: CCVYWWZZZZ
    // CC = Country code (2 chars)
    // V = Vendor/revision (1 char)
    // Y = Year last digit (1 char)
    // WW = Week of year (2 digits)
    // ZZZZ = Unique identifier (4 chars)

    if (strlen($serial) < 6) {
        $reason = 'hp_serial_too_short';
        return null;
    }

    $yearDigit = $serial[3];
    $week = substr($serial, 4, 2);

    if (!ctype_digit($yearDigit)) {
        $reason = 'hp_year_not_digit';
        return null;
    }

    if (!ctype_digit($week)) {
        $reason = 'hp_week_not_digit';
        return null;
    }

    $year = resolveYearFromDigit($yearDigit);
    $weekNum = (int)$week;

    if ($weekNum < 1 || $weekNum > 53) {
        $reason = 'hp_invalid_week';
        return null;
    }

    try {
        $date = new DateTimeImmutable();
        $date = $date->setISODate($year, $weekNum, 1);
        return $date ?: null;
    } catch (Exception $e) {
        $reason = 'hp_date_creation_failed';
        return null;
    }
}

function decodeBrotherDate(string $serial, ?string &$reason = null): ?DateTimeImmutable {
    if (strlen($serial) < 8) {
        $reason = 'brother_serial_too_short';
        return null;
    }

    $monthChar = $serial[6];
    $yearDigit = $serial[7];

    $month = letterToMonthBrother($monthChar);
    if ($month === null) {
        $reason = 'brother_invalid_month_char';
        return null;
    }

    $year = resolveYearFromDigit($yearDigit);
    return DateTimeImmutable::createFromFormat('Y-n-j', "{$year}-{$month}-1") ?: null;
}

function decodeRicohDate(string $serial, ?string &$reason = null): ?DateTimeImmutable {
    // Ricoh format (post-2011): Position 3=year, Position 4=month/factory code
    // Example: V839... = 2009

    if (strlen($serial) < 5) {
        $reason = 'ricoh_serial_too_short';
        return null;
    }

    $yearDigit = $serial[3];

    if (!ctype_digit($yearDigit)) {
        $reason = 'ricoh_year_not_digit';
        return null;
    }

    // Position 4 can encode month info, but format varies
    // For now, use year only and set to January
    $year = resolveYearFromDigit($yearDigit);

    return DateTimeImmutable::createFromFormat('Y-n-j', "{$year}-1-1") ?: null;
}

function decodeKonicaDate(string $serial, ?string &$reason = null): ?DateTimeImmutable {
    // Konica Minolta format: First 2 digits = month, 3rd digit = year
    // Example: 115... = November 2015

    if (strlen($serial) < 3) {
        $reason = 'konica_serial_too_short';
        return null;
    }

    $monthPart = substr($serial, 0, 2);
    $yearDigit = $serial[2];

    if (!ctype_digit($monthPart)) {
        $reason = 'konica_month_not_digit';
        return null;
    }

    if (!ctype_digit($yearDigit)) {
        $reason = 'konica_year_not_digit';
        return null;
    }

    $month = (int)$monthPart;
    if ($month < 1 || $month > 12) {
        $reason = 'konica_invalid_month';
        return null;
    }

    $year = resolveYearFromDigit($yearDigit);
    return DateTimeImmutable::createFromFormat('Y-n-j', "{$year}-{$month}-1") ?: null;
}

function decodeSharpDate(string $serial, ?string &$reason = null): ?DateTimeImmutable {
    if (strlen($serial) < 3) {
        $reason = 'sharp_serial_too_short';
        return null;
    }

    $yearDigit = $serial[0];
    $monthPart = substr($serial, 1, 2);

    if (!ctype_digit($yearDigit)) {
        $reason = 'sharp_year_not_digit';
        return null;
    }

    if (!ctype_digit($monthPart)) {
        $reason = 'sharp_month_not_digit';
        return null;
    }

    $month = (int)$monthPart;
    if ($month < 1 || $month > 12) {
        $reason = 'sharp_invalid_month';
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
2025-12-03 Codex
- Added secret bypass and force flag, made cache-first (with cache age), then live-query, then API fallback; added install-date preference for age math and vendor DealerCode filters for accuracy.
2025-12-03 Claude
- CRITICAL FIX: Corrected HP serial number decoder to use proper 10-char format (CCVYWWZZZZ)
- Position 3 = Year digit (not letters), Position 4-5 = Week of year (00-53)
- Added detailed error reasons for all manufacturer decoders (hp_year_not_digit, konica_invalid_month, etc)
- Updated Ricoh decoder to post-2011 format (position 3 = year digit)
- Enhanced all decode functions to pass reason parameter for better diagnostics
- Based on research: HP uses country code + vendor + year + week format for PageWide/Enterprise printers
*/
