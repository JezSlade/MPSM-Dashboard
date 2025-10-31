import urllib.request
import json
import sys

dealer_code = "NY06AGDWUQ"
customer_code = "W9OPXL0YDK"

# Load cookies
import http.cookiejar
cj = http.cookiejar.MozillaCookieJar()

# Read curl cookies
cookie_content = open('C:/tmp/cookies.txt' if sys.platform == 'win32' else '/tmp/cookies.txt').read()
# Parse Netscape format
for line in cookie_content.split('\n'):
    if line.strip() and not line.startswith('#'):
        parts = line.split('\t')
        if len(parts) >= 7:
            domain, _, path, secure, _, name, value = parts[:7]
            cookie = http.cookiejar.Cookie(
                version=0, name=name, value=value,
                port=None, port_specified=False,
                domain=domain, domain_specified=True, domain_initial_dot=False,
                path=path, path_specified=True,
                secure=(secure == 'TRUE'),
                expires=None, discard=True,
                comment=None, comment_url=None,
                rest={}, rfc2109=False
            )
            cj.set_cookie(cookie)

opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

print(f"Searching customer {customer_code} for EB821...")
print("=" * 60)

page = 1
total_devices = 0
found = False

while page <= 10:  # Check up to 10 pages (2000 devices max)
    url = f'https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?pageRows=200&pageNumber={page}&customerCode={customer_code}&dealerCode={dealer_code}'

    try:
        response = opener.open(url)
        data = json.loads(response.read())

        devices = data.get('devices', [])
        if not devices:
            print(f"Page {page}: No more devices")
            break

        total_devices += len(devices)

        # Search for EB821
        for device in devices:
            ext_id = (device.get('ExternalIdentifier') or '').upper()
            if 'EB821' in ext_id:
                print(f"\n*** FOUND EB821 on page {page}! ***")
                print(f"  ExternalIdentifier: {device.get('ExternalIdentifier')}")
                print(f"  SerialNumber: {device.get('SerialNumber')}")
                print(f"  IsOffline: {device.get('IsOffline')}")
                print(f"  Customer: {device.get('CustomerDescription')}")
                print(f"  Install Date: {device.get('Install')}")
                print(f"  Uninstall Date: {device.get('Uninstall')}")
                found = True
                break

        if found:
            break

        if len(devices) < 200:
            print(f"Page {page}: Last page ({len(devices)} devices)")
            break

        page += 1

    except Exception as e:
        print(f"Error on page {page}: {e}")
        break

print(f"\nTotal devices checked: {total_devices}")
if not found:
    print("EB821 NOT FOUND in any page")
