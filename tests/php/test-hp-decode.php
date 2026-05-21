<?php
/**
 * Test HP Serial Decode Logic Locally
 */

// Test cases from real devices
$testSerials = [
    'MXBCL7T1BY',  // HP PAGEWIDE
    'MXBCLCX0YN',  // HP PAGEWIDE
    'MXBCL9B135',  // HP PAGEWIDE
    'MXBCL9K1FS',  // HP PAGEWIDE
];

echo "HP Serial Number Decode Test\n";
echo "============================\n\n";

foreach ($testSerials as $serial) {
    echo "Serial: $serial\n";
    echo "  Length: " . strlen($serial) . "\n";

    if (strlen($serial) >= 6) {
        $country = substr($serial, 0, 2);
        $vendor = $serial[2];
        $yearDigit = $serial[3];
        $week = substr($serial, 4, 2);

        echo "  Country: $country\n";
        echo "  Vendor: $vendor\n";
        echo "  Year digit: $yearDigit (is digit: " . (ctype_digit($yearDigit) ? 'YES' : 'NO') . ")\n";
        echo "  Week: $week (is digit: " . (ctype_digit($week) ? 'YES' : 'NO') . ")\n";

        if (ctype_digit($yearDigit) && ctype_digit($week)) {
            $year = 2020 + (int)$yearDigit;
            if ($year > 2025) {
                $year -= 10;
            }
            $weekNum = (int)$week;
            echo "  Decoded: Week $weekNum of $year\n";

            $date = new DateTimeImmutable();
            $date = $date->setISODate($year, max(1, $weekNum), 1);
            echo "  Manufacture Date: " . $date->format('Y-m-d') . "\n";

            $now = new DateTimeImmutable();
            $age = $date->diff($now);
            $ageYears = $age->y + ($age->m / 12);
            echo "  Age: " . round($ageYears, 1) . " years\n";
        } else {
            echo "  ERROR: Cannot decode - year or week not numeric\n";
        }
    }
    echo "\n";
}
