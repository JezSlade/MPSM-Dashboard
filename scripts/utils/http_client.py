"""
HTTP client with retries, backoff, and rate-limit handling.
"""
import time
import random
import json
from typing import Dict, Any, Optional, Tuple
from urllib.request import Request, urlopen
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode


class HTTPClient:
    """HTTP client with built-in retry logic and rate limiting."""

    def __init__(self, config: Dict[str, Any]):
        """
        Initialize HTTP client.

        Args:
            config: Configuration dictionary from env_loader
        """
        self.config = config
        self.connect_timeout = config.get('mps_connect_timeout', 10)
        self.read_timeout = config.get('mps_timeout', 60)
        self.max_retries = config.get('mps_max_retries', 3)
        self.base_url = config.get('mps_base_url', '').rstrip('/')
        self._token = None
        self._token_expires_at = 0

    def _should_retry(self, status_code: int, attempt: int) -> bool:
        """
        Determine if a request should be retried based on status code.

        Args:
            status_code: HTTP status code
            attempt: Current attempt number (0-indexed)

        Returns:
            True if should retry
        """
        if attempt >= self.max_retries:
            return False

        # Retry on transient errors
        transient_codes = [408, 429, 500, 502, 503, 504]
        return status_code in transient_codes

    def _get_backoff_delay(self, attempt: int) -> float:
        """
        Calculate exponential backoff delay with jitter.

        Args:
            attempt: Current attempt number (0-indexed)

        Returns:
            Delay in seconds
        """
        # Exponential backoff: 2^attempt seconds
        base_delay = min(2 ** attempt, 60)  # Cap at 60 seconds

        # Add jitter (random 0-50% of base delay)
        jitter = random.uniform(0, base_delay * 0.5)

        return base_delay + jitter

    def _get_token(self) -> str:
        """
        Get OAuth token, fetching a new one if needed.

        Returns:
            Bearer token string

        Raises:
            Exception if authentication fails
        """
        # Check if we have a valid token
        if self._token and time.time() < self._token_expires_at:
            return self._token

        # Fetch new token
        token_url = self.config.get('token_url', '')
        if not token_url:
            raise ValueError("No token_url configured")

        data = {
            'grant_type': 'password',
            'client_id': self.config.get('client_id', ''),
            'client_secret': self.config.get('client_secret', ''),
            'username': self.config.get('username', ''),
            'password': self.config.get('password', ''),
            'scope': self.config.get('scope', 'account'),
        }

        body = urlencode(data).encode('utf-8')
        headers = {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
        }

        req = Request(token_url, data=body, headers=headers, method='POST')

        try:
            with urlopen(req, timeout=self.connect_timeout) as response:
                response_data = json.loads(response.read().decode('utf-8'))

                if 'access_token' not in response_data:
                    raise ValueError("No access_token in response")

                self._token = response_data['access_token']

                # Set expiration (default to 1 hour if not provided)
                expires_in = response_data.get('expires_in', 3600)
                # Refresh 5 minutes before actual expiration
                self._token_expires_at = time.time() + expires_in - 300

                return self._token

        except HTTPError as e:
            error_body = e.read().decode('utf-8') if e.fp else 'No error body'
            raise Exception(f"Token fetch failed: {e.code} {e.reason} - {error_body}")
        except URLError as e:
            raise Exception(f"Token fetch network error: {e.reason}")

    def request(
        self,
        method: str,
        path: str,
        query_params: Optional[Dict[str, Any]] = None,
        body: Optional[Dict[str, Any]] = None,
        headers: Optional[Dict[str, str]] = None,
        require_auth: bool = True,
    ) -> Tuple[int, Dict[str, Any], str]:
        """
        Make an HTTP request with retries.

        Args:
            method: HTTP method (GET, POST, etc.)
            path: API path (will be appended to base_url)
            query_params: Optional query parameters
            body: Optional request body (will be JSON encoded)
            headers: Optional additional headers
            require_auth: Whether to include OAuth token

        Returns:
            Tuple of (status_code, response_data, raw_response_text)

        Raises:
            Exception on non-recoverable errors
        """
        # Build URL
        url = f"{self.base_url}/{path.lstrip('/')}"
        if query_params:
            # Filter out None values
            filtered_params = {k: v for k, v in query_params.items() if v is not None}
            if filtered_params:
                url += '?' + urlencode(filtered_params)

        # Build headers
        req_headers = {
            'Accept': 'application/json',
            'User-Agent': 'MPSM-Discovery/1.0',
        }

        if headers:
            req_headers.update(headers)

        # Add auth if required
        if require_auth:
            token = self._get_token()
            req_headers['Authorization'] = f'Bearer {token}'

        # Build request body
        req_body = None
        if body is not None:
            req_body = json.dumps(body).encode('utf-8')
            req_headers['Content-Type'] = 'application/json'

        # Retry loop
        last_error = None
        for attempt in range(self.max_retries + 1):
            start_time = time.time()

            try:
                req = Request(url, data=req_body, headers=req_headers, method=method)

                with urlopen(req, timeout=self.read_timeout) as response:
                    latency_ms = int((time.time() - start_time) * 1000)
                    status_code = response.getcode()
                    response_text = response.read().decode('utf-8')

                    # Try to parse as JSON
                    try:
                        response_data = json.loads(response_text) if response_text else {}
                    except json.JSONDecodeError:
                        response_data = {'_raw': response_text}

                    return status_code, response_data, response_text

            except HTTPError as e:
                latency_ms = int((time.time() - start_time) * 1000)
                status_code = e.code
                error_text = e.read().decode('utf-8') if e.fp else ''

                # Try to parse error response
                try:
                    error_data = json.loads(error_text) if error_text else {}
                except json.JSONDecodeError:
                    error_data = {'error': error_text or e.reason}

                # Check if we should retry
                if self._should_retry(status_code, attempt):
                    delay = self._get_backoff_delay(attempt)
                    time.sleep(delay)
                    last_error = (status_code, error_data, error_text)
                    continue

                # Non-retryable error, return it
                return status_code, error_data, error_text

            except URLError as e:
                latency_ms = int((time.time() - start_time) * 1000)

                # Network error - retry if attempts remain
                if attempt < self.max_retries:
                    delay = self._get_backoff_delay(attempt)
                    time.sleep(delay)
                    last_error = (0, {'error': str(e.reason)}, str(e.reason))
                    continue

                # Out of retries
                return 0, {'error': f'Network error: {e.reason}'}, str(e.reason)

            except Exception as e:
                # Unexpected error
                return 0, {'error': f'Request failed: {str(e)}'}, str(e)

        # Should not reach here, but if we do, return last error
        if last_error:
            return last_error
        return 0, {'error': 'Max retries exceeded'}, 'Max retries exceeded'
