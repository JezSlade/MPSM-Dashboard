# AI Sandbox Patch Validation Protocol - Hard Memory Reference

## CORE DIRECTIVE
For sandbox/POC patches: Prevent regression and ensure STRICT documentation adherence. Never make assumptions about endpoints, payloads, methods, or requirements.

## PRIMARY VALIDATION CATEGORIES

### 1. DOCUMENTATION COMPLIANCE - CRITICAL
**MANDATORY VERIFICATION:**
- Every endpoint specification followed EXACTLY as documented
- HTTP methods match documentation precisely (GET/POST/PUT/DELETE/PATCH)
- Request payload structure matches documented schema exactly
- Response payload structure matches documented schema exactly
- Parameter names, types, and constraints followed literally
- Error response codes match documentation exactly

**VALIDATION APPROACH:**
- Compare implementation against documentation word-for-word
- Verify parameter case sensitivity matches documentation
- Confirm required vs optional fields exactly as specified
- Validate data types match documentation (string vs integer vs boolean)
- Check constraint ranges and limits match documented values

**ZERO TOLERANCE RULE:**
- If documentation says "userId" parameter, code cannot use "user_id" or "UserID"
- If documentation specifies integer, code cannot accept string representations
- If documentation shows specific error messages, implementation must match exactly

### 2. REGRESSION PREVENTION - CRITICAL
**EXISTING FUNCTIONALITY:**
- All previously working features continue to work identically
- No changes to existing endpoint behaviors unless explicitly documented
- Previous test cases still pass with identical results
- Existing API contracts maintained exactly

**CHANGE ISOLATION:**
- New patch affects ONLY the intended functionality
- No side effects on unrelated system components
- No unintended modifications to existing data structures
- No changes to existing configuration or environment variables

### 3. FUNCTIONAL VERIFICATION - MODERATE
**CORE FUNCTIONALITY:**
- New feature works as documented
- Edge cases handled as specified in documentation
- Error conditions produce documented responses
- Input validation matches documented requirements exactly

**BASIC TESTING:**
- Happy path scenarios work correctly
- Error scenarios produce expected results
- Boundary conditions handled as documented

### 4. TECHNICAL IMPLEMENTATION - MODERATE
**CODE QUALITY:**
- Code follows existing project patterns
- Variable/function names consistent with codebase style
- No obvious logic errors or typos
- Comments explain non-obvious implementation choices

**BASIC PERFORMANCE:**
- No obvious infinite loops or blocking operations
- No memory leaks in simple test scenarios
- Response times reasonable for sandbox environment

## VALIDATION DECISION MATRIX

### CRITICAL FAILURES (MUST FIX)
- Any deviation from documented API specification
- Any regression in existing functionality
- Parameter names/types don't match documentation exactly
- HTTP methods differ from documentation
- Error responses don't match documented format

### MODERATE ISSUES (SHOULD FIX)
- Code style inconsistencies
- Missing error handling for edge cases
- Performance concerns in sandbox environment

### ACCEPTABLE (CAN PROCEED)
- Minor code formatting differences
- Non-critical comments or documentation gaps
- Sandbox-specific optimizations

## DOCUMENTATION VALIDATION CHECKLIST

**API ENDPOINT VERIFICATION:**
- [ ] HTTP method matches documentation exactly
- [ ] URL path matches documentation exactly
- [ ] Query parameters match documented names and types
- [ ] Request headers match documentation requirements
- [ ] Request body structure matches documented schema

**RESPONSE VALIDATION:**
- [ ] Response status codes match documentation
- [ ] Response headers match documentation
- [ ] Response body structure matches documented schema
- [ ] Error responses match documented format and messages
- [ ] Data types in response match documentation exactly

**PARAMETER VALIDATION:**
- [ ] Required parameters enforced as documented
- [ ] Optional parameters handled as documented
- [ ] Parameter validation rules match documentation
- [ ] Default values match documentation
- [ ] Constraint checking matches documented limits

## REGRESSION TESTING CHECKLIST

**EXISTING FUNCTIONALITY:**
- [ ] All previous API endpoints still work
- [ ] Previous test cases still pass
- [ ] No changes to unrelated code paths
- [ ] Existing data structures unchanged
- [ ] Configuration settings unmodified

**INTEGRATION STABILITY:**
- [ ] No breaking changes to internal interfaces
- [ ] No modifications to shared utilities or libraries
- [ ] Database schema changes are additive only
- [ ] No changes to existing file structures or naming

## VALIDATION COMMANDS FOR AI

**DOCUMENTATION COMPLIANCE:**
- "SPEC VERIFIED" - Implementation matches documentation exactly
- "SPEC VIOLATION" - Deviation from documented specification detected
- "ASSUMPTION DETECTED" - Implementation assumes undocumented behavior

**REGRESSION CHECKING:**
- "REGRESSION CLEAR" - No existing functionality affected
- "REGRESSION DETECTED" - Existing functionality broken or changed
- "ISOLATION CONFIRMED" - Changes contained to intended scope

**VALIDATION RESULTS:**
- "PATCH APPROVED" - No critical issues, ready for sandbox deployment
- "PATCH REJECTED" - Critical issues require immediate fixes
- "PATCH CONDITIONAL" - Minor issues noted but acceptable for POC

## STRICT ENFORCEMENT RULES

1. **NEVER ASSUME**: If documentation doesn't explicitly state something, ask for clarification
2. **LITERAL INTERPRETATION**: Follow documentation word-for-word, character-for-character
3. **REGRESSION PRIORITY**: Existing functionality takes precedence over new features
4. **DOCUMENTATION WINS**: When code conflicts with documentation, documentation is correct
5. **MINIMAL SCOPE**: Only implement exactly what's documented, nothing extra

## FAILURE RESPONSE TEMPLATE

When validation fails:
```
VALIDATION FAILED: [Category]
ISSUE: [Specific problem]
EXPECTED: [What documentation specifies]
ACTUAL: [What implementation does]
REQUIRED ACTION: [Specific fix needed]
```

## SUCCESS CRITERIA

Patch is ready when:
- Implementation matches documentation exactly
- No regression in existing functionality  
- All specified behaviors work correctly
- No assumptions made about undocumented features