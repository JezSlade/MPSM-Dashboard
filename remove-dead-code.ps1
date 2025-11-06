# ==================================================
# MPSM Dashboard - Remove Dead Code (Phase 2)
# ==================================================
# Version: 2.1.0
# Date: 2025-11-06
# Purpose: Safely remove obsolete/redundant API files
# Strategy: Rename to .bak first, test for 24h, then delete
# ==================================================

$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "MPSM Dashboard - Dead Code Removal" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Files to remove (identified in audit)
$filesToRemove = @(
    "cms\api\refresh-cache.php",
    "cms\api\refresh-cache-v2.php",
    "cms\api\refresh-cache-cron.php",
    "cms\api\get-all-devices-all-customers.php",
    "cms\api\get-all-customers-devices.php"
)

$logFile = ".\dead_code_removal_log_$(Get-Date -Format 'yyyy-MM-dd_HHmmss').txt"
$backupDir = ".\backups\dead_code_$(Get-Date -Format 'yyyy-MM-dd_HHmmss')"

Write-Host "Strategy: Safe Rename-First Approach" -ForegroundColor Yellow
Write-Host "1. Rename files to .bak extension" -ForegroundColor White
Write-Host "2. Test system for 24-48 hours" -ForegroundColor White
Write-Host "3. If no issues, permanently delete" -ForegroundColor White
Write-Host "4. If issues, easily restore from .bak" -ForegroundColor White
Write-Host ""

# Create backup directory
if (-not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
    Write-Host "✓ Created backup directory: $backupDir" -ForegroundColor Green
}

Write-Host ""
Write-Host "Files to be marked as obsolete:" -ForegroundColor Cyan
foreach ($file in $filesToRemove) {
    if (Test-Path $file) {
        $size = (Get-Item $file).Length
        Write-Host "  • $file ($size bytes)" -ForegroundColor Yellow
    } else {
        Write-Host "  • $file (NOT FOUND)" -ForegroundColor Gray
    }
}

Write-Host ""
$confirm = Read-Host "Proceed with renaming these files to .bak? (yes/no)"

if ($confirm -ne "yes") {
    Write-Host "Operation cancelled" -ForegroundColor Yellow
    exit 0
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "STEP 1: Creating Backups" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

$renamedCount = 0
$skippedCount = 0
$backupManifest = @()

foreach ($file in $filesToRemove) {
    if (Test-Path $file) {
        try {
            # Copy to backup directory first
            $fileName = Split-Path $file -Leaf
            $backupPath = Join-Path $backupDir $fileName
            Copy-Item $file $backupPath -Force

            # Rename original to .bak
            $bakFile = "$file.bak"
            Rename-Item $file $bakFile -Force

            Write-Host "✓ Renamed: $file → $bakFile" -ForegroundColor Green

            # Add to manifest
            $backupManifest += [PSCustomObject]@{
                OriginalFile = $file
                BackupFile = $bakFile
                BackupCopy = $backupPath
                Size = (Get-Item $bakFile).Length
                RenamedAt = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
            }

            $renamedCount++

        } catch {
            Write-Host "✗ Failed to rename: $file" -ForegroundColor Red
            Write-Host "  Error: $($_.Exception.Message)" -ForegroundColor Red
        }
    } else {
        Write-Host "⊘ Skipped (not found): $file" -ForegroundColor Gray
        $skippedCount++
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "STEP 2: Verify System Functionality" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Files have been renamed to .bak extension" -ForegroundColor Green
Write-Host "They are no longer accessible by the application" -ForegroundColor Yellow
Write-Host ""
Write-Host "IMPORTANT: Test the system now!" -ForegroundColor Red
Write-Host ""
Write-Host "Required Tests:" -ForegroundColor Cyan
Write-Host "1. Visit: https://mpsm.resolutionsbydesign.us/cms/" -ForegroundColor White
Write-Host "2. Login and verify dashboard loads" -ForegroundColor White
Write-Host "3. Search for a device" -ForegroundColor White
Write-Host "4. Open device deep-dive modal" -ForegroundColor White
Write-Host "5. Check browser console for 404 errors" -ForegroundColor White
Write-Host "6. Check panel message monitor" -ForegroundColor White
Write-Host ""

# Save manifest
$manifestFile = Join-Path $backupDir "manifest.json"
$backupManifest | ConvertTo-Json | Out-File $manifestFile

Write-Host "Backup manifest saved to: $manifestFile" -ForegroundColor Green
Write-Host ""

# Save log
$logContent = @"
========================================
MPSM Dashboard - Dead Code Removal Log
========================================
Date: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Action: Rename files to .bak (safe mode)

Files Renamed: $renamedCount
Files Skipped: $skippedCount
Total Files: $($filesToRemove.Count)

Backup Directory: $backupDir
Manifest File: $manifestFile

========================================
Renamed Files:
========================================
$($backupManifest | ForEach-Object { "$($_.OriginalFile) → $($_.BackupFile)" } | Out-String)

========================================
Next Steps:
========================================
1. Test system for 24-48 hours
2. Monitor error logs for 404s
3. If no issues, run: .\finalize-dead-code-removal.ps1
4. If issues, run: .\restore-dead-code.ps1

========================================
"@

$logContent | Out-File $logFile
Write-Host "Log saved to: $logFile" -ForegroundColor Yellow
Write-Host ""

Write-Host "========================================" -ForegroundColor Green
Write-Host "PHASE 2 STEP 1 COMPLETE" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "  • Files renamed: $renamedCount" -ForegroundColor Green
Write-Host "  • Files skipped: $skippedCount" -ForegroundColor Yellow
Write-Host "  • Backups created: $backupDir" -ForegroundColor Green
Write-Host ""
Write-Host "Status: TESTING PHASE (24-48 hours)" -ForegroundColor Yellow
Write-Host ""
Write-Host "To restore if needed:" -ForegroundColor Red
Write-Host "  .\restore-dead-code.ps1" -ForegroundColor White
Write-Host ""
Write-Host "To permanently delete after testing:" -ForegroundColor Green
Write-Host "  .\finalize-dead-code-removal.ps1" -ForegroundColor White
Write-Host ""

Read-Host "Press Enter to continue"
