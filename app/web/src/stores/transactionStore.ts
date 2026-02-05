import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import {
  useTransactions,
  useTransactionSummary,
  useCreateTransaction,
  useUpdateTransaction,
  useDeleteTransaction,
} from '../composables/useTransactions';
import type { TransactionFormData, CsvImportItem, Transaction } from '../types';
import { transactionApi } from '../services/api.service';

/**
 * Transaction Store
 * 
 * Manages transaction state using TanStack Vue Query for server state.
 * Provides helper methods for component usage with backward compatibility.
 */
export const useTransactionStore = defineStore('transaction', () => {
  // Query parameters for filtering
  const queryParams = ref<{
    type?: 'income' | 'expense';
    category_id?: number;
    start_date?: string;
    end_date?: string;
  }>();

  // Use Vue Query composables for server state
  const transactionsQuery = useTransactions(queryParams);
  const summaryQuery = useTransactionSummary();
  const createMutation = useCreateTransaction();
  const updateMutation = useUpdateTransaction();
  const deleteMutation = useDeleteTransaction();

  // Backward compatible getters
  const transactions = computed(() => transactionsQuery.data.value || []);
  const summary = computed(() => summaryQuery.data.value?.summary || {
    total_income: 0,
    total_expenses: 0,
    net_balance: 0,
    transaction_count: 0,
  });
  const expensesByCategory = computed(() => summaryQuery.data.value?.expenses_by_category || []);
  const loading = computed(() => transactionsQuery.isPending.value || summaryQuery.isPending.value);
  const error = computed(() => 
    transactionsQuery.error.value?.message || 
    summaryQuery.error.value?.message || 
    null
  );

  /**
   * Fetch transactions with optional filters
   * Maintained for backward compatibility
   */
  async function fetchTransactions(params?: {
    type?: 'income' | 'expense';
    category_id?: number;
    start_date?: string;
    end_date?: string;
  }) {
    queryParams.value = params;
    await transactionsQuery.refetch();
  }

  /**
   * Fetch summary data
   * Maintained for backward compatibility
   */
  async function fetchSummary() {
    await summaryQuery.refetch();
  }

  /**
   * Create a new transaction
   */
  async function createTransaction(data: TransactionFormData) {
    return new Promise((resolve, reject) => {
      createMutation.mutate(data, {
        onSuccess: (newTransaction: Transaction) => resolve(newTransaction),
        onError: (error: any) => reject(error),
      });
    });
  }

  /**
   * Update an existing transaction
   */
  async function updateTransaction(id: number, data: Partial<TransactionFormData>) {
    return new Promise((resolve, reject) => {
      updateMutation.mutate(
        { id, data },
        {
          onSuccess: (updated: Transaction) => resolve(updated),
          onError: (error: any) => reject(error),
        }
      );
    });
  }

  /**
   * Delete a transaction
   */
  async function deleteTransaction(id: number) {
    return new Promise<void>((resolve, reject) => {
      deleteMutation.mutate(id, {
        onSuccess: () => resolve(),
        onError: (error) => reject(error),
      });
    });
  }

  /**
   * Preview CSV import
   */
  async function importPreview(file: File): Promise<CsvImportItem[]> {
    return await transactionApi.importPreview(file);
  }

  /**
   * Confirm CSV import
   */
  async function importConfirm(items: { data: any; skip?: boolean }[]) {
    const result = await transactionApi.importConfirm(items);
    await fetchTransactions();
    await fetchSummary();
    return result;
  }

  return {
    transactions,
    summary,
    expensesByCategory,
    loading,
    error,
    fetchTransactions,
    fetchSummary,
    createTransaction,
    updateTransaction,
    deleteTransaction,
    importPreview,
    importConfirm,
  };
});

