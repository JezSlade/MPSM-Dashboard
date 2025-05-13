import os
import requests
from dotenv import load_dotenv

load_dotenv()

def get_token():
    auth_url = os.getenv("BASE_URL") + "/Login/Authenticate"
    payload = {
        "Username": os.getenv("USERNAME"),
        "Password": os.getenv("PASSWORD"),
        "DealerCode": os.getenv("DEALER_CODE")
    }
    res = requests.post(auth_url, json=payload)
    res.raise_for_status()
    return res.json().get("Token")
