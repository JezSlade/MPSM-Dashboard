<?php
/**
 * Lightweight verification that docs/MPSM_Code_Descriptions.md parses into expected code/name pairs.
 */

$file = dirname(__DIR__) . '/docs/MPSM_Code_Descriptions.md';

if (!is_readable($file)) {
    fwrite(STDERR, "File not readable: {$file}\n");
    exit(1);
}

$map = [];
$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    $trim = trim($line);
    if ($trim === '' || stripos($trim, 'code') === 0) {
        continue;
    }
    $parts = preg_split('/\s+/', $trim, 2);
    if (count($parts) === 2) {
        $map[$parts[0]] = $parts[1];
    }
}

$expected = [
    '8' => 'Jam',
    '807' => 'Input Media Supply Low',
    '808' => 'Input Media Supply Empty',
    '809' => 'Input Media Change Request',
];

$failures = [];
foreach ($expected as $code => $name) {
    if (!isset($map[$code])) {
        $failures[] = "Missing code {$code}";
        continue;
    }
    if (trim($map[$code]) !== $name) {
        $failures[] = "Code {$code} expected '{$name}' got '{$map[$code]}'";
    }
}

if ($failures) {
    fwrite(STDERR, "Alert code map failures:\n - " . implode("\n - ", $failures) . "\n");
    exit(1);
}

echo "Alert code map OK (" . count($expected) . " codes validated)\n";

/*
CHANGELOG
2025-11-25 Codex
- Added parser test for docs/MPSM_Code_Descriptions.md covering core alert codes (8, 807, 808, 809).
*/
