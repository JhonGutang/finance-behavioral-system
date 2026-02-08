<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Carbon\Carbon;

class AnalyticsService
{
    public function __construct(
        private TransactionRepository $transactionRepository
    ) {}

    public function getDailyFlow(int $days, int $userId): array
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays($days - 1);

        // Get all transactions in the date range using Eloquent
        $transactions = Transaction::where('user_id', $userId)
            ->whereDate('date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('date', '<=', $endDate->format('Y-m-d'))
            ->get();

        // Initialize arrays for all dates in range
        $labels = [];
        $income = [];
        $expenses = [];

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::today()->subDays($days - 1 - $i);
            $labels[] = $date->format('M j'); // e.g., "Jan 23"
            $income[] = 0.0;
            $expenses[] = 0.0;
        }

        // Group transactions by date and type
        $grouped = $transactions->groupBy(function ($transaction) {
            return Carbon::parse($transaction->date)->format('Y-m-d');
        });

        foreach ($grouped as $date => $dateTransactions) {
            $transactionDate = Carbon::parse($date);
            $dayIndex = $startDate->diffInDays($transactionDate);

            if ($dayIndex >= 0 && $dayIndex < $days) {
                $incomeSum = $dateTransactions->where('type', 'income')->sum('amount');
                $expenseSum = $dateTransactions->where('type', 'expense')->sum('amount');

                if ($incomeSum > 0) {
                    $income[$dayIndex] = (float) $incomeSum;
                }
                if ($expenseSum > 0) {
                    $expenses[$dayIndex] = (float) $expenseSum;
                }
            }
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expenses' => $expenses,
        ];
    }

    public function getMonthlyFlow(int $months, int $userId): array
    {
        $endDate = Carbon::today()->endOfMonth();
        $startDate = Carbon::today()->subMonths($months - 1)->startOfMonth();

        // Get all transactions in the date range using Eloquent
        $transactions = Transaction::where('user_id', $userId)
            ->whereDate('date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('date', '<=', $endDate->format('Y-m-d'))
            ->get();

        // Initialize arrays for all months in range
        $labels = [];
        $income = [];
        $expenses = [];

        for ($i = 0; $i < $months; $i++) {
            $date = Carbon::today()->subMonths($months - 1 - $i)->startOfMonth();
            $labels[] = $date->format('M Y'); // e.g., "Jan 2026"
            $income[] = 0.0;
            $expenses[] = 0.0;
        }

        // Group transactions by year-month and type
        $grouped = $transactions->groupBy(function ($transaction) {
            $date = Carbon::parse($transaction->date);

            return $date->format('Y-m');
        });

        foreach ($grouped as $yearMonth => $monthTransactions) {
            $transactionDate = Carbon::parse($yearMonth.'-01');
            $monthIndex = $startDate->diffInMonths($transactionDate);

            if ($monthIndex >= 0 && $monthIndex < $months) {
                $incomeSum = $monthTransactions->where('type', 'income')->sum('amount');
                $expenseSum = $monthTransactions->where('type', 'expense')->sum('amount');

                if ($incomeSum > 0) {
                    $income[$monthIndex] = (float) $incomeSum;
                }
                if ($expenseSum > 0) {
                    $expenses[$monthIndex] = (float) $expenseSum;
                }
            }
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expenses' => $expenses,
        ];
    }

    /**
     * Get yearly flow data for all available years.
     *
     * @return array Array with labels, income, and expenses
     */
    public function getYearlyFlow(int $userId): array
    {
        // Get all transactions using Eloquent
        $transactions = Transaction::where('user_id', $userId)->get();

        if ($transactions->isEmpty()) {
            return [
                'labels' => [],
                'income' => [],
                'expenses' => [],
            ];
        }

        // Group transactions by year
        $grouped = $transactions->groupBy(function ($transaction) {
            return Carbon::parse($transaction->date)->format('Y');
        });

        // Get sorted years
        $years = $grouped->keys()->sort()->values()->toArray();

        // Initialize arrays
        $labels = [];
        $income = [];
        $expenses = [];

        foreach ($years as $year) {
            $labels[] = (string) $year;

            $yearTransactions = $grouped[$year];
            $incomeSum = $yearTransactions->where('type', 'income')->sum('amount');
            $expenseSum = $yearTransactions->where('type', 'expense')->sum('amount');

            $income[] = (float) $incomeSum;
            $expenses[] = (float) $expenseSum;
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expenses' => $expenses,
        ];
    }
}
