# FTP Deployment Documentation

## Deployment Setup for GreenGeeks Hosting

This document outlines the automatic FTP deployment configuration for `mpsm.resolutionsbydesign.us`.

### FTP Connection Details
| Setting                | Value                                  |
|------------------------|----------------------------------------|
| **FTP Server**         | `ftp.resolutionsbydesign.us`           |
| **FTP Username**       | `mpsm@mpsm.resolutionsbydesign.us`     |
| **FTP Password**       | [Stored in GitHub Secrets]             |
| **Port**               | `21` (FTP/Explicit FTPS)               |
| **Remote Directory**   | `./`                                   |
| **Connection Mode**    | Passive (required for GreenGeeks)      |

### GitHub Actions Workflow
The deployment is handled by `.github/workflows/deploy.yml`:

```yaml
name: Simple FTP Deploy
on: push
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: sebastianpopp/ftp-action@releases/v2
        with:
          host: ${{ secrets.FTP_SERVER }}
          user: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          localDir: "./"
          remoteDir: ${{ secrets.REMOTE_DIR }}
          passive: true
```

### Required GitHub Secrets
These secrets must be set in Repository Settings > Secrets > Actions:

1. `FTP_SERVER`: `ftp.resolutionsbydesign.us`
2. `FTP_USERNAME`: `mpsm@mpsm.resolutionsbydesign.us`
3. `FTP_PASSWORD`: [Your FTP password]
4. `REMOTE_DIR`: `./`

### Troubleshooting Guide

#### Common Issues
1. **Connection Failures**:
   - Verify credentials work in FileZilla
   - Ensure passive mode is enabled
   - Check GreenGeeks server status

2. **File Permission Errors**:
   - GreenGeeks requires files to be chmod 644
   - Directories should be 755

3. **Deployment Not Triggering**:
   - Check if push was to `main` branch
   - Verify workflow file is in correct location

#### Manual FTP Connection Test
```bash
ftp ftp.resolutionsbydesign.us
# Enter username and password when prompted
cd public_html/mpsm.resolutionsbydesign.us/mpsm
put testfile.txt
```

### Maintenance Notes
- Last configured: {today's date}
- Working action version: `sebastianpopp/ftp-action@releases/v2`
- GreenGeeks-specific requirements: Passive mode only

### Emergency Manual Deployment
If GitHub Actions fails:
1. Connect using FileZilla with above credentials
2. Upload files to the remote directory
3. Set correct permissions:
   - Files: 644
   - Folders: 755
```

## How to Use This File

1. Save this as `ftp_readme.md` in your repository root
2. Replace `{today's date}` with the current date
3. Update any future changes to the workflow or credentials
4. Keep password out of the file (it's stored in GitHub Secrets)

This document contains all the information needed to maintain or recreate the deployment setup, including:
- Current working configuration
- Troubleshooting steps
- Manual fallback procedures
- Complete connection details.