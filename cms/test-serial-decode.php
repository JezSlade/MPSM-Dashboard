<?php
/**
 * Test Serial Number Decoding
 * Diagnose why device age decoding is failing
 */

require 'config.php';
require 'functions.php';

// Sample serials we know exist
$testCases = [
    ['HP', 'MXBCL7T1BY'],
    ['HP', 'MXBCLCX0YN'],
];

echo "=== Serial Number Decode Diagnostics ===\n\n";

// Test the decoding functions
foreach ($testCases as $test) {
    list($mfg, $serial) = $test;
    echo "Testing: $mfg - $serial\n";

    // Try HP decode directly
    $reason = null;
    $date = decodeManufactureDate($mfg, $serial, $reason);

    if ($date) {
        echo "  SUCCESS: {$date->format('Y-m-d')}\n";
        $now = new DateTimeImmutable();
        $interval = $date->diff($now);
        $years = $interval->y + ($interval->m / 12);
        echo "  Age: " . round($years, 1) . " years\n";
    } else {
        echo "  FAILED: $reason\n";
    }
    echo "\n";
}

// Now get real samples from cache
echo "\n=== Real Cache Sample ===\n\n";
$pdo = getDatabase();
$stmt = $pdo->query("
    SELECT
        serial_number,
        COALESCE(
            JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Manufacturer')),
            JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Make')),
            JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Vendor')),
            JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Product.Brand'))
        ) AS manufacturer,
        JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Model')) as model
    FROM mpsm_cache_devices
    WHERE is_uninstalled = 0
    LIMIT 20
");

$stats = [
    'total' => 0,
    'success' => 0,
    'failed' => 0,
    'reasons' => []
];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stats['total']++;
    $mfg = $row['manufacturer'] ?? 'UNKNOWN';
    $serial = $row['serial_number'] ?? '';
    $model = $row['model'] ?? '';

    echo "Device: $model\n";
    echo "  Mfg: $mfg\n";
    echo "  Serial: $serial\n";

    $reason = null;
    $date = decodeManufactureDate($mfg, $serial, $reason);

    if ($date) {
        $stats['success']++;
        $now = new DateTimeImmutable();
        $interval = $date->diff($now);
        $years = $interval->y + ($interval->m / 12);
        echo "  SUCCESS: {$date->format('Y-m-d')} (Age: " . round($years, 1) . " years)\n";
    } else {
        $stats['failed']++;
        echo "  FAILED: $reason\n";
        $stats['reasons'][$reason] = ($stats['reasons'][$reason] ?? 0) + 1;
    }
    echo "\n";
}

echo "\n=== Summary ===\n";
echo "Total tested: {$stats['total']}\n";
echo "Successful: {$stats['success']}\n";
echo "Failed: {$stats['failed']}\n";
echo "Success rate: " . round(($stats['success'] / max($stats['total'], 1)) * 100, 1) . "%\n";
echo "\nFailure reasons:\n";
foreach ($stats['reasons'] as $reason => $count) {
    echo "  $reason: $count\n";
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
    if ($weekNum < 1 || $weekNum > 52) {
        $reason = 'hp_invalid_week';
        return null;
    }

    $date = new DateTimeImmutable();
    $date = $date->setISODate($year, $weekNum, 1);
    return $date ?: null;
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
    if (strlen($serial) < 6) {
        $reason = 'ricoh_serial_too_short';
        return null;
    }

    $yearDigit = $serial[3];
    $monthChar = $serial[5];

    $month = digitOrLetterToMonthRicoh($monthChar);
    if ($month === null) {
        $reason = 'ricoh_invalid_month_char';
        return null;
    }

    $year = resolveYearFromDigit($yearDigit);
    return DateTimeImmutable::createFromFormat('Y-n-j', "{$year}-{$month}-1") ?: null;
}

function decodeKonicaDate(string $serial, ?string &$reason = null): ?DateTimeImmutable {
    if (strlen($serial) < 3) {
        $reason = 'konica_serial_too_short';
        return null;
    }

    $yearDigit = $serial[2];
    $monthPart = substr($serial, 0, 2);
    if (!ctype_digit($monthPart)) {
        $reason = 'konica_month_not_digit';
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
