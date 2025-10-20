"""
Redaction utility - redacts secrets and PII from data structures.
"""
import re
from typing import Any, Dict, List, Union
from .env_loader import is_secret_key


def redact_value(value: Any, context_key: str = '') -> str:
    """
    Redact a value, showing only type and partial info if safe.

    Args:
        value: The value to redact
        context_key: The key name for context (to detect secrets)

    Returns:
        Redacted string representation
    """
    if value is None:
        return 'null'

    # Check if key suggests a secret
    if context_key and is_secret_key(context_key):
        return '<REDACTED>'

    # For strings, check content patterns
    if isinstance(value, str):
        # Check for tokens, keys, passwords (common patterns)
        if len(value) > 20 and re.match(r'^[A-Za-z0-9+/=_-]+$', value):
            # Looks like a token or key
            return f'<REDACTED:{len(value)}chars>'

        # Check for email
        if '@' in value and re.match(r'^[^@]+@[^@]+\.[^@]+$', value):
            parts = value.split('@')
            return f'{parts[0][:2]}***@{parts[1]}'

        # Check for URLs with credentials
        if '://' in value and '@' in value:
            return '<REDACTED:URL_WITH_CREDS>'

        # For IDs, show prefix only
        if context_key and ('id' in context_key.lower() or 'code' in context_key.lower()):
            if len(value) > 8:
                return f'{value[:4]}***{value[-2:]}'

        # Safe string, return as-is if not too long
        if len(value) <= 100:
            return value
        else:
            return f'{value[:50]}...<{len(value)-50} more chars>'

    # Numbers are generally safe
    if isinstance(value, (int, float)):
        return str(value)

    # Booleans
    if isinstance(value, bool):
        return str(value).lower()

    # For other types, show type
    return f'<{type(value).__name__}>'


def redact_dict(data: Dict[str, Any], depth: int = 0, max_depth: int = 10) -> Dict[str, Any]:
    """
    Recursively redact secrets from a dictionary.

    Args:
        data: Dictionary to redact
        depth: Current recursion depth
        max_depth: Maximum recursion depth

    Returns:
        Redacted dictionary
    """
    if depth >= max_depth:
        return {'<truncated>': 'max_depth_reached'}

    redacted = {}
    for key, value in data.items():
        if isinstance(value, dict):
            redacted[key] = redact_dict(value, depth + 1, max_depth)
        elif isinstance(value, list):
            redacted[key] = redact_list(value, depth + 1, max_depth)
        else:
            redacted[key] = redact_value(value, key)

    return redacted


def redact_list(data: List[Any], depth: int = 0, max_depth: int = 10) -> List[Any]:
    """
    Recursively redact secrets from a list.

    Args:
        data: List to redact
        depth: Current recursion depth
        max_depth: Maximum recursion depth

    Returns:
        Redacted list
    """
    if depth >= max_depth:
        return ['<truncated:max_depth_reached>']

    # Limit list size in output
    if len(data) > 10:
        data = data[:10] + [f'<{len(data) - 10} more items>']

    redacted = []
    for item in data:
        if isinstance(item, dict):
            redacted.append(redact_dict(item, depth + 1, max_depth))
        elif isinstance(item, list):
            redacted.append(redact_list(item, depth + 1, max_depth))
        else:
            redacted.append(redact_value(item))

    return redacted


def redact(data: Any) -> Any:
    """
    Main redaction entry point. Redacts secrets from any data structure.

    Args:
        data: Data to redact (dict, list, or primitive)

    Returns:
        Redacted version of the data
    """
    if isinstance(data, dict):
        return redact_dict(data)
    elif isinstance(data, list):
        return redact_list(data)
    else:
        return redact_value(data)


def redact_url(url: str) -> str:
    """
    Redact sensitive parts of a URL (credentials, sensitive query params).

    Args:
        url: URL to redact

    Returns:
        Redacted URL
    """
    # Remove credentials from URL
    if '@' in url and '://' in url:
        protocol, rest = url.split('://', 1)
        if '@' in rest:
            rest = '<REDACTED>@' + rest.split('@', 1)[1]
        url = f'{protocol}://{rest}'

    # Redact common sensitive query parameters
    sensitive_params = ['token', 'key', 'secret', 'password', 'auth', 'apikey', 'api_key']
    for param in sensitive_params:
        # Match param=value or param%3Dvalue
        url = re.sub(
            f'({param}[=])[^&]*',
            r'\1<REDACTED>',
            url,
            flags=re.IGNORECASE
        )

    return url


def redact_headers(headers: Dict[str, str]) -> Dict[str, str]:
    """
    Redact sensitive headers.

    Args:
        headers: Dictionary of HTTP headers

    Returns:
        Redacted headers dictionary
    """
    sensitive_headers = ['authorization', 'x-api-key', 'cookie', 'set-cookie']
    redacted = {}

    for key, value in headers.items():
        key_lower = key.lower()
        if any(sensitive in key_lower for sensitive in sensitive_headers):
            # Show auth type but not value
            if key_lower == 'authorization' and ' ' in value:
                auth_type = value.split(' ', 1)[0]
                redacted[key] = f'{auth_type} <REDACTED>'
            else:
                redacted[key] = '<REDACTED>'
        else:
            redacted[key] = value

    return redacted
