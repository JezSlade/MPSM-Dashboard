import os
import json
import requests
from dotenv import load_dotenv

load_dotenv()

url = os.getenv("BASE_URL") + "/Customer/GetCustomers"
token = os.getenv("ACCESS_TOKEN")

headers = {
    "Authorization": f"Bearer {token}",
    "Content-Type": "application/json"
}

# Keep payload minimal to get all customers
payload = {
    "PageNumber": 1,
    "PageRows": 9999
}

try:
    res = requests.post(url, headers=headers, json=payload)
    res.raise_for_status()
    print(json.dumps(res.json()))
except Exception as e:
    print(json.dumps({"status": "error", "message": str(e)}))
