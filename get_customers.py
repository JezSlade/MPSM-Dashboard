import os
import json
import requests
from dotenv import load_dotenv
from auth_token import get_token

load_dotenv()

url = os.getenv("BASE_URL") + "/Customer/GetCustomers"
headers = {
    "Authorization": f"Bearer {get_token()}",
    "Content-Type": "application/json"
}

payload = {
    "PageNumber": 1,
    "PageRows": 9999
}

try:
    res = requests.post(url, headers=headers, json=payload)
    res.raise_for_status()
    print(json.dumps(res.json()))
except Exception as e:
    print(json.dumps({ "status": "error", "message": str(e) }))
