<?php

namespace App\Services;

use App\DTOs\FeedbackTemplateInputDTO;
use App\Repositories\FeedbackHistoryRepository;
use App\Repositories\UserProgressRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FeedbackEngineService
{
    protected FeedbackHistoryRepository $feedbackHistoryRepository;

    protected UserProgressRepository $userProgressRepository;

    public function __construct(
        FeedbackHistoryRepository $feedbackHistoryRepository,
        UserProgressRepository $userProgressRepository
    ) {
        $this->feedbackHistoryRepository = $feedbackHistoryRepository;
        $this->userProgressRepository = $userProgressRepository;
    }

    /**
     * Process evaluation results from RuleEngine and generate feedback.
     */
    public function processRuleResults(array $evaluationResults): array
    {
        $userId = $evaluationResults['user_id'];
        $weekStart = Carbon::parse($evaluationResults['weeks']['current']['start']);
        $weekEnd = Carbon::parse($evaluationResults['weeks']['current']['end']);

        $triggeredRules = $evaluationResults['triggered_rules'];

        // 1. Get User Progress History
        $history = $this->userProgressRepository->getRecent($userId);

        // 2. Determine Feedback Level ('basic' or 'advanced')
        $feedbackLevels = $this->determineFeedbackLevels($triggeredRules, $history);

        $generatedFeedback = [];
        $triggeredRuleIds = [];

        // 3. Generate Feedback for each triggered rule
        foreach ($triggeredRules as $ruleResult) {
            $ruleId = $ruleResult['rule_id'];
            $triggeredRuleIds[] = $ruleId;
            $level = $feedbackLevels[$ruleId] ?? 'basic';

            $template = FeedbackTemplateLibrary::getTemplate($ruleId, $level);

            if ($template) {
                $input = FeedbackTemplateInputDTO::from(
                    template: $template,
                    data: $ruleResult['data'],
                    userId: $userId,
                    ruleId: $ruleId,
                    level: $level
                );

                $feedbackData = $this->fillTemplate($input);

                $feedback = $this->feedbackHistoryRepository->updateOrCreate(
                    [
                        'user_id' => $userId,
                        'week_start' => $weekStart->toDateString(),
                        'rule_id' => $ruleId,
                        // For category overspend, differentiate by category name
                        'category_name' => $ruleResult['data']['category'] ?? null,
                    ],
                    [
                        'week_end' => $weekEnd->toDateString(),
                        'template_id' => $template['template_id'],
                        'level' => $level,
                        'explanation' => $feedbackData['explanation'],
                        'suggestion' => $feedbackData['suggestion'],
                        'data' => $ruleResult['data'],
                    ]
                );

                // Force timestamp update even if data didn't change
                $feedback->touch();

                $generatedFeedback[] = $feedback;
            }
        }

        // 4. Update User Progress for this week
        $allRuleIds = ['category_overspend', 'weekly_spending_spike', 'frequent_small_purchases'];
        $notTriggeredRuleIds = array_diff($allRuleIds, array_unique($triggeredRuleIds));

        $improvementScore = $this->calculateImprovementScore($triggeredRuleIds, $history);

        $progress = $this->userProgressRepository->updateOrCreate(
            ['user_id' => $userId, 'week_start' => $weekStart->toDateString()],
            [
                'week_end' => $weekEnd->toDateString(),
                'rules_triggered' => array_unique($triggeredRuleIds),
                'rules_not_triggered' => array_values($notTriggeredRuleIds),
                'improvement_score' => $improvementScore,
            ]
        );

        // Force timestamp update even if data didn't change
        $progress->touch();

        return $generatedFeedback;
    }

    /**
     * Determine if a rule should show 'basic' or 'advanced' feedback.
     */
    private function determineFeedbackLevels(array $triggeredRules, Collection $history): array
    {
        $levels = [];

        foreach ($triggeredRules as $rule) {
            $ruleId = $rule['rule_id'];

            // Count consecutive weeks this specific rule has triggered
            $consecutiveViolations = 0;
            foreach ($history as $progress) {
                if (in_array($ruleId, $progress->rules_triggered)) {
                    $consecutiveViolations++;
                } else {
                    break;
                }
            }

            // Count consecutive weeks this rule has NOT triggered (after being triggered before)
            // For simplicity in MVP, we check if it was absent in recent history
            $consecutiveImprovements = 0;
            foreach ($history as $progress) {
                if (! in_array($ruleId, $progress->rules_triggered)) {
                    $consecutiveImprovements++;
                } else {
                    break;
                }
            }

            if ($consecutiveViolations >= 2) {
                $levels[$ruleId] = 'basic'; // User is struggling
            } elseif ($consecutiveImprovements >= 2) {
                $levels[$ruleId] = 'advanced'; // User is improving
            } else {
                $levels[$ruleId] = 'basic'; // Default
            }
        }

        return $levels;
    }

    /**
     * Calculate an improvement score (0-100).
     */
    private function calculateImprovementScore(array $triggeredIds, Collection $history): int
    {
        if ($history->isEmpty()) {
            return 50;
        }

        $score = 50;
        $triggeredCount = count(array_unique($triggeredIds));

        // Fewer rules triggered = better score
        $score += (3 - $triggeredCount) * 10;

        // Compare with previous week
        $previous = $history->first();
        if ($previous) {
            $prevTriggeredCount = count($previous->rules_triggered);
            if ($triggeredCount < $prevTriggeredCount) {
                $score += 15;
            } elseif ($triggeredCount > $prevTriggeredCount) {
                $score -= 10;
            }
        }

        return (int) max(0, min(100, $score));
    }

    /**
     * Fill template placeholders with actual data, calculating advanced metrics if needed.
     */
    private function fillTemplate(FeedbackTemplateInputDTO $input): array
    {
        $template = $input->template;
        $data = $input->data;
        $userId = $input->userId;
        $ruleId = $input->ruleId;
        $level = $input->level;

        $explanation = $template['explanation'];
        $suggestion = $template['suggestion'];

        // Enrich data for advanced templates if needed
        if ($level === 'advanced') {
            $data = $this->enrichAdvancedData($data, $ruleId, $userId);
        }

        foreach ($template['placeholders'] as $placeholder) {
            $value = $data[$placeholder] ?? null;

            // Special derivation for target_amount if not in raw data
            if ($placeholder === 'target_amount' && ($value === null || $value === '')) {
                $current = $data['current_week_amount'] ?? $data['current_week_total'] ?? 0;
                $value = (float) $current * 0.9; // Suggest 10% reduction
            }

            // Fallback for placeholders that might be missing after enrichment
            if ($value === null) {
                $value = '[N/A]';
            }

            // Basic formatting
            $value = $this->formatPlaceholderValue($placeholder, $value);

            $explanation = str_replace('${'.$placeholder.'}', (string) $value, $explanation);
            $suggestion = str_replace('${'.$placeholder.'}', (string) $value, $suggestion);
        }

        return [
            'explanation' => $explanation,
            'suggestion' => $suggestion,
        ];
    }

    /**
     * Enrich data with historical calculations for advanced feedback.
     */
    private function enrichAdvancedData(array $data, string $ruleId, int $userId): array
    {
        $history = $this->getFeedbackHistory($userId, $ruleId);

        return match ($ruleId) {
            'category_overspend' => $this->enrichCategoryOverspend($data, $history),
            'weekly_spending_spike' => $this->enrichWeeklySpendingSpike($data, $history),
            'frequent_small_purchases' => $this->enrichFrequentSmallPurchases($data),
            default => $data,
        };
    }

    private function getFeedbackHistory(int $userId, string $ruleId): Collection
    {
        return $this->feedbackHistoryRepository->getByUserAndRule($userId, $ruleId);
    }

    private function enrichCategoryOverspend(array $data, Collection $history): array
    {
        $category = $data['category'] ?? null;
        if (! $category) {
            return $data;
        }

        $categoryHistory = $history->filter(fn ($f) => ($f->data['category'] ?? null) === $category);
        $amounts = $categoryHistory
            ->map(fn ($f) => $f->data['current_week_amount'] ?? 0)
            ->push($data['current_week_amount'] ?? 0);

        $data['four_week_average'] = $amounts->avg();

        return $data;
    }

    private function enrichWeeklySpendingSpike(array $data, Collection $history): array
    {
        $totals = $history
            ->map(fn ($f) => $f->data['current_week_total'] ?? 0)
            ->push($data['current_week_total'] ?? 0);

        $data['four_week_average'] = $totals->avg();

        return $data;
    }

    private function enrichFrequentSmallPurchases(array $data): array
    {
        $amounts = $data['small_purchase_amounts'] ?? [];

        if (! empty($amounts)) {
            $data['average_amount'] = array_sum($amounts) / count($amounts);
        } else {
            $data['average_amount'] = ($data['total_amount'] ?? 0) / ($data['transaction_count'] ?? 1);
        }

        return $data;
    }

    private function formatPlaceholderValue(string $placeholder, mixed $value): string
    {
        if (! is_numeric($value)) {
            return (string) $value;
        }

        if (Str::contains($placeholder, ['amount', 'total', 'average', 'limit', 'budget', 'target'])) {
            return '₱'.number_format((float) $value, 2);
        }

        if (Str::contains($placeholder, 'percentage')) {
            return number_format((float) $value, 2);
        }

        return (string) $value;
    }
}
