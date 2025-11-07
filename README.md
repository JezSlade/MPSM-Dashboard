# MPSM Dashboard

**Version**: 3.0.0 (Refactored)
**Last Updated**: January 7, 2025
**Status**: Production Ready - Modern Architecture

---

## What This Is

A comprehensive dashboard for monitoring MPS (Managed Print Services) devices using the MPS Monitors API with a modern, scalable architecture.

**Live Site**: https://mpsm.resolutionsbydesign.us/cms/

**Key Features**:
- Real-time device monitoring across multiple customers
- 4-level role-based access control (Viewer, Analyst, Admin, Super Admin)
- Background job queue for long-running tasks
- Multi-driver caching system (Database, File, Redis)
- Modern ES6 frontend with component architecture
- REST API v1 with authentication and permissions
- Panel message webhook integration
- Professional admin UI with detailed metrics

---

## Quick Start

### Requirements

- PHP 7.4+ (8.0+ recommended)
- MySQL 5.7+
- Web server (Apache/Nginx/LiteSpeed)
- Optional: Redis (for high-performance caching)

### Installation

```bash
# 1. Clone repository
git clone https://github.com/JezSlade/MPSM-Dashboard.git
cd MPSM-Dashboard

# 2. Configure environment
cp config/app.php.example config/app.php
# Edit config/app.php with your credentials

# 3. Initialize database
# Tables auto-create on first access

# 4. Setup worker (optional, for background jobs)
# Add to cron: * * * * * cd /path/to/project && php worker.php

# 5. Access dashboard
# https://your-domain.com/cms/
```

### Default Login

- **Username**: admin
- **Password**: admin
- **Change immediately after first login**

---

## Architecture

### Modern Architecture (v3.0)

```
┌─────────────────────────────────────┐
│    Frontend (ES6 Modules)           │
│    - api-client.js                  │
│    - state-manager.js               │
│    - components/device-table.js     │
├─────────────────────────────────────┤
│    API Gateway (REST + Middleware)  │
│    - Routes with permissions        │
│    - Auth + CORS middleware         │
├─────────────────────────────────────┤
│    Controllers (Business Logic)     │
│    - DeviceController               │
│    - PanelMessageController         │
├─────────────────────────────────────┤
│    Repositories (Data Access)       │
│    - DeviceRepository               │
│    - PanelMessageRepository         │
│    - UserRepository                 │
├─────────────────────────────────────┤
│    Cache Layer (Multi-Driver)       │
│    - DatabaseCache                  │
│    - FileCache                      │
│    - RedisCache                     │
├─────────────────────────────────────┤
│    Database (MySQL)                 │
└─────────────────────────────────────┘

      Background Workers
   ┌──────────────────────┐
   │   Job Queue System   │
   │   - RefreshCacheJob  │
   │   - QueueManager     │
   │   - Worker CLI       │
   └──────────────────────┘
```

### Technology Stack

- **Backend**: PHP 7.4+ with dependency injection
- **Frontend**: Vanilla JavaScript (ES6 modules)
- **Database**: MySQL with PDO
- **Cache**: Swappable drivers (Database/File/Redis)
- **API**: RESTful JSON endpoints
- **Queue**: Database-backed job system
- **Auth**: Session-based with RBAC

---

## Project Structure

```
MPSM-Dashboard/
├── config/
│   └── app.php              # Configuration (single source of truth)
│
├── src/
│   ├── Auth/                # Access control system
│   │   ├── Permission.php   # Permission definitions
│   │   ├── Role.php         # 4-level role system
│   │   └── AccessControl.php # Permission checking
│   │
│   ├── Cache/               # Cache drivers
│   │   ├── DatabaseCache.php
│   │   ├── FileCache.php
│   │   └── RedisCache.php
│   │
│   ├── Controllers/         # API controllers
│   │   ├── BaseController.php
│   │   ├── DeviceController.php
│   │   └── PanelMessageController.php
│   │
│   ├── Contracts/           # Interfaces
│   │   ├── RepositoryInterface.php
│   │   ├── CacheInterface.php
│   │   └── EngineInterface.php
│   │
│   ├── Jobs/                # Background jobs
│   │   ├── Job.php
│   │   └── RefreshCacheJob.php
│   │
│   ├── Middleware/          # HTTP middleware
│   │   ├── AuthMiddleware.php
│   │   ├── CorsMiddleware.php
│   │   └── PermissionMiddleware.php
│   │
│   ├── Queue/               # Job queue system
│   │   ├── QueueManager.php
│   │   └── Worker.php
│   │
│   ├── Repositories/        # Data access layer
│   │   ├── BaseRepository.php
│   │   ├── DeviceRepository.php
│   │   ├── PanelMessageRepository.php
│   │   └── UserRepository.php
│   │
│   ├── ServiceContainer.php # Dependency injection
│   └── Router.php           # REST router
│
├── cms/
│   ├── api/
│   │   ├── v1/              # New REST API
│   │   │   ├── index.php
│   │   │   └── .htaccess
│   │   └── *.php            # Legacy APIs (backwards compatible)
│   │
│   ├── assets/
│   │   ├── js/              # ES6 modules
│   │   │   ├── api-client.js
│   │   │   ├── state-manager.js
│   │   │   ├── utils.js
│   │   │   ├── main.js
│   │   │   └── components/
│   │   │       └── device-table.js
│   │   ├── app.js           # Legacy (still works)
│   │   └── style.css
│   │
│   ├── index.php            # Dashboard
│   ├── login.php            # Login page
│   ├── functions.php        # Helper functions
│   └── config.php           # Legacy config (generated)
│
├── tests/
│   ├── validation-checklist.md  # 47-feature checklist
│   ├── test-examples.php         # Unit tests
│   ├── api-tests.sh              # API integration tests
│   └── README.md                 # Testing docs
│
├── bootstrap.php            # Application initialization
├── worker.php               # Background worker CLI
├── REFACTOR_STATUS.md       # Refactor progress
└── README.md                # This file
```

---

## Features

### All 47 Features Maintained

✅ **Dashboard (8 features)**
- Statistics display
- Device counts
- Activity feed
- Navigation
- Session management

✅ **Device Management (12 features)**
- Device list with search/filter/sort
- Pagination
- Device details
- CRUD operations (Admin+)
- Serial number linking
- Status indicators

✅ **Cache Management (6 features)**
- Background refresh (no timeouts!)
- Full/partial refresh
- Drilldown caching
- Statistics and health

✅ **Panel Messages (8 features)**
- Real-time monitoring
- Message history
- Webhook integration
- Alert codes
- Device lifecycle

✅ **API Endpoints (8 features)**
- Legacy APIs (backwards compatible)
- New REST API v1
- Webhook callbacks
- Authentication required
- Permission enforcement

✅ **Security (5 features)**
- 4-level RBAC
- Session management
- Password hashing (bcrypt)
- Permission middleware
- Unauthorized prevention

---

## Access Control

### 4-Level Role Hierarchy

**1. Viewer** (Read-only)
- View dashboard
- View devices and details
- Read-only access

**2. Analyst** (Viewer + Analysis)
- View panel messages
- View message history
- View reports
- Export data

**3. Admin** (Analyst + Management)
- Manage devices (CRUD)
- Refresh/clear cache
- Manage job queue
- View cache statistics

**4. Super Admin** (Full Access)
- All Admin permissions
- Manage users
- Manage roles
- System settings
- View logs
- Manage API keys

---

## API Documentation

### Legacy APIs (Backwards Compatible)

```bash
GET  /cms/api/get-devices.php
GET  /cms/api/get-device-deep-dive.php?serial=ABC123
GET  /cms/api/get-dashboard-stats.php
GET  /cms/api/refresh-cache-enhanced.php
POST /cms/api/login.php
```

### REST API v1

```bash
# Public
GET  /cms/api/v1/health

# Protected (requires authentication)
GET  /cms/api/v1/devices
GET  /cms/api/v1/devices/:serial
GET  /cms/api/v1/devices/:serial/drilldown
POST /cms/api/v1/devices/search
GET  /cms/api/v1/panel-messages
GET  /cms/api/v1/panel-messages/stats

# Webhook (no auth required)
POST /cms/api/v1/panel-messages
```

### Example Usage

```javascript
// Using API client module
import { api } from './api-client.js';

const devices = await api.get('/get-devices.php');
const stats = await api.get('/get-dashboard-stats.php');
```

---

## Background Jobs

### Job Queue System

```bash
# Run worker once (cron)
php worker.php

# Run as daemon
php worker.php --daemon

# Process specific queue
php worker.php --queue=cache

# View statistics
php worker.php --stats

# Clean up old jobs
php worker.php --cleanup

# Retry failed jobs
php worker.php --retry-failed
```

### Dispatching Jobs

```php
// Dispatch cache refresh job
$jobId = dispatchCacheRefresh(null, true); // Full refresh

// Dispatch for specific device
$jobId = dispatchCacheRefresh('ABC123');

// Check job status
$status = getJobStatus($jobId);
```

---

## Testing

### Run Automated Tests

```bash
# PHP unit tests
php tests/test-examples.php

# API integration tests
bash tests/api-tests.sh
```

### Manual Validation

Follow `tests/validation-checklist.md` for comprehensive feature validation.

---

## Development

### Making Changes

1. Read architecture documentation
2. Follow existing patterns
3. Test locally before deploying
4. Run automated tests
5. Update documentation
6. Commit with descriptive messages

### Adding New Features

```php
// 1. Add permission (if needed)
// src/Auth/Permission.php
const MY_FEATURE = 'my_feature';

// 2. Add to role
// src/Auth/Role.php
private static function getAdminPermissions(): array {
    return [
        // ... existing
        Permission::MY_FEATURE,
    ];
}

// 3. Create repository method
// src/Repositories/MyRepository.php
public function myMethod(): array {
    // Implementation
}

// 4. Create API endpoint
// src/Controllers/MyController.php
public function myAction(): void {
    PermissionMiddleware::check(Permission::MY_FEATURE);
    // Implementation
}

// 5. Register route
// cms/api/v1/index.php
$router->get('/api/v1/my-endpoint', function() {
    $controller = new MyController();
    $controller->myAction();
});
```

---

## Deployment

### Production Checklist

- [ ] Update config/app.php with production credentials
- [ ] Set `'debug' => false` in config
- [ ] Configure Redis (optional, for caching)
- [ ] Setup worker cron job
- [ ] Test all 47 features
- [ ] Verify role-based access
- [ ] Check error logs
- [ ] Performance benchmarking
- [ ] Security audit

### Monitoring

- Check `/cms/api/v1/health` for API status
- Monitor job queue: `php worker.php --stats`
- Review error logs regularly
- Track cache hit rates

---

## Troubleshooting

### Common Issues

**Can't Login**
- Check database credentials in config/app.php
- Verify session settings
- Check browser console for errors

**Jobs Not Processing**
- Ensure worker is running (cron or daemon)
- Check `php worker.php --stats`
- Review mpsm_jobs table

**Cache Not Working**
- Verify cache driver in config/app.php
- Check cache table exists (mpsm_cache)
- Review cache statistics

**Permission Denied**
- Check user role in database
- Verify permission definitions
- Review middleware logs

---

## Version History

### v3.0.0 (January 2025) - Modern Architecture Refactor

**Major Changes:**
- Complete architectural refactor
- 4-level RBAC system
- Background job queue
- Multi-driver caching
- ES6 frontend modules
- REST API v1
- Comprehensive test suite

**Benefits:**
- 100% backwards compatible
- All 47 features maintained
- No breaking changes
- Scalable architecture
- Easy to maintain
- Production-ready

### v2.0.0 (November 2024) - Simplified Architecture

- Removed broken caching
- Simplified to procedural code
- Enhanced admin UI
- Cross-browser compatibility

### v1.x (October 2024) - Initial Version

- First implementation
- Class-based architecture

---

## Documentation

- **[REFACTOR_STATUS.md](REFACTOR_STATUS.md)** - Refactor progress and architecture
- **[tests/README.md](tests/README.md)** - Testing guide
- **[tests/validation-checklist.md](tests/validation-checklist.md)** - Feature validation
- **[FEATURE_CATALOG_CURRENT_STATE.md](FEATURE_CATALOG_CURRENT_STATE.md)** - Complete feature list

---

## Credits

**Built with**: Claude (Sonnet 4.5)
**Architecture**: Modern PHP with ES6 frontend
**Refactor**: 9-session incremental approach
**For**: MPSM Dashboard Project

---

## License

Proprietary - Resolutions By Design

---

**Questions?** See documentation files or contact development team.
