# Comprehensive Live Site Test Script
# Tests all functionality on https://mpsm.resolutionsbydesign.us/cms/

$base = "https://mpsm.resolutionsbydesign.us/cms"
$cookies = "test_session_cookies.txt"
$results = @()

function Test-API {
    param($name, $endpoint, $expectedField)

    Write-Host "`n[$name]" -ForegroundColor Cyan
    $url = "$base/$endpoint"

    try {
        $response = curl -s $url -b $cookies 2>&1

        if ($response -match $expectedField) {
            Write-Host "  PASS" -ForegroundColor Green
            $results += @{Test=$name; Status="PASS"}
            return $true
        } else {
            Write-Host "  FAIL - Expected field not found: $expectedField" -ForegroundColor Red
            Write-Host "  Response preview: $($response.Substring(0, [Math]::Min(200, $response.Length)))"
            $results += @{Test=$name; Status="FAIL"; Error="Missing $expectedField"}
            return $false
        }
    } catch {
        Write-Host "  ERROR - $($_.Exception.Message)" -ForegroundColor Red
        $results += @{Test=$name; Status="ERROR"; Error=$_.Exception.Message}
        return $false
    }
}

Write-Host "=== MPSM Dashboard Live Site Test ===" -ForegroundColor Yellow
Write-Host "Target: $base`n"

# Wait for deployment
Write-Host "Waiting 30s for deployment..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# Test 1: Site loads
Write-Host "`n[1. Site loads]" -ForegroundColor Cyan
try {
    $status = curl -s "$base/" -o nul -w "%{http_code}"
    if ($status -eq 200) {
        Write-Host "  PASS - HTTP 200" -ForegroundColor Green
        $results += @{Test="Site loads"; Status="PASS"}
    } else {
        Write-Host "  FAIL - HTTP $status" -ForegroundColor Red
        $results += @{Test="Site loads"; Status="FAIL"; Error="HTTP $status"}
    }
} catch {
    Write-Host "  ERROR" -ForegroundColor Red
    $results += @{Test="Site loads"; Status="ERROR"}
}

# Test 2-5: Core APIs (no auth required for testing structure)
Test-API "Login API exists" "api/login.php" "success"
Test-API "Search devices API" "api/search-devices.php?query=HP" "devices"
Test-API "Deep-dive API structure" "api/get-device-deep-dive.php?serialNumber=TEST" "success"
Test-API "Error logs API" "api/get-error-logs.php?lines=10" "logs"

# Test 6: Check for test files (should be deleted)
Write-Host "`n[6. Test files cleaned up]" -ForegroundColor Cyan
$testFiles = @(
    "api/test-counter-list.php",
    "api/find-fq966.php"
)

$foundTestFiles = @()
foreach ($file in $testFiles) {
    $status = curl -s "$base/$file" -o nul -w "%{http_code}" 2>&1
    if ($status -eq 200) {
        $foundTestFiles += $file
    }
}

if ($foundTestFiles.Count -eq 0) {
    Write-Host "  PASS - All test files removed" -ForegroundColor Green
    $results += @{Test="Test files cleaned"; Status="PASS"}
} else {
    Write-Host "  FAIL - Found test files: $($foundTestFiles -join ', ')" -ForegroundColor Red
    $results += @{Test="Test files cleaned"; Status="FAIL"; Error="Files remain"}
}

# Test 7: Assets load
Write-Host "`n[7. Assets load]" -ForegroundColor Cyan
$assets = @(
    "assets/app.js",
    "assets/style.css",
    "assets/error-logs.js"
)

$assetsOK = $true
foreach ($asset in $assets) {
    $status = curl -s "$base/$asset" -o nul -w "%{http_code}" 2>&1
    if ($status -ne 200) {
        Write-Host "  FAIL - $asset returned $status" -ForegroundColor Red
        $assetsOK = $false
    }
}

if ($assetsOK) {
    Write-Host "  PASS - All assets load" -ForegroundColor Green
    $results += @{Test="Assets load"; Status="PASS"}
} else {
    $results += @{Test="Assets load"; Status="FAIL"}
}

# Summary
Write-Host "`n`n=== TEST SUMMARY ===" -ForegroundColor Yellow
$passed = ($results | Where-Object {$_.Status -eq "PASS"}).Count
$failed = ($results | Where-Object {$_.Status -eq "FAIL"}).Count
$errors = ($results | Where-Object {$_.Status -eq "ERROR"}).Count
$total = $results.Count

Write-Host "Total: $total tests"
Write-Host "Passed: $passed" -ForegroundColor Green
Write-Host "Failed: $failed" -ForegroundColor Red
Write-Host "Errors: $errors" -ForegroundColor Red

if ($failed -gt 0 -or $errors -gt 0) {
    Write-Host "`nFailed/Error tests:"
    $results | Where-Object {$_.Status -ne "PASS"} | ForEach-Object {
        Write-Host "  - $($_.Test): $($_.Status) $($_.Error)" -ForegroundColor Red
    }
    exit 1
}

Write-Host "`nAll tests passed!" -ForegroundColor Green
exit 0
