import os
import sys
import json
import requests
from dotenv import load_dotenv
from api_token import get_token

load_dotenv()

if len(sys.argv) < 2:
    print(json.dumps({ "status": "error", "message": "Missing CustomerId" }))
    exit(1)

customer_id = sys.argv[1]
url = os.getenv("BASE_URL") + "/CustomerDashboard/Devices"
token = get_token()

headers = {
    "Authorization": f"Bearer {token}",
    "Content-Type": "application/json"
}

payload = { "CustomerId": customer_id }

res = requests.post(url, headers=headers, json=payload)
res.raise_for_status()
print(json.dumps(res.json()))
