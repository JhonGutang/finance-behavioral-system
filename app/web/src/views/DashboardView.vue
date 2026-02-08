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
          <RouterLink 
            to="/transactions" 
            class="inline-flex items-center justify-center rounded-full bg-emerald-900 px-8 h-11 text-[11px] font-bold text-white shadow-lg shadow-emerald-900/10 hover:bg-emerald-800 transition-all hover:-translate-y-0.5 uppercase tracking-widest"
          >
            Add Transaction
          </RouterLink>
        </div>
      </div>

      <!-- Main Bento Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Hero: Net Balance & Score (Tier 1) -->
        <Card class="relative lg:col-span-8 bg-gradient-to-br from-emerald-900 to-emerald-950 border-0 shadow-xl shadow-emerald-900/20 text-[#FDFCF8] overflow-hidden group">
          <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:opacity-20 transition-opacity">
            <Layers class="w-48 h-48 -mr-16 -mt-16" />
          </div>
          <CardContent class="p-8 md:p-10 relative z-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
              <div class="space-y-4">
                <p class="text-emerald-100 uppercase tracking-[0.2em] text-xs font-bold font-inter">Net Balance</p>
                <div class="flex items-baseline gap-2">
                   <h2 class="text-5xl md:text-6xl font-serif text-white">{{ formattedBalance }}</h2>
                   <div class="flex items-center text-emerald-300 text-sm font-medium px-2 py-0.5 bg-emerald-800/40 rounded-full">
                      <TrendingUp class="w-3 h-3 mr-1" />
                      <span>+2.4%</span>
                   </div>
                </div>
                <div class="flex gap-8 pt-4">
                  <div>
                    <p class="text-emerald-200 text-[10px] uppercase font-bold tracking-widest mb-1 italic">Income This Period</p>
                    <p class="text-xl font-serif text-emerald-50">{{ formattedIncome }}</p>
                  </div>
                  <div class="w-px h-10 bg-emerald-800/50"></div>
                  <div>
                    <p class="text-emerald-200 text-[10px] uppercase font-bold tracking-widest mb-1 italic">Expenses This Period</p>
                    <p class="text-xl font-serif text-rose-200">{{ formattedExpenses }}</p>
                  </div>
                </div>
              </div>

              <!-- Behavioral Health Mini-Widget -->
              <div class="bg-white/5 border border-white/10 backdrop-blur-md p-6 rounded-3xl md:w-64">
                <div class="flex items-center justify-between mb-4">
                   <span class="text-xs text-emerald-100 font-bold uppercase tracking-widest font-inter">Financial Health</span>
                   <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                </div>
                <div class="text-4xl font-serif text-white mb-2">Excellent</div>
                <div class="w-full bg-white/10 h-1.5 rounded-full mt-4 overflow-hidden">
                   <div class="bg-emerald-400 h-full w-[88%] rounded-full shadow-[0_0_8px_rgba(52,211,153,0.5)]"></div>
                </div>
                <p class="text-[10px] text-emerald-200 mt-3 font-medium italic">"Your disciplined saving is blooming."</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Secondary: Behavioral Insights Quick Look (Tier 1/2) -->
        <Card class="lg:col-span-4 bg-white/60 backdrop-blur-xl border border-emerald-100/50 shadow-sm flex flex-col group hover:shadow-md transition-all">
          <CardHeader class="pb-2">
             <div class="flex items-center justify-between">
                <CardTitle class="text-lg font-serif text-emerald-950">Active Insights</CardTitle>
                                <RouterLink to="/insights" class="text-[10px] text-emerald-800 hover:text-emerald-950 uppercase tracking-widest font-black transition-colors">View All →</RouterLink>
             </div>
          </CardHeader>
          <CardContent class="flex-1 overflow-hidden">
             <!-- Simplified list for sidebar look -->
             <div class="space-y-4 mt-2">
                <div class="flex items-start gap-3 p-3 rounded-2xl bg-emerald-50/50 border border-emerald-100/20 group/item hover:bg-white transition-colors">
                   <div class="p-2 rounded-xl bg-emerald-100 text-emerald-700">
                      <TrendingUp class="w-4 h-4" />
                   </div>
                   <div>
                      <p class="text-xs font-bold text-emerald-900 group-hover:text-emerald-700 transition-colors uppercase tracking-tight">Category Optimization</p>
                      <p class="text-sm text-emerald-950/70 mt-1 line-clamp-2">You've reached 85% of your Dining budget early this week.</p>
                   </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-2xl bg-rose-50/30 border border-rose-100/20 group/item hover:bg-white transition-colors">
                   <div class="p-2 rounded-xl bg-rose-100 text-rose-700">
                      <Wallet class="w-4 h-4" />
                   </div>
                   <div>
                      <p class="text-xs font-bold text-rose-900 group-hover:text-rose-700 transition-colors uppercase tracking-tight">Spending Spike</p>
                      <p class="text-sm text-emerald-950/70 mt-1 line-clamp-2">Unusual activity detected in Utilities today.</p>
                   </div>
                </div>
                <div class="pt-2">
                   <button class="w-full h-11 border border-dashed border-emerald-400 rounded-2xl text-[10px] text-emerald-800 uppercase tracking-[0.2em] font-black hover:bg-emerald-50 transition-all">
                      Evaluate Rules Now
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
               <button 
                 @click="selectedTimeRange = 'daily'"
                 :class="['px-5 h-9 text-[10px] font-bold uppercase tracking-widest rounded-full transition-all', selectedTimeRange === 'daily' ? 'bg-white text-emerald-950 shadow-sm' : 'text-emerald-800 hover:text-emerald-950']"
               >Daily</button>
               <button 
                 @click="selectedTimeRange = 'monthly'"
                 :class="['px-5 h-9 text-[10px] font-bold uppercase tracking-widest rounded-full transition-all', selectedTimeRange === 'monthly' ? 'bg-white text-emerald-950 shadow-sm' : 'text-emerald-800 hover:text-emerald-950']"
               >Monthly</button>
               <button 
                 @click="selectedTimeRange = 'yearly'"
                 :class="['px-5 h-9 text-[10px] font-bold uppercase tracking-widest rounded-full transition-all', selectedTimeRange === 'yearly' ? 'bg-white text-emerald-950 shadow-sm' : 'text-emerald-800 hover:text-emerald-950']"
               >Yearly</button>
            </div>
          </CardHeader>
          <CardContent>
            <div class="h-[350px] w-full mt-4">
              <component :is="selectedTimeRange === 'daily' ? DailyFlowChart : (selectedTimeRange === 'monthly' ? MonthlyFlowChart : YearlyFlowChart)" />
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
              <div 
                v-for="expense in transactionStore.expensesByCategory.slice(0, 5)" 
                :key="expense.category_id"
                class="group/bar"
              >
                <div class="flex items-center justify-between text-sm mb-2">
                  <span class="font-medium text-emerald-900 font-inter">{{ expense.category.name }}</span>
                  <span class="font-semibold text-emerald-950 font-serif">₱{{ expense.total.toLocaleString() }}</span>
                </div>
                <div class="w-full bg-emerald-100/20 rounded-full h-1.5">
                  <div 
                    class="bg-emerald-600 h-1.5 rounded-full transition-all duration-1000 ease-in-out shadow-[0_0_10px_rgba(5,150,105,0.2)]"
                    :style="{ 
                      width: `${(expense.total / transactionStore.summary.total_expenses) * 100}%` 
                    }"
                  />
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
        <Card class="lg:col-span-12 bg-white/60 backdrop-blur-xl border border-emerald-100/50 shadow-sm overflow-hidden">
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
                          <th class="px-8 py-3 text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Description</th>
                          <th class="px-8 py-3 text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Date</th>
                          <th class="px-8 py-3 text-right text-[10px] font-bold text-emerald-800 uppercase tracking-widest pr-10">Amount</th>
                       </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50/50">
                       <tr 
                          v-for="transaction in transactionStore.transactions.slice(0, 6)" 
                          :key="transaction.id"
                          class="hover:bg-emerald-50/30 transition-colors group cursor-default"
                       >
                          <td class="px-8 py-5">
                             <div 
                                :class="[
                                  'w-8 h-8 rounded-lg flex items-center justify-center transition-all',
                                  transaction.type === 'income' ? 'bg-emerald-100/60 text-emerald-700 group-hover:scale-110 shadow-sm' : 'bg-rose-100/60 text-rose-700 group-hover:scale-110 shadow-sm'
                                ]"
                             >
                                <component :is="transaction.type === 'income' ? ArrowUpRight : ArrowDownRight" class="w-4 h-4" />
                             </div>
                          </td>
                          <td class="px-8 py-5">
                             <p class="font-medium text-emerald-950 text-sm">{{ transaction.category?.name }}</p>
                             <p class="text-xs text-emerald-800 italic font-medium">{{ transaction.description || 'No notes added' }}</p>
                          </td>
                          <td class="px-8 py-5">
                             <p class="text-xs text-emerald-700 font-bold uppercase tracking-tight">{{ formatTransactionDate(transaction.date) }}</p>
                          </td>
                          <td class="px-8 py-5 text-right pr-10">
                             <span 
                                :class="[
                                  'font-semibold font-serif text-base',
                                  transaction.type === 'income' ? 'text-emerald-700' : 'text-rose-700'
                                ]"
                             >
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
import { TrendingUp, Wallet, ArrowUpRight, ArrowDownRight, Layers } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { RouterLink } from 'vue-router';
import { formatDateSafe } from '@/utils';
import DailyFlowChart from '@/components/charts/DailyFlowChart.vue';
import MonthlyFlowChart from '@/components/charts/MonthlyFlowChart.vue';
import YearlyFlowChart from '@/components/charts/YearlyFlowChart.vue';
import BehavioralInsights from '@/components/feedback/BehavioralInsights.vue';
import InitializationLoader from '@/components/loaders/InitializationLoader.vue';
import type { TimeRange } from '@/types';

const transactionStore = useTransactionStore();
const categoryStore = useCategoryStore();

const selectedTimeRange = ref<TimeRange>('daily');

const isInitialLoad = !transactionStore.isSummaryFetched || !categoryStore.isCategoriesFetched;
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

function formatTransactionDate(dateString: string): string {
  if (dateString.includes('-')) {
    const [year, month, day] = dateString.split('-');
    const formattedInput = `${parseInt(month!)}/${parseInt(day!)}/${year}`;
    return formatDateSafe(formattedInput) || dateString;
  }
  return formatDateSafe(dateString) || dateString;
}

onMounted(async () => {
  if (isLoading.value) {
    try {
      const minLoaderTime = new Promise(resolve => setTimeout(resolve, 1500));
      await Promise.all([
        transactionStore.fetchSummary(),
        categoryStore.fetchCategories(),
        minLoaderTime
      ]);
    } finally {
      isLoading.value = false;
    }
  } else {
    transactionStore.fetchSummary();
    categoryStore.fetchCategories();
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
