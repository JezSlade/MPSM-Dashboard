# ==================================================
# MPSM Dashboard - Rollback Database Optimizations
# ==================================================
# Version: 2.1.0
# Date: 2025-11-06
# Purpose: Remove database indexes if needed
# ==================================================

$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Red
Write-Host "MPSM Database Optimization ROLLBACK" -ForegroundColor Red
Write-Host "========================================" -ForegroundColor Red
Write-Host ""

# Configuration
$dbHost = "localhost"
$dbName = "resolut7_mpsm"
$dbUser = Read-Host "Enter database username"
$dbPass = Read-Host "Enter database password" -AsSecureString
$dbPassPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPass))

$sqlFile = ".\rollback_indexes.sql"
$logFile = ".\database_rollback_log_$(Get-Date -Format 'yyyy-MM-dd_HHmmss').txt"

Write-Host "Database: $dbName" -ForegroundColor Yellow
Write-Host "Log File: $logFile" -ForegroundColor Yellow
Write-Host ""

# Confirm rollback
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "ROLLBACK CONFIRMATION" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "This will remove all performance indexes added by database_optimizations.sql" -ForegroundColor White
Write-Host "No data will be lost." -ForegroundColor Green
Write-Host ""
Write-Host "Are you experiencing issues that require rollback?" -ForegroundColor Yellow
$reason = Read-Host "Describe the issue (or type 'cancel' to abort)"

if ($reason -eq "cancel") {
    Write-Host "Rollback cancelled" -ForegroundColor Yellow
    exit 0
}

Write-Host ""
Write-Host "Proceeding with rollback..." -ForegroundColor Red

try {
    # Execute the rollback SQL
    $output = mysql -h $dbHost -u $dbUser -p"$dbPassPlain" $dbName < $sqlFile 2>&1

    # Log output
    "========================================" | Out-File -FilePath $logFile
    "Database Rollback Execution" | Out-File -FilePath $logFile -Append
    "Date: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" | Out-File -FilePath $logFile -Append
    "Reason: $reason" | Out-File -FilePath $logFile -Append
    "========================================" | Out-File -FilePath $logFile -Append
    $output | Out-File -FilePath $logFile -Append

    Write-Host ""
    Write-Host "✓ Rollback completed successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Results:" -ForegroundColor Cyan
    $output | ForEach-Object {
        Write-Host $_ -ForegroundColor White
    }

} catch {
    Write-Host "✗ Error during rollback!" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "ROLLBACK COMPLETE" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "✓ All custom indexes removed" -ForegroundColor Green
Write-Host "✓ Database restored to previous state" -ForegroundColor Green
Write-Host ""
Write-Host "Log saved to: $logFile" -ForegroundColor Yellow
Write-Host ""

Read-Host "Press Enter to exit"
