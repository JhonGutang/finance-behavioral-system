<?php

use App\DTOs\DateRangeQueryDTO;
use App\DTOs\TransactionUpdateDTO;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new TransactionRepository;
    $this->user = User::factory()->create();

    $this->incomeCategory = Category::create(['name' => 'Salary', 'type' => 'income', 'user_id' => $this->user->id]);
    $this->expenseCategory = Category::create(['name' => 'Food', 'type' => 'expense', 'user_id' => $this->user->id]);

    Transaction::create([
        'type' => 'income',
        'amount' => 5000,
        'date' => '2026-01-15',
        'category_id' => $this->incomeCategory->id,
        'user_id' => $this->user->id,
    ]);

    Transaction::create([
        'type' => 'expense',
        'amount' => 150,
        'date' => '2026-01-16',
        'category_id' => $this->expenseCategory->id,
        'user_id' => $this->user->id,
    ]);
});

test('can get all transactions', function () {
    $transactions = $this->repository->getAll($this->user->id);

    expect($transactions)->toHaveCount(2)
        ->and($transactions->first()->category)->not->toBeNull();
});

test('can get transaction by id', function () {
    $transaction = Transaction::first();
    $found = $this->repository->getById($transaction->id, $this->user->id);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($transaction->id);
});

test('can get transactions by date range', function () {
    $transactions = $this->repository->getByDateRange(
        DateRangeQueryDTO::from($this->user->id, '2026-01-15', '2026-01-15')
    );

    expect($transactions)->toHaveCount(1)
        ->and($transactions->first()->date->format('Y-m-d'))->toBe('2026-01-15');
});

test('can get transactions by category', function () {
    $transactions = $this->repository->getByCategory($this->expenseCategory->id, $this->user->id);

    expect($transactions)->toHaveCount(1)
        ->and($transactions->first()->category_id)->toBe($this->expenseCategory->id);
});

test('can get transactions by type', function () {
    $incomeTransactions = $this->repository->getByType('income', $this->user->id);
    $expenseTransactions = $this->repository->getByType('expense', $this->user->id);

    expect($incomeTransactions)->toHaveCount(1)
        ->and($expenseTransactions)->toHaveCount(1);
});

test('can create transaction', function () {
    $data = [
        'type' => 'expense',
        'amount' => 75.50,
        'date' => now(),
        'category_id' => $this->expenseCategory->id,
        'description' => 'Test transaction',
        'user_id' => $this->user->id,
    ];

    $transaction = $this->repository->create($data);

    expect($transaction->amount)->toBe('75.50')
        ->and($transaction->description)->toBe('Test transaction')
        ->and(Transaction::count())->toBe(3);
});

test('can update transaction', function () {
    $transaction = Transaction::first();
    $updated = $this->repository->update(
        TransactionUpdateDTO::from($transaction->id, $this->user->id, ['amount' => 6000])
    );

    expect($updated)->toBeTrue()
        ->and($transaction->fresh()->amount)->toBe('6000.00');
});

test('can delete transaction', function () {
    $transaction = Transaction::first();
    $deleted = $this->repository->delete($transaction->id, $this->user->id);

    expect($deleted)->toBeTrue()
        ->and(Transaction::count())->toBe(1);
});

test('can get summary', function () {
    $summary = $this->repository->getSummary($this->user->id);

    expect($summary)->toHaveKeys(['total_income', 'total_expenses', 'net_balance', 'transaction_count'])
        ->and($summary['total_income'])->toBe(5000.0)
        ->and($summary['total_expenses'])->toBe(150.0)
        ->and($summary['net_balance'])->toBe(4850.0)
        ->and($summary['transaction_count'])->toBe(2);
});

test('can get expenses by category', function () {
    $expenses = $this->repository->getExpensesByCategory($this->user->id);

    expect($expenses)->toHaveCount(1)
        ->and($expenses->first()->category_id)->toBe($this->expenseCategory->id)
        ->and((float) $expenses->first()->total)->toBe(150.0);
});
