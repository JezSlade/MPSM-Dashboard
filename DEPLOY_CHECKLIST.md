# Deployment Checklist for Live Server

## Issues to Fix:
1. HTTP 302 error on dashboard (OAuth token issue)
2. Database disconnected
3. Cache disabled
4. Auth not configured

## Files to Upload

### 1. Upload .env file to server root
Location: `/home/resolut7/public_html/mpsm.resolutionsbydesign.us/.env`

**IMPORTANT:** The .env file already exists locally with correct credentials.

### 2. Upload cms/config/database.php
Location: `/home/resolut7/public_html/mpsm.resolutionsbydesign.us/cms/config/database.php`

Credentials:
```
Database: resolut7_mpsm
Username: resolut7_mpsm_agent
Password: !C@S@lcd6McFceb8
```

### 3. Verify MySQL cache table exists
Run this SQL if table doesn't exist:

```sql
CREATE TABLE IF NOT EXISTS mpsm_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cache_key VARCHAR(255) NOT NULL UNIQUE,
    cache_value LONGTEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    hit_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cache_key (cache_key),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4. Create cms/data directory with write permissions
```bash
mkdir -p cms/data
chmod 755 cms/data
```

This directory stores:
- card-preferences.json
- users.json

### 5. Test OAuth authentication
The .env file contains:
```
CLIENT_ID="9AT9j4UoU2BgLEqmiYCz"
CLIENT_SECRET="9gTbAKBCZe1ftYQbLbq9"
USERNAME="dashboard"
PASSWORD="d@$hpa$$2024"
```

Test endpoint:
```
https://mpsm.resolutionsbydesign.us/mps-api/index.php?action=status
```

Should return engine status with auth mode.

## Testing After Deployment

1. **Test System Status**
   - Navigate to: https://mpsm.resolutionsbydesign.us/cms/api/system-status.php
   - Should show:
     - Engine: connected
     - Database: connected
     - Cache: enabled

2. **Test Login**
   - Navigate to: https://mpsm.resolutionsbydesign.us/cms/
   - Should redirect to login.html
   - Login with: admin/admin
   - Should redirect to dashboard

3. **Test Dashboard**
   - Should load customer dashboard
   - Cards should display data (not HTTP 302)
   - No console errors

4. **Test Engine Control**
   - Go to Admin > Engine Control
   - Should show "connected" status
   - Should show auth_mode: oauth_password

5. **Test Cache**
   - Go to Admin > Cache
   - Should show cache statistics
   - Should show hit rate, entries, etc.

6. **Test User Management**
   - Go to Admin > Users
   - Should show admin user
   - Try creating a new user
   - Try deleting a non-admin user

## Quick Fix Script

If OAuth is still failing, check:

1. .env file location
   ```bash
   ls -la /home/resolut7/public_html/mpsm.resolutionsbydesign.us/.env
   ```

2. File permissions
   ```bash
   chmod 644 .env
   chmod 644 cms/config/database.php
   ```

3. Check PHP error log
   ```bash
   tail -50 /home/resolut7/public_html/mpsm.resolutionsbydesign.us/mps-api/logs/php_errors_*.log
   ```

## Expected Results

✅ Dashboard loads without HTTP 302
✅ Engine Control shows "connected"
✅ Database shows "connected"
✅ Cache shows statistics
✅ Cards display data correctly
✅ User management works
✅ Login/logout works

## Default Login

**Username:** admin
**Password:** admin

**Change this password after first login!**

## Troubleshooting

### If Dashboard Shows HTTP 302:
- Check .env file exists and has OAuth credentials
- Check mps-api/logs/php_errors_*.log for OAuth errors
- Verify OAuth credentials are correct

### If Database Disconnected:
- Upload cms/config/database.php with correct credentials
- Verify MySQL user has permissions
- Check database name is correct (resolut7_mpsm)

### If Cache Disabled:
- Run the CREATE TABLE SQL above
- Verify mpsm_cache table exists
- Check database user has CREATE/INSERT permissions

### If Auth Not Configured:
- This is normal - lightweight auth is now implemented
- Should show "authenticated" after login
