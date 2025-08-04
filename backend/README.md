
# 📘 API Backend Documentation

This document defines all core functions, classes, endpoints, and cache/database layers used in the modular MPS Monitor API backend.

---

## 📁 /core/

### `EnvLoader`
- **Method**: `load(): array`
- **Purpose**: Parses `.env` file from the parent directory into an associative array.

---

### `TokenManager`
- **Method**: `getToken(): string`
- **Purpose**: Retrieves a bearer token using OAuth2 (password grant).
- **Caches**: Token JSON in `/cache/token.json`.

---

### `CacheManager`
- **Methods**:
  - `get(string $key): mixed`
  - `put(string $key, $data, int $ttl = 300): void`
- **Purpose**: Generic file-based JSON cache.

---

### `ApiCaller`
- **Method**: `request(string $method, string $endpoint, array $payload = [], array $query = []): array`
- **Purpose**: Main HTTP client. Injects token, builds URL, and returns parsed JSON.
- **Dependencies**: TokenManager, EnvLoader

---

### `DataFormatter`
- **Method**: `format(array $raw): array`
- **Purpose**: Flattens API responses for CMS display.

---

## 📁 /cron/

### `SyncData.php`
- **Function**: Loops through all curation targets in `CurationMap.php`, invokes `ApiCaller`, formats result, and caches to:
  - `/cache/data/{cache_key}.json`
  - `/db/curated.db` (future insert logic)

---

## 📁 /includes/

### `CurationMap.php`
- **Format**:
```php
return [
  'API/Endpoint' => [
    'method' => 'GET|POST',
    'query' => [array of query params],
    'payload' => [POST body],
    'cache_key' => 'file_name_or_db_key',
    'formatter' => 'DataFormatter::format'
  ]
];
```
- **Purpose**: Centralized control of what to sync and how to format it.

---

## 📁 /api/

### `{Group}/{Endpoint}.php`
- **Example**: `/api/Account/GetProfile.php`
- **Purpose**: Automatically generated wrappers to make raw API calls via `ApiCaller`.

---

### `/api/admin/query.php`
- **Inputs**:
  - `?source=account_list`
  - `?q=search string`
  - `?limit=50`
- **Purpose**: Queries curated SQLite dataset.

---

## 📁 /db/

### `curated.db`
- SQLite DB with full schema:
```sql
CREATE TABLE curated_data (
  id TEXT PRIMARY KEY,
  source TEXT,
  payload TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```
- **Use**: Fast filtering/search for large CMS tables.

---

## 📁 /cache/

- `token.json`: Access token (auto-refreshed)
- `data/{key}.json`: Formatted cache results from sync

---

## Integration Notes

- To add a new API sync target:
  1. Add entry to `CurationMap.php`
  2. Add formatter if needed
  3. Run `/cron/SyncData.php`
  4. Access formatted results in `/cache/data/` or via `query.php`

- All API output is JSON-ready and OpenAI Actions-compatible.

---

## Maintainer Notes

- Every function lives in its own file
- Every dependency is local (no Composer)
- All logic follows strict OOP and OpenAPI guidelines
