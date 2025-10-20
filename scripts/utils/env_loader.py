"""
Environment loader - loads .env and returns settings dict without logging secrets.
"""
import os
from pathlib import Path
from typing import Dict, Any


def load_env(env_path: str = None) -> Dict[str, Any]:
    """
    Load environment variables from .env file.

    Args:
        env_path: Optional path to .env file. Defaults to project root.

    Returns:
        Dictionary of settings with typed values.
    """
    if env_path is None:
        # Find project root (where .env is located)
        current = Path(__file__).resolve()
        while current.parent != current:
            env_file = current / '.env'
            if env_file.exists():
                env_path = str(env_file)
                break
            current = current.parent

    if not env_path or not Path(env_path).exists():
        raise FileNotFoundError(f"No .env file found at {env_path}")

    # Parse .env file manually (simple parser)
    settings = {}
    with open(env_path, 'r', encoding='utf-8') as f:
        for line in f:
            line = line.strip()
            # Skip empty lines and comments
            if not line or line.startswith('#'):
                continue

            # Parse KEY=VALUE or KEY="VALUE"
            if '=' in line:
                key, value = line.split('=', 1)
                key = key.strip()
                value = value.strip()

                # Remove quotes if present
                if value.startswith('"') and value.endswith('"'):
                    value = value[1:-1]
                elif value.startswith("'") and value.endswith("'"):
                    value = value[1:-1]

                settings[key] = value

    # Type conversions and structured config
    config = {
        # Application
        'app_name': settings.get('APP_NAME', 'MPS Monitor Dashboard'),
        'app_version': settings.get('APP_VERSION', '1.0.0'),
        'app_base_url': settings.get('APP_BASE_URL', ''),
        'timezone': settings.get('TIMEZONE', 'America/New_York'),

        # API Configuration
        'api_base_url': settings.get('API_BASE_URL', ''),
        'mps_base_url': settings.get('MPS_BASE_URL', ''),

        # OAuth Authentication
        'auth_mode': settings.get('AUTH_MODE', 'oauth_password'),
        'token_url': settings.get('TOKEN_URL', ''),
        'client_id': settings.get('CLIENT_ID', ''),
        'client_secret': settings.get('CLIENT_SECRET', ''),
        'username': settings.get('USERNAME', ''),
        'password': settings.get('PASSWORD', ''),
        'scope': settings.get('SCOPE', 'account'),

        # Dealer Information
        'dealer_code': settings.get('DEALER_CODE', ''),
        'dealer_id': settings.get('DEALER_ID', ''),

        # Engine Configuration
        'mps_timeout': int(settings.get('MPS_TIMEOUT', '30')),
        'mps_connect_timeout': int(settings.get('MPS_CONNECT_TIMEOUT', '10')),
        'mps_max_retries': int(settings.get('MPS_MAX_RETRIES', '3')),
        'mps_debug': settings.get('MPS_DEBUG', 'false').lower() == 'true',

        # Debug Settings
        'debug_mode': settings.get('DEBUG_MODE', '0') == '1',
    }

    return config


def get_secret_keys() -> list:
    """Return list of keys that contain secrets and should never be logged."""
    return [
        'client_secret',
        'password',
        'token',
        'api_key',
        'secret',
        'auth',
        'credential',
    ]


def is_secret_key(key: str) -> bool:
    """Check if a key name suggests it contains a secret."""
    key_lower = key.lower()
    return any(secret in key_lower for secret in get_secret_keys())
