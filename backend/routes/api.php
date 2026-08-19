<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FinancialTransactionController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Middleware\SetCurrentOrganization;
use Illuminate\Support\Facades\Route;

Route::get('/health', static fn () => response()->json(['status' => 'ok', 'service' => 'finance-pro-api', 'version' => '1.0.0']));
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware(SetCurrentOrganization::class)->group(function (): void {
        Route::get('/organization/current', [OrganizationController::class, 'current']);
        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::get('/budgets', [BudgetController::class, 'index']);
        Route::post('/budgets', [BudgetController::class, 'store']);
        Route::put('/budgets/{budgetLine}', [BudgetController::class, 'update']);
        Route::get('/transactions', [FinancialTransactionController::class, 'index']);
        Route::post('/transactions', [FinancialTransactionController::class, 'store']);
        Route::post('/transactions/{transaction}/submit', [FinancialTransactionController::class, 'submit']);
        Route::post('/transactions/{transaction}/approve', [FinancialTransactionController::class, 'approve']);
        Route::post('/transactions/{transaction}/reconcile', [FinancialTransactionController::class, 'reconcile']);
        Route::get('/reports/financial', [ReportController::class, 'financial']);
        Route::prefix('sync')->group(function (): void {
            Route::post('/push', [SyncController::class, 'push']);
            Route::get('/pull', [SyncController::class, 'pull']);
        });
    });
});
