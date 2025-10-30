# Rename all cards/get_*.php → cards/card_*.php
Get-ChildItem -Path .\cards\get_*.php | ForEach-Object {
  $old = $_.FullName
  $name = $_.Name -replace '^get_',''        # strip "get_"
  $newName = "card_$name"                    # add "card_"
  $new   = Join-Path -Path $_.DirectoryName -ChildPath $newName
  Write-Host "Renaming $old -> $new"
  Rename-Item -Path $old -NewName $newName
}
