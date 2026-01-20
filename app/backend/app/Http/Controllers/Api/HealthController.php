<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Health check endpoint
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'service' => 'Finance Behavioral System API',
        ]);
    }

    /**
     * Message endpoint for frontend communication test
     */
    public function message(): JsonResponse
    {
        return response()->json([
            'message' => 'Hello from Finance Behavioral System Backend! 🚀',
            'description' => 'This message is coming from the Laravel API backend.',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
