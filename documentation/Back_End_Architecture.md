MPS Monitor API Integration Strategy
Objective

Integrate the MPS Monitor external API into the CMS without polluting the core CMS logic or file structure. The integration must be modular, scalable, and follow all existing conventions around separation of concerns, canonical PHP API structure, and frontend/backend communication.
Directory Structure

A new root-level folder named mps_monitor/ will encapsulate all integration logic:

/mps_monitor/
├── api/                   # All external-facing PHP API endpoints for MPS Monitor
│   ├── get_devices.php
│   ├── get_alerts.php
│   └── ...
├── src/                   # Core logic for handling MPS Monitor API authentication and requests
│   └── MPSMonitorClient.php
├── helpers/               # Utility functions for formatting and transforming API responses
│   └── MPSHelper.php
├── widgets/               # Optional: CMS-compatible widgets that display MPS data
│   └── mps_device_status.php
└── config/                # MPS Monitor-specific configuration values
    └── mps_config.php

Integration Points
1. API Access Layer (/mps_monitor/api/)

Each file is a self-contained API endpoint, following the project’s standard:

    Loads .env or mps_config.php

    Authenticates with the MPS Monitor API

    Sends request to a single external endpoint

    Returns clean JSON response

Example: /mps_monitor/api/get_devices.php

require_once __DIR__ . '/../config/mps_config.php';
require_once __DIR__ . '/../src/MPSMonitorClient.php';

$client = new MPSMonitorClient();
$response = $client->getDevices(); // internally handles token and headers

echo json_encode($response);

2. Client Logic (/mps_monitor/src/MPSMonitorClient.php)

A single reusable class that:

    Authenticates with the MPS Monitor API (via token caching or renewal)

    Sends requests to the correct endpoints with required headers

    Handles error states and retry logic (if needed)

    Normalizes data formats

class MPSMonitorClient {
    public function getDevices() { /* ... */ }
    public function getAlerts() { /* ... */ }
    // etc.
}

3. Helpers (/mps_monitor/helpers/MPSHelper.php)

Optional but encouraged. Use for:

    Converting raw MPS API responses to widget-friendly formats

    Mapping status codes to icons

    Timestamp formatting

    Data filtering or aggregation

4. Widgets (/mps_monitor/widgets/)

Widgets that display MPS Monitor data should follow existing CMS conventions:

    Use render_widget() in helpers.php

    Fetch data via JS from /mps_monitor/api/*.php

    Display data using existing dashboard styles

5. Configuration (/mps_monitor/config/mps_config.php)

Stores all integration-specific values, including:

define('MPS_API_BASE', 'https://api.mpsmonitor.com/');
define('MPS_API_CLIENT_ID', '...');
define('MPS_API_SECRET', '...');
define('MPS_TOKEN_CACHE_FILE', __DIR__ . '/../.token_cache.json');

This file is required at the top of all MPSMonitorClient and api/*.php files.
Core CMS Integration
Modify config.php:

define('MPS_MONITOR_PATH', __DIR__ . '/mps_monitor/');
require_once MPS_MONITOR_PATH . 'config/mps_config.php';

Modify helpers.php (if needed):

require_once MPS_MONITOR_PATH . 'helpers/MPSHelper.php';

Design Rationale

    Isolation: MPS Monitor code stays fully separate. Core CMS is untouched.

    Modularity: The MPS system can be versioned, replaced, or extended independently.

    Scalability: New API endpoints or widgets can be added without rewriting existing code.

    CMS-Native Widgets: By using your current widget system, MPS data fits seamlessly into the UI.

Next Steps

    Create MPSMonitorClient.php with token handling and basic request support.

    Build API bridge files under /mps_monitor/api/.

    Create one widget under /mps_monitor/widgets/ as a working prototype.

    Iterate.