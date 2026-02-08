<script setup lang="ts">
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';
import type { AnalyticsData } from '@/types';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

interface Props {
  data: AnalyticsData | null | undefined;
  isEmpty: boolean;
  emptyMessage: string;
  title: string;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

const chartData = computed(() => {
  if (!props.data) {
    return {
      labels: [],
      datasets: [],
    };
  }

  return {
    labels: props.data.labels,
    datasets: [
      {
        label: 'Income',
        data: props.data.income,
        borderColor: 'rgb(4, 120, 87)', // Emerald 700
        backgroundColor: 'rgba(4, 120, 87, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: 'rgb(4, 120, 87)',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
      },
      {
        label: 'Expenses',
        data: props.data.expenses,
        borderColor: 'rgb(190, 18, 60)', // Rose 700
        backgroundColor: 'rgba(190, 18, 60, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: 'rgb(190, 18, 60)',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
      },
    ],
  };
});

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top' as const,
      labels: {
        usePointStyle: true,
        padding: 15,
        font: {
          size: 12,
          weight: '500' as const,
        },
      },
    },
    tooltip: {
      mode: 'index' as const,
      intersect: false,
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      cornerRadius: 8,
      titleFont: {
        size: 13,
        weight: '600' as const,
      },
      bodyFont: {
        size: 12,
      },
      callbacks: {
        label: (context: any) => {
          const label = context.dataset.label || '';
          const value = context.parsed.y;
          return `${label}: ₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        },
      },
    },
  },
  scales: {
    x: {
      grid: {
        display: false,
      },
      ticks: {
        font: {
          size: 11,
        },
      },
    },
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.05)',
      },
      ticks: {
        font: {
          size: 11,
        },
        callback: (value: any) => {
          return '₱' + value.toLocaleString('en-PH');
        },
      },
    },
  },
  interaction: {
    mode: 'nearest' as const,
    axis: 'x' as const,
    intersect: false,
  },
}));
</script>

<template>
  <div class="w-full h-full">
    <div v-if="loading" class="flex items-center justify-center h-64">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>
    <div v-else-if="isEmpty" class="flex flex-col items-center justify-center h-64 text-center px-4">
      <svg
        class="w-16 h-16 text-slate-300 mb-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
        />
      </svg>
      <p class="text-slate-500 text-sm">{{ emptyMessage }}</p>
    </div>
    <div v-else class="h-64">
      <Line :data="chartData" :options="chartOptions as any" />
    </div>
  </div>
</template>
