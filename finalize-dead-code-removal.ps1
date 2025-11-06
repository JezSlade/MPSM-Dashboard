# ==================================================
# MPSM Dashboard - Finalize Dead Code Removal
# ==================================================
# Version: 2.1.0
# Date: 2025-11-06
# Purpose: Permanently delete .bak files after successful testing
# ==================================================

$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "MPSM Dashboard - Finalize Removal" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Find .bak files in cms/api
$bakFiles = Get-ChildItem -Path "cms\api" -Filter "*.bak" -File

if ($bakFiles.Count -eq 0) {
    Write-Host "✓ No .bak files found - cleanup already completed!" -ForegroundColor Green
    exit 0
}

Write-Host ".bak files found:" -ForegroundColor Yellow
foreach ($file in $bakFiles) {
    $size = $file.Length
    Write-Host "  • $($file.Name) ($size bytes)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "FINAL CONFIRMATION REQUIRED" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "This will PERMANENTLY DELETE the .bak files above." -ForegroundColor Red
Write-Host "This action CANNOT be undone!" -ForegroundColor Red
Write-Host ""
Write-Host "Before proceeding, confirm:" -ForegroundColor Yellow
Write-Host "  ✓ System has been tested for 24-48 hours" -ForegroundColor White
Write-Host "  ✓ No 404 errors detected" -ForegroundColor White
Write-Host "  ✓ Dashboard works normally" -ForegroundColor White
Write-Host "  ✓ Device search works" -ForegroundColor White
Write-Host "  ✓ Panel messages work" -ForegroundColor White
Write-Host ""

$confirm1 = Read-Host "Have you completed testing? (yes/no)"
if ($confirm1 -ne "yes") {
    Write-Host "Please complete testing first" -ForegroundColor Yellow
    exit 0
}

$confirm2 = Read-Host "Are you sure you want to permanently delete? (type DELETE to confirm)"
if ($confirm2 -ne "DELETE") {
    Write-Host "Operation cancelled" -ForegroundColor Yellow
    exit 0
}

Write-Host ""
Write-Host "Deleting files..." -ForegroundColor Red

$deletedCount = 0
$deletedSize = 0

foreach ($file in $bakFiles) {
    try {
        $size = $file.Length
        Remove-Item $file.FullName -Force
        Write-Host "✓ Deleted: $($file.Name) ($size bytes)" -ForegroundColor Green
        $deletedCount++
        $deletedSize += $size
    } catch {
        Write-Host "✗ Failed to delete: $($file.Name)" -ForegroundColor Red
        Write-Host "  Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

$deletedSizeKB = [math]::Round($deletedSize / 1024, 2)

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "DEAD CODE REMOVAL COMPLETE!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "  • Files deleted: $deletedCount" -ForegroundColor Green
Write-Host "  • Space freed: $deletedSizeKB KB" -ForegroundColor Green
Write-Host ""
Write-Host "Code cleanup complete!" -ForegroundColor Green
Write-Host "Repository is now cleaner and easier to maintain." -ForegroundColor White
Write-Host ""

# Log the deletion
$logFile = ".\dead_code_removal_finalized_$(Get-Date -Format 'yyyy-MM-dd_HHmmss').txt"
$logContent = @"
========================================
MPSM Dashboard - Dead Code Removal Finalized
========================================
Date: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Action: Permanent deletion of obsolete files

Files Deleted: $deletedCount
Space Freed: $deletedSizeKB KB

Deleted Files:
$($bakFiles | ForEach-Object { "  • $($_.Name) ($($_.Length) bytes)" } | Out-String)

========================================
"@

$logContent | Out-File $logFile
Write-Host "Log saved to: $logFile" -ForegroundColor Yellow
Write-Host ""

Read-Host "Press Enter to continue to Phase 3 (Cache Consolidation)"
