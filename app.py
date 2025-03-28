# === app.py ===
# Full Flask app for MPSM Dashboard, Phusion Passenger-compatible
# Loads config from environment variables set in hosting panel

from flask import Flask, render_template, jsonify
import os
import requests
import json
import time
import logging

# === Load config from hosting environment variables ===
FLASK_ENV = os.getenv("FLASK_ENV", "production")
BASE_URL = os.getenv("BASE_URL", "/mpsm")
APP_PATH = os.getenv("APP_PATH", os.getcwd())
API_USERNAME = os.getenv("API_USERNAME")
API_PASSWORD = os.getenv("API_PASSWORD")
API_URL = "https://api.abassetmanagement.com/api3/"
TOKEN_CACHE_FILE = os.path.join(APP_PATH, "token_cache.json")

# === Flask app setup ===
app = Flask(__name__, static_folder="static", template_folder="templates")
logging.basicConfig(level=logging.DEBUG if FLASK_ENV == "development" else logging.INFO)
logger = logging.getLogger(__name__)

# === Helper: Request new access token ===
def request_access_token():
    try:
        logger.info("Requesting new access token...")
        response = requests.post(
            API_URL + "token",
            headers={"Content-Type": "application/json"},
            data=json.dumps({"username": API_USERNAME, "password": API_PASSWORD})
        )
        response.raise_for_status()
        token_data = response.json()
        token_data['timestamp'] = time.time()
        with open(TOKEN_CACHE_FILE, 'w') as f:
            json.dump(token_data, f)
        return token_data['access_token']
    except Exception as e:
        logger.exception("Failed to request token")
        raise RuntimeError("Unable to retrieve token")

# === Helper: Load valid token or refresh ===
def get_token():
    try:
        if os.path.exists(TOKEN_CACHE_FILE):
            with open(TOKEN_CACHE_FILE, 'r') as f:
                data = json.load(f)
                if time.time() - data.get('timestamp', 0) < data.get('expires_in', 900):
                    logger.debug("Using cached token")
                    return data['access_token']
        return request_access_token()
    except Exception as e:
        logger.exception("Token handling error")
        raise RuntimeError("Token retrieval failed")

# === Route: Serve dashboard ===
@app.route(BASE_URL + "/")
def index():
    return render_template("index.html")

# === Route: Proxy device data from MPS Monitor API ===
@app.route(BASE_URL + "/api/devices")
def get_devices():
    try:
        token = get_token()
        headers = {"Authorization": f"Bearer {token}", "Accept": "application/json"}
        response = requests.get(API_URL + "devices", headers=headers)
        response.raise_for_status()
        return jsonify({"status": "success", "data": response.json()})
    except Exception as e:
        logger.exception("Device fetch failed")
        return jsonify({"status": "error", "message": str(e)}), 500

# === Error handlers ===
@app.errorhandler(404)
def not_found(e):
    return jsonify({"status": "error", "message": "Route not found"}), 404

@app.errorhandler(Exception)
def handle_error(e):
    logger.exception("Unhandled exception")
    return jsonify({"status": "error", "message": str(e)}), 500
