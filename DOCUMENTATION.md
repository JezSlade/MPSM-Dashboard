# MPSM Dashboard - Complete Documentation

**Project:** MPS Monitor Dashboard
**Version:** 2.0.0
**Last Updated:** November 3, 2025
**Live Site:** https://mpsm.resolutionsbydesign.us/cms/
**Repository:** https://github.com/JezSlade/MPSM-Dashboard

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Recent Major Updates](#recent-major-updates)
3. [Architecture](#architecture)
4. [Features](#features)
5. [System Health Monitoring](#system-health-monitoring)
6. [Visitor Tracking](#visitor-tracking)
7. [Cache Engine](#cache-engine)
8. [Cross-Browser Compatibility](#cross-browser-compatibility)
9. [Deployment Guide](#deployment-guide)
10. [API Reference](#api-reference)
11. [Troubleshooting](#troubleshooting)

---

## Project Overview

The MPSM Dashboard is a comprehensive web application for monitoring and managing MPS (Managed Print Services) devices across multiple customers. It provides real-time device monitoring, health tracking, visitor analytics, and system administration tools.

### Key Technologies

- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Font Awesome 6.5.1
- **Backend:** PHP 8.1+, MySQL 8.0+
- **APIs:** MPS Monitor API (AB Asset Management)
- **Infrastructure:** Apache/Nginx, HTTPS, FTP deployment via GitHub Actions
- **Timezone:** America/New_York (Eastern Time) throughout

### Engineering Standards

The codebase follows strict engineering standards defined in `ENGINEERING_STANDARDS.md`:
- All times in UTC in database, Eastern for display
- Configuration uses constants (cms/config.php)
- One responsibility per file
- Functions must be short (<50 lines)
- Always show errors (development mode)
- Proper error handling with jsonError/jsonSuccess

---

## Recent Major Updates

### November 3, 2025 - Admin UI Enhancement (Commit: 07ee851)

**Enhanced System Health Dashboard:**
- Real-time timezone display showing Eastern Time in purple gradient header
- Detailed component cards for Database, MPS API, Cache Engine, Session
- Verification proofs with response times (e.g., "Query executed in 2.45ms")
- Server resource metrics: PHP version, memory, disk, load average, uptime
- Auto-refresh every 60 seconds when active
- Professional card layouts with gradients and hover effects

**Advanced Visitor Log Manager:**
- Statistics: Unique users, unique IPs, total visits, last visit time
- Filtering: Username, IP address, date range, page URL, results per page
- Pagination: Navigate through thousands of visitor logs
- Export: CSV export with up to 5,000 records
- Responsive design for mobile devices

**Files Modified:**
- cms/assets/app.js: +251 lines, -59 lines
- cms/assets/style.css: +435 lines

### November 3, 2025 - Cross-Browser Fixes (Commit: 8783a45)

**Fixed Firefox "Connection Error":**
- Added SameSite=Lax cookie attribute (required by Firefox)
- Added credentials: 'same-origin' in fetch requests
- Added CORS headers for preflight OPTIONS requests
- Enhanced error messages for better debugging

**Fixed Mobile Loading Issues:**
- Added Secure flag for HTTPS cookies
- Added proper HTTPS detection behind reverse proxy
- Session cookies now work on all mobile browsers

**Files Modified:**
- cms/config.php: +24 lines (session configuration)
- cms/functions.php: +44 lines (CORS and security headers)
- cms/login.html: +68 lines, -28 lines (enhanced error handling)

### November 3, 2025 - System Health Enhancement (Commit: ac9ed2c)

**Enhanced Backend APIs:**
- Rewrote getSystemHealth() with detailed verification data
- Added visitor log filtering and pagination
- All timestamps explicitly in Eastern Time with timezone labels
- Added server metrics (memory, disk, load, uptime)
- Cache engine statistics integration

**Files Modified:**
- cms/functions.php: +207 lines (enhanced getSystemHealth)
- cms/api/get-visitor-logs.php: +90 lines (filtering and pagination)

### October 2025 - Cache Engine Deployment

**Implemented File-Based Cache:**
- Cache storage in cms/api/cache/ directory
- Configurable TTL (Time To Live) per endpoint
- Automatic stale entry cleanup
- Cache statistics and monitoring
- Hit/miss tracking for performance analysis

### October 2025 - Search Fix & Device Monitoring

**Fixed Customer Search:**
- Queries all 82 customers individually (not paginated)
- Prevents missing devices from search results
- Comprehensive device discovery across entire customer base

**Device Health Monitoring:**
- Real-time device status tracking
- Alert color coding (green/yellow/red)
- Last communication timestamps
- Toner level monitoring

---

## Architecture

### Directory Structure

```
MPSM-Dashboard/
├── cms/                          # Main application directory
│   ├── api/                      # API endpoints
│   │   ├── cache/                # Cache storage directory
│   │   ├── get-customers.php     # Customer list
│   │   ├── get-devices.php       # Device list with pagination
│   │   ├── get-all-devices-all-customers.php  # All devices
│   │   ├── get-visitor-logs.php  # Visitor tracking with filters
│   │   ├── login.php             # Authentication
│   │   ├── logout.php            # Session termination
│   │   └── system-health.php     # System health monitoring
│   ├── assets/                   # Static assets
│   │   ├── js/
│   │   │   ├── card-registry.js  # Card configuration
│   │   │   ├── card-manager.js   # Card rendering
│   │   │   └── table-utils.js    # Table utilities
│   │   ├── app.js                # Main application logic (3000+ lines)
│   │   └── style.css             # Styles (1900+ lines)
│   ├── config.php                # Configuration constants
│   ├── functions.php             # Core utility functions
│   ├── index.php                 # Main dashboard
│   └── login.html                # Login page
├── .github/
│   └── workflows/
│       └── deploy.yml            # GitHub Actions FTP deployment
├── DOCUMENTATION.md              # This file
├── README.md                     # Project README
└── package.json                  # Node dependencies (if any)
```

### Database Schema

**Tables:**
- `mpsm_users` - User accounts
- `mpsm_visitor_log` - Visitor tracking with timestamps
- `mpsm_settings` - Application settings (future use)

**Key Columns (visitor_log):**
- `id` - Auto-increment primary key
- `user_id` - Foreign key to users table
- `username` - Username for quick reference
- `ip_address` - Visitor IP address
- `user_agent` - Browser user agent string
- `page_url` - Page visited
- `visited_at` - Timestamp (stored in UTC, displayed in Eastern)

### Session Management

**Configuration (cms/config.php lines 50-72):**
```php
$isHTTPS = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || $_SERVER['SERVER_PORT'] == 443
           || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_set_cookie_params([
    'lifetime' => SESSION_TIMEOUT,  // 3600 seconds (1 hour)
    'path' => '/',
    'domain' => '',  // Empty = current domain
    'secure' => $isHTTPS,  // Only send over HTTPS
    'httponly' => true,  // Prevent JavaScript access
    'samesite' => 'Lax'  // Cross-browser compatibility
]);

session_name('MPSM_SESSION');
```

**Session Cookie Attributes:**
- Name: `MPSM_SESSION`
- Lifetime: 1 hour (3600 seconds)
- Secure: Yes (HTTPS only)
- HttpOnly: Yes (prevents XSS attacks)
- SameSite: Lax (Firefox/Safari compatibility)

---

## Features

### 1. Device Dashboard

**Metric Cards:**
- Total Devices
- Online Devices
- Alert Devices
- Offline Devices
- Average Uptime
- Toner Status
- Recent Activity
- Customer Summary

**Device Table:**
- Sortable columns (device name, customer, status, toner levels)
- Search functionality
- Pagination (200 devices per page)
- Color-coded status indicators
- Click to view device details

**Device Details Modal:**
- Full device information
- Toner levels with visual bars
- Communication history
- Alert status
- Customer information

### 2. Admin Panel

**System Monitoring:**
- Enhanced health dashboard with real-time metrics
- Component verification (Database, MPS API, Cache, Session)
- Server resource monitoring (memory, disk, load, uptime)
- Auto-refresh every 60 seconds
- Timezone display (Eastern Time)

**Visitor Tracking:**
- Statistics cards (unique users, unique IPs, total visits)
- Advanced filtering (username, IP, date range, page URL)
- Pagination with configurable results per page
- CSV export (up to 5,000 records)
- Browser detection from user agent

**User Management:**
- Create new users
- List all users
- Delete users
- Password reset

**Dashboard Configuration:**
- Enable/disable metric cards
- Customize card order
- Save preferences per user

**Endpoint Catalog:**
- List of all API endpoints
- Category filtering
- Search functionality
- Endpoint documentation

### 3. Customer Management

**Customer Selection:**
- Dropdown list of all 82 customers
- Filters devices by selected customer
- Remembers last selection

**Customer Search:**
- Queries all customers individually
- Comprehensive device discovery
- No pagination issues

### 4. Cache Engine

**Cache Storage:**
- File-based cache in cms/api/cache/
- JSON format for cached responses
- Automatic expiration based on TTL
- Stale entry cleanup

**Cache Statistics:**
- Total storage size
- Fresh vs stale entries
- Oldest/newest entry timestamps
- Hit rate percentage

**Cached Endpoints:**
- get-customers.php (TTL: 1 hour)
- get-devices.php (TTL: 5 minutes)
- system-health.php (TTL: 1 minute)

---

## System Health Monitoring

### Health Components

**1. Database Health**
```json
{
  "connected": true,
  "verification": "Query executed successfully in 2.45ms",
  "response_time_ms": 2.45,
  "version": "MySQL 8.0.35",
  "table_count": 12,
  "visitor_log_entries": 1247,
  "error": null
}
```

**2. MPS API Health**
```json
{
  "connected": true,
  "verification": "API ping successful in 187ms",
  "response_time_ms": 187,
  "last_check": "2025-11-03T14:23:45-05:00",
  "error": null
}
```

**3. Cache Engine Health**
```json
{
  "storage": {
    "total_size_mb": 4.23,
    "total_entries": 50,
    "fresh_entries": 42,
    "stale_entries": 8,
    "oldest_entry": "2025-11-02T09:15:32-05:00",
    "newest_entry": "2025-11-03T14:23:40-05:00"
  },
  "hit_rate": 78.4
}
```

**4. Session Health**
```json
{
  "active": true,
  "user": "admin",
  "started": "2025-11-03T13:45:12-05:00"
}
```

**5. Server Metrics**
```json
{
  "php_version": "8.1.27",
  "memory_used_mb": 2.45,
  "memory_peak_mb": 3.12,
  "disk_free_gb": 45.2,
  "disk_total_gb": 59.0,
  "disk_used_percent": 23.5,
  "load_average": {
    "1min": 0.23,
    "5min": 0.18,
    "15min": 0.15
  },
  "uptime": "45 days, 12:34:56"
}
```

### Auto-Refresh

**Behavior:**
- Activates when entering System Monitoring tab
- Refreshes every 60 seconds (60,000ms)
- Stops when switching to different tab
- Prevents memory leaks with proper cleanup

**Implementation:**
```javascript
let healthRefreshInterval = null;

function switchAdminSection(sectionName) {
    // Clear existing interval
    if (healthRefreshInterval) {
        clearInterval(healthRefreshInterval);
        healthRefreshInterval = null;
    }

    if (sectionName === 'system') {
        testSystemHealth();
        loadVisitorLogs();

        // Setup auto-refresh
        healthRefreshInterval = setInterval(() => {
            testSystemHealth();
        }, 60000);
    }
}
```

---

## Visitor Tracking

### Data Collection

**Tracked Information:**
- User ID and username
- IP address
- User agent string
- Page URL
- Timestamp (Eastern Time)

**Collection Point:**
Every page load calls `trackVisit()` function:
```php
trackVisit('/current/page.php');
```

### Filtering API

**Endpoint:** `GET /api/get-visitor-logs.php`

**Query Parameters:**
- `username` - Filter by username (partial match)
- `ip_address` - Filter by IP address (exact match)
- `start_date` - Filter by start date (YYYY-MM-DD)
- `end_date` - Filter by end date (YYYY-MM-DD, inclusive)
- `page_url` - Filter by page URL (partial match)
- `limit` - Results per page (default: 50, max: 500)
- `offset` - Pagination offset (default: 0)

**Example Request:**
```
GET /api/get-visitor-logs.php?username=admin&start_date=2025-11-01&limit=100
```

**Response:**
```json
{
  "success": true,
  "count": 50,
  "total": 1247,
  "offset": 0,
  "limit": 100,
  "has_more": true,
  "timezone": "America/New_York (Eastern)",
  "stats": {
    "unique_users": 5,
    "unique_ips": 12,
    "total_visits": 1247,
    "last_visit": "2025-11-03 14:23:45"
  },
  "filters_applied": {
    "username": "admin",
    "start_date": "2025-11-01"
  },
  "logs": [
    {
      "id": 1247,
      "user_id": 1,
      "username": "admin",
      "ip_address": "192.168.1.100",
      "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/119.0.0.0",
      "page_url": "/cms/index.php",
      "visited_at": "2025-11-03 14:23:45",
      "formatted_time": "2025-11-03 14:23:45"
    }
  ]
}
```

### CSV Export

**Endpoint:** `GET /api/get-visitor-logs.php?export=csv`

**Features:**
- Exports up to 5,000 records
- Respects current filters
- CSV format with headers
- Downloads as attachment

**Example:**
```csv
Time,Username,IP Address,Page,User Agent
2025-11-03 14:23:45,admin,192.168.1.100,/cms/index.php,Mozilla/5.0...
```

---

## Cache Engine

### Configuration

**Cache Directory:** `cms/api/cache/`

**File Naming Convention:**
```
{endpoint_name}_{hash}.json
```

**Cache Entry Structure:**
```json
{
  "data": { /* cached response */ },
  "cached_at": 1730650425,
  "expires_at": 1730653425,
  "ttl": 3600
}
```

### Cache Functions

**Store Cache:**
```php
function cacheStore($key, $data, $ttl = 3600) {
    $cacheFile = CACHE_DIR . $key . '.json';
    $cacheData = [
        'data' => $data,
        'cached_at' => time(),
        'expires_at' => time() + $ttl,
        'ttl' => $ttl
    ];
    file_put_contents($cacheFile, json_encode($cacheData));
}
```

**Retrieve Cache:**
```php
function cacheGet($key) {
    $cacheFile = CACHE_DIR . $key . '.json';
    if (!file_exists($cacheFile)) {
        return null;
    }

    $cacheData = json_decode(file_get_contents($cacheFile), true);

    // Check expiration
    if (time() > $cacheData['expires_at']) {
        unlink($cacheFile);  // Delete stale cache
        return null;
    }

    return $cacheData['data'];
}
```

**Clear Cache:**
```php
function cacheClear($pattern = '*') {
    $files = glob(CACHE_DIR . $pattern . '.json');
    foreach ($files as $file) {
        unlink($file);
    }
}
```

### Cache Statistics

**Get Cache Stats:**
```php
function getCacheStats() {
    $files = glob(CACHE_DIR . '*.json');
    $totalSize = 0;
    $freshCount = 0;
    $staleCount = 0;
    $oldestTime = null;
    $newestTime = null;

    foreach ($files as $file) {
        $totalSize += filesize($file);
        $data = json_decode(file_get_contents($file), true);

        if (time() <= $data['expires_at']) {
            $freshCount++;
        } else {
            $staleCount++;
        }

        if (!$oldestTime || $data['cached_at'] < $oldestTime) {
            $oldestTime = $data['cached_at'];
        }
        if (!$newestTime || $data['cached_at'] > $newestTime) {
            $newestTime = $data['cached_at'];
        }
    }

    return [
        'total_size_mb' => round($totalSize / 1024 / 1024, 2),
        'total_entries' => count($files),
        'fresh_entries' => $freshCount,
        'stale_entries' => $staleCount,
        'oldest_entry' => $oldestTime,
        'newest_entry' => $newestTime
    ];
}
```

---

## Cross-Browser Compatibility

### Browser Support

| Browser | Desktop | Mobile | Status |
|---------|---------|--------|--------|
| Chrome | ✅ | ✅ | Fully supported |
| Firefox | ✅ | ✅ | Fixed in commit 8783a45 |
| Safari | ✅ | ✅ | Fully supported |
| Edge | ✅ | N/A | Fully supported |
| Samsung Internet | N/A | ✅ | Fixed in commit 8783a45 |
| iOS Safari | N/A | ✅ | Fixed in commit 8783a45 |

### Session Cookie Requirements

**Firefox:**
- Requires explicit SameSite attribute
- Solution: Added `samesite => 'Lax'` in session_set_cookie_params

**Mobile Browsers:**
- Require Secure flag on HTTPS
- Solution: Added HTTPS detection with reverse proxy support

**All Browsers:**
- Require credentials: 'same-origin' in fetch requests
- Solution: Added to all API calls in login.html

### CORS Configuration

**Security Headers (cms/functions.php):**
```php
function setSecurityHeaders() {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        $origin = $_SERVER['HTTP_ORIGIN'];
        $allowedOrigins = [
            'https://mpsm.resolutionsbydesign.us',
            'http://localhost',
            'http://127.0.0.1'
        ];

        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
    }

    // Security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');

    // Handle preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
```

---

## Deployment Guide

### Automatic Deployment (GitHub Actions)

**Workflow File:** `.github/workflows/deploy.yml`

**Trigger:** Push to `main` branch

**Process:**
1. Checkout repository
2. Upload files via FTP to production server
3. Exclude: config.php, cache/, .git/, node_modules/

**FTP Credentials (GitHub Secrets):**
- `FTP_SERVER`: ftp.resolutionsbydesign.us
- `FTP_USERNAME`: mpsm@mpsm.resolutionsbydesign.us
- `FTP_PASSWORD`: (stored in GitHub Secrets)

**Deployment Time:** ~2 minutes

### Manual Deployment

**Files to Upload:**
- cms/assets/app.js
- cms/assets/style.css
- cms/functions.php
- cms/index.php
- cms/login.html
- cms/api/*.php (all API endpoints)

**Files to Exclude:**
- cms/config.php (contains secrets, manually update on server)
- cms/api/cache/ (server-managed directory)

### Manual config.php Update

**Required when session configuration changes:**

1. SSH to server:
   ```bash
   ssh user@mpsm.resolutionsbydesign.us
   cd /path/to/cms
   ```

2. Edit config.php:
   ```bash
   nano config.php
   ```

3. Add session configuration after line 48 (see [Cross-Browser Fixes](#cross-browser-compatibility))

4. Verify syntax:
   ```bash
   php -l config.php
   ```

---

## API Reference

### Authentication Endpoints

#### POST /api/login.php

**Request:**
```json
{
  "username": "admin",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful"
}
```

**Error Response (401):**
```json
{
  "success": false,
  "error": "Invalid credentials"
}
```

#### POST /api/logout.php

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Device Endpoints

#### GET /api/get-customers.php

**Query Parameters:**
- `SortColumn` (required): "CustomerName" or other field
- `SortOrder` (optional): "asc" or "desc"

**Response:**
```json
{
  "success": true,
  "customers": [
    {
      "CustomerID": "abc123",
      "CustomerName": "Example Corp",
      "DeviceCount": 45
    }
  ]
}
```

#### GET /api/get-devices.php

**Query Parameters:**
- `CustomerID` (required): Customer ID
- `pageRows` (optional): Results per page (default: 200)
- `pageNumber` (optional): Page number (default: 1)

**Response:**
```json
{
  "success": true,
  "devices": [
    {
      "ExternalID": "device123",
      "DeviceName": "Printer-01",
      "Status": "Online",
      "TonerLevels": {
        "Black": 75,
        "Cyan": 60,
        "Magenta": 80,
        "Yellow": 70
      },
      "LastCommunication": "2025-11-03T14:20:00-05:00"
    }
  ]
}
```

### Monitoring Endpoints

#### GET /api/system-health.php

**Response:** See [System Health Monitoring](#system-health-monitoring)

#### GET /api/get-visitor-logs.php

**Response:** See [Visitor Tracking](#visitor-tracking)

### Admin Endpoints

#### GET /api/get-users.php

**Response:**
```json
{
  "success": true,
  "users": [
    {
      "id": 1,
      "username": "admin",
      "created_at": "2025-01-15 10:30:00"
    }
  ]
}
```

#### POST /api/create-user.php

**Request:**
```json
{
  "username": "newuser",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User created successfully"
}
```

---

## Troubleshooting

### Common Issues

#### Issue: "Connection Error" on Firefox Login

**Cause:** Missing SameSite cookie attribute

**Solution:** Update cms/config.php with session configuration (see commit 8783a45)

**Verification:**
1. Open Firefox DevTools (F12)
2. Go to Storage → Cookies
3. Check that MPSM_SESSION cookie has SameSite=Lax

#### Issue: Mobile Browser Won't Stay Logged In

**Cause:** Missing Secure flag on HTTPS

**Solution:** Session configuration includes $isHTTPS detection

**Verification:**
1. Check cookie attributes in mobile DevTools
2. Ensure Secure flag is set
3. Verify HTTPS is enabled on server

#### Issue: Visitor Log Timestamps "Way Off"

**Cause:** Timezone not explicitly set

**Solution:** All timestamps now include Eastern Time zone indicator

**Verification:**
1. Check visitor logs for "America/New_York (Eastern)" label
2. Verify timestamps match current Eastern Time

#### Issue: System Health Shows "Cache Data Unavailable"

**Cause:** Cache directory doesn't exist or isn't writable

**Solution:**
```bash
mkdir -p cms/api/cache
chmod 777 cms/api/cache
```

#### Issue: Auto-Refresh Not Working

**Cause:** Multiple intervals created or not cleared properly

**Solution:**
- Check browser console for errors
- Verify switchAdminSection clears existing intervals
- Ensure only one interval active at a time

### Debug Mode

**Enable in cms/config.php:**
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
```

**Check PHP Logs:**
```bash
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx
```

**Check Database Connectivity:**
```bash
php -r "
require 'cms/config.php';
require 'cms/functions.php';
try {
    \$pdo = getDatabase();
    echo 'Database connected successfully\n';
} catch (Exception \$e) {
    echo 'Database error: ' . \$e->getMessage() . '\n';
}
"
```

---

## Performance Optimization

### Cache Strategy

**High-Frequency Endpoints:**
- get-devices.php: 5-minute TTL
- get-customers.php: 1-hour TTL

**Low-Frequency Endpoints:**
- system-health.php: 1-minute TTL
- get-visitor-logs.php: No cache (always fresh)

### Database Indexes

**Recommended Indexes:**
```sql
-- Visitor log queries
CREATE INDEX idx_visitor_username ON mpsm_visitor_log(username);
CREATE INDEX idx_visitor_ip ON mpsm_visitor_log(ip_address);
CREATE INDEX idx_visitor_date ON mpsm_visitor_log(visited_at);

-- User queries
CREATE INDEX idx_user_username ON mpsm_users(username);
```

### Auto-Refresh Optimization

**Current Settings:**
- System Health: 60-second interval
- Network Impact: ~2-5KB per refresh
- CPU Impact: Negligible
- Memory Impact: No leaks (proper cleanup)

**Recommendations:**
- Keep 60-second interval for real-time monitoring
- Consider 120 seconds for low-activity periods
- Stop refresh when tab is hidden (future enhancement)

---

## Security

### Authentication

- Passwords hashed with PASSWORD_DEFAULT (bcrypt)
- Session-based authentication
- 1-hour session timeout
- HttpOnly cookies prevent XSS

### Input Validation

- All user inputs sanitized
- SQL queries use prepared statements
- JSON validation on API requests

### Headers

- X-Content-Type-Options: nosniff
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection: 1; mode=block
- Content-Security-Policy (future enhancement)

### Secrets Management

**Never commit to Git:**
- cms/config.php (database passwords, API keys)
- .env files
- FTP credentials (use GitHub Secrets)

---

## Future Enhancements

### High Priority

1. **Real-Time Updates:** WebSocket for instant device status changes
2. **Alert Notifications:** Email/SMS when devices go offline
3. **Historical Graphs:** Chart.js for trend visualization
4. **PDF Reports:** Export device health reports

### Medium Priority

1. **Two-Factor Authentication:** TOTP or SMS verification
2. **Role-Based Access:** Admin, User, Viewer roles
3. **Dark Mode:** Theme toggle in settings
4. **Geolocation:** Map showing visitor locations

### Low Priority

1. **Mobile App:** Native iOS/Android app
2. **API Rate Limiting:** Prevent abuse
3. **Automated Testing:** PHPUnit, Jest tests
4. **Docker Deployment:** Containerization

---

## Support & Contact

**Repository:** https://github.com/JezSlade/MPSM-Dashboard
**Issues:** https://github.com/JezSlade/MPSM-Dashboard/issues
**Live Site:** https://mpsm.resolutionsbydesign.us/cms/

---

**Last Updated:** November 3, 2025
**Version:** 2.0.0
**Documentation Maintained By:** Claude Code

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>
