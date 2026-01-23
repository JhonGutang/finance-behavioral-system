<script setup lang="ts">
import { computed } from 'vue';
import { useDailyAnalytics } from '@/composables/useAnalytics';
import BaseChart from './BaseChart.vue';

const { data, isPending } = useDailyAnalytics();

const isEmpty = computed(() => {
  if (!data.value) return true;
  const hasData = data.value.income.some((v: number) => v > 0) || data.value.expenses.some((v: number) => v > 0);
  return !hasData;
});

const emptyMessage = 'No transactions in the last 30 days. Start tracking to see your daily flow!';
</script>

<template>
  <BaseChart
    :data="data"
    :is-empty="isEmpty"
    :empty-message="emptyMessage"
    :loading="isPending"
    title="Daily Flow"
  />
</template>
