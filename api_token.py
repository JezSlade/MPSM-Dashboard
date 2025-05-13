import os
import requests
from dotenv import load_dotenv

load_dotenv()

def get_token():
    url = os.getenv("BASE_URL") + "/Login/Authenticate"
    payload = {
        "Username": os.getenv("USERNAME"),
        "Password": os.getenv("PASSWORD"),
        "DealerCode": os.getenv("DEALER_CODE")
    }

    try:
        res = requests.post(url, json=payload)
        return res.json()["Token"]
    except Exception as e:
        print("Error getting token:")
        print("Status:", res.status_code)
        print("Text:", res.text)
        raise
