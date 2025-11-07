/**
 * API Client Module
 * Centralized API communication with caching and error handling
 */

export class APIClient {
    constructor(baseUrl = '/cms/api') {
        this.baseUrl = baseUrl;
        this.cache = new Map();
        this.cacheTTL = 60000; // 1 minute default
        this.pendingRequests = new Map();
    }

    /**
     * GET request with caching
     */
    async get(endpoint, options = {}) {
        const cacheKey = `GET:${endpoint}`;
        const useCache = options.cache !== false;

        // Check cache
        if (useCache && this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < (options.cacheTTL || this.cacheTTL)) {
                console.log(`[API] Cache hit: ${endpoint}`);
                return cached.data;
            }
        }

        // Check for pending request (deduplication)
        if (this.pendingRequests.has(cacheKey)) {
            console.log(`[API] Deduping request: ${endpoint}`);
            return this.pendingRequests.get(cacheKey);
        }

        // Make request
        const promise = this._request('GET', endpoint, null, options);
        this.pendingRequests.set(cacheKey, promise);

        try {
            const data = await promise;

            // Cache response
            if (useCache) {
                this.cache.set(cacheKey, {
                    data,
                    timestamp: Date.now()
                });
            }

            return data;
        } finally {
            this.pendingRequests.delete(cacheKey);
        }
    }

    /**
     * POST request
     */
    async post(endpoint, body, options = {}) {
        return this._request('POST', endpoint, body, options);
    }

    /**
     * PUT request
     */
    async put(endpoint, body, options = {}) {
        return this._request('PUT', endpoint, body, options);
    }

    /**
     * DELETE request
     */
    async delete(endpoint, options = {}) {
        return this._request('DELETE', endpoint, null, options);
    }

    /**
     * Internal request handler
     */
    async _request(method, endpoint, body = null, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const startTime = Date.now();

        const config = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            credentials: 'same-origin'
        };

        if (body) {
            config.body = JSON.stringify(body);
        }

        try {
            console.log(`[API] ${method} ${endpoint}`);

            const response = await fetch(url, config);
            const duration = Date.now() - startTime;

            console.log(`[API] ${method} ${endpoint} - ${response.status} (${duration}ms)`);

            if (!response.ok) {
                throw new APIError(
                    `HTTP ${response.status}: ${response.statusText}`,
                    response.status,
                    endpoint
                );
            }

            const data = await response.json();

            if (data.success === false) {
                throw new APIError(
                    data.message || 'Request failed',
                    response.status,
                    endpoint,
                    data.error_code
                );
            }

            return data;
        } catch (error) {
            if (error instanceof APIError) {
                throw error;
            }

            // Network error
            throw new APIError(
                `Network error: ${error.message}`,
                0,
                endpoint
            );
        }
    }

    /**
     * Clear cache for specific endpoint or all
     */
    clearCache(endpoint = null) {
        if (endpoint) {
            const cacheKey = `GET:${endpoint}`;
            this.cache.delete(cacheKey);
            console.log(`[API] Cache cleared: ${endpoint}`);
        } else {
            this.cache.clear();
            console.log('[API] All cache cleared');
        }
    }

    /**
     * Get cache statistics
     */
    getCacheStats() {
        return {
            size: this.cache.size,
            entries: Array.from(this.cache.keys())
        };
    }
}

/**
 * API Error Class
 */
export class APIError extends Error {
    constructor(message, statusCode, endpoint, errorCode = null) {
        super(message);
        this.name = 'APIError';
        this.statusCode = statusCode;
        this.endpoint = endpoint;
        this.errorCode = errorCode;
    }

    isNetworkError() {
        return this.statusCode === 0;
    }

    isClientError() {
        return this.statusCode >= 400 && this.statusCode < 500;
    }

    isServerError() {
        return this.statusCode >= 500;
    }

    isUnauthorized() {
        return this.statusCode === 401;
    }
}

// Singleton instance
export const api = new APIClient();
