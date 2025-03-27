from flask import Flask, jsonify
from auth import TokenManager
import requests
import os
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__)

manager = TokenManager(
    client_id=os.getenv("CLIENT_ID"),
    client_secret=os.getenv("CLIENT_SECRET"),
    username=os.getenv("USERNAME"),
    password=os.getenv("PASSWORD"),
    scope=os.getenv("SCOPE"),
    token_url=os.getenv("TOKEN_URL")
)

API_BASE = "https://api.abassetmanagement.com/api3"

def get_device_stats():
    headers = manager.get_headers()
    try:
        response = requests.post(
            f"{API_BASE}/Device/List",
            headers=headers,
            json={"PageIndex": 0, "PageSize": 1}
        )
        response.raise_for_status()
        total_devices = response.json().get("TotalCount", 0)
        return total_devices
    except Exception as e:
        print("Device stats error:", e)
        return 0

def get_error_count():
    headers = manager.get_headers()
    try:
        response = requests.post(
            f"{API_BASE}/PanelMessageAlert/GetErrorCodes",
            headers=headers,
            json={}
        )
        response.raise_for_status()
        error_codes = response.json()
        return len(error_codes) if isinstance(error_codes, list) else 0
    except Exception as e:
        print("Error count fetch error:", e)
        return 0

@app.route("/api/dashboard-stats")
def dashboard_stats():
    stats = {
        "deviceCount": get_device_stats(),
        "errorCount": get_error_count(),
        "uptime": "99.97%"  # Placeholder
    }
    return jsonify(stats)

if __name__ == "__main__":
    app.run(debug=True)
