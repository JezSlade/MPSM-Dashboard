<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiClient;
use App\Helpers\DebugPanel;

class ApiProxyController extends Controller
{
    public function proxy(Request $request)
    {
        $method = strtoupper($request->input('method', 'GET'));
        $path = trim($request->input('path', ''), '/');

        $client = new ApiClient();
        $token = $client->getAccessToken();
        if (!$token) {
            return response()->json(['error' => 'Failed to acquire token'], 500);
        }

        if ($method === 'GET') {
            $url = rtrim(env('BASE_URL', ''), '/') . '/' . $path;
            DebugPanel::log("Proxy GET $url");
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => "Authorization: Bearer $token\r\nAccept: application/json\r\n",
                    'ignore_errors' => true,
                ],
            ];
            $resp = @file_get_contents($url, false, stream_context_create($opts));
            if ($resp === false) {
                return response()->json(['error' => 'Upstream GET failed'], 502);
            }
            return response($resp, 200)->header('Content-Type', 'application/json');
        }

        $data = $request->json()->all() ?? [];
        $response = $client->postJson($path, $token, $data);
        return response()->json($response);
    }
}
