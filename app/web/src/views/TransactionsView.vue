<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useTransactionStore } from '../stores/transactionStore';
import { useCategoryStore } from '../stores/categoryStore';
import { Plus, Pencil, Trash2, Sparkles } from 'lucide-vue-next';
import type { Transaction, TransactionFormData } from '../types';
import RuleEvaluationModal from '@/components/RuleEvaluationModal.vue';
import { formatDateSafe } from '@/utils';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/components/ui/dialog';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
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

const showModal = ref(false);
const editingTransaction = ref<Transaction | null>(null);
const formData = ref<TransactionFormData>({
  type: 'expense',
  amount: '',
  date: new Date().toISOString().split('T')[0]!,
  category_id: '',
  description: undefined,
});

const ruleEvaluationModal = ref<InstanceType<typeof RuleEvaluationModal> | null>(null);

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

function openRuleEvaluation() {
  ruleEvaluationModal.value?.open();
}

/**
 * Formats a date string to "MMM D, YYYY" format
 * Handles both YYYY-MM-DD and M/D/YYYY formats from backend
 */
function formatTransactionDate(dateString: string): string {
  // Convert YYYY-MM-DD to M/D/YYYY if needed
  if (dateString.includes('-')) {
    const [year, month, day] = dateString.split('-');
    const formattedInput = `${parseInt(month!)}/${parseInt(day!)}/${year}`;
    return formatDateSafe(formattedInput) || dateString;
  }
  return formatDateSafe(dateString) || dateString;
}

onMounted(async () => {
  await Promise.all([
    transactionStore.fetchTransactions(),
    categoryStore.fetchCategories(),
  ]);
});
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-slate-900">Transactions</h1>
        <p class="text-slate-600 mt-1">Manage your income and expenses</p>
      </div>
      <div class="flex items-center gap-3">
        <Button variant="outline" @click="openRuleEvaluation" class="gap-2 border-blue-200 text-blue-700 hover:bg-blue-50 hover:text-blue-800">
          <Sparkles class="w-4 h-4" />
          Evaluate
        </Button>
        <Button @click="openCreateModal" class="gap-2">
          <Plus class="w-5 h-5" />
          Add Transaction
        </Button>
      </div>
    </div>

    <!-- Transactions List -->
    <Card>
      <CardContent class="p-0">
        <div v-if="transactionStore.loading" class="p-12 text-center">
          <p class="text-slate-500">Loading transactions...</p>
        </div>
        
        <div v-else-if="transactionStore.transactions.length === 0" class="p-12 text-center">
          <p class="text-slate-500 mb-4">No transactions yet</p>
          <Button variant="link" @click="openCreateModal">
            Add your first transaction
          </Button>
        </div>

        <div v-else class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Date</TableHead>
                <TableHead>Category</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Type</TableHead>
                <TableHead class="text-right">Amount</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow 
                v-for="transaction in transactionStore.transactions" 
                :key="transaction.id"
              >
                <TableCell class="font-medium">
                  {{ formatTransactionDate(transaction.date) }}
                </TableCell>
                <TableCell>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                    {{ transaction.category?.name }}
                  </span>
                </TableCell>
                <TableCell class="text-slate-600">
                  {{ transaction.description || '-' }}
                </TableCell>
                <TableCell>
                  <span 
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      transaction.type === 'income' 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ transaction.type }}
                  </span>
                </TableCell>
                <TableCell class="text-right">
                  <span 
                    :class="[
                      'font-semibold',
                      transaction.type === 'income' ? 'text-green-600' : 'text-red-600'
                    ]"
                  >
                    {{ transaction.type === 'income' ? '+' : '-' }}${{ transaction.amount }}
                  </span>
                </TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                    <Button
                      variant="ghost"
                      size="icon"
                      @click="openEditModal(transaction)"
                    >
                      <Pencil class="w-4 h-4" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      @click="handleDelete(transaction.id)"
                      class="text-red-600 hover:text-red-800"
                    >
                      <Trash2 class="w-4 h-4" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>

    <!-- Modal -->
    <Dialog v-model:open="showModal">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>
            {{ editingTransaction ? 'Edit Transaction' : 'Add Transaction' }}
          </DialogTitle>
        </DialogHeader>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Type -->
          <div class="space-y-2">
            <Label>Type</Label>
            <div class="grid grid-cols-2 gap-2">
              <Button
                type="button"
                :variant="formData.type === 'income' ? 'default' : 'outline'"
                @click="formData.type = 'income'; formData.category_id = ''"
                class="w-full"
                :class="formData.type === 'income' ? 'bg-green-600 hover:bg-green-700' : ''"
              >
                Income
              </Button>
              <Button
                type="button"
                :variant="formData.type === 'expense' ? 'default' : 'outline'"
                @click="formData.type = 'expense'; formData.category_id = ''"
                class="w-full"
                :class="formData.type === 'expense' ? 'bg-red-600 hover:bg-red-700' : ''"
              >
                Expense
              </Button>
            </div>
          </div>

          <!-- Category -->
          <div class="space-y-2">
            <Label for="category">Category</Label>
            <Select v-model:model-value="formData.category_id" required>
              <SelectTrigger id="category">
                <SelectValue placeholder="Select a category" />
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

          <!-- Amount -->
          <div class="space-y-2">
            <Label for="amount">Amount</Label>
            <Input
              id="amount"
              v-model="formData.amount"
              type="number"
              step="0.01"
              min="0.01"
              required
              placeholder="0.00"
            />
          </div>

          <!-- Date -->
          <div class="space-y-2">
            <Label for="date">Date</Label>
            <Input
              id="date"
              v-model="formData.date"
              type="date"
              required
              :max="new Date().toISOString().split('T')[0]"
            />
          </div>

          <!-- Description -->
          <div class="space-y-2">
            <Label for="description">Description (Optional)</Label>
            <Textarea
              id="description"
              v-model="formData.description"
              rows="3"
              placeholder="Add a note..."
            />
          </div>

          <!-- Actions -->
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeModal">
              Cancel
            </Button>
            <Button type="submit" :disabled="!isFormValid">
              {{ editingTransaction ? 'Update' : 'Create' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Rule Evaluation Modal -->
    <RuleEvaluationModal ref="ruleEvaluationModal" />
  </div>
</template>
