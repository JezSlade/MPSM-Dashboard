# Test Command Center - Create Sample Notification Rules
# This script creates example rules to demonstrate the Command Center system

$baseUrl = "https://mpsm.resolutionsbydesign.us/cms"

Write-Host "=== Command Center Test Script ===" -ForegroundColor Cyan
Write-Host ""

# Rule 1: Any Critical Alert (catches all incoming alerts)
Write-Host "Creating Rule 1: Catch All Critical Alerts..." -ForegroundColor Yellow
$rule1 = @{
    action = "create_rule"
    name = "All Panel Messages - Critical"
    description = "Catch all incoming panel messages for monitoring"
    severity = "critical"
    alert_code_pattern = "%"
    notification_title = "{severity} - {device} has {alert}"
    notification_message = "Device {device} triggered alert {alert}. Customer: {customer}"
    show_dashboard = 1
    auto_dismiss_hours = 24
} | ConvertTo-Json

try {
    $response1 = Invoke-WebRequest -Uri "$baseUrl/api/command-center.php" `
        -Method POST `
        -ContentType "application/json" `
        -Body $rule1 `
        -UseBasicParsing `
        -SessionVariable session

    $result1 = $response1.Content | ConvertFrom-Json
    if ($result1.success) {
        Write-Host "  ✓ Rule 1 created successfully (ID: $($result1.rule_id))" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Failed: $($result1.error)" -ForegroundColor Red
    }
} catch {
    Write-Host "  ✗ Error: $_" -ForegroundColor Red
}

Write-Host ""

# Rule 2: Frequent Alerts (3+ times in 1 hour)
Write-Host "Creating Rule 2: Frequent Alert Detection..." -ForegroundColor Yellow
$rule2 = @{
    action = "create_rule"
    name = "High Frequency Alerts"
    description = "Alert when same device triggers 3+ times in 1 hour"
    severity = "high"
    frequency_count = 3
    frequency_window_hours = 1
    frequency_type = "same_device"
    notification_title = "High Frequency Alert - {device}"
    notification_message = "{device} has triggered {count} alerts in the past {window}"
    show_dashboard = 1
    auto_dismiss_hours = 12
} | ConvertTo-Json

try {
    $response2 = Invoke-WebRequest -Uri "$baseUrl/api/command-center.php" `
        -Method POST `
        -ContentType "application/json" `
        -Body $rule2 `
        -WebSession $session `
        -UseBasicParsing

    $result2 = $response2.Content | ConvertFrom-Json
    if ($result2.success) {
        Write-Host "  ✓ Rule 2 created successfully (ID: $($result2.rule_id))" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Failed: $($result2.error)" -ForegroundColor Red
    }
} catch {
    Write-Host "  ✗ Error: $_" -ForegroundColor Red
}

Write-Host ""

# Check current notifications
Write-Host "Checking for active notifications..." -ForegroundColor Yellow
try {
    $notifResponse = Invoke-WebRequest -Uri "$baseUrl/api/command-center.php?action=get_notifications" `
        -Method GET `
        -WebSession $session `
        -UseBasicParsing

    $notifications = ($notifResponse.Content | ConvertFrom-Json).notifications
    Write-Host "  ✓ Found $($notifications.Count) active notifications" -ForegroundColor Green

    if ($notifications.Count -gt 0) {
        Write-Host ""
        Write-Host "Active Notifications:" -ForegroundColor Cyan
        foreach ($notif in $notifications | Select-Object -First 5) {
            Write-Host "  - [$($notif.severity.ToUpper())] $($notif.title)" -ForegroundColor Yellow
            Write-Host "    $($notif.message)" -ForegroundColor Gray
        }
    }
} catch {
    Write-Host "  ✗ Error checking notifications: $_" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Test Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor White
Write-Host "1. Open dashboard: $baseUrl/" -ForegroundColor Gray
Write-Host "2. Open Command Center: $baseUrl/command-center.php" -ForegroundColor Gray
Write-Host "3. Wait for new panel messages to trigger notifications" -ForegroundColor Gray
Write-Host ""
Write-Host "Press any key to open Command Center in browser..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

Start-Process "$baseUrl/command-center.php"
