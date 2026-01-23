import { describe, it, expect, beforeEach, vi } from 'vitest';
import { ref } from 'vue';
import { setActivePinia, createPinia } from 'pinia';
import { VueQueryPlugin, QueryClient } from '@tanstack/vue-query';
import { mount } from '@vue/test-utils';
import { useDailyAnalytics, useMonthlyAnalytics, useYearlyAnalytics } from '../useAnalytics';
import { transactionApi } from '@/services/api.service';
import type { AnalyticsData } from '@/types';

// Mock the API service
vi.mock('@/services/api.service', () => ({
  transactionApi: {
    getDailyAnalytics: vi.fn(),
    getMonthlyAnalytics: vi.fn(),
    getYearlyAnalytics: vi.fn(),
  },
}));

const mockDailyData: AnalyticsData = {
  labels: ['Jan 1', 'Jan 2', 'Jan 3'],
  income: [100, 200, 150],
  expenses: [50, 75, 60],
};

const mockMonthlyData: AnalyticsData = {
  labels: ['Jan 2026', 'Feb 2026', 'Mar 2026'],
  income: [5000, 6000, 5500],
  expenses: [3000, 3500, 3200],
};

const mockYearlyData: AnalyticsData = {
  labels: ['2024', '2025', '2026'],
  income: [60000, 65000, 70000],
  expenses: [40000, 42000, 45000],
};

describe('useAnalytics composables', () => {
  let queryClient: QueryClient;

  beforeEach(() => {
    setActivePinia(createPinia());
    queryClient = new QueryClient({
      defaultOptions: {
        queries: {
          retry: false,
        },
      },
    });
    vi.clearAllMocks();
  });

  describe('useDailyAnalytics', () => {
    it('fetches daily analytics data', async () => {
      vi.mocked(transactionApi.getDailyAnalytics).mockResolvedValue(mockDailyData);

      const wrapper = mount(
        {
          setup() {
            return useDailyAnalytics();
          },
          template: '<div></div>',
        },
        {
          global: {
            plugins: [[VueQueryPlugin, { queryClient }]],
          },
        }
      );

      await vi.waitFor(() => {
        expect(wrapper.vm.data).toBeDefined();
      });

      expect(transactionApi.getDailyAnalytics).toHaveBeenCalledWith(30);
      expect(wrapper.vm.data).toEqual(mockDailyData);
    });

    it('uses custom days parameter', async () => {
      vi.mocked(transactionApi.getDailyAnalytics).mockResolvedValue(mockDailyData);

      mount(
        {
          setup() {
            const days = ref(7);
            return { ...useDailyAnalytics(days), days };
          },
          template: '<div></div>',
        },
        {
          global: {
            plugins: [[VueQueryPlugin, { queryClient }]],
          },
        }
      );

      await vi.waitFor(() => {
        expect(transactionApi.getDailyAnalytics).toHaveBeenCalledWith(7);
      });
    });

    it('handles loading state', () => {
      vi.mocked(transactionApi.getDailyAnalytics).mockImplementation(
        () => new Promise(() => {}) // Never resolves
      );

      const wrapper = mount(
        {
          setup() {
            return useDailyAnalytics();
          },
          template: '<div></div>',
        },
        {
          global: {
            plugins: [[VueQueryPlugin, { queryClient }]],
          },
        }
      );

      expect(wrapper.vm.isPending).toBe(true);
    });

    it('handles error state', async () => {
      const error = new Error('Failed to fetch');
      vi.mocked(transactionApi.getDailyAnalytics).mockRejectedValue(error);

      const wrapper = mount(
        {
          setup() {
            return useDailyAnalytics();
          },
          template: '<div></div>',
        },
        {
          global: {
            plugins: [[VueQueryPlugin, { queryClient }]],
          },
        }
      );

      await vi.waitFor(() => {
        expect(wrapper.vm.isError).toBe(true);
      });
    });
  });

  describe('useMonthlyAnalytics', () => {
    it('fetches monthly analytics data', async () => {
      vi.mocked(transactionApi.getMonthlyAnalytics).mockResolvedValue(mockMonthlyData);

      const wrapper = mount(
        {
          setup() {
            return useMonthlyAnalytics();
          },
          template: '<div></div>',
        },
        {
          global: {
            plugins: [[VueQueryPlugin, { queryClient }]],
          },
        }
      );

      await vi.waitFor(() => {
        expect(wrapper.vm.data).toBeDefined();
      });

      expect(transactionApi.getMonthlyAnalytics).toHaveBeenCalledWith(12);
      expect(wrapper.vm.data).toEqual(mockMonthlyData);
    });

    it('uses custom months parameter', async () => {
      vi.mocked(transactionApi.getMonthlyAnalytics).mockResolvedValue(mockMonthlyData);

      mount(
        {
          setup() {
            const months = ref(6);
            return { ...useMonthlyAnalytics(months), months };
          },
          template: '<div></div>',
        },
        {
          global: {
            plugins: [[VueQueryPlugin, { queryClient }]],
          },
        }
      );

      await vi.waitFor(() => {
        expect(transactionApi.getMonthlyAnalytics).toHaveBeenCalledWith(6);
      });
    });
  });

  describe('useYearlyAnalytics', () => {
    it('fetches yearly analytics data', async () => {
      vi.mocked(transactionApi.getYearlyAnalytics).mockResolvedValue(mockYearlyData);

      const wrapper = mount(
        {
          setup() {
            return useYearlyAnalytics();
          },
          template: '<div></div>',
        },
        {
          global: {
            plugins: [[VueQueryPlugin, { queryClient }]],
          },
        }
      );

      await vi.waitFor(() => {
        expect(wrapper.vm.data).toBeDefined();
      });

      expect(transactionApi.getYearlyAnalytics).toHaveBeenCalled();
      expect(wrapper.vm.data).toEqual(mockYearlyData);
    });
  });

  describe('caching behavior', () => {
    it('caches daily analytics data', async () => {
      vi.mocked(transactionApi.getDailyAnalytics).mockResolvedValue(mockDailyData);

      // First call
      const wrapper1 = mount(
        {
          setup() {
            return useDailyAnalytics();
          },
          template: '<div></div>',
        },
        {
          global: {
            plugins: [[VueQueryPlugin, { queryClient }]],
          },
        }
      );

      await vi.waitFor(() => {
        expect(wrapper1.vm.data).toBeDefined();
      });

      // Second call should use cache
      const wrapper2 = mount(
        {
          setup() {
            return useDailyAnalytics();
          },
          template: '<div></div>',
        },
        {
          global: {
            plugins: [[VueQueryPlugin, { queryClient }]],
          },
        }
      );

      // API should only be called once due to caching
      expect(transactionApi.getDailyAnalytics).toHaveBeenCalledTimes(1);
      expect(wrapper2.vm.data).toEqual(mockDailyData);
    });
  });
});
