<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\ApiProxyController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api-status', [StatusController::class, 'api']);
Route::get('/db-status', [StatusController::class, 'db']);
Route::match(['get', 'post'], '/api-proxy', [ApiProxyController::class, 'proxy']);
Route::get('/get-token', [TokenController::class, 'fetch']);
