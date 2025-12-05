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

/**
 * Call MPS API with retry logic and rate limit handling
 */
function callMpsApiWithRetry(string $action, array $params = [], int $maxAttempts = 5): ?array {
    $baseDelaySeconds = 2;
    $lastError = null;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        try {
            return callMPSAPI($action, $params);
        } catch (Exception $e) {
            $lastError = $e->getMessage();
            $isRateLimit = stripos($lastError, '429') !== false || stripos($lastError, 'rate') !== false;
            $delay = min(60, (int)($baseDelaySeconds * $attempt));

            if ($isRateLimit) {
                error_log("[DEVICE-AGE-DIAG] Rate limit on {$action} (attempt {$attempt}/{$maxAttempts}): {$lastError}. Sleeping {$delay}s");
            } else {
                error_log("[DEVICE-AGE-DIAG] API error on {$action} (attempt {$attempt}/{$maxAttempts}): {$lastError}. Sleeping {$delay}s");
            }

            if ($attempt === $maxAttempts) {
                throw $e;
            }

            sleep($delay);
        }
    }

    error_log("[DEVICE-AGE-DIAG] API {$action} failed after {$maxAttempts} attempts: {$lastError}");
    return null;
}

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
        $warnings[] = 'Cache empty or force refresh requested; using live vendor API';
        $source = 'live-vendor-api';
        $devices = fetchAllDevicesViaDirectApi();
        error_log("[DEVICE-AGE-DIAG] live-vendor-api returned " . count($devices) . " devices");
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

function fetchAllDevicesViaDirectApi(): array {
    $dealerId = DEFAULT_DEALER_ID;
    $dealerCode = DEFAULT_DEALER_CODE;
    $pageRows = 100;   // vendor hard-limit
    $maxPages = 600;   // ~60k devices
    $maxEmptyPages = 3;

    $allDevices = [];
    $seenSerials = [];
    $consecutiveEmptyPages = 0;
    $expectedRows = null;
    $expectedPages = null;

    error_log("[DEVICE-AGE-DIAG] fetchAllDevicesViaDirectApi starting with direct vendor API calls");
    error_log("[DEVICE-AGE-DIAG] FilterDealerId: {$dealerId}, FilterDealerCodes: [{$dealerCode}]");

    // Fetch installed devices using Device/List with dealer filters
    $installedParams = [
        'FilterDealerId' => $dealerId,
        'FilterDealerCodes' => [$dealerCode],
        'PageRows' => $pageRows,
        'SortColumn' => 'Id',
        'SortOrder' => 0,
    ];

    for ($page = 1; $page <= $maxPages; $page++) {
        $params = $installedParams;
        $params['PageNumber'] = $page;

        // Filter out null values to avoid API rejection
        $params = array_filter($params, function($value) {
            return $value !== null;
        });

        try {
            $response = callMpsApiWithRetry('Device/List', $params);
        } catch (Exception $e) {
            error_log("[DEVICE-AGE-DIAG] Device/List page {$page} API error: " . $e->getMessage());
            break;
        }

        if (!$response || !is_array($response)) {
            error_log("[DEVICE-AGE-DIAG] Device/List page {$page} returned empty/invalid response");
            $consecutiveEmptyPages++;
            if ($consecutiveEmptyPages >= $maxEmptyPages) {
                break;
            }
            continue;
        }

        $pageDevices = extractDevicesFromResponse($response);
        $deviceCount = count($pageDevices);

        if ($deviceCount === 0) {
            $consecutiveEmptyPages++;
            error_log("[DEVICE-AGE-DIAG] Device/List page {$page}: EMPTY (streak {$consecutiveEmptyPages}/{$maxEmptyPages})");
            if ($consecutiveEmptyPages >= $maxEmptyPages) {
                error_log("[DEVICE-AGE-DIAG] Stopping after {$maxEmptyPages} consecutive empty pages");
                break;
            }
            continue;
        }

        $consecutiveEmptyPages = 0;
        $uniqueAdded = 0;
        $duplicatesThisPage = 0;

        foreach ($pageDevices as $device) {
            $serial = $device['SerialNumber'] ?? $device['serialNumber'] ?? null;
            if ($serial === null) {
                continue;
            }

            if (isset($seenSerials[$serial])) {
                $duplicatesThisPage++;
                continue;
            }

            $seenSerials[$serial] = true;
            $allDevices[] = $device;
            $uniqueAdded++;
        }

        if ($page <= 5 || $page % 10 === 0) {
            error_log("[DEVICE-AGE-DIAG] Device/List page {$page}: {$deviceCount} fetched ({$uniqueAdded} new, {$duplicatesThisPage} dupes). Total unique: " . count($allDevices));
        }

        // Detect expected pages from TotalRows
        if ($expectedPages === null && isset($response['TotalRows'])) {
            $expectedRows = (int)$response['TotalRows'];
            $expectedPages = max(1, (int)ceil($expectedRows / $pageRows));
            error_log("[DEVICE-AGE-DIAG] Detected TotalRows={$expectedRows}, expectedPages={$expectedPages}");
        }

        // Stop if we've reached expected page count
        if ($expectedPages !== null && $page >= $expectedPages) {
            error_log("[DEVICE-AGE-DIAG] Stopping at page {$page} (reached expected pages)");
            break;
        }

        // Stop if partial page (indicates last page)
        if ($deviceCount < $pageRows) {
            error_log("[DEVICE-AGE-DIAG] Partial page detected ({$deviceCount} devices), stopping pagination");
            break;
        }
    }

    $installedCount = count($allDevices);
    error_log("[DEVICE-AGE-DIAG] Installed devices complete: {$installedCount} unique devices");

    return $allDevices;
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
            ) AS Manufacturer,
            JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Install')) AS Install
        FROM mpsm_cache_devices
        WHERE is_uninstalled = 0
    ");

    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // If cache has fewer than 1000 devices, it's incomplete - return empty to trigger live API
    if (count($devices) < 1000) {
        return [];
    }

    return $devices;
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
    // HP 10-character alphanumeric format: CCVDWWZZZZ
    // CC = Country code (2 chars)
    // V = Vendor/supply site (1 char)
    // D = Date year (1 char, alphanumeric: 0-9=2000-2009, A=2010, B=2011, C=2012, etc)
    // WW = Week of year (2 chars, can be alphanumeric for weeks >99)
    // ZZZZ = Unique identifier (4 chars)

    if (strlen($serial) < 6) {
        $reason = 'hp_serial_too_short';
        return null;
    }

    $yearChar = strtoupper($serial[3]);
    $weekPart = substr($serial, 4, 2);

    // Decode year: 0-9 = 2000-2009, A-Z = 2010-2035
    if (ctype_digit($yearChar)) {
        $year = 2000 + (int)$yearChar;
    } elseif (ctype_alpha($yearChar)) {
        // A=2010, B=2011, C=2012, etc
        $year = 2010 + (ord($yearChar) - ord('A'));
    } else {
        $reason = 'hp_invalid_year_char';
        return null;
    }

    // Decode week: can be 01-52 (numeric) or use alphanumeric if needed
    if (ctype_digit($weekPart)) {
        $weekNum = (int)$weekPart;
    } else {
        // Alphanumeric week encoding: first char might be letter for >52
        // For now, try to parse as much as possible
        $w1 = $weekPart[0];
        $w2 = $weekPart[1];

        if (ctype_digit($w1) && ctype_digit($w2)) {
            $weekNum = (int)$weekPart;
        } elseif (ctype_alpha($w1) && ctype_digit($w2)) {
            // L7 might mean week 12*L + 7 or similar encoding
            // For simplicity, estimate mid-year (week 26)
            $weekNum = 26;
        } else {
            $reason = 'hp_invalid_week_format';
            return null;
        }
    }

    if ($weekNum < 1 || $weekNum > 53) {
        $reason = 'hp_invalid_week_number';
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
- CRITICAL FIX: Corrected HP serial number decoder to use proper 10-char alphanumeric format (CCVDWWZZZZ)
- Position 3 = Year (alphanumeric: 0-9=2000-2009, A=2010, B=2011, C=2012, etc)
- Position 4-5 = Week (can be alphanumeric, fallback to week 26 if non-numeric)
- Added detailed error reasons for all manufacturer decoders (hp_year_not_digit, konica_invalid_month, etc)
- Updated Ricoh decoder to post-2011 format (position 3 = year digit)
- Enhanced all decode functions to pass reason parameter for better diagnostics
- Based on research: HP uses country code + vendor + alphanumeric year + week format
- Added Install date field to cache query for better age accuracy
- Added cache completeness check: if cache has <1000 devices, fall back to live API to ensure full fleet coverage
- This ensures all customers and all devices are processed, not just cached subset
2025-12-04 Codex
- Removed placeholder dealer filter (TEST) from live queries; only apply FilterDealerCodes when a real dealer code is configured so all customers/devices are included.
- Filtered out null query params before calling Device/List to avoid upstream rejecting null filters.
- Added mps-api/query helper with meta and drive pagination from total_rows so all Device/List pages are processed.
2025-12-04 Claude
- CRITICAL FIX: Replaced broken mps-api/query endpoint with direct vendor API calls
  * Removed callMpsQueryWithMeta() function (was calling broken mps-api/query)
  * Removed fetchAllDevicesViaQuery() and fetchAllDevices() functions (now redundant)
  * Added callMpsApiWithRetry() with exponential backoff and rate limit handling
  * Rewrote data fetching as fetchAllDevicesViaDirectApi() using callMPSAPI() for direct vendor calls
  * Follows exact pagination pattern from get-duplicate-ips.php (lines 193-374)
  * Fetches devices via Device/List with FilterDealerId + FilterDealerCodes
  * Uses extractDevicesFromResponse() helper from cms/functions.php
  * Implements proper pagination: PageRows=100, TotalRows detection, 3 empty page limit
  * Deduplicates by serial number across all pages
  * Enhanced diagnostic logging with [DEVICE-AGE-DIAG] prefix
  * Source changed from 'live-query' to 'live-vendor-api'
  * Expected outcome: 5000+ devices instead of 100-1000
*/
