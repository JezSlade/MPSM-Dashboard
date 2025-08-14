/**
 * WIDGET DASHBOARD APPLICATION
 * * This is the main application file that orchestrates all components.
 * It initializes the services, repositories, and UI handlers.
 */

import { Widget, WidgetArea } from './app/entities.js';
import { WidgetRepository, WidgetAreaRepository } from './app/repositories.js';
import { WidgetService, WidgetAreaService } from './app/services.js';
import { WidgetRendererFactory } from './lib/renderers.js';

/**
 * The main Dashboard application controller.
 * Manages the UI, state, and interaction with services.
 * * @class Dashboard
 */
class Dashboard {
    constructor() {
        this.widgetRepository = new WidgetRepository();
        this.areaRepository = new WidgetAreaRepository();
        this.widgetService = new WidgetService(this.widgetRepository, this.areaRepository);
        this.areaService = new WidgetAreaService(this.areaRepository, this.widgetRepository);
        this.rendererFactory = new WidgetRendererFactory();

        this.ui = {
            mainContent: document.querySelector('.dashboard-main'),
            views: document.querySelectorAll('.dashboard-view'),
            sidebar: document.getElementById('dashboard-sidebar'),
            sidebarItems: document.querySelectorAll('.sidebar-item'),
            mobileMenuToggle: document.getElementById('mobile-menu-toggle'),
            widgetAreasContainer: document.getElementById('widget-areas-container'),
            documentationView: document.getElementById('documentation-view'),
            statTotalWidgets: document.getElementById('stat-total-widgets'),
            statActiveWidgets: document.getElementById('stat-active-widgets'),
            statWidgetAreas: document.getElementById('stat-widget-areas'),
            // Add other UI elements as needed
        };

        this.init();
    }

    /**
     * Initializes the dashboard application
     * @private
     */
    init() {
        this.setupEventListeners();
        this.loadInitialData();
        this.renderDashboardView();
        this.updateStatistics();
    }

    /**
     * Sets up all global event listeners
     * @private
     */
    setupEventListeners() {
        // Sidebar navigation
        this.ui.sidebarItems.forEach(item => {
            item.addEventListener('click', (event) => {
                event.preventDefault();
                this.setActiveView(item.dataset.view);
            });
        });

        // Mobile menu toggle
        this.ui.mobileMenuToggle.addEventListener('click', () => {
            this.ui.sidebar.classList.toggle('mobile-open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (event) => {
            if (this.ui.sidebar.classList.contains('mobile-open') && 
                !this.ui.sidebar.contains(event.target) && 
                event.target !== this.ui.mobileMenuToggle) {
                this.ui.sidebar.classList.remove('mobile-open');
            }
        });
    }

    /**
     * Sets the active view based on the data attribute
     * @param {string} viewName - The name of the view to display
     */
    setActiveView(viewName) {
        this.ui.views.forEach(view => {
            if (view.id === `${viewName}-view`) {
                view.classList.remove('d-none');
            } else {
                view.classList.add('d-none');
            }
        });
        this.ui.sidebarItems.forEach(item => {
            if (item.dataset.view === viewName) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
        // Close sidebar on mobile after view change
        this.ui.sidebar.classList.remove('mobile-open');
    }

    /**
     * Renders the main dashboard view with all widget areas
     * @private
     */
    renderDashboardView() {
        const areas = this.areaService.getActiveAreas();
        this.ui.widgetAreasContainer.innerHTML = ''; // Clear container
        
        areas.forEach(area => {
            const areaElement = this.renderArea(area);
            this.ui.widgetAreasContainer.appendChild(areaElement);
        });
    }

    /**
     * Renders a single widget area
     * @param {WidgetArea} area - The widget area object
     * @returns {HTMLElement} The rendered area element
     * @private
     */
    renderArea(area) {
        const areaElement = document.createElement('div');
        areaElement.className = 'widget-area';
        areaElement.dataset.areaId = area.id;
        areaElement.innerHTML = `
            <div class="widget-area-header">${area.name}</div>
            <div class="widget-area-content layout-grid layout-grid-${area.layoutSettings.columns}">
                </div>
        `;

        const widgetsContainer = areaElement.querySelector('.widget-area-content');
        const widgets = this.widgetService.getWidgetsForArea(area.id);
        
        if (widgets.length > 0) {
            areaElement.classList.add('has-widgets');
            widgets.forEach(widget => {
                widgetsContainer.innerHTML += this.rendererFactory.renderWidget(widget);
            });
        } else {
            widgetsContainer.innerHTML = `
                <div class="empty-state">
                    <span class="empty-state-icon">🤷‍♂️</span>
                    <p class="typography-body">No widgets in this area. Drag and drop widgets here to get started.</p>
                </div>
            `;
        }

        return areaElement;
    }

    /**
     * Updates the statistics on the dashboard
     * @private
     */
    updateStatistics() {
        const stats = this.widgetService.getStatistics();
        this.ui.statTotalWidgets.textContent = stats.totalWidgets;
        this.ui.statActiveWidgets.textContent = stats.activeWidgets;
        this.ui.statWidgetAreas.textContent = stats.totalAreas;
    }

    /**
     * Populates local storage with initial data if it's empty.
     * @private
     */
    loadInitialData() {
        if (this.widgetRepository.count() === 0) {
            console.log('No widgets found, loading sample data.');
            const sampleWidgets = [
                new Widget({ title: 'Welcome to your Dashboard', type: 'text', content: 'This is a sample text widget. You can create, edit, and manage widgets to customize your dashboard layout.', status: 'active', category: 'General' }),
                new Widget({ title: 'Quick Stats', type: 'card', content: 'Here are some quick stats about your dashboard setup. This widget shows you the total number of widgets and areas.', status: 'active', category: 'Data' }),
                new Widget({ title: 'Important Links', type: 'list', content: 'Project Management\nDesign Assets\nClient Feedback', status: 'active', category: 'Productivity' }),
                new Widget({ title: 'Product Banner', type: 'banner', content: 'New feature! Check out the updated layout manager.', status: 'active', category: 'Marketing' }),
                new Widget({ title: 'Upcoming Events', type: 'text', content: 'Team sync-up on Monday at 10 AM. Don\'t forget to prepare your reports.', status: 'draft', category: 'Productivity' }),
                new Widget({ title: 'Sample Form', type: 'form', content: 'Feedback Form', status: 'active', category: 'Forms', settings: JSON.stringify({ action: '/submit-feedback', method: 'POST', fields: [{ name: 'name', label: 'Name', type: 'text' }, { name: 'email', label: 'Email', type: 'email' }] }) }),
            ];
            sampleWidgets.forEach(w => this.widgetRepository.create(w));
        }

        if (this.areaRepository.count() === 0) {
            console.log('No widget areas found, loading sample data.');
            const sampleAreas = [
                new WidgetArea({ name: 'Main Content Area', slug: 'main-content-area', description: 'The primary content area of the dashboard.', widgetIds: this.widgetRepository.findActive().map(w => w.id), layoutSettings: { columns: 2, gap: 20, alignment: 'start' } }),
                new WidgetArea({ name: 'Sidebar', slug: 'sidebar', description: 'Widgets for the sidebar.', type: 'sidebar', isActive: false }),
                new WidgetArea({ name: 'Header', slug: 'header', description: 'Widgets for the header.', type: 'header', isActive: false }),
                new WidgetArea({ name: 'Stats Section', slug: 'stats-section', description: 'Key performance indicators.', layoutSettings: { columns: 4, gap: 20, alignment: 'start' } }),
            ];
            sampleAreas.forEach(a => this.areaRepository.create(a));
        }
    }

    // Public methods called from HTML onclick attributes
    refreshAllWidgets() {
        console.log('Refreshing all widgets...');
        this.renderDashboardView();
        this.updateStatistics();
        // Here you would typically add logic to fetch fresh data from an API
    }

    showDocumentation() {
        this.setActiveView('documentation');
        this.ui.documentationView.innerHTML = `
            <div class="doc-section">
                <div class="doc-header">
                    <h2 class="typography-h4">Documentation & API Reference</h2>
                </div>
                <div class="doc-content">
                    <h3 class="typography-h3">Application Overview</h3>
                    <p class="typography-body">This widget dashboard is built on an Object-Oriented and layered architecture:</p>
                    <ul>
                        <li><b>Entities:</b> Define the data models (<span class="doc-code">Widget</span>, <span class="doc-code">WidgetArea</span>).</li>
                        <li><b>Repositories:</b> Manage data persistence (e.g., using <span class="doc-code">localStorage</span>).</li>
                        <li><b>Services:</b> Contain all business logic and orchestrate data flow.</li>
                        <li><b>Rendering:</b> A factory system renders different widget types.</li>
                    </ul>

                    <h3 class="typography-h3">Key Features</h3>
                    <p class="typography-body">The system includes a variety of features:</p>
                    <ul>
                        <li><b>Dynamic Layouts:</b> Widget areas are rendered based on their configured grid columns and gap settings.</li>
                        <li><b>Component-based Rendering:</b> Each widget type has a dedicated renderer, adhering to the Open/Closed Principle (SOLID's O).</li>
                        <li><b>Extensible:</b> New widget types can be added by creating a new renderer and registering it with the <span class="doc-code">WidgetRendererFactory</span>.</li>
                        <li><b>Observer Pattern:</b> The repositories use an observer pattern to notify the application of data changes, allowing for real-time UI updates.</li>
                    </ul>
                </div>
            </div>
            
            <div class="doc-section">
                <div class="doc-header">
                    <h2 class="typography-h4">API Methods</h2>
                </div>
                <div class="doc-content">
                    <h4 class="typography-h4">Widget API</h4>
                    <p class="typography-body">Methods for managing individual widgets.</p>
                    <div class="doc-code">
                        <span class="doc-api-method">getWidgets</span>(<span class="doc-parameter">options</span>)
                        <p>Retrieves all widgets, with optional filtering and sorting.</p>
                        <span class="doc-api-method">getWidget</span>(<span class="doc-parameter">id</span>)
                        <p>Retrieves a single widget by its ID.</p>
                    </div>

                    <h4 class="typography-h4">Widget Area API</h4>
                    <p class="typography-body">Methods for managing widget areas.</p>
                    <div class="doc-code">
                        <span class="doc-api-method">createArea</span>(<span class="doc-parameter">data</span>)
                        <p>Creates a new widget area with the given data.</p>
                        <span class="doc-api-method">assignWidgetToArea</span>(<span class="doc-parameter">widgetId</span>, <span class="doc-parameter">areaId</span>)
                        <p>Adds a widget to a specific area.</p>
                        <span class="doc-api-method">removeWidgetFromArea</span>(<span class="doc-parameter">widgetId</span>, <span class="doc-parameter">areaId</span>)
                        <p>Removes a widget from a specific area.</p>
                    </div>
                </div>
            </div>
        `;
    }

    showAreaCreator() {
        console.log('Showing area creator...');
        // Placeholder for future implementation
    }
    addWidgetArea() {
        console.log('Adding new widget area...');
        // Placeholder for future implementation
    }
    previewLayout() {
        console.log('Previewing layout...');
        // Placeholder for future implementation
    }
    createAreaTemplate(template) {
        console.log(`Creating area from template: ${template}`);
        // Placeholder for future implementation
    }
    exportLayout() {
        console.log('Exporting layout...');
        // Placeholder for future implementation
    }
    importLayout() {
        console.log('Importing layout...');
        // Placeholder for future implementation
    }
    clearAllAreas() {
        if (confirm('Are you sure you want to clear all widget areas? This cannot be undone.')) {
            this.areaRepository.deleteAll();
            this.renderDashboardView();
            this.updateStatistics();
            console.log('All areas cleared.');
        }
    }
    editWidget(widgetId) {
        console.log(`Editing widget with ID: ${widgetId}`);
        // Placeholder for future implementation
    }
    showWidgetInfo(widgetId) {
        console.log(`Showing info for widget with ID: ${widgetId}`);
        // Placeholder for future implementation
    }
    removeWidgetFromArea(widgetId) {
        console.log(`Removing widget with ID: ${widgetId}`);
        // Placeholder for future implementation
    }
}

// Instantiate the application
const app = new Dashboard();