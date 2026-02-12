<?php

namespace App\Services;

use App\DTOs\DateRangeQueryDTO;
use App\DTOs\TransactionFilterDTO;
use App\DTOs\TransactionUpdateDTO;
use App\Models\Category;
use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    protected TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getAllTransactions(int $userId): Collection
    {
        return $this->transactionRepository->getAll($userId);
    }

    /**
     * Get filtered and paginated transactions.
     */
    public function getFilteredPaginated(TransactionFilterDTO $dto)
    {
        return $this->transactionRepository->getFilteredPaginated($dto);
    }

    /**
     * Get a transaction by ID.
     */
    public function getTransactionById(int $id, int $userId): ?Transaction
    {
        return $this->transactionRepository->getById($id, $userId);
    }

    /**
     * Get transactions by date range.
     */
    public function getTransactionsByDateRange(DateRangeQueryDTO $dto): Collection
    {
        return $this->transactionRepository->getByDateRange($dto);
    }

    /**
     * Get transactions by category.
     */
    public function getTransactionsByCategory(int $categoryId, int $userId): Collection
    {
        return $this->transactionRepository->getByCategory($categoryId, $userId);
    }

    /**
     * Get transactions by type.
     */
    public function getTransactionsByType(string $type, int $userId): Collection
    {
        return $this->transactionRepository->getByType($type, $userId);
    }

    /**
     * Create a new transaction.
     *
     * @throws ValidationException
     */
    public function createTransaction(array $data, int $userId): Transaction
    {
        $data['user_id'] = $userId;
        $this->validateTransaction($data);

        return $this->transactionRepository->create($data);
    }

    /**
     * Update a transaction.
     *
     * @throws ValidationException
     */
    public function updateTransaction(TransactionUpdateDTO $dto): bool
    {
        $data = $dto->data;
        $data['user_id'] = $dto->userId;
        $this->validateTransaction($data, true);

        return $this->transactionRepository->update($dto);
    }

    /**
     * Delete a transaction.
     */
    public function deleteTransaction(int $id, int $userId): bool
    {
        return $this->transactionRepository->delete($id, $userId);
    }

    /**
     * Get financial summary.
     */
    public function getSummary(int $userId): array
    {
        return $this->transactionRepository->getSummary($userId);
    }

    /**
     * Get expense breakdown by category.
     */
    public function getExpensesByCategory(int $userId): Collection
    {
        return $this->transactionRepository->getExpensesByCategory($userId);
    }

    /**
     * Validate transaction data.
     *
     * @throws ValidationException
     */
    private function validateTransaction(array $data, bool $isUpdate = false): void
    {
        $requiredRule = $isUpdate ? ['sometimes', 'required'] : ['required'];

        $rules = [
            'type' => array_merge($requiredRule, ['in:income,expense']),
            'amount' => array_merge($requiredRule, ['numeric', 'min:0.01', 'max:9999999.99']),
            'date' => array_merge($requiredRule, ['date', 'before_or_equal:today']),
            'category_id' => array_merge($requiredRule, [
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($data) {
                    $userId = $data['user_id'] ?? null;
                    $category = Category::find($value);
                    if ($category && $category->user_id !== null && $category->user_id !== $userId) {
                        $fail('The selected category is invalid.');
                    }
                },
            ]),
            'description' => 'nullable|string|max:1000',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
