param([string]$BaseUrl = "https://mpsm.resolutionsbydesign.us",
      [string]$Username = "admin",
      [string]$Password = "admin",
      [int]$Limit = 20)

$ErrorActionPreference = 'Stop'

function Invoke-JsonRequest {
    param(
        [string]$Uri,
        [string]$Method = 'GET',
        [hashtable]$Body = $null,
        [Microsoft.PowerShell.Commands.WebRequestSession]$Session = $null,
        [string]$ContentType = 'application/json'
    )

    $bodyJson = $null
    if ($Body) {
        $bodyJson = ($Body | ConvertTo-Json -Depth 10)
    }

    $response = Invoke-WebRequest -Uri $Uri -Method $Method -WebSession $Session -ContentType $ContentType -Body $bodyJson -UseBasicParsing
    if (-not $response.Content) {
        throw "Empty response from $Uri"
    }

    return $response.Content | ConvertFrom-Json
}

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$loginUri = "$BaseUrl/cms/api/login.php"
$loginPayload = @{
    username = $Username
    password = $Password
}

Write-Host "Logging in as $Username..." -ForegroundColor Cyan
$loginResponse = Invoke-JsonRequest -Uri $loginUri -Method 'POST' -Body $loginPayload -Session $session
if (-not $loginResponse.success) {
    throw "Login failed: $($loginResponse.error)"
}

Write-Host "Fetching last $Limit panel messages..." -ForegroundColor Cyan
$messagesUri = "$BaseUrl/cms/api/get-panel-messages.php?limit=$Limit"
$messagesResponse = Invoke-JsonRequest -Uri $messagesUri -Method 'GET' -Session $session -ContentType 'application/json'

if (-not $messagesResponse.success) {
    throw "Panel message query failed: $($messagesResponse.error)"
}

$messages = $messagesResponse.messages
if (-not $messages) {
    Write-Host "No panel messages stored yet." -ForegroundColor Yellow
    return
}

$latest = $messages | Select-Object -First 1
Write-Host "Latest message received:`n  ID: $($latest.id)`n  Received: $($latest.received_at)`n  Customer: $($latest.customer_code)`n  Device: $($latest.device_serial)`n  Alert: $($latest.maintenance_alert_code)" -ForegroundColor Green

Write-Host "`nRecent message summary:" -ForegroundColor Cyan
$messages | Select-Object id, received_at, customer_code, maintenance_alert_code, device_serial |
    Format-Table -AutoSize | Out-String | Write-Host

Write-Host "`nProcessed stats:" -ForegroundColor Cyan
$processedCount = ($messages | Where-Object { $_.processed }).Count
$unprocessedCount = $messages.Count - $processedCount
Write-Host ("  Processed:   {0}" -f $processedCount)
Write-Host ("  Unprocessed: {0}" -f $unprocessedCount)

Write-Host "`nDone."
