import { useInfiniteQuery, useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { feedbackApi, ruleEngineApi } from '../services/api.service';
import type { FeedbackHistory, PaginatedResponse } from '../types';

export const feedbackKeys = {
  all: ['feedback'] as const,
  history: () => [...feedbackKeys.all, 'history'] as const,
  progress: () => [...feedbackKeys.all, 'progress'] as const,
};

/**
 * Infinite query for feedback history
 */
export function useInfiniteFeedback(perPage: number = 10) {
  return useInfiniteQuery({
    queryKey: feedbackKeys.history(),
    queryFn: ({ pageParam = 1 }) => feedbackApi.getAll({ page: pageParam as number, per_page: perPage }),
    getNextPageParam: (lastPage: PaginatedResponse<FeedbackHistory>) => {
      if (lastPage.meta.current_page < lastPage.meta.last_page) {
        return lastPage.meta.current_page + 1;
      }
      return undefined;
    },
    initialPageParam: 1,
  });
}

/**
 * Query for user progress history
 */
export function useFeedbackProgress() {
  return useQuery({
    queryKey: feedbackKeys.progress(),
    queryFn: () => feedbackApi.getProgress(),
  });
}

/**
 * Mutation for evaluating rules
 */
export function useEvaluateRules() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (date?: string) => ruleEngineApi.evaluate(date),
    onSuccess: () => {
      // Invalidate both history and progress
      queryClient.invalidateQueries({ queryKey: feedbackKeys.all });
    },
  });
}

/**
 * Mutation for acknowledging feedback
 */
export function useAcknowledgeFeedback() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => feedbackApi.acknowledge(id),
    onSuccess: () => {
      // Invalidate history to reflect acknowledgment
      queryClient.invalidateQueries({ queryKey: feedbackKeys.history() });
    },
  });
}
