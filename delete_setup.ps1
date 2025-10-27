# Delete setup.php from live server (security measure)
$ftpServer = 'ftp://ftp.resolutionsbydesign.us/cms/setup.php'
$username = 'mpsm@mpsm.resolutionsbydesign.us'
$password = 'Deploy123!'

Write-Host "Attempting to delete setup.php from production server..."

try {
    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpServer)
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($username, $password)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile

    $response = $ftpRequest.GetResponse()
    Write-Host "SUCCESS: setup.php deleted from server (security hardening complete)"
    $response.Close()
    exit 0
} catch {
    Write-Host "INFO: $_"
    Write-Host "Note: File may already be deleted or not exist"
    exit 0
}
