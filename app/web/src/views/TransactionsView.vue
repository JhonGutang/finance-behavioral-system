<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useTransactionStore } from '../stores/transactionStore';
import { useCategoryStore } from '../stores/categoryStore';
import { 
  Plus, 
  Pencil, 
  Trash2, 
  Sparkles, 
  BrainCircuit, 
  Upload, 
  Filter, 
  Search,
  ArrowUpRight,
  ArrowDownRight,
  ChevronLeft,
  ChevronRight,
  X
} from 'lucide-vue-next';
import type { Transaction, TransactionFormData } from '../types';
import RuleEvaluationModal from '@/components/RuleEvaluationModal.vue';
import { useFeedbackStore } from '@/stores/feedbackStore';
import { formatDateSafe } from '@/utils';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import CsvUploadModal from '@/components/transactions/CsvUploadModal.vue';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const transactionStore = useTransactionStore();
const categoryStore = useCategoryStore();
const feedbackStore = useFeedbackStore();
const router = useRouter();

const showModal = ref(false);
const showFilters = ref(false);
const editingTransaction = ref<Transaction | null>(null);
const formData = ref<TransactionFormData>({
  type: 'expense',
  amount: '',
  date: new Date().toISOString().split('T')[0]!,
  category_id: '',
  description: undefined,
});

const ruleEvaluationModal = ref<InstanceType<typeof RuleEvaluationModal> | null>(null);
const showImportModal = ref(false);

const filteredCategories = computed(() => {
  return formData.value.type === 'income' 
    ? categoryStore.incomeCategories 
    : categoryStore.expenseCategories;
});

const isFormValid = computed(() => {
  return formData.value.amount && 
         formData.value.date && 
         formData.value.category_id;
});

function openCreateModal() {
  editingTransaction.value = null;
  formData.value = {
    type: 'expense',
    amount: '',
    date: new Date().toISOString().split('T')[0]!,
    category_id: '',
    description: undefined,
  };
  showModal.value = true;
}

function openEditModal(transaction: Transaction) {
  editingTransaction.value = transaction;
  formData.value = {
    type: transaction.type,
    amount: transaction.amount.toString(),
    date: transaction.date,
    category_id: transaction.category_id,
    description: transaction.description,
  };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  editingTransaction.value = null;
}

async function handleSubmit() {
  if (!isFormValid.value) return;

  try {
    const data = {
      ...formData.value,
      amount: parseFloat(formData.value.amount as string),
    };

    if (editingTransaction.value) {
      await transactionStore.updateTransaction(editingTransaction.value.id, data);
    } else {
      await transactionStore.createTransaction(data);
    }
    
    closeModal();
    await transactionStore.fetchTransactions();
  } catch (error) {
    console.error('Failed to save transaction:', error);
  }
}

async function handleDelete(id: number) {
  if (confirm('Are you sure you want to delete this transaction?')) {
    try {
      await transactionStore.deleteTransaction(id);
      await transactionStore.fetchTransactions();
    } catch (error) {
      console.error('Failed to delete transaction:', error);
    }
  }
}

const needsReevaluation = computed(() => {
  if (!feedbackStore.hasEvaluatedThisWeek) return true;
  
  const currentProgress = feedbackStore.currentProgress;
  if (!currentProgress) return true;

  const mondayStr = feedbackStore.currentWeekMonday;
  if (!mondayStr) return true;
  
  const currentWeekTransactions = transactionStore.transactions.filter(t => t.date >= mondayStr);
  const progressUpdatedAt = new Date(currentProgress.updated_at).getTime();
  
  return currentWeekTransactions.some(t => {
    const updatedAt = new Date(t.updated_at || t.created_at).getTime();
    return updatedAt > progressUpdatedAt;
  });
});

function openRuleEvaluation() {
  if (!needsReevaluation.value && feedbackStore.hasEvaluatedThisWeek) {
    router.push('/insights');
    return;
  }
  ruleEvaluationModal.value?.open();
}

function formatTransactionDate(dateString: string): string {
  if (dateString.includes('-')) {
    const [year, month, day] = dateString.split('-');
    const formattedInput = `${parseInt(month!)}/${parseInt(day!)}/${year}`;
    return formatDateSafe(formattedInput) || dateString;
  }
  return formatDateSafe(dateString) || dateString;
}

const activeFiltersCount = computed(() => {
  let count = 0;
  if (transactionStore.queryParams.type) count++;
  if (transactionStore.queryParams.category_id) count++;
  if (transactionStore.queryParams.start_date) count++;
  if (transactionStore.queryParams.end_date) count++;
  if (transactionStore.queryParams.min_amount) count++;
  if (transactionStore.queryParams.max_amount) count++;
  return count;
});

onMounted(async () => {
  await Promise.all([
    transactionStore.fetchTransactions(),
    categoryStore.fetchCategories(),
    feedbackStore.fetchProgress(),
  ]);
});
</script>

<template>
  <div class="min-h-screen animate-in fade-in duration-1000 slide-in-from-bottom-2 font-inter pb-20">
    <!-- Background Texture & Atmosphere -->
    <div class="fixed inset-0 pointer-events-none opacity-40 mix-blend-multiply -z-10">
      <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-30"></div>
    </div>
    <div class="fixed inset-0 bg-[#FDFCF8] -z-20"></div>

    <div class="space-y-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-start justify-between gap-8">
        <div>
          <h1 class="text-4xl md:text-5xl font-serif font-medium text-emerald-950 tracking-tight transition-all">Transaction Ledger</h1>
          <p class="text-emerald-800 mt-3 font-light text-lg max-w-xl">
             Every seed planted and every harvest gathered. Keep your financial garden orderly.
          </p>
        </div>
        
        <div class="flex flex-col gap-3 min-w-[320px]">
          <div class="flex items-center gap-3">
            <Button 
              variant="outline" 
              @click="openRuleEvaluation" 
              class="flex-1 rounded-full border-emerald-100 bg-emerald-50/30 text-emerald-800 hover:bg-emerald-50 transition-all font-semibold h-11 uppercase tracking-wider text-[10px]"
            >
              <component :is="needsReevaluation ? Sparkles : BrainCircuit" class="w-4 h-4 mr-2" />
              {{ !feedbackStore.hasEvaluatedThisWeek ? 'Evaluate' : (needsReevaluation ? 'Re-evaluate' : 'View Insights') }}
            </Button>
            <Button 
              variant="outline" 
              @click="showImportModal = true" 
              class="rounded-full border-stone-200 bg-stone-50/30 text-stone-600 hover:bg-stone-50 transition-all h-11 px-5 uppercase tracking-wider text-[10px] font-semibold"
            >
              <Upload class="w-4 h-4 mr-2" />
              Import CSV
            </Button>
          </div>
          
          <Button 
            @click="openCreateModal" 
            class="rounded-full bg-[#064e3b] hover:bg-[#065f46] text-white shadow-md shadow-emerald-900/10 transition-all h-11 px-8 font-bold uppercase tracking-widest text-[11px]"
          >
            <Plus class="w-4 h-4 mr-2" />
            Add Transaction
          </Button>
        </div>
      </div>

      <!-- Quick Stats & Filter Toggle -->
      <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4 overflow-x-auto pb-2 lg:pb-0 w-full lg:w-auto">
          <div class="px-4 py-2 bg-white/60 backdrop-blur-sm border border-emerald-100/50 rounded-2xl flex items-center gap-3">
            <span class="text-[10px] uppercase tracking-widest font-bold text-emerald-700">Total Found</span>
            <span class="font-serif text-lg text-emerald-950">{{ transactionStore.paginationMeta.total }}</span>
          </div>
          <div v-if="activeFiltersCount > 0" class="flex items-center gap-2">
            <Button 
              variant="ghost" 
              size="sm" 
              class="h-9 px-4 rounded-full text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition-colors font-bold uppercase tracking-wider text-[10px]"
              @click="transactionStore.fetchTransactions({ 
                type: undefined, 
                category_id: undefined, 
                start_date: undefined, 
                end_date: undefined, 
                min_amount: undefined, 
                max_amount: undefined, 
                page: 1 
              })"
            >
              Reset Filters ({{ activeFiltersCount }})
              <X class="w-3 h-3 ml-2" />
            </Button>
          </div>
        </div>
        
        <Button 
          variant="outline" 
          @click="showFilters = !showFilters" 
          :class="[
            'rounded-full h-11 px-6 transition-all font-bold uppercase tracking-widest text-[10px]',
            showFilters ? 'bg-emerald-50 border-emerald-200 text-emerald-900 shadow-sm' : 'bg-white/60 text-emerald-800 hover:text-emerald-950'
          ]"
        >
          <Filter class="w-4 h-4 mr-2" />
          {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
        </Button>
      </div>

      <!-- Filters Section (Collapsible) -->
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-4"
      >
        <Card v-if="showFilters" class="bg-white/80 backdrop-blur-xl border-emerald-100/50 shadow-sm overflow-hidden">
          <CardContent class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
              <div class="space-y-2">
                <Label class="text-[10px] uppercase tracking-[0.2em] font-bold text-emerald-800 px-1">Type</Label>
                <Select 
                  :model-value="transactionStore.queryParams.type || 'all'" 
                  @update:model-value="v => transactionStore.fetchTransactions({ type: v === 'all' ? undefined : v as any, page: 1 })"
                >
                  <SelectTrigger class="rounded-xl border-stone-200 focus:ring-emerald-500 bg-white">
                    <SelectValue placeholder="All types" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Types</SelectItem>
                    <SelectItem value="income">Income</SelectItem>
                    <SelectItem value="expense">Expense</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-2">
                <Label class="text-[10px] uppercase tracking-[0.2em] font-bold text-emerald-800 px-1">Category</Label>
                <Select 
                  :model-value="transactionStore.queryParams.category_id?.toString() || 'all'" 
                  @update:model-value="(v: any) => transactionStore.fetchTransactions({ category_id: v === 'all' ? undefined : parseInt(v), page: 1 })"
                >
                  <SelectTrigger class="rounded-xl border-stone-200 focus:ring-emerald-500 bg-white">
                    <SelectValue placeholder="All categories" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Categories</SelectItem>
                    <SelectItem 
                      v-for="cat in categoryStore.categories" 
                      :key="cat.id" 
                      :value="cat.id.toString()"
                    >
                      {{ cat.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-2">
                <Label class="text-[10px] uppercase tracking-[0.2em] font-bold text-emerald-800 px-1">From Date</Label>
                <Input 
                  type="date" 
                  class="rounded-xl border-stone-200 bg-white"
                  :model-value="transactionStore.queryParams.start_date" 
                  @input="(e: Event) => transactionStore.fetchTransactions({ start_date: (e.target as HTMLInputElement).value || undefined, page: 1 })"
                />
              </div>

              <div class="space-y-2">
                <Label class="text-[10px] uppercase tracking-[0.2em] font-bold text-emerald-800 px-1">To Date</Label>
                <Input 
                  type="date" 
                  class="rounded-xl border-stone-200 bg-white"
                  :model-value="transactionStore.queryParams.end_date" 
                  @input="(e: Event) => transactionStore.fetchTransactions({ end_date: (e.target as HTMLInputElement).value || undefined, page: 1 })"
                />
              </div>

              <div class="space-y-2">
                <Label class="text-[10px] uppercase tracking-[0.2em] font-bold text-emerald-800 px-1">Min amount</Label>
                <div class="relative">
                   <div class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-900/30 text-xs">₱</div>
                   <Input 
                    type="number" 
                    placeholder="0.00"
                    class="rounded-xl border-stone-200 pl-7 bg-white"
                    :model-value="transactionStore.queryParams.min_amount" 
                    @input="(e: Event) => transactionStore.fetchTransactions({ min_amount: (e.target as HTMLInputElement).value ? parseFloat((e.target as HTMLInputElement).value) : undefined, page: 1 })"
                  />
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-[10px] uppercase tracking-[0.2em] font-bold text-emerald-800 px-1">Max amount</Label>
                <div class="relative">
                   <div class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-900/30 text-xs">₱</div>
                   <Input 
                    type="number" 
                    placeholder="999..."
                    class="rounded-xl border-stone-200 pl-7 bg-white"
                    :model-value="transactionStore.queryParams.max_amount" 
                    @input="(e: Event) => transactionStore.fetchTransactions({ max_amount: (e.target as HTMLInputElement).value ? parseFloat((e.target as HTMLInputElement).value) : undefined, page: 1 })"
                  />
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </Transition>

      <!-- Transactions List -->
      <Card class="bg-white/60 backdrop-blur-xl border-emerald-100/50 shadow-sm overflow-hidden">
        <CardContent class="p-0">
          <div v-if="transactionStore.loading" class="p-20 text-center">
            <div class="flex flex-col items-center gap-4">
               <div class="w-10 h-10 rounded-full border-2 border-emerald-100 border-t-emerald-600 animate-spin"></div>
               <p class="text-emerald-700 italic font-light">Analyzing the ledger...</p>
            </div>
          </div>
          
          <div v-else-if="transactionStore.transactions.length === 0" class="p-24 text-center">
            <div class="max-w-xs mx-auto">
               <div class="w-16 h-16 bg-emerald-50 text-emerald-200 rounded-full flex items-center justify-center mx-auto mb-6">
                  <Search class="w-8 h-8" />
               </div>
               <h3 class="text-xl font-serif text-emerald-950 mb-2">No activity found</h3>
               <p class="text-emerald-900/50 font-light mb-6">We couldn't find any transactions matching your current filters.</p>
               <Button variant="outline" class="rounded-full border-stone-200 text-emerald-800 font-bold uppercase tracking-widest text-[10px] h-11 px-6 hover:bg-emerald-50 transition-all" @click="openCreateModal">
                 Plant your first seed
               </Button>
            </div>
          </div>

          <div v-else>
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead>
                  <tr class="border-b border-emerald-100/50 bg-emerald-50/20">
                    <th class="px-8 py-4 text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Type</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Date</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Category & Notes</th>
                    <th class="px-8 py-4 text-right text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Amount</th>
                    <th class="px-8 py-4 text-right text-[10px] font-bold text-emerald-800 uppercase tracking-widest pr-10">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50/30">
                  <tr 
                    v-for="transaction in transactionStore.transactions" 
                    :key="transaction.id"
                    class="hover:bg-emerald-50/40 transition-all group duration-300"
                  >
                    <td class="px-8 py-5">
                       <div 
                          :class="[
                            'w-9 h-9 rounded-xl flex items-center justify-center transition-all',
                            transaction.type === 'income' ? 'bg-emerald-100/60 text-emerald-700 shadow-sm' : 'bg-rose-100/60 text-rose-700 shadow-sm'
                          ]"
                       >
                          <component :is="transaction.type === 'income' ? ArrowUpRight : ArrowDownRight" class="w-4 h-4" />
                       </div>
                    </td>
                    <td class="px-8 py-5">
                      <p class="text-sm font-medium text-emerald-950">{{ formatTransactionDate(transaction.date) }}</p>
                      <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-tight mt-0.5">Time recorded</p>
                    </td>
                    <td class="px-8 py-5">
                       <div>
                          <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/80 border border-emerald-100/50 text-emerald-900 mb-1">
                            {{ transaction.category?.name }}
                          </span>
                          <p class="text-sm text-emerald-950/60 italic font-light max-w-sm truncate">{{ transaction.description || '-' }}</p>
                       </div>
                    </td>
                    <td class="px-8 py-5 text-right">
                      <span 
                        :class="[
                          'font-semibold font-serif text-lg tracking-tight',
                          transaction.type === 'income' ? 'text-emerald-700' : 'text-rose-700'
                        ]"
                      >
                        {{ transaction.type === 'income' ? '+' : '-' }}₱{{ transaction.amount.toLocaleString() }}
                      </span>
                    </td>
                    <td class="px-8 py-5 text-right pr-10">
                      <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <Button
                          variant="ghost"
                          size="icon"
                          class="w-9 h-9 rounded-lg hover:bg-emerald-100 text-emerald-700 transition-colors"
                          @click="openEditModal(transaction)"
                        >
                          <Pencil class="w-4 h-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="w-9 h-9 rounded-lg hover:bg-rose-100 text-rose-600 transition-colors"
                          @click="handleDelete(transaction.id)"
                        >
                          <Trash2 class="w-4 h-4" />
                        </Button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="transactionStore.paginationMeta.last_page > 1" class="px-8 py-6 border-t border-emerald-100/50 flex flex-col md:flex-row items-center justify-between gap-4">
              <p class="text-[10px] uppercase font-bold tracking-widest text-emerald-700">
                Viewing <span class="text-emerald-950 font-black">{{ transactionStore.paginationMeta.total > 0 ? (transactionStore.paginationMeta.current_page - 1) * transactionStore.paginationMeta.per_page + 1 : 0 }}</span>
                to <span class="text-emerald-950 font-black">{{ Math.min(transactionStore.paginationMeta.current_page * transactionStore.paginationMeta.per_page, transactionStore.paginationMeta.total) }}</span>
                of <span class="text-emerald-950 font-black">{{ transactionStore.paginationMeta.total }}</span> entries
              </p>
              
              <div class="flex items-center gap-2">
                <Button 
                  variant="ghost" 
                  size="sm" 
                  class="rounded-full h-10 w-10 p-0 text-emerald-900/60 hover:bg-emerald-50 disabled:opacity-30"
                  :disabled="transactionStore.paginationMeta.current_page === 1"
                  @click="transactionStore.goToPage(transactionStore.paginationMeta.current_page - 1)"
                >
                  <ChevronLeft class="w-5 h-5" />
                </Button>
                
                <div class="flex items-center gap-1">
                  <Button 
                    v-for="page in transactionStore.paginationMeta.last_page" 
                    :key="page"
                    variant="ghost"
                    size="sm"
                    :class="[
                      'w-10 h-10 text-xs font-bold rounded-full transition-all',
                      transactionStore.paginationMeta.current_page === page 
                        ? 'bg-emerald-900 text-white shadow-md shadow-emerald-900/10' 
                        : 'text-emerald-900/60 hover:bg-emerald-50'
                    ]"
                    @click="transactionStore.goToPage(page)"
                  >
                    {{ page }}
                  </Button>
                </div>

                <Button 
                  variant="ghost" 
                  size="sm" 
                  class="rounded-full h-10 w-10 p-0 text-emerald-900/60 hover:bg-emerald-50 disabled:opacity-30"
                  :disabled="transactionStore.paginationMeta.current_page === transactionStore.paginationMeta.last_page"
                  @click="transactionStore.goToPage(transactionStore.paginationMeta.current_page + 1)"
                >
                  <ChevronRight class="w-5 h-5" />
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Modal: Add/Edit -->
    <Dialog v-model:open="showModal">
      <DialogContent class="sm:max-w-md bg-[#FDFCF8] border-emerald-100 rounded-[2rem]">
        <DialogHeader>
          <DialogTitle class="text-2xl font-serif text-emerald-950">
            {{ editingTransaction ? 'Refine Transaction' : 'Record New Entry' }}
          </DialogTitle>
        </DialogHeader>

        <form @submit.prevent="handleSubmit" class="space-y-6 pt-4">
          <!-- Type Toggle -->
          <div class="flex p-1 bg-emerald-50/50 rounded-2xl border border-emerald-100/50">
             <button
                type="button"
                @click="formData.type = 'income'; formData.category_id = ''"
                :class="[
                  'flex-1 py-3 px-4 rounded-xl text-xs font-bold uppercase tracking-widest transition-all',
                  formData.type === 'income' ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-900/40'
                ]"
             >Income</button>
             <button
                type="button"
                @click="formData.type = 'expense'; formData.category_id = ''"
                :class="[
                  'flex-1 py-3 px-4 rounded-xl text-xs font-bold uppercase tracking-widest transition-all transition-all',
                  formData.type === 'expense' ? 'bg-white text-rose-700 shadow-sm' : 'text-emerald-900/40'
                ]"
             >Expense</button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
             <!-- Category -->
            <div class="space-y-2">
              <Label class="text-[10px] font-bold uppercase tracking-widest text-emerald-800">Category</Label>
              <Select v-model:model-value="formData.category_id" required>
                <SelectTrigger class="rounded-xl border-stone-200 h-11 bg-white">
                  <SelectValue placeholder="Selection" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem 
                    v-for="category in filteredCategories" 
                    :key="category.id" 
                    :value="category.id.toString()"
                  >
                    {{ category.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Date -->
            <div class="space-y-2">
              <Label class="text-[10px] font-bold uppercase tracking-widest text-emerald-800">Date</Label>
              <Input
                v-model="formData.date"
                type="date"
                required
                class="rounded-xl border-stone-200 h-11 bg-white"
                :max="new Date().toISOString().split('T')[0]"
              />
            </div>
          </div>

          <!-- Amount -->
          <div class="space-y-2">
            <Label class="text-[10px] font-bold uppercase tracking-widest text-emerald-800">Amount (PHP)</Label>
            <div class="relative">
              <div class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-900/30 font-serif">₱</div>
              <Input
                v-model="formData.amount"
                type="number"
                step="0.01"
                min="0.01"
                required
                class="rounded-xl border-stone-200 pl-8 h-12 text-lg font-serif bg-white"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Description -->
          <div class="space-y-2">
            <Label class="text-[10px] font-bold uppercase tracking-widest text-emerald-800">Notes (Optional)</Label>
            <Textarea
              v-model="formData.description"
              rows="3"
              class="rounded-xl border-stone-200 bg-white"
              placeholder="Context for this entry..."
            />
          </div>

          <!-- Actions -->
          <DialogFooter class="sm:justify-between gap-4">
            <Button 
              type="button" 
              variant="ghost" 
              @click="closeModal"
              class="rounded-full text-stone-500 hover:text-rose-600 font-bold uppercase tracking-widest text-[10px] h-11 transition-all"
            >
              Discard Changes
            </Button>
            <Button 
              type="submit" 
              :disabled="!isFormValid"
              class="rounded-full bg-emerald-900 hover:bg-emerald-800 text-white px-8 transition-all h-11 font-bold uppercase tracking-widest text-[11px]"
            >
              {{ editingTransaction ? 'Preserve Changes' : 'Commit Entry' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Modals -->
    <RuleEvaluationModal ref="ruleEvaluationModal" />
    <CsvUploadModal 
      v-model:open="showImportModal" 
      @success="transactionStore.fetchTransactions" 
    />
  </div>
</template>

<style scoped>
.font-serif {
  font-family: 'Playfair Display', serif;
}
.font-inter {
  font-family: 'Inter', sans-serif;
}

/* Transitions for filter expansion */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

