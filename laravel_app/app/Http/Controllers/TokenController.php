<?php

namespace App\Http\Controllers;

use App\Helpers\ApiClient;
use App\Helpers\DebugPanel;
use Exception;

class TokenController extends Controller
{
    public function fetch()
    {
        $client = new ApiClient();
        try {
            $data = $client->getTokenData();
            if (empty($data['access_token'])) {
                throw new Exception($data['error_description'] ?? 'No access_token');
            }
            return response()->json([
                'access_token' => $data['access_token'],
                'expires_in'   => $data['expires_in']   ?? 3600,
                'token_type'   => $data['token_type']   ?? 'Bearer',
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Token fetch error: '.$e->getMessage()], 500);
        }
    }
}
