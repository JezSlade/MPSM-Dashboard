Below is the exact sequence your code executes to obtain and use an OAuth2 password-grant access token, illustrated with real snippets from your implementation. All names and values are taken directly from your code.

---

1. Load authorization settings from your environment

   * Reads the token endpoint URL, client credentials, user credentials and scope from runtime config.

   ```php
   // Read .env or equivalent into $config
   $tokenUrl     = $config['TOKEN_URL'];      // e.g. "https://api.abassetmanagement.com/api3/token"
   $clientId     = $config['CLIENT_ID'];      // e.g. "9AT9j4UoU2BgLEqmiYCz"
   $clientSecret = $config['CLIENT_SECRET'];  // e.g. "9gTbAKBCZe1ftYQbLbq9"
   $username     = $config['USERNAME'];       // e.g. "dashboard"
   $password     = $config['PASSWORD'];       // e.g. "d@$hpa$$2024"
   $scope        = $config['SCOPE'];          // e.g. "account"
   ```

2. Assemble the POST body for the password-grant request

   * Uses URL-encoded form data containing grant type, client and user credentials, plus scope.

   ```php
   // Build form fields for OAuth2 password grant
   $formFields = http_build_query([
       'grant_type'    => 'password',
       'client_id'     => $clientId,
       'client_secret' => $clientSecret,
       'username'      => $username,
       'password'      => $password,
       'scope'         => $scope,
   ]);
   ```

3. Initialize and configure the HTTP client

   * Starts a CURL session, sets it to POST, attaches the URL-encoded body, and requests the response back as a string.

   ```php
   $ch = curl_init($tokenUrl);
   curl_setopt($ch, CURLOPT_POST, true);                   // Use POST method
   curl_setopt($ch, CURLOPT_POSTFIELDS, $formFields);      // Attach form data
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);         // Return response as string
   curl_setopt($ch, CURLOPT_HTTPHEADER, [
       'Content-Type: application/x-www-form-urlencoded'
   ]);
   ```

4. Execute the request and capture the JSON response

   * Runs the HTTP call; on error it throws an exception.

   ```php
   $response = curl_exec($ch);
   if (curl_errno($ch)) {
       throw new Exception('Token request failed: ' . curl_error($ch));
   }
   curl_close($ch);
   ```

5. Parse out the access token

   * Decodes the JSON payload and pulls out the `access_token` field.

   ```php
   $data        = json_decode($response, true);   // Convert JSON to PHP array
   $accessToken = $data['access_token'];          // Bearer token string
   $ttlSeconds  = $data['expires_in'];            // Token lifetime in seconds
   ```

6. Return the token to the caller

   * This value is handed back each time any API helper needs authentication.

   ```php
   return $accessToken;
   ```

7. Use the token on every protected API call

   * Immediately before calling a resource endpoint, the code fetches a fresh token then sets it in the `Authorization` header.

   ```php
   // Obtain fresh token
   $token = get_token($config);

   // Prepare next API request
   $ch = curl_init($resourceUrl);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_HTTPHEADER, [
       "Authorization: Bearer {$token}",
       "Content-Type: application/json"
   ]);
   $apiResponse = curl_exec($ch);
   curl_close($ch);
   ```

8. Repeat for every request

   * Because there’s no persistence of the token or use of the `refresh_token` flow, steps 1–5 run on each API invocation.

---

That exact sequence—load config → build form → POST → parse JSON → return token → inject header—is your token pipeline in action.
