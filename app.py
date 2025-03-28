from flask import Flask, jsonify, request
import os

app = Flask(__name__)

# Load data from working_auth.txt and MPSM Data.txt
def load_data(file_path):
    try:
        with open(file_path, 'r') as f:
            return f.read().splitlines()
    except Exception as e:
        return []

@app.route("/api/data", methods=["GET"])
def get_data():
    """
    Endpoint to return the parsed data from the MPSM Data file.
    """
    try:
        data = load_data("MPSM Data.txt")
        # Basic format, could be improved by parsing to JSON objects if required
        return jsonify({"status": "success", "data": data})
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route("/api/auth", methods=["GET"])
def get_auth():
    """
    Endpoint to return the parsed authentication data.
    """
    try:
        auth_data = load_data("working_auth.txt")
        return jsonify({"status": "success", "auth": auth_data})
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

# Error handler for undefined routes
@app.errorhandler(404)
def not_found(e):
    return jsonify({"status": "error", "message": "Route not found"}), 404

if __name__ == "__main__":
    app.run(debug=True)
