import { defineStore } from 'pinia';
import { ref, computed, watch } from 'vue';
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
  // Query parameters for filtering and pagination
  const queryParams = ref<{
    type?: 'income' | 'expense';
    category_id?: number | string | null;
    start_date?: string;
    end_date?: string;
    min_amount?: number;
    max_amount?: number;
    page?: number;
    per_page?: number;
  }>({
    page: 1,
    per_page: 10,
  });

  const paginationMeta = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
  });

  // Use Vue Query composables for server state
  const transactionsQuery = useTransactions(queryParams);
  const summaryQuery = useTransactionSummary();
  const createMutation = useCreateTransaction();
  const updateMutation = useUpdateTransaction();
  const deleteMutation = useDeleteTransaction();

  // Watch for query results to update pagination meta
  watch(() => transactionsQuery.data.value, (newData) => {
    if (newData && 'meta' in newData) {
      paginationMeta.value = newData.meta;
    }
  });

  // Backward compatible getters
  const transactions = computed(() => {
    const data = transactionsQuery.data.value;
    if (!data) return [];
    return 'data' in data ? data.data : (data as unknown as Transaction[]);
  });

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
   */
  async function fetchTransactions(params?: {
    type?: 'income' | 'expense';
    category_id?: number | string | null;
    start_date?: string;
    end_date?: string;
    min_amount?: number;
    max_amount?: number;
    page?: number;
    per_page?: number;
  }) {
    queryParams.value = {
      ...queryParams.value,
      ...params,
    };
    await transactionsQuery.refetch();
  }

  /**
   * Go to a specific page
   */
  async function goToPage(page: number) {
    queryParams.value = { ...queryParams.value, page };
    await transactionsQuery.refetch();
  }

  /**
   * Fetch summary data
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

  const isSummaryFetched = computed(() => !!summaryQuery.data.value);
  const isTransactionsFetched = computed(() => !!transactionsQuery.data.value);

  return {
    transactions,
    summary,
    expensesByCategory,
    loading,
    error,
    paginationMeta,
    queryParams,
    isSummaryFetched,
    isTransactionsFetched,
    fetchTransactions,
    goToPage,
    fetchSummary,
    createTransaction,
    updateTransaction,
    deleteTransaction,
    importPreview,
    importConfirm,
  };
});

