<script setup lang="ts">
import { ref } from 'vue';
import { useTransactionStore } from '@/stores/transactionStore';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { AlertCircle, Upload, CheckCircle2, AlertTriangle, Loader2, Download } from 'lucide-vue-next';
import type { CsvImportItem } from '@/types';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table';

const props = defineProps<{
  open: boolean;
}>();

const emit = defineEmits(['update:open', 'success']);

const transactionStore = useTransactionStore();
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const isUploading = ref(false);
const isConfirming = ref(false);
const previewData = ref<CsvImportItem[]>([]);
const step = ref<'upload' | 'preview'>('upload');

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0];
    uploadFile();
  }
}

async function uploadFile() {
  if (!selectedFile.value) return;

  isUploading.value = true;
  try {
    previewData.value = await transactionStore.importPreview(selectedFile.value);
    // Auto-skip duplicates by default
    previewData.value.forEach((item: CsvImportItem) => {
      if (item.is_duplicate) item.skip = true;
    });
    step.value = 'preview';
  } catch (error) {
    console.error('Upload failed:', error);
  } finally {
    isUploading.value = false;
  }
}

async function handleConfirm() {
  isConfirming.value = true;
  try {
    const itemsToImport = previewData.value.map((item: CsvImportItem) => ({
      data: item.data,
      skip: item.skip || item.status === 'error',
    }));

    await transactionStore.importConfirm(itemsToImport);
    emit('success');
    closeModal();
  } catch (error) {
    console.error('Confirmation failed:', error);
  } finally {
    isConfirming.value = false;
  }
}

function closeModal() {
  emit('update:open', false);
  // Reset state after a delay to allow the close animation to finish
  setTimeout(() => {
    step.value = 'upload';
    selectedFile.value = null;
    previewData.value = [];
  }, 300);
}

function toggleSkip(id: number) {
  const item = previewData.value.find((i: CsvImportItem) => i.id === id);
  if (item) {
    item.skip = !item.skip;
  }
}
</script>

<template>
  <Dialog :open="open" @update:open="closeModal">
    <DialogContent class="max-w-4xl max-h-[90vh] flex flex-col p-0 overflow-hidden">
      <DialogHeader class="p-6 border-b">
        <DialogTitle class="flex items-center gap-2">
          <Upload class="w-5 h-5 text-primary" />
          {{ step === 'upload' ? 'Import Transactions' : 'Review Imports' }}
        </DialogTitle>
      </DialogHeader>

      <!-- Step 1: Upload -->
      <div v-if="step === 'upload'" class="p-10 flex flex-col items-center justify-center space-y-6">
        <div 
          class="w-full max-w-lg border-2 border-dashed border-slate-200 rounded-3xl p-12 flex flex-col items-center text-center hover:border-primary/50 hover:bg-slate-50 transition-all cursor-pointer group"
          @click="fileInput?.click()"
        >
          <div class="bg-primary/10 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
            <Upload class="w-8 h-8 text-primary" />
          </div>
          <h3 class="text-xl font-bold text-slate-900">Choose a CSV file</h3>
          <p class="text-slate-500 mt-2">
            Make sure your file follows the required format.
          </p>
          <div class="mt-4 flex flex-col items-center gap-4">
            <a 
              href="http://localhost:8000/samples/transactions_template.csv" 
              download 
              class="text-sm text-primary font-semibold hover:underline flex items-center gap-1.5"
            >
              <Download class="w-4 h-4" />
              Download Sample CSV
            </a>
            
            <input 
              type="file" 
              ref="fileInput" 
              class="hidden" 
              accept=".csv" 
              @change="handleFileChange" 
            />
            <Button class="mt-4" :disabled="isUploading">
              <Loader2 v-if="isUploading" class="w-4 h-4 mr-2 animate-spin" />
              Select File
            </Button>
          </div>
        </div>
        
        <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100 max-w-lg w-full">
          <div class="flex gap-3">
            <AlertCircle class="w-5 h-5 text-blue-500 shrink-0" />
            <div class="text-sm text-blue-700">
              <p class="font-bold">Pro Tip</p>
              <p>You can import both Income and Expense transactions in a single file by specifying the 'type' column.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: Preview -->
      <div v-else class="flex-1 overflow-hidden flex flex-col">
        <div class="p-4 border-b bg-slate-50 flex items-center justify-between">
          <p class="text-sm text-slate-600">
            Found <strong>{{ previewData.length }}</strong> rows. 
            Skipping <strong>{{ previewData.filter((i: CsvImportItem) => i.skip).length }}</strong> items.
          </p>
          <div class="flex gap-2">
            <Button variant="outline" size="sm" @click="step = 'upload'">
              Back
            </Button>
          </div>
        </div>

        <div class="flex-1 overflow-auto">
          <Table>
            <TableHeader class="sticky top-0 bg-white z-10">
              <TableRow>
                <TableHead class="w-12"></TableHead>
                <TableHead>Date</TableHead>
                <TableHead>Category</TableHead>
                <TableHead>Description</TableHead>
                <TableHead class="text-right">Amount</TableHead>
                <TableHead>Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow 
                v-for="item in previewData" 
                :key="item.id"
                :class="{ 'opacity-50 grayscale bg-slate-50': item.skip }"
              >
                <TableCell>
                  <input 
                    type="checkbox"
                    :checked="!item.skip" 
                    @change="toggleSkip(item.id)"
                    class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary"
                  />
                </TableCell>
                <TableCell class="whitespace-nowrap font-medium">{{ item.data?.date }}</TableCell>
                <TableCell>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                    {{ item.data?.category_name }}
                  </span>
                </TableCell>
                <TableCell class="max-w-[200px] truncate text-slate-500">
                  {{ item.data?.description || '-' }}
                </TableCell>
                <TableCell class="text-right whitespace-nowrap">
                  <span :class="item.data?.type === 'income' ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">
                    {{ item.data?.type === 'income' ? '+' : '-' }}${{ item.data?.amount }}
                  </span>
                </TableCell>
                <TableCell>
                  <div v-if="item.is_duplicate" class="flex items-center gap-1.5 text-orange-600">
                    <AlertTriangle class="w-3.5 h-3.5" />
                    <span class="text-[10px] font-bold uppercase">Duplicate</span>
                  </div>
                  <div v-else-if="item.status === 'valid'" class="flex items-center gap-1.5 text-green-600">
                    <CheckCircle2 class="w-3.5 h-3.5" />
                    <span class="text-[10px] font-bold uppercase">Ready</span>
                  </div>
                  <div v-else class="flex items-center gap-1.5 text-red-600" :title="item.message">
                    <AlertCircle class="w-3.5 h-3.5" />
                    <span class="text-[10px] font-bold uppercase whitespace-nowrap">Error</span>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <DialogFooter class="p-6 border-t bg-slate-50">
          <Button variant="outline" @click="closeModal">Cancel</Button>
          <Button 
            @click="handleConfirm" 
            :disabled="isConfirming || previewData.every((i: CsvImportItem) => i.skip)"
            class="min-w-[120px]"
          >
            <Loader2 v-if="isConfirming" class="w-4 h-4 mr-2 animate-spin" />
            Import Selected
          </Button>
        </DialogFooter>
      </div>
    </DialogContent>
  </Dialog>
</template>
