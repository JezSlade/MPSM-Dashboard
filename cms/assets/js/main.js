/**
 * Main Application Entry Point
 * Initializes the dashboard application with modular ES6 imports
 */

import { api } from './api-client.js';
import { state } from './state-manager.js';
import { DeviceTable } from './components/device-table.js';
import { showToast } from './utils.js';

/**
 * Application Class
 * Main application controller
 */
class DashboardApp {
    constructor() {
        this.initialized = false;
        this.components = {};
    }

    /**
     * Initialize application
     */
    async init() {
        if (this.initialized) return;

        console.log('[App] Initializing MPSM Dashboard...');

        try {
            // Setup state listeners
            this.setupStateListeners();

            // Initialize components based on page
            this.initializeComponents();

            // Setup global error handling
            this.setupErrorHandling();

            // Mark as initialized
            this.initialized = true;
            state.set('appInitialized', true);

            console.log('[App] Dashboard initialized successfully');
            showToast('Dashboard loaded', 'success', 2000);
        } catch (error) {
            console.error('[App] Initialization failed:', error);
            showToast('Failed to initialize dashboard', 'error');
        }
    }

    /**
     * Setup state listeners for global state changes
     */
    setupStateListeners() {
        // Listen for device selection
        state.subscribe('selectedDevice', (serial) => {
            console.log('[App] Device selected:', serial);
        });

        // Listen for loading states
        state.subscribe('devicesLoading', (loading) => {
            if (loading) {
                this.showGlobalLoader();
            } else {
                this.hideGlobalLoader();
            }
        });
    }

    /**
     * Initialize page-specific components
     */
    initializeComponents() {
        // Check which page we're on and initialize appropriate components
        const page = document.body.dataset.page || 'index';

        console.log('[App] Initializing components for page:', page);

        switch (page) {
            case 'index':
            case 'dashboard':
                this.initializeDashboard();
                break;

            case 'devices':
                this.initializeDevicesPage();
                break;

            case 'panel-messages':
                this.initializePanelMessagesPage();
                break;
        }
    }

    /**
     * Initialize dashboard page
     */
    initializeDashboard() {
        console.log('[App] Initializing dashboard...');

        // Load dashboard statistics
        this.loadDashboardStats();

        // Initialize device table if container exists
        const deviceTableContainer = document.getElementById('deviceTableContainer');
        if (deviceTableContainer) {
            this.components.deviceTable = new DeviceTable('deviceTableContainer', {
                pageSize: 25
            });
            this.components.deviceTable.loadDevices();
        }
    }

    /**
     * Initialize devices page
     */
    initializeDevicesPage() {
        console.log('[App] Initializing devices page...');

        const deviceTableContainer = document.getElementById('deviceTableContainer');
        if (deviceTableContainer) {
            this.components.deviceTable = new DeviceTable('deviceTableContainer', {
                pageSize: 50
            });
            this.components.deviceTable.loadDevices();
        }
    }

    /**
     * Initialize panel messages page
     */
    initializePanelMessagesPage() {
        console.log('[App] Initializing panel messages page...');
        // Panel message components would be initialized here
    }

    /**
     * Load dashboard statistics
     */
    async loadDashboardStats() {
        try {
            const response = await api.get('/get-dashboard-stats.php', {
                cache: true,
                cacheTTL: 60000 // 1 minute
            });

            if (response.success && response.data) {
                state.set('dashboardStats', response.data);
                this.updateDashboardUI(response.data);
            }
        } catch (error) {
            console.error('[App] Failed to load dashboard stats:', error);
        }
    }

    /**
     * Update dashboard UI with statistics
     */
    updateDashboardUI(stats) {
        // Update stat cards
        const statElements = {
            'totalDevices': stats.total_devices,
            'activeDevices': stats.active_devices,
            'pendingAlerts': stats.pending_alerts,
            'cacheHealth': stats.cache_health
        };

        Object.entries(statElements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }

    /**
     * Show global loading indicator
     */
    showGlobalLoader() {
        let loader = document.getElementById('globalLoader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'globalLoader';
            loader.className = 'global-loader';
            loader.innerHTML = '<div class="spinner"></div>';
            document.body.appendChild(loader);
        }
        loader.classList.add('show');
    }

    /**
     * Hide global loading indicator
     */
    hideGlobalLoader() {
        const loader = document.getElementById('globalLoader');
        if (loader) {
            loader.classList.remove('show');
        }
    }

    /**
     * Setup global error handling
     */
    setupErrorHandling() {
        window.addEventListener('unhandledrejection', (event) => {
            console.error('[App] Unhandled promise rejection:', event.reason);
            showToast('An error occurred. Please try again.', 'error');
        });

        window.addEventListener('error', (event) => {
            console.error('[App] Global error:', event.error);
        });
    }

    /**
     * Refresh all data
     */
    async refreshAll() {
        console.log('[App] Refreshing all data...');
        showToast('Refreshing data...', 'info');

        api.clearCache();

        // Reload components
        if (this.components.deviceTable) {
            await this.components.deviceTable.loadDevices();
        }

        if (this.loadDashboardStats) {
            await this.loadDashboardStats();
        }

        showToast('Data refreshed', 'success');
    }
}

// Create and initialize app instance
const app = new DashboardApp();

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => app.init());
} else {
    app.init();
}

// Expose app globally for debugging
window.dashboardApp = app;

// Export for module imports
export default app;
