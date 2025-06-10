# MPSM Dashboard (PHP + Vanilla JS)

## Overview

A lightweight PHP front-end serving a dark glassmorphic dashboard for the MPS Monitor API.  
Includes:

- **Full PHP error reporting** on every page  
- A **Debug Panel** baked into every view, logging PHP & JS errors  
- Role-based cards (Developer, Admin, Dealer, Service, Sales, Accounting, Guest)  
- Drill-down modal for endpoint details  
- DB & API connectivity checks  

## Installation

1. Copy `.env.example` to `.env` and fill in credentials.  
2. Drop the entire `mpsm-dashboard/` folder into your server’s document root.  
3. Ensure `public/` is your web root (e.g. `https://…/index.php`).  
4. Open `https://your-domain/` in your browser.

## Next Steps

- Implement real DB/API checks in `db-status.php` and `api-status.php`.  
- Hook up MPS Monitor token logic & real API calls.  
- Enjoy the built-in debug panel for troubleshooting!
