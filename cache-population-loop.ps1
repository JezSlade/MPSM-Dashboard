# Cache Population Loop
# Runs cache refresh continuously until all devices are cached

$baseUrl = "https://mpsm.resolutionsbydesign.us/cms/api"
$maxRuns = 20  # Safety limit
$runCount = 0

Write-Host "=== MPSM Cache Population Loop ===" -ForegroundColor Cyan
Write-Host "Starting continuous cache refresh..." -ForegroundColor Green
Write-Host ""

while ($runCount -lt $maxRuns) {
    $runCount++
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

    Write-Host "[$timestamp] Run #$runCount of $maxRuns" -ForegroundColor Yellow

    try {
        # Run cache refresh with drill-down
        Write-Host "  Initiating cache refresh..." -ForegroundColor Gray

        $response = Invoke-WebRequest -Uri "$baseUrl/refresh-cache-enhanced.php?force=1" `
            -Method GET `
            -TimeoutSec 1200 `
            -ErrorAction Stop

        $result = $response.Content | ConvertFrom-Json

        if ($result.status -eq "success") {
            $stats = $result.stats

            Write-Host "  ✓ Cache refresh completed successfully" -ForegroundColor Green
            Write-Host "    • Devices cached: $($stats.devices_cached)" -ForegroundColor White
            Write-Host "    • Drill-down cached: $($stats.devices_with_drilldown)" -ForegroundColor White
            Write-Host "    • Panel messages: $($stats.devices_with_panels)" -ForegroundColor White
            Write-Host "    • API calls: $($stats.api_calls_made)" -ForegroundColor White
            Write-Host "    • Duration: $($stats.duration)s" -ForegroundColor White
            Write-Host "    • Errors: $($stats.errors)" -ForegroundColor $(if ($stats.errors -gt 0) { "Red" } else { "White" })

            # Check if we've cached enough devices
            $coverage = 0
            if ($stats.devices_cached -gt 0) {
                $coverage = [math]::Round(($stats.devices_with_drilldown / $stats.devices_cached) * 100, 1)
            }

            Write-Host "    • Drill-down coverage: $coverage%" -ForegroundColor Cyan

            # Stop if we've achieved good coverage
            if ($coverage -ge 95 -and $stats.devices_cached -gt 100) {
                Write-Host ""
                Write-Host "✓ Target achieved: $coverage% drill-down coverage with $($stats.devices_cached) devices" -ForegroundColor Green
                Write-Host "Cache population complete!" -ForegroundColor Green
                break
            }

            # Continue if more work needed
            if ($stats.devices_cached -lt 1000) {
                Write-Host "  → More devices may exist. Continuing..." -ForegroundColor Yellow
            }

        } else {
            Write-Host "  ✗ Cache refresh returned non-success status" -ForegroundColor Red
            Write-Host "    Status: $($result.status)" -ForegroundColor Red
            if ($result.message) {
                Write-Host "    Message: $($result.message)" -ForegroundColor Red
            }
        }

    } catch {
        Write-Host "  ✗ Error during cache refresh:" -ForegroundColor Red
        Write-Host "    $($_.Exception.Message)" -ForegroundColor Red

        if ($_.Exception.Message -like "*timeout*" -or $_.Exception.Message -like "*timed out*") {
            Write-Host "  ⚠ Request timed out - cache may still be running on server" -ForegroundColor Yellow
            Write-Host "    Waiting 5 minutes before next attempt..." -ForegroundColor Yellow
            Start-Sleep -Seconds 300
        }
    }

    Write-Host ""

    # Wait between runs
    if ($runCount -lt $maxRuns) {
        Write-Host "  Waiting 2 minutes before next run..." -ForegroundColor Gray
        Start-Sleep -Seconds 120
    }
}

Write-Host ""
Write-Host "=== Cache Population Loop Finished ===" -ForegroundColor Cyan
Write-Host "Total runs: $runCount" -ForegroundColor White
Write-Host ""
Write-Host "To check final cache status, visit:" -ForegroundColor Yellow
Write-Host "https://mpsm.resolutionsbydesign.us/cms/api/cache-audit.php" -ForegroundColor Cyan
