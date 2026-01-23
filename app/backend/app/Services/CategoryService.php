<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use App\Models\Category;
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
    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    /**
     * Get categories by type.
     */
    public function getCategoriesByType(string $type): Collection
    {
        return $this->categoryRepository->getByType($type);
    }

    /**
     * Get a category by ID.
     */
    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->getById($id);
    }

    /**
     * Create a new category.
     *
     * @throws ValidationException
     */
    public function createCategory(array $data): Category
    {
        $this->validateCategory($data);

        return $this->categoryRepository->create($data);
    }

    /**
     * Update a category.
     *
     * @throws ValidationException
     */
    public function updateCategory(int $id, array $data): bool
    {
        $this->validateCategory($data, $id);

        return $this->categoryRepository->update($id, $data);
    }

    /**
     * Delete a category.
     *
     * @throws \Exception
     */
    public function deleteCategory(int $id): bool
    {
        // Check if category has transactions
        if ($this->categoryRepository->hasTransactions($id)) {
            throw new \Exception('Cannot delete category with existing transactions');
        }

        return $this->categoryRepository->delete($id);
    }

    /**
     * Validate category data.
     *
     * @throws ValidationException
     */
    private function validateCategory(array $data, ?int $id = null): void
    {
        $nameRule = $id ? 'sometimes|required' : 'required';
        $typeRule = $id ? 'sometimes|required' : 'required';
        
        $rules = [
            'name' => $nameRule . '|string|max:100|unique:categories,name' . ($id ? ",$id" : ''),
            'type' => $typeRule . '|in:income,expense',
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
