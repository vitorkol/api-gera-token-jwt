<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API de autenticação JWT — contrato em decisionTree/002-arquitetura-api.md
|--------------------------------------------------------------------------
*/

// Público
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protegido por JWT (validação do token)
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/role', [RoleController::class, 'show']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Validação de rule: exige role admin (403 caso contrário)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/ping', [RoleController::class, 'adminPing']);
    });
});
