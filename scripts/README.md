# API Discovery Scripts

Systematic API endpoint discovery and payload calibration system for the MPSM Dashboard API.

## Overview

This system automatically discovers working request payloads and response shapes for every endpoint defined in `Swagger.json`, verifies authentication, and generates a definitive reference for implementing the API engine.

## Quick Start

### Prerequisites

- Python 3.7+
- Valid `.env` file with API credentials in project root
- `Swagger.json` in project root

### Run Full Discovery

```bash
# From the scripts directory
python run_discovery.py
```

This will:
1. Parse `Swagger.json` and discover all endpoints
2. Probe each endpoint to find working payloads
3. Generate reference files for API implementation

## Portable Operations

These scripts are cross-platform and do not require PowerShell:

```bash
python3 scripts/run_checks.py
python3 scripts/ftp_backup.py
python3 scripts/ftp_deploy.py --delete
python3 scripts/live_smoke.py
```

`ftp_backup.py` backs up the live FTP tree before deployment. It skips files that vanish between the FTP directory listing and download, which can happen with volatile server log files.

FTP scripts read credentials from environment variables or `.runtime/ftp.env`:

```text
MPSM_FTP_HOST=ftp.resolutionsbydesign.us
MPSM_FTP_ROOT=/
MPSM_FTP_USER=<FTP_USER>
MPSM_FTP_PASSWORD=<FTP_PASSWORD>
```

### Run Limited Discovery (Testing)

```bash
# Test with just 10 endpoints
python run_discovery.py --limit 10
```

### Skip Phases

```bash
# Skip discovery, just rebuild references
python run_discovery.py --skip-probe

# Skip discovery and probing, just build references
python run_discovery.py --skip-discover --skip-probe
```

## Scripts

### `discover_endpoints.py`

Parses `Swagger.json` and creates a normalized endpoint matrix.

**Output:**
- `output/endpoints.json` - All endpoints with metadata
- `output/endpoints_by_tag.json` - Endpoints grouped by tag

**Run directly:**
```bash
python discover_endpoints.py
```

### `probe_endpoint.py`

Probes each endpoint to discover working payloads.

**Features:**
- Automatic OAuth token management
- Exponential backoff with jitter on rate limits
- Domain seed discovery (Dealers, Customers, Locations)
- Safe-write detection (skips destructive operations)
- Detailed NDJSON logging

**Output:**
- `output/probe_results.json` - Probe results
- `output/domain_seeds.json` - Discovered IDs/codes
- `logs/run.ndjson` - Detailed probe logs

**Run directly:**
```bash
# Probe all endpoints
python probe_endpoint.py

# Probe with custom settings
python probe_endpoint.py --max-attempts 20 --limit 50
```

**Options:**
- `--input FILE` - Input endpoints file (default: output/endpoints.json)
- `--max-attempts N` - Max attempts per endpoint (default: 12)
- `--limit N` - Limit number of endpoints to probe

### `build_reference.py`

Builds reference files from probe results.

**Output:**
- `output/endpoint_reference.yaml` - Canonical endpoint reference
- `output/curl_recipes.md` - Copy-paste cURL commands
- `output/samples/` - Sample request/response files
- `output/coverage_report.md` - Coverage statistics

**Run directly:**
```bash
python build_reference.py
```

### `run_discovery.py`

Main runner that executes the full workflow.

**Run:**
```bash
python run_discovery.py [options]
```

**Options:**
- `--limit N` - Limit endpoints (for testing)
- `--max-attempts N` - Max attempts per endpoint
- `--skip-discover` - Skip endpoint discovery
- `--skip-probe` - Skip probing phase

## Utilities

### `utils/env_loader.py`

Loads `.env` configuration without logging secrets.

### `utils/http_client.py`

HTTP client with:
- OAuth token management (auto-refresh)
- Retry logic with exponential backoff
- Rate limit handling (429, 503)
- Configurable timeouts

### `utils/payload_gen.py`

Generates minimal valid payloads from OpenAPI schemas:
- Handles required parameters only
- Uses domain seeds for IDs/codes
- Enum value enumeration
- Type-aware value generation

### `utils/validator.py`

JSON Schema validator for response validation.

### `utils/redact.py`

Redacts secrets and PII from:
- URLs (credentials, sensitive query params)
- Headers (Authorization, API keys)
- Response bodies (tokens, IDs, emails)

## Output Files

### `endpoint_reference.yaml`

Canonical reference for each endpoint:

```yaml
- path: /v1/devices
  method: GET
  auth:
    scheme: bearer
    header: Authorization
  params:
    query:
      page: 1
      pageSize: 50
  success:
    status: 200
    latency_ms: 245
    pagination:
      type: cursor
      next_key: nextPageToken
```

### `curl_recipes.md`

Ready-to-use cURL commands:

```bash
curl -H "Authorization: Bearer <REDACTED>" \
  "https://api.example.com/v1/devices?page=1&pageSize=50"
```

### `domain_seeds.json`

Discovered domain data for use in requests:

```json
{
  "dealers": [
    {"id": "...", "code": "...", "name": "..."}
  ],
  "customers": [
    {"id": "...", "code": "...", "dealerId": "..."}
  ]
}
```

### `coverage_report.md`

Statistics and breakdown:
- Total endpoints
- Discovered (working)
- Skipped (write operations without safe mode)
- Errors (failed to discover)

### `run.ndjson`

Line-delimited JSON logs of every probe:

```json
{"ts": 1234567890, "method": "GET", "url": "...", "status": 200, "latency_ms": 123, "outcome": "success"}
```

## Safety Features

### Write Protection

Destructive operations (POST, PUT, PATCH, DELETE) are automatically skipped unless they provide a documented safe mode (e.g., `dry_run=true` parameter).

Skipped endpoints are marked with `skip_reason: write_prohibited`.

### Secret Redaction

All secrets are redacted before saving to files:
- OAuth tokens
- API keys
- Passwords
- Customer IDs (partial)
- Email addresses (partial)

### Rate Limiting

Generic exponential backoff on 429/503 responses:
- Base delay: 2^attempt seconds (max 60s)
- Jitter: 0-50% of base delay
- Max retries: 3 (configurable)

## Configuration

### Environment Variables

Required in `.env`:

```env
# API Configuration
MPS_BASE_URL="https://api.example.com/api3/"
TOKEN_URL="https://api.example.com/api3/token"

# OAuth Credentials
CLIENT_ID="..."
CLIENT_SECRET="..."
USERNAME="..."
PASSWORD="..."
SCOPE="account"

# Dealer Information
DEALER_CODE="..."
DEALER_ID="..."

# Timeouts
MPS_TIMEOUT=30
MPS_CONNECT_TIMEOUT=10
MPS_MAX_RETRIES=3
```

### Discovery Settings

Edit in scripts:
- `MAX_ATTEMPTS = 12` - Max attempts per endpoint
- `connect_timeout = 10` - Connection timeout (seconds)
- `read_timeout = 60` - Read timeout (seconds)

## Domain Seed Discovery

The system automatically discovers domain data needed for dependent endpoints:

1. **Phase 1**: Probe lookup endpoints first (priority 1)
   - Endpoints with "dealer", "customer", "location" in path
   - GET methods only
   - No path parameters

2. **Phase 2**: Extract IDs and codes from responses
   - Save to `domain_seeds.json`
   - Update payload generator

3. **Phase 3**: Use seeds in subsequent requests
   - Auto-populate `dealerId`, `customerCode`, etc.
   - Reduce errors from missing required IDs

## Troubleshooting

### No endpoints discovered

Check:
- `Swagger.json` exists in project root
- File is valid JSON
- File has `paths` section

### Authentication failures

Check:
- `.env` has valid credentials
- `TOKEN_URL` is correct
- Network can reach auth server

### All probes failing

Check:
- `MPS_BASE_URL` is correct
- API is accessible
- Firewall allows outbound HTTPS

### Low discovery rate

Try:
- Increase `--max-attempts`
- Check `logs/run.ndjson` for error patterns
- Verify domain seeds are being discovered

## Development

### Adding New Utilities

Add modules to `utils/` and import in scripts:

```python
from utils.my_module import my_function
```

### Customizing Payload Generation

Edit `utils/payload_gen.py`:
- Add context-aware defaults
- Add new domain seed types
- Customize type generation

### Customizing Probe Logic

Edit `probe_endpoint.py`:
- Adjust retry logic
- Add error-based payload adjustment
- Add new skip conditions

## Quality Gates

Before using reference files, verify:
- [ ] No unredacted secrets in outputs
- [ ] Coverage >= 80% (check `coverage_report.md`)
- [ ] Domain seeds discovered (check `domain_seeds.json`)
- [ ] No schema validation failures
- [ ] All skips justified with `skip_reason`

## Next Steps

After discovery:

1. Review `coverage_report.md` for completeness
2. Examine failed endpoints in `probe_results.json`
3. Use `endpoint_reference.yaml` to implement API engine
4. Reference `curl_recipes.md` for manual testing
5. Use `samples/` for request/response examples

## License

Part of MPSM Dashboard project.
