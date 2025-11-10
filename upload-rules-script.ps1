$ftpUrl = "ftp://ftp.resolutionsbydesign.us/create-live-rules.php"
$localFile = "C:\Users\jez.slade\Desktop\Projects\MPSM-Dashboard\create-live-rules.php"
$ftpUsername = "mpsm@mpsm.resolutionsbydesign.us"
$ftpPassword = "T3!-@D47XN=b"

$ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUrl)
$ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUsername, $ftpPassword)
$ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
$ftpRequest.UseBinary = $true
$ftpRequest.KeepAlive = $false

$fileContent = [System.IO.File]::ReadAllBytes($localFile)
$ftpRequest.ContentLength = $fileContent.Length

$requestStream = $ftpRequest.GetRequestStream()
$requestStream.Write($fileContent, 0, $fileContent.Length)
$requestStream.Close()

$response = $ftpRequest.GetResponse()
Write-Host "Upload complete: $($response.StatusDescription)"
$response.Close()
