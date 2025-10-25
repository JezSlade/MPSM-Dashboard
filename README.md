# MPS Monitor Dashboard

**Production CMS for MPS Monitors API**
**Status:** ✅ Fully Deployed and Operational

---

## Quick Start

**Live Site:** https://mpsm.resolutionsbydesign.us/cms/

**Login:**
- Username: `admin`
- Password: `admin`

---

## Features

### Authentication
- Lightweight file-based user management
- Session-based authentication
- User CRUD in Admin panel

### Dashboard
- 9 customizable cards
- Real-time MPS API data
- Snapshot + modal pattern
- Drag-and-drop card arrangement
- Light/dark theme

### Caching
- MySQL persistent cache
- Hit rate tracking
- Automatic expiration
- Cache management interface

### Admin Interface
- Settings configuration
- Card management
- User management
- Engine monitoring
- Cache control
- Traffic analytics

---

## System Architecture

```
User → Login → Dashboard → Cards → MPS API Engine → MPS Monitors API
                                 ↓
                           MySQL Cache (mpsm_cache)
```

---

## Deployment

Auto-deploys via GitHub Actions on push to main.

**Manual Setup Required:**
1. Upload `cms/config/database.php` (credentials in local repo)
2. Run `cms/api/setup-cache-table.php` to create cache table

---

## Testing

Run comprehensive tests:
```bash
python test_live_site.py
```

---

## Documentation

- **API_VERIFIED_TRUTHS.md** - MPS API endpoint documentation
- **CARD_SYSTEM_TRUTHS.md** - Card system architecture
- **DEPLOYMENT_NOTES.md** - MySQL cache setup
- **DEPLOYMENT_COMPLETE.md** - Final deployment report

---

## Tech Stack

- **Frontend:** Vanilla JS, CSS Variables
- **Backend:** PHP 8.4, MySQL
- **API:** MPS Monitors API (OAuth 2.0)
- **Deployment:** GitHub Actions, FTP
- **Caching:** MySQL with TTL

---

## Current Status

**All Systems Operational:**
- ✅ Authentication working
- ✅ Database connected
- ✅ Cache functional
- ✅ User management active
- ✅ MPS API engine running (544 endpoints)
- ✅ Dashboard cards configured

**Deployment Date:** October 25, 2025

---

Built with [Claude Code](https://claude.com/claude-code)
