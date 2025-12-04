<?php
/**
 * Test HP Serial Decode Logic v2 - Alphanumeric Format
 */

$testSerials = [
    'MXBCL7T1BY',  // HP PAGEWIDE
    'MXBCLCX0YN',  // HP PAGEWIDE
    'MXBCL9B135',  // HP PAGEWIDE
];

echo "HP Serial Number Decode Test (Alphanumeric)\n";
echo "==========================================\n\n";

foreach ($testSerials as $serial) {
    echo "Serial: $serial\n";

    if (strlen($serial) >= 6) {
        $country = substr($serial, 0, 2);
        $vendor = $serial[2];
        $yearChar = strtoupper($serial[3]);
        $weekPart = substr($serial, 4, 2);

        echo "  Country: $country (Mexico)\n";
        echo "  Vendor/Site: $vendor\n";
        echo "  Year char: $yearChar\n";

        // Decode year
        if (ctype_digit($yearChar)) {
            $year = 2000 + (int)$yearChar;
        } elseif (ctype_alpha($yearChar)) {
            $year = 2010 + (ord($yearChar) - ord('A'));
        } else {
            $year = null;
        }

        if ($year) {
            echo "  Decoded year: $year (C = 2010+2 = 2012)\n";
        }

        echo "  Week part: $weekPart\n";

        // Estimate week
        if (ctype_digit($weekPart)) {
            $week = (int)$weekPart;
        } else {
            // Alphanumeric - estimate mid-year
            $week = 26;
            echo "  Week is alphanumeric, using estimate: $week\n";
        }

        if ($year && $week) {
            $date = new DateTimeImmutable();
            $date = $date->setISODate($year, $week, 1);
            echo "  Manufacture Date: " . $date->format('Y-m-d') . "\n";

            $now = new DateTimeImmutable();
            $age = $date->diff($now);
            $ageYears = $age->y + ($age->m / 12);
            echo "  Age: " . round($ageYears, 1) . " years\n";
        }
    }
    echo "\n";
}
