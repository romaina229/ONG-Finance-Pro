<?php

declare(strict_types=1);

use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

Route::post('/sync/push', [SyncController::class, 'push']);
Route::get('/sync/pull', [SyncController::class, 'pull']);
