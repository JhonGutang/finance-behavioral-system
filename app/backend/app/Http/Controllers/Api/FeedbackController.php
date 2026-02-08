<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\FeedbackHistoryRepository;
use App\Repositories\UserProgressRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function __construct(
        private FeedbackHistoryRepository $feedbackHistoryRepository,
        private UserProgressRepository $userProgressRepository
    ) {}

    /**
     * Get the latest feedback for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $perPage = $request->query('per_page', 10);
        $feedback = $this->feedbackHistoryRepository->getPaginated($userId, (int) $perPage);

        return response()->json([
            'success' => true,
            'data' => $feedback->items(),
            'meta' => [
                'current_page' => $feedback->currentPage(),
                'last_page' => $feedback->lastPage(),
                'per_page' => $feedback->perPage(),
                'total' => $feedback->total(),
            ],
        ]);
    }

    /**
     * Get the authenticated user's progress history.
     */
    public function progress(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $progress = $this->userProgressRepository->getRecent($userId);

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    /**
     * Acknowledge a feedback item.
     */
    public function acknowledge(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $updated = $this->feedbackHistoryRepository->acknowledge($id, $userId);

        return response()->json([
            'success' => $updated,
            'message' => $updated ? 'Feedback acknowledged' : 'Feedback not found',
        ]);
    }
}
