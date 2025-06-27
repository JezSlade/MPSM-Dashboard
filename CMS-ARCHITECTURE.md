MPS Widget CMS Architecture Documentation
========================================

Table of Contents
-----------------
1. Core System Overview
2. Database Layer
3. Error Handling System
4. Widget Architecture
5. API Integration
6. Dashboard Rendering
7. Security Model
8. Performance Considerations


1. Core System Overview
-----------------------

Entry Point (index.php):
The main entry point implements a front controller pattern that:

1. Initializes the environment:
   - Loads configuration via config.php
   - Sets up error handling via ErrorHandler::initialize()
   - Establishes database connection

2. Routes requests:
   if (str_starts_with($_SERVER['REQUEST_URI'], '/api')) {
       require 'api/index.php';  // API endpoints
   } else {
       require 'dashboard/index.php';  // Admin interface
   }

3. Handles failures:
   - Catches all unhandled exceptions
   - Converts to JSON for API, HTML for UI
   - Logs detailed error information


2. Database Layer
----------------

SQLite Implementation (lib/Database.php):

Connection Management:
$this->pdo = new PDO('sqlite:' . DB_FILE, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_PERSISTENT => false
]);

- Uses WAL journal mode for better concurrency
- Enforces foreign key constraints
- Disables persistent connections for stability

Transaction Handling:
Three-tier transaction system:
1. beginTransaction(): Starts transaction, sets flag
2. commit()/rollBack(): Cleans up transaction state
3. Automatic rollback on query failure

Parameter Binding:
Type-aware binding system:
$type = is_int($value) ? PDO::PARAM_INT : 
       (is_bool($value) ? PDO::PARAM_BOOL : 
       (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR);
$stmt->bindValue($param, $value, $type);


3. Error Handling System
------------------------

Multi-layer Error Management:
1. Error Conversion:
   - Converts all errors to ErrorException
   - Maintains original error context

2. Log Rotation:
   if (filesize(self::$logFile) >= self::$maxLogSize) {
       rename(self::$logFile, self::$logFile . '.' . date('YmdHis'));
   }

3. Context Capture:
   - Records stack traces
   - Preserves error chain for exceptions
   - Includes request details in web context


4. Widget Architecture
----------------------

Widget Lifecycle:
1. Registration:
   - Stored in widgets table
   - Contains type, position, and configuration

2. Rendering:
   // dashboard/index.php
   foreach ($widgets as $widget) {
       include "../widgets/{$widget['type']}.php";
   }

3. Client-Side Interaction:
   - Uses MutationObserver for dynamic updates
   - Event delegation for widget controls

Widget Types:
1. Static Widgets:
   - Pre-rendered HTML
   - Example: System Console

2. Dynamic Widgets:
   - API-driven content
   - Example: Device List

3. Interactive Widgets:
   - User-configurable
   - Example: Code Editor


5. API Integration
------------------

Client Implementation (lib/ApiClient.php):
public function getDevices(): array {
    $token = $this->getToken();
    return $this->makeRequest('GET', '/Device/GetDevices', [
        'headers' => ['Authorization: Bearer ' . $token]
    ]);
}

Token Management:
- Caches tokens in session
- Automatic refresh before expiration
- Retry logic for failed requests


6. Dashboard Rendering
----------------------

Grid System:
1. Server-Side:
   - PHP calculates initial positions
   - Outputs data attributes for grid

2. Client-Side:
   new GridStack({
       float: true,
       removable: true,
       acceptWidgets: true
   });

Theme Implementation:
CSS Variables for theming:
:root {
    --glass-bg: rgba(30, 30, 45, 0.7);
    --neon-accent: #0af;
    --text-primary: #e0f7fa;
}


7. Security Model
-----------------

Key Protections:
1. Input Handling:
   - Always use prepared statements
   - Type casting for all inputs

2. Output Encoding:
   htmlspecialchars($output, ENT_QUOTES, 'UTF-8');

3. CSRF Protection:
   - Session-based tokens
   - Same-origin policy enforcement


8. Performance Considerations
----------------------------

Caching Strategies:
1. Widget-Level Caching:
   - Cache API responses per-widget
   - TTL-based invalidation

2. Database Optimization:
   - WAL mode for SQLite
   - Indexed frequently-queried fields

3. Frontend Performance:
   - Debounced resize events
   - Virtual scrolling for long lists