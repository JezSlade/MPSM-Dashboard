<?php
/**
 * Test FIXED pattern matching implementation
 */

// CURRENT (BROKEN) implementation from engine
function matchesPatternBroken(string $value, string $pattern): bool
{
    // Escape regex special characters first to prevent ReDoS
    $pattern = preg_quote($pattern, '/');

    // Convert escaped SQL LIKE wildcards to regex
    $pattern = str_replace('\\%', '.*', $pattern);
    $pattern = str_replace('\\_', '.', $pattern);
    $pattern = '/^' . $pattern . '$/i';

    return (bool)preg_match($pattern, $value);
}

// FIXED implementation
function matchesPatternFixed(string $value, string $pattern): bool
{
    // Escape backslash first
    $pattern = str_replace('\\', '\\\\', $pattern);

    // Escape regex special chars (but NOT % and _)
    $special = ['.', '^', '$', '+', '?', '[', ']', '{', '}', '(', ')', '|', '*', '/'];
    foreach ($special as $char) {
        $pattern = str_replace($char, '\\' . $char, $pattern);
    }

    // Now convert SQL wildcards to regex
    $pattern = str_replace('%', '.*', $pattern);
    $pattern = str_replace('_', '.', $pattern);

    // Build final regex
    $pattern = '/^' . $pattern . '$/i';

    return (bool)preg_match($pattern, $value);
}

$tests = [
    ['JAM-001', 'JAM%', true],
    ['E-001', 'E-%', true],
    ['MOT-OVERLOAD', 'MOT%', true],
    ['E-001', 'E-001', true],
    ['E-001', 'E-00_', true],
    ['PREJAM', 'JAM%', false],
    ['COMM-LOSS', 'COMM%', true],
    ['SENSOR-FAIL', 'SENS%', true],
];

echo "=== BROKEN Implementation ===\n";
$brokenPassed = 0;
foreach ($tests as [$value, $pattern, $expected]) {
    $result = matchesPatternBroken($value, $pattern);
    $pass = ($result === $expected);
    if ($pass) $brokenPassed++;
    echo ($pass ? '✓' : '✗') . " $pattern matches $value: " . ($result ? 'YES' : 'NO') . " (expected: " . ($expected ? 'YES' : 'NO') . ")\n";
}
echo "Passed: $brokenPassed/" . count($tests) . "\n\n";

echo "=== FIXED Implementation ===\n";
$fixedPassed = 0;
foreach ($tests as [$value, $pattern, $expected]) {
    $result = matchesPatternFixed($value, $pattern);
    $pass = ($result === $expected);
    if ($pass) $fixedPassed++;
    echo ($pass ? '✓' : '✗') . " $pattern matches $value: " . ($result ? 'YES' : 'NO') . " (expected: " . ($expected ? 'YES' : 'NO') . ")\n";
}
echo "Passed: $fixedPassed/" . count($tests) . "\n\n";

if ($fixedPassed === count($tests)) {
    echo "✅ FIXED implementation passes all tests!\n";
} else {
    echo "❌ FIXED implementation still has issues\n";
}
