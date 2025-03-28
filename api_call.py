 import sys
import os
import requests
import json
from dotenv import load_dotenv
from auth import TokenManager

load_dotenv()

API_BASE = os.getenv("API_ENDPOINT", "https://api.abassetmanagement.com/api3/")
endpoint = sys.argv[1] if len(sys.argv) > 1 else ""

if not endpoint:
    print(json.dumps({"status": "error", "message": "No endpoint provided"}))
    sys.exit(1)

CLIENT_ID = os.getenv("CLIENT_ID")
CLIENT_SECRET = os.getenv("CLIENT_SECRET")
USERNAME = os.getenv("USERNAME")
PASSWORD = os.getenv("PASSWORD")
SCOPE = os.getenv("SCOPE")
TOKEN_URL = os.getenv("TOKEN_URL")

token_mgr = TokenManager(
    client_id=CLIENT_ID,
    client_secret=CLIENT_SECRET,
    username=USERNAME,
    password=PASSWORD,
    scope=SCOPE,
    token_url=TOKEN_URL
)

headers = token_mgr.get_headers()

try:
    res = requests.get(API_BASE + endpoint, headers=headers)
    res.raise_for_status()
    print(json.dumps(res.json()))
except Exception as e:
    print(json.dumps({"status": "error", "message": str(e)}))
