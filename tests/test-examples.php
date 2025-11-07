<?php
/**
 * Test Examples
 * Example test cases for validating refactored code
 *
 * This file provides example tests that can be run to validate
 * the refactored architecture works correctly.
 *
 * Usage: php tests/test-examples.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Test counter
$passed = 0;
$failed = 0;

/**
 * Test helper functions
 */
function test(string $name, callable $test): void
{
    global $passed, $failed;

    try {
        $test();
        echo "✓ PASS: {$name}\n";
        $passed++;
    } catch (Exception $e) {
        echo "✗ FAIL: {$name}\n";
        echo "  Error: {$e->getMessage()}\n";
        $failed++;
    }
}

function assert_true($condition, $message = 'Assertion failed')
{
    if (!$condition) {
        throw new Exception($message);
    }
}

function assert_equals($expected, $actual, $message = 'Values not equal')
{
    if ($expected !== $actual) {
        throw new Exception("{$message}. Expected: {$expected}, Got: {$actual}");
    }
}

function assert_not_null($value, $message = 'Value is null')
{
    if ($value === null) {
        throw new Exception($message);
    }
}

echo "=== MPSM Dashboard Refactor Tests ===\n\n";

// ============================================================================
// Configuration Tests
// ============================================================================

echo "--- Configuration Tests ---\n";

test('Config helper function works', function() {
    $appName = config('app.name');
    assert_not_null($appName, 'App name should be configured');
    assert_equals('MPS Monitor Dashboard', $appName);
});

test('Feature flag helper works', function() {
    $deviceCrud = feature('device_crud');
    assert_true(is_bool($deviceCrud), 'Feature flag should return boolean');
});

test('App helper returns container', function() {
    $container = app();
    assert_true($container instanceof ServiceContainer);
});

// ============================================================================
// Service Container Tests
// ============================================================================

echo "\n--- Service Container Tests ---\n";

test('PDO service registered', function() {
    $pdo = app(PDO::class);
    assert_true($pdo instanceof PDO);
});

test('CacheInterface service registered', function() {
    $cache = app(CacheInterface::class);
    assert_true($cache instanceof CacheInterface);
});

test('Singleton pattern works', function() {
    $pdo1 = app(PDO::class);
    $pdo2 = app(PDO::class);
    assert_true($pdo1 === $pdo2, 'Same instance should be returned');
});

// ============================================================================
// Repository Tests
// ============================================================================

echo "\n--- Repository Tests ---\n";

test('DeviceRepository instantiates', function() {
    $repo = app(DeviceRepository::class);
    assert_true($repo instanceof DeviceRepository);
});

test('PanelMessageRepository instantiates', function() {
    $repo = app(PanelMessageRepository::class);
    assert_true($repo instanceof PanelMessageRepository);
});

test('UserRepository instantiates', function() {
    $repo = app(UserRepository::class);
    assert_true($repo instanceof UserRepository);
});

// ============================================================================
// Cache Tests
// ============================================================================

echo "\n--- Cache Tests ---\n";

test('Cache set and get works', function() {
    $cache = app(CacheInterface::class);

    $testKey = 'test_key_' . time();
    $testValue = ['test' => 'data', 'timestamp' => time()];

    $cache->set($testKey, $testValue, 60);
    $retrieved = $cache->get($testKey);

    assert_equals($testValue['test'], $retrieved['test']);

    // Cleanup
    $cache->delete($testKey);
});

test('Cache has() method works', function() {
    $cache = app(CacheInterface::class);

    $testKey = 'test_has_' . time();
    $cache->set($testKey, 'value', 60);

    assert_true($cache->has($testKey));

    $cache->delete($testKey);
    assert_true(!$cache->has($testKey));
});

test('Cache delete works', function() {
    $cache = app(CacheInterface::class);

    $testKey = 'test_delete_' . time();
    $cache->set($testKey, 'value', 60);
    $cache->delete($testKey);

    $retrieved = $cache->get($testKey);
    assert_true($retrieved === null);
});

// ============================================================================
// Queue Tests
// ============================================================================

echo "\n--- Queue Tests ---\n";

test('QueueManager instantiates', function() {
    $queue = app(QueueManager::class);
    assert_true($queue instanceof QueueManager);
});

test('Job can be dispatched', function() {
    $queue = app(QueueManager::class);

    // Create a simple test job
    $job = new RefreshCacheJob(null, false, false);
    $jobId = $queue->dispatch($job, 0);

    assert_true($jobId > 0, 'Job ID should be positive integer');

    // Check job status
    $status = $queue->getStatus($jobId);
    assert_not_null($status, 'Job status should exist');
    assert_equals('pending', $status['status']);
});

test('Queue statistics work', function() {
    $queue = app(QueueManager::class);
    $stats = $queue->getStats('default');

    assert_true(is_array($stats));
    assert_true(isset($stats['total_jobs']));
    assert_true(isset($stats['pending_jobs']));
});

// ============================================================================
// Access Control Tests
// ============================================================================

echo "\n--- Access Control Tests ---\n";

test('Permission constants defined', function() {
    $permission = Permission::VIEW_DASHBOARD;
    assert_equals('view_dashboard', $permission);
});

test('Role has correct permissions', function() {
    $viewerPerms = Role::getPermissions(Role::VIEWER);
    assert_true(in_array(Permission::VIEW_DASHBOARD, $viewerPerms));

    $adminPerms = Role::getPermissions(Role::ADMIN);
    assert_true(in_array(Permission::MANAGE_DEVICES, $adminPerms));
    assert_true(in_array(Permission::VIEW_DASHBOARD, $adminPerms)); // Inherits from Viewer
});

test('Role hierarchy works', function() {
    assert_true(Role::isHigherThan(Role::ADMIN, Role::VIEWER));
    assert_true(Role::isHigherThan(Role::SUPER_ADMIN, Role::ADMIN));
    assert_true(!Role::isHigherThan(Role::VIEWER, Role::ADMIN));
});

test('AccessControl permission checking', function() {
    $acl = new AccessControl();

    // Set test user with admin role
    $acl->setUser(['id' => 1, 'username' => 'test', 'role' => Role::ADMIN]);

    assert_true($acl->isAdmin());
    assert_true($acl->can(Permission::MANAGE_DEVICES));
    assert_true($acl->can(Permission::VIEW_DASHBOARD)); // Inherited from Viewer
    assert_true(!$acl->can(Permission::MANAGE_ROLES)); // Super Admin only
});

// ============================================================================
// Backwards Compatibility Tests
// ============================================================================

echo "\n--- Backwards Compatibility Tests ---\n";

test('Legacy constants defined', function() {
    assert_true(defined('DB_HOST'));
    assert_true(defined('DB_NAME'));
    assert_true(defined('APP_NAME'));
    assert_true(defined('FEATURE_DEVICE_CRUD'));
});

test('Legacy helper functions exist', function() {
    assert_true(function_exists('getCachedDevices'));
    assert_true(function_exists('dispatchJob'));
    assert_true(function_exists('getJobStatus'));
});

// ============================================================================
// Test Summary
// ============================================================================

echo "\n=== Test Summary ===\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "Total:  " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✓ All tests passed!\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed. Please review.\n";
    exit(1);
}
