# MPS MONITORS API ENGINE - QUICK START

**Get up and running in 5 minutes!**

## Prerequisites
- GreenGeeks hosting with cPanel
- MPS Monitors API credentials

## Step 1: Upload Files (2 minutes)

### Option A: cPanel File Manager
1. Log into cPanel → File Manager
2. Navigate to `public_html/`
3. Create folder: `mps-api`
4. Upload ALL files to this folder

### Option B: FTP
1. Connect to your FTP
2. Navigate to `public_html/`
3. Create folder: `mps-api`
4. Upload ALL files

## Step 2: Configure (2 minutes)

1. In File Manager, copy `.env.example` to `.env`
2. Edit `.env`:
```env
MPS_BASE_URL=https://api.mpsmonitors.com/v1
MPS_API_KEY=your_actual_api_key_here
MPS_TIMEOUT=30
MPS_DEBUG=false
```
3. Save

## Step 3: Set Permissions (1 minute)

1. Select `.env` → Permissions → Set to `644`
2. Create `logs` folder if not exists
3. Set `logs/` permissions to `755`

## Step 4: Test (<1 minute)

Open browser:
```
https://yourdomain.com/mps-api/health
```

**Expected:**
```json
{
  "status": "healthy",
  "api_connection": true,
  "response_time": "123ms"
}
```

✅ **Done!** Your API is live.

---

## Quick Test Commands

```bash
# Health check
curl https://yourdomain.com/mps-api/health

# Available endpoints
curl https://yourdomain.com/mps-api/endpoints

# Test query
curl -X POST https://yourdomain.com/mps-api/query \
  -H "Content-Type: application/json" \
  -d '{"action":"healthCheck","params":{}}'
```

---

## Common Issues

**500 Error?**
- Check `.env` file exists and has valid credentials

**404 Error?**
- Verify `.htaccess` uploaded
- Check subdirectory name matches in `.htaccess` line 9

**CORS Error?**
- Update `index.php` line 25 with your dashboard domain

---

## Next Steps

1. **ChatGPT Integration**: Import `swagger.json` in GPT Actions
2. **Dashboard**: Use examples in `SDK_Examples_Verified_Working.md`
3. **Full Docs**: Read `README.md` and `HANDOFF.md`

---

## File Checklist

Make sure you uploaded:
- [x] `index.php`
- [x] `engine.php`
- [x] `config.php`
- [x] `.env.example` (then copy to `.env`)
- [x] `.htaccess`
- [x] `swagger.json`
- [x] All `.md` files

---

## Support

- **Detailed Deployment**: See `DEPLOYMENT.md`
- **API Usage**: See `SDK_Examples_Verified_Working.md`
- **Full Documentation**: See `README.md` and `HANDOFF.md`
- **GreenGeeks Issues**: Contact GreenGeeks support

---

**That's it!** 🎉

Your MPS Monitors API Engine is ready for:
- ✅ ChatGPT Actions
- ✅ Dashboard integration  
- ✅ Direct API calls

**Production URL:** https://mpsm.resolutionsbydesign.us/mps-api/
