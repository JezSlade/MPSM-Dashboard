import os
import requests
from requests.exceptions import RequestException
from dotenv import load_dotenv

# Load configuration from .env file (ensures BASE_URL and credentials are available as environment variables)
load_dotenv()  # This will read the .env file and set environment variables

# Base URL for the API (must be defined in .env as BASE_URL, e.g., "https://api.abassetmanagement.com/api3/")
BASE_URL = os.getenv("BASE_URL")
if not BASE_URL:
    raise Exception("BASE_URL is not set in the environment. Please define BASE_URL in the .env file.")
 
# OAuth2 client credentials and user credentials (should be defined in .env for security)
CLIENT_ID = os.getenv("CLIENT_ID")
CLIENT_SECRET = os.getenv("CLIENT_SECRET")
USERNAME = os.getenv("USERNAME")
PASSWORD = os.getenv("PASSWORD")
SCOPE = os.getenv("SCOPE")

# Define API endpoints (these will be appended to the BASE_URL when constructing full URLs)
TOKEN_ENDPOINT = "token"           # Endpoint path for obtaining OAuth2 token
GET_PRINTERS_ENDPOINT = "GetPrinters"  # Example endpoint for fetching printers data (no leading slash needed)
GET_ALERTS_ENDPOINT = "GetAlerts"      # Example endpoint for fetching alerts data (no leading slash needed)

def build_url(endpoint_path: str) -> str:
    """
    Construct the full API URL by combining the BASE_URL with a specific endpoint path.
    This function ensures there is exactly one '/' between the base URL and endpoint.
    """
    # Remove any trailing slash from BASE_URL and leading slash from endpoint_path, then combine
    return BASE_URL.rstrip("/") + "/" + endpoint_path.lstrip("/")

# Construct the full URL for the token endpoint by appending the token path to the base URL
TOKEN_URL = build_url(TOKEN_ENDPOINT)

# Headers for token requests (using form-encoded data as required by the OAuth2 token endpoint)
TOKEN_REQUEST_HEADERS = {
    "Content-Type": "application/x-www-form-urlencoded",
    "Cache-Control": "no-cache"
}

def get_token():
    """
    Obtain a new access token and refresh token using the Resource Owner Password Credentials grant.
    Uses CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD, and SCOPE from the environment.
    
    Returns:
        tuple: (access_token, refresh_token) if successful, otherwise (None, None).
    """
    # Prepare the form data payload for the token request
    payload = {
        "client_id": CLIENT_ID,
        "client_secret": CLIENT_SECRET,
        "grant_type": "password",   # using the Password grant type
        "username": USERNAME,
        "password": PASSWORD,
        "scope": SCOPE
    }
    try:
        # Make a POST request to the token endpoint URL with the form data
        response = requests.post(TOKEN_URL, headers=TOKEN_REQUEST_HEADERS, data=payload)
        response.raise_for_status()  # Raise an error if the request returned an HTTP error status
        data = response.json()       # Parse the JSON response body

        # Log success and token values for debugging (avoid printing sensitive info in production)
        print("Token acquisition successful.")
        print(f"Access Token: {data['access_token']}")
        print(f"Refresh Token: {data['refresh_token']}")

        # Return the obtained tokens
        return data['access_token'], data['refresh_token']
    except RequestException as e:
        # This will catch HTTP errors (non-200 responses) or network problems
        print(f"HTTP error during token acquisition: {e}")
    except ValueError:
        # This will catch JSON parsing errors (e.g., if response is not JSON formatted as expected)
        print("Invalid response format during token acquisition.")
    except KeyError:
        # This will catch cases where expected fields are missing in the response JSON
        print("Missing token fields in response.")
    
    # If we reach this point, something went wrong
    return None, None

def refresh_token(refresh_token_value):
    """
    Use a refresh token to obtain a new access token (and a new refresh token) via the OAuth2 refresh grant.
    
    Parameters:
        refresh_token_value (str): The refresh token issued by a previous call to get_token.
    
    Returns:
        tuple: (new_access_token, new_refresh_token) if successful, otherwise (None, None).
    """
    # Prepare the form data payload for the refresh request
    payload = {
        "client_id": CLIENT_ID,
        "client_secret": CLIENT_SECRET,
        "grant_type": "refresh_token",   # using the Refresh Token grant type
        "refresh_token": refresh_token_value
    }
    try:
        # Make a POST request to the token endpoint URL with the refresh payload
        response = requests.post(TOKEN_URL, headers=TOKEN_REQUEST_HEADERS, data=payload)
        response.raise_for_status()  # Raise error for bad status codes
        data = response.json()       # Parse JSON response

        # Log success and new token values for debugging
        print("Token refresh successful.")
        print(f"New Access Token: {data['access_token']}")
        print(f"New Refresh Token: {data['refresh_token']}")

        # Return the new tokens
        return data['access_token'], data['refresh_token']
    except RequestException as e:
        # Catches HTTP errors or connection issues
        print(f"HTTP error during token refresh: {e}")
    except ValueError:
        # Catches JSON parse errors
        print("Invalid response format during token refresh.")
    except KeyError:
        # Catches missing fields in the refresh response
        print("Missing token fields in refresh response.")
    
    # If refresh failed, return (None, None)
    return None, None

# The following demonstrates how to use the above functions.
# It first obtains a token, then uses the refresh token to get a new token.
# In a real application, you would call get_token() to get initial credentials, then use the returned
# access token in authorized API requests (with an "Authorization: Bearer <token>" header).
# When the access token expires, call refresh_token() with the saved refresh token to get a new one.
if __name__ == "__main__":
    # Attempt to get an initial access token and refresh token
    access_token, refresh_token_val = get_token()
    if access_token and refresh_token_val:
        # Successfully obtained tokens; you can now use the access token to make authorized API calls.
        # Example: Prepare headers for an authenticated request to another endpoint.
        api_headers = {
            "Authorization": f"Bearer {access_token}",
            "Content-Type": "application/json"
        }
        # Example: Construct the full URL for the GetPrinters endpoint and make a GET request (if needed).
        printers_url = build_url(GET_PRINTERS_ENDPOINT)
        print(f"Full printers URL: {printers_url}")  # Debug: show the constructed URL
        # (You would typically do: response = requests.get(printers_url, headers=api_headers) to fetch data)
        
        # Now demonstrate using the refresh token to get a new access token when the old one expires.
        new_access_token, new_refresh_token = refresh_token(refresh_token_val)
        if new_access_token and new_refresh_token:
            print("Access token refreshed and working.")
        else:
            print("Token refresh failed.")
    else:
        print("Initial token acquisition failed.")
