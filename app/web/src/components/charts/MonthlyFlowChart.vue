<script setup lang="ts">
import { computed } from 'vue';
import { useMonthlyAnalytics } from '@/composables/useAnalytics';
import BaseChart from './BaseChart.vue';

const { data, isPending } = useMonthlyAnalytics();

const isEmpty = computed(() => {
  if (!data.value) return true;
  const hasData = data.value.income.some((v: number) => v > 0) || data.value.expenses.some((v: number) => v > 0);
  return !hasData;
});

const emptyMessage = 'No transactions in the last 12 months. Add transactions to visualize your monthly trends!';
</script>

<template>
  <BaseChart
    :data="data"
    :is-empty="isEmpty"
    :empty-message="emptyMessage"
    :loading="isPending"
    title="Monthly Flow"
  />
</template>
