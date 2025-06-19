# Create a zip file of all project files including hidden files
param(
    [string]$ZipName = "project-backup-$(Get-Date -Format 'yyyy-MM-dd-HHmm').zip"
)

# Get the current directory (project root)
$ProjectRoot = Get-Location
$ZipPath = Join-Path $ProjectRoot $ZipName

# Remove existing zip if it exists
if (Test-Path $ZipPath) {
    Remove-Item $ZipPath -Force
    Write-Host "Removed existing zip file: $ZipName" -ForegroundColor Yellow
}

Write-Host "Creating zip archive: $ZipName" -ForegroundColor Green
Write-Host "Source directory: $ProjectRoot" -ForegroundColor Cyan

try {
    # Create zip archive including hidden files and directories
    # Using System.IO.Compression.FileSystem
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    
    # Get all items including hidden ones, but exclude the zip file itself
    $AllItems = Get-ChildItem -Path $ProjectRoot -Recurse -Force | Where-Object { 
        $_.FullName -ne $ZipPath 
    }
    
    # Create the zip file
    $Zip = [System.IO.Compression.ZipFile]::Open($ZipPath, 'Create')
    
    foreach ($Item in $AllItems) {
        if (-not $Item.PSIsContainer) {
            # Calculate relative path
            $RelativePath = $Item.FullName.Substring($ProjectRoot.Path.Length + 1)
            
            # Replace backslashes with forward slashes for zip compatibility
            $RelativePath = $RelativePath.Replace('\', '/')
            
            # Add file to zip
            [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($Zip, $Item.FullName, $RelativePath)
            Write-Host "  Added: $RelativePath" -ForegroundColor Gray
        }
    }
    
    $Zip.Dispose()
    
    # Get zip file size
    $ZipSize = [math]::Round((Get-Item $ZipPath).Length / 1MB, 2)
    $FileCount = $AllItems | Where-Object { -not $_.PSIsContainer } | Measure-Object | Select-Object -ExpandProperty Count
    
    Write-Host "`nZip created successfully!" -ForegroundColor Green
    Write-Host "File: $ZipName" -ForegroundColor White
    Write-Host "Size: $ZipSize MB" -ForegroundColor White
    Write-Host "Files archived: $FileCount" -ForegroundColor White
    
} catch {
    Write-Error "Failed to create zip file: $($_.Exception.Message)"
    if ($Zip) { $Zip.Dispose() }
    if (Test-Path $ZipPath) { Remove-Item $ZipPath -Force }
}