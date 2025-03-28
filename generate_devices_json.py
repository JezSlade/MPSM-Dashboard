import os
import json
import requests
import time
from dotenv import load_dotenv

# === Load environment variables ===
load_dotenv()

CLIENT_ID = os.getenv("CLIENT_ID")
CLIENT_SECRET = os.getenv("CLIENT_SECRET")
USERNAME = os.getenv("USERNAME")
PASSWORD = os.getenv("PASSWORD")
SCOPE = os.getenv("SCOPE", "account")
OUTPUT_PATH = os.getenv("OUTPUT_PATH")

TOKEN_URL = "https://api.abassetmanagement.com/api3/token"
DEVICES_URL = "https://api.abassetmanagement.com/api3/devices"

headers = {
    "Content-Type": "application/x-www-form-urlencoded",
    "Cache-Control": "no-cache"
}

def get_token():
    payload = {
        "client_id": CLIENT_ID,
        "client_secret": CLIENT_SECRET,
        "grant_type": "password",
        "username": USERNAME,
        "password": PASSWORD,
        "scope": SCOPE
    }
    response = requests.post(TOKEN_URL, headers=headers, data=payload)
    response.raise_for_status()
    return response.json()['access_token']

def fetch_devices(token):
    auth_header = {"Authorization": f"Bearer {token}", "Accept": "application/json"}
    response = requests.get(DEVICES_URL, headers=auth_header)
    response.raise_for_status()
    return response.json()

def save_devices(data):
    payload = {
        "timestamp": time.strftime("%Y-%m-%d %H:%M:%S"),
        "devices": data
    }
    with open(OUTPUT_PATH, "w") as f:
        json.dump(payload, f, indent=2)
    print(f"✅ Data saved to {OUTPUT_PATH}")

if __name__ == "__main__":
    try:
        print("🔐 Authenticating...")
        token = get_token()
        print("✅ Token retrieved")

        print("📡 Fetching devices...")
        devices = fetch_devices(token)
        print(f"✅ Retrieved {len(devices)} devices")

        save_devices(devices)

    except Exception as e:
        print(f"❌ ERROR: {e}")
