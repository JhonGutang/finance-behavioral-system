<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);

    $this->category = Category::create(['name' => 'Food', 'type' => 'expense', 'user_id' => $this->user->id]);

    Transaction::create([
        'type' => 'expense',
        'amount' => 150,
        'date' => '2026-01-15',
        'category_id' => $this->category->id,
        'user_id' => $this->user->id,
    ]);
});

test('can list all transactions', function () {
    $response = $this->getJson('/api/transactions');

    $response->assertStatus(200)
        ->assertJson(['success' => true])
        ->assertJsonCount(1, 'data');
});

test('can filter transactions by type', function () {
    $response = $this->getJson('/api/transactions?type=expense');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can get single transaction', function () {
    $transaction = Transaction::first();

    $response = $this->getJson("/api/transactions/{$transaction->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'amount' => '150.00',
            ],
        ]);
});

test('can create transaction', function () {
    $data = [
        'type' => 'expense',
        'amount' => 75.50,
        'date' => now()->format('Y-m-d'),
        'category_id' => $this->category->id,
        'description' => 'Test transaction',
    ];

    $response = $this->postJson('/api/transactions', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'amount' => '75.50',
                'description' => 'Test transaction',
            ],
        ]);

    expect(Transaction::count())->toBe(2);
});

test('validates transaction creation', function () {
    $response = $this->postJson('/api/transactions', []);

    $response->assertStatus(422)
        ->assertJson(['success' => false])
        ->assertJsonValidationErrors(['type', 'amount', 'date', 'category_id']);
});

test('validates amount must be positive', function () {
    $response = $this->postJson('/api/transactions', [
        'type' => 'expense',
        'amount' => -100,
        'date' => now()->format('Y-m-d'),
        'category_id' => $this->category->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['amount']);
});

test('validates date cannot be in future', function () {
    $response = $this->postJson('/api/transactions', [
        'type' => 'expense',
        'amount' => 100,
        'date' => now()->addDay()->format('Y-m-d'),
        'category_id' => $this->category->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['date']);
});

test('can update transaction', function () {
    $transaction = Transaction::first();

    $response = $this->putJson("/api/transactions/{$transaction->id}", [
        'type' => 'expense',
        'amount' => 200,
        'date' => $transaction->date->format('Y-m-d'),
        'category_id' => $this->category->id,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => ['amount' => '200.00'],
        ]);
});

test('can delete transaction', function () {
    $transaction = Transaction::first();

    $response = $this->deleteJson("/api/transactions/{$transaction->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    expect(Transaction::count())->toBe(0);
});

test('can get summary', function () {
    $response = $this->getJson('/api/transactions/summary');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_expenses' => 150.0,
                    'transaction_count' => 1,
                ],
            ],
        ]);
});

describe('analytics endpoints', function () {
    test('can get daily analytics', function () {
        $response = $this->getJson('/api/transactions/analytics/daily');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'labels',
                    'income',
                    'expenses',
                ],
            ]);

        $data = $response->json('data');
        expect($data['labels'])->toHaveCount(30)
            ->and($data['income'])->toHaveCount(30)
            ->and($data['expenses'])->toHaveCount(30);
    });

    test('can get daily analytics with custom days parameter', function () {
        $response = $this->getJson('/api/transactions/analytics/daily?days=7');

        $response->assertStatus(200);

        $data = $response->json('data');
        expect($data['labels'])->toHaveCount(7)
            ->and($data['income'])->toHaveCount(7)
            ->and($data['expenses'])->toHaveCount(7);
    });

    test('can get monthly analytics', function () {
        $response = $this->getJson('/api/transactions/analytics/monthly');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'labels',
                    'income',
                    'expenses',
                ],
            ]);

        $data = $response->json('data');
        expect($data['labels'])->toHaveCount(12)
            ->and($data['income'])->toHaveCount(12)
            ->and($data['expenses'])->toHaveCount(12);
    });

    test('can get monthly analytics with custom months parameter', function () {
        $response = $this->getJson('/api/transactions/analytics/monthly?months=6');

        $response->assertStatus(200);

        $data = $response->json('data');
        expect($data['labels'])->toHaveCount(6)
            ->and($data['income'])->toHaveCount(6)
            ->and($data['expenses'])->toHaveCount(6);
    });

    test('can get yearly analytics', function () {
        $response = $this->getJson('/api/transactions/analytics/yearly');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'labels',
                    'income',
                    'expenses',
                ],
            ]);
    });

    test('analytics endpoints return correct data types', function () {
        $response = $this->getJson('/api/transactions/analytics/daily');

        $data = $response->json('data');

        expect($data['labels'])->toBeArray()
            ->and($data['income'])->toBeArray()
            ->and($data['expenses'])->toBeArray()
            ->and($data['income'][0])->toBeNumeric()
            ->and($data['expenses'][0])->toBeNumeric();
    });
});
