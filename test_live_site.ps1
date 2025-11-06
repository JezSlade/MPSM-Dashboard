# Live Site Testing Script
# Tests the MPSM Dashboard after deployment

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "MPSM Dashboard Live Site Test" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$baseUrl = "https://mpsm.resolutionsbydesign.us"

# Test 1: Homepage loads
Write-Host "Test 1: Homepage..." -NoNewline
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/cms/" -TimeoutSec 10 -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        Write-Host " PASS" -ForegroundColor Green
    } else {
        Write-Host " WARN (Status: $($response.StatusCode))" -ForegroundColor Yellow
    }
} catch {
    Write-Host " FAIL ($($_.Exception.Message))" -ForegroundColor Red
}

# Test 2: Cache endpoint responds
Write-Host "Test 2: Cache endpoint..." -NoNewline
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/cms/api/get-cached-devices.php" -TimeoutSec 10 -UseBasicParsing
    $json = $response.Content | ConvertFrom-Json
    if ($json.success) {
        Write-Host " PASS (Devices: $($json.total))" -ForegroundColor Green
    } else {
        Write-Host " WARN (Not cached yet)" -ForegroundColor Yellow
    }
} catch {
    Write-Host " FAIL ($($_.Exception.Message))" -ForegroundColor Red
}

# Test 3: Panel message monitor
Write-Host "Test 3: Panel message monitor..." -NoNewline
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/cms/panel-message-monitor.php" -TimeoutSec 10 -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        Write-Host " PASS" -ForegroundColor Green
    } else {
        Write-Host " WARN (Status: $($response.StatusCode))" -ForegroundColor Yellow
    }
} catch {
    Write-Host " FAIL ($($_.Exception.Message))" -ForegroundColor Red
}

# Test 4: Payload debugger
Write-Host "Test 4: Payload debugger..." -NoNewline
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/cms/payload-debugger.php" -TimeoutSec 10 -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        Write-Host " PASS" -ForegroundColor Green
    } else {
        Write-Host " WARN (Status: $($response.StatusCode))" -ForegroundColor Yellow
    }
} catch {
    Write-Host " FAIL ($($_.Exception.Message))" -ForegroundColor Red
}

# Test 5: Background refresh endpoint
Write-Host "Test 5: Background refresh..." -NoNewline
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/cms/api/refresh-cache-enhanced.php" -TimeoutSec 30 -UseBasicParsing
    $json = $response.Content | ConvertFrom-Json
    if ($json.success) {
        Write-Host " PASS (Refreshed: $($json.devices_cached) devices)" -ForegroundColor Green
    } else {
        Write-Host " WARN (Error: $($json.error))" -ForegroundColor Yellow
    }
} catch {
    Write-Host " FAIL ($($_.Exception.Message))" -ForegroundColor Red
}

# Test 6: MPS API engine
Write-Host "Test 6: MPS API engine..." -NoNewline
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/mps-api/query" -Method POST -Body '{"action":"test"}' -ContentType "application/json" -TimeoutSec 10 -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        Write-Host " PASS" -ForegroundColor Green
    } else {
        Write-Host " WARN (Status: $($response.StatusCode))" -ForegroundColor Yellow
    }
} catch {
    Write-Host " FAIL ($($_.Exception.Message))" -ForegroundColor Red
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Testing Complete" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Check GitHub Actions: https://github.com/JezSlade/MPSM-Dashboard/actions" -ForegroundColor White
Write-Host "2. Apply database indexes via phpMyAdmin (see DEPLOY_NOW.md)" -ForegroundColor White
Write-Host "3. Rename get-cached-devices.php.NEW to .php on server" -ForegroundColor White
Write-Host "4. Run cache refresh: $baseUrl/cms/api/refresh-cache-enhanced.php" -ForegroundColor White
Write-Host "5. Schedule cron job for cache refresh (every 5 min)" -ForegroundColor White
Write-Host ""
