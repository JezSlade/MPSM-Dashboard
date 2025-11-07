# Simple cache refresh starter script
# Runs one cache refresh and shows the results

$url = "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?force=1"

Write-Host "=== MPSM Cache Refresh ===" -ForegroundColor Cyan
Write-Host "Initiating cache refresh (this may take 10-20 minutes)..." -ForegroundColor Yellow
Write-Host ""

try {
    $response = Invoke-WebRequest -Uri $url -Method GET -TimeoutSec 1200 -ErrorAction Stop
    $result = $response.Content | ConvertFrom-Json

    if ($result.status -eq "success") {
        Write-Host "✓ Cache refresh completed successfully!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Statistics:" -ForegroundColor Cyan
        Write-Host "  • Devices cached: $($result.stats.devices_cached)" -ForegroundColor White
        Write-Host "  • Drill-down cached: $($result.stats.devices_with_drilldown)" -ForegroundColor White
        Write-Host "  • Panel messages: $($result.stats.devices_with_panels)" -ForegroundColor White
        Write-Host "  • API calls made: $($result.stats.api_calls_made)" -ForegroundColor White
        Write-Host "  • Rate limit retries: $($result.stats.rate_limit_retries)" -ForegroundColor White
        Write-Host "  • Errors: $($result.stats.errors)" -ForegroundColor White
        Write-Host "  • Duration: $($result.stats.duration)s" -ForegroundColor White
    } else {
        Write-Host "✗ Cache refresh failed" -ForegroundColor Red
        Write-Host "Status: $($result.status)" -ForegroundColor Red
        if ($result.message) {
            Write-Host "Message: $($result.message)" -ForegroundColor Red
        }
    }
} catch {
    Write-Host "✗ Error occurred:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

Write-Host ""
