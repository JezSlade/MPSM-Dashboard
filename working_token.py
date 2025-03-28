import requests
import json
import os
from dotenv import load_dotenv

# === Load environment variables from .env file ===
env_path = os.path.join(os.path.dirname(__file__), '.env')
load_dotenv(dotenv_path=env_path)

CLIENT_ID = os.getenv("CLIENT_ID")
CLIENT_SECRET = os.getenv("CLIENT_SECRET")
USERNAME = os.getenv("USERNAME")
PASSWORD = os.getenv("PASSWORD")
SCOPE = os.getenv("SCOPE")
TOKEN_URL = os.getenv("TOKEN_URL")
API_URL = os.getenv("API_URL")

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
    try:
        response = requests.post(TOKEN_URL, headers=headers, data=payload)
        response.raise_for_status()
        data = response.json()
        return data["access_token"], data["refresh_token"]
    except Exception as e:
        return None, None

def fetch_device_data(access_token):
    headers = {
        "Authorization": f"Bearer {access_token}",
        "Accept": "application/json"
    }
    try:
        response = requests.get(API_URL, headers=headers)
        response.raise_for_status()
        return response.json()
    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    access_token, refresh_token = get_token()
    if not access_token:
        print(json.dumps({"status": "error", "message": "Token acquisition failed"}))
    else:
        data = fetch_device_data(access_token)
        print(json.dumps(data, indent=2))
