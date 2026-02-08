<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
    $this->category = Category::create(['name' => 'Food', 'type' => 'expense', 'user_id' => $this->user->id]);
});

test('it generates feedback when a rule is triggered', function () {
    // Previous Week
    Transaction::create([
        'type' => 'expense', 'amount' => 100, 'date' => '2026-01-15',
        'category_id' => $this->category->id, 'user_id' => $this->user->id,
    ]);

    // Current Week (30% increase)
    Transaction::create([
        'type' => 'expense', 'amount' => 130, 'date' => '2026-01-20',
        'category_id' => $this->category->id, 'user_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/rules/evaluate?date=2026-01-20');

    $response->assertStatus(200);

    // Check feedback was generated in response
    $feedback = $response->json('data.feedback');
    expect($feedback)->toHaveCount(2); // Category overspend + potentially weekly spike (total expenses also increased)

    // Check database entries
    $this->assertDatabaseHas('feedback_histories', [
        'user_id' => $this->user->id,
        'rule_id' => 'category_overspend',
        'level' => 'basic',
    ]);

    $this->assertDatabaseHas('user_progress', [
        'user_id' => $this->user->id,
        'improvement_score' => 50,
    ]);
});

test('it selects advanced level feedback for improving users', function () {
    // Create progress history showing no violations for 2 weeks
    UserProgress::create([
        'user_id' => $this->user->id,
        'week_start' => '2026-01-05',
        'week_end' => '2026-01-11',
        'rules_triggered' => [],
        'rules_not_triggered' => ['category_overspend', 'weekly_spending_spike', 'frequent_small_purchases'],
        'improvement_score' => 80,
    ]);

    UserProgress::create([
        'user_id' => $this->user->id,
        'week_start' => '2025-12-29',
        'week_end' => '2026-01-04',
        'rules_triggered' => [],
        'rules_not_triggered' => ['category_overspend', 'weekly_spending_spike', 'frequent_small_purchases'],
        'improvement_score' => 80,
    ]);

    // Trigger a rule now (after 2 weeks of perfection)
    // Previous Week (Jan 12 - Jan 18)
    Transaction::create([
        'type' => 'expense', 'amount' => 100, 'date' => '2026-01-15',
        'category_id' => $this->category->id, 'user_id' => $this->user->id,
    ]);

    // Current Week (Jan 19 - Jan 25)
    Transaction::create([
        'type' => 'expense', 'amount' => 130, 'date' => '2026-01-20',
        'category_id' => $this->category->id, 'user_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/rules/evaluate?date=2026-01-20');

    // Should generate 'advanced' feedback because user was "improving" (no violations for 2+ weeks)
    $this->assertDatabaseHas('feedback_histories', [
        'user_id' => $this->user->id,
        'rule_id' => 'category_overspend',
        'level' => 'advanced',
    ]);
});
