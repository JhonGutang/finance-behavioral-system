<?php

use App\DTOs\TransactionUpdateDTO;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new TransactionRepository;
    $this->service = new TransactionService($this->repository);

    $this->user = User::factory()->create();

    $this->category = Category::create(['name' => 'Food', 'type' => 'expense', 'user_id' => $this->user->id]);

    Transaction::create([
        'type' => 'expense',
        'amount' => 150,
        'date' => now(),
        'category_id' => $this->category->id,
        'user_id' => $this->user->id,
    ]);
});

test('can get all transactions', function () {
    $transactions = $this->service->getAllTransactions($this->user->id);

    expect($transactions)->toHaveCount(1);
});

test('can create valid transaction', function () {
    $data = [
        'type' => 'expense',
        'amount' => 75.50,
        'date' => now()->format('Y-m-d'),
        'category_id' => $this->category->id,
        'description' => 'Test',
    ];

    $transaction = $this->service->createTransaction($data, $this->user->id);

    expect($transaction->amount)->toBe('75.50')
        ->and(Transaction::count())->toBe(2);
});

test('validates transaction type is required', function () {
    expect(fn () => $this->service->createTransaction([
        'amount' => 100,
        'date' => now()->format('Y-m-d'),
        'category_id' => $this->category->id,
    ], $this->user->id))->toThrow(ValidationException::class);
});

test('validates transaction amount is required', function () {
    expect(fn () => $this->service->createTransaction([
        'type' => 'expense',
        'date' => now()->format('Y-m-d'),
        'category_id' => $this->category->id,
    ], $this->user->id))->toThrow(ValidationException::class);
});

test('validates transaction amount must be positive', function () {
    expect(fn () => $this->service->createTransaction([
        'type' => 'expense',
        'amount' => -100,
        'date' => now()->format('Y-m-d'),
        'category_id' => $this->category->id,
    ], $this->user->id))->toThrow(ValidationException::class);
});

test('validates transaction date is required', function () {
    expect(fn () => $this->service->createTransaction([
        'type' => 'expense',
        'amount' => 100,
        'category_id' => $this->category->id,
    ], $this->user->id))->toThrow(ValidationException::class);
});

test('validates transaction date cannot be in future', function () {
    expect(fn () => $this->service->createTransaction([
        'type' => 'expense',
        'amount' => 100,
        'date' => now()->addDay()->format('Y-m-d'),
        'category_id' => $this->category->id,
    ], $this->user->id))->toThrow(ValidationException::class);
});

test('validates category must exist', function () {
    expect(fn () => $this->service->createTransaction([
        'type' => 'expense',
        'amount' => 100,
        'date' => now()->format('Y-m-d'),
        'category_id' => 999,
    ], $this->user->id))->toThrow(ValidationException::class);
});

test('can get summary', function () {
    $summary = $this->service->getSummary($this->user->id);

    expect($summary)->toHaveKeys(['total_income', 'total_expenses', 'net_balance', 'transaction_count'])
        ->and($summary['total_expenses'])->toBe(150.0);
});

test('can update transaction', function () {
    $transaction = Transaction::first();
    $updated = $this->service->updateTransaction(
        TransactionUpdateDTO::from($transaction->id, $this->user->id, ['amount' => 200])
    );

    expect($updated)->toBeTrue()
        ->and($transaction->fresh()->amount)->toBe('200.00');
});

test('can delete transaction', function () {
    $transaction = Transaction::first();
    $deleted = $this->service->deleteTransaction($transaction->id, $this->user->id);

    expect($deleted)->toBeTrue()
        ->and(Transaction::count())->toBe(0);
});
