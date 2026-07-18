<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PqrController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])
    ->name('api.register');

Route::post('/login', [AuthController::class, 'login'])
    ->name('api.login');

Route::middleware(['auth:api', 'active'])
    ->name('api.')
    ->group(function (): void {
        Route::get('/me', [AuthController::class, 'me'])
            ->name('me');

        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');

        Route::apiResource('pqrs', PqrController::class);
    });
