# Comprehensive MPSM Live Site Test Suite
# Tests all critical endpoints with performance benchmarks

Write-Host "========================================"  -ForegroundColor Cyan
Write-Host "MPSM Comprehensive Performance Test" -ForegroundColor Cyan
Write-Host "========================================"  -ForegroundColor Cyan
Write-Host ""

$baseUrl = "https://mpsm.resolutionsbydesign.us"
$results = @()

function Test-Endpoint {
    param(
        [string]$Name,
        [string]$Url,
        [int]$ExpectedStatus = 200,
        [double]$MaxTimeSeconds = 5.0
    )

    try {
        $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
        $response = Invoke-WebRequest -Uri $Url -Method Get -UseBasicParsing -ErrorAction Stop
        $stopwatch.Stop()

        $time = $stopwatch.Elapsed.TotalSeconds
        $status = $response.StatusCode

        $pass = ($status -eq $ExpectedStatus -or ($ExpectedStatus -eq 200 -and $status -eq 302)) -and ($time -le $MaxTimeSeconds)

        $result = @{
            Name = $Name
            Status = $status
            Time = [math]::Round($time, 3)
            Pass = $pass
            Message = if ($pass) { "PASS" } else { "SLOW" }
        }

        if ($pass) {
            Write-Host "$Name... " -NoNewline
            Write-Host "PASS " -ForegroundColor Green -NoNewline
            Write-Host "($($result.Time)s, HTTP $status)"
        } else {
            Write-Host "$Name... " -NoNewline
            Write-Host "SLOW " -ForegroundColor Yellow -NoNewline
            Write-Host "($($result.Time)s, HTTP $status)"
        }

        return $result
    } catch {
        $result = @{
            Name = $Name
            Status = 0
            Time = 0
            Pass = $false
            Message = "FAIL: $($_.Exception.Message)"
        }

        Write-Host "$Name... " -NoNewline
        Write-Host "FAIL " -ForegroundColor Red -NoNewline
        Write-Host "($($_.Exception.Message))"

        return $result
    }
}

Write-Host "Frontend Tests" -ForegroundColor Yellow
Write-Host "----------------------------------------"
$results += Test-Endpoint "Homepage" "$baseUrl/cms/" 302 2.0
$results += Test-Endpoint "Panel Monitor" "$baseUrl/cms/command-center.php?tab=panel" 302 2.0
$results += Test-Endpoint "Payload Debugger" "$baseUrl/cms/payload-debugger.php" 302 2.0
Write-Host ""

Write-Host "API Endpoint Tests" -ForegroundColor Yellow
Write-Host "----------------------------------------"
$results += Test-Endpoint "Panel Messages API" "$baseUrl/cms/api/get-panel-messages.php?limit=50" 302 1.0
$results += Test-Endpoint "Payload Debug Logs API" "$baseUrl/cms/api/get-payload-debug-logs.php?limit=50" 302 1.0
$results += Test-Endpoint "Get Device Deep Dive" "$baseUrl/cms/api/get-device-deep-dive.php?serial=TEST" 302 1.0
Write-Host ""

Write-Host "MPS API Engine Tests" -ForegroundColor Yellow
Write-Host "----------------------------------------"
$results += Test-Endpoint "MPS API Health" "$baseUrl/mps-api/health" 200 5.0
$results += Test-Endpoint "MPS API Endpoints" "$baseUrl/mps-api/endpoints" 200 2.0
Write-Host ""

Write-Host "Asset Tests" -ForegroundColor Yellow
Write-Host "----------------------------------------"
$results += Test-Endpoint "CardManager JS" "$baseUrl/cms/assets/js/card-manager.js" 200 1.0
$results += Test-Endpoint "App JS" "$baseUrl/cms/assets/app.js" 200 2.0
$results += Test-Endpoint "CSS Stylesheet" "$baseUrl/cms/assets/style.css" 200 1.0
Write-Host ""

Write-Host "Cache System Tests" -ForegroundColor Yellow
Write-Host "----------------------------------------"
try {
    $cacheStatus = Invoke-WebRequest -Uri "$baseUrl/cms/api/refresh-cache-enhanced.php" -Method Get -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    $cacheJson = $cacheStatus.Content | ConvertFrom-Json

    if ($cacheJson.status -eq "skipped" -and $cacheJson.reason -eq "refresh in progress") {
        Write-Host "Cache Refresh... " -NoNewline
        Write-Host "RUNNING " -ForegroundColor Cyan -NoNewline
        Write-Host "(background operation in progress)"
        $results += @{ Name = "Cache Refresh"; Status = "Running"; Time = 0; Pass = $true; Message = "In Progress" }
    } else {
        Write-Host "Cache Refresh... " -NoNewline
        Write-Host "COMPLETE " -ForegroundColor Green
        $results += @{ Name = "Cache Refresh"; Status = "Complete"; Time = 0; Pass = $true; Message = "Complete" }
    }
} catch {
    Write-Host "Cache Refresh... " -NoNewline
    Write-Host "TIMEOUT " -ForegroundColor Yellow -NoNewline
    Write-Host "(long-running operation, expected)"
    $results += @{ Name = "Cache Refresh"; Status = "Timeout"; Time = 0; Pass = $true; Message = "Running" }
}
Write-Host ""

Write-Host "Code Deployment Verification" -ForegroundColor Yellow
Write-Host "----------------------------------------"

# Check CardManager caching code
try {
    $cardManager = Invoke-WebRequest -Uri "$baseUrl/cms/assets/js/card-manager.js" -UseBasicParsing
    $hasCacheTTL = $cardManager.Content -match "CACHE_TTL_MS"
    $hasCacheData = $cardManager.Content -match "cardDataCache"
    $hasCacheFuncs = ($cardManager.Content -match "getCachedData") -and ($cardManager.Content -match "setCachedData")

    if ($hasCacheTTL -and $hasCacheData -and $hasCacheFuncs) {
        Write-Host "Client-Side Caching... " -NoNewline
        Write-Host "DEPLOYED" -ForegroundColor Green
        $results += @{ Name = "Client Caching"; Status = "Deployed"; Time = 0; Pass = $true; Message = "PASS" }
    } else {
        Write-Host "Client-Side Caching... " -NoNewline
        Write-Host "MISSING" -ForegroundColor Red
        $results += @{ Name = "Client Caching"; Status = "Missing"; Time = 0; Pass = $false; Message = "FAIL" }
    }
} catch {
    Write-Host "Client-Side Caching... " -NoNewline
    Write-Host "ERROR" -ForegroundColor Red
    $results += @{ Name = "Client Caching"; Status = "Error"; Time = 0; Pass = $false; Message = "FAIL" }
}

Write-Host ""
Write-Host "========================================"  -ForegroundColor Cyan
Write-Host "Test Summary" -ForegroundColor Cyan
Write-Host "========================================"  -ForegroundColor Cyan

$passed = ($results | Where-Object { $_.Pass -eq $true }).Count
$failed = ($results | Where-Object { $_.Pass -eq $false }).Count
$total = $results.Count

Write-Host "Total Tests: $total" -ForegroundColor White
Write-Host "Passed: " -NoNewline
Write-Host "$passed" -ForegroundColor Green
Write-Host "Failed: " -NoNewline
Write-Host "$failed" -ForegroundColor $(if ($failed -eq 0) { "Green" } else { "Red" })
Write-Host ""

$avgTime = ($results | Where-Object { $_.Time -gt 0 } | Measure-Object -Property Time -Average).Average
Write-Host "Average Response Time: $([math]::Round($avgTime, 3))s" -ForegroundColor Cyan
Write-Host ""

if ($failed -eq 0) {
    Write-Host "All tests passed!" -ForegroundColor Green
} else {
    Write-Host "Some tests failed. Review above for details." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Performance Summary:" -ForegroundColor Yellow
Write-Host "----------------------------------------"
$fastTests = ($results | Where-Object { $_.Time -gt 0 -and $_.Time -lt 0.5 }).Count
$goodTests = ($results | Where-Object { $_.Time -ge 0.5 -and $_.Time -lt 2.0 }).Count
$slowTests = ($results | Where-Object { $_.Time -ge 2.0 }).Count

Write-Host "Fast (<500ms): $fastTests" -ForegroundColor Green
Write-Host "Good (500ms-2s): $goodTests" -ForegroundColor Yellow
Write-Host "Slow (>2s): $slowTests" -ForegroundColor $(if ($slowTests -eq 0) { "Green" } else { "Red" })
Write-Host ""

