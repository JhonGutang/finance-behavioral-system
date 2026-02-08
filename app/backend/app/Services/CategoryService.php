<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {}

    /**
     * Get all categories.
     */
    public function getAllCategories(int $userId): Collection
    {
        return $this->categoryRepository->getAll($userId);
    }

    /**
     * Get categories by type.
     */
    public function getCategoriesByType(string $type, int $userId): Collection
    {
        return $this->categoryRepository->getByType($type, $userId);
    }

    /**
     * Get a category by ID.
     */
    public function getCategoryById(int $id, int $userId): ?Category
    {
        return $this->categoryRepository->getById($id, $userId);
    }

    /**
     * Create a new category.
     *
     * @throws ValidationException
     */
    public function createCategory(array $data, int $userId): Category
    {
        $data['user_id'] = $userId;
        $this->validateCategory($data);

        return $this->categoryRepository->create($data);
    }

    /**
     * Update a category.
     *
     * @throws ValidationException
     */
    public function updateCategory(int $id, int $userId, array $data): bool
    {
        $data['user_id'] = $userId;
        $this->validateCategory($data, $id);

        return $this->categoryRepository->update($id, $userId, $data);
    }

    /**
     * Delete a category.
     *
     * @throws \Exception
     */
    public function deleteCategory(int $id, int $userId): bool
    {
        // Check if category has transactions
        if ($this->categoryRepository->hasTransactions($id, $userId)) {
            throw new \Exception('Cannot delete category with existing transactions');
        }

        return $this->categoryRepository->delete($id, $userId);
    }

    /**
     * Validate category data.
     *
     * @throws ValidationException
     */
    private function validateCategory(array $data, ?int $id = null): void
    {
        $nameRule = $id ? ['sometimes', 'required'] : ['required'];
        $typeRule = $id ? ['sometimes', 'required'] : ['required'];

        $userId = $data['user_id'] ?? null;

        $rules = [
            'name' => array_merge($nameRule, [
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('categories', 'name')
                    ->where(function ($query) use ($userId) {
                        return $query->where('user_id', $userId)
                            ->orWhereNull('user_id');
                    })
                    ->ignore($id),
            ]),
            'type' => [...$typeRule, 'in:income,expense'],
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
