<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(
        private TransactionRepository $transactionRepository
    ) {}

    /**
     * Get all transactions.
     */
    public function getAllTransactions(): Collection
    {
        return $this->transactionRepository->getAll();
    }

    /**
     * Get a transaction by ID.
     */
    public function getTransactionById(int $id): ?Transaction
    {
        return $this->transactionRepository->getById($id);
    }

    /**
     * Get transactions by date range.
     */
    public function getTransactionsByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->transactionRepository->getByDateRange($startDate, $endDate);
    }

    /**
     * Get transactions by category.
     */
    public function getTransactionsByCategory(int $categoryId): Collection
    {
        return $this->transactionRepository->getByCategory($categoryId);
    }

    /**
     * Get transactions by type.
     */
    public function getTransactionsByType(string $type): Collection
    {
        return $this->transactionRepository->getByType($type);
    }

    /**
     * Create a new transaction.
     *
     * @throws ValidationException
     */
    public function createTransaction(array $data): Transaction
    {
        $this->validateTransaction($data);

        return $this->transactionRepository->create($data);
    }

    /**
     * Update a transaction.
     *
     * @throws ValidationException
     */
    public function updateTransaction(int $id, array $data): bool
    {
        $this->validateTransaction($data, true);

        return $this->transactionRepository->update($id, $data);
    }

    /**
     * Delete a transaction.
     */
    public function deleteTransaction(int $id): bool
    {
        return $this->transactionRepository->delete($id);
    }

    /**
     * Get financial summary.
     */
    public function getSummary(): array
    {
        return $this->transactionRepository->getSummary();
    }

    /**
     * Get expense breakdown by category.
     */
    public function getExpensesByCategory(): Collection
    {
        return $this->transactionRepository->getExpensesByCategory();
    }

    /**
     * Validate transaction data.
     *
     * @throws ValidationException
     */
    private function validateTransaction(array $data, bool $isUpdate = false): void
    {
        $requiredRule = $isUpdate ? 'sometimes|required' : 'required';
        
        $rules = [
            'type' => $requiredRule . '|in:income,expense',
            'amount' => $requiredRule . '|numeric|min:0.01|max:9999999.99',
            'date' => $requiredRule . '|date|before_or_equal:today',
            'category_id' => $requiredRule . '|exists:categories,id',
            'description' => 'nullable|string|max:1000',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
