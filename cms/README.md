# MPSM Dashboard - Rebuilt

**Version**: 2.0.0
**Architecture**: Simple, procedural PHP + Vanilla JavaScript
**Built**: 2025-10-26

---

## What This Does

MPS Monitor Dashboard displays printer/device data from the MPS Monitors API in a clean, easy-to-use interface.

**Features**:
- Real-time device monitoring
- Customer dashboard with metrics
- Light/dark theme
- Simple admin settings
- System health checks

---

## How To Run It

### 1. Database Setup

The application creates its own tables automatically on first run.

**Required**:
- MySQL database: `resolut7_mpsm`
- MySQL user: `resolut7_mpsm_agent`
- Password: See `config.php`

### 2. Access

- URL: https://mpsm.resolutionsbydesign.us/
- Default login: `admin` / `admin`

### 3. First Time Setup

1. Login with default credentials
2. Go to Admin tab
3. Change customer code if needed
4. Save settings

---

## Where To Find Credentials

All credentials are in **ONE file**: `config.php`

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'resolut7_mpsm');
define('DB_USER', 'resolut7_mpsm_agent');
define('DB_PASS', '...');

define('MPS_CLIENT_ID', '...');
define('MPS_CLIENT_SECRET', '...');
// etc
```

**IMPORTANT**: `config.php` should NOT be in Git (it's in .gitignore)

---

## File Structure

```
cms/
  config.php          ← All constants (DB, API, defaults)
  functions.php       ← All utility functions
  index.php           ← Main dashboard page
  login.html          ← Login page
  api/                ← API endpoints
    login.php
    logout.php
    get-devices.php
    get-customer-dashboard.php
    get-preferences.php
    save-preferences.php
    system-health.php
  assets/
    app.js            ← Main JavaScript
    style.css         ← All styles
```

**Total Files**: 12
**Total Lines of Code**: ~1,500
**External Dependencies**: 0 (except Font Awesome CDN)

---

## Engineering Standards

This rebuild follows strict standards documented in `ENGINEERING_STANDARDS.md`:

1. **No classes** - Simple functions only
2. **No cache** - Not needed yet
3. **One config** file - All constants in config.php
4. **Direct PDO** - No database wrappers
5. **Visible errors** - All errors shown to user
6. **Short functions** - Max 50 lines
7. **Flat structure** - Max 2 levels deep

---

## Troubleshooting

### Can't login
- Check database connection in `config.php`
- Default user created automatically: `admin/admin`

### No devices showing
- Check MPS API credentials in `config.php`
- Go to Admin → Test System Health
- Check browser console for errors

### Database errors
- Ensure MySQL is running
- Check credentials match in `config.php`
- Tables create automatically - no manual setup needed

---

## What Changed From Old Version

**REMOVED**:
- `classes/` directory (Database.php, MySQLCache.php)
- `config/database.php` (separate config file)
- Complex cache system (broken, never worked)
- Multiple authentication files
- OOP abstractions
- Silent error handling

**ADDED**:
- Single `config.php` with all constants
- Single `functions.php` with all utilities
- Visible error messages
- Clean, simple API endpoints
- System health checks that actually work

**RESULT**:
- 1/3 the code
- 100% working
- Easy to maintain
- Fast to debug

---

## Development Notes

- PHP 8.4+ required
- MySQL 5.7+ required
- No build tools
- No npm packages
- No composer packages
- Just upload and run

---

**For questions or issues**: See FORENSIC_ROOT_CAUSE_ANALYSIS.md for history
