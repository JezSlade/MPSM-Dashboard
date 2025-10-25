# Upload database.php via FTP
$ftpServer = 'ftp://ftp.resolutionsbydesign.us/cms/config/database.php'
$username = 'mpsm@mpsm.resolutionsbydesign.us'
$password = 'Deploy123!'
$localFile = 'cms/config/database.php'

if (-Not (Test-Path $localFile)) {
    Write-Host 'ERROR: Local database.php file not found'
    exit 1
}

try {
    $webclient = New-Object System.Net.WebClient
    $webclient.Credentials = New-Object System.Net.NetworkCredential($username, $password)
    $webclient.UploadFile($ftpServer, $localFile)
    Write-Host 'SUCCESS: database.php uploaded to cms/config/'
} catch {
    Write-Host "ERROR: $_"
    exit 1
}
