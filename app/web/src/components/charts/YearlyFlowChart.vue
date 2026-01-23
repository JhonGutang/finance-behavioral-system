<script setup lang="ts">
import { computed } from 'vue';
import { useYearlyAnalytics } from '@/composables/useAnalytics';
import BaseChart from './BaseChart.vue';

const { data, isPending } = useYearlyAnalytics();

const isEmpty = computed(() => {
  if (!data.value) return true;
  const hasData = data.value.income.some((v: number) => v > 0) || data.value.expenses.some((v: number) => v > 0);
  return !hasData;
});

const emptyMessage = 'No transaction history yet. Start adding transactions to see your yearly overview!';
</script>

<template>
  <BaseChart
    :data="data"
    :is-empty="isEmpty"
    :empty-message="emptyMessage"
    :loading="isPending"
    title="Yearly Flow"
  />
</template>
