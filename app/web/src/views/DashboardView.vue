<template>
  <InitializationLoader v-if="isLoading" />
  <div v-else class="min-h-screen animate-in fade-in duration-1000 slide-in-from-bottom-2 font-inter pb-12">

    <!-- Background Texture & Atmosphere -->
    <div class="fixed inset-0 pointer-events-none opacity-40 mix-blend-multiply -z-10">
      <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-30"></div>
    </div>
    <div class="fixed inset-0 bg-[#FDFCF8] -z-20"></div>

    <!-- Dashboard Content -->
    <div class="space-y-8 max-w-7xl mx-auto">

      <!-- Header & Welcome -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <h1 class="text-4xl font-serif font-medium text-emerald-950 tracking-tight">Your Financial Garden</h1>
          <p class="text-emerald-800 mt-2 font-light text-lg">Detailed overview of your disciplines and growth.</p>
        </div>
        <div class="flex items-center gap-3">
          <RouterLink to="/transactions"
            class="inline-flex items-center justify-center rounded-full bg-emerald-900 px-8 h-11 text-[11px] font-bold text-white shadow-lg shadow-emerald-900/10 hover:bg-emerald-800 transition-all hover:-translate-y-0.5 uppercase tracking-widest">
            Add Transaction
          </RouterLink>
        </div>
      </div>

      <!-- Main Bento Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Row 1: Three Equal Columns -->

        <!-- 1. Net Balance (Compact & Dense) -->
        <Card
          class="relative lg:col-span-4 bg-emerald-950 border-0 shadow-lg shadow-emerald-900/20 text-[#FDFCF8] overflow-hidden group flex flex-col justify-between h-full min-h-[280px]">
          <!-- Deco -->
          <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
            <Layers class="w-32 h-32 -mr-10 -mt-10" />
          </div>

          <CardContent class="p-6 relative z-10 flex flex-col h-full justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <p class="text-emerald-200/80 uppercase tracking-[0.2em] text-[10px] font-bold font-inter">Net Balance
                </p>
                <div
                  :class="['flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full', netBalanceTrendColor]">
                  <TrendingUp v-if="transactionStore.summary.net_balance_trend >= 0" class="w-3 h-3 mr-1" />
                  <TrendingDown v-else class="w-3 h-3 mr-1" />
                  <span>{{ netBalanceTrend }}</span>
                </div>
              </div>
              <h2 class="text-4xl lg:text-5xl font-serif text-white tracking-tight">{{ formattedBalance }}</h2>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-6 border-t border-emerald-800/30">
              <div>
                <p class="text-emerald-400 text-[10px] uppercase font-bold tracking-widest mb-1">Income</p>
                <p class="text-lg font-serif text-emerald-50">{{ formattedIncome }}</p>
              </div>
              <div class="pl-4 border-l border-emerald-800/30">
                <p class="text-rose-300 text-[10px] uppercase font-bold tracking-widest mb-1">Expenses</p>
                <p class="text-lg font-serif text-rose-100">{{ formattedExpenses }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- 2. Financial Health (Visual Score) -->
        <Card
          class="relative lg:col-span-4 bg-gradient-to-b from-emerald-900 to-emerald-950 border-0 shadow-lg shadow-emerald-900/20 text-[#FDFCF8] overflow-hidden group flex flex-col justify-between h-full min-h-[280px]">
          <!-- Deco -->
          <div class="absolute bottom-0 left-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
            <div class="w-32 h-32 rounded-full border-[12px] border-emerald-100 -ml-16 -mb-16"></div>
          </div>

          <CardContent class="p-6 relative z-10 flex flex-col h-full justify-between">
            <div class="flex items-center justify-between">
              <span class="text-[10px] text-emerald-200/80 font-bold uppercase tracking-widest font-inter">Financial
                Health</span>
              <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.8)]">
              </div>
            </div>

            <div class="py-4">
              <div class="flex justify-between items-end mb-2">
                <div class="text-3xl font-serif text-white">{{ healthLabel }}</div>
                <div class="text-xl font-serif text-emerald-300">{{ healthScore }}<span
                    class="text-sm text-emerald-600">/100</span></div>
              </div>

              <div class="w-full bg-black/30 h-1.5 rounded-full overflow-hidden backdrop-blur-sm mb-4">
                <div :class="['h-full rounded-full transition-all duration-1000', healthColor]"
                  :style="{ width: `${healthScore}%` }"></div>
              </div>

              <p class="text-xs text-emerald-200/70 font-medium italic leading-relaxed">"{{
                (feedbackStore.currentProgress?.improvement_score ?? 0) >= 80 ? 'Your disciplined saving is blooming.' :
                  'Keep nurturing your financial habits.' }}"</p>
            </div>

            <div class="pt-4 border-t border-emerald-800/30 flex items-center justify-between">
              <span class="text-[10px] uppercase tracking-widest text-emerald-400 font-bold">Status</span>
              <span class="text-xs font-serif text-white">{{ (feedbackStore.currentProgress?.improvement_score ?? 0) >=
                50 ? 'On Track' : 'Needs Care' }}</span>
            </div>
          </CardContent>
        </Card>

        <!-- 3. Active Insights (Equal Priority) -->
        <Card
          class="lg:col-span-4 bg-white/80 backdrop-blur-xl border border-emerald-100/50 shadow-sm flex flex-col h-full min-h-[280px]">
          <CardHeader class="pb-2 pt-6 px-6">
            <div class="flex items-center justify-between">
              <CardTitle class="text-base font-serif text-emerald-950">Active Insights</CardTitle>
              <RouterLink to="/insights"
                class="text-[9px] text-emerald-800 hover:text-emerald-950 uppercase tracking-widest font-black transition-colors border-b border-emerald-800/20 hover:border-emerald-950">
                View All</RouterLink>
            </div>
          </CardHeader>
          <CardContent class="flex-1 overflow-y-auto px-6 pb-6 scrollbar-hide">
            <div class="space-y-3 mt-2">
              <template v-if="feedbackStore.latestFeedback.length > 0">
                <div v-for="item in feedbackStore.latestFeedback.slice(0, 2)" :key="item.id"
                  class="flex items-start gap-3 p-3 rounded-xl bg-emerald-50/50 border border-emerald-100/20 group/item hover:bg-white transition-colors">
                  <div
                    :class="['p-1.5 rounded-lg shrink-0 mt-0.5', item.level === 'advanced' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700']">
                    <TrendingUp v-if="item.rule_id === 'spending_spike' || item.rule_id === 'category_overspend'"
                      class="w-3.5 h-3.5" />
                    <Wallet v-else class="w-3.5 h-3.5" />
                  </div>
                  <div>
                    <p class="text-[10px] font-bold text-emerald-900 uppercase tracking-wide leading-tight">{{
                      item.rule_id.replace(/_/g, ' ') }}</p>
                    <p class="text-xs text-emerald-950/70 mt-1 line-clamp-2 leading-relaxed">{{ item.explanation }}</p>
                  </div>
                </div>
              </template>
              <div v-else class="py-8 text-center flex flex-col items-center justify-center h-full">
                <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center mb-2">
                  <Wallet class="w-4 h-4 text-emerald-200" />
                </div>
                <p class="text-[10px] text-emerald-800/40 uppercase font-black tracking-widest">No active insights</p>
              </div>

              <div v-if="feedbackStore.latestFeedback.length > 0" class="pt-2">
                <button @click="handleRefreshAnalysis" :disabled="feedbackStore.loading"
                  class="w-full h-9 border border-dashed border-emerald-400/50 rounded-xl text-[9px] text-emerald-700 uppercase tracking-[0.2em] font-black hover:bg-emerald-50 transition-all disabled:opacity-50">
                  {{ feedbackStore.loading ? 'Evaluating...' : 'Refresh Analysis' }}
                </button>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Tier 2: Money Flow (Major Data) -->
        <Card class="lg:col-span-8 bg-white/60 backdrop-blur-xl border border-emerald-100/50 shadow-sm">
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle class="text-xl font-serif text-emerald-950">Cash Flow Trends</CardTitle>
            <div class="flex items-center bg-emerald-100/30 p-1 rounded-full">
              <button @click="selectedTimeRange = 'daily'"
                :class="['px-5 h-11 text-[10px] font-bold uppercase tracking-widest rounded-full transition-all', selectedTimeRange === 'daily' ? 'bg-white text-emerald-950 shadow-sm' : 'text-emerald-800 hover:text-emerald-950']">Daily</button>
              <button @click="selectedTimeRange = 'monthly'"
                :class="['px-5 h-11 text-[10px] font-bold uppercase tracking-widest rounded-full transition-all', selectedTimeRange === 'monthly' ? 'bg-white text-emerald-950 shadow-sm' : 'text-emerald-800 hover:text-emerald-950']">Monthly</button>
              <button @click="selectedTimeRange = 'yearly'"
                :class="['px-5 h-11 text-[10px] font-bold uppercase tracking-widest rounded-full transition-all', selectedTimeRange === 'yearly' ? 'bg-white text-emerald-950 shadow-sm' : 'text-emerald-800 hover:text-emerald-950']">Yearly</button>
            </div>
          </CardHeader>
          <CardContent>
            <div class="h-[350px] w-full mt-4">
              <component
                :is="selectedTimeRange === 'daily' ? DailyFlowChart : (selectedTimeRange === 'monthly' ? MonthlyFlowChart : YearlyFlowChart)" />
            </div>
          </CardContent>
        </Card>

        <!-- Tier 2: Expense Breakdown (Circular stats) -->
        <Card class="lg:col-span-4 bg-white/60 backdrop-blur-xl border border-emerald-100/50 shadow-sm overflow-hidden">
          <CardHeader>
            <CardTitle class="text-xl font-serif text-emerald-950">Expense Core</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="transactionStore.expensesByCategory.length === 0" class="text-center py-12">
              <p class="text-emerald-900/50 italic">No seeds sown yet</p>
            </div>
            <div v-else class="space-y-6">
              <div v-for="expense in transactionStore.expensesByCategory.slice(0, 5)" :key="expense.category_id"
                class="group/bar">
                <div class="flex items-center justify-between text-sm mb-2">
                  <span class="font-medium text-emerald-900 font-inter">{{ expense.category.name }}</span>
                  <span class="font-semibold text-emerald-950 font-serif">₱{{ expense.total.toLocaleString() }}</span>
                </div>
                <div class="w-full bg-emerald-100/20 rounded-full h-1.5">
                  <div
                    class="bg-emerald-600 h-1.5 rounded-full transition-all duration-1000 ease-in-out shadow-[0_0_10px_rgba(5,150,105,0.2)]"
                    :style="{
                      width: `${(expense.total / transactionStore.summary.total_expenses) * 100}%`
                    }" />
                </div>
              </div>
            </div>

            <div class="mt-8 pt-6 border-t border-emerald-100/50 grid grid-cols-2 gap-4">
              <div class="p-3 bg-emerald-50/50 rounded-2xl text-center">
                <p class="text-[10px] text-emerald-800 uppercase font-black tracking-widest mb-1">Entries</p>
                <p class="text-lg font-serif text-emerald-950">{{ transactionStore.summary.transaction_count }}</p>
              </div>
              <div class="p-3 bg-emerald-50/50 rounded-2xl text-center">
                <p class="text-[10px] text-emerald-800 uppercase font-black tracking-widest mb-1">Types</p>
                <p class="text-lg font-serif text-emerald-950">{{ categoryStore.categories.length }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Tier 3: Recent Transactions (Wide) -->
        <Card
          class="lg:col-span-12 bg-white/60 backdrop-blur-xl border border-emerald-100/50 shadow-sm overflow-hidden">
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle class="text-xl font-serif text-emerald-950">Recent Garden Activity</CardTitle>
            <RouterLink to="/transactions" class="text-sm text-emerald-700 hover:underline">Full History →</RouterLink>
          </CardHeader>
          <CardContent class="p-0">
            <div v-if="transactionStore.transactions.length === 0" class="text-center py-20 bg-emerald-50/30">
              <p class="text-emerald-900/30 font-light italic">The soil is currently quiet.</p>
            </div>
            <div v-else class="overflow-x-auto">
              <table class="w-full text-left">
                <thead>
                  <tr class="border-b border-emerald-100/50 bg-emerald-50/30">
                    <th class="px-8 py-3 text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Type</th>
                    <th class="px-8 py-3 text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Description
                    </th>
                    <th class="px-8 py-3 text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Date</th>
                    <th
                      class="px-8 py-3 text-right text-[10px] font-bold text-emerald-800 uppercase tracking-widest pr-10">
                      Amount</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50/50">
                  <tr v-for="transaction in transactionStore.transactions.slice(0, 6)" :key="transaction.id"
                    class="hover:bg-emerald-50/30 transition-colors group cursor-default">
                    <td class="px-8 py-5">
                      <div :class="[
                        'w-8 h-8 rounded-lg flex items-center justify-center transition-all',
                        transaction.type === 'income' ? 'bg-emerald-100/60 text-emerald-700 group-hover:scale-110 shadow-sm' : 'bg-rose-100/60 text-rose-700 group-hover:scale-110 shadow-sm'
                      ]">
                        <component :is="transaction.type === 'income' ? ArrowUpRight : ArrowDownRight"
                          class="w-4 h-4" />
                      </div>
                    </td>
                    <td class="px-8 py-5">
                      <p class="font-medium text-emerald-950 text-sm">{{ transaction.category?.name }}</p>
                      <p class="text-xs text-emerald-800 italic font-medium">{{ transaction.description || 'No notes added' }}
                      </p>
                    </td>
                    <td class="px-8 py-5">
                      <p class="text-xs text-emerald-700 font-bold uppercase tracking-tight">{{
                        formatTransactionDate(transaction.date) }}</p>
                    </td>
                    <td class="px-8 py-5 text-right pr-10">
                      <span :class="[
                        'font-semibold font-serif text-base',
                        transaction.type === 'income' ? 'text-emerald-700' : 'text-rose-700'
                      ]">
                        {{ transaction.type === 'income' ? '+' : '-' }}₱{{ transaction.amount.toLocaleString() }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Behavioral Insights (Original Component but better integrated at the bottom as well) -->
      <div class="pt-8">
        <BehavioralInsights />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed, ref } from 'vue';
import { useTransactionStore } from '../stores/transactionStore';
import { useCategoryStore } from '../stores/categoryStore';
import { useFeedbackStore } from '../stores/feedbackStore';
import { TrendingUp, TrendingDown, Wallet, ArrowUpRight, ArrowDownRight, Layers } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { RouterLink } from 'vue-router';
import { formatDateSafe } from '@/utils';
import DailyFlowChart from '@/components/charts/DailyFlowChart.vue';
import MonthlyFlowChart from '@/components/charts/MonthlyFlowChart.vue';
import YearlyFlowChart from '@/components/charts/YearlyFlowChart.vue';
import BehavioralInsights from '@/components/feedback/BehavioralInsights.vue';
import InitializationLoader from '@/components/loaders/InitializationLoader.vue';
import { useBotanicalFeedback } from '@/composables/useBotanicalFeedback';
import type { TimeRange } from '@/types';

const transactionStore = useTransactionStore();
const categoryStore = useCategoryStore();
const feedbackStore = useFeedbackStore();
const { notifyWisdomRefreshed } = useBotanicalFeedback();

const selectedTimeRange = ref<TimeRange>('daily');

const isInitialLoad = !transactionStore.isSummaryFetched || !categoryStore.isCategoriesFetched || !feedbackStore.isFeedbackFetched;
const isLoading = ref(isInitialLoad);

const formattedIncome = computed(() =>
  transactionStore.summary.total_income.toLocaleString('en-PH', {
    style: 'currency',
    currency: 'PHP'
  })
);

const formattedExpenses = computed(() =>
  transactionStore.summary.total_expenses.toLocaleString('en-PH', {
    style: 'currency',
    currency: 'PHP'
  })
);

const formattedBalance = computed(() =>
  transactionStore.summary.net_balance.toLocaleString('en-PH', {
    style: 'currency',
    currency: 'PHP'
  })
);

const netBalanceTrend = computed(() => {
  const trend = transactionStore.summary.net_balance_trend || 0;
  return trend > 0 ? `+${trend.toFixed(1)}%` : `${trend.toFixed(1)}%`;
});

const netBalanceTrendColor = computed(() => {
  const trend = transactionStore.summary.net_balance_trend || 0;
  return trend >= 0 ? 'text-emerald-300 bg-emerald-800/40' : 'text-rose-300 bg-rose-800/40';
});

const healthScore = computed(() => feedbackStore.currentProgress?.improvement_score || 0);

const healthLabel = computed(() => {
  const score = healthScore.value;
  if (score >= 80) return 'Excellent';
  if (score >= 50) return 'Stable';
  return 'Attention';
});

const healthColor = computed(() => {
  const score = healthScore.value;
  if (score >= 80) return 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]';
  if (score >= 50) return 'bg-teal-400 shadow-[0_0_8px_rgba(45,212,191,0.5)]';
  return 'bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.5)]';
});

function formatTransactionDate(dateString: string): string {
  if (dateString.includes('-')) {
    const [year, month, day] = dateString.split('-');
    const formattedInput = `${parseInt(month!)}/${parseInt(day!)}/${year}`;
    return formatDateSafe(formattedInput) || dateString;
  }
  return formatDateSafe(dateString) || dateString;
}

async function handleRefreshAnalysis() {
  try {
    await feedbackStore.evaluateRules();
    notifyWisdomRefreshed();
  } catch (err) {
    console.error('Failed to refresh analysis:', err);
  }
}

onMounted(async () => {
  if (isLoading.value) {
    try {
      const minLoaderTime = new Promise(resolve => setTimeout(resolve, 1500));
      await Promise.all([
        transactionStore.fetchSummary(),
        categoryStore.fetchCategories(),
        feedbackStore.fetchFeedback(),
        feedbackStore.fetchProgress(),
        minLoaderTime
      ]);
    } finally {
      isLoading.value = false;
    }
  } else {
    transactionStore.fetchSummary();
    categoryStore.fetchCategories();
    feedbackStore.fetchFeedback();
    feedbackStore.fetchProgress();
  }
});
</script>

<style scoped>
.font-serif {
  font-family: 'Playfair Display', serif;
}

.font-inter {
  font-family: 'Inter', sans-serif;
}

.font-black {
  font-weight: 900;
}

/* Custom Scrollbar for the table if needed */
.overflow-x-auto::-webkit-scrollbar {
  height: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: rgba(16, 185, 129, 0.1);
  border-radius: 10px;
}
</style>
