<?php

use App\Models\Category;
use App\Models\FeedbackHistory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);

    $this->cat1 = Category::create(['name' => 'Food', 'type' => 'expense', 'user_id' => $this->user->id]);
    $this->cat2 = Category::create(['name' => 'Salary', 'type' => 'income', 'user_id' => $this->user->id]);

    // Create 15 transactions to test pagination
    for ($i = 1; $i <= 15; $i++) {
        Transaction::create([
            'type' => $i <= 10 ? 'expense' : 'income',
            'amount' => $i * 100,
            'date' => now()->subDays($i)->format('Y-m-d'),
            'category_id' => $i <= 10 ? $this->cat1->id : $this->cat2->id,
            'user_id' => $this->user->id,
            'description' => "Transaction $i",
        ]);
    }
});

test('transactions index returns paginated results', function () {
    $response = $this->getJson('/api/transactions?per_page=10');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

    $response->assertJsonCount(10, 'data');
    expect($response->json('meta.total'))->toBe(15)
        ->and($response->json('meta.last_page'))->toBe(2);
});

test('transactions can be filtered by multiple criteria', function () {
    // Filter by type=expense and min_amount=500
    $response = $this->getJson('/api/transactions?type=expense&min_amount=500');

    $response->assertStatus(200);
    $data = $response->json('data');

    // Expenses are 1 to 10 (100 to 1000). Min 500 means 500, 600, 700, 800, 900, 1000 (6 items)
    expect($data)->toHaveCount(6);
    foreach ($data as $item) {
        expect($item['type'])->toBe('expense')
            ->and((float) $item['amount'])->toBeGreaterThanOrEqual(500);
    }
});

test('transactions can be filtered by category', function () {
    $response = $this->getJson("/api/transactions?category_id={$this->cat2->id}");

    $response->assertStatus(200);
    $data = $response->json('data');

    // cat2 has items 11 to 15 (5 items)
    expect($data)->toHaveCount(5);
    foreach ($data as $item) {
        expect($item['category_id'])->toBe($this->cat2->id);
    }
});

test('transactions can be filtered by date range', function () {
    $startDate = now()->subDays(5)->format('Y-m-d');
    $endDate = now()->subDays(2)->format('Y-m-d');

    $response = $this->getJson("/api/transactions?start_date=$startDate&end_date=$endDate");

    $response->assertStatus(200);
    $data = $response->json('data');

    // Items are subDays(1) to subDays(15). Range [subDays(5), subDays(2)] means items 2, 3, 4, 5 (4 items)
    expect($data)->toHaveCount(4);
});

test('feedback history index returns paginated results', function () {
    // Create 12 feedback entries
    for ($i = 1; $i <= 12; $i++) {
        FeedbackHistory::create([
            'user_id' => $this->user->id,
            'rule_id' => 'test_rule',
            'template_id' => 'test_template',
            'level' => 'basic',
            'explanation' => 'Test explanation',
            'suggestion' => 'Test suggestion',
            'data' => [],
            'week_start' => now()->subWeeks($i)->startOfWeek()->format('Y-m-d'),
            'week_end' => now()->subWeeks($i)->endOfWeek()->format('Y-m-d'),
        ]);
    }

    $response = $this->getJson('/api/feedback?per_page=5');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data',
            'meta' => [
                'current_page',
                'last_page',
                'total',
            ],
        ]);

    $response->assertJsonCount(5, 'data');
    expect($response->json('meta.total'))->toBe(12)
        ->and($response->json('meta.last_page'))->toBe(3);
});
