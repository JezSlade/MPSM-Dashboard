<?php
/**
 * Pure PHP coverage for the OEM toner catalog resolver.
 */

require_once dirname(__DIR__, 2) . '/cms/api/toner-catalog.php';

$failures = [];
$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        $failures[] = $message;
    }
};
$partNumbers = static function (array $result, string $color): array {
    $parts = array_values(array_map(
        static fn(array $option): string => (string)$option['partNumber'],
        $result['colors'][$color] ?? []
    ));
    sort($parts, SORT_NATURAL | SORT_FLAG_CASE);
    return $parts;
};

$hp = resolveTonerPartNumbers('HP', 'Color LaserJet Enterprise M555dn');
$assert($hp['matched'] === true, 'HP M555dn should match');
$assert($partNumbers($hp, 'black') === ['W2120A', 'W2120X'], 'HP M555dn should return both black yields');
$assert($partNumbers($hp, 'cyan') === ['W2121A', 'W2121X'], 'HP M555dn should return both cyan yields');
$assert($partNumbers($hp, 'magenta') === ['W2123A', 'W2123X'], 'HP M555dn should return both magenta yields');
$assert($partNumbers($hp, 'yellow') === ['212A', 'W2122A', 'W2122X'], 'HP M555dn should return every compatible yellow option');

$hpPrefixed = resolveTonerPartNumbers('Hewlett-Packard', 'HP Color LaserJet Enterprise M555dn');
$assert($hpPrefixed['matched'] === true, 'Brand-prefixed HP model should match exactly after prefix removal');

$konica = resolveTonerPartNumbers('Konica Minolta', 'bizhub C558');
$assert($partNumbers($konica, 'black') === ['TN514K'], 'Konica C558 black part should match');
$assert($partNumbers($konica, 'cyan') === ['TN514C'], 'Konica C558 cyan part should match');
$assert($partNumbers($konica, 'magenta') === ['TN514M'], 'Konica C558 magenta part should match');
$assert($partNumbers($konica, 'yellow') === ['TN514Y'], 'Konica C558 yellow part should match');

$ricoh = resolveTonerPartNumbers('Ricoh', 'MP 4054');
$assert($partNumbers($ricoh, 'black') === ['842126'], 'Ricoh MP 4054 black part should match');

$safetyRow = resolveTonerPartNumbers('Ricoh', '4053');
$assert($safetyRow['matched'] === false, 'Verify-exact-model safety row must not resolve');
$assert(resolveTonerPartNumbers('Unknown', 'M555dn')['matched'] === false, 'Unsupported brand must not cross-match');
$assert(resolveTonerPartNumbers('HP', 'No Such Model')['matched'] === false, 'Unknown model must remain unmatched');

if ($failures) {
    fwrite(STDERR, "OEM toner catalog tests failed:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OEM toner catalog tests passed.\n";
exit(0);

/*
CHANGELOG
2026-11-25 Codex
- Added exact alias, multiple-yield, brand isolation, and no-match coverage.
*/
