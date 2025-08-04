# # ExplorerClusterDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**customer** | [**\OpenAPI\Client\Model\CustomerBaseDto**](CustomerBaseDto.md) |  | [optional]
**description** | **string** | Cluster name | [optional]
**auto_fix_day_limit** | **int** | The number of days after which a non communicating eXplorer will be switched off              and a communicating eXplorer switched on in the same cluster | [optional]
**explorer_datas** | [**\OpenAPI\Client\Model\ExplorerDataDto[]**](ExplorerDataDto.md) | The list of eXplorers inside the cluster | [optional]
**subnets** | **string[]** | Subnets in the cluster | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
