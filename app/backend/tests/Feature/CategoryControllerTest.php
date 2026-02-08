<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);

    Category::create(['name' => 'Salary', 'type' => 'income', 'is_default' => true, 'user_id' => $this->user->id]);
    Category::create(['name' => 'Food', 'type' => 'expense', 'is_default' => true, 'user_id' => $this->user->id]);
});

test('can list all categories', function () {
    $response = $this->getJson('/api/categories');

    $response->assertStatus(200)
        ->assertJson(['success' => true])
        ->assertJsonCount(2, 'data');
});

test('can filter categories by type', function () {
    $response = $this->getJson('/api/categories?type=income');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can get single category', function () {
    $category = Category::first();

    $response = $this->getJson("/api/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
        ]);
});

test('returns 404 for non-existent category', function () {
    $response = $this->getJson('/api/categories/999');

    $response->assertStatus(404)
        ->assertJson(['success' => false]);
});

test('can create category', function () {
    $data = [
        'name' => 'Entertainment',
        'type' => 'expense',
    ];

    $response = $this->postJson('/api/categories', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Entertainment',
                'type' => 'expense',
            ],
        ]);

    expect(Category::count())->toBe(3);
});

test('validates category creation', function () {
    $response = $this->postJson('/api/categories', []);

    $response->assertStatus(422)
        ->assertJson(['success' => false])
        ->assertJsonValidationErrors(['name', 'type']);
});

test('can update category', function () {
    $category = Category::first();

    $response = $this->putJson("/api/categories/{$category->id}", [
        'name' => 'Updated Name',
        'type' => $category->type,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => ['name' => 'Updated Name'],
        ]);
});

test('can delete category without transactions', function () {
    $category = Category::first();

    $response = $this->deleteJson("/api/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    expect(Category::count())->toBe(1);
});

test('cannot delete category with transactions', function () {
    $category = Category::first();
    $category->transactions()->create([
        'type' => 'income',
        'amount' => 100,
        'date' => now(),
    ]);

    $response = $this->deleteJson("/api/categories/{$category->id}");

    $response->assertStatus(400)
        ->assertJson(['success' => false]);
});
