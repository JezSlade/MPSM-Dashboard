# === Dependency Check and Auto-Installer ===
try:
    import flask
    import requests
except ImportError:
    import subprocess
    import sys
    print("🔧 Missing dependencies detected. Attempting to install...")
    subprocess.check_call([sys.executable, "-m", "pip", "install", "Flask", "requests"])

# === Standard Imports ===
from flask import Flask, jsonify, request
import requests
import json
import os
import logging
import time

# === Flask Setup ===
app = Flask(__name__)
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

# === API Endpoint and Credentials ===
API_URL = "https://api.abassetmanagement.com/api3/"
AUTH_FILE = "working_auth.txt"
TOKEN_CACHE_FILE = "token_cache.json"

# === Load Auth Credentials ===
def load_credentials():
    try:
        with open(AUTH_FILE, 'r') as f:
            lines = f.read().splitlines()
            credentials = {}
            for line in lines:
                if '=' in line:
                    key, val = line.split('=', 1)
                    credentials[key.strip()] = val.strip()
            return credentials
    except Exception as e:
        logger.exception("Error reading working_auth.txt")
        raise RuntimeError("Failed to load authentication config.")

# === Request Access Token ===
def request_access_token(credentials):
    try:
        response = requests.post(
            API_URL + "token",
            headers={"Content-Type": "application/json"},
            data=json.dumps({
                "username": credentials["username"],
                "password": credentials["password"]
            })
        )
        response.raise_for_status()
        token_data = response.json()
        token_data['timestamp'] = time.time()
        with open(TOKEN_CACHE_FILE, 'w') as f:
            json.dump(token_data, f)
        return token_data["access_token"]
    except Exception as e:
        logger.exception("Token request failed.")
        raise RuntimeError("Unable to retrieve token from API.")

# === Get Valid Token (from cache or refresh) ===
def get_token():
    credentials = load_credentials()
    try:
        if os.path.exists(TOKEN_CACHE_FILE):
            with open(TOKEN_CACHE_FILE, 'r') as f:
                data = json.load(f)
            if "access_token" in data and time.time() - data["timestamp"] < data.get("expires_in", 900):
                return data["access_token"]
        return request_access_token(credentials)
    except Exception as e:
        raise RuntimeError("Token retrieval failed.")

# === API Proxy Route ===
@app.route('/mpsm/api/devices', methods=["GET"])
def get_devices():
    logger.info("Request received: /mpsm/api/devices")
    try:
        token = get_token()
        headers = {
            "Authorization": f"Bearer {token}",
            "Accept": "application/json"
        }
        response = requests.get(API_URL + "devices", headers=headers)
        response.raise_for_status()
        return jsonify({"status": "success", "data": response.json()})
    except Exception as e:
        logger.exception("Failed to fetch devices from MPS Monitor")
        return jsonify({"status": "error", "message": str(e)}), 500

# === 404 Handler ===
@app.errorhandler(404)
def route_not_found(e):
    return jsonify({"status": "error", "message": "Route not found"}), 404

# === Global Error Handler ===
@app.errorhandler(Exception)
def handle_error(e):
    logger.exception("Unhandled exception")
    return jsonify({"status": "error", "message": str(e)}), 500

# === Run Server ===
if __name__ == "__main__":
    logger.info("Starting Flask app for MPSM Dashboard")
    app.run(debug=True)
