# MPSM Dashboard (PHP-Driven API Core)

## 🔧 What This Is

Live API-powered dashboard served from a GreenGeeks static subdomain, using PHP to bridge Python backend execution.

## ✅ Features

- Token-based authentication with MPS Monitor
- Real-time device listing via `auth.py` and `api_call.py`
- No need for Flask, Passenger, or dynamic web servers
- Triggered from browser via `trigger_api.php`

## 🚀 Deployment Instructions

1. Upload all files to `/public_html/mpsm.resolutionsbydesign.us/mpsm/`
2. Ensure Python path in `trigger_api.php` is correct (usually `/usr/bin/python3`)
3. Place `.env` with your secure credentials alongside `api_call.py`
4. Visit: https://mpsm.resolutionsbydesign.us/mpsm/
5. Optional: Secure `trigger_api.php` with IP lock or token

## 🛠 Development Notes

- `auth.py` is fully modular and reusable
- API errors are logged to browser via debug console
- No need to reload or pre-generate static JSON

---
