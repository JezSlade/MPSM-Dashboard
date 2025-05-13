import os
import json
import requests
from dotenv import load_dotenv

load_dotenv()

auth_url = os.getenv("BASE_URL") + "/Login/Authenticate"
data_url = os.getenv("BASE_URL") + "/Customer/GetCustomers"

# Step 1: Get auth token
auth_payload = {
    "Username": os.getenv("USERNAME"),
    "Password": os.getenv("PASSWORD"),
    "DealerCode": os.getenv("DEALER_CODE")
}

try:
    auth_res = requests.post(auth_url, json=auth_payload)
    auth_res.raise_for_status()
    token = auth_res.json().get("Token")
except Exception as e:
    print(json.dumps({"status": "error", "message": f"Auth failed: {str(e)}"}))
    exit(1)

# Step 2: Use token to get customer list
headers = {
    "Authorization": f"Bearer {token}",
    "Content-Type": "application/json"
}

payload = {
    "PageNumber": 1,
    "PageRows": 9999
}

try:
    res = requests.post(data_url, headers=headers, json=payload)
    res.raise_for_status()
    print(json.dumps(res.json()))
except Exception as e:
    print(json.dumps({"status": "error", "message": f"Data call failed: {str(e)}"}))
