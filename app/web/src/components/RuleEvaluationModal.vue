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
  AlertTriangle, 
  CheckCircle2, 
  TrendingUp, 
  ArrowUpRight, 
  Coffee,
  Loader2,
  Sparkles
} from 'lucide-vue-next';
import { ruleEngineApi } from '@/services/api.service';
import type { RuleEvaluation } from '@/types';

const isOpen = ref(false);
const loading = ref(false);
const results = ref<RuleEvaluation | null>(null);
const error = ref<string | null>(null);

async function runEvaluation() {
  isOpen.value = true;
  loading.value = true;
  error.value = null;
  results.value = null;

  try {
    // For demo purposes, we can pass Jan 20, 2026 if it's the demo user
    // In a real app, this would use the current user's current date
    const response = await ruleEngineApi.evaluate();
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
    case 'category_overspend': return 'text-orange-600 bg-orange-50 ring-orange-100';
    case 'weekly_spending_spike': return 'text-red-600 bg-red-50 ring-red-100';
    case 'frequent_small_purchases': return 'text-amber-600 bg-amber-50 ring-amber-100';
    default: return 'text-slate-600 bg-slate-50 ring-slate-100';
  }
}
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-[600px] overflow-hidden p-0">
      <div class="relative overflow-hidden">
        <!-- Background Accents -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/5 rounded-full blur-3xl" />
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-purple-500/5 rounded-full blur-3xl" />

        <div class="p-6 relative">
          <DialogHeader class="mb-6">
            <div class="flex items-center gap-2 mb-2">
              <Sparkles class="w-5 h-5 text-blue-600" />
              <Badge variant="secondary" class="bg-blue-100 text-blue-700 hover:bg-blue-100 border-none">
                Behavioral Analysis
              </Badge>
            </div>
            <DialogTitle class="text-2xl font-bold">Financial Habit Analysis</DialogTitle>
            <DialogDescription>
              We've analyzed your spending patterns for the current week compared to your baseline.
            </DialogDescription>
          </DialogHeader>

          <!-- Loading State -->
          <div v-if="loading" class="py-12 flex flex-col items-center justify-center space-y-4">
            <Loader2 class="w-12 h-12 text-blue-600 animate-spin" />
            <div class="text-center">
              <p class="font-semibold text-slate-900">Running Heuristic Rules...</p>
              <p class="text-sm text-slate-500">Detecting spending drift and frequency patterns</p>
            </div>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="py-8 text-center space-y-4">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto">
              <AlertTriangle class="w-6 h-6" />
            </div>
            <p class="text-slate-800 font-medium">{{ error }}</p>
            <Button @click="runEvaluation">Try Again</Button>
          </div>

          <!-- Results State -->
          <div v-else-if="results" class="space-y-4">
            <div v-if="results.triggered_rules.length === 0" class="py-12 text-center space-y-4">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto">
                  <CheckCircle2 class="w-8 h-8" />
                </div>
                <div>
                  <h3 class="text-lg font-bold text-slate-900">All habits look stable!</h3>
                  <p class="text-slate-500 max-w-sm mx-auto">No negative spending patterns were detected for this week. Great job maintaining your baseline!</p>
                </div>
            </div>

            <div v-else class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
              <Card 
                v-for="rule in results.triggered_rules" 
                :key="rule.rule_id"
                class="border-slate-200 overflow-hidden"
              >
                <div class="flex">
                  <div 
                    :class="[
                      'w-1.5 shrink-0',
                      rule.rule_id === 'category_overspend' ? 'bg-orange-500' : 
                      rule.rule_id === 'weekly_spending_spike' ? 'bg-red-500' : 'bg-amber-500'
                    ]" 
                  />
                  <div class="p-4 flex-1">
                    <div class="flex items-start justify-between gap-4 mb-2">
                      <div class="flex items-center gap-2">
                        <component 
                          :is="getRuleIcon(rule.rule_id)" 
                          class="w-5 h-5 text-slate-700"
                        />
                        <h3 class="font-bold text-slate-900">{{ getRuleTitle(rule.rule_id) }}</h3>
                      </div>
                      <Badge 
                        variant="outline" 
                        :class="[getRuleColor(rule.rule_id), 'border-none ring-1']"
                      >
                        Action Needed
                      </Badge>
                    </div>

                    <div class="text-sm text-slate-600 space-y-2">
                      <p v-if="rule.rule_id === 'category_overspend'">
                        Analysis detected a <span class="font-bold text-slate-900">{{ rule.data.increase_percentage }}%</span> jump in 
                        <span class="font-bold text-slate-900">{{ rule.data.category }}</span> spending 
                        compared to last week (${{ rule.data.previous_week_amount }} &rarr; ${{ rule.data.current_week_amount }}).
                      </p>
                      
                      <p v-else-if="rule.rule_id === 'weekly_spending_spike'">
                        Your overall burn rate is up <span class="font-bold text-slate-900">{{ rule.data.increase_percentage }}%</span> 
                        from last week's total of ${{ rule.data.previous_week_total }}.
                      </p>

                      <p v-else-if="rule.rule_id === 'frequent_small_purchases'">
                        You've made <span class="font-bold text-slate-900">{{ rule.data.transaction_count }}</span> small purchases 
                        (under ${{ rule.data.amount_limit }}) this week, averaging ${{ rule.data.average_amount }} each.
                      </p>

                      <div class="pt-2 mt-2 border-t border-slate-100">
                        <p class="text-[12px] text-slate-400 italic">
                          Why this matters: {{ 
                            rule.rule_id === 'category_overspend' ? 'Sudden category increases often indicate "spending creep" where temporary treats become permanent habits.' :
                            rule.rule_id === 'weekly_spending_spike' ? 'Large spikes in total spending can derail long-term savings goals if not actively monitored.' :
                            'Frequent small transactions are often mindless impulse buys that add up to significant leakage over time.'
                          }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </Card>
            </div>
          </div>

          <DialogFooter class="mt-8">
            <Button variant="outline" @click="isOpen = false">Close</Button>
            <Button v-if="results && results.triggered_rules.length > 0" class="bg-blue-600 hover:bg-blue-700">
              Create Savings Plan
            </Button>
          </DialogFooter>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>
