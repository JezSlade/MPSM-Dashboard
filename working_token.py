import requests
from requests.exceptions import RequestException

# Credentials and API info
CLIENT_ID = "9AT9j4UoU2BgLEqmiYCz"
CLIENT_SECRET = "9gTbAKBCZe1ftYQbLbq9"
USERNAME = "dashboard"
PASSWORD = "d@$hpa$$2024"
SCOPE = "account"
TOKEN_URL = "https://api.abassetmanagement.com/api3/token"

headers = {
    "Content-Type": "application/x-www-form-urlencoded",
    "Cache-Control": "no-cache"
}

def get_token():
    payload = {
        "client_id": CLIENT_ID,
        "client_secret": CLIENT_SECRET,
        "grant_type": "password",
        "username": USERNAME,
        "password": PASSWORD,
        "scope": SCOPE
    }
    try:
        response = requests.post(TOKEN_URL, headers=headers, data=payload)
        response.raise_for_status()
        data = response.json()
        print("Token acquisition successful.")
        print(f"Access Token: {data['access_token']}")
        print(f"Refresh Token: {data['refresh_token']}")
        return data['access_token'], data['refresh_token']
    except RequestException as e:
        print(f"HTTP error during token acquisition: {e}")
    except ValueError:
        print("Invalid response format during token acquisition.")
    except KeyError:
        print("Missing token fields in response.")
    return None, None

def refresh_token(refresh_token_value):
    payload = {
        "client_id": CLIENT_ID,
        "client_secret": CLIENT_SECRET,
        "grant_type": "refresh_token",
        "refresh_token": refresh_token_value
    }
    try:
        response = requests.post(TOKEN_URL, headers=headers, data=payload)
        response.raise_for_status()
        data = response.json()
        print("Token refresh successful.")
        print(f"New Access Token: {data['access_token']}")
        print(f"New Refresh Token: {data['refresh_token']}")
        return data['access_token'], data['refresh_token']
    except RequestException as e:
        print(f"HTTP error during token refresh: {e}")
    except ValueError:
        print("Invalid response format during token refresh.")
    except KeyError:
        print("Missing token fields in refresh response.")
    return None, None

if __name__ == "__main__":
    access_token, refresh_token_val = get_token()
    if access_token and refresh_token_val:
        new_access_token, new_refresh_token = refresh_token(refresh_token_val)
        if new_access_token and new_refresh_token:
            print("Access token refreshed and working.")
        else:
            print("Token refresh failed.")
    else:
        print("Initial token acquisition failed.")
