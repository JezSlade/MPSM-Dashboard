MPS Monitor Dashboard Project Documentation
1. Project Overview

The MPS Monitor Dashboard is a dynamic, modular web application designed to provide users with a personalized dashboard experience. It features a modern "glassmorphism" UI, a customizable layout, and a library of interactive widgets. Key functionalities include:

    Customizable Dashboard: Users can add, remove, reorder, and resize widgets.

    Global Settings: Adjust dashboard title, accent color, glass effect intensity, blur amount, and animations.

    Widget Management: A dedicated interface to manage active widgets and their properties.

    New Widget Creation: Users can generate new widget templates directly from the dashboard.

    Integrated Development Environment (IDE) Widget: A basic file editor for quick code adjustments within the dashboard.

The project is structured with a PHP backend for server-side logic and data persistence (using JSON files), and a JavaScript frontend for interactive UI and AJAX communication.
2. Architecture and Modularization Strategy

This project follows a modular architecture to ensure maintainability, scalability, and separation of concerns.
2.1. Backend (PHP) Architecture

The PHP backend is designed around a set of dedicated classes that encapsulate specific business logic, and API endpoints that serve as the interface for frontend requests.

    Core PHP Classes (src/php/):

        DashboardManager.php: Manages the overall dashboard state, including global settings and the list of active widgets. It handles loading from and saving to dashboard_settings.json and dynamic_widgets.json.

        FileManager.php: Handles all file system operations, specifically for the IDE widget and widget template creation. It includes robust path validation to prevent directory traversal attacks.

    API Endpoints (api/):

        dashboard.php: Handles all AJAX requests related to global dashboard settings and active widget management (adding, removing, reordering, resizing).

        ide.php: Manages AJAX requests for the IDE widget, including listing files, reading file content, and saving file content.

        widget_creation.php: Handles the AJAX request for creating new widget template files and registering them in dynamic_widgets.json.

    Configuration and Utilities:

        config.php: Defines global constants (like file paths) and the initial list of available widgets. It also loads dynamically created widgets.

        helpers.php: Contains utility functions, notably render_widget(), which is responsible for including and rendering the content of individual widget PHP files.

2.2. Frontend (JavaScript) Architecture

The JavaScript frontend is built using a module pattern, where each significant UI component or feature has its own dedicated module. This enhances code organization, reusability, and makes debugging easier.

    Main Entry Point (src/js/main.js): This file is the primary entry point for the frontend application. It imports and initializes all other JavaScript modules when the DOM is ready.

    Utility Modules (src/js/utils/):

        AjaxService.js: Provides a centralized function (sendAjaxRequest) for making AJAX POST requests to the PHP API endpoints. This abstracts away the fetch API details and handles common error logging.

    UI Component Modules (src/js/ui/):

        MessageModal.js: Manages a generic, reusable modal dialog for displaying messages, confirmations, and alerts to the user.

        SettingsPanel.js: Controls the behavior and interactions of the global dashboard settings sidebar panel.

        WidgetSettingsModal.js: Manages the modal dialog for adjusting individual widget dimensions.

        WidgetManagementModal.js: Handles the comprehensive modal interface for listing, deactivating, and managing active widgets.

        CreateWidgetModal.js: Manages the modal dialog for creating new widget templates.

    Feature-Specific Modules (src/js/features/):

        WidgetActions.js: Contains event listeners and logic for actions performed on individual widgets (e.g., expand/collapse, open settings, remove).

        DragDropManager.js: Implements the drag-and-drop functionality for reordering existing widgets on the dashboard and adding new widgets from the sidebar library.

        IdeWidget.js: Contains the specific logic for the Integrated Development Environment (IDE) widget, including managing the file tree, displaying file content, and handling save operations.

2.3. Data Flow

    Initial Load: index.php initializes the DashboardManager and loads the current dashboard state from dashboard_settings.json (and merges with config.php defaults). This state, including active widgets and global settings, is then used to render the initial HTML.

    User Interaction: User actions (e.g., clicking a button, dragging a widget) trigger JavaScript event listeners in the relevant frontend modules.

    AJAX Request: Frontend modules use AjaxService.js to send POST requests to specific PHP API endpoints (api/dashboard.php, api/ide.php, api/widget_creation.php).

    Backend Processing: The PHP API endpoint receives the request, instantiates the appropriate PHP manager class (DashboardManager or FileManager), and calls the relevant method to perform the requested operation (e.g., save settings, update widget order, read file).

    Data Persistence: Manager classes interact with JSON files (dashboard_settings.json, dynamic_widgets.json) or widget PHP files (widgets/*.php) to read or write data.

    Response: The PHP API endpoint returns a JSON response indicating success or failure, along with any relevant data.

    Frontend Update: The JavaScript module receives the JSON response. On success, it typically reloads the page (location.reload(true)) to ensure the UI reflects the updated server-side state, or updates the UI dynamically if appropriate (e.g., IDE file content).

3. Detailed Component Documentation
3.1. Backend Components (PHP)
3.1.1. config.php

    Purpose: Central configuration file. Defines constants for data file paths and the initial static list of all available widgets. It also dynamically loads widgets defined in dynamic_widgets.json.

    Key Variables/Constants:

        DASHBOARD_SETTINGS_FILE: Path to the main dashboard settings JSON file.

        DYNAMIC_WIDGETS_FILE: Path to the JSON file storing dynamically created widget configurations.

        APP_ROOT: Defines the application's root directory for security (used by FileManager).

        $available_widgets: An associative array mapping unique widget IDs to their display name, icon, default width, and height. This array is merged with dynamic_widgets.json and sorted alphabetically.

    Usage: Included at the very beginning of index.php and all API endpoints to provide global configuration.

3.1.2. helpers.php

    Purpose: Contains helper functions used across the application, primarily for rendering widget content.

    Key Functions:

        render_widget(string $widget_id):

            Description: Includes and executes the PHP file corresponding to the given $widget_id from the widgets/ directory. It uses output buffering to capture the HTML output of the widget file.

            Parameters: $widget_id (string) - The unique identifier of the widget (e.g., 'stats', 'ide').

            Returns: (string) - The HTML content generated by the widget's PHP file, or an error message if the file is not found.

            Security: Uses basename() to prevent directory traversal when constructing the widget file path.

3.1.3. src/php/DashboardManager.php

    Class: DashboardManager

    Purpose: Encapsulates all logic related to loading, saving, and manipulating the overall dashboard state and active widgets.

    Constructor: __construct($dashboardSettingsFile, $dynamicWidgetsFile, $availableWidgets)

        Initializes the manager with file paths and the list of available widgets. Defines a $defaultDashboardState.

    Key Methods:

        loadDashboardState():

            Description: Reads the dashboard_settings.json file. If the file exists and is valid, it decodes the JSON and merges it with the $defaultDashboardState to ensure all settings keys are present. It also ensures active widgets have valid width and height properties, clamping them within defined ranges (0.5-3.0 for width, 0.5-4.0 for height).

            Returns: (array) - The complete dashboard state array.

        saveDashboardState(array $state):

            Description: Encodes the provided dashboard state array into JSON format and writes it to dashboard_settings.json.

            Parameters: $state (array) - The complete dashboard state to save.

            Returns: (bool) - true on success, false on failure (e.g., JSON encoding error, file write error).

        loadDynamicWidgets():

            Description: Reads and decodes the dynamic_widgets.json file.

            Returns: (array) - An associative array of dynamic widget configurations.

        saveDynamicWidgets(array $widgets):

            Description: Encodes and writes the provided dynamic widget configurations to dynamic_widgets.json.

            Parameters: $widgets (array) - The dynamic widget configurations to save.

            Returns: (bool) - true on success, false on failure.

        addWidget(string $widget_id, array $current_active_widgets):

            Description: Adds a new widget entry (with default dimensions) to the end of the $current_active_widgets array.

            Parameters: $widget_id (string), $current_active_widgets (array).

            Returns: (array) - The updated array of active widgets.

        removeWidgetById(string $widget_id, array $current_active_widgets):

            Description: Removes a widget from the $current_active_widgets array based on its ID.

            Parameters: $widget_id (string), $current_active_widgets (array).

            Returns: (array) - The updated array of active widgets (re-indexed).

        updateWidgetDimensions(int $widget_index, float $new_width, float $new_height, array $current_active_widgets):

            Description: Updates the width and height properties of a specific widget in the $current_active_widgets array, identified by its numerical index. Values are clamped.

            Parameters: $widget_index (int), $new_width (float), $new_height (float), $current_active_widgets (array).

            Returns: (array) - The updated array of active widgets.

        updateWidgetOrder(array $new_order_ids, array $current_active_widgets):

            Description: Reorders the $current_active_widgets array based on a provided list of widget IDs. Widgets not in new_order_ids are effectively removed from the new order.

            Parameters: $new_order_ids (array) - An ordered list of widget IDs, $current_active_widgets (array).

            Returns: (array) - The newly ordered array of active widgets.

        setAllAvailableWidgetsAsActive():

            Description: Generates an active_widgets array containing all widgets defined in $available_widgets (from config.php), sorted alphabetically by ID. Used when the "Show All Available Widgets" setting is enabled.

            Returns: (array) - A new array of active widgets.

3.1.4. src/php/FileManager.php

    Class: FileManager

    Purpose: Provides secure file system operations, primarily for the IDE widget and new widget template creation.

    Constructor: __construct($appRoot)

        Initializes the manager with the application root directory (APP_ROOT).

    Key Methods:

        validatePath(string $path):

            Description: Crucial for security. Normalizes a given path and ensures it is strictly within the defined APP_ROOT. Prevents directory traversal (../) vulnerabilities.

            Parameters: $path (string) - A user-provided relative path.

            Returns: (string|false) - The absolute, validated real path if valid and within APP_ROOT, otherwise false.

        listFiles(string $path):

            Description: Lists files and directories within a specified path, after validating it. Returns an array of item details (name, path, type, writability). Includes .. for parent directory navigation.

            Parameters: $path (string) - Relative path from APP_ROOT.

            Returns: (array|false) - List of files/directories or false on error/invalid path.

        readFile(string $path):

            Description: Reads the content of a file after validating its path.

            Parameters: $path (string) - Relative path from APP_ROOT.

            Returns: (string|false) - File content or false on error/invalid path.

        saveFile(string $path, string $content):

            Description: Writes content to a file after validating its path and checking write permissions.

            Parameters: $path (string) - Relative path from APP_ROOT, $content (string) - Content to write.

            Returns: (bool) - true on success, false on failure.

        createWidgetTemplate(string $widget_id, string $widget_name, string $widget_icon, float $widget_width, float $widget_height):

            Description: Generates a new PHP file for a widget in the widgets/ directory based on provided details. Includes a basic HTML structure for compact and expanded views.

            Parameters: $widget_id (string), $widget_name (string), $widget_icon (string), $widget_width (float), $widget_height (float).

            Returns: (bool) - true on success, false if the widget ID already exists or file creation fails.

3.1.5. api/dashboard.php

    Purpose: Handles all AJAX requests related to the dashboard's global settings and active widget list.

    Request Method: Primarily accepts POST requests.

    Actions Handled (ajax_action parameter):

        delete_settings_json: Resets the dashboard to its default state by deleting dashboard_settings.json and clearing the session.

        get_active_widgets_data: Fetches data for currently active widgets, including their names, icons, and dimensions, for display in the Widget Management modal.

        update_single_widget_dimensions: Updates the width and height of a specific active widget.

        update_widget_order: Reorders the active widgets based on a new sequence of IDs.

        remove_widget_from_management: Deactivates (removes) a widget from the active list.

        add_widget: Adds a new widget to the active list (used by drag-and-drop from sidebar).

        update_settings: Saves all global dashboard settings (title, colors, animations, "show all widgets" mode). If "show all widgets" is enabled, it automatically populates the active widgets with all available ones.

    Interaction: Uses DashboardManager to perform operations and interacts with $_SESSION to keep the current request's state synchronized.

3.1.6. api/ide.php

    Purpose: Handles all AJAX requests for the IDE widget.

    Request Method: Primarily accepts POST requests.

    Actions Handled (ajax_action parameter):

        ide_list_files: Lists files and directories for the IDE's file tree.

        ide_read_file: Reads the content of a specified file.

        ide_save_file: Saves content to a specified file.

    Interaction: Uses FileManager to perform file system operations.

3.1.7. api/widget_creation.php

    Purpose: Handles the AJAX request for creating new widget template files.

    Request Method: Primarily accepts POST requests.

    Actions Handled (ajax_action parameter):

        create_new_widget_template: Creates a new PHP file in the widgets/ directory and registers its configuration in dynamic_widgets.json. Includes basic validation for widget ID format.

    Interaction: Uses FileManager to create the file and DashboardManager to update dynamic_widgets.json.

3.2. Frontend Components (JavaScript)
3.2.1. src/js/main.js

    Purpose: The main entry point for all client-side JavaScript. It ensures the DOM is fully loaded before initializing all other modules.

    Key Functionality: Imports and calls initialization functions for:

        SettingsPanel

        WidgetSettingsModal

        WidgetManagementModal

        CreateWidgetModal

        WidgetActions

        DragDropManager

        IdeWidget (specifically its event listeners)

    Other: Contains event listeners for global UI elements like the "Refresh" button and the "Theme Settings" shortcut.

3.2.2. src/js/utils/AjaxService.js

    Purpose: Provides a standardized way to send AJAX POST requests.

    Key Functions:

        sendAjaxRequest(endpoint, ajaxAction, data = {}):

            Description: Constructs a FormData object, appends the ajax_action and other data, and sends a POST request using the fetch API. It includes an X-Requested-With header for PHP to identify AJAX requests.

            Parameters:

                endpoint (string): The URL of the PHP API endpoint (e.g., 'api/dashboard.php').

                ajaxAction (string): The specific action string to be sent to the PHP backend.

                data (Object): An object containing key-value pairs to be sent as form data.

            Returns: (Promise<Object>) - A Promise that resolves with the parsed JSON response from the server. Includes basic error handling and uses MessageModal to display AJAX errors.

3.2.3. src/js/ui/MessageModal.js

    Purpose: Manages a generic modal dialog for displaying messages and handling confirmations.

    Key Functions:

        showMessageModal(title, message, confirmCallback = null):

            Description: Displays the modal with a given title and message. It can optionally execute a confirmCallback function when the "OK" button is clicked.

            Parameters:

                title (string): The title text for the modal header.

                message (string): The main message content to display.

                confirmCallback (function, optional): A callback function to execute when the user clicks "OK".

3.2.4. src/js/ui/SettingsPanel.js

    Purpose: Manages the visibility and interactions of the main dashboard settings sidebar.

    Key Functions:

        initSettingsPanel():

            Description: Initializes event listeners for opening/closing the settings panel, handling the global settings form submission, and managing the "Delete Settings JSON" button.

            Interactions:

                Listens for click events on #settings-toggle and #close-settings.

                Listens for submit on #global-settings-form to send update_settings or add_widget AJAX requests.

                Updates the enabled/disabled state of the "Add Widget" button and widget dimension inputs based on the "Show All Available Widgets" toggle.

                Handles the click event for #delete-settings-json-btn to reset the dashboard.

3.2.5. src/js/ui/WidgetSettingsModal.js

    Purpose: Manages the modal for adjusting individual widget dimensions.

    Key Functions:

        showWidgetSettingsModal(widgetName, widgetIndex, currentWidth, currentHeight):

            Description: Populates and displays the modal with the current widget's name, index, width, and height. It also disables inputs if "Show All Widgets" mode is active.

        initWidgetSettingsModal():

            Description: Initializes event listeners for closing the modal and handling the dimension form submission.

            Interactions:

                Listens for click on #close-widget-settings-modal.

                Listens for submit on #widget-dimensions-form to send update_single_widget_dimensions AJAX requests.

3.2.6. src/js/ui/WidgetManagementModal.js

    Purpose: Provides a centralized interface for managing active widgets (deactivating, potentially re-sizing in the future).

    Key Functions:

        initWidgetManagementModal():

            Description: Initializes event listeners for opening/closing the modal and saving changes.

            Interactions:

                Listens for click on #widget-management-nav-item to open the modal and trigger loadWidgetManagementTable().

                Listens for click on #close-widget-management-modal.

                Listens for click on #save-widget-management-changes-btn to send update_single_widget_dimensions AJAX requests for all modified widgets.

                Handles click events on "Deactivate" buttons within the table to send remove_widget_from_management AJAX requests.

        loadWidgetManagementTable():

            Description: Fetches the current active widget data from the backend (get_active_widgets_data action) and dynamically populates the management table.

3.2.7. src/js/ui/CreateWidgetModal.js

    Purpose: Manages the modal for creating new widget templates.

    Key Functions:

        initCreateWidgetModal():

            Description: Initializes event listeners for opening/closing the modal and handling the new widget creation form submission.

            Interactions:

                Listens for click on #open-create-widget-modal.

                Listens for click on #close-create-widget-modal.

                Listens for submit on #create-widget-form to send create_new_widget_template AJAX requests. Includes client-side validation for the widget ID.

3.2.8. src/js/features/WidgetActions.js

    Purpose: Handles interactive actions directly on the dashboard widgets.

    Key Functions:

        initWidgetActions():

            Description: Sets up a delegated event listener on document.body to capture clicks on widget action buttons.

            Interactions:

                Settings (.action-settings): Triggers showWidgetSettingsModal with the relevant widget data.

                Expand/Collapse (.action-expand): Toggles the maximized class on the widget, moving it to an overlay and adjusting its appearance. If the IDE widget is expanded, it triggers its initialization.

                Remove (.remove-widget): Prompts for confirmation and then sends an remove_widget_from_management AJAX request. This action is disabled if "Show All Widgets" mode is active.

                Expanded Overlay Click: Listens for clicks on the widget-expanded-overlay to collapse any currently maximized widget.

3.2.9. src/js/features/DragDropManager.js

    Purpose: Manages drag-and-drop functionality for widgets.

    Key Functions:

        initDragDropManager():

            Description: Initializes event listeners for drag-and-drop operations across the document body and the main content area.

            Interactions:

                dragstart: Identifies whether a sidebar widget item or an existing dashboard widget is being dragged. Sets dataTransfer accordingly (copy for new, move for reordering).

                dragover: Prevents default to allow dropping. Provides visual feedback (highlighting) for valid drop targets.

                dragleave: Clears visual feedback.

                drop:

                    If a new widget is dropped (copy effect), sends an add_widget AJAX request.

                    If an existing widget is reordered (move effect), reinserts the widget into the DOM at the new position and then calls saveWidgetOrder().

        saveWidgetOrder():

            Description: Gathers the data-widget-id attributes of all widgets in their current DOM order within the main-content area and sends this order to the backend via an update_widget_order AJAX request.

3.2.10. src/js/features/IdeWidget.js

    Purpose: Contains the specific client-side logic for the Integrated Development Environment (IDE) widget.

    Key Functions:

        initIdeWidget(widget):

            Description: Called when the IDE widget is expanded. It resets the editor state, identifies the specific IDE elements within that widget instance, and initiates the loading of the file tree from the root (.).

        initIdeEventListeners():

            Description: Sets up delegated event listeners on document.body to handle interactions within the IDE widget (since the widget itself can be dynamically added/removed from the main DOM flow during expansion).

            Interactions:

                File Tree Clicks: When a file or directory is clicked in the tree:

                    If a directory, it reloads the file tree for that directory.

                    If a file, it sends an ide_read_file AJAX request to fetch its content and displays it in the code editor. It also sets the editor's read-only state based on file writability.

                Editor Input: Marks the file as "Unsaved Changes" when the user types in the code editor.

                Save Button Click: Sends an ide_save_file AJAX request to save the current editor content.

4. Dashboard Functionality Overview
4.1. Widget Rendering and Sizing

    Widgets are rendered dynamically by index.php using the render_widget() helper function, which includes the respective PHP file from the widgets/ directory.

    Each widget div has data-widget-id, data-widget-index, data-current-width, and data-current-height attributes, which are used by JavaScript for identification and state management.

    CSS Custom Properties (--width, --height) are used to control the grid span of widgets, allowing for flexible sizing (e.g., width: 2.0 translates to grid-column: span 4 because the internal grid is doubled for half-unit precision).

4.2. Settings Management

    Global Settings: Accessed via the "Settings" button in the header. Changes are saved via an AJAX POST to api/dashboard.php with ajax_action=update_settings. The page reloads to apply CSS variable changes and potentially re-render widgets if "Show All Available Widgets" is toggled.

    Widget Dimensions: Individual widget dimensions can be adjusted via the cog icon on each widget. This opens a modal managed by WidgetSettingsModal.js, which sends an AJAX request to api/dashboard.php with ajax_action=update_single_widget_dimensions. A page reload applies the changes.

    Widget Management Modal: The "Widget Management" item in the sidebar opens a modal (WidgetManagementModal.js) that lists all active widgets. From here, users can adjust dimensions (which are saved on "Save All Widget Changes") or deactivate widgets. Deactivation sends an AJAX request to api/dashboard.php with ajax_action=remove_widget_from_management.

4.3. Widget Addition and Reordering

    Adding New Widgets:

        From Sidebar Library: Drag a widget item from the "Widget Library" section in the sidebar onto the main dashboard content area. This triggers an add_widget AJAX action to api/dashboard.php.

        From Settings Panel: Use the "Add Widget to Dashboard" dropdown in the global settings panel. This also triggers an add_widget AJAX action.

    Reordering Existing Widgets: Drag and drop widgets directly on the dashboard. The DragDropManager.js handles the visual reordering and then sends an update_widget_order AJAX request to api/dashboard.php to persist the new order.

    "Show All Available Widgets" Mode: A toggle in the global settings. When enabled, the dashboard will automatically display all widgets defined in config.php (including dynamic ones) and disable the ability to add or remove individual widgets. This is useful for development or showcasing all available components.

4.4. IDE Widget Functionality

    The IDE widget provides a basic file browser and code editor.

    When expanded, it loads the file tree from the server (api/ide.php with ide_list_files).

    Clicking on a file loads its content into the editor (api/ide.php with ide_read_file).

    Changes in the editor are marked as "Unsaved Changes". Clicking the "Save" button sends the content to the server (api/ide.php with ide_save_file).

    The FileManager.php class on the backend ensures that file operations are restricted to the APP_ROOT for security.

5. Styling (dashboard.css)

The dashboard features a "glassmorphism" design, heavily relying on CSS variables and modern CSS properties.

    CSS Variables: Defined in the :root pseudo-class for easy theme customization (colors, background, blur, shadows, transitions, border-radius).

    Layout: Uses CSS Grid for the main dashboard layout (header, sidebar, main content) and for the widget grid within the main-content area. Flexbox is used for internal component layouts.

    Glass Effect: Achieved using background: var(--glass-bg) (a semi-transparent color) and backdrop-filter: blur(var(--blur-amount)).

    Responsiveness: Media queries are extensively used to adapt the layout for different screen sizes (desktop, tablet, mobile), adjusting grid columns, sidebar behavior, and element sizes.

    Animations: Uses transition: var(--transition) for smooth visual feedback on hover, panel opening/closing, and widget expansion. will-change: transform is added to .widget.maximized to potentially optimize rendering of the expanded widget, reducing visual glitches.

    IDE Specific Styles: Dedicated styles for the file tree, editor, and status indicators within the IDE widget to provide a code-editor-like appearance.

6. Setup and Deployment

To get this project running, you will need a PHP-enabled web server (e.g., Apache, Nginx with PHP-FPM).

    Server Requirements:

        PHP 7.4+ (or newer)

        Web server (Apache, Nginx)

        mod_rewrite (if using Apache for clean URLs, though not strictly necessary for current AJAX setup)

    File Structure:

        Place all project files in your web server's document root or a subfolder.

        Ensure the following directory structure is maintained:

        your-project-root/
        ├── index.php
        ├── config.php
        ├── helpers.php
        ├── dashboard.css
        ├── widgets/
        │   ├── stats.php
        │   ├── tasks.php
        │   ├── ide.php
        │   └── ... (other widgets you create)
        ├── api/
        │   ├── dashboard.php
        │   ├── ide.php
        │   └── widget_creation.php
        └── src/
            ├── php/
            │   ├── DashboardManager.php
            │   └── FileManager.php
            └── js/
                ├── main.js
                ├── utils/
                │   └── AjaxService.js
                ├── ui/
                │   ├── MessageModal.js
                │   ├── SettingsPanel.js
                │   ├── WidgetSettingsModal.js
                │   ├── WidgetManagementModal.js
                │   └── CreateWidgetModal.js
                └── features/
                    ├── WidgetActions.js
                    ├── DragDropManager.js
                    └── IdeWidget.js

    Permissions:

        The web server user (e.g., www-data on Linux) must have write permissions to:

            The directory containing dashboard_settings.json (usually the project root, where index.php is).

            The directory containing dynamic_widgets.json (usually the project root).

            The widgets/ directory (for creating new widget templates via the dashboard).

        If these files/directories don't exist initially, ensure the parent directory is writable so PHP can create them.

    Access:

        Navigate to http://localhost/your-project-root/ (or your domain) in your web browser.

7. Future Enhancements and Considerations

    