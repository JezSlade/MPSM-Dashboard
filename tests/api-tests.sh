#!/bin/bash
# API Integration Tests
# Tests all API endpoints to verify functionality

BASE_URL="https://mpsm.resolutionsbydesign.us"
API_URL="${BASE_URL}/cms/api"
API_V1="${BASE_URL}/cms/api/v1"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

PASSED=0
FAILED=0

# Test helper function
test_api() {
    local name="$1"
    local method="$2"
    local url="$3"
    local expected_code="${4:-200}"

    echo -n "Testing: $name... "

    if [ "$method" = "GET" ]; then
        response=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    elif [ "$method" = "POST" ]; then
        response=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$url")
    fi

    if [ "$response" = "$expected_code" ]; then
        echo -e "${GREEN}✓ PASS${NC} (HTTP $response)"
        ((PASSED++))
    else
        echo -e "${RED}✗ FAIL${NC} (Expected HTTP $expected_code, got $response)"
        ((FAILED++))
    fi
}

echo "=== MPSM Dashboard API Tests ==="
echo ""

# ============================================================================
# Legacy API Tests
# ============================================================================

echo "--- Legacy API Endpoints ---"

test_api "Get Devices" "GET" "${API_URL}/get-devices.php"
test_api "Get Dashboard Stats" "GET" "${API_URL}/get-dashboard-stats.php"
test_api "Get Device Deep Dive" "GET" "${API_URL}/get-device-deep-dive.php?serial=TEST123" "200"
test_api "Refresh Cache (triggers job)" "GET" "${API_URL}/refresh-cache-enhanced.php"
test_api "Get Payload Debug Logs" "GET" "${API_URL}/get-payload-debug-logs.php"
test_api "Get Database Monitor" "GET" "${API_URL}/get-database-monitor.php"

echo ""

# ============================================================================
# New REST API v1 Tests
# ============================================================================

echo "--- REST API v1 Endpoints ---"

test_api "Health Check" "GET" "${API_V1}/health"
test_api "List Devices (requires auth)" "GET" "${API_V1}/devices" "401"
test_api "Device Stats (requires auth)" "GET" "${API_V1}/devices/stats" "401"
test_api "Panel Messages (requires auth)" "GET" "${API_V1}/panel-messages" "401"
test_api "Panel Message Stats (requires auth)" "GET" "${API_V1}/panel-messages/stats" "401"

echo ""

# ============================================================================
# Webhook Tests
# ============================================================================

echo "--- Webhook Endpoints ---"

test_api "Panel Message Webhook" "POST" "${BASE_URL}/mps-api/callbacks/panel-message.php"

echo ""

# ============================================================================
# Frontend Tests
# ============================================================================

echo "--- Frontend Pages ---"

test_api "Login Page" "GET" "${BASE_URL}/cms/login.html"
test_api "Dashboard (requires auth)" "GET" "${BASE_URL}/cms/index.php" "302"
test_api "Panel Message Monitor (requires auth)" "GET" "${BASE_URL}/cms/panel-message-monitor.php" "302"
test_api "Payload Debugger (requires auth)" "GET" "${BASE_URL}/cms/payload-debugger.php" "302"

echo ""

# ============================================================================
# Static Assets
# ============================================================================

echo "--- Static Assets ---"

test_api "Main CSS" "GET" "${BASE_URL}/cms/assets/style.css"
test_api "Main JS" "GET" "${BASE_URL}/cms/assets/app.js"
test_api "API Client Module" "GET" "${BASE_URL}/cms/assets/js/api-client.js"
test_api "State Manager Module" "GET" "${BASE_URL}/cms/assets/js/state-manager.js"

echo ""

# ============================================================================
# Summary
# ============================================================================

echo "=== Test Summary ==="
echo "Passed: $PASSED"
echo "Failed: $FAILED"
echo "Total:  $((PASSED + FAILED))"

if [ $FAILED -eq 0 ]; then
    echo -e "\n${GREEN}✓ All tests passed!${NC}"
    exit 0
else
    echo -e "\n${RED}✗ Some tests failed. Please review.${NC}"
    exit 1
fi
