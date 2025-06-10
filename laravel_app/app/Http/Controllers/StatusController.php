<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class StatusController extends Controller
{
    public function api(): Response
    {
        return response('API OK', 200)->header('Content-Type', 'text/plain');
    }

    public function db(): Response
    {
        return response('DB OK', 200)->header('Content-Type', 'text/plain');
    }
}
