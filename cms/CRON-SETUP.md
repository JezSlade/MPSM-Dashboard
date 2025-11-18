# CRON Router Setup Guide

## Overview

The CRON router provides centralized management of all scheduled tasks without needing to modify cPanel for every change.

**ONE CRON JOB** in cPanel runs the router every minute, which then handles all tasks based on their configured schedules.

---

## Setup Instructions

### Step 1: Add ONE CRON Job in cPanel

1. Log into cPanel
2. Go to **Advanced → CRON Jobs**
3. Add this single job:

```bash
* * * * * /usr/bin/php /home/resolut7/public_html/cms/cron-router.php
```

> **Important:** Make sure the filesystem path matches the actual document root (example: `.../public_html/mpsm.resolutionsbydesign.us/...`) and avoid placeholder text such as `...`. Cron daemons will emit “Could not open input file” if the subdirectory is missing, as happened when the refresh-cache job used `/home/resolut7/public_html/.../cms/api/refresh-cache-chunked.php process`.

That's it! You never need to modify cPanel again.

---

## Managing Tasks

All task management happens in `cms/cron-router.php`. Edit the `$tasks` array to:

- Enable/disable tasks
- Change schedules
- Add new tasks
- Modify task behavior

### Task Configuration

```php
[
    'name' => 'task-identifier',
    'enabled' => true,  // Set to false to disable
    'interval' => 'every_minute',  // See schedules below
    'description' => 'What this task does',
    'callback' => function() {
        // Task logic here
        return ['status' => 'success'];
    }
]
```

### Available Schedules

- `'every_minute'` - Runs every minute
- `'every_5_minutes'` - Runs every 5 minutes
- `'every_15_minutes'` - Runs every 15 minutes
- `'hourly'` - Runs once per hour
- `'every_6_hours'` - Runs every 6 hours
- `'daily'` - Runs once per day at midnight
- `'weekly'` - Runs once per week on Sunday

---

## Current Tasks

### 1. Chunked Cache Refresh (ENABLED)

**Name:** `cache-refresh-chunked`
**Schedule:** Every minute
**Purpose:** Processes one chunk of cache refresh (devices or drill-downs)

This task runs continuously to populate the database. It will:
- Fetch 100 devices per minute during device phase
- Fetch 10 drill-downs per minute during drill-down phase
- Complete full refresh in 30-60 minutes

### 2. Daily Cache Refresh Init (DISABLED)

**Name:** `cache-refresh-daily-init`
**Schedule:** Daily at midnight
**Purpose:** Automatically starts a new cache refresh cycle

Enable this if you want automatic daily refreshes:
```php
'enabled' => true,
```

### 3. Panel Message Processing (DISABLED)

**Name:** `process-panel-messages`
**Schedule:** Every 15 minutes
**Purpose:** Example task for processing panel messages in batches

### 4. Cache Cleanup (DISABLED)

**Name:** `cache-cleanup`
**Schedule:** Weekly
**Purpose:** Removes logs older than 30 days

---

## Monitoring

### View Logs

Daily logs are created in `cms/logs/cron-router-YYYY-MM-DD.log`

```bash
tail -f cms/logs/cron-router-$(date +%Y-%m-%d).log
```

### Web Access (Testing)

You can trigger the router manually via web browser:

```
https://mpsm.resolutionsbydesign.us/cms/cron-router.php?secret=cron-router-access-2025
```

This shows JSON output of what ran.

### Check Task Status

Each task creates lock files in `cms/locks/`:
- `{task-name}.lock` - Indicates task is currently running
- `{task-name}.lastrun` - Timestamp of last successful run

---

## Adding New Tasks

Example: Add a task to email reports weekly

```php
[
    'name' => 'weekly-report-email',
    'enabled' => true,
    'interval' => 'weekly',
    'description' => 'Send weekly dashboard report via email',
    'callback' => function() {
        // Your logic here
        $pdo = getDatabase();
        $stats = /* fetch stats */;

        mail(
            'admin@example.com',
            'Weekly Dashboard Report',
            "Stats: " . json_encode($stats)
        );

        return ['status' => 'success', 'sent' => true];
    }
],
```

Then commit and push the change. The CRON job will pick it up automatically on the next minute.

---

## Troubleshooting

### Task Not Running

1. Check if task is enabled: `'enabled' => true`
2. Check schedule interval matches expectation
3. View logs: `cms/logs/cron-router-YYYY-MM-DD.log`
4. Check lock file isn't stuck: `cms/locks/{task-name}.lock`

### Stuck Lock

If a task shows "already running" but isn't, remove the lock file:
```bash
rm cms/locks/{task-name}.lock
```

Locks auto-expire after 1 hour to prevent permanent stalls.

### CRON Not Running

Test manually:
```bash
php cms/cron-router.php
```

Check cPanel CRON jobs are active.

---

## Security

- Web access requires secret parameter to prevent abuse
- Lock files prevent concurrent execution
- Failed tasks are logged but don't stop other tasks
- Each task runs in isolated try/catch block

---

## Benefits

✅ **ONE cPanel CRON job** manages everything
✅ **Git-based task management** - all changes in version control
✅ **No cPanel access needed** for task changes
✅ **Centralized logging** - all tasks log to one place
✅ **Overlap prevention** - automatic locking prevents duplicates
✅ **Easy testing** - run via CLI or web browser
✅ **Flexible scheduling** - predefined intervals or custom logic

---

## Example Workflow

1. Need to add a new scheduled task? Edit `cms/cron-router.php`
2. Add task to `$tasks` array with your logic
3. Commit and push to GitHub
4. Deploy to server (git pull)
5. Task runs automatically on next schedule

No cPanel access required!

/*
CHANGELOG
2025-11-17 Codex
- Clarified the required cron paths, warned against using placeholder ellipses, and linked the observed “Could not open input file” failure so future CRON edits stay accurate.
*/
