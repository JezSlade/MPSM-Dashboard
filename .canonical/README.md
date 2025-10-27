# .canonical - Sacred and Holy Documents

This folder contains the **authoritative source of truth** for all MPS API integration.

## Purpose

The .canonical folder holds documents that are:
1. **Sacred** - Never modified without explicit approval
2. **Holy** - The single source of truth
3. **Immutable** - All other code references these documents

## Contents

### API Documentation (The Keys to All Our Data)

- **EndpointCatalog.php** - Complete catalog of all MPS API endpoints
- **MPS_API_Swagger.json** - Official Swagger/OpenAPI spec from MPS Monitor
- **MPS_Monitor_API_Endpoints.html** - Human-readable API reference
- **SDK_Examples_Verified_Working.md** - Battle-tested code examples

## Rules

1. NEVER modify files in this folder without understanding the full impact
2. ALWAYS reference .canonical when implementing new API calls
3. DO NOT duplicate endpoint definitions
4. Update .canonical first when API spec changes

---

**This folder holds the keys to all our data. Treat it with respect.**
