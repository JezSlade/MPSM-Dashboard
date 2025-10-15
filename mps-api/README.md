# MPS Monitors API Engine

**Version:** 1.0.0  
**PHP Version:** 7.4+  
**Environment:** GreenGeeks Shared Hosting (Subdirectory Deployment)

## Overview

The MPS Monitors API Engine is a lightweight, subdirectory-deployable PHP API gateway designed specifically for:
- **ChatGPT Actions integration** - Unified query endpoint optimized for AI assistants
- **Dashboard integration** - Clean REST endpoints for web applications
- **GreenGeeks hosting** - No framework dependencies, subdirectory-safe paths
- **Minimal footprint** - Pure PHP with cURL, no external dependencies

## Features

✅ **Subdirectory Safe** - Deploy in any subdirectory without code changes  
✅ **No Framework** - Pure PHP 7.4+ compatible  
✅ **Unified Query Endpoint** - Single `/query` endpoint for all operations  
✅ **RESTful Routes** - Standard REST endpoints for direct access  
✅ **OpenAPI 3.0** - Swagger documentation for ChatGPT Actions  
✅ **CORS Enabled** - Cross-origin ready for dashboard integration  
✅ **Error Logging** - Automatic logging to subdirectory logs folder  
✅ **Health Monitoring** - Built-in health check and diagnostics  

## Architecture

```
mps-api/                    # Subdirectory root
├── index.php               # Main router & entry point
├── engine.php              # Core API engine class
├── config.php              # Configuration loader
├── .env                    # Environment variables (create from .env.example)
├── .env.example            # Environment template
├── .htaccess               # Apache rewrite rules
├── swagger.json            # OpenAPI specification
├── SDK_Examples_Verified_Working.md
├── README.md               # This file
└── logs/                   # Auto-created error logs
    ├── error_YYYY-MM-DD.log
    └── php_errors_YYYY-MM-DD.log
```

## Installation

### 1. Upload Files

Upload all files to your GreenGeeks subdirectory:
```
public_html/mps-api/  (or your chosen subdirectory)
```

### 2. Configure Environment

Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

Edit `.env` with your MPS Monitors credentials:
```env
MPS_BASE_URL=https://api.mpsmonitors.com/v1
MPS_API_KEY=your_actual_api_key_here
MPS_TIMEOUT=30
MPS_DEBUG=false
```

### 3. Set Permissions

```bash
chmod 644 .env
chmod 755 logs/
```

### 4. Update .htaccess (if needed)

If your subdirectory is NOT `/mps-api/`, update line 9 in `.htaccess`:
```apache
RewriteBase /your-subdirectory/
```

### 5. Verify Installation

Visit: `https://yourdomain.com/mps-api/health`

Expected response:
```json
{
  "status": "healthy",
  "api_connection": true,
  "response_time": "123.45ms",
  "timestamp": "2024-10-15T12:00:00+00:00"
}
```

## API Endpoints

### Information Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/` | GET | API info and available endpoints |
| `/health` | GET | Health check and connectivity test |
| `/endpoints` | GET | List all available endpoints |
| `/swagger.json` | GET | OpenAPI specification |

### Query Endpoint (Unified)

**Primary endpoint for ChatGPT Actions:**

```http
POST /query
Content-Type: application/json

{
  "action": "ACTION_NAME",
  "params": {}
}
```

**Available Actions:**
- `getMonitors` - List all monitors
- `getMonitor` - Get specific monitor (requires `id`)
- `createMonitor` - Create new monitor
- `updateMonitor` - Update monitor (requires `id`)
- `deleteMonitor` - Delete monitor (requires `id`)
- `getAlerts` - List alerts
- `getStatistics` - Get monitor statistics (requires `id`, optional `period`)
- `healthCheck` - Health check

### REST Endpoints (Direct Access)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/monitors` | GET | List monitors |
| `/monitors` | POST | Create monitor |
| `/monitors/{id}` | GET | Get monitor |
| `/monitors/{id}` | PUT | Update monitor |
| `/monitors/{id}` | DELETE | Delete monitor |
| `/alerts` | GET | List alerts |
| `/monitors/{id}/statistics` | GET | Get statistics |

## Usage Examples

### Quick Start (PHP)

```php
<?php
$apiBase = 'https://mpsm.resolutionsbydesign.us/mps-api';

// Get all monitors
$ch = curl_init($apiBase . '/monitors');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

print_r($response);
```

### Query Endpoint (JavaScript)

```javascript
async function queryAPI(action, params = {}) {
    const response = await fetch('https://mpsm.resolutionsbydesign.us/mps-api/query', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, params })
    });
    return await response.json();
}

// Get monitors
const monitors = await queryAPI('getMonitors');

// Create monitor
const result = await queryAPI('createMonitor', {
    name: 'My Website',
    url: 'https://example.com',
    interval: 60
});
```

### cURL Examples

```bash
# Health check
curl https://mpsm.resolutionsbydesign.us/mps-api/health

# List monitors
curl https://mpsm.resolutionsbydesign.us/mps-api/monitors

# Create monitor via query endpoint
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/query \
  -H "Content-Type: application/json" \
  -d '{"action":"createMonitor","params":{"name":"Test","url":"https://example.com"}}'
```

See `SDK_Examples_Verified_Working.md` for comprehensive examples.

## ChatGPT Actions Integration

### Setup Instructions

1. **Import Swagger Definition**
   - URL: `https://mpsm.resolutionsbydesign.us/mps-api/swagger.json`
   - Or copy content from `swagger.json` file

2. **Authentication**: None (handled by backend)

3. **Test Prompts**:
   - "Show me all monitors"
   - "Create a monitor for https://example.com"
   - "What alerts do I have?"
   - "Get statistics for monitor_123"

The `/query` endpoint is optimized for ChatGPT Actions with a single unified interface.

## Configuration

### Environment Variables

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `MPS_BASE_URL` | ✅ | - | MPS Monitors API base URL |
| `MPS_API_KEY` | ✅ | - | Your MPS Monitors API key |
| `MPS_TIMEOUT` | ❌ | 30 | Request timeout (seconds) |
| `MPS_DEBUG` | ❌ | false | Enable debug logging |

### Path Configuration

The engine automatically detects its subdirectory location. No hardcoded paths.

**How it works:**
- Uses `__DIR__` for file paths
- Uses `$_SERVER['SCRIPT_NAME']` for URL generation
- Portable to any subdirectory

## Error Handling

### Response Format

Success:
```json
{
  "success": true,
  "data": {},
  "http_code": 200
}
```

Error:
```json
{
  "success": false,
  "error": "Error message",
  "http_code": 400
}
```

### Logging

Errors are automatically logged to:
- `logs/error_YYYY-MM-DD.log` - Engine errors
- `logs/php_errors_YYYY-MM-DD.log` - PHP errors
- `logs/config_error_YYYY-MM-DD.log` - Configuration errors

## Security

### Protected Files

`.htaccess` blocks access to:
- `.env` file
- `.git` directory
- `.log` files

### CORS Policy

Default: Allow all origins (`*`)

**Production:** Update in `index.php` and `.htaccess`:
```php
header('Access-Control-Allow-Origin: https://yourdomain.com');
```

### API Key Security

- API key stored in `.env` file
- Never exposed in responses
- Backend-only authentication

## Troubleshooting

### Common Issues

**1. 500 Internal Server Error**
- Check `.env` file exists and is readable
- Verify PHP version (7.4+)
- Check error logs in `logs/` directory

**2. CORS Errors**
- Update CORS headers in `index.php`
- Check `.htaccess` CORS settings

**3. Configuration Not Found**
- Ensure `.env` file is in same directory as `index.php`
- Check file permissions (644)

**4. API Connection Failed**
- Verify `MPS_BASE_URL` is correct
- Check `MPS_API_KEY` is valid
- Test with `/health` endpoint

### Debug Mode

Enable in `.env`:
```env
MPS_DEBUG=true
```

This enables additional logging. Check `logs/` for details.

## Performance

- **Response Time**: Typically 100-500ms (depends on MPS Monitors API)
- **Memory**: ~2MB per request
- **Concurrent Requests**: Limited by PHP-FPM configuration
- **Caching**: None (direct passthrough to MPS API)

## Limitations

- **GreenGeeks Shared Hosting**: No async/background processing
- **PHP 7.4**: No type declarations or newer PHP 8 features
- **Single API Key**: One MPS Monitors account per installation
- **No Database**: Stateless, no local data storage

## Maintenance

### Log Rotation

Logs are created daily. Manually remove old logs:
```bash
cd logs/
rm error_2024-*.log
```

### Updates

To update:
1. Backup current files
2. Upload new files
3. Keep existing `.env` file
4. Test `/health` endpoint

## Development

### Adding New Endpoints

1. Add method to `engine.php`:
```php
public function newMethod($param) {
    return $this->makeRequest('new-endpoint', 'GET', [], ['param' => $param]);
}
```

2. Add route in `index.php`:
```php
if ($path === '/new-route' && $method === 'GET') {
    $result = $engine->newMethod($_GET['param']);
    sendResponse($result);
}
```

3. Update `swagger.json` with new endpoint definition

### Testing

Use included examples in `SDK_Examples_Verified_Working.md`

## Support

- **Documentation**: This file and `SDK_Examples_Verified_Working.md`
- **API Spec**: `/swagger.json`
- **Endpoints List**: `/endpoints`
- **Health Check**: `/health`

## License

Proprietary - For MPS Monitors integration use only

## Version History

**1.0.0** (2024-10-15)
- Initial release
- Subdirectory deployment support
- ChatGPT Actions optimization
- Complete REST API coverage

---

**Deployment URL**: https://mpsm.resolutionsbydesign.us/mps-api/  
**Status**: Production Ready
