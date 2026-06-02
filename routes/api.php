<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OrdemController;
use App\Http\Controllers\PecaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Autenticação ──────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ── Rotas Protegidas ──────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ── Clientes ──────────────────────────────────────────────────────────────
    Route::apiResource('clientes', ClienteController::class)->except(['destroy']);
    Route::get('/cep/{cep}', [ClienteController::class, 'cep']);

    // ── Ordens de Serviço ─────────────────────────────────────────────────────
    Route::apiResource('ordens', OrdemController::class)
    ->parameters(['ordens' => 'ordemServico'])
    ->except(['destroy']);
    Route::get('/relatorios/ordens', [OrdemController::class, 'relatorio']);

    // Vinculação de peças às ordens
    Route::post('/ordens/{ordemServico}/pecas', [PecaController::class, 'vincularOrdem']);

    // ── Peças e Estoque ───────────────────────────────────────────────────────
    Route::apiResource('pecas', PecaController::class)->except(['destroy']);
    Route::post('/pecas/{peca}/estoque', [PecaController::class, 'movimentar']);
    Route::get('/relatorios/pecas-mais-utilizadas', [PecaController::class, 'maisUtilizadas']);

    // ── Usuários (apenas admin) ───────────────────────────────────────────────
    Route::middleware('perfil:admin')->group(function () {
        Route::apiResource('usuarios', UserController::class)->except(['destroy']);
    });

    // Técnicos disponíveis (atendente e admin podem ver)
    Route::get('/tecnicos-disponiveis', [UserController::class, 'tecnicosDisponiveis'])
         ->middleware('perfil:admin,atendente');

});
