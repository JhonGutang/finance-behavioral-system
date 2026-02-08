<?php

namespace App\Services;

class FeedbackTemplateLibrary
{
    public static function getTemplates(): array
    {
        return [
            'category_overspend' => [
                'basic' => [
                    'template_id' => 'category_overspend_basic',
                    'explanation' => 'You spent ₱${current_week_amount} in ${category} this week, which is ${increase_percentage}% higher than last week (₱${previous_week_amount}).',
                    'suggestion' => 'Try limiting ${category} spending to ₱${target_amount} next week.',
                    'placeholders' => ['current_week_amount', 'category', 'increase_percentage', 'previous_week_amount', 'target_amount'],
                    'priority' => 8,
                ],
                'advanced' => [
                    'template_id' => 'category_overspend_advanced',
                    'explanation' => 'Your ${category} spending increased by ${increase_percentage}% (₱${current_week_amount} vs ₱${previous_week_amount}). Over the past 4 weeks, your average is ₱${four_week_average}.',
                    'suggestion' => 'Consider setting a weekly ${category} budget to stay below your ₱${four_week_average} average. Success here will help stabilize your long-term habits.',
                    'placeholders' => ['category', 'increase_percentage', 'current_week_amount', 'previous_week_amount', 'four_week_average'],
                    'priority' => 8,
                ],
            ],
            'weekly_spending_spike' => [
                'basic' => [
                    'template_id' => 'weekly_spike_basic',
                    'explanation' => 'Your total spending this week (₱${current_week_total}) is ${increase_percentage}% higher than last week (₱${previous_week_total}).',
                    'suggestion' => 'Review your transactions to identify unexpected expenses. Try to reduce discretionary spending next week.',
                    'placeholders' => ['current_week_total', 'increase_percentage', 'previous_week_total'],
                    'priority' => 9,
                ],
                'advanced' => [
                    'template_id' => 'weekly_spike_advanced',
                    'explanation' => 'Weekly spending increased ${increase_percentage}% to ₱${current_week_total}. Your current 4-week average is ₱${four_week_average}.',
                    'suggestion' => 'Focus on reducing discretionary categories to get back below your ₱${four_week_average} baseline. Stability is key to financial health.',
                    'placeholders' => ['increase_percentage', 'current_week_total', 'four_week_average'],
                    'priority' => 9,
                ],
            ],
            'frequent_small_purchases' => [
                'basic' => [
                    'template_id' => 'small_purchases_basic',
                    'explanation' => 'You made ${transaction_count} small purchases (under ₱${amount_limit}) this week, totaling ₱${total_amount}.',
                    'suggestion' => 'Small purchases add up quickly. Try consolidating purchases or setting a daily spending limit.',
                    'placeholders' => ['transaction_count', 'amount_limit', 'total_amount'],
                    'priority' => 6,
                ],
                'advanced' => [
                    'template_id' => 'small_purchases_advanced',
                    'explanation' => '${transaction_count} small purchases totaling ₱${total_amount} (avg: ₱${average_amount}). These minor leaks can drain your budget over time.',
                    'suggestion' => 'Implement a "wait 24 hours" rule for purchases under ₱${amount_limit}. This small friction can help you save significantly over the next month.',
                    'placeholders' => ['transaction_count', 'total_amount', 'average_amount', 'amount_limit'],
                    'priority' => 6,
                ],
            ],
        ];
    }

    public static function getTemplate(string $ruleId, string $level): ?array
    {
        $templates = self::getTemplates();

        return $templates[$ruleId][$level] ?? null;
    }
}
