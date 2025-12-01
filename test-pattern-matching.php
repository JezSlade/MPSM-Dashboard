<?php
/**
 * Pattern Matching Test Suite
 * Tests the matchesPattern() function from command-center-engine.php
 */

// Simulate the matchesPattern function
function matchesPattern(string $value, string $pattern): bool
{
    // Escape regex special characters first to prevent ReDoS
    $pattern = preg_quote($pattern, '/');

    // Convert escaped SQL LIKE wildcards to regex
    $pattern = str_replace('\\%', '.*', $pattern);
    $pattern = str_replace('\\_', '.', $pattern);
    $pattern = '/^' . $pattern . '$/i';

    return (bool)preg_match($pattern, $value);
}

// Test cases
$tests = [
    // JAM pattern tests
    ['value' => 'JAM-001', 'pattern' => 'JAM%', 'expected' => true, 'desc' => 'JAM% should match JAM-001'],
    ['value' => 'JAM-002', 'pattern' => 'JAM%', 'expected' => true, 'desc' => 'JAM% should match JAM-002'],
    ['value' => 'JAMMING', 'pattern' => 'JAM%', 'expected' => true, 'desc' => 'JAM% should match JAMMING'],
    ['value' => 'PREJAM', 'pattern' => 'JAM%', 'expected' => false, 'desc' => 'JAM% should NOT match PREJAM'],
    ['value' => 'jam-001', 'pattern' => 'JAM%', 'expected' => true, 'desc' => 'JAM% should match jam-001 (case insensitive)'],

    // Emergency stop tests
    ['value' => 'E-001', 'pattern' => 'E-%', 'expected' => true, 'desc' => 'E-% should match E-001'],
    ['value' => 'E-STOP', 'pattern' => 'E-%', 'expected' => true, 'desc' => 'E-% should match E-STOP'],
    ['value' => 'ESTOP', 'pattern' => 'E-%', 'expected' => false, 'desc' => 'E-% should NOT match ESTOP (no hyphen)'],

    // Sensor tests
    ['value' => 'SENS-001', 'pattern' => 'SENS%', 'expected' => true, 'desc' => 'SENS% should match SENS-001'],
    ['value' => 'SENSOR-FAIL', 'pattern' => 'SENS%', 'expected' => true, 'desc' => 'SENS% should match SENSOR-FAIL'],

    // Communication tests
    ['value' => 'COMM-LOSS', 'pattern' => 'COMM%', 'expected' => true, 'desc' => 'COMM% should match COMM-LOSS'],
    ['value' => 'COMM-ERROR', 'pattern' => 'COMM%', 'expected' => true, 'desc' => 'COMM% should match COMM-ERROR'],

    // Motor tests
    ['value' => 'MOT-001', 'pattern' => 'MOT%', 'expected' => true, 'desc' => 'MOT% should match MOT-001'],
    ['value' => 'MOTOR-OVERLOAD', 'pattern' => 'MOT%', 'expected' => true, 'desc' => 'MOT% should match MOTOR-OVERLOAD'],

    // Exact match tests
    ['value' => 'E-001', 'pattern' => 'E-001', 'expected' => true, 'desc' => 'Exact match should work'],
    ['value' => 'E-002', 'pattern' => 'E-001', 'expected' => false, 'desc' => 'Exact match should NOT match different code'],

    // Underscore wildcard tests
    ['value' => 'E-001', 'pattern' => 'E-00_', 'expected' => true, 'desc' => 'E-00_ should match E-001 (underscore = single char)'],
    ['value' => 'E-002', 'pattern' => 'E-00_', 'expected' => true, 'desc' => 'E-00_ should match E-002'],
    ['value' => 'E-0010', 'pattern' => 'E-00_', 'expected' => false, 'desc' => 'E-00_ should NOT match E-0010 (extra char)'],

    // Edge cases
    ['value' => '', 'pattern' => '%', 'expected' => true, 'desc' => '% should match empty string'],
    ['value' => 'ANYTHING', 'pattern' => '%', 'expected' => true, 'desc' => '% should match any string'],
    ['value' => 'TEST', 'pattern' => '', 'expected' => false, 'desc' => 'Empty pattern should NOT match non-empty value'],

    // ReDoS protection tests (should not hang)
    ['value' => 'AAAAAAAAAAAAAAAAAAAAAAAAA', 'pattern' => '(A%)*', 'expected' => false, 'desc' => 'ReDoS pattern should be escaped and fail to match'],
    ['value' => 'TEST-001', 'pattern' => 'TEST-[0-9]+', 'expected' => false, 'desc' => 'Regex special chars should be escaped'],
];

echo "=== Pattern Matching Test Suite ===\n\n";

$passed = 0;
$failed = 0;

foreach ($tests as $i => $test) {
    $result = matchesPattern($test['value'], $test['pattern']);
    $status = ($result === $test['expected']) ? 'PASS' : 'FAIL';

    if ($status === 'PASS') {
        $passed++;
        echo "✓ ";
    } else {
        $failed++;
        echo "✗ ";
    }

    echo "Test " . ($i + 1) . ": {$test['desc']}\n";
    echo "  Value: '{$test['value']}', Pattern: '{$test['pattern']}'\n";
    echo "  Expected: " . ($test['expected'] ? 'true' : 'false') . ", Got: " . ($result ? 'true' : 'false') . "\n";

    if ($status === 'FAIL') {
        echo "  ⚠️  MISMATCH DETECTED\n";
    }

    echo "\n";
}

echo "=== Summary ===\n";
echo "Total: " . count($tests) . "\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "\n";

if ($failed === 0) {
    echo "✅ All tests passed! Pattern matching is working correctly.\n";
    exit(0);
} else {
    echo "❌ Some tests failed. Review pattern matching logic.\n";
    exit(1);
}
