<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RuleEngineController;

// Health check routes
Route::get('/health', [HealthController::class, 'health']);
Route::get('/message', [HealthController::class, 'message']);

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Category routes
    Route::apiResource('categories', CategoryController::class);

    // Transaction analytics routes
    Route::get('/transactions/analytics/daily', [TransactionController::class, 'dailyAnalytics']);
    Route::get('/transactions/analytics/monthly', [TransactionController::class, 'monthlyAnalytics']);
    Route::get('/transactions/analytics/yearly', [TransactionController::class, 'yearlyAnalytics']);

    // Transaction routes
    Route::get('/transactions/summary', [TransactionController::class, 'summary']);
    Route::apiResource('transactions', TransactionController::class);

    // Rule Engine routes
    Route::get('/rules/evaluate', [RuleEngineController::class, 'evaluate']);
});
