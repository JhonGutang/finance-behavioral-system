<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new TransactionRepository;
    $this->service = new AnalyticsService($this->repository);

    $this->user = User::factory()->create();

    // Create test categories
    $this->incomeCategory = Category::create(['name' => 'Salary', 'type' => 'income', 'user_id' => $this->user->id]);
    $this->expenseCategory = Category::create(['name' => 'Food', 'type' => 'expense', 'user_id' => $this->user->id]);
});

describe('getDailyFlow', function () {
    test('returns empty arrays when no transactions exist', function () {
        $result = $this->service->getDailyFlow(7, $this->user->id);

        expect($result)->toHaveKeys(['labels', 'income', 'expenses'])
            ->and($result['labels'])->toHaveCount(7)
            ->and($result['income'])->toHaveCount(7)
            ->and($result['expenses'])->toHaveCount(7)
            ->and(array_sum($result['income']))->toBe(0.0)
            ->and(array_sum($result['expenses']))->toBe(0.0);
    });

    test('aggregates transactions by date correctly', function () {
        // Create transactions for today and yesterday
        Transaction::create([
            'type' => 'income',
            'amount' => 1000,
            'date' => Carbon::today(),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        Transaction::create([
            'type' => 'expense',
            'amount' => 150,
            'date' => Carbon::today(),
            'category_id' => $this->expenseCategory->id,
            'user_id' => $this->user->id,
        ]);

        Transaction::create([
            'type' => 'expense',
            'amount' => 50,
            'date' => Carbon::yesterday(),
            'category_id' => $this->expenseCategory->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->service->getDailyFlow(7, $this->user->id);

        expect($result['labels'])->toHaveCount(7)
            ->and($result['income'])->toContain(1000.0)
            ->and($result['expenses'])->toContain(150.0)
            ->and($result['expenses'])->toContain(50.0);
    });

    test('handles multiple transactions on same day', function () {
        // Create multiple transactions on the same day
        Transaction::create([
            'type' => 'income',
            'amount' => 500,
            'date' => Carbon::today(),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        Transaction::create([
            'type' => 'income',
            'amount' => 300,
            'date' => Carbon::today(),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->service->getDailyFlow(7, $this->user->id);

        // Should sum to 800
        expect($result['income'])->toContain(800.0);
    });

    test('respects custom days parameter', function () {
        $result = $this->service->getDailyFlow(14, $this->user->id);

        expect($result['labels'])->toHaveCount(14)
            ->and($result['income'])->toHaveCount(14)
            ->and($result['expenses'])->toHaveCount(14);
    });

    test('formats date labels correctly', function () {
        $result = $this->service->getDailyFlow(3, $this->user->id);

        // Labels should be in "M j" format (e.g., "Jan 23")
        foreach ($result['labels'] as $label) {
            expect($label)->toMatch('/^[A-Z][a-z]{2} \d{1,2}$/');
        }
    });
});

describe('getMonthlyFlow', function () {
    test('returns empty arrays when no transactions exist', function () {
        $result = $this->service->getMonthlyFlow(6, $this->user->id);

        expect($result)->toHaveKeys(['labels', 'income', 'expenses'])
            ->and($result['labels'])->toHaveCount(6)
            ->and($result['income'])->toHaveCount(6)
            ->and($result['expenses'])->toHaveCount(6)
            ->and(array_sum($result['income']))->toBe(0.0)
            ->and(array_sum($result['expenses']))->toBe(0.0);
    });

    test('aggregates transactions by month correctly', function () {
        // Create transactions for this month and last month
        Transaction::create([
            'type' => 'income',
            'amount' => 5000,
            'date' => Carbon::now()->startOfMonth(),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        Transaction::create([
            'type' => 'expense',
            'amount' => 2000,
            'date' => Carbon::now()->startOfMonth(),
            'category_id' => $this->expenseCategory->id,
            'user_id' => $this->user->id,
        ]);

        Transaction::create([
            'type' => 'income',
            'amount' => 4500,
            'date' => Carbon::now()->subMonth()->startOfMonth(),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->service->getMonthlyFlow(12, $this->user->id);

        expect($result['labels'])->toHaveCount(12)
            ->and($result['income'])->toContain(5000.0)
            ->and($result['income'])->toContain(4500.0)
            ->and($result['expenses'])->toContain(2000.0);
    });

    test('handles multiple transactions in same month', function () {
        Transaction::create([
            'type' => 'expense',
            'amount' => 100,
            'date' => Carbon::now()->startOfMonth(),
            'category_id' => $this->expenseCategory->id,
            'user_id' => $this->user->id,
        ]);

        Transaction::create([
            'type' => 'expense',
            'amount' => 200,
            'date' => Carbon::now()->startOfMonth()->addDays(10),
            'category_id' => $this->expenseCategory->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->service->getMonthlyFlow(12, $this->user->id);

        // Should sum to 300
        expect($result['expenses'])->toContain(300.0);
    });

    test('respects custom months parameter', function () {
        $result = $this->service->getMonthlyFlow(6, $this->user->id);

        expect($result['labels'])->toHaveCount(6)
            ->and($result['income'])->toHaveCount(6)
            ->and($result['expenses'])->toHaveCount(6);
    });

    test('formats month labels correctly', function () {
        $result = $this->service->getMonthlyFlow(3, $this->user->id);

        // Labels should be in "M Y" format (e.g., "Jan 2026")
        foreach ($result['labels'] as $label) {
            expect($label)->toMatch('/^[A-Z][a-z]{2} \d{4}$/');
        }
    });
});

describe('getYearlyFlow', function () {
    test('returns empty arrays when no transactions exist', function () {
        $result = $this->service->getYearlyFlow($this->user->id);

        expect($result)->toHaveKeys(['labels', 'income', 'expenses'])
            ->and($result['labels'])->toBeArray()
            ->and($result['income'])->toBeArray()
            ->and($result['expenses'])->toBeArray()
            ->and($result['labels'])->toHaveCount(0);
    });

    test('aggregates transactions by year correctly', function () {
        // Create transactions for different years
        Transaction::create([
            'type' => 'income',
            'amount' => 60000,
            'date' => Carbon::create(2026, 1, 1),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        Transaction::create([
            'type' => 'expense',
            'amount' => 30000,
            'date' => Carbon::create(2026, 6, 1),
            'category_id' => $this->expenseCategory->id,
            'user_id' => $this->user->id,
        ]);

        Transaction::create([
            'type' => 'income',
            'amount' => 55000,
            'date' => Carbon::create(2025, 1, 1),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->service->getYearlyFlow($this->user->id);

        expect($result['labels'])->toContain('2025')
            ->and($result['labels'])->toContain('2026')
            ->and($result['income'])->toContain(55000.0)
            ->and($result['income'])->toContain(60000.0)
            ->and($result['expenses'])->toContain(30000.0);
    });

    test('handles multiple transactions in same year', function () {
        Transaction::create([
            'type' => 'income',
            'amount' => 30000,
            'date' => Carbon::create(2026, 1, 1),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        Transaction::create([
            'type' => 'income',
            'amount' => 30000,
            'date' => Carbon::create(2026, 7, 1),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->service->getYearlyFlow($this->user->id);

        // Should sum to 60000
        expect($result['income'])->toContain(60000.0);
    });

    test('returns years in chronological order', function () {
        // Create transactions in reverse chronological order
        Transaction::create([
            'type' => 'income',
            'amount' => 1000,
            'date' => Carbon::create(2026, 1, 1),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        Transaction::create([
            'type' => 'income',
            'amount' => 1000,
            'date' => Carbon::create(2024, 1, 1),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        Transaction::create([
            'type' => 'income',
            'amount' => 1000,
            'date' => Carbon::create(2025, 1, 1),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->service->getYearlyFlow($this->user->id);

        expect($result['labels'])->toBe(['2024', '2025', '2026']);
    });
});

describe('edge cases', function () {
    test('handles decimal amounts correctly', function () {
        Transaction::create([
            'type' => 'expense',
            'amount' => 123.45,
            'date' => Carbon::today(),
            'category_id' => $this->expenseCategory->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->service->getDailyFlow(7, $this->user->id);

        expect($result['expenses'])->toContain(123.45);
    });

    test('handles large amounts correctly', function () {
        Transaction::create([
            'type' => 'income',
            'amount' => 9999999.99,
            'date' => Carbon::today(),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->service->getDailyFlow(7, $this->user->id);

        expect($result['income'])->toContain(9999999.99);
    });

    test('handles single transaction correctly', function () {
        Transaction::create([
            'type' => 'income',
            'amount' => 100,
            'date' => Carbon::today(),
            'category_id' => $this->incomeCategory->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->service->getDailyFlow(7, $this->user->id);

        expect($result['income'])->toContain(100.0)
            ->and(array_filter($result['income']))->toHaveCount(1);
    });
});
