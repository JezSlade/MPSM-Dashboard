<?php
/**
 * MPSM Dashboard - Global Utility Functions
 *
 * This file contains a collection of reusable functions used across the MPSM Dashboard.
 * These functions cover common tasks such as logging, file handling, sanitization,
 * and data retrieval, ensuring consistency and reducing code duplication.
 *
 * Debugging Philosophy:
 * Every function, especially those critical to application flow or data handling,
 * should include extensive internal debugging. This helps trace execution and
 * identify issues quickly.
 */

// --- Debugging and Logging Functions ---

/**
 * Logs a message to the debug panel and/or a log file based on configuration.
 *
 * @param string $message The message to log.
 * @param string $level The log level (e.g., 'INFO', 'WARNING', 'ERROR', 'DEBUG', 'SECURITY').
 * Only levels enabled in DEBUG_LOG_LEVELS will be logged.
 * @return void
 */
function debug_log($message, $level = 'INFO') {
    // Convert level to uppercase for consistent comparison.
    $level = strtoupper($level);

    // Check if the current log level is enabled in the configuration.
    if (!defined('DEBUG_LOG_LEVELS') || !isset(DEBUG_LOG_LEVELS[$level]) || !DEBUG_LOG_LEVELS[$level]) {
        // If the level is not defined or not enabled, do not log this message.
        return;
    }

    // Prepare the timestamp for the log entry.
    $timestamp = date('Y-m-d H:i:s');

    // Format the log entry.
    // Includes file and line number for detailed debugging, but only if DEBUG_MODE is true.
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = isset($backtrace[1]) ? $backtrace[1] : $backtrace[0]; // Get the actual caller, or current function if called directly
    $file = isset($caller['file']) ? basename($caller['file']) : 'unknown';
    $line = isset($caller['line']) ? $caller['line'] : 'unknown';

    if (DEBUG_MODE) {
        $log_entry = "[$timestamp] [$level] [$file:$line] - $message";
    } else {
        $log_entry = "[$timestamp] [$level] - $message";
    }

    // 1. Log to the debug panel (always if enabled and in debug mode).
    // This uses a global array to store messages which will be rendered in footer.php.
    if (defined('DEBUG_MODE') && DEBUG_MODE && defined('DEBUG_PANEL_ENABLED') && DEBUG_PANEL_ENABLED) {
        // Ensure the global debug messages array exists.
        if (!isset($GLOBALS['debug_messages'])) {
            $GLOBALS['debug_messages'] = [];
        }
        $GLOBALS['debug_messages'][] = $log_entry;
    }

    // 2. Log to file if DEBUG_LOG_TO_FILE is enabled.
    if (defined('DEBUG_LOG_TO_FILE') && DEBUG_LOG_TO_FILE && defined('DEBUG_LOG_FILE')) {
        $log_file = DEBUG_LOG_FILE;

        // Check if the log file size exceeds the limit.
        if (defined('MAX_DEBUG_LOG_SIZE_MB') && MAX_DEBUG_LOG_SIZE_MB > 0) {
            // Convert MB to bytes for comparison.
            $max_bytes = MAX_DEBUG_LOG_SIZE_MB * 1024 * 1024;
            if (file_exists($log_file) && filesize($log_file) > $max_bytes) {
                // Truncate the file to prevent it from growing indefinitely.
                // A more robust solution would be log rotation (e.g., rename old log, start new).
                if ($fp = fopen($log_file, 'w')) {
                    fwrite($fp, "[$timestamp] [INFO] - Log file truncated due to size limit (" . MAX_DEBUG_LOG_SIZE_MB . "MB).\n");
                    fclose($fp);
                    debug_log("Log file '$log_file' truncated.", 'INFO'); // Log this action within the same function
                } else {
                    // This error cannot be logged to the file, so we'll log it to the debug panel if possible.
                    if (defined('DEBUG_PANEL_ENABLED') && DEBUG_PANEL_ENABLED) {
                        if (!isset($GLOBALS['debug_messages'])) {
                            $GLOBALS['debug_messages'] = [];
                        }
                        $GLOBALS['debug_messages'][] = "[$timestamp] [ERROR] - Could not truncate log file: $log_file. Check permissions.";
                    }
                    // Fallback to PHP error log if debug panel not active
                    error_log("MPSM Dashboard Error: Could not truncate log file '$log_file'. Check permissions.", 0);
                }
            }
        }

        // Attempt to append the log entry to the file.
        // Use FILE_APPEND for appending, LOCK_EX for exclusive lock to prevent race conditions.
        if (file_put_contents($log_file, $log_entry . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            // If writing to file fails, log an internal error.
            // This error won't go to the file itself, but to the debug panel or PHP error log.
            $error_message = "Failed to write to log file: $log_file. Check permissions or disk space.";
            if (defined('DEBUG_PANEL_ENABLED') && DEBUG_PANEL_ENABLED) {
                if (!isset($GLOBALS['debug_messages'])) {
                    $GLOBALS['debug_messages'] = [];
                }
                $GLOBALS['debug_messages'][] = "[$timestamp] [ERROR] - " . $error_message;
            }
            // Fallback to PHP error log
            error_log("MPSM Dashboard Error: " . $error_message, 0);
        }
    }
}

/**
 * Includes a PHP partial file securely.
 * This function ensures that included files are within the application's base path,
 * preventing arbitrary file inclusion.
 *
 * @param string $path The relative path to the partial file from APP_BASE_PATH.
 * @param array $data An associative array of data to extract into the partial's scope.
 * @return bool True on successful inclusion, false otherwise.
 */
function include_partial($path, $data = []) {
    // Construct the full absolute path.
    $full_path = APP_BASE_PATH . ltrim($path, '/\\');

    // Basic security check: Ensure the path is within the allowed application base path.
    // realpath() resolves symlinks and '..' segments, making the check more robust.
    if (strpos(realpath($full_path), realpath(APP_BASE_PATH)) === 0 && file_exists($full_path)) {
        debug_log("Attempting to include partial: $full_path", 'DEBUG');

        // If data is provided, extract it into the current symbol table,
        // making variables available within the included partial.
        if (!empty($data) && is_array($data)) {
            extract($data);
        }

        // Include the file.
        require_once $full_path; // Use require_once to prevent multiple inclusions of the same file.
        debug_log("Successfully included partial: $full_path", 'DEBUG');
        return true;
    } else {
        debug_log("Failed to include partial: '$path'. File not found or outside allowed path: $full_path", 'ERROR');
        if (DEBUG_MODE) {
            trigger_error("Failed to include partial: '$path'. File not found or outside allowed path.", E_USER_WARNING);
        }
        return false;
    }
}

// --- Data Sanitization and Validation Functions ---

/**
 * Sanitizes a string for safe display in HTML.
 * Converts special characters to HTML entities to prevent XSS attacks.
 *
 * @param string $input The string to sanitize.
 * @return string The sanitized string.
 */
function sanitize_html($input) {
    debug_log("Sanitizing HTML input.", 'DEBUG');
    // ENT_QUOTES: Converts both double and single quotes.
    // 'UTF-8': Specifies the character encoding.
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitizes a string for use in a URL.
 * Encodes spaces and other special characters.
 *
 * @param string $input The string to sanitize.
 * @return string The URL-encoded string.
 */
function sanitize_url($input) {
    debug_log("Sanitizing URL input.", 'DEBUG');
    return urlencode($input);
}

/**
 * Validates and sanitizes a numeric input to ensure it's an integer.
 *
 * @param mixed $input The input to validate and sanitize.
 * @return int|null The sanitized integer, or null if validation fails.
 */
function sanitize_int($input) {
    debug_log("Sanitizing integer input: " . var_export($input, true), 'DEBUG');
    // Use filter_var with FILTER_SANITIZE_NUMBER_INT to remove all characters except digits, plus and minus signs.
    $sanitized = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    // Then, use FILTER_VALIDATE_INT to ensure it's a valid integer.
    $validated = filter_var($sanitized, FILTER_VALIDATE_INT);
    if ($validated === false) {
        debug_log("Integer sanitization failed for input: " . var_export($input, true), 'WARNING');
        return null;
    }
    return (int)$validated;
}

// --- Data Retrieval and Processing Functions ---

/**
 * Retrieves a GET parameter safely.
 *
 * @param string $param_name The name of the GET parameter.
 * @param mixed $default The default value to return if the parameter is not set.
 * @param string $type The expected type ('string', 'int', 'bool').
 * @return mixed The sanitized GET parameter value, or the default.
 */
function get_get_param($param_name, $default = null, $type = 'string') {
    debug_log("Retrieving GET parameter: '$param_name' with type '$type'.", 'DEBUG');
    if (isset($_GET[$param_name])) {
        $value = $_GET[$param_name];
        switch ($type) {
            case 'int':
                return sanitize_int($value);
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            case 'string':
            default:
                return sanitize_html($value); // Sanitize strings for HTML display by default
        }
    }
    debug_log("GET parameter '$param_name' not found, returning default value: " . var_export($default, true), 'DEBUG');
    return $default;
}

/**
 * Retrieves a POST parameter safely.
 *
 * @param string $param_name The name of the POST parameter.
 * @param mixed $default The default value to return if the parameter is not set.
 * @param string $type The expected type ('string', 'int', 'bool').
 * @return mixed The sanitized POST parameter value, or the default.
 */
function get_post_param($param_name, $default = null, $type = 'string') {
    debug_log("Retrieving POST parameter: '$param_name' with type '$type'.", 'DEBUG');
    if (isset($_POST[$param_name])) {
        $value = $_POST[$param_name];
        switch ($type) {
            case 'int':
                return sanitize_int($value);
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            case 'string':
            default:
                return sanitize_html($value); // Sanitize strings for HTML display by default
        }
    }
    debug_log("POST parameter '$param_name' not found, returning default value: " . var_export($default, true), 'DEBUG');
    return $default;
}

/**
 * Discovers and returns a list of available views from the 'views' directory.
 * Each view file is expected to be a PHP file.
 *
 * @return array An associative array where keys are view slugs and values are display names.
 * Example: ['service' => 'Service View', 'printers' => 'Printer Management']
 */
function get_available_views() {
    debug_log("Discovering available views from: " . VIEWS_PATH, 'INFO');
    $views = [];
    if (is_dir(VIEWS_PATH)) {
        $files = scandir(VIEWS_PATH);
        foreach ($files as $file) {
            // Ensure it's a PHP file and not '.' or '..'.
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && $file !== '.' && $file !== '..') {
                $view_slug = basename($file, '.php');
                // Basic way to create a display name: replace underscores with spaces and capitalize.
                $view_display_name = ucwords(str_replace('_', ' ', $view_slug));
                $views[$view_slug] = $view_display_name;
                debug_log("Found view: '$view_slug' with display name '$view_display_name'.", 'DEBUG');
            }
        }
    } else {
        debug_log("Views directory not found: " . VIEWS_PATH, 'ERROR');
        if (DEBUG_MODE) {
            trigger_error("Views directory not found: " . VIEWS_PATH, E_USER_WARNING);
        }
    }
    // Sort views alphabetically for consistent display.
    ksort($views);
    debug_log("Discovered " . count($views) . " views.", 'INFO');
    return $views;
}

/**
 * Discovers and returns a list of available cards from the 'cards' directory.
 * Each card file is expected to be a PHP file following a specific template.
 *
 * @return array An associative array where keys are card slugs and values are display names.
 * Example: ['printer_status' => 'Printer Status', 'toner_levels' => 'Toner Levels']
 */
function get_available_cards() {
    debug_log("Discovering available cards from: " . CARDS_PATH, 'INFO');
    $cards = [];
    if (is_dir(CARDS_PATH)) {
        $files = scandir(CARDS_PATH);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && $file !== '.' && $file !== '..') {
                $card_slug = basename($file, '.php');
                // Basic way to create a display name: replace underscores with spaces and capitalize.
                $card_display_name = ucwords(str_replace('_', ' ', $card_slug));
                $cards[$card_slug] = $card_display_name;
                debug_log("Found card: '$card_slug' with display name '$card_display_name'.", 'DEBUG');
            }
        }
    } else {
        debug_log("Cards directory not found: " . CARDS_PATH, 'ERROR');
        if (DEBUG_MODE) {
            trigger_error("Cards directory not found: " . CARDS_PATH, E_USER_WARNING);
        }
    }
    // Sort cards alphabetically for consistent display.
    ksort($cards);
    debug_log("Discovered " . count($cards) . " cards.", 'INFO');
    return $cards;
}

/**
 * Renders a specific card.
 *
 * @param string $card_slug The slug of the card to render (e.g., 'printer_status').
 * @param array $data An associative array of data to pass to the card.
 * @return bool True if the card was rendered, false otherwise.
 */
function render_card($card_slug, $data = []) {
    $card_path = CARDS_PATH . sanitize_url($card_slug) . '.php'; // Use sanitize_url to prevent path traversal
    debug_log("Attempting to render card: $card_slug from $card_path", 'INFO');

    // Ensure the card path is within the allowed cards directory and the file exists.
    if (strpos(realpath($card_path), realpath(CARDS_PATH)) === 0 && file_exists($card_path)) {
        // Start output buffering to capture the card's HTML output.
        // This allows us to potentially process or wrap the card content.
        ob_start();
        try {
            // Extract data into the card's scope.
            if (!empty($data) && is_array($data)) {
                extract($data);
            }
            include $card_path; // Use include (not require) in case a card is optional or fails.
            $card_content = ob_get_clean(); // Get the buffered content and clean the buffer.
            echo $card_content; // Output the card's content.
            debug_log("Successfully rendered card: $card_slug", 'INFO');
            return true;
        } catch (Throwable $e) {
            // Catch any exceptions during card rendering.
            $error_message = "Error rendering card '$card_slug': " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
            debug_log($error_message, 'ERROR');
            if (DEBUG_MODE) {
                echo "<div class='card error-card'><h3>Error Loading Card: " . sanitize_html($card_slug) . "</h3><p>" . sanitize_html($error_message) . "</p></div>";
            }
            ob_end_clean(); // Ensure output buffer is cleaned even on error.
            return false;
        }
    } else {
        debug_log("Card file not found or invalid path for '$card_slug': $card_path", 'WARNING');
        if (DEBUG_MODE) {
            echo "<div class='card error-card'><h3>Card Not Found: " . sanitize_html($card_slug) . "</h3><p>The requested card file could not be loaded or does not exist at: " . sanitize_html($card_path) . "</p></div>";
        }
        return false;
    }
}

/**
 * Placeholder function for fetching Database Status.
 * In a real application, this would connect to the database and check its health.
 *
 * @return array An associative array with 'status' (string: 'ok', 'error', 'unknown') and 'message' (string).
 */
function get_db_status() {
    debug_log("Checking database status (placeholder).", 'INFO');
    // Simulate database connection check
    $status = ['status' => 'ok', 'message' => 'Database connection successful.'];
    /*
    // Example of actual database check:
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // You might run a simple query like SELECT 1 to verify connection
        $stmt = $pdo->query("SELECT 1");
        if ($stmt) {
            $status = ['status' => 'ok', 'message' => 'Database connection successful.'];
        } else {
            $status = ['status' => 'error', 'message' => 'Failed to query database.'];
        }
    } catch (PDOException $e) {
        $status = ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
    }
    */
    debug_log("Database status: " . $status['status'] . " - " . $status['message'], ($status['status'] == 'ok' ? 'INFO' : 'ERROR'));
    return $status;
}

/**
 * Placeholder function for fetching API Status (e.g., MPS Monitor API).
 * In a real application, this would make a small, non-destructive call to the API.
 *
 * @return array An associative array with 'status' (string: 'ok', 'error', 'unknown') and 'message' (string).
 */
function get_api_status() {
    debug_log("Checking API status (placeholder).", 'INFO');
    // Simulate API call check
    $status = ['status' => 'ok', 'message' => 'MPS Monitor API reachable.'];
    /*
    // Example of actual API check (using cURL for demonstration):
    $ch = curl_init(MPSM_API_BASE_URL . 'status'); // Assuming a status endpoint
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5-second timeout
    // Add authentication headers if necessary for a real check
    // curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . MPSM_API_KEY]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code >= 200 && $http_code < 300) {
        $status = ['status' => 'ok', 'message' => 'MPS Monitor API reachable. HTTP ' . $http_code];
    } else {
        $error_message = "API unreachable. HTTP " . $http_code;
        if ($curl_error) {
            $error_message .= ". cURL Error: " . $curl_error;
        }
        $status = ['status' => 'error', 'message' => $error_message];
    }
    */
    debug_log("API status: " . $status['status'] . " - " . $status['message'], ($status['status'] == 'ok' ? 'INFO' : 'ERROR'));
    return $status;
}

/**
 * Placeholder function for fetching customer data.
 * In a real application, this would query your database or MPS Monitor API for customer list.
 *
 * @return array An array of associative arrays, each representing a customer.
 * Example: [['id' => 1, 'name' => 'Customer A'], ['id' => 2, 'name' => 'Customer B']]
 */
function get_customers() {
    debug_log("Fetching customer data (placeholder).", 'INFO');
    // Simulate fetching customer data
    $customers = [
        ['id' => 101, 'name' => 'Acme Corp'],
        ['id' => 102, 'name' => 'Globex Inc.'],
        ['id' => 103, 'name' => 'Soylent Corp'],
        ['id' => 104, 'name' => 'Omni Consumer Products'],
        ['id' => 105, 'name' => 'Umbrella Corporation'],
        ['id' => 106, 'name' => 'Stark Industries'],
        ['id' => 107, 'name' => 'Wayne Enterprises'],
        ['id' => 108, 'name' => 'Cyberdyne Systems'],
        ['id' => 109, 'name' => 'Weyland-Yutani Corp'],
        ['id' => 110, 'name' => 'Tyrell Corporation'],
    ];
    debug_log("Fetched " . count($customers) . " placeholder customers.", 'INFO');
    return $customers;
}

// End of functions.php