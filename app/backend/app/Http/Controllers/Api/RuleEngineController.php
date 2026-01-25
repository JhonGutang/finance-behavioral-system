<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RuleEngineService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RuleEngineController extends Controller
{
    public function __construct(
        private RuleEngineService $ruleEngineService
    ) {}

    /**
     * Evaluate rules for the authenticated user.
     */
    public function evaluate(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $targetDate = $request->query('date') 
            ? Carbon::parse($request->query('date')) 
            : null;

        $evaluation = $this->ruleEngineService->evaluateRules($userId, $targetDate);

        return response()->json([
            'success' => true,
            'data' => $evaluation,
        ]);
    }
}
