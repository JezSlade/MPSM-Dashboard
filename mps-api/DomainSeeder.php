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
     * OPTIMIZED: Only calls essential endpoints, fails fast
     */
    public static function collectSeeds() {
        $startTime = microtime(true);
        $collected = [];

        // MINIMAL SEED COLLECTION: Only call what's absolutely necessary
        // Priority order: fastest endpoints first, most valuable data first
        $seedEndpoints = [
            // CRITICAL: This endpoint returns actual device data with customer codes!
            // Device/Deleted/ListByDealer - dealerCode is optional, pagination required
            // This will give us real customer codes from devices
            'Device/Deleted/ListByDealer',  // ~1s, provides customer codes from devices

            // Fast supplementary data
            'ApiClient/List',  // ~500ms, provides client IDs
        ];

        // Execute seed collection with timeout protection
        $maxExecutionTime = 5; // 5 seconds max for seed collection
        $startCollectionTime = microtime(true);

        foreach ($seedEndpoints as $action) {
            // Check if we're approaching timeout
            $elapsed = microtime(true) - $startCollectionTime;
            if ($elapsed > $maxExecutionTime) {
                $collected['timeout'] = 'Seed collection stopped after ' . round($elapsed, 2) . 's';
                break;
            }

            try {
                $result = self::$engine->dispatchAction($action, []);
                if ($result['success']) {
                    self::processSeedData($action, $result['data']);
                    $collected[$action] = 'success';
                } else {
                    // Don't fail completely on one endpoint error
                    $collected[$action] = 'failed: ' . ($result['error'] ?? 'unknown');
                }
            } catch (Exception $e) {
                // Log but continue
                $collected[$action] = 'exception: ' . $e->getMessage();
            }
        }

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'success' => true,
            'duration_ms' => $duration,
            'collected' => $collected,
            'seed_count' => count(self::$seeds),
            'seeds' => self::$seeds,
            'note' => 'Optimized collection - only essential endpoints called'
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
