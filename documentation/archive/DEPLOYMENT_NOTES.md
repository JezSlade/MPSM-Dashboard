# MPSM Dashboard - Deployment Notes

## MySQL Cache System Setup

### Prerequisites
- MySQL database created: `resolut7_mpsm`
- Database user created: `resolut7_mpsm_agent`
- Password: `!C@S@lcd6McFceb8`

### Files to Upload Manually (Not in Git)

The following file contains sensitive credentials and must be uploaded manually:

**File:** `cms/config/database.php`

**Location on Server:** `/path/to/cms/config/database.php`

**Contents:**
```php
<?php
return [
    'host' => 'localhost',
    'database' => 'resolut7_mpsm',
    'username' => 'resolut7_mpsm_agent',
    'password' => '!C@S@lcd6McFceb8',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => 'mpsm_',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

### Automatic Setup

The MySQL cache system will automatically:
1. Create the `mpsm_cache` table on first use
2. Create necessary indexes for performance
3. Set up proper character encoding (utf8mb4)

### Table Schema

The cache table will be created with:
- `id` - Auto-increment primary key
- `cache_key` - Unique MD5 hash of endpoint + params
- `cache_value` - JSON-encoded response data
- `expires_at` - Expiration timestamp
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp
- `hit_count` - Number of cache hits for analytics

### File Permissions

Ensure the following directories are writable:
- `cms/data/` - For card preferences (already created with .gitignore)
- `cms/config/` - For database config file (644 permissions recommended)

### Verification

After deploying, verify the setup:

1. **Check Database Connection:**
   - Visit: `/cms/` (Admin tab → Engine Control)
   - Should show "Connected" status under Database section

2. **Check Cache System:**
   - Visit: `/cms/` (Admin tab → Cache)
   - Should show MySQL cache statistics
   - No errors should appear

3. **Test Cache Operations:**
   - Click "Clear All Cache" button
   - Should see success message
   - Cache stats should reset

### Security Notes

- ✅ database.php is in .gitignore (never committed)
- ✅ database.php.example is in git (safe template)
- ✅ PDO prepared statements prevent SQL injection
- ✅ Separate user with limited privileges

### Troubleshooting

**If cache is not working:**

1. Check PHP error logs for database connection errors
2. Verify database credentials are correct
3. Ensure MySQL user has CREATE, SELECT, INSERT, UPDATE, DELETE permissions
4. Check that the database exists: `resolut7_mpsm`

**If table creation fails:**

Run this SQL manually:
```sql
CREATE TABLE IF NOT EXISTS mpsm_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cache_key VARCHAR(255) UNIQUE NOT NULL,
    cache_value LONGTEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    hit_count INT DEFAULT 0,
    INDEX idx_cache_key (cache_key),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## New Features Deployed

### 1. Customer Name Banner
- Prominent blue gradient banner with customer name
- Large, easy-to-read heading
- Building icon for visual clarity
- Responsive design

### 2. MySQL Cache System
- Persistent caching across server restarts
- Hit count tracking
- Automatic expiration cleanup
- RESTful API for cache management

### 3. Card Parameter Fixes
- All required parameters now set correctly
- FilterDealerId for printer list
- Date ranges for meter readings (last 30 days)
- Device parameters when available

### 4. Engine Control Center
- Real-time engine health monitoring
- Database status
- Cache statistics
- OAuth/auth status
- Human-readable display with icons

## API Endpoints

### Cache Management
- `GET /cms/api/cache-manager.php` - Get cache stats
- `GET /cms/api/cache-manager.php?action=entries` - List all entries
- `DELETE /cms/api/cache-manager.php` - Clear all cache
- `POST /cms/api/cache-manager.php` (action: clean) - Clean expired

## Performance

Expected improvements:
- Faster page loads (persistent cache)
- Reduced API calls (hit rate tracking)
- Better reliability (database-backed)
- Automatic cleanup (expired entries)

## Monitoring

Check these regularly:
- Cache hit rate (should be >70%)
- Cache size (monitor growth)
- Expired entries (should auto-clean)
- Database connection status
