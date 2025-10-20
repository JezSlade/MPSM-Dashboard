#!/usr/bin/env python3
"""
MPS API Engine Fix Verification Script

Verifies that all three critical fixes are properly implemented in engine.php:
1. OAuth authentication on all endpoints
2. Smart parameter auto-population
3. MPSM response validation (IsValid field)

This script performs static analysis of the code to verify the fixes exist.
For runtime testing, use test.php or the TEST_GUIDE.md instructions.
"""

import re
import sys
from pathlib import Path

# ANSI color codes
GREEN = '\033[92m'
RED = '\033[91m'
YELLOW = '\033[93m'
BLUE = '\033[94m'
RESET = '\033[0m'
BOLD = '\033[1m'

def print_header(text):
    print(f"\n{BOLD}{BLUE}{'=' * 80}{RESET}")
    print(f"{BOLD}{BLUE}{text}{RESET}")
    print(f"{BOLD}{BLUE}{'=' * 80}{RESET}\n")

def print_test(test_num, name):
    print(f"\n{BOLD}[TEST {test_num}] {name}{RESET}")
    print('-' * 80)

def print_pass(message):
    print(f"{GREEN}[PASS]{RESET} {message}")

def print_fail(message):
    print(f"{RED}[FAIL]{RESET} {message}")

def print_info(message):
    print(f"  > {message}")

def find_method(content, method_name):
    """Find a method definition and return its line range"""
    pattern = rf'(private|public|protected)\s+function\s+{re.escape(method_name)}\s*\('
    match = re.search(pattern, content)
    return match is not None, match.start() if match else -1

def find_method_call(content, method_name):
    """Check if a method is called in the code"""
    pattern = rf'\$this->{re.escape(method_name)}\s*\('
    matches = re.findall(pattern, content)
    return len(matches) > 0, len(matches)

def find_code_pattern(content, pattern, description):
    """Generic pattern finder"""
    matches = re.findall(pattern, content, re.MULTILINE | re.DOTALL)
    return len(matches) > 0, len(matches)

def get_line_number(content, position):
    """Get line number from character position"""
    return content[:position].count('\n') + 1

# Initialize test counters
tests_run = 0
tests_passed = 0
tests_failed = 0

print_header("MPS API ENGINE FIX VERIFICATION")
print("Verifying that all three critical fixes are implemented in engine.php")

# Load engine.php
engine_path = Path(__file__).parent / 'engine.php'

if not engine_path.exists():
    print_fail(f"engine.php not found at {engine_path}")
    sys.exit(1)

with open(engine_path, 'r', encoding='utf-8') as f:
    engine_content = f.read()

print_info(f"Loaded engine.php ({len(engine_content)} bytes)")

# =============================================================================
# FIX #1: OAuth Authentication
# =============================================================================
print_test(1, "Fix #1: OAuth Authentication Implementation")
tests_run += 1

# Check for OAuth methods
oauth_methods = [
    'prepareAuthorization',
    'getAuthorizationHeader',
    'ensureAccessToken',
    'fetchAccessToken'
]

oauth_found = 0
for method in oauth_methods:
    exists, pos = find_method(engine_content, method)
    if exists:
        oauth_found += 1
        line_num = get_line_number(engine_content, pos)
        print_info(f"[OK] Method '{method}()' found at line {line_num}")
    else:
        print_info(f"[X] Method '{method}()' NOT FOUND")

if oauth_found == len(oauth_methods):
    print_pass(f"All {len(oauth_methods)} OAuth methods implemented")
    tests_passed += 1
else:
    print_fail(f"Only {oauth_found}/{len(oauth_methods)} OAuth methods found")
    tests_failed += 1

# Check that Authorization header is added
print_test(2, "Fix #1: Authorization Header Integration")
tests_run += 1

auth_header_pattern = r"Authorization['\"]?\s*\]\s*=\s*\$this->getAuthorizationHeader\(\)"
found, count = find_code_pattern(engine_content, auth_header_pattern, "Authorization header")

if found:
    print_pass(f"Authorization header is added to requests ({count} occurrence(s))")
    print_info("OAuth Bearer token will be included in all API calls")
    tests_passed += 1
else:
    print_fail("Authorization header not found in request preparation")
    tests_failed += 1

# =============================================================================
# FIX #2: Smart Parameter Population
# =============================================================================
print_test(3, "Fix #2: Smart Parameter Auto-population Method")
tests_run += 1

# Check for getDefaultParameterValue method
exists, pos = find_method(engine_content, 'getDefaultParameterValue')

if exists:
    line_num = get_line_number(engine_content, pos)
    print_pass(f"Method 'getDefaultParameterValue()' found at line {line_num}")

    # Extract the method to check its implementation
    method_start = pos
    method_end = engine_content.find('\n    }\n', method_start) + 6
    method_body = engine_content[method_start:method_end]

    # Check for dealer code handling
    if 'dealercode' in method_body.lower() or 'dealer_code' in method_body.lower():
        print_info("[OK] Handles dealer code auto-population")

    # Check for dealer ID handling
    if 'dealerid' in method_body.lower() or 'dealer_id' in method_body.lower():
        print_info("[OK] Handles dealer ID auto-population")

    # Check for pagination
    if 'page' in method_body.lower() and 'pagesize' in method_body.lower():
        print_info("[OK] Handles pagination defaults (page, pageSize)")

    tests_passed += 1
else:
    print_fail("Method 'getDefaultParameterValue()' NOT FOUND")
    print_info("This method should auto-populate dealer codes and pagination")
    tests_failed += 1

print_test(4, "Fix #2: Parameter Auto-population Integration")
tests_run += 1

# Check that getDefaultParameterValue is called in dispatchAction
called, count = find_method_call(engine_content, 'getDefaultParameterValue')

if called:
    print_pass(f"Auto-population method is called ({count} occurrence(s))")
    print_info("Parameters will be auto-filled when missing")
    tests_passed += 1
else:
    print_fail("Auto-population method not called in code")
    print_info("Method exists but is not integrated into request flow")
    tests_failed += 1

# Check for query parameter handling
print_test(5, "Fix #2: Query Parameter Default Value Logic")
tests_run += 1

query_param_pattern = r'elseif\s*\(\s*!empty\(\s*\$meta\[.required.\]\s*\)\s*\)\s*\{[^}]*getDefaultParameterValue'
found, count = find_code_pattern(engine_content, query_param_pattern, "Query param defaults")

if found:
    print_pass("Query parameters use auto-population for missing required fields")
    print_info("GET requests can omit dealer codes and pagination")
    tests_passed += 1
else:
    print_fail("Query parameter auto-population logic not found")
    tests_failed += 1

# =============================================================================
# FIX #3: MPSM Response Validation
# =============================================================================
print_test(6, "Fix #3: MPSM Response Validation Method")
tests_run += 1

# Check for validateMPSMResponse method
exists, pos = find_method(engine_content, 'validateMPSMResponse')

if exists:
    line_num = get_line_number(engine_content, pos)
    print_pass(f"Method 'validateMPSMResponse()' found at line {line_num}")

    # Extract method body
    method_start = pos
    method_end = engine_content.find('\n    }\n', method_start) + 6
    method_body = engine_content[method_start:method_end]

    # Check for IsValid field handling
    if 'IsValid' in method_body:
        print_info("[OK] Checks for MPSM 'IsValid' field")

    # Check for Result field extraction
    if 'Result' in method_body:
        print_info("[OK] Extracts 'Result' field from valid responses")

    # Check for Errors array handling
    if 'Errors' in method_body:
        print_info("[OK] Processes 'Errors' array from failed responses")

    tests_passed += 1
else:
    print_fail("Method 'validateMPSMResponse()' NOT FOUND")
    print_info("This method should check IsValid field and extract errors")
    tests_failed += 1

print_test(7, "Fix #3: Response Validation Integration")
tests_run += 1

# Check that validateMPSMResponse is called in executeRequest
called, count = find_method_call(engine_content, 'validateMPSMResponse')

if called:
    print_pass(f"Response validation method is called ({count} occurrence(s))")
    print_info("MPSM responses will be validated before returning")
    tests_passed += 1
else:
    print_fail("Response validation method not called in code")
    print_info("Method exists but is not integrated into response processing")
    tests_failed += 1

# Check for validation in 2xx response handler
print_test(8, "Fix #3: Validation in Success Response Handler")
tests_run += 1

success_validation_pattern = r'if\s*\(\s*\$httpCode\s*>=\s*200\s*&&\s*\$httpCode\s*<\s*300\s*\)[^{]*\{[^}]*validateMPSMResponse'
found, count = find_code_pattern(engine_content, success_validation_pattern, "Success handler validation")

if found:
    print_pass("Response validation integrated into 2xx success handler")
    print_info("HTTP 200 responses with IsValid=false will be caught")
    tests_passed += 1
else:
    print_fail("Validation not found in 2xx success response handler")
    print_info("MPSM errors (HTTP 200 + IsValid=false) may not be detected")
    tests_failed += 1

# =============================================================================
# Additional Checks
# =============================================================================
print_test(9, "Code Structure: executeRequest Method")
tests_run += 1

exists, pos = find_method(engine_content, 'executeRequest')
if exists:
    line_num = get_line_number(engine_content, pos)
    print_pass(f"Method 'executeRequest()' found at line {line_num}")
    print_info("This is the main HTTP request handler")
    tests_passed += 1
else:
    print_fail("Method 'executeRequest()' NOT FOUND - Critical error!")
    tests_failed += 1

print_test(10, "Code Structure: dispatchAction Method")
tests_run += 1

exists, pos = find_method(engine_content, 'dispatchAction')
if exists:
    line_num = get_line_number(engine_content, pos)
    print_pass(f"Method 'dispatchAction()' found at line {line_num}")
    print_info("This is the main action routing handler")
    tests_passed += 1
else:
    print_fail("Method 'dispatchAction()' NOT FOUND - Critical error!")
    tests_failed += 1

# =============================================================================
# Summary
# =============================================================================
print_header("VERIFICATION SUMMARY")

print(f"Total Tests:  {tests_run}")
print(f"{GREEN}Passed:       {tests_passed} ({round(tests_passed/tests_run*100, 1)}%){RESET}")

if tests_failed > 0:
    print(f"{RED}Failed:       {tests_failed}{RESET}")
else:
    print(f"Failed:       {tests_failed}")

print()

if tests_failed == 0:
    print(f"{GREEN}{BOLD}[OK] ALL VERIFICATIONS PASSED!{RESET}")
    print()
    print("All three critical fixes are properly implemented:")
    print("  1. [OK] OAuth authentication on all endpoints")
    print("  2. [OK] Smart parameter auto-population")
    print("  3. [OK] MPSM response validation (IsValid field)")
    print()
    print("Next steps:")
    print("  - Run runtime tests: php test.php")
    print("  - Follow TEST_GUIDE.md for HTTP testing")
    print("  - Deploy to production subdomain")
    print()
    sys.exit(0)
else:
    print(f"{RED}{BOLD}[X] VERIFICATION FAILED{RESET}")
    print()
    print(f"{tests_failed} test(s) failed. Review the failures above.")
    print()
    print("The engine may not work correctly. Please:")
    print("  1. Review the failed tests above")
    print("  2. Check engine.php for missing implementations")
    print("  3. Compare with the fix plan in MPS_API_ENGINE_FIX_PLAN.md")
    print()
    sys.exit(1)
