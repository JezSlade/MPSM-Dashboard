# Deployment Checklist for mpsm.resolutionsbydesign.us

## Pre-Deployment Status

✅ **All fixes implemented and verified** (10/10 tests passed)
✅ **Production domain identified:** `mpsm.resolutionsbydesign.us`
✅ **Testing framework ready:** test.php, verify_fixes.py
⏳ **Ready for deployment**

---

## Deployment Checklist

### 1. Server Preparation

#### A. Domain & DNS
- [ ] DNS A record points to server IP
- [ ] Subdomain `mpsm.resolutionsbydesign.us` resolves correctly
- [ ] Test: `ping mpsm.resolutionsbydesign.us`

#### B. SSL Certificate
- [ ] SSL certificate installed for `mpsm.resolutionsbydesign.us`
- [ ] Certificate includes subdomain (or wildcard *.resolutionsbydesign.us)
- [ ] Auto-renewal configured (if Let's Encrypt)
- [ ] Test: `curl -I https://mpsm.resolutionsbydesign.us`

#### C. Web Server
- [ ] Apache/Nginx installed and running
- [ ] PHP 7.4+ installed (`php -v`)
- [ ] Required PHP extensions:
  - [ ] `php-curl` (for API requests)
  - [ ] `php-json` (for JSON handling)
  - [ ] `php-mbstring` (for string handling)
- [ ] Test: `php -m | grep -E "curl|json|mbstring"`

### 2. File Deployment

#### A. Upload Files
```bash
# Files to upload to server
mps-api/
├── index.php              # Main entry point ✓
├── engine.php             # Core engine with fixes ✓
├── SwaggerActionRegistry.php  # Swagger parser ✓
├── Swagger.json           # API definitions ✓
├── .env.example           # Template (rename to .env)
├── .htaccess              # Apache rewrite rules (if needed)
├── test.php               # Testing suite ✓
├── verify_fixes.py        # Verification script ✓
└── logs/                  # Create writable directory
```

#### B. Set Permissions
```bash
# On server
chmod 755 mps-api/
chmod 644 mps-api/*.php
chmod 644 mps-api/*.json
chmod 755 mps-api/logs/
chmod 600 mps-api/.env  # Protect secrets
```

### 3. Configuration

#### A. Environment File
- [ ] Copy `.env.example` to `.env`
- [ ] Configure OAuth credentials:
  ```bash
  API_BASE_URL=https://your-mpsm-api.com
  TOKEN_URL=https://your-mpsm-api.com/oauth/token
  CLIENT_ID=your_client_id
  CLIENT_SECRET=your_client_secret
  USERNAME=your_username
  PASSWORD=your_password
  ```
- [ ] Configure dealer information:
  ```bash
  DEALER_CODE=YOUR_CODE
  DEALER_ID=12345
  ```
- [ ] Set debug mode for testing:
  ```bash
  MPS_DEBUG=true  # Change to false after testing
  ```

#### B. Web Server Configuration

**If using Apache:**
- [ ] Create virtual host configuration
- [ ] Enable SSL and required modules
- [ ] Configure document root
- [ ] Restart Apache

**Apache VirtualHost Example:**
```apache
<VirtualHost *:443>
    ServerName mpsm.resolutionsbydesign.us
    DocumentRoot /var/www/mps-api

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/resolutionsbydesign_us.crt
    SSLCertificateKeyFile /etc/ssl/private/resolutionsbydesign_us.key
    SSLCertificateChainFile /etc/ssl/certs/resolutionsbydesign_us_chain.crt

    <Directory /var/www/mps-api>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Enable rewrite if using .htaccess
        RewriteEngine On
    </Directory>

    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set X-XSS-Protection "1; mode=block"

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/mpsm-error.log
    CustomLog ${APACHE_LOG_DIR}/mpsm-access.log combined
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName mpsm.resolutionsbydesign.us
    Redirect permanent / https://mpsm.resolutionsbydesign.us/
</VirtualHost>
```

**If using Nginx:**
```nginx
server {
    listen 443 ssl http2;
    server_name mpsm.resolutionsbydesign.us;

    root /var/www/mps-api;
    index index.php;

    ssl_certificate /etc/ssl/certs/resolutionsbydesign_us.crt;
    ssl_certificate_key /etc/ssl/private/resolutionsbydesign_us.key;

    # Security headers
    add_header X-Content-Type-Options "nosniff";
    add_header X-Frame-Options "DENY";
    add_header X-XSS-Protection "1; mode=block";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\.env {
        deny all;
    }

    location ~ /\.git {
        deny all;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name mpsm.resolutionsbydesign.us;
    return 301 https://$server_name$request_uri;
}
```

### 4. Post-Deployment Testing

#### A. Health Check
```bash
# Test basic connectivity
curl https://mpsm.resolutionsbydesign.us/health

# Expected response:
{
  "status": "ok",
  "service": "MPS Monitors API Engine",
  "version": "1.1.0",
  "config": {
    "auth_mode": "oauth_password",
    "dealer_code_configured": true
  }
}
```

#### B. Verify Fixes on Server
```bash
# SSH into server and run:
cd /var/www/mps-api
python3 verify_fixes.py

# Should show: 10/10 tests passed
```

#### C. Test OAuth (Fix #1)
```bash
curl -X POST https://mpsm.resolutionsbydesign.us/query \
  -H "Content-Type: application/json" \
  -d '{"action":"healthCheck","params":{}}'

# Should return 200 OK with dealer info
```

#### D. Test Auto-population (Fix #2)
```bash
# Test without dealer code (should auto-populate)
curl -X POST https://mpsm.resolutionsbydesign.us/query \
  -H "Content-Type: application/json" \
  -d '{"action":"getDealerInfo","params":{}}'

# Should NOT return "Missing required parameter: code"
```

#### E. Test Real Endpoint
```bash
# Test a real endpoint that we know works from discovery
curl -X POST https://mpsm.resolutionsbydesign.us/query \
  -H "Content-Type: application/json" \
  -d '{"action":"getCustomers","params":{}}'

# Should return customer data or proper error message
```

#### F. Test Error Handling (Fix #3)
```bash
# Test with invalid dealer code (should catch MPSM error)
curl -X POST https://mpsm.resolutionsbydesign.us/query \
  -H "Content-Type: application/json" \
  -d '{"action":"getDealerInfo","params":{"code":"INVALID"}}'

# Should return properly formatted error from MPSM
```

### 5. Security Verification

- [ ] `.env` file not accessible via web (403 Forbidden)
- [ ] `.git` directory not accessible (if exists)
- [ ] `logs/` directory not browsable
- [ ] No PHP errors displayed (check production mode)
- [ ] HTTPS enforced (HTTP redirects to HTTPS)
- [ ] Security headers present (X-Frame-Options, etc.)

**Test:**
```bash
# These should all return 403 or 404
curl -I https://mpsm.resolutionsbydesign.us/.env
curl -I https://mpsm.resolutionsbydesign.us/.git/
curl -I https://mpsm.resolutionsbydesign.us/logs/
```

### 6. Performance & Monitoring

#### A. Enable Production Mode
- [ ] Set `MPS_DEBUG=false` in `.env` after testing
- [ ] Verify error messages don't expose sensitive info

#### B. Log Monitoring
```bash
# Set up log rotation
sudo nano /etc/logrotate.d/mps-api

# Content:
/var/www/mps-api/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    missingok
    notifempty
}
```

#### C. Monitor Initial Traffic
```bash
# Watch logs for issues
tail -f /var/www/mps-api/logs/mps_api_*.log
tail -f /var/log/apache2/mpsm-error.log  # or nginx equivalent
```

### 7. Available Endpoints

After deployment, these endpoints should work:

| Endpoint | Method | Description | Test |
|----------|--------|-------------|------|
| `/` | GET | Status & info | ✓ Public |
| `/health` | GET | Health check | ✓ Public |
| `/endpoints` | GET | List all actions | ✓ Public |
| `/swagger.json` | GET | API documentation | ✓ Public |
| `/query` | POST | Main query endpoint | Requires action param |

**Quick Test:**
```bash
# List all available actions
curl https://mpsm.resolutionsbydesign.us/endpoints | jq '.operations[].action' | head -20
```

### 8. Documentation for Users

After deployment is verified, document the API for Custom GPT:

#### A. Create OpenAPI Spec
```yaml
openapi: 3.0.0
info:
  title: MPS Monitors API
  version: 1.1.0
  description: |
    API for querying MPS Monitor System (MPSM) data through a simplified interface.
    Handles OAuth authentication, dealer codes, and MPSM response validation automatically.

servers:
  - url: https://mpsm.resolutionsbydesign.us
    description: Production API

paths:
  /query:
    post:
      summary: Query MPSM data
      description: |
        Main endpoint for all MPSM queries. Automatically handles:
        - OAuth authentication (Fix #1)
        - Dealer code population (Fix #2)
        - MPSM error validation (Fix #3)

      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - action
              properties:
                action:
                  type: string
                  description: The MPSM action to perform
                  example: getDealerInfo
                params:
                  type: object
                  description: Optional parameters (dealer codes auto-populated)
                  example: {}

      responses:
        '200':
          description: Successful response
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                  data:
                    type: object
        '400':
          description: Bad request
        '502':
          description: API error

  /health:
    get:
      summary: Health check
      responses:
        '200':
          description: Service is healthy

  /endpoints:
    get:
      summary: List available actions
      responses:
        '200':
          description: List of all available MPSM actions
```

Save as: `mps-api/openapi.yaml`

### 9. Common Issues & Solutions

#### Issue: 500 Internal Server Error
**Check:**
```bash
# PHP error logs
tail -50 /var/log/apache2/mpsm-error.log
# Or application logs
tail -50 /var/www/mps-api/logs/php_errors_*.log
```

**Common causes:**
- PHP version too old (need 7.4+)
- Missing PHP extensions (curl, json, mbstring)
- File permissions wrong
- Syntax errors in .env file

#### Issue: "Failed to acquire OAuth token"
**Check:**
- CLIENT_ID, CLIENT_SECRET in .env are correct
- USERNAME, PASSWORD in .env are correct
- TOKEN_URL in .env is accessible from server
- Server can make outbound HTTPS connections

**Test:**
```bash
curl -v $TOKEN_URL  # Should connect without SSL errors
```

#### Issue: "Missing required parameter: code"
**Check:**
- DEALER_CODE is set in .env
- Fix #2 is properly implemented (verify_fixes.py should pass)
- .env file is being loaded (check logs with MPS_DEBUG=true)

#### Issue: SSL Certificate Errors
**Check:**
```bash
# Test SSL
openssl s_client -connect mpsm.resolutionsbydesign.us:443

# Check certificate expiry
echo | openssl s_client -connect mpsm.resolutionsbydesign.us:443 2>/dev/null | openssl x509 -noout -dates
```

### 10. Final Verification Checklist

Before considering deployment complete:

- [ ] Health endpoint returns 200 OK
- [ ] OAuth tokens are acquired successfully
- [ ] Dealer codes auto-populate (no "missing code" errors)
- [ ] At least 3 different actions work successfully
- [ ] Error responses are properly formatted
- [ ] HTTPS is enforced
- [ ] Logs are being written
- [ ] No sensitive data exposed in errors (production mode)
- [ ] Documentation updated with production URL

### 11. Custom GPT Integration (After Deployment)

Once the API is deployed and tested:

1. **Create Custom GPT** in ChatGPT
2. **Import OpenAPI spec** (openapi.yaml)
3. **Test natural language queries:**
   - "Get my dealer information"
   - "Show me all customers"
   - "List vehicles"
4. **Refine and iterate**

---

## Rollback Plan

If deployment fails:

1. **Keep old version** available during deployment
2. **Test thoroughly** before switching DNS/traffic
3. **If issues occur:**
   - Switch back to old version
   - Review logs in `logs/` directory
   - Test locally with `test.php`
   - Fix issues and redeploy

---

## Support Contacts

- **API Issues:** Check logs in `/var/www/mps-api/logs/`
- **MPSM API Issues:** Contact MPSM support
- **Server Issues:** Contact hosting provider
- **SSL Issues:** Contact certificate provider

---

## Quick Command Reference

```bash
# Test health
curl https://mpsm.resolutionsbydesign.us/health

# List actions
curl https://mpsm.resolutionsbydesign.us/endpoints | jq '.count'

# Test query
curl -X POST https://mpsm.resolutionsbydesign.us/query \
  -H "Content-Type: application/json" \
  -d '{"action":"getDealerInfo","params":{}}'

# Check logs
tail -f /var/www/mps-api/logs/mps_api_*.log

# Verify fixes
cd /var/www/mps-api && python3 verify_fixes.py

# Restart Apache
sudo systemctl restart apache2

# Restart Nginx
sudo systemctl restart nginx
```

---

**Deployment Domain:** `https://mpsm.resolutionsbydesign.us`
**Status:** Ready for deployment ✓
**Documentation:** Complete ✓
**Testing:** Verified (10/10 tests passed) ✓
