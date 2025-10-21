<?php
/**
 * Domain Seeder - Collects prerequisite data for dependent endpoints
 *
 * This class executes TIER 1 and TIER 2 endpoints to collect IDs/codes
 * needed by TIER 3, 4, and 5 endpoints.
 *
 * @version 1.0.0
 */

if (!defined('MPS_ENGINE_ACCESS')) {
    die('Direct access not permitted');
}

class DomainSeeder {
    private static $seeds = [];
    private static $engine = null;

    /**
     * Initialize with engine instance
     */
    public static function init($engine) {
        self::$engine = $engine;
        self::$seeds = [];
    }

    /**
     * Collect all domain seeds
     */
    public static function collectSeeds() {
        $startTime = microtime(true);
        $collected = [];

        // TIER 1: Seed endpoints (no dependencies)
        $tier1 = [
            'Product/GetBrands',
            'Product/GetModels',
            'Role/GetAllCapabilities',
            'Orders/GetOrderLineStatuses',
        ];

        // TIER 2: Dealer-only endpoints (provides customer codes, integration IDs, etc.)
        $tier2 = [
            'Integrations/GetJoinedCustomers',  // Get customer statistics
            'ApiClient/List',                    // Get API client IDs
            'Role/List',                         // Get role codes
            'CustomField/List',                  // Get custom field IDs
        ];

        // Execute TIER 1
        foreach ($tier1 as $action) {
            try {
                $result = self::$engine->dispatchAction($action, []);
                if ($result['success']) {
                    self::processSeedData($action, $result['data']);
                    $collected[$action] = 'success';
                } else {
                    $collected[$action] = 'failed: ' . $result['error'];
                }
            } catch (Exception $e) {
                $collected[$action] = 'exception: ' . $e->getMessage();
            }
        }

        // Execute TIER 2
        foreach ($tier2 as $action) {
            try {
                $result = self::$engine->dispatchAction($action, []);
                if ($result['success']) {
                    self::processSeedData($action, $result['data']);
                    $collected[$action] = 'success';
                } else {
                    $collected[$action] = 'failed: ' . $result['error'];
                }
            } catch (Exception $e) {
                $collected[$action] = 'exception: ' . $e->getMessage();
            }
        }

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'success' => true,
            'duration_ms' => $duration,
            'collected' => $collected,
            'seed_count' => count(self::$seeds),
            'seeds' => self::$seeds
        ];
    }

    /**
     * Process seed data and extract useful IDs/codes
     */
    private static function processSeedData($action, $data) {
        if (is_array($data)) {
            foreach ($data as $item) {
                if (is_array($item)) {
                    self::extractIds($item);
                }
            }
        } elseif (is_object($data) || is_array($data)) {
            self::extractIds((array)$data);
        }
    }

    /**
     * Extract IDs and codes from data
     */
    private static function extractIds($item) {
        // Extract customer codes
        if (isset($item['CustomerCode']) && $item['CustomerCode']) {
            self::addSeed('customerCodes', $item['CustomerCode']);
        }
        if (isset($item['Code']) && $item['Code'] && is_string($item['Code'])) {
            // Could be customer code, role code, etc.
            if (strlen($item['Code']) > 5) { // Likely a customer code
                self::addSeed('customerCodes', $item['Code']);
            }
        }

        // Extract device IDs
        if (isset($item['DeviceId']) && $item['DeviceId']) {
            self::addSeed('deviceIds', $item['DeviceId']);
        }
        if (isset($item['Id']) && $item['Id'] && is_string($item['Id'])) {
            self::addSeed('genericIds', $item['Id']);
        }

        // Extract integration IDs
        if (isset($item['IntegrationId']) && $item['IntegrationId']) {
            self::addSeed('integrationIds', $item['IntegrationId']);
        }

        // Extract role codes
        if (isset($item['RoleCode']) && $item['RoleCode']) {
            self::addSeed('roleCodes', $item['RoleCode']);
        }
    }

    /**
     * Add seed to collection
     */
    private static function addSeed($category, $value) {
        if (!isset(self::$seeds[$category])) {
            self::$seeds[$category] = [];
        }
        if (!in_array($value, self::$seeds[$category])) {
            self::$seeds[$category][] = $value;
        }
    }

    /**
     * Get a seed value for a parameter
     */
    public static function getSeedFor($paramName) {
        $paramLower = strtolower($paramName);

        // Customer codes
        if (in_array($paramLower, ['customercode', 'customer_code', 'code', 'customerid'])) {
            if (isset(self::$seeds['customerCodes']) && count(self::$seeds['customerCodes']) > 0) {
                return self::$seeds['customerCodes'][0];
            }
        }

        // Device IDs
        if (in_array($paramLower, ['deviceid', 'device_id', 'id']) && strpos(strtolower(debug_backtrace()[1]['function'] ?? ''), 'device') !== false) {
            if (isset(self::$seeds['deviceIds']) && count(self::$seeds['deviceIds']) > 0) {
                return self::$seeds['deviceIds'][0];
            }
        }

        // Generic IDs
        if ($paramLower === 'id') {
            if (isset(self::$seeds['genericIds']) && count(self::$seeds['genericIds']) > 0) {
                return self::$seeds['genericIds'][0];
            }
        }

        return null;
    }

    /**
     * Get all seeds
     */
    public static function getSeeds() {
        return self::$seeds;
    }

    /**
     * Check if seeds are available
     */
    public static function hasSeeds() {
        return count(self::$seeds) > 0;
    }
}
