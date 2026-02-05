<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Services\AnalyticsService;
use App\Services\CsvImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private AnalyticsService $analyticsService,
        private CsvImportService $csvImportService
    ) {}

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $type = $request->query('type');
        $categoryId = $request->query('category_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($startDate && $endDate) {
            $transactions = $this->transactionService->getTransactionsByDateRange($startDate, $endDate, $userId);
        } elseif ($categoryId) {
            $transactions = $this->transactionService->getTransactionsByCategory($categoryId, $userId);
        } elseif ($type) {
            $transactions = $this->transactionService->getTransactionsByType($type, $userId);
        } else {
            $transactions = $this->transactionService->getAllTransactions($userId);
        }

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $transaction = $this->transactionService->createTransaction($request->all(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction->load('category'),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $transaction = $this->transactionService->getTransactionById($id, $request->user()->id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $updated = $this->transactionService->updateTransaction($id, $userId, $request->all());

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'data' => $this->transactionService->getTransactionById($id, $userId),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $deleted = $this->transactionService->deleteTransaction($id, $userId);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully',
        ]);
    }

    /**
     * Get financial summary.
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $summary = $this->transactionService->getSummary($userId);
        $expensesByCategory = $this->transactionService->getExpensesByCategory($userId);

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'expenses_by_category' => $expensesByCategory,
            ],
        ]);
    }

    /**
     * Get daily analytics data.
     */
    public function dailyAnalytics(Request $request): JsonResponse
    {
        $days = $request->query('days', 30);
        $data = $this->analyticsService->getDailyFlow((int) $days, $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get monthly analytics data.
     */
    public function monthlyAnalytics(Request $request): JsonResponse
    {
        $months = $request->query('months', 12);
        $data = $this->analyticsService->getMonthlyFlow((int) $months, $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get yearly analytics data.
     */
    public function yearlyAnalytics(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getYearlyFlow($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Preview CSV import data.
     */
    public function importPreview(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $preview = $this->csvImportService->getPreview($request->file('file'), $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $preview,
        ]);
    }

    /**
     * Confirm CSV import.
     */
    public function importConfirm(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.skip' => 'boolean',
            'items.*.data' => 'required|array',
        ]);

        $result = $this->csvImportService->confirmImport($request->input('items'), $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => "Imported {$result['imported']} transactions, skipped {$result['skipped']}.",
            'data' => $result,
        ]);
    }
}
