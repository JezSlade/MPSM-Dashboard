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

    try:
        res = requests.post(url, json=payload)

        # ✅ Write the full response to debug log
        with open("/tmp/token_debug.log", "w") as f:
            f.write(f"Status: {res.status_code}\n")
            f.write("Headers:\n")
            f.write(str(res.headers) + "\n")
            f.write("Body:\n")
            f.write(res.text + "\n")

        res.raise_for_status()

        try:
            return_data = res.json()
        except json.JSONDecodeError:
            raise Exception("API did not return valid JSON. See /tmp/token_debug.log for raw response.")

        token = return_data.get("Token")
        expires_in = return_data.get("ExpiresInSeconds", 1800)

        if not token:
            raise Exception("No token found in response.")

        with open(token_cache_path, "w") as f:
            json.dump({
                "token": token,
                "expires_at": time.time() + expires_in - 30
            }, f)

        return token

    except Exception as e:
        with open("/tmp/token_debug.log", "a") as f:
            f.write("\nERROR: " + str(e) + "\n")
        raise

def get_token():
    if is_token_valid():
        with open(token_cache_path, "r") as f:
            return json.load(f)["token"]
    return fetch_new_token()
