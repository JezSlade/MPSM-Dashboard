# Panel Message Callback Smoke Tests (Success Paths Only)
$debugUrl = "https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message-debug.php"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Panel Callback Payload Testing" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

function Invoke-CallbackTest($label, $payload) {
    Write-Host "`n[$label] Sending payload..." -ForegroundColor Green
    try {
        $response = Invoke-RestMethod -Uri $debugUrl -Method Post -Body $payload -ContentType "application/json" -ErrorAction Stop
        Write-Host "✔ SUCCESS Response:" -ForegroundColor Green
        Write-Host ($response | ConvertTo-Json -Depth 5) -ForegroundColor White
    } catch {
        Write-Host "✖ ERROR:" -ForegroundColor Red
        Write-Host $_.Exception.Message -ForegroundColor Red
    }
    Start-Sleep -Seconds 2
}

# Test 1: VALID payload with correct secret
$validPayload1 = @{
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
        note = "Line 1`nLine 2 (multi-line note test)"
    }
    maintenanceAlert = @{
        code = "TEST_ALERT"
        id = "ALERT_001"
        panelConfiguration = "Test Configuration"
    }
} | ConvertTo-Json -Depth 10

Invoke-CallbackTest "Test 1" $validPayload1

# Test 2: Second valid payload (different device/customer)
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

Invoke-CallbackTest "Test 2" $validPayload2

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Testing Complete!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "`nCheck the payload debugger at:" -ForegroundColor White
Write-Host "https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php" -ForegroundColor Cyan
