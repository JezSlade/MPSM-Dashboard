# === passenger_wsgi.py ===
# Entry point for Phusion Passenger
# This file is required and must expose `application = app`

import sys
import os

# Ensure current directory is in sys.path
sys.path.insert(0, os.path.dirname(__file__))

# Import Flask app instance from app.py
from app import app as application
