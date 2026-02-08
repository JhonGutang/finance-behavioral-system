<?php

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::create([
        'name' => 'Food & Dining',
        'type' => 'expense',
        'is_default' => true,
    ]);
});

test('transaction has correct fillable attributes', function () {
    $transaction = new Transaction;

    expect($transaction->getFillable())->toContain('type', 'amount', 'date', 'category_id', 'description');
});

test('transaction belongs to category', function () {
    $transaction = Transaction::factory()->create(['category_id' => $this->category->id]);

    expect($transaction->category)->toBeInstanceOf(Category::class)
        ->and($transaction->category->id)->toBe($this->category->id);
});

test('transaction casts amount to decimal', function () {
    $transaction = Transaction::create([
        'type' => 'expense',
        'amount' => 150.50,
        'date' => now(),
        'category_id' => $this->category->id,
    ]);

    expect($transaction->amount)->toBeString()
        ->and((float) $transaction->amount)->toBe(150.50);
});

test('transaction casts date correctly', function () {
    $transaction = Transaction::create([
        'type' => 'expense',
        'amount' => 100,
        'date' => '2026-01-21',
        'category_id' => $this->category->id,
    ]);

    expect($transaction->date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('can create income transaction', function () {
    $incomeCategory = Category::create(['name' => 'Salary', 'type' => 'income']);

    $transaction = Transaction::create([
        'type' => 'income',
        'amount' => 5000,
        'date' => now(),
        'category_id' => $incomeCategory->id,
        'description' => 'Monthly salary',
    ]);

    expect($transaction->type)->toBe('income')
        ->and($transaction->amount)->toBe('5000.00')
        ->and($transaction->description)->toBe('Monthly salary');
});

test('can create expense transaction', function () {
    $transaction = Transaction::create([
        'type' => 'expense',
        'amount' => 150.50,
        'date' => now(),
        'category_id' => $this->category->id,
    ]);

    expect($transaction->type)->toBe('expense')
        ->and((float) $transaction->amount)->toBe(150.50);
});

test('transaction requires category', function () {
    expect(fn () => Transaction::create([
        'type' => 'expense',
        'amount' => 100,
        'date' => now(),
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});
