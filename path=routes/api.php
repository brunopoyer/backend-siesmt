<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CidadeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_middleware', 'api')
])->group(function () {
    Route::apiResource('cidades', CidadeController::class);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_middleware', 'api')
])->group(function () {
    Route::apiResource('cidades', CidadeController::class);
});
