from flask import Flask, jsonify, send_from_directory
from flask_cors import CORS
from auth import TokenManager
import os
import requests
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__)
CORS(app)

# Initialize TokenManager
token_manager = TokenManager(
    client_id=os.getenv("CLIENT_ID"),
    client_secret=os.getenv("CLIENT_SECRET"),
    username=os.getenv("USERNAME"),
    password=os.getenv("PASSWORD"),
    scope=os.getenv("SCOPE"),
    token_url=os.getenv("TOKEN_URL")
)

API_BASE = "https://api.abassetmanagement.com/api3"

@app.route('/')
def serve_dashboard():
    return send_from_directory('frontend', 'dashboard.html')

@app.route('/api/dashboard-stats')
def dashboard_stats():
    try:
        headers = token_manager.get_headers()

        # Device List
        device_resp = requests.post(f"{API_BASE}/Device/List", headers=headers, json={"PageSize": 1})
        device_data = device_resp.json()

        # PanelMessageAlert List
        alert_resp = requests.post(f"{API_BASE}/PanelMessageAlert/List", headers=headers, json={"PageSize": 1})
        alert_data = alert_resp.json()

        # Get Error Code Definitions
        error_defs = requests.post(f"{API_BASE}/PanelMessageAlert/GetErrorCodes", headers=headers, json={})
        error_data = error_defs.json()

        # Optional: Add more if needed

        return jsonify({
            "Device/List": device_data,
            "PanelMessageAlert/List": alert_data,
            "PanelMessageAlert/GetErrorCodes": error_data,
            "uptime": "99.97%"
        })

    except Exception as e:
        print("[ERROR]", e)
        return jsonify({"error": "Failed to fetch one or more endpoints."}), 500

@app.route('/<path:path>')
def static_proxy(path):
    return send_from_directory('frontend', path)

if __name__ == '__main__':
    app.run(debug=True)
