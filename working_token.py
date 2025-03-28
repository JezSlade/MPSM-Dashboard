#!/usr/bin/env python3

import subprocess
import sys
import os

# === Auto-install dotenv if missing ===
try:
    from dotenv import load_dotenv
except ImportError:
    subprocess.check_call([sys.executable, "-m", "pip", "install", "--user", "python-dotenv"])
    from dotenv import load_dotenv

import requests
import json

# === Load env vars from .env file ===
env_path = os.path.join(os.path.dirname(__file__), '.env')
if not os.path.exists(env_path):
    print(json.dumps({"status": "error", "message": "Missing .env file"}))
    sys.exit(1)

load_dotenv(dotenv_path=env_path)

# === Required vars ===
CLIENT_ID = os.getenv("CLIENT_ID")
CLIENT_SECRET = os.getenv("CLIENT_SECRET")
USERNAME = os.getenv("USERNAME")
PASSWORD = os.getenv("PASSWORD")
SCOPE = os.getenv("SCOPE")
TOKEN_URL = os.getenv("TOKEN_URL")
API_URL = os.getenv("API_URL")

# === Validate ===
required = [CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD, SCOPE, TOKEN_URL, API_URL]
if not all(required):
    print(json.dumps({"status": "error", "message": "Missing one or more required environment variables"}))
    sys.exit(1)

# === Token Request ===
def get_token():
    headers = {
        "Content-Type": "application/x-www-form-urlencoded",
        "Cache-Control": "no-cache"
    }
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
        return data["access_token"]
    except Exception as e:
        return None, str(e)

# === API Call ===
def fetch_data(token):
    headers = {
        "Authorization": f"Bearer {token}",
        "Accept": "application/json"
    }
    try:
        response = requests.get(API_URL, headers=headers)
        response.raise_for_status()
        return response.json()
    except Exception as e:
        return {"status": "error", "message": str(e)}

# === Main Run ===
if __name__ == "__main__":
    token, error = get_token()
    if not token:
        print(json.dumps({"status": "error", "message": f"Token acquisition failed: {error}"}))
    else:
        result = fetch_data(token)
        print(json.dumps(result, indent=2))
