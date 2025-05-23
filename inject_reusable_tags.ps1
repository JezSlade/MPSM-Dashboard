<#
.SYNOPSIS
    inject_reusable_tags.ps1
    v1.0.4 [Fix: Replace en-dash with ASCII hyphen in Write-Host]

.DESCRIPTION
    Scans every .php file (excluding vendor/.git), 
    ensures every class or function declaration is preceded by a docblock
    containing @reusable. Existing docblocks gain @reusable, and missing ones
    are created—without touching your code logic. Uses explicit foreach
    instead of AddRange to avoid type errors, and now writes a valid
    Write-Host line.

.USAGE
    In your project root PowerShell:
        powershell -ExecutionPolicy Bypass -File .\inject_reusable_tags.ps1
#>

param(
    [string]$RootPath = (Get-Location).Path
)

# --- CONFIGURATION ---
$excludePatterns = @('\\vendor\\', '\\.git\\')  # directories to skip

# --- FUNCTIONS ---
function ShouldSkipFile([string]$path) {
    foreach ($pat in $excludePatterns) {
        if ($path -match $pat) { return $true }
    }
    return $false
}

function Process-PhpFile([string]$filePath) {
    Write-Host "Processing $filePath"
    $lines = Get-Content -Encoding UTF8 $filePath
    $output = New-Object System.Collections.Generic.List[string]
    $inDoc = $false
    $docLines = @()

    for ($i = 0; $i -lt $lines.Count; $i++) {
        $line = $lines[$i]

        # 1) Accumulate docblock lines
        if ($inDoc) {
            $docLines += $line
            if ($line -match '\*/') {
                $inDoc = $false
                # Peek next non-empty line
                $nextIdx = ($i+1..$lines.Count-1 | Where-Object { $lines[$_] -match '\S' } | Select-Object -First 1)
                if ($nextIdx -and $lines[$nextIdx] -match '^\s*(public|protected|private)?\s*(static\s+)?(abstract\s+|final\s+)?(class|function)\s+\w+') {
                    $combined = $docLines -join "`n"
                    if (-not ($combined -match '@reusable')) {
                        for ($j = 0; $j -lt $docLines.Count; $j++) {
                            if ($docLines[$j] -match '\*/') {
                                $indent = ($docLines[$j] -replace '^(.*?\s*)\*/','$1')
                                $docLines.Insert($j, "$indent * @reusable")
                                break
                            }
                        }
                    }
                }
                # Flush docLines via foreach
                foreach ($dl in $docLines) {
                    $output.Add($dl)
                }
                $docLines = @()
            }
            continue
        }

        # 2) Detect start of docblock
        if ($line -match '^\s*/\*\*') {
            $inDoc   = $true
            $docLines = @($line)
            continue
        }

        # 3) Detect class/function without preceding docblock
        if ($line -match '^\s*(public|protected|private)?\s*(static\s+)?(abstract\s+|final\s+)?(class|function)\s+\w+') {
            $prev = $output | Where-Object { $_ -match '\S' } | Select-Object -Last 1
            if (-not ($prev -match '\*/')) {
                $indent = ($line -replace '^(\s*).*$','$1')
                $output.Add("$indent/**")
                $output.Add("$indent * @reusable")
                $output.Add("$indent */")
            }
        }

        # 4) Default: copy line
        $output.Add($line)
    }

    # Write back, preserving line breaks
    $output | Set-Content -Encoding UTF8 $filePath
}

# --- MAIN LOOP ---
Get-ChildItem -Path $RootPath -Recurse -Filter *.php | ForEach-Object {
    if (-not (ShouldSkipFile $_.FullName)) {
        Process-PhpFile $_.FullName
    }
}

Write-Host "`n✅ v1.0.4 complete - @reusable tags injected, AddRange errors resolved."
