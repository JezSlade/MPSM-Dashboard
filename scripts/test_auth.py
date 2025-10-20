#!/usr/bin/env python3
"""
Test authentication to verify OAuth flow works.
"""
import sys
from pathlib import Path

# Add parent directory to path for imports
sys.path.insert(0, str(Path(__file__).parent))

from utils.env_loader import load_env
from utils.http_client import HTTPClient


def main():
    """Test authentication."""
    print("Testing OAuth authentication...")

    # Load config
    config = load_env()
    print(f"Token URL: {config['token_url']}")
    print(f"Base URL: {config['mps_base_url']}")
    print(f"Client ID: {config['client_id'][:10]}...")
    print(f"Username: {config['username']}")

    # Create HTTP client
    client = HTTPClient(config)

    # Try to get a token
    print("\nAttempting to fetch OAuth token...")
    try:
        token = client._get_token()
        print(f"[OK] Token acquired successfully!")
        print(f"Token (first 20 chars): {token[:20]}...")
        print(f"Token length: {len(token)} chars")

        # Try a simple API call
        print("\nTesting simple API call...")
        status, data, text = client.request('GET', '/Dealer/Get', query_params={'code': config['dealer_code']})

        print(f"Status: {status}")
        if status == 200:
            print(f"[OK] API call successful!")
            print(f"Response keys: {list(data.keys())[:5]}")
        else:
            print(f"[X] API call failed")
            print(f"Response: {text[:200]}")

    except Exception as e:
        print(f"[X] Error: {e}")
        return 1

    return 0


if __name__ == '__main__':
    sys.exit(main())
