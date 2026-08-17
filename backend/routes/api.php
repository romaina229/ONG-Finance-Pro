<?php

declare(strict_types=1);

use App\Http\Controllers\Api\FinancialTransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/health', static fn () => response()->json([
    'status' => 'ok',
    'service' => 'finance-pro-api',
]));

Route::get('/transactions', [FinancialTransactionController::class, 'index']);
Route::post('/transactions', [FinancialTransactionController::class, 'store']);
