/**
 * MPS Monitor API Client
 * Handles all API communication with the MPS API engine
 */

const MPSApi = (function() {
    'use strict';

    // API Configuration
    const API_BASE_URL = '/mps-api/query';
    const ENGINE_STATUS_URL = '/mps-api/';
    const CACHE_DURATION = 60000; // 1 minute
    const MAX_RETRIES = 2;
    const RETRY_DELAY = 1000;

    // In-memory cache
    const cache = new Map();

    // Runtime settings (set from admin panel)
    let settings = {
        dealerCode: 'NY06AGDWUQ',
        customerCode: 'W9OPXL0YDK', // Default: Cape Fear Valley Med Ctr
        customerId: '0xUi5WEYLzOCrZ8ILowOvA2', // Customer ID
        customerName: 'CAPE FEAR VALLEY MED CTR.',
        autoRefresh: false,
        refreshInterval: 60
    };

    /**
     * Make API request with retry logic
     */
    async function makeRequest(action, params = {}, options = {}) {
        const cacheKey = `${action}:${JSON.stringify(params)}`;

        // Check cache first
        if (!options.skipCache && cache.has(cacheKey)) {
            const cached = cache.get(cacheKey);
            if (Date.now() - cached.timestamp < CACHE_DURATION) {
                return cached.data;
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

                // Cache successful response
                cache.set(cacheKey, {
                    data: data.data,
                    timestamp: Date.now()
                });

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
     * Get devices for customer
     */
    async function getDevicesByCustomer(customerCode) {
        const code = customerCode || settings.customerCode;
        if (!code) {
            throw new Error('Customer code not set');
        }

        // Try to get deleted devices list (this endpoint works)
        return makeRequest('Device/Deleted/ListByDealer', {
            dealerCode: settings.dealerCode
        });
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
     * Discover customer by name or get all customers
     */
    async function discoverCustomerByName(searchName) {
        // Get devices and extract customer codes with names
        const devices = await makeRequest('Device/Deleted/ListByDealer', {
            dealerCode: settings.dealerCode
        });

        if (!devices || !Array.isArray(devices)) {
            throw new Error('No customer data available');
        }

        // Build customer list from devices
        const customers = [];
        const customerMap = new Map();

        for (const device of devices) {
            if (device.Customer && device.Customer.Code) {
                const code = device.Customer.Code;
                const name = device.Customer.Description || 'Unknown';
                const id = device.Customer.Id;

                if (!customerMap.has(code)) {
                    customerMap.set(code, {
                        code: code,
                        name: name,
                        id: id,
                        countryCode: device.Customer.CountryCode,
                        countryName: device.Customer.CountryName
                    });
                    customers.push(customerMap.get(code));
                }
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
        const result = await discoverCustomerByName('');
        return result.customers;
    }

    /**
     * Clear cache
     */
    function clearCache() {
        cache.clear();
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
