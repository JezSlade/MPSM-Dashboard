#!/usr/bin/env python3
import os
import json
import requests

# === Manual .env loader ===
def load_env(env_path=".env"):
    if not os.path.exists(env_path):
        return {}
    with open(env_path) as f:
        lines = f.read().splitlines()
    return {
        k.strip(): v.strip()
        for k, v in (
            line.split("=", 1)
            for line in lines
            if line and not line.startswith("#") and "=" in line
        )
    }

# === Load environment config ===
env = load_env()
CLIENT_ID = env.get("CLIENT_ID")
CLIENT_SECRET = env.get("CLIENT_SECRET")
USERNAME = env.get("USERNAME")
PASSWORD = env.get("PASSWORD")
SCOPE = env.get("SCOPE")
TOKEN_URL = env.get("TOKEN_URL")
API_URL = env.get("API_URL")

# === Sanity check ===
required = [CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD, SCOPE, TOKEN_URL, API_URL]
if not all(required):
    print(json.dumps({"status": "error", "message": "Missing one or more required environment variables"}))
    exit()

def get_token():
    payload = {
        "client_id": CLIENT_ID,
        "client_secret": CLIENT_SECRET,
        "grant_type": "password",
        "username": USERNAME,
        "password": PASSWORD,
        "scope": SCOPE
    }
    headers = {
        "Content-Type": "application/x-www-form-urlencoded",
        "Cache-Control": "no-cache"
    }
    try:
        response = requests.post(TOKEN_URL, headers=headers, data=payload)
        response.raise_for_status()
        data = response.json()
        return data.get("access_token")
    except Exception as e:
        return None, str(e)

def fetch_data(token):
    headers = {
        "Authorization": f"bearer {token}",
        "Accept": "application/json"
    }
    try:
        response = requests.get(API_URL, headers=headers)
        response.raise_for_status()
        return response.json()
    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    token = get_token()
    if isinstance(token, tuple):
        # (None, error) tuple returned
        _, error = token
        print(json.dumps({"status": "error", "message": f"Token acquisition failed: {error}"}))
    else:
        data = fetch_data(token)
        print(json.dumps(data, indent=2))
