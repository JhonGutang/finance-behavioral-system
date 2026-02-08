import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { transactionApi } from '../services/api.service';
import type { TransactionFormData } from '../types';
import type { MaybeRef } from 'vue';
import { unref } from 'vue';

/**
 * Query Keys for Transactions
 * Used for caching, refetching, and invalidation
 */
export const transactionKeys = {
  all: ['transactions'] as const,
  lists: () => [...transactionKeys.all, 'list'] as const,
  list: (params?: {
    type?: 'income' | 'expense';
    category_id?: number | string | null;
    start_date?: string;
    end_date?: string;
    min_amount?: number;
    max_amount?: number;
    page?: number;
    per_page?: number;
  }) => [...transactionKeys.lists(), params] as const,
  details: () => [...transactionKeys.all, 'detail'] as const,
  detail: (id: number) => [...transactionKeys.details(), id] as const,
  summary: () => [...transactionKeys.all, 'summary'] as const,
};

/**
 * Fetch all transactions with optional filters
 * 
 * @param params - Optional filters (type, category_id, start_date, end_date, amounts, pagination)
 * @returns Query result with transactions data, loading state, and error
 */
export function useTransactions(
  params?: MaybeRef<{
    type?: 'income' | 'expense';
    category_id?: number | string | null;
    start_date?: string;
    end_date?: string;
    min_amount?: number;
    max_amount?: number;
    page?: number;
    per_page?: number;
  } | undefined>
) {
  return useQuery({
    queryKey: transactionKeys.list(unref(params)),
    queryFn: () => transactionApi.getAll(unref(params)),
  });
}

/**
 * Fetch a single transaction by ID
 * 
 * @param id - Transaction ID
 * @returns Query result with transaction data, loading state, and error
 */
export function useTransactionById(id: MaybeRef<number>) {
  return useQuery({
    queryKey: transactionKeys.detail(unref(id)),
    queryFn: () => transactionApi.getById(unref(id)),
    enabled: !!unref(id), // Only fetch if ID is provided
  });
}

/**
 * Fetch transaction summary and expenses by category
 * 
 * @returns Query result with summary data, loading state, and error
 */
export function useTransactionSummary() {
  return useQuery({
    queryKey: transactionKeys.summary(),
    queryFn: () => transactionApi.getSummary(),
  });
}

/**
 * Create a new transaction
 * 
 * @returns Mutation object with mutate function and state
 */
export function useCreateTransaction() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: TransactionFormData) => transactionApi.create(data),
    onSuccess: () => {
      // Invalidate both transactions and summary queries to trigger refetch
      queryClient.invalidateQueries({ queryKey: transactionKeys.all });
    },
  });
}

/**
 * Update an existing transaction
 * 
 * @returns Mutation object with mutate function and state
 */
export function useUpdateTransaction() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<TransactionFormData> }) =>
      transactionApi.update(id, data),
    onSuccess: (_, variables) => {
      // Invalidate all transaction queries including summary
      queryClient.invalidateQueries({ queryKey: transactionKeys.all });
      // Also invalidate the specific transaction detail
      queryClient.invalidateQueries({ queryKey: transactionKeys.detail(variables.id) });
    },
  });
}

/**
 * Delete a transaction
 * 
 * @returns Mutation object with mutate function and state
 */
export function useDeleteTransaction() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => transactionApi.delete(id),
    onSuccess: () => {
      // Invalidate all transaction queries including summary to trigger refetch
      queryClient.invalidateQueries({ queryKey: transactionKeys.all });
    },
  });
}
