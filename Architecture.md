## 🏗️ MPSM Architecture Overview

### 📁 Project Structure

A well-defined file and folder structure ensures that all project assets are organized logically, making it easy for any contributor to find what they're looking for and to understand where new files should go. **Adherence to this structure is mandatory.**

├── .env                  # Environment-specific variables (e.g., database credentials, API keys)
├── CHANGELOG.md          # Detailed log of all notable changes (human-readable)
├── CONTRIBUTING.md       # (This document) Guidelines for contributors
├── README.md             # Project overview, setup instructions, etc.
├── index.php             # The single entry point for the application (router)
│
├── includes/             # Core application logic, configuration, common functions, and UI components
│   ├── config.php        # Loads .env variables, defines global constants (e.g., APP_NAME, BASE_URL)
│   ├── constants.php     # Other general PHP constants (if any, separate from config.php)
│   ├── functions.php     # Global helper functions (e.g., sanitize_html, render_view, debug_log)
│   │
│   ├── header.php        # HTML for the top section of every page (doctype, head, opening body/wrapper, app header)
│   ├── navigation.php    # HTML for the main application navigation menu
│   ├── footer.php        # HTML for the bottom section of every page (closing body/wrapper, actual footer content)
│   │
│   └── (optional)        # Subdirectories for specific modules or libraries if the project grows
│       ├── db/           # Database connection and interaction logic (e.g., db_connect.php, query_builder.php)
│       └── auth/         # Authentication related functions/classes (e.g., auth_helpers.php)
│
├── views/                # HTML templates for different pages/sections (rendered by render_view())
│   ├── dashboard.php     # Content for the dashboard view
│   ├── reports.php       # Content for the reports view
│   └── (optional)        # Subdirectories for nested or complex views (e.g., views/user-management/profile.php)
│
├── public/               # Publicly accessible static assets (CSS, images, fonts)
│   ├── css/              # Stylesheets
│   │   └── styles.css    # Main application stylesheet
│   ├── img/              # Images
│   └── fonts/            # Web fonts
│
├── logs/                 # Directory for application log files (e.g., debug.log)
│   └── debug.log         # Main debug log file (managed by debug_log function)
│
└── (optional)
├── tests/            # Unit and integration tests (if testing framework is introduced)
└── .git/             # Git version control metadata (managed by Git)

### 🔒 Environment Configuration

`.env` must contain:

* CLIENT_ID
* CLIENT_SECRET
* USERNAME
* PASSWORD
* SCOPE
* TOKEN_URL
* BASE_URL
* DEALER_CODE
* DEALER_ID
* DEVICE_PAGE_SIZE

### 🔁 API Call Design Pattern

All frontend requests **must go through local PHP proxies** under `/api/`.

**✅ Example Flow:**

1.  PHP makes request to `/api/get_token.php`
2.  PHP loads `.env`, builds the token form request
3.  Sends to `TOKEN_URL` via curl
4.  Returns JSON `{ access_token, expires_in, ... }`

### 📐 PHP Proxy Standards

Each PHP file in `/api/` must:

* Load `.env` locally (not from globals)
* Validate required keys (`if (empty($env[...]))`)
* Require `Authorization: Bearer` headers if token needed
* Respond with only `application/json` data
* Use curl with JSON headers:

```php
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer {$token}",
  "Content-Type: application/json",
  "Accept: application/json"
]);

🛡️ Error Handling

    All errors (token failures, API errors) must return:

JSON

{ "error": "..." }

    No HTML error pages.
    No stack traces or raw PHP warnings.

📚 Extension Guidance

To add another API:

    Create /api/get_devices.php
    Copy get_customers.php as base
    Change Url and payload in $request
    Update frontend to call the new PHP endpoint

This ensures consistency and security across the stack.