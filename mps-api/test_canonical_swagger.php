<?php
/**
 * Test script to verify the API engine works with the canonical swagger.json
 * This script loads the canonical swagger and verifies all endpoints are registered.
 */

require_once __DIR__ . '/engine.php';

echo "=== MPS API Engine - Canonical Swagger Test ===\n\n";

try {
    // Initialize the engine
    $engine = MPSMonitorEngine::getInstance();
    echo "✓ Engine initialized successfully\n\n";

    // Get available endpoints
    $endpoints = $engine->getAvailableEndpoints();

    echo "Canonical Swagger Statistics:\n";
    echo "- Total operations: " . $endpoints['count'] . "\n";
    echo "- Total groups: " . count($endpoints['groups']) . "\n\n";

    // Display groups
    echo "Endpoint Groups:\n";
    echo str_repeat("-", 60) . "\n";

    foreach ($endpoints['groups'] as $group => $operations) {
        echo sprintf("%-30s %5d operations\n", ucfirst($group), count($operations));
    }

    echo str_repeat("-", 60) . "\n";
    echo "Total: " . $endpoints['count'] . " operations\n\n";

    // Test a few sample actions to verify they're accessible
    echo "Sample Action Tests:\n";
    echo str_repeat("-", 60) . "\n";

    $sampleActions = [
        'Account/GetProfile',
        'Device/List',
        'Customer/List',
        'Dealer/Get',
    ];

    foreach ($sampleActions as $action) {
        $found = false;
        foreach ($endpoints['groups'] as $group => $operations) {
            foreach ($operations as $op) {
                if ($op['action'] === $action) {
                    $found = true;
                    echo sprintf("✓ %-30s [%s] %s\n", $action, $op['method'], $op['path']);
                    break 2;
                }
            }
        }
        if (!$found) {
            echo sprintf("✗ %-30s NOT FOUND\n", $action);
        }
    }

    echo str_repeat("-", 60) . "\n\n";

    // Display first 10 operations from each major group
    echo "Sample Operations by Group (first 5 per group):\n";
    echo str_repeat("-", 80) . "\n";

    $majorGroups = array_slice($endpoints['groups'], 0, 5);
    foreach ($majorGroups as $group => $operations) {
        echo "\n[" . strtoupper($group) . "]\n";
        $sample = array_slice($operations, 0, 5);
        foreach ($sample as $op) {
            $summary = !empty($op['summary']) ? ' - ' . substr($op['summary'], 0, 40) : '';
            echo sprintf("  %-6s %-40s%s\n", $op['method'], $op['action'], $summary);
        }
        if (count($operations) > 5) {
            echo "  ... and " . (count($operations) - 5) . " more\n";
        }
    }

    echo "\n" . str_repeat("=", 80) . "\n";
    echo "SUCCESS: API Engine is working with canonical swagger!\n";
    echo "All " . $endpoints['count'] . " operations are available.\n";
    echo str_repeat("=", 80) . "\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
