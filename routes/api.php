<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    PessoaController,
    UnidadeController,
    LotacaoController,
    ServidorEfetivoController,
    ServidorTemporarioController
};

// Rotas públicas
Route::post('login', [AuthController::class, 'login']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Pessoas
    Route::apiResource('pessoas', PessoaController::class);

    // Unidades
    Route::apiResource('unidades', UnidadeController::class);

    // Lotações
    Route::apiResource('lotacoes', LotacaoController::class);

    // Servidores Efetivos
    Route::prefix('servidores-efetivos')->group(function () {
        Route::get('/', [ServidorEfetivoController::class, 'index']);
        Route::post('/', [ServidorEfetivoController::class, 'store']);
        Route::get('/{servidorEfetivo}', [ServidorEfetivoController::class, 'show']);
        Route::put('/{servidorEfetivo}', [ServidorEfetivoController::class, 'update']);
        Route::delete('/{servidorEfetivo}', [ServidorEfetivoController::class, 'destroy']);
        Route::get('/unidade/{unidId}', [ServidorEfetivoController::class, 'porUnidade']);
        Route::get('/busca', [ServidorEfetivoController::class, 'buscarPorNome']);
    });

    // Servidores Temporários
    Route::apiResource('servidores-temporarios', ServidorTemporarioController::class);
});
