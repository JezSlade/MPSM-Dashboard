<?php
/**
 * EndpointCatalog.php
 *
 * Intelligent endpoint routing and recommendation system
 * Integrates comprehensive test results to provide metadata about endpoint capabilities
 *
 * Created: 2025-10-21
 * Purpose: Enable smart routing based on actual endpoint behavior
 */

class EndpointCatalog {
    private static $catalog = null;
    private static $catalogIndex = [];
    private static $testResults = null;

    /**
     * Categories of endpoints by use case
     */
    private static $categories = [
        'dealer_config' => [
            'description' => 'Dealer configuration and settings',
            'endpoints' => [
                'Dealer/AccountingSettings/Get',
                'Dealer/AdvancedOptions/Get',
                'Dealer/AlertLimitOptions/Get',
                'Dealer/AlertSettings/Get',
                'Dealer/CounterBlend/List',
                'Dealer/Customizations/Get',
                'Dealer/DealerServicesStatus/Get',
                'Dealer/GetDealerHierarchy',
                'Dealer/GetDealerTagsHierarchy',
                'Dealer/Onboarding/Get',
                'Dealer/RemoteOfflineCountersSettings/Get',
                'Dealer/eXplorerSettings/Get',
            ]
        ],
        'alerts' => [
            'description' => 'Alert limits and thresholds',
            'endpoints' => [
                'AlertLimit/Dealer/Get',
                'AlertLimit2/Dealer/GetDefault',
                'AlertLimit2/Dealer/GetProduct',
            ]
        ],
        'notifications' => [
            'description' => 'Notification templates and settings',
            'endpoints' => [
                'DealerNotification/GetSampleNotification',
                'DealerNotification/List',
                'DealerNotification/Template/Get',
                'CustomerNotification/GetSampleNotification',
            ]
        ],
        'products' => [
            'description' => 'Product catalogs and models',
            'endpoints' => [
                'Product/GetBrands',
                'Product/GetModels',
                'DealerProduct/Get',
                'StandardProduct/GetStandardProductsSummary',
                'StandardProduct/ListOperations',
                'StandardProduct/ListStandardProducts',
            ]
        ],
        'supplies' => [
            'description' => 'Supply management and pricing',
            'endpoints' => [
                'DealerSupply/Count',
                'DealerSupplyPriceListing/Get',
                'DealerSupplyPriceListing/List',
                'DealerSupplySet/Count',
                'DealerSupplySet/Export',
                'DealerSupplySet/ExportExcel',
                'DealerSupplySet/List',
            ]
        ],
        'integrations' => [
            'description' => 'Explorer and integration settings',
            'endpoints' => [
                'Explorer/Configuration/Get',
                'Explorer/Configuration/List',
                'Explorer/GetConnectorEndpoints',
                'Explorer/GetConnectors',
                'Explorer/License/List',
                'Explorer/V3/ReleaseNotes',
                'Integrations/List',
                'Integrations/GetJoinedCustomers',
                'SdsConnector/GetConnectors',
                'SdsConnector/GetWppConnectors',
            ]
        ],
        'permissions' => [
            'description' => 'User roles and permissions',
            'endpoints' => [
                'Role/List',
                'Role/GetAllCapabilities',
                'CustomField/List',
            ]
        ],
        'api_clients' => [
            'description' => 'API client management',
            'endpoints' => [
                'ApiClient/Get',
                'ApiClient/List',
            ]
        ],
        'account' => [
            'description' => 'Account and profile information',
            'endpoints' => [
                'Account/GetProfile',
                'WhiteLabel/Get',
            ]
        ],
        'miscellaneous' => [
            'description' => 'Other dealer-level endpoints',
            'endpoints' => [
                'Customer/EpsonUSBCustomerId/Get',
                'Office/OfficeFloor/List',
                'Product/Dealer/List',
                'TradingPartner/List',
            ]
        ],
    ];

    /**
     * Initialize the catalog by loading test results
     */
    public static function init() {
        if (self::$catalog !== null) {
            return;
        }

        $resultsFile = __DIR__ . '/../output/endpoint_test_results.json';

        if (!file_exists($resultsFile)) {
            self::$catalog = [];
            self::$catalogIndex = [];
            self::$testResults = null;
            return;
        }

        $json = file_get_contents($resultsFile);
        $data = json_decode($json, true);

        if (!$data) {
            self::$catalog = [];
            self::$catalogIndex = [];
            self::$testResults = null;
            return;
        }

        self::$testResults = $data;
        self::buildCatalog($data['results']);
    }

    /**
     * Build the catalog from test results
     */
    private static function buildCatalog($results) {
        self::$catalog = [];
        self::$catalogIndex = [];

        foreach ($results as $result) {
            $action = $result['action'];

            $entry = [
                'action' => $action,
                'success' => $result['success'],
                'data_type' => $result['data_type'] ?? 'unknown',
                'data_count' => $result['data_count'] ?? null,
                'error' => $result['error'] ?? null,
                'error_code' => $result['error_code'] ?? null,
                'duration_ms' => $result['duration_ms'] ?? null,
                'category' => self::getCategoryForEndpoint($action),
                'prerequisites' => self::getPrerequisites($result),
                'use_case' => self::getUseCase($action),
            ];

            self::$catalog[$action] = $entry;
            self::$catalogIndex[strtolower($action)] = $entry;
        }
    }

    /**
     * Retrieve catalog metadata for a specific action.
     *
     * @param string $action
     * @return array|null
     */
    public static function getMetadata(string $action): ?array
    {
        self::init();

        if (isset(self::$catalog[$action])) {
            return self::$catalog[$action];
        }

        $lower = strtolower($action);
        return self::$catalogIndex[$lower] ?? null;
    }

    /**
     * Determine if the catalog recorded a successful invocation.
     *
     * @param string $action
     * @return bool|null Returns true/false for known endpoints, null if unknown.
     */
    public static function wasSuccessful(string $action): ?bool
    {
        $metadata = self::getMetadata($action);
        if ($metadata === null || !array_key_exists('success', $metadata)) {
            return null;
        }

        return (bool) $metadata['success'];
    }

    /**
     * Get category for an endpoint
     */
    private static function getCategoryForEndpoint($action) {
        foreach (self::$categories as $category => $info) {
            if (in_array($action, $info['endpoints'])) {
                return $category;
            }
        }

        // Infer category from action name
        if (strpos($action, 'Customer/') === 0) return 'customer_data';
        if (strpos($action, 'Device/') === 0) return 'device_data';
        if (strpos($action, 'Counter/') === 0) return 'counter_data';
        if (strpos($action, 'Analytics/') === 0) return 'analytics';

        return 'other';
    }

    /**
     * Determine prerequisites from error message
     */
    private static function getPrerequisites($result) {
        if ($result['success']) {
            return [];
        }

        $error = $result['error'] ?? '';
        $prerequisites = [];

        if (stripos($error, 'customerCode') !== false || stripos($error, 'Customer not found') !== false) {
            $prerequisites[] = 'customerCode';
        }
        if (stripos($error, 'deviceId') !== false || stripos($error, 'Device not found') !== false) {
            $prerequisites[] = 'deviceId';
        }
        if (stripos($error, 'Access denied') !== false) {
            $prerequisites[] = 'higher_permissions';
        }

        return $prerequisites;
    }

    /**
     * Get use case description for an endpoint
     */
    private static function getUseCase($action) {
        $useCases = [
            'AlertLimit/Dealer/Get' => 'Get dealer toner/maintenance alert thresholds',
            'Role/List' => 'List all user roles with capabilities',
            'Product/GetBrands' => 'Get available printer brands',
            'Product/GetModels' => 'Get available printer models',
            'ApiClient/List' => 'List OAuth API clients',
            'Integrations/GetJoinedCustomers' => 'Get customer integration statistics',
            'StandardProduct/ListStandardProducts' => 'Get standard product catalog (28+ items)',
            'DealerSupplySet/List' => 'Get dealer supply sets (39+ items)',
            'Explorer/V3/ReleaseNotes' => 'Get Explorer software release notes',
            'Dealer/GetDealerHierarchy' => 'Get dealer organizational structure',
            'CustomField/List' => 'Get custom field definitions',
            'Account/GetProfile' => 'Get current account profile',
        ];

        return $useCases[$action] ?? null;
    }

    /**
     * Get all working endpoints (53 verified)
     */
    public static function getWorkingEndpoints() {
        self::init();

        $working = [];
        foreach (self::$catalog as $action => $info) {
            if ($info['success']) {
                $working[] = $action;
            }
        }

        return $working;
    }

    /**
     * Get endpoints by category
     */
    public static function getEndpointsByCategory($category) {
        self::init();

        $endpoints = [];
        foreach (self::$catalog as $action => $info) {
            if ($info['category'] === $category && $info['success']) {
                $endpoints[] = [
                    'action' => $action,
                    'data_type' => $info['data_type'],
                    'data_count' => $info['data_count'],
                    'use_case' => $info['use_case'],
                ];
            }
        }

        return $endpoints;
    }

    /**
     * Get endpoints that return lists (arrays)
     */
    public static function getListEndpoints() {
        self::init();

        $lists = [];
        foreach (self::$catalog as $action => $info) {
            if ($info['success'] && $info['data_type'] === 'list') {
                $lists[] = [
                    'action' => $action,
                    'count' => $info['data_count'],
                    'use_case' => $info['use_case'],
                ];
            }
        }

        // Sort by count descending
        usort($lists, function($a, $b) {
            return ($b['count'] ?? 0) - ($a['count'] ?? 0);
        });

        return $lists;
    }

    /**
     * Get endpoints that return objects (dictionaries)
     */
    public static function getObjectEndpoints() {
        self::init();

        $objects = [];
        foreach (self::$catalog as $action => $info) {
            if ($info['success'] && $info['data_type'] === 'dict') {
                $objects[] = [
                    'action' => $action,
                    'use_case' => $info['use_case'],
                ];
            }
        }

        return $objects;
    }

    /**
     * Get metadata for a specific endpoint
     */
    public static function getEndpointMetadata($action) {
        self::init();
        return self::$catalog[$action] ?? null;
    }

    /**
     * Suggest alternative endpoints when one fails
     */
    public static function suggestAlternatives($failedAction, $errorMessage = null) {
        self::init();

        $suggestions = [];

        // If customer code required, suggest dealer-level alternatives
        if (stripos($errorMessage, 'customerCode') !== false || stripos($errorMessage, 'Customer not found') !== false) {
            $suggestions[] = [
                'reason' => 'This endpoint requires a customer code. Try these dealer-level alternatives:',
                'alternatives' => [
                    'Dealer/GetDealerHierarchy' => 'View dealer organizational structure',
                    'Integrations/GetJoinedCustomers' => 'View customer integration statistics',
                    'Role/List' => 'View available user roles',
                ]
            ];
        }

        // If device ID required
        if (stripos($errorMessage, 'deviceId') !== false || stripos($errorMessage, 'Device not found') !== false) {
            $suggestions[] = [
                'reason' => 'This endpoint requires a device ID. Try these alternatives:',
                'alternatives' => [
                    'Product/GetModels' => 'View available printer models',
                    'Product/GetBrands' => 'View available printer brands',
                    'DealerSupplySet/List' => 'View dealer supply sets',
                ]
            ];
        }

        // If access denied
        if (stripos($errorMessage, 'Access denied') !== false) {
            $suggestions[] = [
                'reason' => 'Access denied - API role permissions insufficient. Try these accessible endpoints:',
                'alternatives' => [
                    'Account/GetProfile' => 'View your account profile',
                    'ApiClient/List' => 'View API clients',
                    'Role/List' => 'View user roles',
                ]
            ];
        }

        // Category-based suggestions
        $failedCategory = self::$catalog[$failedAction]['category'] ?? null;
        if ($failedCategory && isset(self::$categories[$failedCategory])) {
            $categoryEndpoints = self::getEndpointsByCategory($failedCategory);
            if (!empty($categoryEndpoints)) {
                $alternatives = [];
                foreach ($categoryEndpoints as $ep) {
                    if ($ep['action'] !== $failedAction) {
                        $alternatives[$ep['action']] = $ep['use_case'] ?? 'No description';
                    }
                }

                if (!empty($alternatives)) {
                    $suggestions[] = [
                        'reason' => 'Try these working endpoints in the same category:',
                        'alternatives' => array_slice($alternatives, 0, 3) // Top 3
                    ];
                }
            }
        }

        return $suggestions;
    }

    /**
     * Get top endpoints by data volume (most items returned)
     */
    public static function getTopEndpointsByVolume($limit = 10) {
        self::init();

        $endpoints = [];
        foreach (self::$catalog as $action => $info) {
            if ($info['success'] && $info['data_type'] === 'list' && $info['data_count'] > 0) {
                $endpoints[] = [
                    'action' => $action,
                    'count' => $info['data_count'],
                    'use_case' => $info['use_case'],
                ];
            }
        }

        usort($endpoints, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        return array_slice($endpoints, 0, $limit);
    }

    /**
     * Get statistics about the catalog
     */
    public static function getStatistics() {
        self::init();

        $stats = [
            'total_endpoints' => count(self::$catalog),
            'working' => 0,
            'failed' => 0,
            'by_data_type' => [],
            'by_category' => [],
            'avg_response_time' => 0,
        ];

        $responseTimes = [];

        foreach (self::$catalog as $action => $info) {
            if ($info['success']) {
                $stats['working']++;
            } else {
                $stats['failed']++;
            }

            $type = $info['data_type'];
            if (!isset($stats['by_data_type'][$type])) {
                $stats['by_data_type'][$type] = 0;
            }
            $stats['by_data_type'][$type]++;

            $category = $info['category'];
            if (!isset($stats['by_category'][$category])) {
                $stats['by_category'][$category] = 0;
            }
            $stats['by_category'][$category]++;

            if ($info['duration_ms']) {
                $responseTimes[] = $info['duration_ms'];
            }
        }

        if (!empty($responseTimes)) {
            $stats['avg_response_time'] = round(array_sum($responseTimes) / count($responseTimes), 2);
        }

        return $stats;
    }

    /**
     * Get all categories
     */
    public static function getCategories() {
        return self::$categories;
    }

    /**
     * Search endpoints by keyword
     */
    public static function searchEndpoints($keyword) {
        self::init();

        $results = [];
        $keyword = strtolower($keyword);

        foreach (self::$catalog as $action => $info) {
            $searchable = strtolower($action . ' ' . ($info['use_case'] ?? ''));

            if (stripos($searchable, $keyword) !== false) {
                $results[] = [
                    'action' => $action,
                    'success' => $info['success'],
                    'use_case' => $info['use_case'],
                    'data_type' => $info['data_type'],
                    'data_count' => $info['data_count'],
                ];
            }
        }

        return $results;
    }
}
