#!/usr/bin/env python3
"""
Test a single endpoint manually.
"""
import sys
import json
from pathlib import Path

# Add parent directory to path for imports
sys.path.insert(0, str(Path(__file__).parent))

from utils.env_loader import load_env
from utils.http_client import HTTPClient


def main():
    """Test a single endpoint."""
    # Load config
    config = load_env()
    client = HTTPClient(config)

    # Get token
    print("Getting token...")
    try:
        token = client._get_token()
        print(f"Token: {token[:30]}...")
    except Exception as e:
        print(f"Token error: {e}")
        return 1

    # Test endpoint
    print("\nTesting /AlertLimit/Customer/Get with dealer code...")
    status, data, text = client.request(
        'GET',
        '/AlertLimit/Customer/Get',
        query_params={'code': config['dealer_code']},
        require_auth=True
    )

    print(f"Status: {status}")
    print(f"Response: {json.dumps(data, indent=2)}")

    return 0


if __name__ == '__main__':
    sys.exit(main())
