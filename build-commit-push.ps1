# build-deploy.ps1
# Usage: Run from your project root folder.
# Runs npm install, builds React app, copies build contents to root,
# stages and commits changes, then pushes to trigger your FTP GitHub Action.

Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force

# Step 1: npm install (optional but recommended)
Write-Host "Running npm install..."
npm install

# Step 2: Build React app
Write-Host "Running npm run build..."
npm run build

# Step 3: Copy build output files to project root (overwrite existing)
$buildDir = Join-Path -Path $PSScriptRoot -ChildPath "build"
if (-Not (Test-Path $buildDir)) {
    Write-Error "Build folder not found. Build failed?"
    exit 1
}

Write-Host "Copying build contents to project root..."
Get-ChildItem -Path $buildDir -Force | ForEach-Object {
    $targetPath = Join-Path -Path $PSScriptRoot -ChildPath $_.Name
    if (Test-Path $targetPath) {
        # Remove existing file or folder before copy
        Remove-Item -Recurse -Force -Path $targetPath
    }
    Copy-Item -Recurse -Force -Path $_.FullName -Destination $PSScriptRoot
}

# Verify copy success
$indexFile = Join-Path -Path $PSScriptRoot -ChildPath "index.html"
if (-Not (Test-Path $indexFile)) {
    Write-Error "index.html not found in project root after copy."
    exit 1
}

Write-Host "Build files copied successfully."

# Step 4: Git add, commit, push to trigger FTP deploy
Write-Host "Staging all changes..."
git add -A

$commitMessage = "Auto build and deploy $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
Write-Host "Committing with message: $commitMessage"
git commit -m $commitMessage

Write-Host "Pushing to remote..."
git push

Write-Host "Done. Build completed, changes pushed, deployment triggered."
