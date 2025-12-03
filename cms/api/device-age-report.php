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
    $devices = fetchAllDevices();
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
        ]
    ];

    $customers = [];
    $ageSum = 0.0;

    foreach ($devices as $device) {
        $summary['totalDevices']++;

        $customerCode = $device['CustomerCode'] ?? $device['customerCode'] ?? 'UNKNOWN';
        $customerName = $device['CustomerName'] ?? $device['customerName'] ?? $customerCode;
        $manufacturer = strtoupper($device['Manufacturer'] ?? $device['Make'] ?? $device['make'] ?? '');
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

        $ageYears = calculateAgeYears($manufacturer, $serial, $now);
        $bucket = getAgeBucket($ageYears);

        $summary['buckets'][$bucket]++;
        $customers[$customerCode]['buckets'][$bucket]++;

        if ($ageYears !== null) {
            $summary['withAge']++;
            $customers[$customerCode]['withAge']++;
            $ageSum += $ageYears;
            $customers[$customerCode]['ageSum'] += $ageYears;
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
    }

    return [
        'generated_at' => (new DateTimeImmutable('now'))->format('c'),
        'total_devices_processed' => $summary['totalDevices'],
        'summary' => $summary,
        'customers' => array_values($customers)
    ];
}

function fetchAllDevices() {
    $devices = [];
    $pageRows = 100;
    $emptyStreak = 0;

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

function calculateAgeYears(string $manufacturer, string $serial, DateTimeImmutable $now): ?float {
    $manufactureDate = decodeManufactureDate($manufacturer, $serial);
    if (!$manufactureDate) {
        return null;
    }

    $interval = $manufactureDate->diff($now);
    $years = $interval->y + ($interval->m / 12) + ($interval->d / 365.25);
    return round($years, 2);
}

function decodeManufactureDate(string $manufacturer, string $serial): ?DateTimeImmutable {
    $serial = trim($serial);
    if ($serial === '') {
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
*/
