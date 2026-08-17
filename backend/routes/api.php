<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FinancialTransactionController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Middleware\SetCurrentOrganization;
use Illuminate\Support\Facades\Route;

Route::get('/health', static fn () => response()->json([
    'status' => 'ok',
    'service' => 'finance-pro-api',
]));

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware(SetCurrentOrganization::class)->group(function (): void {
        Route::get('/organization/current', [OrganizationController::class, 'current']);
        Route::get('/transactions', [FinancialTransactionController::class, 'index']);
        Route::post('/transactions', [FinancialTransactionController::class, 'store']);

        Route::prefix('sync')->group(function (): void {
            Route::post('/push', [SyncController::class, 'push']);
            Route::get('/pull', [SyncController::class, 'pull']);
        });
    });
});
