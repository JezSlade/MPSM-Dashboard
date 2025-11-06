# ==================================================
# MPSM Dashboard - Restore Dead Code (Rollback)
# ==================================================
# Version: 2.1.0
# Date: 2025-11-06
# Purpose: Restore .bak files if issues detected
# ==================================================

$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Red
Write-Host "MPSM Dashboard - Restore Dead Code" -ForegroundColor Red
Write-Host "========================================" -ForegroundColor Red
Write-Host ""

# Find most recent backup directory
$backupDirs = Get-ChildItem -Path ".\backups" -Directory | Where-Object { $_.Name -like "dead_code_*" } | Sort-Object LastWriteTime -Descending

if ($backupDirs.Count -eq 0) {
    Write-Host "✗ No backup directories found!" -ForegroundColor Red
    Write-Host "Looking for: .\backups\dead_code_*" -ForegroundColor Yellow
    exit 1
}

$latestBackup = $backupDirs[0].FullName
$manifestFile = Join-Path $latestBackup "manifest.json"

Write-Host "Latest backup found: $latestBackup" -ForegroundColor Yellow
Write-Host ""

if (-not (Test-Path $manifestFile)) {
    Write-Host "✗ Manifest file not found: $manifestFile" -ForegroundColor Red
    exit 1
}

# Load manifest
$manifest = Get-Content $manifestFile | ConvertFrom-Json

Write-Host "Files to restore:" -ForegroundColor Cyan
foreach ($item in $manifest) {
    Write-Host "  • $($item.BackupFile) → $($item.OriginalFile)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Why are you restoring these files?" -ForegroundColor Yellow
$reason = Read-Host "Describe the issue"

Write-Host ""
$confirm = Read-Host "Proceed with restore? (yes/no)"

if ($confirm -ne "yes") {
    Write-Host "Restore cancelled" -ForegroundColor Yellow
    exit 0
}

Write-Host ""
Write-Host "Restoring files..." -ForegroundColor Cyan

$restoredCount = 0
foreach ($item in $manifest) {
    $bakFile = $item.BackupFile
    $originalFile = $item.OriginalFile

    if (Test-Path $bakFile) {
        try {
            Rename-Item $bakFile $originalFile -Force
            Write-Host "✓ Restored: $bakFile → $originalFile" -ForegroundColor Green
            $restoredCount++
        } catch {
            Write-Host "✗ Failed to restore: $bakFile" -ForegroundColor Red
            Write-Host "  Error: $($_.Exception.Message)" -ForegroundColor Red
        }
    } else {
        Write-Host "⊘ Backup not found: $bakFile" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "RESTORE COMPLETE" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Files restored: $restoredCount" -ForegroundColor Green
Write-Host "Reason: $reason" -ForegroundColor Yellow
Write-Host ""
Write-Host "System should now be back to pre-removal state" -ForegroundColor Green
Write-Host ""

Read-Host "Press Enter to exit"
