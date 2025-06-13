### `MPSM_Bible.md`

```markdown
# MPSM Bible: Detailed Facts & Advice

1.  General Architecture & Requirements

1.1 Portal & API Separation
- The MPS Monitor Web Portal is a separate frontend, accessible via HTTPS and user accounts.
- The MPSM API is a back-end service exposing JSON endpoints under [https://api.abassetmanagement.com/api3/](https://api.abassetmanagement.com/api3/) (Swagger.json).
1.2 General Preconditions
To use the MPSM API you must have:
- A valid user account on the portal.
- A .env file defining CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD, SCOPE, TOKEN_URL, BASE_URL, DEALER_CODE, DEALER_ID, DEVICE_PAGE_SIZE.
- A DCA Connector installed on the customer’s network to feed real-time device data.
1.3 DCA Connector
- The DCA Connector (eXplorer) runs on a LAN machine, polling printers via SNMP v1/v2 (“Public” community by default).
- Ensure SNMP v3 is disabled for compatibility.
1.4 .env Configuration
The .env file must contain:
CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD, SCOPE, TOKEN_URL, BASE_URL, DEALER_CODE, DEALER_ID, DEVICE_PAGE_SIZE.
- BASE_URL example: [https://api.abassetmanagement.com/api3](https://api.abassetmanagement.com/api3)
- DEALER_CODE example: NY06AGDWUQ
- DEALER_ID example: SZ13qRwU5GtFLj0i_CbEgQ2

2.  Authentication (OAuth 2.0 Password Grant)

2.1 Token Endpoint
- POST [https://api.abassetmanagement.com/api3/token](https://api.abassetmanagement.com/api3/token)
- Content-Type: application/x-www-form-urlencoded
- Body: grant_type=password, client_id, client_secret, username, password, scope.
2.2 Required Headers
- Use Authorization: Bearer <access_token> and Accept: application/json.
- For JSON payloads, include Content-Type: application/json.
3.  Base URL & Endpoint Patterns

3.1 Base URL
- All calls use BASE_URL from .env (e.g., [https://api.abassetmanagement.com/api3](https://api.abassetmanagement.com/api3)).
3.2 Endpoint Categories
- Account: /Account/GetProfile (GET), /Account/Logout (POST).
- Customer: /Customer/GetCustomers (POST).
- Device: /Device/List, /Device/GetDetailedInformations.
- Alerts/Supply/Job/Log/Notification: /SupplyAlert/List, /Device/JobHistory/List, /Device/SupplyHistory/List, etc.
- Explorer: /Explorer/Hostname/Update, /Explorer/WorkingDays/Update, /Explorer/Intervals/Update.
- Reports/Exports: /SupplyAlert/Export, etc.
- ApiClient: /ApiClient/List, /ApiClient/Create, etc..
3.3 Response Format
- All endpoints return JSON.
- Common wrappers: SingleResultResponse[T], ListResultResponse[T], PagedResultResponse[T].

3.4 Content Types
- Request JSON: application/json.
- Response JSON: application/json.
- Export responses: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet or application/vnd.ms-excel.
4.  Pagination, Filtering, and Sorting

4.1 PageSize & PageNumber
- Use PageRows and PageNumber parameters. Inspect TotalRows for number of pages.
4.2 SortColumn & SortOrder
- SortColumn is a field name (e.g., Id, DateTime). SortOrder is 0/1 or “Asc”/“Desc”.
4.3 Filtering
- Many endpoints accept a free-text filter (FilterText). Others accept typed filters (arrays, date ranges).
5.  Error Handling & HTTP Status Codes

5.1 200 OK with Error Payload
- Even on 200, JSON might contain “Errors” array.
- Check for non-empty “Errors”.

5.2 Non-200 Status Codes
- Handle 400, 401, 403, 404, 500. Use proper HTTP library or inspect http_response_header after file_get_contents().
6.  Data Models & Key Fields

6.1 CustomerDto
- Fields: Code, Description, Country, City, Zip, etc.

6.2 DeviceDto
- Fields: Id (GUID), SerialNumber, AssetNumber, Model, Brand, Status, OfficeId, etc.

6.3 SupplyAlertDto
- Fields: SerialNumber, ProductModel, Warning, InitialDate, ActualDate, etc.

6.4 CounterDto
- Fields: CounterType, Count, Date.
7.  Best Practices & Developer Advice

7.1 Token Reuse & Caching
- Cache tokens until near expiration.
- Use “expires_in” to determine when to refresh.

7.2 Retry Logic
- On transient errors (timeouts, 5xx, 429), retry with exponential backoff.
7.3 Logging & Debugging
- Log request URLs, headers (without Authorization), payloads (omit passwords), and timestamps.
- Use debugging flag to show raw JSON errors.

7.4 Security
- Do not commit .env file to source control.
- Store secrets in environment variables.

7.5 Pagination Best Practices
- Loop pages until you retrieve TotalRows.
- Avoid huge PageRows to prevent memory overload.

7.6 Date Handling
- Convert dates to local time if needed.
- Use GMT functions in code.

8.  Common Troubleshooting Scenarios

8.1 “Failed to get access token.”
- Check TOKEN_URL, CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD.

8.2 403 Forbidden
- Account lacks permission. Ensure correct role and permissions in portal.
8.3 404 Not Found
- Check path casing and spelling. Use exact endpoint paths.
8.4 Invalid JSON Response
- Likely hitting export endpoint. Use List endpoint for JSON.
9.  Swagger.json Reference

- Contains Swagger 2.0 definitions for every endpoint. Use “definitions” section for request/response schemas.
- Paths section details HTTP methods, parameters, and response schemas.
- Consult Swagger.json for up-to-date endpoint information.
10. Summary of Key Field Names

- DealerCode: string (e.g., “NY06AGDWUQ”).
- DealerId: GUID string.
- CustomerCode: string.
- DeviceId: GUID string.
- PageRows / PageNumber / SortColumn / SortOrder.
- FromDate / ToDate: UTC ISO-8601 strings.
- Result: container for response data.

---
References:
- Swagger.json (Swagger 2.0) – endpoint definitions and schemas.
- User Guide PDF – architectural overview, DCA Connector requirements, portal-specific behavior.