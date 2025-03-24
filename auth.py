import time
import requests
import os
from requests.exceptions import RequestException
from dotenv import load_dotenv

load_dotenv()

class TokenManager:
    def __init__(self, client_id, client_secret, username, password, scope, token_url):
        self.client_id = client_id
        self.client_secret = client_secret
        self.username = username
        self.password = password
        self.scope = scope
        self.token_url = token_url
        self.access_token = None
        self.refresh_token = None
        self.token_expiry = 0  # Epoch time

    def get_headers(self):
        if self.access_token is None or time.time() >= self.token_expiry - 60:
            self.refresh_or_get_token()
        return {
            "Authorization": f"Bearer {self.access_token}",
            "Content-Type": "application/json"
        }

    def refresh_or_get_token(self):
        if self.refresh_token:
            print("Attempting to refresh token...")
            success = self.refresh_access_token()
            if not success:
                print("Refresh failed, obtaining new token...")
                self.obtain_token()
        else:
            print("No refresh token found, obtaining new token...")
            self.obtain_token()

    def obtain_token(self):
        payload = {
            "client_id": self.client_id,
            "client_secret": self.client_secret,
            "grant_type": "password",
            "username": self.username,
            "password": self.password,
            "scope": self.scope
        }
        headers = {
            "Content-Type": "application/x-www-form-urlencoded",
            "Cache-Control": "no-cache"
        }
        try:
            response = requests.post(self.token_url, headers=headers, data=payload)
            response.raise_for_status()
            data = response.json()
            self.access_token = data['access_token']
            self.refresh_token = data['refresh_token']
            self.token_expiry = time.time() + data.get('expires_in', 1800)
            print("Token acquisition successful.")
            print(f"Access Token: {self.access_token}")
            print(f"Refresh Token: {self.refresh_token}")
        except RequestException as e:
            print(f"HTTP error during token acquisition: {e}")
        except ValueError:
            print("Invalid response format during token acquisition.")
        except KeyError:
            print("Missing token fields in response.")

    def refresh_access_token(self):
        payload = {
            "client_id": self.client_id,
            "client_secret": self.client_secret,
            "grant_type": "refresh_token",
            "refresh_token": self.refresh_token
        }
        headers = {
            "Content-Type": "application/x-www-form-urlencoded",
            "Cache-Control": "no-cache"
        }
        try:
            response = requests.post(self.token_url, headers=headers, data=payload)
            response.raise_for_status()
            data = response.json()
            self.access_token = data['access_token']
            self.refresh_token = data['refresh_token']
            self.token_expiry = time.time() + data.get('expires_in', 1800)
            print("Token refresh successful.")
            print(f"New Access Token: {self.access_token}")
            print(f"New Refresh Token: {self.refresh_token}")
            return True
        except RequestException as e:
            print(f"HTTP error during token refresh: {e}")
        except ValueError:
            print("Invalid response format during token refresh.")
        except KeyError:
            print("Missing token fields in refresh response.")
        return False

if __name__ == "__main__":
    manager = TokenManager(
        client_id=os.getenv("CLIENT_ID"),
        client_secret=os.getenv("CLIENT_SECRET"),
        username=os.getenv("USERNAME"),
        password=os.getenv("PASSWORD"),
        scope=os.getenv("SCOPE"),
        token_url=os.getenv("TOKEN_URL")
    )
    headers = manager.get_headers()
    print("Headers ready for API use:", headers)
