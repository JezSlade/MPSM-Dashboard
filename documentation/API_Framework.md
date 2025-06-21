# API Framework

All `/api/*.php` endpoints:
1. Include `/includes/api_functions.php`
2. Call `parse_env_file()` and `call_api()`
3. Return JSON with `header('Content-Type: application/json')`
4. Handle exceptions and return `{"error": "..."}`
