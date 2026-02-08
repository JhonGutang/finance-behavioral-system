<script setup lang="ts">
import { ref } from 'vue';
import { 
  Dialog, 
  DialogContent, 
  DialogHeader, 
  DialogTitle, 
  DialogDescription,
  DialogFooter
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
  Sparkles, 
  CheckCircle2, 
  TrendingUp,
  ArrowUpRight,
  Coffee,
  AlertTriangle,
  ArrowRight
} from 'lucide-vue-next';
import { useFeedbackStore } from '@/stores/feedbackStore';
import { useRouter } from 'vue-router';
import type { EvaluationResponse } from '@/types';

const isOpen = ref(false);
const loading = ref(false);
const results = ref<EvaluationResponse | null>(null);
const error = ref<string | null>(null);

const feedbackStore = useFeedbackStore();
const router = useRouter();

async function runEvaluation() {
  isOpen.value = true;
  loading.value = true;
  error.value = null;
  results.value = null;

  try {
    const response = await feedbackStore.evaluateRules();
    results.value = response;
  } catch (err) {
    error.value = 'Failed to analyze spending behavior. Please try again.';
    console.error(err);
  } finally {
    loading.value = false;
  }
}

defineExpose({ open: runEvaluation });

function getRuleTitle(ruleId: string) {
  switch (ruleId) {
    case 'category_overspend': return 'Category Spending Drift';
    case 'weekly_spending_spike': return 'Weekly Burn Rate Spike';
    case 'frequent_small_purchases': return 'Frequent Small Purchases';
    default: return 'Behavioral Alert';
  }
}

function getRuleIcon(ruleId: string) {
  switch (ruleId) {
    case 'category_overspend': return TrendingUp;
    case 'weekly_spending_spike': return ArrowUpRight;
    case 'frequent_small_purchases': return Coffee;
    default: return AlertTriangle;
  }
}

function getRuleColor(ruleId: string) {
  switch (ruleId) {
    case 'category_overspend': return 'text-amber-700 bg-amber-50 border-amber-100';
    case 'weekly_spending_spike': return 'text-rose-700 bg-rose-50 border-rose-100';
    case 'frequent_small_purchases': return 'text-emerald-700 bg-emerald-50 border-emerald-100';
    default: return 'text-stone-600 bg-stone-50 border-stone-100';
  }
}

function navigateToInsights() {
  isOpen.value = false;
  router.push('/insights');
}
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-[620px] overflow-hidden p-0 bg-[#FDFCF8] border-emerald-100 rounded-[2.5rem] shadow-2xl">
      <div class="relative overflow-hidden">
        <!-- Background Grain -->
        <div class="fixed inset-0 pointer-events-none opacity-20 mix-blend-multiply -z-10">
          <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-30"></div>
        </div>

        <div class="p-8 relative">
          <DialogHeader class="mb-8">
            <div class="flex items-center gap-3 mb-4">
              <div class="p-2 bg-emerald-100 text-emerald-700 rounded-xl">
                 <Sparkles class="w-5 h-5" />
              </div>
              <Badge variant="secondary" class="bg-emerald-50 text-emerald-700 hover:bg-emerald-50 border-emerald-100 font-bold tracking-widest text-[10px] uppercase">
                Heuristic Engine
              </Badge>
            </div>
            <DialogTitle class="text-3xl font-serif text-emerald-950">Behavioral Harvest</DialogTitle>
            <DialogDescription class="text-emerald-900/60 font-light text-base mt-2">
              Our sensory arrays have processed your recent activity. Here is what we've unearthed.
            </DialogDescription>
          </DialogHeader>

          <!-- Loading State -->
          <div v-if="loading" class="py-16 flex flex-col items-center justify-center space-y-6">
            <div class="relative">
               <div class="w-20 h-20 rounded-full border-2 border-emerald-100 border-t-emerald-600 animate-spin"></div>
               <div class="absolute inset-0 flex items-center justify-center">
                  <Sparkles class="w-6 h-6 text-emerald-600 animate-pulse" />
               </div>
            </div>
            <div class="text-center">
              <p class="font-serif text-xl text-emerald-950 italic">Analyzing spending drift...</p>
              <p class="text-xs uppercase tracking-[0.2em] font-bold text-emerald-900/30 mt-2">Connecting patterns to baseline</p>
            </div>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="py-12 text-center space-y-6">
            <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center mx-auto border border-rose-100">
              <AlertTriangle class="w-8 h-8" />
            </div>
            <p class="text-emerald-950 font-serif text-xl">{{ error }}</p>
            <Button @click="runEvaluation" class="rounded-full bg-emerald-900 hover:bg-emerald-800 text-white px-8 h-11 font-bold uppercase tracking-widest text-[11px] shadow-lg shadow-emerald-900/10 transition-all">Try Again</Button>
          </div>

          <!-- Results State -->
          <div v-else-if="results" class="space-y-6">
            <div v-if="results.evaluation.triggered_rules.length === 0" class="py-12 text-center space-y-6">
                <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto border border-emerald-100 shadow-sm">
                  <CheckCircle2 class="w-10 h-10" />
                </div>
                <div>
                  <h3 class="text-2xl font-serif text-emerald-950 mb-2">Pristine Discipline</h3>
                  <p class="text-emerald-900/50 max-w-sm mx-auto font-light leading-relaxed">No anomalies detected in your spending geometry this week. Your financial garden is in perfect balance.</p>
                </div>
            </div>

            <div v-else class="space-y-4 max-h-[400px] overflow-y-auto pr-3 custom-scrollbar">
              <Card 
                v-for="rule in results.evaluation.triggered_rules" 
                :key="rule.rule_id"
                class="border-emerald-50 bg-white/60 backdrop-blur-sm overflow-hidden rounded-3xl group transition-all hover:bg-white"
              >
                <div class="flex">
                  <div 
                    :class="[
                      'w-1.5 shrink-0',
                      rule.rule_id === 'category_overspend' ? 'bg-amber-500' : 
                      rule.rule_id === 'weekly_spending_spike' ? 'bg-rose-500' : 'bg-emerald-500'
                    ]" 
                  />
                  <div class="p-5 flex-1">
                    <div class="flex items-start justify-between gap-4 mb-3">
                      <div class="flex items-center gap-3">
                        <div class="p-2 bg-stone-50 rounded-xl border border-stone-100 group-hover:bg-white transition-colors">
                           <component 
                            :is="getRuleIcon(rule.rule_id)" 
                            class="w-5 h-5 text-emerald-900"
                          />
                        </div>
                        <h3 class="font-serif text-lg text-emerald-950 font-medium">{{ getRuleTitle(rule.rule_id) }}</h3>
                      </div>
                      <Badge 
                        variant="outline" 
                        :class="[getRuleColor(rule.rule_id), 'rounded-full border-none ring-1 px-3 py-0.5 text-[9px] font-black uppercase tracking-widest']"
                      >
                        Action Recommended
                      </Badge>
                    </div>

                    <div class="text-sm text-emerald-900/70 space-y-3 leading-relaxed">
                      <p v-if="rule.rule_id === 'category_overspend'" class="font-light">
                        Analyzed a <span class="font-bold text-emerald-950">{{ rule.data.increase_percentage }}%</span> surge in 
                        <span class="font-bold text-emerald-950">{{ rule.data.category }}</span> 
                        compared to prior week baseline (₱{{ rule.data.previous_week_amount.toLocaleString() }} &rarr; ₱{{ rule.data.current_week_amount.toLocaleString() }}).
                      </p>
                      
                      <p v-else-if="rule.rule_id === 'weekly_spending_spike'" class="font-light">
                        Overall burn rate has intensified by <span class="font-bold text-emerald-950">{{ rule.data.increase_percentage }}%</span> 
                        from previous total of ₱{{ rule.data.previous_week_total.toLocaleString() }}.
                      </p>

                      <p v-else-if="rule.rule_id === 'frequent_small_purchases'" class="font-light">
                        Identity confirmed: <span class="font-bold text-emerald-950">{{ rule.data.transaction_count }}</span> micro-transactions 
                        (under ₱{{ rule.data.amount_limit }}) detected, averaging ₱{{ rule.data.average_amount.toLocaleString() }} each.
                      </p>

                      <div class="pt-3 mt-3 border-t border-emerald-50">
                        <p class="text-[11px] text-emerald-800 italic font-bold flex items-start gap-2">
                          <span class="font-black shrink-0 uppercase tracking-widest">Context:</span>
                          <span>{{ 
                            rule.rule_id === 'category_overspend' ? 'Sudden category increases often indicate "spending creep" where temporary treats become permanent habits.' :
                            rule.rule_id === 'weekly_spending_spike' ? 'Large spikes in total spending can derail long-term savings goals if not actively monitored.' :
                            'Frequent small transactions are often mindless impulse buys that add up to significant leakage over time.'
                          }}</span>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </Card>
            </div>
          </div>

          <DialogFooter class="mt-10 sm:justify-between items-center gap-6 pt-6 border-t border-emerald-50">
            <Button variant="ghost" @click="isOpen = false" class="rounded-full text-stone-500 hover:text-emerald-950 font-bold uppercase tracking-widest text-[10px] h-11 transition-all">
              Close Report
            </Button>
            <Button 
              v-if="results" 
              @click="navigateToInsights"
              class="rounded-full bg-emerald-900 hover:bg-emerald-800 text-white px-10 h-11 shadow-lg shadow-emerald-900/10 transition-all font-bold uppercase tracking-widest text-[11px] group"
            >
              Analyze Deeper
              <ArrowRight class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
            </Button>
          </DialogFooter>
        </div>
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
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(16, 185, 129, 0.1);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(16, 185, 129, 0.2);
}
</style>
