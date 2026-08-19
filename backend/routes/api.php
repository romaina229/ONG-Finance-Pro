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
        Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->middleware('permission:dashboard.view');
        Route::get('/projects', [ProjectController::class, 'index'])->middleware('permission:projects.manage');
        Route::post('/projects', [ProjectController::class, 'store'])->middleware('permission:projects.manage');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->middleware('permission:projects.manage');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->middleware('permission:projects.manage');
        Route::get('/budgets', [BudgetController::class, 'index'])->middleware('permission:budgets.manage');
        Route::post('/budgets', [BudgetController::class, 'store'])->middleware('permission:budgets.manage');
        Route::put('/budgets/{budgetLine}', [BudgetController::class, 'update'])->middleware('permission:budgets.manage');
        Route::get('/transactions', [FinancialTransactionController::class, 'index'])->middleware('permission:transactions.create');
        Route::post('/transactions', [FinancialTransactionController::class, 'store'])->middleware('permission:transactions.create');
        Route::post('/transactions/{transaction}/submit', [FinancialTransactionController::class, 'submit'])->middleware('permission:transactions.create');
        Route::post('/transactions/{transaction}/approve', [FinancialTransactionController::class, 'approve'])->middleware('permission:transactions.approve');
        Route::post('/transactions/{transaction}/reconcile', [FinancialTransactionController::class, 'reconcile'])->middleware('permission:transactions.reconcile');
        Route::get('/reports/financial', [ReportController::class, 'financial'])->middleware('permission:reports.view');
        Route::prefix('sync')->middleware('permission:transactions.create')->group(function (): void {
            Route::post('/push', [SyncController::class, 'push']);
            Route::get('/pull', [SyncController::class, 'pull']);
        });
    });
});
