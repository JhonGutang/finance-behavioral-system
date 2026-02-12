<?php

namespace App\Repositories;

use App\Models\FeedbackHistory;
use Illuminate\Database\Eloquent\Collection;

class FeedbackHistoryRepository
{
    /**
     * Get feedback history for a user within a date range.
     */
    public function getByUserAndDate(int $userId, string $startDate): Collection
    {
        return FeedbackHistory::where('user_id', $userId)
            ->where('week_start', $startDate)
            ->get();
    }

    /**
     * Get feedback history for a user and specific rule.
     */
    public function getByUserAndRule(int $userId, string $ruleId, int $limit = 4): Collection
    {
        return FeedbackHistory::where('user_id', $userId)
            ->where('rule_id', $ruleId)
            ->orderBy('week_start', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getAll(int $userId): Collection
    {
        return FeedbackHistory::where('user_id', $userId)
            ->orderBy('week_start', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get paginated feedback for a user.
     */
    public function getPaginated(int $userId, int $perPage = 10)
    {
        return FeedbackHistory::where('user_id', $userId)
            ->orderBy('week_start', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new feedback history record.
     */
    public function create(array $data): FeedbackHistory
    {
        return FeedbackHistory::create($data);
    }

    /**
     * Mark feedback as displayed.
     */
    public function markAsDisplayed(int $id, int $userId): bool
    {
        return FeedbackHistory::where('id', $id)
            ->where('user_id', $userId)
            ->update(['displayed' => true]);
    }

    /**
     * Update or create a feedback history record.
     */
    public function updateOrCreate(array $attributes, array $values): FeedbackHistory
    {
        return FeedbackHistory::updateOrCreate($attributes, $values);
    }

    /**
     * Mark feedback as acknowledged.
     */
    public function acknowledge(int $id, int $userId): bool
    {
        return FeedbackHistory::where('id', $id)
            ->where('user_id', $userId)
            ->update(['user_acknowledged' => true]);
    }
}
