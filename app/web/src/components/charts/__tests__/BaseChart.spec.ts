import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import BaseChart from '../BaseChart.vue';
import type { AnalyticsData } from '@/types';

// Mock vue-chartjs
vi.mock('vue-chartjs', () => ({
  Line: {
    name: 'Line',
    props: ['data', 'options'],
    template: '<div class="mock-chart"></div>',
  },
}));

// Mock Chart.js
vi.mock('chart.js', () => ({
  Chart: class MockChart {
    static register() {
      // Mock implementation - do nothing
    }
  },
  CategoryScale: class {},
  LinearScale: class {},
  PointElement: class {},
  LineElement: class {},
  Title: class {},
  Tooltip: class {},
  Legend: class {},
  Filler: class {},
}));

const mockData: AnalyticsData = {
  labels: ['Jan 1', 'Jan 2', 'Jan 3'],
  income: [100, 200, 150],
  expenses: [50, 75, 60],
};

describe('BaseChart', () => {
  it('renders loading state when loading prop is true', () => {
    const wrapper = mount(BaseChart, {
      props: {
        data: null,
        isEmpty: false,
        emptyMessage: 'No data',
        title: 'Test Chart',
        loading: true,
      },
    });

    expect(wrapper.find('.animate-spin').exists()).toBe(true);
    expect(wrapper.find('.mock-chart').exists()).toBe(false);
  });

  it('renders empty state when isEmpty prop is true', () => {
    const wrapper = mount(BaseChart, {
      props: {
        data: null,
        isEmpty: true,
        emptyMessage: 'No transactions yet',
        title: 'Test Chart',
        loading: false,
      },
    });

    expect(wrapper.text()).toContain('No transactions yet');
    expect(wrapper.find('svg').exists()).toBe(true);
    expect(wrapper.find('.mock-chart').exists()).toBe(false);
  });

  it('renders chart when data is provided and not empty', () => {
    const wrapper = mount(BaseChart, {
      props: {
        data: mockData,
        isEmpty: false,
        emptyMessage: 'No data',
        title: 'Test Chart',
        loading: false,
      },
    });

    expect(wrapper.find('.mock-chart').exists()).toBe(true);
    expect(wrapper.find('.animate-spin').exists()).toBe(false);
  });

  it('displays custom empty message', () => {
    const customMessage = 'Custom empty state message';
    const wrapper = mount(BaseChart, {
      props: {
        data: null,
        isEmpty: true,
        emptyMessage: customMessage,
        title: 'Test Chart',
        loading: false,
      },
    });

    expect(wrapper.text()).toContain(customMessage);
  });

  it('computes chart data correctly', () => {
    const wrapper = mount(BaseChart, {
      props: {
        data: mockData,
        isEmpty: false,
        emptyMessage: 'No data',
        title: 'Test Chart',
        loading: false,
      },
    });

    const chartData = (wrapper.vm as any).chartData;

    expect(chartData.labels).toEqual(mockData.labels);
    expect(chartData.datasets).toHaveLength(2);
    expect(chartData.datasets[0].label).toBe('Income');
    expect(chartData.datasets[0].data).toEqual(mockData.income);
    expect(chartData.datasets[1].label).toBe('Expenses');
    expect(chartData.datasets[1].data).toEqual(mockData.expenses);
  });

  it('uses correct colors for income and expenses', () => {
    const wrapper = mount(BaseChart, {
      props: {
        data: mockData,
        isEmpty: false,
        emptyMessage: 'No data',
        title: 'Test Chart',
        loading: false,
      },
    });

    const chartData = (wrapper.vm as any).chartData;

    // Income should be green
    expect(chartData.datasets[0].borderColor).toBe('rgb(4, 120, 87)');
    // Expenses should be red
    expect(chartData.datasets[1].borderColor).toBe('rgb(190, 18, 60)');
  });

  it('returns empty datasets when data is null', () => {
    const wrapper = mount(BaseChart, {
      props: {
        data: null,
        isEmpty: true,
        emptyMessage: 'No data',
        title: 'Test Chart',
        loading: false,
      },
    });

    const chartData = (wrapper.vm as any).chartData;

    expect(chartData.labels).toEqual([]);
    expect(chartData.datasets).toEqual([]);
  });

  it('has correct chart options structure', () => {
    const wrapper = mount(BaseChart, {
      props: {
        data: mockData,
        isEmpty: false,
        emptyMessage: 'No data',
        title: 'Test Chart',
        loading: false,
      },
    });

    const chartOptions = (wrapper.vm as any).chartOptions;

    expect(chartOptions.responsive).toBe(true);
    expect(chartOptions.maintainAspectRatio).toBe(false);
    expect(chartOptions.plugins).toBeDefined();
    expect(chartOptions.plugins.legend).toBeDefined();
    expect(chartOptions.plugins.tooltip).toBeDefined();
    expect(chartOptions.scales).toBeDefined();
    expect(chartOptions.scales.x).toBeDefined();
    expect(chartOptions.scales.y).toBeDefined();
  });
});
