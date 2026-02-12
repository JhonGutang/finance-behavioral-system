<?php

use App\DTOs\CategoryUpdateDTO;
use App\Models\Category;
use App\Models\User;
use App\Repositories\CategoryRepository;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new CategoryRepository;
    $this->service = new CategoryService($this->repository);

    $this->user = User::factory()->create();

    Category::create(['name' => 'Salary', 'type' => 'income', 'is_default' => true, 'user_id' => $this->user->id]);
    Category::create(['name' => 'Food', 'type' => 'expense', 'is_default' => true, 'user_id' => $this->user->id]);
});

test('can get all categories', function () {
    $categories = $this->service->getAllCategories($this->user->id);

    expect($categories)->toHaveCount(2);
});

test('can get categories by type', function () {
    $incomeCategories = $this->service->getCategoriesByType('income', $this->user->id);

    expect($incomeCategories)->toHaveCount(1)
        ->and($incomeCategories->first()->type)->toBe('income');
});

test('can create valid category', function () {
    $data = [
        'name' => 'Entertainment',
        'type' => 'expense',
    ];

    $category = $this->service->createCategory($data, $this->user->id);

    expect($category->name)->toBe('Entertainment')
        ->and(Category::count())->toBe(3);
});

test('validates category name is required', function () {
    expect(fn () => $this->service->createCategory(['type' => 'expense'], $this->user->id))
        ->toThrow(ValidationException::class);
});

test('validates category type is required', function () {
    expect(fn () => $this->service->createCategory(['name' => 'Test'], $this->user->id))
        ->toThrow(ValidationException::class);
});

test('validates category type must be income or expense', function () {
    expect(fn () => $this->service->createCategory([
        'name' => 'Test',
        'type' => 'invalid',
    ], $this->user->id))->toThrow(ValidationException::class);
});

test('validates category name must be unique', function () {
    expect(fn () => $this->service->createCategory([
        'name' => 'Salary',
        'type' => 'income',
    ], $this->user->id))->toThrow(ValidationException::class);
});

test('validates color must be hex format', function () {
    expect(fn () => $this->service->createCategory([
        'name' => 'Test',
        'type' => 'income',
        'color' => 'invalid',
    ], $this->user->id))->toThrow(ValidationException::class);
});

test('can update category', function () {
    $category = Category::first();
    $updated = $this->service->updateCategory(
        CategoryUpdateDTO::from($category->id, $this->user->id, ['name' => 'Updated'])
    );

    expect($updated)->toBeTrue()
        ->and($category->fresh()->name)->toBe('Updated');
});

test('cannot delete category with transactions', function () {
    $category = Category::first();
    $category->transactions()->create([
        'type' => 'income',
        'amount' => 100,
        'date' => now(),
        'user_id' => $this->user->id,
    ]);

    expect(fn () => $this->service->deleteCategory($category->id, $this->user->id))
        ->toThrow(Exception::class, 'Cannot delete category with existing transactions');
});

test('can delete category without transactions', function () {
    $category = Category::first();
    $deleted = $this->service->deleteCategory($category->id, $this->user->id);

    expect($deleted)->toBeTrue()
        ->and(Category::count())->toBe(1);
});
