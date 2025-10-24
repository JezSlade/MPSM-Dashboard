/**
 * MPS Monitor API Client
 * Handles all API communication with the MPS API engine
 */

const MPSApi = (function() {
    'use strict';

    // API Configuration
    const API_BASE_URL = '/mps-api/query';
    const ENGINE_STATUS_URL = '/mps-api/';
    const CACHE_DURATION = 300000; // 5 minutes (persistent)
    const MAX_RETRIES = 2;
    const RETRY_DELAY = 1000;

    // Persistent localStorage cache helper
    const persistentCache = {
        get: function(key) {
            try {
                const item = localStorage.getItem(`mps_cache_${key}`);
                if (!item) return null;

                const cached = JSON.parse(item);
                if (Date.now() - cached.timestamp > CACHE_DURATION) {
                    localStorage.removeItem(`mps_cache_${key}`);
                    return null;
                }
                return cached.data;
            } catch (e) {
                return null;
            }
        },
        set: function(key, data) {
            try {
                localStorage.setItem(`mps_cache_${key}`, JSON.stringify({
                    data: data,
                    timestamp: Date.now()
                }));
            } catch (e) {
                // localStorage full, clear old cache
                this.cleanup();
            }
        },
        cleanup: function() {
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith('mps_cache_')) {
                    try {
                        const item = JSON.parse(localStorage.getItem(key));
                        if (Date.now() - item.timestamp > CACHE_DURATION) {
                            localStorage.removeItem(key);
                        }
                    } catch (e) {
                        localStorage.removeItem(key);
                    }
                }
            });
        }
    };

    // Runtime settings (set from admin panel)
    let settings = {
        dealerCode: 'NY06AGDWUQ',
        dealerId: 'SZ13qRwU5GtFLj0i_CbEgQ2', // Dealer ID for Device/List
        customerCode: 'W9OPXL0YDK', // Default: Cape Fear Valley Med Ctr
        customerId: '0xUi5WEYLzOCrZ8ILowOvA2', // Customer ID
        customerName: 'CAPE FEAR VALLEY MED CTR.',
        autoRefresh: false,
        refreshInterval: 60
    };

    /**
     * Make API request with retry logic and persistent caching
     */
    async function makeRequest(action, params = {}, options = {}) {
        const cacheKey = `${action}:${JSON.stringify(params)}`;

        // Check persistent cache first
        if (!options.skipCache) {
            const cached = persistentCache.get(cacheKey);
            if (cached) {
                return cached;
            }
        }

        // Make request with retry
        for (let attempt = 0; attempt <= MAX_RETRIES; attempt++) {
            try {
                const response = await fetch(API_BASE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: action,
                        params: params
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'API request failed');
                }

                // Cache successful response to localStorage
                persistentCache.set(cacheKey, data.data);

                return data.data;

            } catch (error) {
                if (attempt < MAX_RETRIES) {
                    // Wait before retry with exponential backoff
                    await new Promise(resolve => setTimeout(resolve, RETRY_DELAY * Math.pow(2, attempt)));
                    continue;
                }
                throw error;
            }
        }
    }

    /**
     * Get dealer hierarchy
     */
    async function getDealerHierarchy() {
        return makeRequest('Dealer/GetDealerHierarchy', {});
    }

    /**
     * Get customer dashboard data
     */
    async function getCustomerDashboard(customerCode) {
        const code = customerCode || settings.customerCode;
        if (!code) {
            throw new Error('Customer code not set');
        }

        return makeRequest('CustomerDashboard', {
            customerCode: code
        });
    }

    /**
     * Get customer dashboard pages
     */
    async function getCustomerDashboardPages(customerCode) {
        const code = customerCode || settings.customerCode;
        if (!code) {
            throw new Error('Customer code not set');
        }

        return makeRequest('CustomerDashboard/Pages', {
            customerCode: code
        });
    }

    /**
     * Get devices for customer with pagination
     * NOTE: FilterCustomerId filters multiple customers, so we fetch by dealer and filter client-side
     */
    async function getDevicesByCustomer(customerCode, customerId) {
        const code = customerCode || settings.customerCode;

        if (!code) {
            throw new Error('Customer code not set');
        }

        // Fetch all pages of ACTIVE devices for dealer (paginated API)
        const allDevices = [];
        let pageNumber = 1;
        const pageRows = 100; // Max items per page
        let hasMore = true;

        while (hasMore && pageNumber <= 50) { // Safety limit: 50 pages max (5000 devices)
            const devices = await makeRequest('Device/List', {
                FilterDealerId: settings.dealerId,
                pageNumber: pageNumber,
                pageRows: pageRows
            }); // REMOVED skipCache: true - use persistent cache!

            if (devices && devices.length > 0) {
                // Filter by customer code on client side
                const filtered = devices.filter(d => d.CustomerCode === code);
                allDevices.push(...filtered);

                // Check if we got a full page (meaning there might be more)
                hasMore = devices.length === pageRows;
                pageNumber++;
            } else {
                hasMore = false;
            }
        }

        return allDevices;
    }

    /**
     * Get device details
     */
    async function getDeviceDetails(deviceId) {
        // Try multiple endpoints to get device data
        try {
            return await makeRequest('Device/GetSuppliesDetails', {
                deviceId: deviceId
            });
        } catch (error) {
            // Fallback to other device endpoints
            try {
                return await makeRequest('Device/GetDeviceAdditionalInfos', {
                    deviceId: deviceId
                });
            } catch (error2) {
                throw new Error('Unable to get device details');
            }
        }
    }

    /**
     * Get alert limits for customer
     */
    async function getAlertLimits(customerCode) {
        const code = customerCode || settings.customerCode;
        if (!code) {
            throw new Error('Customer code not set');
        }

        return makeRequest('AlertLimit/Customer/Get', {
            customerCode: code
        });
    }

    /**
     * Get alert limits default for customer
     */
    async function getAlertLimitsDefault(customerCode) {
        const code = customerCode || settings.customerCode;
        if (!code) {
            throw new Error('Customer code not set');
        }

        return makeRequest('AlertLimit2/Customer/GetDefault', {
            customerCode: code
        });
    }

    /**
     * Get products for customer
     */
    async function getCustomerProducts(customerCode) {
        const code = customerCode || settings.customerCode;
        if (!code) {
            throw new Error('Customer code not set');
        }

        return makeRequest('Product/Customer/List', {
            customerCode: code
        });
    }

    /**
     * Get all product brands
     */
    async function getProductBrands() {
        return makeRequest('Product/GetBrands', {});
    }

    /**
     * Get all product models
     */
    async function getProductModels() {
        return makeRequest('Product/GetModels', {});
    }

    /**
     * Get dealer products
     */
    async function getDealerProducts() {
        return makeRequest('Product/Dealer/List', {
            dealerCode: settings.dealerCode
        });
    }

    /**
     * Get dealer supply sets
     */
    async function getDealerSupplySets() {
        return makeRequest('DealerSupplySet/List', {
            dealerCode: settings.dealerCode
        });
    }

    /**
     * Get SDS device actions
     */
    async function getDeviceActions(customerCode) {
        const code = customerCode || settings.customerCode;
        if (!code) {
            throw new Error('Customer code not set');
        }

        return makeRequest('SdsAction/GetDeviceActions', {
            customerCode: code
        });
    }

    /**
     * Get SDS device operations
     */
    async function getDeviceOperations(customerCode) {
        const code = customerCode || settings.customerCode;
        if (!code) {
            throw new Error('Customer code not set');
        }

        return makeRequest('SdsDevice/GetDevicesOperations', {
            customerCode: code
        });
    }

    /**
     * Get all roles
     */
    async function getRoles() {
        return makeRequest('Role/List', {});
    }

    /**
     * Get account profile
     */
    async function getAccountProfile() {
        return makeRequest('Account/GetProfile', {});
    }

    /**
     * Get engine status/health
     */
    async function getEngineStatus() {
        try {
            const response = await fetch(ENGINE_STATUS_URL);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            throw new Error('Failed to get engine status: ' + error.message);
        }
    }

    /**
     * Discover customer by name or get all customers (with pagination)
     */
    async function discoverCustomerByName(searchName) {
        // Fetch ALL pages of ACTIVE devices to get ALL customers
        const allDevices = [];
        let pageNumber = 1;
        const pageRows = 100;
        let hasMore = true;

        while (hasMore && pageNumber <= 50) {
            const devices = await makeRequest('Device/List', {
                FilterDealerId: settings.dealerId,
                pageNumber: pageNumber,
                pageRows: pageRows
            }); // REMOVED skipCache: true - use persistent cache!

            if (devices && devices.length > 0) {
                allDevices.push(...devices);
                hasMore = devices.length === pageRows;
                pageNumber++;
            } else {
                hasMore = false;
            }
        }

        if (!allDevices || allDevices.length === 0) {
            throw new Error('No customer data available');
        }

        // Build customer list from ALL devices
        const customers = [];
        const customerMap = new Map();

        for (const device of allDevices) {
            if (device.CustomerCode) {
                const code = device.CustomerCode;
                const name = device.CustomerDescription || 'Unknown';
                const id = device.CustomerId;

                if (!customerMap.has(code)) {
                    customerMap.set(code, {
                        code: code,
                        name: name,
                        id: id,
                        deviceCount: 0
                    });
                    customers.push(customerMap.get(code));
                }

                // Count devices per customer
                customerMap.get(code).deviceCount++;
            }
        }

        // Sort customers by name
        customers.sort((a, b) => a.name.localeCompare(b.name));

        // Search for matching customer
        if (searchName) {
            const searchLower = searchName.toLowerCase();
            const match = customers.find(c =>
                c.name.toLowerCase().includes(searchLower) ||
                c.code.toLowerCase() === searchLower
            );

            if (match) {
                return { match: match, customers: customers };
            }
        }

        return { customers: customers, match: null };
    }

    /**
     * Get all customers
     */
    async function getAllCustomers() {
        // Check aggregated cache first (fixes admin dropdown reload issue)
        const cached = persistentCache.get('customer_list_aggregated');
        if (cached) {
            console.log('Using cached customer list (aggregated)');
            return cached;
        }

        console.log('Building customer list from devices...');
        const result = await discoverCustomerByName('');
        const customers = result.customers;

        // Cache the final aggregated customer list
        persistentCache.set('customer_list_aggregated', customers);
        console.log(`Cached ${customers.length} customers`);

        return customers;
    }

    /**
     * Clear cache (both memory and localStorage)
     */
    function clearCache() {
        // Clear all localStorage cache entries
        const keys = Object.keys(localStorage);
        keys.forEach(key => {
            if (key.startsWith('mps_cache_')) {
                localStorage.removeItem(key);
            }
        });
        console.log('Cache cleared (including aggregated customer list)');
    }

    /**
     * Update settings
     */
    function updateSettings(newSettings) {
        settings = { ...settings, ...newSettings };

        // Save to localStorage
        localStorage.setItem('mps_settings', JSON.stringify(settings));
    }

    /**
     * Load settings
     */
    function loadSettings() {
        const saved = localStorage.getItem('mps_settings');
        if (saved) {
            try {
                settings = { ...settings, ...JSON.parse(saved) };
            } catch (error) {
                console.error('Failed to load settings:', error);
            }
        }
        return settings;
    }

    /**
     * Get current settings
     */
    function getSettings() {
        return { ...settings };
    }

    // Public API
    return {
        // Settings
        getSettings,
        updateSettings,
        loadSettings,
        clearCache,

        // Dealer
        getDealerHierarchy,
        getDealerProducts,
        getDealerSupplySets,

        // Customer
        getCustomerDashboard,
        getCustomerDashboardPages,
        getCustomerProducts,
        discoverCustomerByName,
        getAllCustomers,

        // Devices
        getDevicesByCustomer,
        getDeviceDetails,
        getDeviceActions,
        getDeviceOperations,

        // Alerts
        getAlertLimits,
        getAlertLimitsDefault,

        // Products
        getProductBrands,
        getProductModels,

        // Other
        getRoles,
        getAccountProfile,
        getEngineStatus,

        // Raw request
        request: makeRequest
    };
})();
