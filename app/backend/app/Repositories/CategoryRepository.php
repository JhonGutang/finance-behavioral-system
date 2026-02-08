<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    /**
     * Get all categories.
     */
    public function getAll(int $userId): Collection
    {
        return Category::where('user_id', $userId)
            ->orWhereNull('user_id') // For default categories
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get a category by ID.
     */
    public function getById(int $id, int $userId): ?Category
    {
        return Category::where('id', $id)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })
            ->first();
    }

    /**
     * Get categories by type (income or expense).
     */
    public function getByType(string $type, int $userId): Collection
    {
        return Category::where('type', $type)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new category.
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update a category.
     */
    public function update(int $id, int $userId, array $data): bool
    {
        $category = $this->getById($id, $userId);

        if (! $category || $category->user_id !== $userId) {
            return false; // Cannot update default or others' categories
        }

        return $category->update($data);
    }

    /**
     * Delete a category.
     */
    public function delete(int $id, int $userId): bool
    {
        $category = $this->getById($id, $userId);

        if (! $category || $category->user_id !== $userId) {
            return false;
        }

        // Check if category has transactions
        if ($category->transactions()->where('user_id', $userId)->count() > 0) {
            return false;
        }

        return $category->delete();
    }

    /**
     * Check if a category has transactions.
     */
    public function hasTransactions(int $id, int $userId): bool
    {
        $category = $this->getById($id, $userId);

        if (! $category) {
            return false;
        }

        return $category->transactions()->where('user_id', $userId)->count() > 0;
    }
}
