# # OktaDomainDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id_dealer** | **string** | Sets the dealer identifier | [optional]
**id_customer** | **string** | Sets the customer identifier | [optional]
**roles_match** | **string** | Sets the roles match | [optional]
**allow_create_users** | **bool** | If true, the user can be created automatically with login from okta | [optional]
**domain_url** | **string** | Sets the Okta complete domain url (iss parameter received in querystring from okta) (es. https://dev-981774.okta.com) | [optional]
**client_id** | **string** | Sets the client id fro okta openid | [optional]
**client_secret** | **string** | Sets the client secret fro okta openid | [optional]
**authority** | **string** | Sets the issuer Issuer (es. https://dev-981774.okta.com/oauth2/default) | [optional]
**metadata_address** | **string** | Sets the url of the configuration data of this open id connection (es. https://dev-981774.okta.com/oauth2/default/.well-known/openid-configuration) | [optional]
**domain** | **string** | Sets the domain string used for the openid configuration (es. dev-981774) | [optional]
**api_token_for_sdk** | **string** | Sets the api token used for calling okta sdk api | [optional]
**login_url** | **string** | Sets the login url of okta | [optional]
**is_validated** | **bool** |  | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
