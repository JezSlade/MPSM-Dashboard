<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show()
    {
        $specFile = base_path('public/AllEndpoints.json');
        $allEndpoints = [];
        if (file_exists($specFile)) {
            $swagger = json_decode(file_get_contents($specFile), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                foreach (($swagger['paths'] ?? []) as $path => $methods) {
                    foreach ($methods as $http => $details) {
                        $allEndpoints[] = [
                            'method'      => strtoupper($http),
                            'path'        => $path,
                            'summary'     => $details['summary'] ?? '',
                            'description' => $details['description'] ?? '',
                        ];
                    }
                }
            }
        }

        $roleMappings = [
            'Developer'  => ['/ApiClient/List'],
            'Admin'      => ['/Analytics/GetReportResult','/ApiClient/List','/Account/GetAccounts','/Account/UpdateProfile'],
            'Dealer'     => ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
            'Service'    => ['/AlertLimit2/GetAllLimits','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
            'Sales'      => ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
            'Accounting' => ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
            'Guest'      => ['/Account/GetProfile','/Account/Logout','/Account/UpdateProfile'],
        ];

        return view('dashboard', [
            'allEndpoints' => $allEndpoints,
            'roleMappings' => $roleMappings,
        ]);
    }
}
