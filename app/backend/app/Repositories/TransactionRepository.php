<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TransactionRepository
{
    /**
     * Get all transactions with category relationship.
     */
    public function getAll(): Collection
    {
        return Transaction::with('category')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get a transaction by ID.
     */
    public function getById(int $id): ?Transaction
    {
        return Transaction::with('category')->find($id);
    }

    /**
     * Get transactions by date range.
     */
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return Transaction::with('category')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get transactions by category.
     */
    public function getByCategory(int $categoryId): Collection
    {
        return Transaction::with('category')
            ->where('category_id', $categoryId)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get transactions by type (income or expense).
     */
    public function getByType(string $type): Collection
    {
        return Transaction::with('category')
            ->where('type', $type)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Create a new transaction.
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Update a transaction.
     */
    public function update(int $id, array $data): bool
    {
        $transaction = $this->getById($id);
        
        if (!$transaction) {
            return false;
        }

        return $transaction->update($data);
    }

    /**
     * Delete a transaction.
     */
    public function delete(int $id): bool
    {
        $transaction = $this->getById($id);
        
        if (!$transaction) {
            return false;
        }

        return $transaction->delete();
    }

    /**
     * Get summary statistics.
     */
    public function getSummary(): array
    {
        $income = Transaction::where('type', 'income')->sum('amount');
        $expenses = Transaction::where('type', 'expense')->sum('amount');

        return [
            'total_income' => (float) $income,
            'total_expenses' => (float) $expenses,
            'net_balance' => (float) ($income - $expenses),
            'transaction_count' => Transaction::count(),
        ];
    }

    /**
     * Get expense breakdown by category.
     */
    public function getExpensesByCategory(): Collection
    {
        return Transaction::select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->get();
    }
}
