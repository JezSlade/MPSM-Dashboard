# Test Panel Message Callback Payloads
# Sends both successful and failed test requests to debug endpoint

$debugUrl = "https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message-debug.php"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Panel Callback Payload Testing" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Test 1: SUCCESS - Valid payload with correct secret
Write-Host "[Test 1] Sending VALID payload (correct secret)..." -ForegroundColor Green

$validPayload = @{
    callbackSecret = "mpsm-panel-message-v1"
    customer = @{
        code = "TEST_SUCCESS"
        description = "Success Test Customer"
    }
    installedProduct = @{
        serialNumber = "SUCCESS_SERIAL_001"
        product = @{
            brand = "Test Brand"
            model = "Test Model"
        }
        toner = @{
            black = 85
            cyan = 90
            magenta = 88
            yellow = 92
        }
        office = @{
            name = "Test Office"
            address = "123 Test St"
        }
    }
    maintenanceAlert = @{
        code = "TEST_ALERT"
        id = "ALERT_001"
        panelConfiguration = "Test Configuration"
    }
} | ConvertTo-Json -Depth 10

try {
    $response = Invoke-RestMethod -Uri $debugUrl -Method Post -Body $validPayload -ContentType "application/json" -ErrorAction Stop
    Write-Host "✓ SUCCESS Response:" -ForegroundColor Green
    Write-Host ($response | ConvertTo-Json) -ForegroundColor White
} catch {
    Write-Host "✗ ERROR:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

Start-Sleep -Seconds 2

# Test 2: ERROR - Invalid secret
Write-Host "`n[Test 2] Sending payload with INVALID secret..." -ForegroundColor Yellow

$invalidSecretPayload = @{
    callbackSecret = "wrong-secret"
    customer = @{
        code = "TEST_ERROR"
        description = "Error Test - Wrong Secret"
    }
    installedProduct = @{
        serialNumber = "ERROR_SERIAL_002"
    }
    maintenanceAlert = @{
        code = "TEST_ALERT"
        id = "ALERT_002"
    }
} | ConvertTo-Json -Depth 10

try {
    $response = Invoke-RestMethod -Uri $debugUrl -Method Post -Body $invalidSecretPayload -ContentType "application/json" -ErrorAction Stop
    Write-Host "Response:" -ForegroundColor Yellow
    Write-Host ($response | ConvertTo-Json) -ForegroundColor White
} catch {
    Write-Host "✓ Expected ERROR Response (401 Unauthorized):" -ForegroundColor Yellow
    if ($_.Exception.Response.StatusCode -eq 401) {
        Write-Host "Status: 401 Unauthorized - Security working correctly!" -ForegroundColor Green
    }
}

Start-Sleep -Seconds 2

# Test 3: ERROR - Missing secret entirely
Write-Host "`n[Test 3] Sending payload with NO secret..." -ForegroundColor Yellow

$noSecretPayload = @{
    customer = @{
        code = "TEST_ERROR2"
        description = "Error Test - No Secret"
    }
    installedProduct = @{
        serialNumber = "ERROR_SERIAL_003"
    }
    maintenanceAlert = @{
        code = "TEST_ALERT"
        id = "ALERT_003"
    }
} | ConvertTo-Json -Depth 10

try {
    $response = Invoke-RestMethod -Uri $debugUrl -Method Post -Body $noSecretPayload -ContentType "application/json" -ErrorAction Stop
    Write-Host "Response:" -ForegroundColor Yellow
    Write-Host ($response | ConvertTo-Json) -ForegroundColor White
} catch {
    Write-Host "✓ Expected ERROR Response (401 Unauthorized):" -ForegroundColor Yellow
    if ($_.Exception.Response.StatusCode -eq 401) {
        Write-Host "Status: 401 Unauthorized - Security working correctly!" -ForegroundColor Green
    }
}

Start-Sleep -Seconds 2

# Test 4: ERROR - Wrong Content-Type
Write-Host "`n[Test 4] Sending request with wrong Content-Type..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri $debugUrl -Method Post -Body "some text" -ContentType "text/plain" -ErrorAction Stop
    Write-Host "Response:" -ForegroundColor Yellow
    Write-Host ($response | ConvertTo-Json) -ForegroundColor White
} catch {
    Write-Host "✓ Expected ERROR Response (415 Unsupported Media Type):" -ForegroundColor Yellow
    if ($_.Exception.Response.StatusCode -eq 415) {
        Write-Host "Status: 415 Unsupported Media Type - Validation working!" -ForegroundColor Green
    }
}

Start-Sleep -Seconds 2

# Test 5: ERROR - Invalid JSON
Write-Host "`n[Test 5] Sending invalid JSON..." -ForegroundColor Yellow

$invalidJson = "{this is not valid json"

try {
    $response = Invoke-RestMethod -Uri $debugUrl -Method Post -Body $invalidJson -ContentType "application/json" -ErrorAction Stop
    Write-Host "Response:" -ForegroundColor Yellow
    Write-Host ($response | ConvertTo-Json) -ForegroundColor White
} catch {
    Write-Host "✓ Expected ERROR Response (400 Bad Request):" -ForegroundColor Yellow
    if ($_.Exception.Response.StatusCode -eq 400) {
        Write-Host "Status: 400 Bad Request - JSON validation working!" -ForegroundColor Green
    }
}

Start-Sleep -Seconds 2

# Test 6: ERROR - Empty body
Write-Host "`n[Test 6] Sending empty body..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri $debugUrl -Method Post -Body "" -ContentType "application/json" -ErrorAction Stop
    Write-Host "Response:" -ForegroundColor Yellow
    Write-Host ($response | ConvertTo-Json) -ForegroundColor White
} catch {
    Write-Host "✓ Expected ERROR Response (400 Bad Request):" -ForegroundColor Yellow
    if ($_.Exception.Response.StatusCode -eq 400) {
        Write-Host "Status: 400 Bad Request - Empty body validation working!" -ForegroundColor Green
    }
}

Start-Sleep -Seconds 2

# Test 7: ERROR - GET request (should be POST)
Write-Host "`n[Test 7] Sending GET request (should only accept POST)..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri $debugUrl -Method Get -ErrorAction Stop
    Write-Host "Response:" -ForegroundColor Yellow
    Write-Host ($response | ConvertTo-Json) -ForegroundColor White
} catch {
    Write-Host "✓ Expected ERROR Response (405 Method Not Allowed):" -ForegroundColor Yellow
    if ($_.Exception.Response.StatusCode -eq 405) {
        Write-Host "Status: 405 Method Not Allowed - HTTP method validation working!" -ForegroundColor Green
    }
}

Start-Sleep -Seconds 2

# Test 8: SUCCESS - Another valid payload with different data
Write-Host "`n[Test 8] Sending second VALID payload..." -ForegroundColor Green

$validPayload2 = @{
    callbackSecret = "mpsm-panel-message-v1"
    customer = @{
        code = "ACME_CORP"
        description = "ACME Corporation"
    }
    installedProduct = @{
        serialNumber = "SUCCESS_SERIAL_002"
        product = @{
            brand = "Canon"
            model = "imageRUNNER ADVANCE"
        }
        toner = @{
            black = 45
            cyan = 62
            magenta = 58
            yellow = 71
        }
        office = @{
            name = "Main Office"
            address = "456 Business Blvd"
            city = "Test City"
            state = "TS"
        }
    }
    maintenanceAlert = @{
        code = "C2557"
        id = "ALERT_002"
        panelConfiguration = "Low Toner Warning"
        severity = "Medium"
    }
} | ConvertTo-Json -Depth 10

try {
    $response = Invoke-RestMethod -Uri $debugUrl -Method Post -Body $validPayload2 -ContentType "application/json" -ErrorAction Stop
    Write-Host "✓ SUCCESS Response:" -ForegroundColor Green
    Write-Host ($response | ConvertTo-Json) -ForegroundColor White
} catch {
    Write-Host "✗ ERROR:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Testing Complete!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "`nCheck the payload debugger at:" -ForegroundColor White
Write-Host "https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php" -ForegroundColor Cyan
Write-Host "`nYou should see:" -ForegroundColor White
Write-Host "  - 2 SUCCESS entries (Tests 1 and 8)" -ForegroundColor Green
Write-Host "  - 6 ERROR entries (Tests 2-7)" -ForegroundColor Yellow
Write-Host "`n"
