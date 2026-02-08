<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed categories for testing
    Category::create(['name' => 'Salary', 'type' => 'income', 'is_default' => true]);
    Category::create(['name' => 'Food & Dining', 'type' => 'expense', 'is_default' => true]);
});

test('category has correct fillable attributes', function () {
    $category = new Category;

    expect($category->getFillable())->toContain('name', 'type', 'color', 'icon', 'is_default');
});

test('category has transactions relationship', function () {
    $category = Category::first();

    expect($category->transactions())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('category casts is_default to boolean', function () {
    $category = Category::create([
        'name' => 'Test Category',
        'type' => 'income',
        'is_default' => '1',
    ]);

    expect($category->is_default)->toBeBool();
});

test('can create income category', function () {
    $category = Category::create([
        'name' => 'Freelance',
        'type' => 'income',
        'is_default' => false,
    ]);

    expect($category->name)->toBe('Freelance')
        ->and($category->type)->toBe('income')
        ->and($category->is_default)->toBeFalse();
});

test('can create expense category', function () {
    $category = Category::create([
        'name' => 'Entertainment',
        'type' => 'expense',
        'is_default' => false,
    ]);

    expect($category->name)->toBe('Entertainment')
        ->and($category->type)->toBe('expense');
});

test('category name must be unique', function () {
    Category::create(['name' => 'Duplicate', 'type' => 'income']);

    expect(fn () => Category::create(['name' => 'Duplicate', 'type' => 'expense']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
