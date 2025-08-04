# OpenAPI\Client\TicketApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**ticketCreateTicket()**](TicketApi.md#ticketCreateTicket) | **POST** /Ticket/CreateTicket | Create the ticket |
| [**ticketCreateTicketChild()**](TicketApi.md#ticketCreateTicketChild) | **POST** /Ticket/CreateTicketChild | Create the ticket |
| [**ticketGetAttachment()**](TicketApi.md#ticketGetAttachment) | **GET** /Ticket/GetAttachment | Gets the attachment. |
| [**ticketGetSubTypesByCategories()**](TicketApi.md#ticketGetSubTypesByCategories) | **POST** /Ticket/GetSubTypesByCategories | Returns the categories and subtypes gerarchy |
| [**ticketGetTicket()**](TicketApi.md#ticketGetTicket) | **GET** /Ticket/GetTicket | Gets the ticket. |
| [**ticketGetTicketByNumber()**](TicketApi.md#ticketGetTicketByNumber) | **GET** /Ticket/GetTicketByNumber | Gets the ticket. |
| [**ticketGetTickets()**](TicketApi.md#ticketGetTickets) | **GET** /Ticket/GetTickets | Gets the tickets. |


## `ticketCreateTicket()`

```php
ticketCreateTicket($request): \OpenAPI\Client\Model\SingleResultResponseCreateTicketResponse
```

Create the ticket

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TicketApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestCreateTicketDto(); // \OpenAPI\Client\Model\CreateRequestCreateTicketDto

try {
    $result = $apiInstance->ticketCreateTicket($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TicketApi->ticketCreateTicket: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestCreateTicketDto**](../Model/CreateRequestCreateTicketDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCreateTicketResponse**](../Model/SingleResultResponseCreateTicketResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `ticketCreateTicketChild()`

```php
ticketCreateTicketChild($request): \OpenAPI\Client\Model\BaseResponse
```

Create the ticket

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TicketApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestCreateTicketChildDto(); // \OpenAPI\Client\Model\CreateRequestCreateTicketChildDto

try {
    $result = $apiInstance->ticketCreateTicketChild($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TicketApi->ticketCreateTicketChild: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestCreateTicketChildDto**](../Model/CreateRequestCreateTicketChildDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\BaseResponse**](../Model/BaseResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `ticketGetAttachment()`

```php
ticketGetAttachment($id, $attachment_name): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```

Gets the attachment.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TicketApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$attachment_name = 'attachment_name_example'; // string | Gets or sets the name of the attachment.

try {
    $result = $apiInstance->ticketGetAttachment($id, $attachment_name);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TicketApi->ticketGetAttachment: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **attachment_name** | **string**| Gets or sets the name of the attachment. | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseFileInfoDto**](../Model/SingleResultResponseFileInfoDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `ticketGetSubTypesByCategories()`

```php
ticketGetSubTypesByCategories($request): \OpenAPI\Client\Model\ListResultResponseTicketSubTypesByCategoryDto
```

Returns the categories and subtypes gerarchy

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TicketApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CategoryLanguageRequest(); // \OpenAPI\Client\Model\CategoryLanguageRequest

try {
    $result = $apiInstance->ticketGetSubTypesByCategories($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TicketApi->ticketGetSubTypesByCategories: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CategoryLanguageRequest**](../Model/CategoryLanguageRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseTicketSubTypesByCategoryDto**](../Model/ListResultResponseTicketSubTypesByCategoryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `ticketGetTicket()`

```php
ticketGetTicket($id): \OpenAPI\Client\Model\SingleResultResponseTicketDto
```

Gets the ticket.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TicketApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->ticketGetTicket($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TicketApi->ticketGetTicket: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseTicketDto**](../Model/SingleResultResponseTicketDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `ticketGetTicketByNumber()`

```php
ticketGetTicketByNumber($ticket_number): \OpenAPI\Client\Model\SingleResultResponseTicketDto
```

Gets the ticket.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TicketApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$ticket_number = 56; // int | Gets or sets the ticket number.

try {
    $result = $apiInstance->ticketGetTicketByNumber($ticket_number);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TicketApi->ticketGetTicketByNumber: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **ticket_number** | **int**| Gets or sets the ticket number. | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseTicketDto**](../Model/SingleResultResponseTicketDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `ticketGetTickets()`

```php
ticketGetTickets($page_number, $page_rows, $sort_column, $sort_order, $customer_id, $dealer_id, $serial_asset_number, $ip_address, $resolve, $id_ticket, $category, $is_feature_request, $is_parked, $is_flagged, $is_waiting_third_level, $sub_type, $priority, $opened_from, $opened_to, $owner_name, $assigned_to, $ticket_type, $oberon_ticket, $sds_ticket, $filter_text): \OpenAPI\Client\Model\PagedResultResponseTicketDto
```

Gets the tickets.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TicketApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$customer_id = 'customer_id_example'; // string | Gets or sets the customer identifier.
$dealer_id = 'dealer_id_example'; // string | Gets or sets the dealer identifier.
$serial_asset_number = 'serial_asset_number_example'; // string | Gets or sets the serial asset number.
$ip_address = 'ip_address_example'; // string | Gets or sets the ip address.
$resolve = 'resolve_example'; // string | Filter by resolve status, NotClosed and New are filtered together
$id_ticket = 56; // int | Gets or sets the identifier ticket.
$category = 'category_example'; // string | Filter the list by category
$is_feature_request = True; // bool | Gets or sets the is feature request.
$is_parked = True; // bool | Gets or sets the IsParked.
$is_flagged = True; // bool | Gets or sets the IsFlagged.
$is_waiting_third_level = True; // bool | Gets or sets the is waiting third level.
$sub_type = 'sub_type_example'; // string | Gets or sets the type of the sub.
$priority = 'priority_example'; // string | Gets or sets the priority.
$opened_from = new \DateTime('2013-10-20T19:20:30+01:00'); // \DateTime | Gets or sets the opened from.
$opened_to = new \DateTime('2013-10-20T19:20:30+01:00'); // \DateTime | Gets or sets the opened to.
$owner_name = 'owner_name_example'; // string | Gets or sets the name of the owner.
$assigned_to = 'assigned_to_example'; // string | Gets or sets the name of the assigned to.
$ticket_type = 'ticket_type_example'; // string | Gets or sets the type of the ticket.
$oberon_ticket = 'oberon_ticket_example'; // string | Gets or sets the oberon ticket.
$sds_ticket = 'sds_ticket_example'; // string | Gets or sets the SDS ticket.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->ticketGetTickets($page_number, $page_rows, $sort_column, $sort_order, $customer_id, $dealer_id, $serial_asset_number, $ip_address, $resolve, $id_ticket, $category, $is_feature_request, $is_parked, $is_flagged, $is_waiting_third_level, $sub_type, $priority, $opened_from, $opened_to, $owner_name, $assigned_to, $ticket_type, $oberon_ticket, $sds_ticket, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TicketApi->ticketGetTickets: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **customer_id** | **string**| Gets or sets the customer identifier. | [optional] |
| **dealer_id** | **string**| Gets or sets the dealer identifier. | [optional] |
| **serial_asset_number** | **string**| Gets or sets the serial asset number. | [optional] |
| **ip_address** | **string**| Gets or sets the ip address. | [optional] |
| **resolve** | **string**| Filter by resolve status, NotClosed and New are filtered together | [optional] |
| **id_ticket** | **int**| Gets or sets the identifier ticket. | [optional] |
| **category** | **string**| Filter the list by category | [optional] |
| **is_feature_request** | **bool**| Gets or sets the is feature request. | [optional] |
| **is_parked** | **bool**| Gets or sets the IsParked. | [optional] |
| **is_flagged** | **bool**| Gets or sets the IsFlagged. | [optional] |
| **is_waiting_third_level** | **bool**| Gets or sets the is waiting third level. | [optional] |
| **sub_type** | **string**| Gets or sets the type of the sub. | [optional] |
| **priority** | **string**| Gets or sets the priority. | [optional] |
| **opened_from** | **\DateTime**| Gets or sets the opened from. | [optional] |
| **opened_to** | **\DateTime**| Gets or sets the opened to. | [optional] |
| **owner_name** | **string**| Gets or sets the name of the owner. | [optional] |
| **assigned_to** | **string**| Gets or sets the name of the assigned to. | [optional] |
| **ticket_type** | **string**| Gets or sets the type of the ticket. | [optional] |
| **oberon_ticket** | **string**| Gets or sets the oberon ticket. | [optional] |
| **sds_ticket** | **string**| Gets or sets the SDS ticket. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseTicketDto**](../Model/PagedResultResponseTicketDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
