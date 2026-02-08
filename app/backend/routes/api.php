<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\RuleEngineController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

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
    Route::post('/transactions/import-preview', [TransactionController::class, 'importPreview']);
    Route::post('/transactions/import-confirm', [TransactionController::class, 'importConfirm']);
    Route::apiResource('transactions', TransactionController::class);

    // Rule Engine routes
    Route::get('/rules/evaluate', [RuleEngineController::class, 'evaluate']);

    // Feedback routes
    Route::get('/feedback', [\App\Http\Controllers\Api\FeedbackController::class, 'index']);
    Route::get('/feedback/progress', [\App\Http\Controllers\Api\FeedbackController::class, 'progress']);
    Route::post('/feedback/{id}/acknowledge', [\App\Http\Controllers\Api\FeedbackController::class, 'acknowledge']);
});
