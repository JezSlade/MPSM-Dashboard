import os
import json
import time
import requests
from dotenv import load_dotenv

load_dotenv()

token_cache_path = "/tmp/mpsm_token.json"

def is_token_valid():
    if not os.path.exists(token_cache_path):
        return False
    try:
        with open(token_cache_path, "r") as f:
            data = json.load(f)
            return data.get("expires_at", 0) > time.time()
    except Exception:
        return False

def fetch_new_token():
    url = os.getenv("BASE_URL") + "/Login/Authenticate"
    payload = {
            "Username": os.getenv("USERNAME"),
            "Password": os.getenv("PASSWORD"),
            "DealerCode": os.getenv("DEALER_CODE")
    }
    res = requests.post(url, json=payload)
    res.raise_for_status()
    token = res.json().get("Token")
    expires_in = res.json().get("ExpiresInSeconds", 1800)
    with open(token_cache_path, "w") as f:
        json.dump({
            "token": token,
            "expires_at": time.time() + expires_in - 30  # buffer
        }, f)
    return token

def get_token():
    if is_token_valid():
        with open(token_cache_path, "r") as f:
            return json.load(f)["token"]
    return fetch_new_token()
