<?php
/**
 * Payload Sanitization Tests
 * Validates the helpers used by the panel-message callbacks to clean incoming JSON.
 *
 * Usage: php tests/test-payload-sanitization.php
 */

require_once __DIR__ . '/../mps-api/callbacks/payload-sanitizer.php';

$passed = 0;
$failed = 0;

function test(string $name, callable $fn): void
{
    global $passed, $failed;
    try {
        $fn();
        echo "✔ PASS: {$name}\n";
        $passed++;
    } catch (Exception $e) {
        echo "✖ FAIL: {$name}\n";
        echo "  → {$e->getMessage()}\n";
        $failed++;
    }
}

function assert_equals($expected, $actual, string $message = 'Values differ'): void
{
    if ($expected !== $actual) {
        throw new Exception("{$message}. Expected: " . var_export($expected, true) . ", Got: " . var_export($actual, true));
    }
}

function assert_true($condition, string $message = 'Assertion failed'): void
{
    if (!$condition) {
        throw new Exception($message);
    }
}

function decodeSanitized(string $payload): array
{
    $clean = sanitizeRawPayload($payload);
    return json_decode($clean, true, 512, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
}

echo "=== Payload Sanitization Tests ===\n\n";

test('Multi-line fields decode after sanitization', function () {
    $payload = "{\n\"message\":\"Line1\nLine2\"}";
    $decoded = decodeSanitized($payload);
    assert_equals("Line1\nLine2", $decoded['message']);
});

test('Control characters escape to unicode sequences', function () {
    $payload = "{\"foo\":\"bar\x0B\"}";
    $decoded = decodeSanitized($payload);
    assert_equals("bar\x0B", $decoded['foo']);
});

test('Unicode line separators normalize correctly', function () {
    $payload = "{\"note\":\"first\u2028second\"}";
    $decoded = decodeSanitized($payload);
    assert_true(strpos($decoded['note'], "\u{2028}") !== false, 'Line separator should remain inside decoded value');
});

test('BOM prefixes are stripped', function () {
    $payload = "\xEF\xBB\xBF{\"key\":\"value\"}";
    $decoded = decodeSanitized($payload);
    assert_equals('value', $decoded['key']);
});

echo "\n--- Summary ---\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "Total:  " . ($passed + $failed) . "\n";

if ($failed > 0) {
    echo "\n✘ Some sanitization tests failed.\n";
    exit(1);
}

echo "\n✔ All sanitization tests passed!\n";

/*
CHANGELOG
2025-11-11 Codex
- Added a standalone sanity suite that walks through multiline strings, control characters, line separators, and BOMs to prove `sanitizeRawPayload()` consistently produces decodable JSON.
*/
