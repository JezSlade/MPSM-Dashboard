# === api_call.py ===
# Pulls live data from MPS Monitor API using credentials in .env

import os
import json
import requests
from dotenv import load_dotenv
from auth import TokenManager

# Load environment variables from .env
load_dotenv()

CLIENT_ID = os.getenv("CLIENT_ID")
CLIENT_SECRET = os.getenv("CLIENT_SECRET")
USERNAME = os.getenv("USERNAME")
PASSWORD = os.getenv("PASSWORD")
SCOPE = os.getenv("SCOPE", "account")
TOKEN_URL = os.getenv("TOKEN_URL")
API_ENDPOINT = os.getenv("API_ENDPOINT")

# Initialize token manager using auth.py
manager = TokenManager(
    client_id=CLIENT_ID,
    client_secret=CLIENT_SECRET,
    username=USERNAME,
    password=PASSWORD,
    scope=SCOPE,
    token_url=TOKEN_URL
)

# Function to run API call
def run_api_call():
    try:
        headers = manager.get_headers()
        response = requests.get(API_ENDPOINT, headers=headers)
        response.raise_for_status()
        return {"status": "success", "data": response.json()}
    except Exception as e:
        return {"status": "error", "message": str(e)}

# Execute only when run directly
if __name__ == "__main__":
    result = run_api_call()
    print("Content-Type: application/json\n")
    print(json.dumps(result))
