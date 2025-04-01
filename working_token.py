#!/usr/bin/env python3
import os
import json
import requests
from dotenv import load_dotenv

# === Load environment variables from .env ===
load_dotenv()
BASE_URL = os.getenv("BASE_URL")
CLIENT_ID = os.getenv("CLIENT_ID")
CLIENT_SECRET = os.getenv("CLIENT_SECRET")
USERNAME = os.getenv("USERNAME")
PASSWORD = os.getenv("PASSWORD")
SCOPE = os.getenv("SCOPE")

# === Helper to build API URLs ===
def build_url(endpoint):
    return BASE_URL.rstrip("/") + "/" + endpoint.lstrip("/")

TOKEN_URL = build_url("token")
DATA_URL = build_url("GetPrinters")

HEADERS_FORM = {
    "Content-Type": "application/x-www-form-urlencoded",
    "Cache-Control": "no-cache"
}

# === Function to get token ===
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
        response = requests.post(TOKEN_URL, headers=HEADERS_FORM, data=payload)
        response.raise_for_status()
        return response.json().get("access_token")
    except Exception as e:
        return None, str(e)

# === Function to call the API with token ===
def fetch_data(token):
    headers = {
        "Authorization": f"bearer {token}",
        "Content-Type": "application/json"
    }
    try:
        response = requests.get(DATA_URL, headers=headers)
        response.raise_for_status()
        return response.json()
    except Exception as e:
        return {"status": "error", "message": f"API call failed: {str(e)}"}

# === MAIN OUTPUT: JSON only ===
if __name__ == "__main__":
    token = get_token()
    if isinstance(token, tuple):
        print(json.dumps({"status": "error", "message": f"Token error: {token[1]}"}))
    elif token:
        data = fetch_data(token)
        print(json.dumps(data, indent=2))
    else:
        print(json.dumps({"status": "error", "message": "Token acquisition failed"}))
