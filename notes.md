
MPS MONITOR

    How to
    API References

MPS MONITOR API Documentation
Read the How to connect section to see how to connect to our services, passing the required authentication credentials.
Go to API References to see the available services, along with request\response definitions .
How to connect
1 - Register your application
Access the developer section of the portal and create the APP credentials.

2- Get the authentication token
In order to obtain a valid access token, you must perform a x-www-form-urlencoded POST to our /token endpoint, passing the following parameters:

    client_id: the client identifier we gave you in the step 1
    client_secret: the client password we gave you in the step 1
    grant_type: fixed value to password
    username: the identifier of the user that wants to login
    password: the password\token of the user that wants to login
    scope: the type of MPS access level(account)

Please note that the access token has an expiration and you should refresh it before that happens (see step 4) or you must perform this step again.
Sample Request

POST /token HTTP/1.1
Host: EndpointApiUri
Content-Type: application/x-www-form-urlencoded
Cache-Control: no-cache
client_id=YourClientId&client_secret=YourClientSecret&grant_type=password&username=test%40test.com&password=Password123!&scope=account

Sample Response

{
    "access_token": "WDqEbIZNQ2zpFvulchVP8JXT5IpLlHzMkLmmBKbJmsPF",
    "token_type": "bearer",
    "expires_in": 1799,
    "refresh_token": "f1efdf74ecc043b69f60c110af41a3f1",
    "as:client_id": "YourClientId",
    "userName": "test%40test.com",
    ".issued": "Mon, 30 Jan 2017 13:40:47 GMT",
    ".expires": "Mon, 30 Jan 2017 14:10:47 GMT"
}


3- Call the service
Once you have retrieved valid access token, you must pass the access_token value, for every following request to our enpoints, in the Authorization header as Bearer.
Sample Request

GET /account/getaccount HTTP/1.1
Host: EndpointApiUri
Content-Type: application/json
Authorization: bearer WDqEbIZNQ2zpFvulchVP8JXT5IpLlHzMkLmmBKbJmsPF
Cache-Control: no-cache


4- Refresh the authentication token
In order to refresh the access token, you must perform a x-www-form-urlencoded POST to our /token endpoint, passing the following parameters:

    client_id: the client identifier we gave you in the step 1
    client_secret: the client password we gave you in the step 1
    grant_type: fixed value to refresh_token
    refresh_token: the refresh_token (not access_token) value obtained in step 2

Sample Request

POST /token HTTP/1.1
Host: EndpointApiUri
Content-Type: application/x-www-form-urlencoded
Cache-Control: no-cache
client_id=YourClientId&client_secret=YourClientSecret&grant_type=refresh_token&refresh_token=f1efdf74ecc043b69f60c110af41a3f1

Sample Response

{
    "access_token": "mFpZAd4MXC9syF1KxAdf23HWcXhZ9NMiwrrZxnv",
    "token_type": "bearer",
    "expires_in": 1799,
    "refresh_token": "b99af13fc24840f188b21dde192634db",
    "as:client_id": "YourClientId",
    "userName": "test%40test.com",
    ".issued": "Mon, 30 Jan 2017 13:40:47 GMT",
    ".expires": "Mon, 30 Jan 2017 14:10:47 GMT"
}

© 2025 - MPSMonitor.com
