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
        private RuleEngineService $ruleEngineService,
        private \App\Services\FeedbackEngineService $feedbackEngineService,
        private \App\Repositories\UserProgressRepository $userProgressRepository,
        private \App\Repositories\FeedbackHistoryRepository $feedbackHistoryRepository
    ) {}

    /**
     * Evaluate rules for the authenticated user.
     */
    public function evaluate(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $targetDate = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $weekStart = $targetDate->clone()->startOfWeek(Carbon::MONDAY)->toDateString();

        // Check if we should re-evaluate
        if (! $this->ruleEngineService->shouldReevaluate($userId, $targetDate)) {
            $existingProgress = $this->userProgressRepository->getByWeek($userId, $weekStart);
            $existingFeedback = $this->feedbackHistoryRepository->getByUserAndDate($userId, $weekStart);

            // Reconstruct evaluation result from feedback history to maintain data structure
            $evaluationRules = $existingFeedback->map(function ($feedback) {
                return [
                    'rule_id' => $feedback->rule_id,
                    'triggered' => true,
                    'data' => $feedback->data,
                ];
            })->values()->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation' => [
                        'user_id' => $userId,
                        'evaluation_date' => $targetDate->toDateTimeString(),
                        'weeks' => [
                            'current' => [
                                'start' => $weekStart,
                                'end' => $targetDate->clone()->endOfWeek(Carbon::SUNDAY)->toDateString(),
                            ],
                        ],
                        'triggered_rules' => $evaluationRules,
                        'cached' => true,
                    ],
                    'feedback' => $existingFeedback,
                ],
            ]);
        }

        $evaluation = $this->ruleEngineService->evaluateRules($userId, $targetDate);

        // Generate adaptive feedback based on evaluation
        $feedback = $this->feedbackEngineService->processRuleResults($evaluation);

        return response()->json([
            'success' => true,
            'data' => [
                'evaluation' => $evaluation,
                'feedback' => $feedback,
            ],
        ]);
    }
}
