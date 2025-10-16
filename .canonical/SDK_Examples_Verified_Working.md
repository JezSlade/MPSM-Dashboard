# MPS Monitors API Engine - SDK Examples (Verified Working)

This document contains tested, working examples for integrating with the MPS Monitors API Engine.

## Table of Contents
1. [PHP Examples](#php-examples)
2. [JavaScript/Node.js Examples](#javascript-examples)
3. [Python Examples](#python-examples)
4. [cURL Examples](#curl-examples)
5. [ChatGPT Actions Setup](#chatgpt-actions-setup)

---

## PHP Examples

### Basic Setup
```php
<?php
$apiBase = 'https://mpsm.resolutionsbydesign.us/mps-api';

function apiRequest($endpoint, $method = 'GET', $data = []) {
    global $apiBase;
    
    $url = $apiBase . $endpoint;
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
```

### Get All Monitors
```php
// Method 1: Direct endpoint
$monitors = apiRequest('/monitors');

// Method 2: Query endpoint
$monitors = apiRequest('/query', 'POST', [
    'action' => 'getMonitors',
    'params' => ['status' => 'active']
]);

echo "Found " . count($monitors['data']) . " monitors\n";
```

### Get Specific Monitor
```php
$monitorId = 'monitor_123';
$monitor = apiRequest("/monitors/{$monitorId}");

if ($monitor['success']) {
    echo "Monitor: {$monitor['data']['name']}\n";
    echo "Status: {$monitor['data']['status']}\n";
}
```

### Create New Monitor
```php
$newMonitor = apiRequest('/query', 'POST', [
    'action' => 'createMonitor',
    'params' => [
        'name' => 'My Website',
        'url' => 'https://example.com',
        'interval' => 60
    ]
]);

if ($newMonitor['success']) {
    echo "Created monitor ID: {$newMonitor['data']['id']}\n";
}
```

### Update Monitor
```php
$result = apiRequest('/query', 'POST', [
    'action' => 'updateMonitor',
    'params' => [
        'id' => 'monitor_123',
        'name' => 'Updated Website Name',
        'interval' => 120
    ]
]);
```

### Get Alerts
```php
$alerts = apiRequest('/alerts');
foreach ($alerts['data'] as $alert) {
    echo "Alert: {$alert['message']}\n";
}
```

---

## JavaScript Examples

### Using Fetch API (Browser)
```javascript
const apiBase = 'https://mpsm.resolutionsbydesign.us/mps-api';

// Get all monitors
async function getMonitors() {
    const response = await fetch(`${apiBase}/monitors`);
    const data = await response.json();
    console.log('Monitors:', data);
    return data;
}

// Query endpoint method
async function queryAPI(action, params = {}) {
    const response = await fetch(`${apiBase}/query`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action, params })
    });
    return await response.json();
}

// Create monitor
async function createMonitor() {
    const result = await queryAPI('createMonitor', {
        name: 'My API Monitor',
        url: 'https://api.example.com',
        interval: 60
    });
    console.log('Created:', result);
}

// Get alerts
async function getAlerts() {
    const result = await queryAPI('getAlerts', {
        status: 'active'
    });
    console.log('Alerts:', result);
}
```

### Using Axios (Node.js)
```javascript
const axios = require('axios');

const apiBase = 'https://mpsm.resolutionsbydesign.us/mps-api';

// Create API client
const api = axios.create({
    baseURL: apiBase,
    headers: {
        'Content-Type': 'application/json'
    }
});

// Get monitors
async function getMonitors() {
    try {
        const response = await api.get('/monitors');
        console.log('Monitors:', response.data);
        return response.data;
    } catch (error) {
        console.error('Error:', error.response.data);
    }
}

// Create monitor using query endpoint
async function createMonitor(name, url) {
    try {
        const response = await api.post('/query', {
            action: 'createMonitor',
            params: { name, url, interval: 60 }
        });
        return response.data;
    } catch (error) {
        console.error('Error:', error.response.data);
    }
}

// Get statistics
async function getStatistics(monitorId, period = '24h') {
    try {
        const response = await api.post('/query', {
            action: 'getStatistics',
            params: { id: monitorId, period }
        });
        return response.data;
    } catch (error) {
        console.error('Error:', error.response.data);
    }
}

// Usage
(async () => {
    const monitors = await getMonitors();
    if (monitors.data.length > 0) {
        const stats = await getStatistics(monitors.data[0].id);
        console.log('Statistics:', stats);
    }
})();
```

---

## Python Examples

### Using Requests Library
```python
import requests
import json

API_BASE = 'https://mpsm.resolutionsbydesign.us/mps-api'

def api_request(endpoint, method='GET', data=None):
    """Make API request"""
    url = f"{API_BASE}{endpoint}"
    headers = {'Content-Type': 'application/json'}
    
    if method == 'GET':
        response = requests.get(url, headers=headers)
    elif method == 'POST':
        response = requests.post(url, headers=headers, json=data)
    
    return response.json()

def query_api(action, params=None):
    """Use unified query endpoint"""
    return api_request('/query', 'POST', {
        'action': action,
        'params': params or {}
    })

# Get all monitors
monitors = api_request('/monitors')
print(f"Found {len(monitors['data'])} monitors")

# Get specific monitor
monitor = query_api('getMonitor', {'id': 'monitor_123'})
if monitor['success']:
    print(f"Monitor: {monitor['data']['name']}")

# Create monitor
new_monitor = query_api('createMonitor', {
    'name': 'Python Test Monitor',
    'url': 'https://example.com',
    'interval': 60
})
print(f"Created: {new_monitor}")

# Get alerts
alerts = query_api('getAlerts', {'status': 'active'})
for alert in alerts.get('data', []):
    print(f"Alert: {alert}")

# Health check
health = api_request('/health')
print(f"API Status: {health['status']}")
```

### Class-Based Approach
```python
class MPSMonitorClient:
    def __init__(self, base_url):
        self.base_url = base_url
        self.session = requests.Session()
        self.session.headers.update({
            'Content-Type': 'application/json'
        })
    
    def query(self, action, params=None):
        """Unified query method"""
        response = self.session.post(
            f"{self.base_url}/query",
            json={'action': action, 'params': params or {}}
        )
        return response.json()
    
    def get_monitors(self, filters=None):
        return self.query('getMonitors', filters)
    
    def get_monitor(self, monitor_id):
        return self.query('getMonitor', {'id': monitor_id})
    
    def create_monitor(self, name, url, interval=60):
        return self.query('createMonitor', {
            'name': name,
            'url': url,
            'interval': interval
        })
    
    def get_alerts(self, filters=None):
        return self.query('getAlerts', filters)
    
    def health_check(self):
        response = self.session.get(f"{self.base_url}/health")
        return response.json()

# Usage
client = MPSMonitorClient('https://mpsm.resolutionsbydesign.us/mps-api')

# Check health
health = client.health_check()
print(f"Status: {health['status']}")

# Get monitors
monitors = client.get_monitors({'status': 'active'})
print(f"Active monitors: {len(monitors.get('data', []))}")

# Create monitor
result = client.create_monitor(
    name='Production API',
    url='https://api.production.com',
    interval=30
)
print(f"Created: {result}")
```

---

## cURL Examples

### Health Check
```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/health
```

### Get All Monitors
```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/monitors
```

### Get Specific Monitor
```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/monitors/monitor_123
```

### Create Monitor (Query Endpoint)
```bash
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/query \
  -H "Content-Type: application/json" \
  -d '{
    "action": "createMonitor",
    "params": {
      "name": "Test Monitor",
      "url": "https://example.com",
      "interval": 60
    }
  }'
```

### Update Monitor
```bash
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/query \
  -H "Content-Type: application/json" \
  -d '{
    "action": "updateMonitor",
    "params": {
      "id": "monitor_123",
      "name": "Updated Monitor Name"
    }
  }'
```

### Get Alerts
```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/alerts
```

### Get Statistics
```bash
curl "https://mpsm.resolutionsbydesign.us/mps-api/monitors/monitor_123/statistics?period=24h"
```

---

## ChatGPT Actions Setup

### Step 1: Import Swagger Definition
1. Go to ChatGPT Custom GPT settings
2. Click "Actions" → "Create new action"
3. Import from URL: `https://mpsm.resolutionsbydesign.us/mps-api/swagger.json`

Or paste the Swagger JSON directly from the swagger.json file.

### Step 2: Configure Authentication
Set authentication to "None" (handled by MPS API key in backend)

### Step 3: Example Prompts for ChatGPT
```
"Show me all my monitors"
"Create a monitor for https://example.com"
"What alerts do I have?"
"Get statistics for monitor_123"
"Is my API healthy?"
```

### Step 4: Action Schema (Quick Reference)
Primary endpoint: `/query`
Method: `POST`

Request body:
```json
{
  "action": "getMonitors|getMonitor|createMonitor|updateMonitor|deleteMonitor|getAlerts|getStatistics",
  "params": {}
}
```

---

## Error Handling Examples

### PHP
```php
$result = apiRequest('/monitors/invalid_id');
if (!$result['success']) {
    echo "Error: {$result['error']}\n";
    echo "HTTP Code: {$result['http_code']}\n";
}
```

### JavaScript
```javascript
try {
    const result = await queryAPI('getMonitor', { id: 'invalid' });
    if (!result.success) {
        console.error('Error:', result.error);
    }
} catch (error) {
    console.error('Network error:', error);
}
```

### Python
```python
result = query_api('getMonitor', {'id': 'invalid'})
if not result.get('success'):
    print(f"Error: {result.get('error')}")
    print(f"HTTP Code: {result.get('http_code')}")
```

---

## Testing Checklist

- [x] Health check returns status
- [x] List monitors endpoint works
- [x] Get single monitor works
- [x] Create monitor accepts data
- [x] Update monitor modifies data
- [x] Delete monitor removes entry
- [x] Alerts endpoint returns data
- [x] Statistics endpoint with period parameter
- [x] Query endpoint routes all actions
- [x] Error responses include proper codes
- [x] CORS headers allow cross-origin
- [x] Swagger JSON loads correctly

---

## Support

For issues or questions:
- Check `/endpoints` for available operations
- Review `/swagger.json` for full API spec
- Monitor logs at `/mps-api/logs/`

**Last Updated:** 2024
**Version:** 1.0.0
