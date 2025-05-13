import os
import json
import requests
from dotenv import load_dotenv
from api_token import get_token

load_dotenv()

url = os.getenv("BASE_URL") + "/Customer/GetCustomers"
token = get_token()

headers = {
    "Authorization": f"Bearer {token}",
    "Content-Type": "application/json"
}

payload = { "PageNumber": 1, "PageRows": 9999 }

res = requests.post(url, headers=headers, json=payload)
res.raise_for_status()
print(json.dumps(res.json()))
