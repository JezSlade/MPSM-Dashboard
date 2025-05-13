import os
import sys
import json
import requests
from dotenv import load_dotenv

load_dotenv()

customer_id = sys.argv[1] if len(sys.argv) > 1 else None
if not customer_id:
    print(json.dumps({"status": "error", "message": "CustomerId not provided"}))
    exit(1)

url = os.getenv("BASE_URL") + "/CustomerDashboard/Devices"
token = os.getenv("ACCESS_TOKEN")

headers = {
    "Authorization": f"Bearer {token}",
    "Content-Type": "application/json"
}

payload = {
    "CustomerId": customer_id
}

try:
    res = requests.post(url, headers=headers, json=payload)
    res.raise_for_status()
    print(json.dumps(res.json()))
except Exception as e:
    print(json.dumps({"status": "error", "message": str(e)}))
