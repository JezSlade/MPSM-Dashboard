# MPS API Engine Fix - Executive Summary

## 🎯 Mission
Fix the `mps-api/` engine using today's discovery learnings to enable a Custom GPT Action for querying MPSM data.

---

## 🔍 Key Discoveries from API Discovery

### 1. **Authentication Crisis** ⚠️
- **Discovery**: ALL endpoints require OAuth, despite Swagger showing no security requirements
- **Current Engine**: Likely not sending auth headers on all requests
- **Fix**: Default `requires_auth = true` for all endpoints

### 2. **Payload Generation Broken** ⚠️
- **Discovery**: Parameters generating "null" strings instead of actual dealer codes
- **Current Engine**: Not auto-populating dealer/customer codes
- **Fix**: Auto-inject `DEALER_CODE` when parameter is `code`, `DEALER_ID` when parameter is `dealerId`

### 3. **Response Validation Missing** ⚠️
- **Discovery**: API returns HTTP 200 even for errors! Must check `IsValid` field
- **Response Format**: `{"Result": {...}, "IsValid": true/false, "Errors": [...]}`
- **Fix**: Parse response, check `IsValid`, extract `Errors` array

---

## 📋 Implementation Phases (in priority order)

### **Phase 1: Authentication** (Week 1 - CRITICAL)
✅ Force OAuth on ALL endpoints
✅ Ensure `Authorization: Bearer {token}` header on every request
✅ Auto-refresh tokens 5 minutes before expiry
✅ Cache tokens properly

**Files**: `engine.php`, `SwaggerActionRegistry.php`

---

### **Phase 2: Smart Parameters** (Week 1 - CRITICAL)
✅ Auto-populate `code` with `DEALER_CODE` from config
✅ Auto-populate `dealerId` with `DEALER_ID`
✅ Add defaults for pagination (`page=1`, `pageSize=50`)
✅ Validate required parameters before sending

**Files**: `engine.php` (new method `getDefaultParameterValue()`)

---

### **Phase 3: Response Validation** (Week 1 - CRITICAL)
✅ Check `IsValid` field in all responses
✅ Extract and format `Errors` array
✅ Return structured errors to GPT
✅ Differentiate HTTP errors from API errors

**Files**: `engine.php` (new method `validateMPSMResponse()`)

---

### **Phase 4: OpenAPI for GPT** (Week 2 - HIGH)
✅ Create simplified OpenAPI 3.0 spec (`openapi.yaml`)
✅ Define endpoints GPT will use:
  - `POST /action` - Execute any MPSM action
  - `GET /actions` - List available actions
  - `GET /health` - Health check
✅ Add authentication (API key for engine, not MPSM)

**Files**: `openapi.yaml`, `index.php`

---

### **Phase 5: Deployment** (Week 2 - HIGH)
✅ Configure subdomain: `mps-api.yourdomain.com`
✅ Setup SSL certificate
✅ Configure CORS for `chat.openai.com`
✅ Create `.env.example` with all required variables
✅ Write deployment documentation

**Files**: `DEPLOYMENT.md`, `SUBDOMAIN_SETUP.md`

---

### **Phase 6: Custom GPT Setup** (Week 2 - HIGH)
✅ Import OpenAPI spec into Custom GPT
✅ Configure API key authentication
✅ Write GPT instructions
✅ Test with common actions:
  - `Dealer/Get` (get dealer info)
  - `Device/List` (list devices)
  - `Customer/List` (list customers)
  - `Counter/Device/List` (get counters)

**Files**: `GPT_INSTRUCTIONS.md`

---

### **Phase 7: Import Discovery Results** (Week 3 - MEDIUM)
⏸️ Copy discovered endpoints to `mps-api/discovered_endpoints/`
⏸️ Create endpoint validator class
⏸️ Use discovered payloads for validation
⏸️ Add "known good" parameter examples

**Files**: `EndpointValidator.php`, `discovered_endpoints/`

---

### **Phase 8: Testing** (Week 3-4 - MEDIUM)
⏸️ Create test suite
⏸️ Create Postman collection
⏸️ Test all phases
⏸️ Load testing

**Files**: `tests/`

---

## 🚀 Quick Start Guide

### Week 1 - Get It Working
```bash
# Day 1-2: Authentication
1. Edit SwaggerActionRegistry.php - Set requires_auth = true
2. Edit engine.php - Ensure auth header on ALL requests
3. Test: curl engine with Dealer/Get action

# Day 3-4: Smart Parameters
4. Add getDefaultParameterValue() method
5. Update dispatchAction() to use defaults
6. Test: Call Dealer/Get without sending code param

# Day 5: Response Validation
7. Add validateMPSMResponse() method
8. Update executeRequest() to use validator
9. Test: Trigger error, verify Errors array is extracted
```

### Week 2 - Deploy to GPT
```bash
# Day 1-2: OpenAPI Spec
1. Create openapi.yaml with 3 endpoints
2. Add /actions route to index.php
3. Test locally

# Day 3-4: Deploy
4. Setup subdomain
5. Configure SSL
6. Test health endpoint

# Day 5: GPT Setup
7. Create Custom GPT
8. Import openapi.yaml
9. Test 5 common actions
10. 🎉 SUCCESS!
```

---

## ✅ Success Criteria (MVP)

**Week 1 Goals**:
- [ ] OAuth working on all requests (test with curl)
- [ ] Dealer code auto-populated (test Dealer/Get with no params)
- [ ] Errors extracted properly (test invalid request)

**Week 2 Goals**:
- [ ] Engine deployed on subdomain with SSL
- [ ] OpenAPI spec created and tested
- [ ] Custom GPT successfully executes 5 actions:
  1. Dealer/Get
  2. Device/List
  3. Customer/List
  4. Counter/Device/List
  5. DealerProduct/List

**Week 3-4 Goals** (stretch):
- [ ] 50+ endpoints tested and working
- [ ] Comprehensive test suite
- [ ] Full documentation

---

## 📊 Current Discovery Progress

From the running discovery (200/544 endpoints tested so far):

**Success Rate**: ~95% (189 discovered, 11 failed)

**Top Working Endpoints**:
- Dealer/* (all working)
- Device/* (all working)
- Customer/* (all working)
- AlertLimit/* (all working)
- Explorer/* (most working)

**Known Issues**:
- Some endpoints need specific IDs (Orders, Saga, etc.)
- A few require special permissions
- Overall: Vast majority work with correct auth + dealer code

---

## 🎯 End Goal

```
┌─────────────────────────────────────────────┐
│           Custom GPT Interface               │
│  "Show me all devices for dealer NY06"      │
└─────────────────┬───────────────────────────┘
                  │
                  │ HTTPS (OpenAPI 3.0)
                  ▼
┌─────────────────────────────────────────────┐
│      mps-api.yourdomain.com (Engine)        │
│  - Receives: action + params                │
│  - Adds: OAuth token, dealer code           │
│  - Validates: IsValid field                 │
│  - Returns: Clean JSON for GPT              │
└─────────────────┬───────────────────────────┘
                  │
                  │ OAuth Bearer + Dealer Code
                  ▼
┌─────────────────────────────────────────────┐
│    api.abassetmanagement.com (MPSM API)     │
│  - Returns: {"Result": {...}, "IsValid": ...}│
└─────────────────────────────────────────────┘
```

---

## 📁 Files Changed/Created

### Critical Changes (Week 1)
- `mps-api/engine.php` - 3 new methods, 2 modified methods
- `mps-api/SwaggerActionRegistry.php` - 1 line change (huge impact!)

### New Files (Week 2)
- `mps-api/openapi.yaml` - GPT interface spec
- `mps-api/DEPLOYMENT.md` - Deployment guide
- `mps-api/GPT_INSTRUCTIONS.md` - GPT system prompt

### Future Files (Week 3-4)
- `mps-api/EndpointValidator.php` - Validation class
- `mps-api/tests/*` - Test suite
- `mps-api/discovered_endpoints/*` - Discovery results

---

## 💡 Key Insights

1. **Don't Trust the Swagger** - Missing security requirements cost us hours
2. **API Lies with 200s** - Must validate `IsValid` field, not HTTP status
3. **Auto-Populate Everything** - Dealer codes, pagination, defaults = better UX
4. **Token Management is Critical** - Refresh proactively, never mid-request
5. **GPT Needs Simple Interface** - One endpoint (`/action`) beats 544 endpoints

---

**Ready to implement?** Start with [MPS_API_ENGINE_FIX_PLAN.md](MPS_API_ENGINE_FIX_PLAN.md) for detailed instructions.

**Questions?** The full discovery is running now and will complete in ~1 hour with comprehensive results.
