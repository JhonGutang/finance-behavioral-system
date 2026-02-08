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
import { 
  AlertCircle, 
  Upload, 
  CheckCircle2, 
  AlertTriangle, 
  Loader2, 
  Download,
  FileSpreadsheet,
  ChevronLeft
} from 'lucide-vue-next';
import type { CsvImportItem } from '@/types';

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
    <DialogContent class="max-w-4xl max-h-[90vh] flex flex-col p-0 overflow-hidden bg-[#FDFCF8] border-emerald-100 rounded-[2.5rem] shadow-2xl">
      <!-- Header -->
      <DialogHeader class="p-8 pb-6 border-b border-emerald-50 relative">
        <DialogTitle class="text-3xl font-serif text-emerald-950 flex items-center gap-3">
          <div class="p-2.5 bg-emerald-50 rounded-2xl text-emerald-700">
            <FileSpreadsheet class="w-6 h-6" />
          </div>
          {{ step === 'upload' ? 'Data Integration' : 'Validation Protocol' }}
        </DialogTitle>
        <p class="text-emerald-900/50 font-light mt-1">
          {{ step === 'upload' ? 'Import your transaction ledger into the system.' : 'Review each entry before final commitment.' }}
        </p>
      </DialogHeader>

      <!-- Step 1: Upload -->
      <div v-if="step === 'upload'" class="p-8 flex flex-col space-y-8 flex-1 overflow-auto">
        <div 
          class="w-full border-2 border-dashed border-emerald-100/50 rounded-[2.5rem] p-12 flex flex-col items-center text-center transition-all cursor-pointer group hover:bg-emerald-50/30 hover:border-emerald-200"
          @click="fileInput?.click()"
        >
          <div class="w-20 h-20 bg-emerald-50 text-emerald-700 rounded-3xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
            <Upload class="w-10 h-10" />
          </div>
          <h3 class="text-2xl font-serif text-emerald-950 mb-2">Select your ledger file</h3>
          <p class="text-emerald-800 max-w-sm mb-8 font-light italic">
            Ensuring harmony between your external data and our system.
          </p>
          
          <div class="flex flex-col items-center gap-6">
            <input 
              type="file" 
              ref="fileInput" 
              class="hidden" 
              accept=".csv" 
              @change="handleFileChange" 
            />
            <Button 
              class="rounded-full bg-emerald-900 hover:bg-emerald-800 text-white px-10 h-11 shadow-lg shadow-emerald-900/10 transition-all font-bold uppercase tracking-widest text-[11px]"
              :disabled="isUploading"
            >
              <Loader2 v-if="isUploading" class="w-4 h-4 mr-2 animate-spin" />
              Upload .CSV
            </Button>

            <a 
              href="http://localhost:8000/samples/transactions_template.csv" 
              download 
              class="text-xs text-emerald-700 font-bold uppercase tracking-widest hover:underline flex items-center gap-2 py-2"
            >
              <Download class="w-4 h-4" />
              Download Standard Template
            </a>
          </div>
        </div>
        
        <div class="bg-emerald-50/50 p-6 rounded-3xl border border-emerald-100/30">
          <div class="flex gap-4">
            <div class="p-2 bg-white rounded-xl shadow-sm h-fit">
               <AlertCircle class="w-5 h-5 text-emerald-600" />
            </div>
            <div class="text-sm">
              <p class="font-bold text-emerald-950 uppercase tracking-tight mb-1">Architectural Note</p>
              <p class="text-emerald-900/60 font-light italic leading-relaxed">
                A single file may contain both Income and Expense vectors. Simply tag each row appropriately in the 'type' column for proper classification.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: Preview -->
      <div v-else class="flex-1 overflow-hidden flex flex-col">
        <div class="px-8 py-4 border-b border-emerald-50 bg-emerald-50/20 flex items-center justify-between">
          <div class="flex items-center gap-4">
             <button @click="step = 'upload'" class="p-2 hover:bg-emerald-50 rounded-full transition-colors text-emerald-950/40 hover:text-emerald-950">
                <ChevronLeft class="w-5 h-5" />
             </button>
             <p class="text-xs font-bold uppercase tracking-widest text-emerald-800">
                Identified <span class="text-emerald-950 font-black">{{ previewData.length }}</span> entries
             </p>
          </div>
          <div class="text-xs font-bold uppercase tracking-widest text-rose-800">
             Skipping <span class="text-rose-950 font-black">{{ previewData.filter((i: CsvImportItem) => i.skip).length }}</span> items
          </div>
        </div>

        <div class="flex-1 overflow-auto px-4">
          <table class="w-full text-left">
            <thead>
              <tr class="border-b border-emerald-50/50">
                <th class="px-6 py-4 text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Action</th>
                <th class="px-6 py-4 text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Details</th>
                <th class="px-6 py-4 text-right text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Value</th>
                <th class="px-6 py-4 text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Integrity</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-emerald-50/20">
              <tr 
                v-for="item in previewData" 
                :key="item.id"
                :class="[
                  'transition-all duration-300 group',
                  item.skip ? 'opacity-40 grayscale bg-stone-50/50' : 'hover:bg-white/80'
                ]"
              >
                <td class="px-6 py-4">
                   <div class="flex items-center justify-center">
                     <input 
                        type="checkbox"
                        :checked="!item.skip" 
                        @change="toggleSkip(item.id)"
                        class="w-5 h-5 rounded-lg border-emerald-100 text-emerald-700 focus:ring-emerald-500 cursor-pointer"
                      />
                   </div>
                </td>
                <td class="px-6 py-4">
                   <div>
                      <p class="text-sm font-medium text-emerald-950 mb-1">{{ item.data?.date }}</p>
                      <div class="flex items-center gap-2">
                         <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-tighter bg-emerald-50 text-emerald-700 border border-emerald-100/50">
                            {{ item.data?.category_name }}
                         </span>
                         <span class="text-[11px] text-emerald-800 italic font-medium truncate max-w-[150px]">
                            {{ item.data?.description || 'No description' }}
                         </span>
                      </div>
                   </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <span 
                    :class="[
                       'font-serif text-base font-semibold',
                       item.data?.type === 'income' ? 'text-emerald-700' : 'text-rose-700'
                    ]"
                  >
                    {{ item.data?.type === 'income' ? '+' : '-' }}₱{{ item.data?.amount.toLocaleString() }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div v-if="item.is_duplicate" class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 rounded-full border border-amber-100">
                    <AlertTriangle class="w-3.5 h-3.5" />
                    <span class="text-[9px] font-black uppercase tracking-widest">Duplicate</span>
                  </div>
                  <div v-else-if="item.status === 'valid'" class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-100">
                    <CheckCircle2 class="w-3.5 h-3.5" />
                    <span class="text-[9px] font-black uppercase tracking-widest">Pristine</span>
                  </div>
                  <div v-else class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 rounded-full border border-rose-100" :title="item.message">
                    <AlertCircle class="w-3.5 h-3.5" />
                    <span class="text-[9px] font-black uppercase tracking-widest">Error</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <DialogFooter class="p-8 border-t border-emerald-50 bg-white/40 sm:justify-between items-center gap-6">
          <Button 
            variant="ghost" 
            @click="closeModal"
            class="rounded-full text-stone-500 hover:text-rose-700 font-bold uppercase tracking-widest text-[10px] h-11 transition-all"
          >
            Abort Integration
          </Button>
          <Button 
            @click="handleConfirm" 
            :disabled="isConfirming || previewData.every((i: CsvImportItem) => i.skip)"
            class="rounded-full bg-emerald-900 hover:bg-emerald-800 text-white px-12 h-11 shadow-lg shadow-emerald-900/10 transition-all font-bold uppercase tracking-widest text-[11px]"
          >
            <Loader2 v-if="isConfirming" class="w-4 h-4 mr-2 animate-spin" />
            {{ isConfirming ? 'Processing' : 'Commit to history' }}
          </Button>
        </DialogFooter>
      </div>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
.font-serif {
  font-family: 'Playfair Display', serif;
}
.font-inter {
  font-family: 'Inter', sans-serif;
}
/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 4px;
}
::-webkit-scrollbar-thumb {
  background: rgba(16, 185, 129, 0.1);
  border-radius: 10px;
}
</style>
