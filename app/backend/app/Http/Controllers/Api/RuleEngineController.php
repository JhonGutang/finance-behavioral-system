<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\FeedbackHistoryRepository;
use App\Repositories\UserProgressRepository;
use App\Services\FeedbackEngineService;
use App\Services\RuleEngineService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RuleEngineController extends Controller
{
    protected RuleEngineService $ruleEngineService;

    protected FeedbackEngineService $feedbackEngineService;

    protected UserProgressRepository $userProgressRepository;

    protected FeedbackHistoryRepository $feedbackHistoryRepository;

    public function __construct(
        RuleEngineService $ruleEngineService,
        FeedbackEngineService $feedbackEngineService,
        UserProgressRepository $userProgressRepository,
        FeedbackHistoryRepository $feedbackHistoryRepository
    ) {
        $this->ruleEngineService = $ruleEngineService;
        $this->feedbackEngineService = $feedbackEngineService;
        $this->userProgressRepository = $userProgressRepository;
        $this->feedbackHistoryRepository = $feedbackHistoryRepository;
    }

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

        if (! $this->ruleEngineService->shouldReevaluate($userId, $targetDate)) {
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
