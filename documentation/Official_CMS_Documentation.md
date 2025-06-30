Dashboard CMS Documentation

This document provides an exhaustive overview of the custom PHP-based dashboard Content Management System (CMS). It details the architecture, file structure, component interactions, data storage mechanisms, core functionalities, and best practices for future development and maintenance.
Table of Contents

    Recent Development History & Current Status (For Handover)

    System Overview and Architecture

        1.1. Core Technologies and Dependencies

        1.2. Architectural Paradigm

        1.3. Component Interaction Flow

    Core Files and Their Responsibilities

        2.1. index.php (Main Dashboard Entry Point)

        2.2. config.php (Configuration and Widget Definition)

        2.3. helpers.php (Utility Functions for Widgets)

        2.4. dashboard_settings.json (Persistent Data Storage)

        2.5. dashboard.css (Styling and Presentation)

        2.6. dashboard.js (Client-Side Interactivity)

    Widget Development Guide

        3.1. Widget File Structure (widgets/template.php)

        3.2. Widget Configuration ($_widget_config)

        3.3. Widget Rendering Function

        3.4. Widget Content States (Compact vs. Expanded)

    Settings and Data Persistence Deep Dive

        4.1. Loading Configuration (loadDashboardState())

        4.2. Saving Configuration (saveDashboardState())

        4.3. File Permissions: A Critical Note

    Core Functionalities Detailed

        5.1. Widget Expansion and Minimization

        5.2. Widget Removal

        5.3. Widget Addition (Drag-and-Drop & Button)

        5.4. "Show All Available Widgets" Mode

        5.5. Individual Widget Dimension Adjustment

    Debugging and Troubleshooting Guide

        6.1. General Debugging Practices

        6.2. Common Issues and Solutions

    Security Considerations

    Future Enhancements and Roadmap

        8.1. Drag-and-Drop Reordering

        8.2. Dashboard Layout Adjustments

    Changelog

0. Recent Development History & Current Status (For Handover)

This section provides a summary of the most recent development efforts and the current known status of the dashboard, intended for a smooth handover to a new development context.

Over the past few interactions, the primary focus has been on resolving critical layout and UI interaction issues:

    Horizontal Widget Flow (Side-by-Side Layout):

        Problem: Widgets were consistently stacking one per row, even on wide screens, despite CSS Grid configurations intended to enable multi-column layouts.

        Attempts & Fixes:

            Initial attempts involved explicit repeat(X, 1fr) column definitions and media queries in dashboard.css.

            Further refinement introduced minmax(0, 1fr) for the grid columns to ensure flexible space distribution.

            The most critical fix identified and implemented was adding min-width: 0; to the .widget CSS rule itself. This prevents the intrinsic minimum width of widget content from overriding the grid's ability to shrink columns, allowing multiple widgets to flow horizontally.

        Current Status (as of last user feedback): The user reported "No joy" with widgets arranging side-by-side. This indicates that despite the CSS fixes, the issue persists, suggesting a deeper problem with CSS application, browser caching, or an unknown override. A key diagnostic test involves reducing active widgets to only "Task Manager" (1.0 width) and "Calendar" (1.0 width) to see if they appear side-by-side.

    Modal Centering:

        Problem: The "Widget Management" modal and other confirmation modals were appearing in the top-left corner instead of being perfectly centered.

        Attempts & Fixes: Standard Flexbox centering (display: flex; justify-content: center; align-items: center; on the overlay and margin: auto; on the modal) was applied. To force these styles, !important flags were added to the relevant CSS properties (position, top, left, width, height, display, justify-content, align-items, margin) on .message-modal-overlay and .message-modal.

        Current Status (as of last user feedback): The user reported "No" for modals being perfectly centered. This is highly unusual given the !important overrides, pointing to a potential browser-specific rendering quirk or a very strong external style overriding even !important.

    Widget Deactivation Functionality:

        Problem: The "Deactivate" button within the "Manage Widgets" modal stopped working.

        Fix: The JavaScript event listener for the "Deactivate" buttons was refactored to use robust event delegation on the activeWidgetsTableBody. This ensures that even dynamically added rows and buttons are correctly handled. The fetch call and data payload for the deactivate_widget action were also verified.

        Current Status: This issue should be resolved with the regenerated dashboard.js.

Latest Codebase:

    index.php, dashboard.js, and dashboard.css were fully regenerated in the last interaction to ensure all fixes and best practices are consistently applied across the codebase.

    The dashboard.css includes min-width: 0; on .main-content and .widget, and repeat(X, minmax(0, 1fr)) for grid columns, along with !important for modal centering.

    The dashboard.js includes robust event delegation for dynamic elements and correct API calls.

Next Steps for a Fresh Instance:
The immediate priority is to re-verify the "Horizontal Widget Flow" and "Modal Centering" issues. The recommended diagnostic steps are:

    Hard Refresh: Ensure the user performs a hard refresh (Ctrl+F5 or Cmd+Shift+R) after applying the latest code.

    Specific Widget Test: Instruct the user to deactivate all widgets except "Task Manager" (1.0 width) and "Calendar" (1.0 width) and observe if they appear side-by-side. This isolates the grid behavior.

    Developer Tools Inspection: If issues persist, guide the user through inspecting the computed styles in the browser's developer tools for .main-content (checking display: grid, grid-template-columns) and .widget (checking grid-column: span var(--width); and min-width: 0;). For modals, inspect .message-modal-overlay and .message-modal for their display, position, top, left, margin, justify-content, and align-items properties, looking for any !important overrides being ignored or other conflicting styles.

1. System Overview and Architecture

The Dashboard CMS is designed as a lightweight, single-page application built primarily with PHP for server-side logic and persistence, and a combination of HTML, CSS, and JavaScript for a dynamic and responsive frontend. Its modular widget system allows for straightforward extensibility and maintainability.
1.1. Core Technologies and Dependencies

    Server-Side:

        PHP (7.4+ recommended): The backend language responsible for processing requests, managing sessions, reading/writing persistent data, and rendering the initial HTML page.

    Client-Side:

        HTML5: Provides the semantic structure of the dashboard.

        CSS3: Styles the dashboard, implements the glassmorphism aesthetic, and ensures responsiveness.

        JavaScript (ES6+): Handles all client-side interactivity, DOM manipulation, asynchronous communication, and user experience enhancements.

        Font Awesome 6 (CDN): Used for all icons throughout the dashboard (https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css). An active internet connection is required for icons to display.

1.2. Architectural Paradigm

The CMS adheres to a simplified Model-View-Controller (MVC) pattern, albeit not strictly separated into distinct directories for each component due to the project's scale.

    Model (Data Management): Handled implicitly by dashboard_settings.json (the persistent data store) and the PHP functions (loadDashboardState, saveDashboardState) that interact with it. Widget data itself is also part of the model, often hardcoded in widget PHP files or fetched by them.

    View (Presentation): Primarily index.php (for the overall dashboard structure and dynamically injected CSS variables) and individual widget PHP files (for widget-specific HTML content). dashboard.css heavily influences the view's aesthetics.

    Controller (Logic/Orchestration): index.php acts as the main controller, dispatching actions based on POST requests. dashboard.js functions as a client-side controller, handling user interactions and initiating server requests.

1.3. Component Interaction Flow

The system operates in a request-response cycle, with client-side JavaScript mediating user interactions and server communication.

    Initial Page Load / Refresh:

        User navigates to index.php.

        index.php (PHP) starts a session, sets cache control headers, includes config.php and helpers.php.

        loadDashboardState() reads dashboard_settings.json or uses defaults, populating the $settings array and $_SESSION['active_widgets'].

        index.php renders the HTML, dynamically injecting CSS variables from $settings and looping through $_SESSION['active_widgets'] to call render_widget() for each.

        render_widget() (from helpers.php) includes and executes the relevant widget PHP file, capturing its HTML output via output buffering.

        The complete HTML is sent to the browser.

        dashboard.js loads and initializes event listeners, applying initial UI states (e.g., disabling buttons based on "Show All Widgets" mode).

    User Interaction (e.g., Remove Widget, Save Settings, Change Widget Size):

        User clicks an interactive element (e.g., "X" button on a widget).

        dashboard.js event listener captures the click.

        dashboard.js determines the action_type and relevant data (e.g., widget_index, new_width).

        A fetch API call (POST request) is made to api.php or index.php (depending on the implementation, currently api.php is assumed for actions like save_settings, reset_layout, get_dashboard_settings, update_active_widgets, create_widget_template, deactivate_widget, add_widget, remove_widget, update_widget_positions).

    Server-Side Processing (POST Request):

        index.php (or api.php) receives the POST request.

        $_SESSION is loaded (resuming the previous session).

        loadDashboardState() is called again to ensure PHP has the absolute latest persistent state from dashboard_settings.json before processing the new request.

        PHP checks the action parameter (or action_type) to determine the requested operation.

        Based on the action, $_SESSION['active_widgets'] or other session-based settings are modified. Permissions checks (e.g., if "Show All" mode is active) are applied.

        The $has_state_changed flag is set to true if modifications occurred.

        If $has_state_changed is true, saveDashboardState() is called to write the updated $settings array (including $_SESSION['active_widgets']) back to dashboard_settings.json.

        A JSON response indicating success or failure is sent back to the client.

    Browser Re-rendering:

        dashboard.js receives the JSON response.

        If successful, location.reload() is often called to trigger a full page refresh, ensuring the latest state from index.php is rendered. This is crucial due to the stateless nature of PHP and the need to re-render the entire grid.

        dashboard.js re-initializes, attaching event listeners to the new DOM elements and reflecting the updated dashboard state (e.g., a widget is now gone, or its size has changed).

2. Core Files and Their Responsibilities
2.1. index.php (Main Dashboard Entry Point)

This is the central file that orchestrates the entire dashboard, acting as the primary controller and view renderer.

    Purpose:

        Initializes the PHP session.

        Manages HTTP caching headers.

        Loads current dashboard settings and active widgets from persistent storage (dashboard_settings.json).

        Processes all incoming POST requests (add/remove widgets, update global settings, update individual widget dimensions, reorder widgets).

        Saves changes back to persistent storage.

        Renders the complete HTML structure of the dashboard, dynamically inserting content and styling variables based on the current state.

        Includes all necessary CSS and JavaScript files.

    Key Sections & Logic:

        PHP Error Reporting: ini_set('display_errors', 1); error_reporting(E_ALL); are at the very top for development-time error visibility. These should be disabled or adjusted for production environments.

        Cache Control Headers: A series of header() calls (Cache-Control: no-store, Pragma: no-cache, Expires) are crucial. They instruct browsers and proxies not to cache the index.php file, ensuring that after every POST request and subsequent reload, the client receives the most up-to-date dashboard state from the server. Without these, users might see stale data.

        File Includes: require_once 'config.php'; and require_once 'helpers.php'; ensure that global configurations and utility functions are available.

        $default_dashboard_state: A PHP array defining the default values for all dashboard settings and the initial set of active widgets. This is used as a fallback if dashboard_settings.json is missing or invalid. Note that active_widgets here only defines id and position; actual dimensions are pulled from config.php or persistent storage.

        loadDashboardState() Function Call: This is executed early in the script to populate $current_dashboard_state and $settings with the latest configuration from dashboard_settings.json.

        Session Synchronization: $_SESSION['active_widgets'] = $current_dashboard_state['active_widgets']; ensures that the session's understanding of which widgets are active matches the persistent state. All subsequent in-request logic modifies $_SESSION first.

        POST Request Handling (if ($_SERVER['REQUEST_METHOD'] === 'POST')):

            This block is the core of the server-side controller. It dispatches actions based on the $_POST['action_type'] parameter received from dashboard.js.

            action_type === 'add_widget': Appends a new widget entry to $_SESSION['active_widgets']. It retrieves the default width and height from $available_widgets (defined in config.php) for the new entry. This action is disabled if show_all_available_widgets is true.

            action_type === 'remove_widget': Uses unset() to remove a widget at a specific widget_index from $_SESSION['active_widgets'], then array_values() to re-index the array. This action is disabled if show_all_available_widgets is true.

            action_type === 'update_settings': Updates global dashboard settings (title, colors, animations, "Show All Widgets" toggle). Critically, if show_all_available_widgets is just turned on, $_SESSION['active_widgets'] is dynamically repopulated with all widgets from $available_widgets (with their default dimensions), effectively resetting the layout to "all on."

            action_type === 'update_widget_dimensions': Receives widget_index, new_width, and new_height for a specific widget. It updates the width and height properties for that widget's entry within $_SESSION['active_widgets']. This action is also disabled if show_all_available_widgets is true.

            action_type === 'update_widget_positions': Receives an array of positions (containing id, width, height, position) for all active widgets. It completely rebuilds $_SESSION['active_widgets'] based on this new order.

            action_type === 'deactivate_widget': Removes a widget by its id from the $_SESSION['active_widgets'] array.

            action_type === 'reset_layout': Resets $_SESSION['active_widgets'] to the default layout from config.php.

            $has_state_changed and saveDashboardState(): If any of the above actions modify the session state, $has_state_changed is set to true, triggering a call to saveDashboardState() to write the updated configuration to dashboard_settings.json. Error logging is used if saving fails.

        HTML Output: The script then generates the full HTML page.

            Dynamic CSS Variables: The <style> block in the <head> uses PHP echo statements to inject dynamic values for CSS custom properties (--accent, --glass-bg, --blur-amount) directly from the $settings array. This allows the theme to change instantly based on user preferences.

            Widget Rendering Loop: The main-content section iterates through the $widgets_to_render array.

                If show_all_available_widgets is true, $widgets_to_render contains all widgets from config.php (with their default dimensions).

                Otherwise, $widgets_to_render contains the actively managed widgets from $_SESSION['active_widgets'] (which include user-defined dimensions).

                For each widget, it dynamically sets CSS variables (--width, --height) for grid spanning using the width and height properties from the $widget array.

                data-* attributes (data-widget-id, data-widget-index, data-current-width, data-current-height) are added to each .widget div and its "Settings" action button. These attributes are essential for JavaScript to identify and interact with specific widgets without relying on potentially volatile DOM indices.

                render_widget($widget_id) is called to embed the actual HTML content of the widget.

        Modal Structures: HTML for the widget-expanded-overlay, message-modal-overlay, and widget-settings-modal-overlay is included. These are initially hidden by CSS and controlled by JavaScript.

        JavaScript Include: <script src="dashboard.js"></script> is placed at the end of the <body> for optimal loading performance, ensuring the DOM is fully parsed before the script attempts to manipulate it.

2.2. config.php (Configuration and Widget Definition)

This file acts as the global configuration and the registry for all available widgets in the CMS.

    Purpose:

        Defines the path to the persistent settings file.

        Scans the widgets/ directory to discover and register all modular widgets, extracting their metadata.

    Key Logic:

        DASHBOARD_SETTINGS_FILE: A define() constant that provides the absolute path to dashboard_settings.json. __DIR__ ensures the path is relative to the config.php file itself, making the setup portable.

        $available_widgets Array: This array is populated dynamically. It acts as the definitive list of all widgets that can be added to the dashboard.

        Widget Discovery Loop:

            $widget_directory = __DIR__ . '/widgets/';: Defines the path to the directory where widget PHP files reside.

            glob($widget_directory . '*.php'): Retrieves an array of all PHP file paths within the widgets/ directory.

            Isolated Inclusion ((function($file) { ... })($file_path);): For each *.php file found:

                An anonymous function is immediately invoked, passing the widget file path as an argument. This creates a temporary, isolated variable scope for each widget file.

                ob_start(); include $file; ob_end_clean();: This is a critical pattern. ob_start() begins output buffering. include $file; executes the widget's PHP code. Widget files are expected to define a $_widget_config array. If they also echo content directly, ob_end_clean() discards that output, preventing it from interfering with config.php's primary role of reading metadata, not rendering HTML.

                The anonymous function returns the $_widget_config array defined within the widget file.

            Metadata Extraction & Validation: The returned $_widget_config is then validated (checking for name and icon). The widget_id is derived from the filename (e.g., stats.php becomes stats).

            Default Dimensions: If a widget's $_widget_config does not explicitly specify width or height, they default to 1. These defaults are used when a widget is initially added to the dashboard or when "Show All Widgets" mode is active.

            Error Logging: error_log() statements are used to report issues like missing widget directories or invalid $_widget_config structures to the server's error logs.

2.3. helpers.php (Utility Functions for Widgets)

This file contains helper functions, primarily for rendering widget content.

    Purpose:

        Provides a centralized function to include and execute widget PHP files and capture their generated HTML.

    Key Logic:

        render_widget($widget_id) Function:

            $widget_file = __DIR__ . '/widgets/' . $widget_id . '.php';: Constructs the full path to the specific widget's PHP file.

            if (file_exists($widget_file)): Ensures the target widget file exists before attempting to include it.

            Output Buffering (ob_start(), ob_get_clean()): This is the core mechanism.

                ob_start(): Activates the output buffer. Any HTML generated by the included widget file is stored in this buffer, not sent directly to the browser.

                include $widget_file;: Executes the widget's PHP file. This file is expected to define and call a render_YOUR_WIDGET_ID_widget() function that echoes the widget's HTML.

                $widget_content = ob_get_clean();: Captures all content from the buffer into the $widget_content variable and then turns off buffering.

            Return Value: The function returns the captured HTML content string. If the widget file does not exist, it returns a generic error message indicating the problem.

            This pattern is crucial for maintaining control over the HTML output flow, preventing widgets from prematurely sending content to the browser and allowing index.php to construct the overall page seamlessly.

2.4. dashboard_settings.json (Persistent Data Storage)

dashboard_settings.json is a vital JSON file automatically created and managed by the CMS. It serves as the durable storage mechanism for all dashboard configurations that must persist across user sessions and server restarts. It functions as the "database" for this lightweight CMS.

    Purpose: To store the long-term state of the dashboard, including:

        Global theme and animation preferences.

        The show_all_available_widgets toggle state.

        The complete list of active_widgets on the dashboard, including their specific id, position, and crucially, their custom width and height dimensions.

    Structure Detail (Example):

    {
        "title": "MPS Monitor Dashboard",
        "accent_color": "#a214f1",
        "glass_intensity": 0.6,
        "blur_amount": "10px",
        "enable_animations": true,
        "show_all_available_widgets": false,
        "active_widgets": [
            {
                "id": "activity",
                "position": 1,
                "width": 1,   // Custom width for this specific instance
                "height": 1   // Custom height for this specific instance
            },
            {
                "id": "calendar",
                "position": 2,
                "width": 1,
                "height": 2
            },
            {
                "id": "debug_info",
                "position": 3,
                "width": 2,
                "height": 2
            },
            {
                "id": "printers", // New widget example
                "position": 4,
                "width": 1,
                "height": 1
            }
        ]
    }

        Top-Level Keys: Represent global dashboard settings.

        active_widgets (Array of Objects): This array is the core of the layout persistence. Each object within it represents an active widget instance on the dashboard.

            "id": (string) Matches the filename of the widget (e.g., "stats" for stats.php).

            "position": (integer) A simple incremental number indicating its order in the array. This determines the rendering order in the grid (though drag-and-drop reordering would require updating this).

            "width": (integer) The number of grid columns this specific widget instance should span. This value overrides the widget's default width from its $_widget_config once it's customized by the user.

            "height": (integer) The number of grid rows this specific widget instance should span. This value overrides the widget's default height from its $_widget_config once it's customized by the user.

    Loading Mechanism (loadDashboardState()):

        This function in index.php is responsible for reading and parsing this JSON file on every page load.

        It uses file_get_contents() to read the entire file and json_decode(..., true) to convert it to a PHP associative array.

        If the file is missing or malformed, it defaults to $default_dashboard_state.

        Crucially, loadDashboardState() now also ensures that each active_widgets entry has width and height properties. If these are missing from the JSON (e.g., from an older dashboard_settings.json or a newly added widget), it populates them with the default width and height specified in the widget's $_widget_config (which config.php makes available via $available_widgets).

    Saving Mechanism (saveDashboardState()):

        This function in index.php is invoked whenever the dashboard's state changes (widget added/removed, settings updated, widget dimensions changed).

        It takes the current complete dashboard state (PHP array, including the updated $_SESSION['active_widgets']) as an argument.

        json_encode(..., JSON_PRETTY_PRINT) converts this PHP array into a human-readable JSON string. JSON_PRETTY_PRINT is important for inspectability.

        file_put_contents() writes this JSON string to the DASHBOARD_SETTINGS_FILE location, overwriting previous content.

    File Permissions (Critical): Refer to Section 4.3. File Permissions: A Critical Note for vital information regarding file system permissions required for persistence.

2.5. dashboard.css (Styling and Presentation)

The dashboard.css file is the central stylesheet that dictates the visual presentation, aesthetic, and responsiveness of the entire dashboard CMS.

    Purpose:

        Defines the overall visual theme (colors, fonts, shadows).

        Implements the glassmorphism effect.

        Ensures responsive layout across devices.

        Controls visibility and appearance of interactive elements and widget content states.

    Key Sections & Logic:

        :root CSS Custom Properties (Variables):

            Defines global variables like --bg-primary, --accent, --blur-amount, etc.

            These variables are dynamically set within an inline <style> block in index.php based on user preferences saved in dashboard_settings.json. This allows for real-time theme customization without CSS file modification.

        Basic Resets & Typography: Standard universal resets (* { margin: 0; padding: 0; box-sizing: border-box; }) and font-family declarations.

        Body Styling: Sets the background gradient and default text color. overflow: auto; allows the main page to scroll if content exceeds viewport height. html, body now explicitly have height: 100%; to ensure correct fixed positioning for modals.

        body.expanded-active: This class is added to the <body> by dashboard.js when a widget is maximized. overflow: hidden; is applied here to prevent the background dashboard from scrolling while a modal widget is open.

        widget-expanded-overlay:

            position: fixed; and width: 100%; height: 100%; make it cover the entire viewport.

            background: rgba(0, 0, 0, 0.7); creates the darkened overlay effect.

            z-index: 999; ensures it sits above the main dashboard content.

            display: flex; justify-content: center; align-items: center; are crucial for horizontally and vertically centering the maximized widget within the overlay.

            opacity: 0; visibility: hidden; transition: ...; provide a smooth fade-in/out effect when activated.

        widget-placeholder:

            display: none; by default.

            When a widget is maximized and moved to the overlay, dashboard.js makes its .widget-placeholder visible. This element occupies the original grid space of the moved widget, preventing other grid items from reflowing and maintaining the layout structure.

        dashboard (Main Grid Container):

            display: grid; grid-template-columns: var(--sidebar-width) 1fr; grid-template-rows: var(--header-height) 1fr;: Establishes the primary two-column layout (sidebar and main content) with a header row.

            height: 100vh; ensures the dashboard takes full viewport height.

        Header, Sidebar, Widget List Styling: Extensive styles for the header, sidebar, navigation items, and widget library items. They utilize glassmorphism (via background: var(--glass-bg); border: var(--glass-border); backdrop-filter: blur(var(--blur-amount));) and neomorphic shadow effects.

        btn.disabled, widget-action.disabled, widget-item.disabled:

            These classes are added by JavaScript to elements that are temporarily inactive (e.g., when "Show All Widgets" mode is enabled, or when specific widget settings are disabled).

            opacity: 0.5; cursor: not-allowed; pointer-events: none; filter: grayscale(100%);: Provide clear visual and functional feedback of disabled state.

            transform: none !important; box-shadow: none !important;: Crucially override any hover effects to prevent them from activating on disabled elements.

        main-content (Widget Grid Area):

            display: grid; grid-template-columns: repeat(8, minmax(0, 1fr));: Sets up a responsive grid for widgets, using minmax(0, 1fr) to ensure columns are flexible and can shrink, allowing multiple widgets to fit horizontally. This is crucial for the side-by-side layout.

            grid-auto-rows: minmax(100px, auto);: Ensures a minimum row height for widgets and allows rows to expand based on content.

            min-width: 0; and box-sizing: border-box; are applied to prevent content from implicitly forcing the grid columns wider than intended.

        .widget:

            display: flex; flex-direction: column;: Makes the widget content a flex container, allowing its header and content to stack vertically.

            grid-column: span var(--width); grid-row: span var(--height);: These are critical for individual widget dimension control. The var(--width) and var(--height) CSS variables are dynamically set inline by PHP based on the width and height properties stored for each widget in active_widgets.

            min-width: 0; is critically important here. It allows the grid item (the widget) to shrink below its content's intrinsic minimum size, enabling the 1fr columns in the parent grid to distribute space correctly and allow widgets to flow horizontally.

            transition: var(--transition);: Enables smooth animations for hover effects and other transitions.

        widget-content: flex: 1; overflow-y: auto; ensures the content area within a widget takes up available space and scrolls if its content overflows vertically.

        Widget Content States (.compact-content, .expanded-content):

            .widget .compact-content { display: flex; }: By default, the compact-content section within any widget is visible.

            .widget .expanded-content { display: none; }: By default, the expanded-content section is hidden.

            .widget.maximized .compact-content { display: none; }: When the parent .widget element gains the maximized class, the compact-content is hidden.

            .widget.maximized .expanded-content { display: flex; flex: 1; overflow-y: auto; }: When the parent .widget is maximized, the expanded-content is revealed, taking up all available vertical space and allowing its internal content to scroll. This is the core CSS logic for differentiating widget views based on their expansion state.

        stat-card, task-item, calendar-grid, note, activity-item: Specific styling for the content within the various default widgets.

        settings-panel (Global Settings Sidebar): position: fixed; right: -350px; (to hide off-screen initially), width: 350px;, height: 100%;, z-index: 100;, and transition: ...;. The right: 0; rule on .settings-panel.active slides it into view.

        overlay: A generic transparent overlay used for the global settings panel. Its width and height are now 100% !important; for robust coverage.

        message-modal-overlay and message-modal: These classes are reused for both the general confirmation/alert modal and the specific widget settings modal. They provide consistent styling for modal dialogs.

            z-index: 1001 !important; ensures they appear on top of other elements like the settings panel.

            position: fixed !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; are used to aggressively force the overlay to cover the entire viewport.

            display: flex !important; justify-content: center !important; align-items: center !important; centrally align the modal content, with !important to override any conflicting styles.

            opacity: 0; visibility: hidden; for smooth transitions.

            .message-modal itself uses margin: auto !important; for self-centering within its flex parent.

        Responsive Design Media Queries: Define how the grid adapts to different screen sizes, adjusting the number of 1fr columns.

2.6. dashboard.js (Client-Side Interactivity)

The dashboard.js file is the powerhouse of client-side interactivity, bringing the static HTML and CSS to life. It manages all user interactions, orchestrates dynamic DOM manipulations, and handles communication with the PHP backend.

    Purpose:

        Handles opening/closing of settings panels and modals.

        Manages widget expansion/minimization.

        Implements drag-and-drop for adding and reordering widgets.

        Collects and submits data for adding/removing/deactivating widgets and updating settings (global and individual widget dimensions).

        Provides visual feedback and disables elements based on dashboard state ("Show All Widgets" mode).

    Key Logic & Functions:

        DOMContentLoaded Listener: All JavaScript code is wrapped in this listener, ensuring the entire HTML DOM structure is fully loaded and parsed before any script attempts to interact with it.

        Global Settings Panel Controls: Event listeners for #openSettingsBtn, #closeSettingsBtn, and #settingsOverlay manage the active class on #settingsPanel and display on #settingsOverlay to show/hide the global settings sidebar.

        showMessageModal(title, message, buttons, isLarge):

            This function creates a custom, consistent modal for alerts and confirmations, replacing native alert() and confirm().

            It dynamically sets the modal's title and content.

            buttons: An array of button configurations, allowing flexible actions and auto-hiding.

            isLarge: A boolean to apply a wider modal style.

        Widget Management Modal Logic:

            openWidgetManagementModal(): Populates the table of active widgets with their current dimensions and status.

            populateWidgetManagementTable(): Fetches current active widgets and their configurations, dynamically creating table rows with input fields for width/height and a "Deactivate" button.

            saveWidgetManagementChanges(): Collects updated width/height values from the table and sends them to the server.

            createNewWidgetTemplate(): Handles the creation of new widget PHP files.

        Widget Deactivation Logic (Delegated Event Listener):

            Crucial Fix: Instead of attaching individual click listeners to every "Deactivate" button (which can break when the table is re-rendered), a single delegated event listener is attached to activeWidgetsTableBody.

            activeWidgetsTableBody.addEventListener('click', async (event) => { ... }); checks if the clicked element or its ancestor matches .btn-deactivate-widget. This ensures that dynamically added buttons are always clickable.

            It triggers a confirmation modal before sending an action: 'deactivate_widget' request to api.php.

        Drag-and-Drop Functionality (Adding & Reordering):

            Adding: dragstart on sidebar widgets, dragover/drop on mainContent. Sends action: 'add_widget' to api.php.

            Reordering: dragstart on active widgets, dragover/drop on mainContent. A visual widget-placeholder is used to indicate the drop position. After drop, updateWidgetPositions() is called to send an action: 'update_widget_positions' request to api.php with the new order.

        Widget Actions (Maximize/Remove):

            Uses event delegation on mainContent for .maximize-widget and .remove-widget buttons.

            maximize-widget: Toggles the maximized class on the widget and expanded-active on body for full-screen view.

            remove-widget: Triggers a confirmation modal and sends action: 'remove_widget' to api.php.

        API Communication: All server interactions are handled via fetch API calls to api.php (or index.php for direct form submissions). Responses are typically JSON, and location.reload() is often used on success to ensure the UI reflects the latest server state.

        UI State Synchronization: Functions like updateThemePreview() and logic within event listeners ensure that the UI accurately reflects the current dashboard settings and widget states.

3. Widget Development Guide

The CMS's modular design makes adding new widgets straightforward. Follow this guide to create custom functionalities for your dashboard.
3.1. Widget File Structure (widgets/template.php)

All widget definitions reside in individual PHP files within the widgets/ directory.

    Naming Convention: The filename (without the .php extension) serves as the unique widget_id. For example, a widget file named my_custom_chart.php will have the widget_id of my_custom_chart.

    Template: Use widgets/template.php as a starting point. It contains all the necessary structural comments and code snippets.

3.2. Widget Configuration ($_widget_config)

Every widget file must define a $_widget_config associative array at its top. This array provides essential metadata that config.php reads to register the widget and display it in the "Widget Library."

<?php
// widgets/your_widget_id.php

$_widget_config = [
    'name' => 'Your Widget Name', // Required: Display name in the sidebar and widget header.
    'icon' => 'cube',             // Required: A Font Awesome 6 icon class name (e.g., 'chart-bar', 'clipboard', 'server').
    'width' => 1,                 // Optional (Default: 1): Initial number of grid columns this widget spans.
    'height' => 1                 // Optional (Default: 1): Initial number of grid rows this widget spans.
];

// ... rest of the widget code ...

    name: This string is displayed in the "Widget Library" in the sidebar and in the header of the widget itself when added to the dashboard.

    icon: This should be a valid Font Awesome 6 icon class (e.g., 'users', 'cloud-sun', 'clipboard'). These are used to display an icon next to the widget's name in the library and header. Ensure the Font Awesome CDN is accessible.

    width and height: These optional integer values define the default dimensions of the widget in grid units when it is initially added to the dashboard. If not specified, they default to 1. Users can later customize these dimensions via the widget's individual settings, which will then override these defaults in dashboard_settings.json.

3.3. Widget Rendering Function

Each widget file must contain a PHP function responsible for generating its HTML content.

    Function Naming Convention: The function name must follow the precise format: render_YOUR_WIDGET_ID_widget(), where YOUR_WIDGET_ID is the filename of your widget without the .php extension.

        Example: For my_widget.php, the function would be render_my_widget_widget().

    if (!function_exists('...')) Guard:

        It is absolutely critical to wrap your function definition within if (!function_exists('render_YOUR_WIDGET_ID_widget')) { ... }.

        Reason: The widget's PHP file is included twice during a typical page load cycle: once by config.php (to read $_widget_config) and again by helpers.php (to render_widget()). Without this guard, PHP would throw a Fatal error: Cannot redeclare function ....

    Content Output: The function should use echo or print statements to output the HTML that makes up your widget's content. This HTML will be captured by helpers.php (via output buffering) and then embedded into the main index.php page.

    Returning the Function Name: At the very end of your widget's PHP file (after the function definition), you must have:

    return 'render_YOUR_WIDGET_ID_widget';

    This line informs helpers.php which function it needs to call to get the content for this specific widget.

3.4. Widget Content States (Compact vs. Expanded)

Widgets are designed to have two distinct content states, automatically managed by CSS based on whether the widget is in its normal grid position or expanded into a modal view.

    compact-content (<div class="compact-content">...</div>)

        Purpose: This div should contain the summary, snapshot, or basic information of your widget. It is visible when the widget is in its default grid dimensions.

        Content: Keep this content concise and visually appealing for a small space. Examples include a single metric, a status indicator, or a very brief list.

        Visibility: Controlled by dashboard.css. By default, display: flex; when the widget is not maximized.

    expanded-content (<div class="expanded-content">...</div>)

        Purpose: This div should contain detailed data, tables, charts, forms, or more interactive elements that require a larger view. It is visible only when the widget is maximized (expanded into the modal).

        Content: This is where you put the "drilldown" information. Ensure it's designed to fit well within a larger modal window. Use internal scrolling (overflow-y: auto;) for large tables or lists within this div.

        Visibility: Controlled by dashboard.css. By default, display: none;. When the parent .widget has the maximized class, display: flex; is applied.

    Implementation Example (within render_YOUR_WIDGET_ID_widget()):

    if (!function_exists('render_printers_widget')) {
        function render_printers_widget() {
            // ... data fetching logic ...

            // Compact View (shown when widget is small)
            echo '<div class="compact-content">';
            echo '    <p>Printers Online: ' . htmlspecialchars($printers_online) . '</p>';
            echo '</div>'; // End compact-content

            // Expanded View (shown when widget is maximized)
            echo '<div class="expanded-content">';
            echo '    <h4>Detailed Printer Status</h4>';
            echo '    <table>';
            // ... detailed table rows ...
            echo '    </table>';
            echo '</div>'; // End expanded-content
        }
    }

    By structuring your widget content this way, the CSS automatically handles the display toggling, allowing you to focus on rendering the appropriate data for each state.

4. Settings and Data Persistence Deep Dive

The CMS relies on a single JSON file, dashboard_settings.json, for all persistent storage. This "flat-file database" approach simplifies deployment and management for smaller applications.
4.1. Loading Configuration (loadDashboardState())

The loadDashboardState() function, implicitly defined within index.php, is responsible for retrieving the entire dashboard's configuration.

    Location: Defined within index.php.

    Execution: Called once at the beginning of every index.php request cycle.

    Process:

        File Existence Check: It first checks if DASHBOARD_SETTINGS_FILE (defined in config.php) exists.

        Read and Decode: If the file exists, file_get_contents() reads its entire content, and json_decode($json_data, true) converts the JSON string into a PHP associative array. The true argument is crucial for ensuring the JSON objects are decoded into associative arrays rather than standard PHP objects, which simplifies property access.

        Default Fallback: A PHP array $default_dashboard_state (defined at the top of index.php) provides a complete baseline configuration. If dashboard_settings.json is not found, or if json_decode fails (indicating malformed JSON), this default state is used. This ensures the dashboard always has a valid configuration, preventing crashes on initial load or data corruption.

        Recursive Merging (array_replace_recursive):

            $final_state = array_replace_recursive($default_dashboard_state, $loaded_state);

            Purpose: This is a sophisticated merging strategy. It combines the $default_dashboard_state with any $loaded_state from the JSON file. This ensures that if new configuration keys or sections are added to $default_dashboard_state in a CMS update, existing dashboard_settings.json files from previous versions will automatically inherit these new defaults without losing any user-defined settings. It intelligently merges arrays, handling nested arrays correctly.

        Active Widget Dimension Enrichment: A critical loop within loadDashboardState() ensures that every widget entry in active_widgets (whether loaded from JSON or from defaults) has explicit width and height properties.

            It iterates through active_widgets. For each widget, it consults $available_widgets (from config.php) to get its default width and height.

            If the active_widgets entry from dashboard_settings.json already contains width or height (meaning the user previously customized it), those values are preserved. Otherwise, the widget's default dimensions from config.php are applied. This guarantees that every widget has defined dimensions for CSS Grid rendering.

        Return Value: The function returns the fully constructed and validated $final_state array.

    Session Synchronization: After loadDashboardState() is called, $_SESSION['active_widgets'] = $current_dashboard_state['active_widgets']; ensures that the PHP session's active widget list is always synchronized with the persistently loaded state. This is crucial because subsequent POST request processing directly manipulates $_SESSION.

4.2. Saving Configuration (saveDashboardState())

The saveDashboardState() function, also implicitly defined within index.php, is responsible for writing the current dashboard configuration back to dashboard_settings.json.

    Location: Defined within index.php.

    Execution: Called at the end of the POST request handling block if the $has_state_changed flag is true.

    Process:

        State Preparation: The function receives a complete PHP array representing the dashboard's current state (which at this point includes all global settings and the latest active_widgets from $_SESSION).

        JSON Encoding: json_encode($state, JSON_PRETTY_PRINT) converts the PHP array into a JSON string. JSON_PRETTY_PRINT is highly recommended for readability, making the JSON file easy to inspect and debug by developers.

        File Write: file_put_contents(DASHBOARD_SETTINGS_FILE, $json_data) attempts to write the JSON string to the specified file. This operation will overwrite any existing content in dashboard_settings.json.

        Robust Error Handling and Logging:

            json_encode() Failure: The function explicitly checks if json_encode() returned false (indicating a JSON encoding error, e.g., attempting to encode a resource type). If so, it logs a specific error message to the server's error log.

            file_put_contents() Failure: It checks the return value of file_put_contents(). If false (indicating a write failure), it constructs a detailed error message.

            Permission Diagnostics: This error message includes checks for common file permission issues:

                !is_writable(dirname(DASHBOARD_SETTINGS_FILE)): Checks if the parent directory is writable.

                file_exists(...) && !is_writable(DASHBOARD_SETTINGS_FILE): Checks if the file exists but is not writable.

                If neither specific permission issue is found, it provides a general "Unknown write error."

            All detailed error messages are logged to the server's PHP error log (error_log()). This is critical because file_put_contents failures often occur silently from the browser's perspective, making the server logs the only place to diagnose the root cause.

        Return Value: Returns true on successful save, false otherwise.

4.3. File Permissions: A Critical Note

This is the most common reason for persistence issues. For the CMS to successfully save changes to dashboard_settings.json, the web server's user (the user account under which your PHP scripts execute) must have appropriate write permissions to the file and its containing directory.

    Web Server User:

        On Linux/Apache/Nginx, this is typically www-data, apache, nginx, or a custom user.

        On Windows/IIS, it's often IIS_IUSRS or IUSR.

    Required Permissions:

        Directory containing dashboard_settings.json: Must be writable by the web server user. This allows PHP to create the file if it doesn't exist, or to create temporary files during the write process.

            Recommended (Linux): chmod 775 [your_dashboard_directory] (allows owner/group read/write/execute, others read/execute).

            Less Secure (Linux): chmod 777 [your_dashboard_directory] (grants everyone full permissions; avoid in production unless absolutely necessary for specific, highly controlled scenarios).

        dashboard_settings.json file itself: Must be writable by the web server user.

            Recommended (Linux): chmod 664 dashboard_settings.json (allows owner/group read/write, others read).

    How to Check/Change (Linux Example):

        Check current permissions: Open your terminal, navigate to the directory where dashboard_settings.json is located, and run ls -l. Look for the permissions string (e.g., -rw-r--r--).

        Check ownership: Run ls -l. Note the user and group owning the file/directory.

        Change permissions (if needed):

            To make a directory writable by the web server group: sudo chown :www-data [your_dashboard_directory] (if www-data is your web server group) followed by sudo chmod 775 [your_dashboard_directory].

            To make the file writable: sudo chmod 664 dashboard_settings.json.

            If you're unsure or facing persistent issues, temporarily using chmod 777 on the directory and chmod 666 on the file can confirm if it's a permission problem, but revert immediately in production!

5. Core Functionalities Detailed
5.1. Widget Expansion and Minimization

This feature allows users to temporarily enlarge any widget into a modal-like full-screen view for focused interaction, and then revert it to its original size and position. It leverages a combination of JavaScript for DOM manipulation and CSS for visual transitions.

    Trigger: Clicking the "Expand" icon (fa-expand / fa-compress) on a widget's header.

    Client-Side (JavaScript toggleWidgetExpansion function):

        Maximization Process (if (!widget.classList.contains('maximized'))):

            State Capture: The function meticulously captures the widget's current context. It identifies the id of its immediate parent container (main-content typically) and its precise numerical index (Array.from(widget.parentNode.children).indexOf(widget)) among that parent's children. This contextual information is vital for the precise restoration of the widget later. This data is then stored in custom data-* attributes (data-original-parent-id and data-original-index) on a hidden .widget-placeholder element that resides inside the widget itself.

            Class Toggling: The maximized class is dynamically added to the target widget element. Concurrently, the expanded-active class is added to the <body> element, and the active class is applied to the #widget-expanded-overlay. These class additions trigger predefined CSS rules that manage the visual transformations and visibility states for the modal display.

            DOM Relocation (The Core Mechanism): The entire DOM element representing the widget is physically detached from its original position within the CSS Grid layout (#mainContent) and is then appended as a child to the #widget-expanded-overlay element. This critical step moves the widget out of the normal document flow and places it within the dedicated full-screen modal container.

            Placeholder Activation: The .widget-placeholder element, which was previously hidden (display: none), is now explicitly made visible (style.display = 'block'). This placeholder effectively reserves and occupies the original grid cell where the widget resided, preventing other surrounding widgets from collapsing, reflowing, or otherwise disrupting the grid layout in the main dashboard area.

            Icon Change: The Font Awesome icon displayed on the expand button dynamically switches from fa-expand to fa-compress, providing clear visual feedback of the widget's new expanded state.

        Minimization Process (else block):

            Original Position Retrieval: The function retrieves the previously stored data-original-parent-id and data-original-index values from the .widget-placeholder.

            DOM Re-insertion: The widget's DOM element is physically moved back from the #widget-expanded-overlay to its originalParent. insertBefore() is used if a sibling element at the original index still exists, ensuring it's placed precisely. If the target index is beyond the current last child (e.g., due to other widgets being removed or reordered in the interim), appendChild() acts as a fallback to ensure it's re-added to the correct container.

            Class Removal: The maximized, expanded-active, and active classes are removed from the relevant elements, reverting the styles back to the widget's normal appearance and hiding the modal overlay.

            Placeholder Deactivation: The .widget-placeholder is hidden again (style.display = 'none'), removing its visual presence from the grid.

            Icon Change: The icon reverts from fa-compress back to fa-expand.

        Overlay Click Handling (widgetManagementModalOverlay.addEventListener('click', ...)): A dedicated event listener on the widgetManagementModalOverlay (the darkened background behind the modal) detects clicks. If the click occurred directly on the overlay (not on the maximized widget itself), it triggers the minimization process, providing an intuitive way to close the modal.

    Client-Side (CSS):

        widget-expanded-overlay: Styles this fixed-position, full-screen div as the modal background. Its opacity and visibility are transitioned for smooth fade effects. It uses Flexbox (display: flex, justify-content: center, align-items: center) to perfectly center the contained maximized widget.

        widget.maximized: When this class is applied by JavaScript, it transforms the widget into its large, centered modal appearance. transform: scale(0.8) on initial display (and transform: scale(1) when active on the overlay) creates a subtle "pop-in" animation. Its high z-index (1000) ensures it overlays all other content. Hover effects are overridden (!important) to prevent visual glitches when maximized.

        body.expanded-active: This class, applied to the <body>, sets overflow: hidden to prevent the underlying dashboard content from scrolling while the modal is open.

        Content State Management (.compact-content, .expanded-content): This is a key part of the dual-state widget display.

            .widget .compact-content { display: flex; }: By default, the compact view content is always visible within any widget.

            .widget .expanded-content { display: none; }: By default, the expanded view content is always hidden.

            .widget.maximized .compact-content { display: none; }: When the parent .widget element has the maximized class (i.e., is expanded), the compact content is hidden.

            .widget.maximized .expanded-content { display: flex; flex: 1; overflow-y: auto; }: Simultaneously, when the widget is maximized, the expanded content is revealed. flex: 1 ensures it takes up all available vertical space, and overflow-y: auto enables internal scrolling for extensive content.

    User Experience: This approach delivers a fluid and intuitive modal experience. The expanded widget visually floats above the rest of the dashboard, which becomes temporarily dimmed and non-interactive. The ability to close the modal by clicking outside the widget enhances user comfort.

5.2. Widget Removal

Users have the capability to permanently remove unwanted widgets from their dashboard. This action is handled through a confirmation step to prevent accidental deletions.

    Trigger: Clicking the "Times" icon (fa-times) located in a widget's header. This action is primarily intended for widgets in their minimized (normal) state.

    Client-Side (JavaScript):

        Event Listener (mainContent.addEventListener('click', ...)): The delegated click listener on mainContent detects clicks on elements with the remove-widget class.

        Maximized State Check: The script first checks if the clicked widget is currently in the maximized (expanded) state. If it is, the "X" button functionality is repurposed to act as a "minimize" button, and toggleWidgetExpansion() is called to revert the widget to its normal size. This prevents accidentally removing a widget that is being actively viewed in detail.

        Disabled Check (removeButton.classList.contains('disabled')): Before proceeding, it verifies if the "Remove" button has the disabled CSS class. This class is applied by JavaScript when the "Show All Available Widgets" mode is active (as individual widget removal is disallowed in that mode). If disabled, an informational modal is shown, and the process stops.

        Confirmation Modal: If the widget is not maximized and not disabled, a showMessageModal() (the custom alert/confirmation modal) is displayed. This asks the user to confirm their intent to remove the widget, providing a crucial safeguard against accidental deletion.

        API Call: If the user confirms the removal within the modal, a fetch POST request is made to api.php.

        Payload Construction: The request body is a JSON string containing:

            action: 'remove_widget'

            widget_id: The ID of the widget to remove (obtained from removeButton.dataset.widgetId).

        Page Reload: On successful response from api.php, location.reload() is called to refresh the dashboard and reflect the removal.

    Server-Side (PHP api.php):

        Request Reception: api.php receives the POST request, decodes the JSON payload, and checks for action === 'remove_widget' and the widget_id.

        "Show All Widgets" Mode Enforcement: A server-side check confirms that the show_all_available_widgets setting is false. If this mode is active, the removal operation is gracefully skipped, and an error response is returned.

        active_widgets Array Manipulation: If the removal is permitted, PHP accesses the $_SESSION['active_widgets'] array. It iterates through the array to find the widget with the matching widget_id and removes it.

        $_SESSION['active_widgets'] = array_values($_SESSION['active_widgets']);: This is a crucial step. It re-indexes the array, creating a new array where all numeric keys are sequential from 0. This re-indexing is vital for consistent rendering by PHP's foreach loops and for correct future index lookups by JavaScript.

        Persistence Trigger: The $has_state_changed flag is set to true. This flag then ensures that the saveDashboardState() function is called.

        Data Saving: saveDashboardState() writes the now-modified $_SESSION['active_widgets'] (with the widget permanently removed) to dashboard_settings.json, ensuring the change persists across browser sessions.

        JSON Response: Returns a JSON response indicating success: true or success: false with an error message.

    User Experience: As location.reload() is triggered, the browser fetches the latest HTML from index.php. This new HTML is generated based on the updated dashboard_settings.json, resulting in the removed widget no longer being present on the dashboard.

5.3. Widget Addition (Drag-and-Drop & Button)

Widgets can be added to the dashboard using two distinct user interfaces: by dragging them from the sidebar's "Widget Library" or by selecting them from a dropdown in the settings panel and clicking an "Add" button.

    Trigger:

        Drag-and-Drop: Initiated by dragging a .widget-item element from the "Widget Library" section in the sidebar and releasing (dropping) it onto the #mainContent (the main content area).

        "Create New Widget Template" Button: Clicking this button within the Widget Management modal.

    Client-Side (JavaScript):

        Drag-and-Drop Event Listeners:

            dragstart on .draggable (sidebar widget items): Stores the widgetId in e.dataTransfer.

            dragover, dragleave on mainContent: Provide visual feedback for valid drop targets. e.preventDefault() is essential in dragover to allow for a drop.

            drop on mainContent: Retrieves widgetId from e.dataTransfer.

        "Show All Widgets" Mode Restriction: Before initiating any add operation (whether via drag-and-drop or the button), JavaScript performs a client-side check. It queries the state of the #showAvailableWidgets toggle. If "Show All Widgets" mode is active, it displays an informative modal (showMessageModal) to the user, explaining that adding widgets is disabled in this mode, and then prevents the action. This provides immediate feedback and prevents unnecessary server requests.

        API Call: For drag-and-drop, a fetch POST request is made to api.php with action: 'add_widget' and the widget_id.

        createNewWidgetTemplate(): This function handles the button click for creating a new widget file. It sends a fetch POST request to api.php with action: 'create_widget_template' and the widget_id.

        Page Reload: On successful response from api.php for adding or creating, location.reload() is called to refresh the dashboard and reflect the changes.

    Server-Side (PHP api.php):

        action === 'add_widget':

            Receives widget_id.

            Performs server-side check for show_all_available_widgets mode.

            Appends a new widget entry (with id, position, default width, height from config.php) to $_SESSION['active_widgets'].

            Calls saveDashboardState() to persist the change.

            Returns JSON success: true.

        action === 'create_widget_template':

            Receives widget_id.

            Generates a new PHP file in the widgets/ directory based on widgets/template.php.

            Returns JSON success: true or false with error.

    User Experience: Following the full page reload, the newly added widget will now appear in the dashboard's main content area, or the new template will be available in the "Available Widgets" sidebar.

5.4. "Show All Available Widgets" Mode

This powerful setting provides a quick way to toggle the dashboard's display behavior between a custom-curated selection of widgets and a comprehensive view that includes every available widget from the library. It acts as a global override for individual widget management.

    Trigger: Toggling the "Show All Available Widgets" checkbox located within the "Widget Behavior" group of the global settings panel.

    Client-Side (JavaScript):

        Event Listener (#showAvailableWidgets.addEventListener('change', ...)): A change event listener is attached to the checkbox. This listener ensures that the UI's interactive elements are immediately updated.

        UI Synchronization: The JavaScript dynamically applies or removes the disabled CSS class and the disabled HTML attribute to several interactive elements (e.g., "Create New Widget" button, "Deactivate" buttons on active widgets). This provides clear, immediate feedback to the user, indicating that adding or removing individual widgets (as well as adjusting individual dimensions) is not permitted while "Show All Widgets" mode is active.

        Form Submission: When the global settings form is submitted, the value of the showAvailableWidgets checkbox is sent as part of the settings payload to api.php via action: 'save_settings'.

    Server-Side (PHP api.php):

        Request Reception (action === 'save_settings'): When api.php receives a POST request with action='save_settings', it reads the show_available_widgets value from the JSON payload.

        Conditional active_widgets Generation (Critical Logic):

            Enabling "Show All": If the show_available_widgets setting is now true (and it was previously false):

                The $_SESSION['active_widgets'] array is completely overwritten.

                It is repopulated by iterating through the entire $available_widgets array (which contains metadata for all widgets registered via config.php).

                Each available widget is added to $_SESSION['active_widgets'] with its id, a new position, and its default width and height dimensions.

                The list is then sorted alphabetically by id using usort() to ensure a consistent display order. This action effectively "resets" the dashboard layout to include every possible widget.

            Disabling "Show All": If the show_available_widgets setting is now false (and it was previously true):

                The $_SESSION['active_widgets'] array is not explicitly overwritten in this block.

                Instead, because loadDashboardState() is called at the very beginning of every request, when "Show All" is turned off, the active_widgets list in $_SESSION will automatically revert to the last saved state from dashboard_settings.json (which would be the manually curated list that existed before "Show All" was enabled).

        Persistence: The updated show_available_widgets setting, along with the potentially modified (or reverted) $_SESSION['active_widgets'] list, is saved persistently to dashboard_settings.json via saveDashboardState().

    User Experience:

        When "Show All" is activated and the page reloads, the dashboard dynamically displays every available widget, overriding any previous custom layout. The "Add" and "Remove" controls become visually disabled.

        When "Show All" is deactivated, the dashboard reverts to displaying the manually curated selection of widgets (as stored in dashboard_settings.json), and the individual adding/removing/resizing controls become active again.

5.5. Individual Widget Dimension Adjustment

This feature allows users to customize the width and height (in grid units) of individual widgets on the dashboard, providing fine-grained control over their layout. These custom dimensions override the widget's default dimensions and are persistently saved.

    Trigger: Clicking the "Manage Widgets" button in the header, then editing the width/height inputs in the modal.

    Client-Side (JavaScript):

        Event Listener: The saveWidgetManagementBtn in the "Manage Widgets" modal triggers saveWidgetManagementChanges().

        Data Retrieval: The function iterates through each row in the activeWidgetsTableBody, extracting the widgetId, and the width and height values from the input fields.

        "Show All Widgets" Mode Check: A client-side check is performed to ensure that the "Show All Widgets" mode is not active before sending the update request.

        API Call: A fetch POST request is made to api.php with action: 'update_active_widgets' and an array of updatedWidgets (containing id, width, height, position).

        Page Reload: On successful response from api.php, location.reload() is called to refresh the dashboard and reflect the changes.

    Server-Side (PHP api.php):

        Request Reception (action === 'update_active_widgets'): api.php receives the POST request and checks for this action along with the active_widgets array.

        "Show All Widgets" Mode Enforcement: A server-side check ensures !$settings['show_all_available_widgets']. If "Show All" mode is active, the dimension update is skipped, and an error is logged.

        active_widgets Array Update: If the action is permitted, PHP iterates through the received active_widgets array and updates the corresponding entries in $_SESSION['active_widgets'].

        Persistence Trigger: The $has_state_changed flag is set to true, ensuring saveDashboardState() is called.

        Data Saving: saveDashboardState() writes the modified $_SESSION['active_widgets'] (now with the updated dimensions for those widgets) to dashboard_settings.json, making the dimension change permanent.

    User Experience: After the page reload, the dashboard is re-rendered using the updated dimensions from dashboard_settings.json. The affected widgets will now occupy their new custom sizes in the grid.

6. Debugging and Troubleshooting Guide

Effective debugging is paramount for maintaining and extending the CMS. This section outlines general debugging practices and common issues specific to this system.
6.1. General Debugging Practices

    PHP Error Reporting (Development Only):

        Enable: At the very top of index.php, ensure:

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        This will force all PHP errors, warnings, and notices to be displayed directly in your browser, providing immediate feedback.

        Production: Always disable or strictly control these in a production environment to prevent sensitive information from being exposed. Set display_errors = Off and error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED in your php.ini.

    Server Error Logs:

        For critical errors that might not display in the browser (especially in production or due to execution halting), or for errors related to file system permissions, consult your web server's error logs.

        Common Locations:

            Apache: /var/log/apache2/error.log (Linux) or logs/error.log in your Apache installation directory (Windows).

            Nginx: /var/log/nginx/error.log.

            PHP: Check your php.ini for the error_log directive, which specifies where PHP writes its errors.

        The CMS explicitly uses error_log() in index.php (e.g., in saveDashboardState()) to write detailed messages to these logs for persistence failures.

    Browser Developer Tools (F12): Your primary client-side debugging toolkit.

        Console Tab:

            JavaScript Errors: Look for red error messages indicating issues in dashboard.js. Click on the error to go to the specific line of code.

            console.log() Output: The dashboard.js file is instrumented with numerous console.log() statements (e.g., in showMessageModal and fetch calls). Monitor this tab to see the flow of JavaScript execution, values being sent in requests, and internal state. This is invaluable for tracking client-side behavior.

        Network Tab:

            Inspect Requests: After any user interaction that triggers a server request (e.g., clicking a button, dragging a widget, reloading the page), observe the HTTP requests. Filter for api.php or index.php.

            Headers: Examine the "Headers" sub-tab. Verify:

                The Request Method is POST when expected.

                Cache-Control headers are correctly set by index.php to no-store, no-cache, etc. If you're seeing cached content, these headers might not be applied correctly by the server or an upstream proxy.

            Payload: Check the "Payload" (or "Request Body") sub-tab. This shows the exact JSON data (action, widget_id, settings, active_widgets, etc.) that your JavaScript sent to the PHP backend. This is crucial for verifying that the client is sending the correct parameters PHP expects.

            Response: View the "Response" sub-tab. This displays the raw JSON (or HTML if index.php is accessed directly) that PHP sent back. Any PHP error messages or debugging output (var_dump, echo) that were not intended for the final UI will appear here. This helps confirm what PHP is processing and returning.

    Debug Info Stream Widget:

        Add the Debug Info Stream widget (widgets/debug_info.php) to your dashboard.

        This widget dynamically displays the current content of $_SESSION and the $_POST array from the last request directly on the dashboard.

        It updates on every page reload or form submission, providing real-time insight into the server's perception of your data. This is extremely powerful for diagnosing issues with session state or incorrect POST data.

6.2. Common Issues and Solutions

    "Fatal error: Cannot redeclare function ..."

        Cause: A PHP function (like a widget's render_ function) is being defined more than once. This happens because widget files are included both by config.php (to read metadata) and by helpers.php (to render content).

        Solution: Ensure all widget rendering functions are wrapped in an if (!function_exists('your_function_name')) { ... } block. (This has been implemented in widgets/template.php and existing widgets).

    Changes (e.g., widget removal, settings updates) don't persist after page refresh.

        Cause 1: File Permissions: The web server user does not have write permissions to dashboard_settings.json or its containing directory. file_put_contents() will fail.

        Solution 1: Check server error logs for "Permission denied" errors. Adjust file/directory permissions as described in Section 4.3. File Permissions: A Critical Note.

        Cause 2: JSON Encoding Failure: PHP failed to encode the data to JSON (less common now with JSON_PRETTY_PRINT and error_log checks).

        Solution 2: Check server error logs for "Failed to encode dashboard state to JSON" messages.

        Cause 3: Cache: The browser or an intermediary proxy is serving a cached version of index.php.

        Solution 3: Verify Cache-Control headers are correctly set in index.php. Perform a "hard refresh" in your browser (Ctrl+F5 or Cmd+Shift+R).

    Widgets are not arranging side-by-side (only one per row).

        Cause 1: Insufficient Grid Columns: The grid-template-columns definition in dashboard.css might not be allowing enough columns, or the minmax() values are too large.

        Solution 1: Ensure grid-template-columns on .main-content is using repeat(X, minmax(0, 1fr)) with an appropriate X (e.g., 8 for desktop) and that min-width: 0; is applied to .main-content itself.

        Cause 2: Widget Minimum Width: The .widget elements themselves have an implicit minimum width (due to content, padding, etc.) that prevents the grid columns from shrinking enough to allow multiple widgets per row.

        Solution 2: Crucially, ensure min-width: 0; is applied directly to the .widget CSS rule. This allows the browser to shrink the widget as needed for grid distribution.

        Cause 3: Combined Widget Width Exceeds Grid Capacity: If the sum of --width values for widgets in a single row exceeds the total columns available in the main-content grid (e.g., an IDE widget with --width: 6; and a Stats widget with --width: 4; will sum to 10, exceeding the 8-column desktop grid), they will naturally wrap.

        Solution 3: Adjust widget width settings in the "Manage Widgets" modal to ensure their combined size fits within the grid's column capacity for the given screen size. Test with small widgets (e.g., two 1.0 width widgets, which sum to 4 internal units) to isolate the grid behavior.

        Cause 4: CSS Specificity/Override: Another CSS rule, potentially inline or from a different stylesheet, is overriding the grid-template-columns or grid-column properties.

        Solution 4: Use browser developer tools (Elements tab, Styles panel) to inspect .main-content and .widget. Look for grid-template-columns, grid-column, and min-width properties. If they are struck through or have a warning icon, another rule is overriding them. Identify the conflicting rule and adjust its specificity or remove it.

    Modals (Settings, Widget Management, Confirmation) are not centered.

        Cause: Conflicting CSS, or the position: fixed context is not correctly established.

        Solution: Ensure html, body { height: 100%; } is present. Verify that .message-modal-overlay has position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;. Also, ensure .message-modal has margin: auto;. If issues persist, the use of !important flags on these properties in dashboard.css (position: fixed !important;, margin: auto !important; etc.) has been added as a strong diagnostic measure to force their application.

    Widgets are added/removed/resized, but they revert when "Show All Widgets" is toggled.

        Cause: This is intended behavior. "Show All Widgets" mode overrides individual widget management, forcing all widgets from config.php to display.

        Solution: Disable "Show All Widgets" mode to reactivate individual widget customization and have changes persist.

    Widget actions (e.g., remove, settings) don't work or trigger unexpected behavior when "Show All Widgets" is active.

        Cause: Client-side JavaScript explicitly disables these actions when show_all_available_widgets is enabled.

        Solution: This is by design. Disable "Show All Widgets" mode to re-enable these actions. The UI provides visual feedback (greyed out buttons).

    Widgets are not displaying content or appear blank.

        Cause 1: The render_widget() function in helpers.php might not be correctly including the widget file, or the widget's rendering function might not be echoing any HTML.

        Solution 1: Temporarily add error_log("Attempting to render widget: " . $widget_id); inside helpers.php's render_widget function. Also, ensure your widget's render_YOUR_WIDGET_ID_widget() function actually contains echo statements.

        Cause 2: CSS visibility issue.

        Solution 2: Use browser developer tools to inspect the widget's elements. Check if compact-content or expanded-content divs are set to display: none; unexpectedly.

    "Add New Widget" (button or drag-and-drop) is unresponsive.

        Cause: Often related to show_all_available_widgets being active, which disables these functions.

        Solution: Check the state of the "Show All Available Widgets" toggle in global settings. Inspect the browser console for JavaScript console.log messages indicating if the action was prevented.

7. Security Considerations

While this CMS is designed for simplicity and a lightweight footprint, it's important to acknowledge basic security considerations, especially if deployed in a production environment or exposed to the public internet.

    Input Sanitization (htmlspecialchars()):

        All user-supplied text (like dashboard_title) and any dynamic content generated by widgets ($printers_online, $city, etc.) that is echoed into the HTML should always be run through htmlspecialchars().

        Purpose: This prevents Cross-Site Scripting (XSS) attacks, where malicious scripts could be injected into the HTML if user input is rendered directly without escaping. The current implementation uses htmlspecialchars() where appropriate for text values.

    File Permissions:

        As highlighted in Section 4.3. File Permissions: A Critical Note, incorrect file permissions can be a security vulnerability. If the web server has excessive write permissions (e.g., 777) on sensitive directories or files, it could be exploited by an attacker to write malicious code.

        Best Practice: Always use the most restrictive permissions possible (664 for files, 775 for directories) that still allow the application to function.

    No User Authentication/Authorization:

        This CMS currently has no built-in user authentication or authorization. Anyone with access to the URL can modify settings and widget layouts.

        Implication: This system is best suited for local development environments, controlled internal networks, or as a component within a larger application that handles user authentication. It is not designed for public-facing deployments where unauthorized access could lead to data manipulation.

    Limited Input Validation:

        While some inputs are cast to integers ((int)) or floats ((float)), more extensive server-side validation for all POST parameters (e.g., widget_id exists in $available_widgets, new_width/new_height are within reasonable bounds, dashboard_title length limits) is not exhaustively implemented.

        Best Practice: For production, implement comprehensive server-side input validation to prevent invalid data from being saved and to mitigate potential vulnerabilities.

    Direct File Access:

        Widget PHP files are directly accessible if their URLs are known (e.g., yourdomain.com/widgets/stats.php). While they contain PHP logic that won't execute directly in the browser if accessed that way, their presence might be discoverable.

        Mitigation: For more robust systems, consider placing widget files outside the web root or using .htaccess rules (Apache) / Nginx configuration to restrict direct access to the widgets/ directory.

8. Future Enhancements and Roadmap

The current CMS provides a solid, flexible foundation. Here are potential future enhancements that could significantly extend its capabilities and user experience, along with considerations for their implementation.
8.1. Drag-and-Drop Reordering

This is a frequently requested feature that allows users to visually rearrange widgets on the dashboard.

    Current State: Widgets are currently rendered based on their position in the active_widgets array. Changes to order require manually removing and re-adding. The latest dashboard.js now includes basic drag-and-drop reordering functionality that saves positions to the server.

    Implementation Strategy:

        Client-Side (JavaScript):

            Drag & Drop Library/API: Leverages the HTML5 Drag and Drop API with custom logic.

            Visual Feedback: Implements a widget-placeholder to provide visual feedback during dragging.

            DOM Reordering: Updates the order of DOM elements within #mainContent during drag.

            State Reconstruction: After a successful reorder, constructs a new, reordered array of widget ids (and their associated width/height) based on the updated DOM order.

            Asynchronous Request (AJAX): Sends an AJAX POST request (using fetch API) to api.php with action: 'update_widget_positions' and the new ordered array.

        Server-Side (PHP api.php):

            New Action Type: Handles action: 'update_widget_positions'.

            Payload Processing: Receives the new ordered array of widget data.

            Update active_widgets: Completely replaces $_SESSION['active_widgets'] with this new, reordered data. The position property for each widget is re-calculated based on its new index in the array.

            Persistence: Calls saveDashboardState() to write this new active_widgets array to dashboard_settings.json.

            Response: Sends a minimal JSON response back to the client (echo json_encode(['success' => true]);).

    Benefits: Provides a highly intuitive and interactive way for users to personalize their dashboard layout with visual drag-and-drop actions.

8.2. Dashboard Layout Adjustments

Beyond simple widget reordering, offering options to dynamically change the grid layout of the main content area would further enhance customization.

    Current State: The main-content currently uses a responsive grid with a fixed maximum of 8 internal columns on desktop, adapting via media queries.

    Implementation Strategy:

        New Settings: Introduce new settings in dashboard_settings.json (and corresponding controls in the global settings panel) for defining the number of columns in the main content area (e.g., grid_columns_count: 3 or grid_min_widget_width: '250px').

        Client-Side (JavaScript/CSS):

            When these settings are updated, dashboard.js would need to dynamically modify the CSS styles of the #mainContent element. This could involve updating the grid-template-columns CSS property directly via element.style.gridTemplateColumns based on the user's selection.

            Alternatively, new CSS classes could be defined (e.g., .grid-2-columns, .grid-3-columns), and JavaScript would toggle these classes on the mainContent element.

            The dashboard.css file would need to include flexible rules to respond to these dynamic changes.

        Server-Side (PHP):

            Receive the new layout settings via the update_settings action.

            Save these new settings to dashboard_settings.json alongside other global settings.

    Benefits: Allows users greater control over the density and arrangement of their widgets, optimizing the dashboard for different screen sizes, resolutions, or personal viewing preferences. For instance, a user with a large monitor might prefer a 4-column layout, while a user on a smaller screen might opt for 1 or 2 columns.

9. Changelog

This document outlines significant changes and improvements made to the dashboard application.
Version 0.1.0 - Initial Setup (Implicit)

    Basic dashboard structure with header, sidebar, and main content area.

    Initial CSS styling for a glass theme.

    Core PHP logic for loading widgets and dashboard settings.

Version 0.2.0 - Widget Sizing and Persistence (Implicit)

    Implemented functionality to define and save widget dimensions (width and height) in dashboard_settings.json.

    Introduced "half-unit" sizing (e.g., 0.5, 1.5) for widgets, with PHP converting these to internal grid units for CSS.

    Added "Show All Available Widgets" toggle in settings.

    Introduced drag-and-drop for reordering existing widgets on the dashboard.

    Added a "Widget Management" modal for adjusting individual widget dimensions and deactivating widgets.

    Implemented a "Create New Widget Template" feature, allowing users to generate new PHP widget files.

    Added IDE widget for file editing.

Version 0.3.0 - June 30, 2025
Features & Improvements:

    Enhanced Dynamic Grid Layout:

        Implemented an explicit column-based CSS Grid for the main content area (.main-content).

        The grid now uses repeat(X, minmax(0, 1fr)) for its columns. This crucial change ensures that columns correctly distribute available space proportionally, even when content might otherwise try to force them wider, thereby resolving the persistent "single widget per row" issue.

        Media queries dynamically adjust the column count for different screen sizes:

            @media (min-width: 1200px): repeat(8, minmax(0, 1fr)) (allows for 4 full-width widgets or 8 half-width widgets in a row).

            @media (max-width: 1199px) and (min-width: 992px): repeat(6, minmax(0, 1fr)) (3 full units).

            @media (max-width: 991px) and (min-width: 768px): repeat(4, minmax(0, 1fr)) (2 full units).

            @media (max-width: 767px): repeat(2, minmax(0, 1fr)) (1 full unit on mobile).

        grid-auto-flow: dense; is maintained to encourage efficient filling of empty spaces.

    Robust Modal Centering:

        Strengthened CSS for .message-modal-overlay and .message-modal with !important flags on position, top, left, width, height, display, justify-content, align-items, and margin to ensure modals are forced to center, addressing previous off-screen rendering issues.

        Added html, body { height: 100%; } to ensure the viewport is correctly sized for fixed positioning.

    Improved Widget Deactivation:

        Refactored JavaScript event listener for "Deactivate" buttons within the Widget Management modal to use robust event delegation, ensuring dynamically added buttons are always clickable.

        Verified the fetch call and data payload for the deactivate_widget action in api.php.

Bug Fixes:

    Resolved Persistent Single-Column Layout Issue: The primary fix for widgets stacking one per row, allowing them to flow into multiple columns as intended, achieved by adding min-width: 0; to the .widget grid items. This prevents widget content from implicitly forcing columns wider than intended.

    Corrected Modal Positioning: Fixed the bug where modals were rendering partially off-screen by enforcing centering properties with !important.

    Restored Widget Deactivation Functionality: Fixed the issue preventing widgets from being deactivated via the "Manage Widgets" modal.

Technical Notes:

    Added min-width: 0; to both .main-content and, critically, to .widget to prevent implicit minimum content widths from breaking grid column distribution.

    Explicitly set box-sizing: border-box; for .main-content and .widget for consistent layout calculations.

    Regenerated index.php and dashboard.js to ensure consistency and incorporate all latest fixes.