# Pre-Deployment Checklist

Use this checklist before deploying to production.

## 🔧 Configuration

- [ ] **`.env` file exists** at project root
- [ ] **`.env` has production credentials**
  - [ ] `MPS_BASE_URL` is correct production URL
  - [ ] `MPS_API_KEY` is production key (NOT test/placeholder)
  - [ ] All OAuth values set (if using OAuth instead of API key)
- [ ] **`MPS_DEBUG=false`** in `.env` (not true!)
- [ ] **Config values validated**
  - [ ] No placeholder values like "your_api_key_here"
  - [ ] URLs use HTTPS
  - [ ] API key is at least 10 characters

## 🔒 Security

- [ ] **Root `.htaccess`** protects sensitive files
  - [ ] Blocks `.env` access
  - [ ] Blocks `.git` directory
  - [ ] Blocks `logs/` directories
  - [ ] Forces HTTPS
- [ ] **API `.htaccess`** exists at `mps-api/.htaccess`
  - [ ] Blocks `engine.php` direct access
  - [ ] Blocks `config.php` direct access
  - [ ] Routes requests through `index.php`
- [ ] **Database directory protected** (if exists)
- [ ] **No credentials in code** (all in `.env`)

## 📦 Files & Structure

- [ ] **Canonical swagger exists**: `.canonical/Swagger.json`
- [ ] **Swagger file is valid JSON** (1.2MB, 544 operations)
- [ ] **SwaggerActionRegistry points to canonical**
  - Check: `dirname(__DIR__) . '/.canonical/Swagger.json'` is first in search list
- [ ] **All required PHP files present**
  - [ ] `index.php` (root monitoring interface)
  - [ ] `mps-api/index.php` (API router)
  - [ ] `mps-api/engine.php`
  - [ ] `mps-api/config.php`
  - [ ] `mps-api/SwaggerActionRegistry.php`
- [ ] **No test files in production paths**
  - [ ] Remove `mps-api/test_*.php` if any
  - [ ] Remove `mps-api/verify_*.py` if any

## 🚀 Deployment Config

- [ ] **`deploy.yml` is configured**
  - [ ] FTP server address correct
  - [ ] FTP username correct
  - [ ] FTP password correct
  - [ ] Server directory correct (`/`)
- [ ] **Exclusions properly set**
  - [ ] Excludes `.git*`, `.github/`
  - [ ] Excludes `*.log`, `logs/`
  - [ ] Excludes `tests/`, test scripts
  - [ ] Excludes `node_modules/`
  - [ ] Excludes IDE files (`.vscode/`, `.idea/`)
- [ ] **Important files NOT excluded**
  - [ ] `.htaccess` files ARE deployed
  - [ ] `.env` file IS deployed (will be protected)
  - [ ] `.canonical/Swagger.json` IS deployed
  - [ ] `mps-api/*.php` files ARE deployed

## 🧪 Local Testing

- [ ] **Test root URL**
  - Visit `http://localhost/` or your local path
  - Should show monitoring interface
  - Health check should work
- [ ] **Test API health**
  - Visit `/mps-api/health`
  - Should return JSON
  - Should show 544 operations
- [ ] **Test endpoints list**
  - Visit `/mps-api/endpoints`
  - Should return JSON with 544 operations
- [ ] **Test swagger access**
  - Visit `/mps-api/swagger.json`
  - Should return canonical swagger JSON
- [ ] **Test query endpoint**
  - Use monitoring interface test harness
  - Try `healthCheck` action
  - Should return success
- [ ] **Verify file protection**
  - Try accessing `/mps-api/engine.php` → Should fail (403)
  - Try accessing `/mps-api/config.php` → Should fail (403)
  - Try accessing `/.env` → Should fail (403)
  - Try accessing `/logs/` → Should fail (403)
  - Try accessing `/.canonical/Swagger.json` → Should fail (403)

## 📝 Code Review

- [ ] **No hardcoded credentials** anywhere in code
- [ ] **No debug output** (var_dump, print_r) in production code
- [ ] **Error reporting off** for production
  - Check: `ini_set('display_errors', 0)` in index files
- [ ] **Proper error logging** configured
- [ ] **No TODO comments** marked as critical

## 📊 Documentation

- [ ] **Read DEPLOYMENT_ALIGNMENT.md**
- [ ] **Read REQUEST_FLOW.md**
- [ ] **Read ALIGNMENT_COMPLETE.md**
- [ ] **Understand the security layers**

## ⚙️ Pre-Commit

- [ ] **Run git status** - verify what will be committed
- [ ] **No log files staged** for commit
- [ ] **No `.env` changes** staged (should be .gitignored)
- [ ] **All changes intentional**

## 🚦 Ready to Deploy

When all above items are checked:

1. **Commit changes**
   ```bash
   git add .
   git commit -m "Production deployment: aligned config and canonical swagger"
   ```

2. **Push to main**
   ```bash
   git push origin main
   ```

3. **Monitor GitHub Actions**
   - Go to: https://github.com/your-repo/actions
   - Watch deployment progress
   - Verify "✓" success

## 🎯 Post-Deployment

After deployment completes:

- [ ] **Visit production root URL**
  - Should show monitoring interface
  - Health status should be green
  - Should show "544 operations"
- [ ] **Test API health endpoint**
  - Visit `https://your-domain.com/mps-api/health`
  - Should return JSON
  - HTTP status should be 200
- [ ] **Test endpoints list**
  - Visit `https://your-domain.com/mps-api/endpoints`
  - Should list all 544 operations
- [ ] **Test swagger JSON**
  - Visit `https://your-domain.com/mps-api/swagger.json`
  - Should return 1.2MB JSON file
- [ ] **Run test query**
  - Use monitoring interface at root
  - Try action: `healthCheck`
  - Should succeed
- [ ] **Verify HTTPS redirect**
  - Try HTTP URL
  - Should redirect to HTTPS
- [ ] **Check security**
  - Try `https://your-domain.com/.env` → Should fail
  - Try `https://your-domain.com/mps-api/engine.php` → Should fail
  - Try `https://your-domain.com/.canonical/Swagger.json` → Should fail
- [ ] **Check logs on server**
  - Verify no errors in `mps-api/logs/`
  - Check PHP error logs if available

## 🐛 Troubleshooting

### If deployment fails:
1. Check GitHub Actions logs
2. Verify FTP credentials
3. Check server connection
4. Try manual FTP upload

### If site doesn't load:
1. Check `.htaccess` syntax
2. Verify PHP version (7.4+)
3. Check file permissions (644 for files, 755 for dirs)
4. Look at server error logs

### If API returns errors:
1. Check `.env` file deployed correctly
2. Verify `MPS_BASE_URL` and `MPS_API_KEY`
3. Enable `MPS_DEBUG=true` temporarily
4. Check `mps-api/logs/` for errors
5. Test with a simple healthCheck

### If files are blocked unexpectedly:
1. Check `.htaccess` rules
2. Verify file permissions
3. Test with different file
4. Check server Apache config

## 📞 Support Resources

- **Deployment Docs**: [DEPLOYMENT_ALIGNMENT.md](DEPLOYMENT_ALIGNMENT.md)
- **Request Flow**: [REQUEST_FLOW.md](REQUEST_FLOW.md)
- **Refactor Summary**: [REFACTOR_COMPLETE.md](REFACTOR_COMPLETE.md)
- **Quick Start**: [mps-api/QUICK_START.md](mps-api/QUICK_START.md)
- **Usage Examples**: [mps-api/USAGE_EXAMPLES.md](mps-api/USAGE_EXAMPLES.md)

## ✅ Final Check

Before clicking deploy, ask yourself:

- ✅ Do I have a backup of current production?
- ✅ Are credentials correct for production?
- ✅ Is debug mode OFF?
- ✅ Have I tested locally?
- ✅ Have I read the alignment docs?
- ✅ Am I deploying during a safe time window?

**If all YES → Deploy!** 🚀

---

**Remember**: You can always roll back by pushing a previous commit or manually uploading files via FTP.
