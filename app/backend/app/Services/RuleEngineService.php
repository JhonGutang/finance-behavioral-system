<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use Carbon\Carbon;

class RuleEngineService
{
    private const CATEGORY_OVERSPEND_THRESHOLD = 0.25; // 25%
    private const WEEKLY_SPIKE_THRESHOLD = 0.20;       // 20%
    private const SMALL_PURCHASE_AMOUNT_LIMIT = 10.00;
    private const SMALL_PURCHASE_COUNT_THRESHOLD = 10;

    public function __construct(
        private TransactionRepository $transactionRepository
    ) {}

    /**
     * Evaluate all rules for a given user and target date.
     * Target date determines the "current week".
     */
    public function evaluateRules(int $userId, ?Carbon $targetDate = null): array
    {
        $targetDate = $targetDate ?? Carbon::today();
        
        // Define weeks (Monday to Sunday)
        $currentWeekStart = $targetDate->clone()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $currentWeekEnd = $targetDate->clone()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        
        $previousWeekStart = $currentWeekStart->clone()->subWeek();
        $previousWeekEnd = $currentWeekEnd->clone()->subWeek();

        // Get summaries
        $currentSummary = $this->transactionRepository->getWeeklySummary(
            $userId, 
            $currentWeekStart->toDateTimeString(), 
            $currentWeekEnd->toDateTimeString()
        );
        
        $previousSummary = $this->transactionRepository->getWeeklySummary(
            $userId, 
            $previousWeekStart->toDateTimeString(), 
            $previousWeekEnd->toDateTimeString()
        );

        $results = [];

        // Rule 1: Category Overspend
        $categoryTriggers = $this->checkCategoryOverspend($currentSummary, $previousSummary);
        foreach ($categoryTriggers as $trigger) {
            $results[] = $trigger;
        }

        // Rule 2: Weekly Spending Spike
        $results[] = $this->checkWeeklySpendingSpike($currentSummary, $previousSummary);

        // Rule 3: Frequent Small Purchases
        $results[] = $this->checkFrequentSmallPurchases($currentSummary);

        return [
            'user_id' => $userId,
            'evaluation_date' => $targetDate->toDateTimeString(),
            'weeks' => [
                'current' => [
                    'start' => $currentWeekStart->toDateString(),
                    'end' => $currentWeekEnd->toDateString(),
                ],
                'previous' => [
                    'start' => $previousWeekStart->toDateString(),
                    'end' => $previousWeekEnd->toDateString(),
                ]
            ],
            'triggered_rules' => array_filter($results, fn($r) => $r['triggered'])
        ];
    }

    private function checkCategoryOverspend(array $current, array $previous): array
    {
        $triggers = [];
        
        foreach ($current['category_totals'] as $category => $currentAmount) {
            $previousAmount = (float) ($previous['category_totals'][$category] ?? 0);
            
            // Skip if no previous spending (Edge case: avoid new category alerts)
            if ($previousAmount <= 0) continue;
            
            $increase = ($currentAmount - $previousAmount) / $previousAmount;
            
            if ($increase > self::CATEGORY_OVERSPEND_THRESHOLD) {
                $triggers[] = [
                    'rule_id' => 'category_overspend',
                    'triggered' => true,
                    'data' => [
                        'category' => $category,
                        'current_week_amount' => (float) $currentAmount,
                        'previous_week_amount' => (float) $previousAmount,
                        'increase_percentage' => (float) round($increase * 100, 2),
                        'threshold' => (float) (self::CATEGORY_OVERSPEND_THRESHOLD * 100)
                    ]
                ];
            }
        }
        
        return $triggers;
    }

    private function checkWeeklySpendingSpike(array $current, array $previous): array
    {
        $currentTotal = (float) $current['total_expenses'];
        $previousTotal = (float) $previous['total_expenses'];

        // Edge case: No previous week data
        if ($previousTotal <= 0) {
            return ['rule_id' => 'weekly_spending_spike', 'triggered' => false, 'data' => []];
        }

        $increase = ($currentTotal - $previousTotal) / $previousTotal;

        return [
            'rule_id' => 'weekly_spending_spike',
            'triggered' => $increase > self::WEEKLY_SPIKE_THRESHOLD,
            'data' => [
                'current_week_total' => $currentTotal,
                'previous_week_total' => $previousTotal,
                'increase_percentage' => (float) round($increase * 100, 2),
                'threshold' => (float) (self::WEEKLY_SPIKE_THRESHOLD * 100)
            ]
        ];
    }

    private function checkFrequentSmallPurchases(array $current): array
    {
        $count = (int) $current['small_transaction_count'];
        $triggered = $count >= self::SMALL_PURCHASE_COUNT_THRESHOLD;

        return [
            'rule_id' => 'frequent_small_purchases',
            'triggered' => $triggered,
            'data' => [
                'transaction_count' => $count,
                'total_amount' => (float) $current['small_transaction_total'],
                'average_amount' => $count > 0 ? (float) round($current['small_transaction_total'] / $count, 2) : 0.0,
                'count_threshold' => (int) self::SMALL_PURCHASE_COUNT_THRESHOLD,
                'amount_limit' => (float) self::SMALL_PURCHASE_AMOUNT_LIMIT
            ]
        ];
    }
}
