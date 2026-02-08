import { defineStore } from 'pinia';
import { computed } from 'vue';
import {
  useInfiniteFeedback,
  useFeedbackProgress,
  useEvaluateRules,
  useAcknowledgeFeedback,
} from '../composables/useFeedback';
import type { EvaluationResponse } from '../types';

/**
 * Feedback Store
 * 
 * Manages behavioral insights and progress tracking using Vue Query for caching.
 */
export const useFeedbackStore = defineStore('feedback', () => {
  // Use Vue Query composables
  const historyQuery = useInfiniteFeedback();
  const progressQuery = useFeedbackProgress();
  const evaluateMutation = useEvaluateRules();
  const acknowledgeMutation = useAcknowledgeFeedback();

  // Computed state for backward compatibility
  const feedbackHistory = computed(() => {
    if (!historyQuery.data.value) return [];
    return historyQuery.data.value.pages.flatMap((page: any) => page.data);
  });

  const progressHistory = computed(() => progressQuery.data.value || []);
  
  const loading = computed(() => historyQuery.isPending.value || progressQuery.isPending.value);
  const isFetchingMore = computed(() => historyQuery.isFetchingNextPage.value);
  const hasMore = computed(() => historyQuery.hasNextPage.value);
  const error = computed(() => 
    (historyQuery.error.value as any)?.message || 
    (progressQuery.error.value as any)?.message || 
    null
  );

  const latestFeedback = computed(() => feedbackHistory.value.slice(0, 3));
  const currentProgress = computed(() => progressHistory.value[0] || null);

  // Helper getters
  const currentWeekMonday = computed(() => {
    const now = new Date();
    const day = now.getDay();
    const diff = now.getDate() - day + (day === 0 ? -6 : 1);
    
    const monday = new Date(now);
    monday.setDate(diff);
    
    const year = monday.getFullYear();
    const month = String(monday.getMonth() + 1).padStart(2, '0');
    const date = String(monday.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${date}`;
  });

  const hasEvaluatedThisWeek = computed(() => {
    if (progressHistory.value.length === 0) return false;
    return progressHistory.value.some((p: any) => p.week_start === currentWeekMonday.value);
  });

  const canEvaluate = computed(() => {
    if (progressHistory.value.length === 0) return true;
    return !hasEvaluatedThisWeek.value;
  });

  // Actions
  async function fetchFeedback(reset = true) {
    if (reset) {
      await historyQuery.refetch();
    }
  }

  async function fetchMoreFeedback() {
    if (hasMore.value && !isFetchingMore.value) {
      await historyQuery.fetchNextPage();
    }
  }

  async function fetchProgress() {
    await progressQuery.refetch();
  }

  async function evaluateRules(date?: string): Promise<EvaluationResponse> {
    return new Promise<EvaluationResponse>((resolve, reject) => {
      evaluateMutation.mutate(date, {
        onSuccess: (data: any) => resolve(data),
        onError: (err: any) => reject(err),
      });
    });
  }

  async function acknowledgeFeedback(id: number) {
    return new Promise((resolve, reject) => {
      acknowledgeMutation.mutate(id, {
        onSuccess: (data: any) => resolve(data),
        onError: (err: any) => reject(err),
      });
    });
  }

  const isFeedbackFetched = computed(() => !!historyQuery.data.value);
  const isProgressFetched = computed(() => !!progressQuery.data.value);

  return {
    feedbackHistory,
    progressHistory,
    loading,
    isFetchingMore,
    hasMore,
    error,
    latestFeedback,
    currentProgress,
    currentWeekMonday,
    hasEvaluatedThisWeek,
    canEvaluate,
    isFeedbackFetched,
    isProgressFetched,
    fetchFeedback,
    fetchMoreFeedback,
    fetchProgress,
    evaluateRules,
    acknowledgeFeedback,
  };
});
