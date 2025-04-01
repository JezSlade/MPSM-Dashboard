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

# === Constants ===
DEALER_ID = "SZ13qRwU5GtFLj0i_CbEgQ2"  # Static, as approved by Jez
TOKEN_URL = BASE_URL.rstrip("/") + "/token"
DEVICE_LIST_URL = BASE_URL.rstrip("/") + "/Device/List"

HEADERS_FORM = {
    "Content-Type": "application/x-www-form-urlencoded",
    "Cache-Control": "no-cache"
}

# === Request Body for Device/List ===
DEVICE_LIST_PAYLOAD = {
    "FilterDealerId": DEALER_ID,
    "FilterCustomerCodes": None,
    "ProductBrand": None,
    "ProductModel": None,
    "OfficeId": None,
    "Status": 1,
    "FilterText": None,
    "PageNumber": 1,
    "PageRows": 50,
    "SortColumn": "Id",
    "SortOrder": 0
}

# === Function to get access token ===
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

# === Function to POST Device/List ===
def fetch_devices(token):
    headers = {
        "Authorization": f"bearer {token}",
        "Content-Type": "application/json"
    }
    try:
        response = requests.post(DEVICE_LIST_URL, headers=headers, json=DEVICE_LIST_PAYLOAD)
        response.raise_for_status()
        return response.json()
    except Exception as e:
        return {"status": "error", "message": f"Device call failed: {str(e)}", "raw": response.text if 'response' in locals() else None}

# === Main Execution Block ===
if __name__ == "__main__":
    token = get_token()
    if isinstance(token, tuple):
        print(json.dumps({"status": "error", "message": f"Token error: {token[1]}"}))
    elif token:
        data = fetch_devices(token)
        print(json.dumps(data, indent=2))
    else:
        print(json.dumps({"status": "error", "message": "Token acquisition failed"}))
