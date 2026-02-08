<script setup lang="ts">
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  CategoryScale,
  Filler
} from 'chart.js';
import type { UserProgress } from '@/types';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  CategoryScale,
  Filler
);

interface Props {
  progressHistory: UserProgress[];
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

const chartData = computed(() => {
  // Sort history by date ascending for the chart
  const sortedHistory = [...props.progressHistory].sort((a, b) => 
    new Date(a.week_start).getTime() - new Date(b.week_start).getTime()
  );

  return {
    labels: sortedHistory.map(p => {
      const date = new Date(p.week_start);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }),
    datasets: [
      {
        label: 'Improvement Score',
        data: sortedHistory.map(p => p.improvement_score),
        borderColor: 'rgb(5, 150, 105)', // Emerald-600
        backgroundColor: 'rgba(16, 185, 129, 0.1)', // Emerald-500
        fill: true,
        tension: 0.4,
        pointRadius: 6,
        pointHoverRadius: 8,
        pointBackgroundColor: 'rgb(5, 150, 105)', // Emerald-600
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
      display: false,
    },
    tooltip: {
      mode: 'index' as const,
      intersect: false,
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: (context: any) => `Score: ${context.parsed.y}%`,
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
      min: 0,
      max: 100,
      grid: {
        color: 'rgba(0, 0, 0, 0.05)',
      },
      ticks: {
        stepSize: 20,
        font: {
          size: 11,
        },
        callback: (value: any) => `${value}%`,
      },
    },
  },
}));
</script>

<template>
  <div class="w-full h-full min-h-[300px]">
    <div v-if="loading" class="flex items-center justify-center h-full">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>
    <div v-else-if="progressHistory.length < 2" class="flex flex-col items-center justify-center h-full text-center px-4 bg-stone-50 rounded-2xl border border-dashed border-stone-300">
      <div class="bg-white p-3 rounded-full mb-3 shadow-sm">
        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
        </svg>
      </div>
      <p class="text-slate-500 text-sm max-w-[200px]">Need at least two weeks of data to show your progress trend.</p>
    </div>
    <div v-else class="h-full">
      <Line :data="chartData" :options="chartOptions as any" />
    </div>
  </div>
</template>
