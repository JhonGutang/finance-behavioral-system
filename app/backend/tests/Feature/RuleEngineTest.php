<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
    $this->category = Category::create(['name' => 'Food', 'type' => 'expense', 'user_id' => $this->user->id]);
});

test('rule engine returns empty triggers for new user (no history)', function () {
    $response = $this->getJson('/api/rules/evaluate?date=2026-01-20');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'triggered_rules' => []
            ]
        ]);
});

test('Rule 1: triggers category overspend when WoW increase > 25%', function () {
    // Previous Week (Jan 12 - Jan 18)
    Transaction::create([
        'type' => 'expense', 'amount' => 100, 'date' => '2026-01-15',
        'category_id' => $this->category->id, 'user_id' => $this->user->id
    ]);

    // Current Week (Jan 19 - Jan 25)
    Transaction::create([
        'type' => 'expense', 'amount' => 130, 'date' => '2026-01-20',
        'category_id' => $this->category->id, 'user_id' => $this->user->id
    ]);

    $response = $this->getJson('/api/rules/evaluate?date=2026-01-20');

    $response->assertStatus(200);
    $triggered = collect($response->json('data.triggered_rules'));
    
    $rule = $triggered->firstWhere('rule_id', 'category_overspend');
    expect($rule)->not->toBeNull()
        ->and($rule['data']['category'])->toBe('Food')
        ->and($rule['data']['increase_percentage'])->toEqual(30.0);
});

test('Rule 2: triggers weekly spending spike when WoW increase > 20%', function () {
    // Previous Week Total: 500
    Transaction::create([
        'type' => 'expense', 'amount' => 500, 'date' => '2026-01-15',
        'category_id' => $this->category->id, 'user_id' => $this->user->id
    ]);

    // Current Week Total: 650 (30% increase)
    Transaction::create([
        'type' => 'expense', 'amount' => 650, 'date' => '2026-01-20',
        'category_id' => $this->category->id, 'user_id' => $this->user->id
    ]);

    $response = $this->getJson('/api/rules/evaluate?date=2026-01-20');

    $triggered = collect($response->json('data.triggered_rules'));
    $rule = $triggered->firstWhere('rule_id', 'weekly_spending_spike');
    
    expect($rule)->not->toBeNull()
        ->and($rule['triggered'])->toBeTrue()
        ->and($rule['data']['increase_percentage'])->toEqual(30.0);
});

test('Rule 3: triggers frequent small purchases when count >= 10 and amount < 10', function () {
    // Create 10 small transactions in current week
    for ($i = 0; $i < 10; $i++) {
        Transaction::create([
            'type' => 'expense', 'amount' => 5, 'date' => '2026-01-20',
            'category_id' => $this->category->id, 'user_id' => $this->user->id
        ]);
    }

    $response = $this->getJson('/api/rules/evaluate?date=2026-01-20');

    $triggered = collect($response->json('data.triggered_rules'));
    $rule = $triggered->firstWhere('rule_id', 'frequent_small_purchases');
    
    expect($rule)->not->toBeNull()
        ->and($rule['triggered'])->toBeTrue()
        ->and($rule['data']['transaction_count'])->toBe(10)
        ->and($rule['data']['average_amount'])->toEqual(5.0);
});

test('does not trigger category overspend if previous week was zero', function () {
    // No transactions in previous week
    
    // Current Week
    Transaction::create([
        'type' => 'expense', 'amount' => 100, 'date' => '2026-01-20',
        'category_id' => $this->category->id, 'user_id' => $this->user->id
    ]);

    $response = $this->getJson('/api/rules/evaluate?date=2026-01-20');

    $triggered = collect($response->json('data.triggered_rules'));
    expect($triggered->firstWhere('rule_id', 'category_overspend'))->toBeNull();
});

test('it works with the demo seeder data', function () {
    $this->seed(\Database\Seeders\RuleEngineSeeder::class);
    
    $user = User::where('email', 'demo@example.com')->first();
    Sanctum::actingAs($user);

    // Evaluation date from the seeder is Jan 20, 2026
    $response = $this->getJson('/api/rules/evaluate?date=2026-01-20');

    $response->assertStatus(200);
    $triggered = collect($response->json('data.triggered_rules'));

    // Check all three rules are triggered as per seeder design
    expect($triggered->where('rule_id', 'category_overspend')->count())->toBeGreaterThan(0)
        ->and($triggered->where('rule_id', 'weekly_spending_spike')->count())->toBeGreaterThan(0)
        ->and($triggered->where('rule_id', 'frequent_small_purchases')->count())->toBeGreaterThan(0);
});
