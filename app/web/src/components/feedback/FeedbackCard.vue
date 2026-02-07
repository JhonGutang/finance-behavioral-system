<script setup lang="ts">
import { computed } from 'vue';
import type { FeedbackHistory } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
  Lightbulb, 
  TrendingUp, 
  Zap, 
  CheckCircle2,
  AlertCircle,
  ArrowRight
} from 'lucide-vue-next';

const props = defineProps<{
  feedback: FeedbackHistory;
}>();

const emit = defineEmits<{
  (e: 'acknowledge', id: number): void;
}>();

const iconComponent = computed(() => {
  switch (props.feedback.rule_id) {
    case 'category_overspend': return TrendingUp;
    case 'weekly_spending_spike': return Zap;
    case 'frequent_small_purchases': return Lightbulb;
    default: return AlertCircle;
  }
});

const statusColorClass = computed(() => {
  if (props.feedback.user_acknowledged) return 'bg-zinc-200';
  switch (props.feedback.rule_id) {
    case 'category_overspend': return 'bg-amber-500';
    case 'weekly_spending_spike': return 'bg-rose-500';
    case 'frequent_small_purchases': return 'bg-teal-500';
    default: return 'bg-emerald-500';
  }
});

const iconColorClass = computed(() => {
    if (props.feedback.user_acknowledged) return 'text-zinc-400';
    switch (props.feedback.rule_id) {
      case 'category_overspend': return 'text-amber-600';
      case 'weekly_spending_spike': return 'text-rose-600';
      case 'frequent_small_purchases': return 'text-teal-600';
      default: return 'text-emerald-600';
    }
});

function handleAcknowledge() {
  if (!props.feedback.user_acknowledged) {
    emit('acknowledge', props.feedback.id);
  }
}
</script>

<template>
  <Card 
    :class="[
      'group relative h-full flex flex-col overflow-hidden transition-all duration-500 border-zinc-200/60 bg-white shadow-sm hover:shadow-md hover:border-zinc-300',
      feedback.user_acknowledged ? 'opacity-70 grayscale-[0.1]' : 'hover:-translate-y-1'
    ]"
  >
    <!-- Top Status Line -->
    <div :class="['absolute top-0 left-0 right-0 h-1 transition-colors duration-300', statusColorClass]" />

    <CardContent class="p-6 flex flex-col h-full">
      
      <!-- Header -->
      <div class="flex items-start justify-between mb-5">
        <div class="flex items-center gap-3">
          <div :class="['p-2 rounded-lg bg-zinc-50 border border-zinc-100 group-hover:bg-white group-hover:shadow-sm transition-all duration-300', feedback.user_acknowledged ? 'opacity-50' : '']">
            <component :is="iconComponent" :class="['w-4 h-4', iconColorClass]" />
          </div>
          <span v-if="feedback.level === 'advanced'" class="px-2 py-0.5 rounded-full bg-violet-50 text-violet-700 text-[10px] font-bold uppercase tracking-wider border border-violet-100">
            Advanced
          </span>
        </div>
        
        <span class="text-[10px] font-medium uppercase tracking-widest text-zinc-400">
           {{ feedback.rule_id.replace(/_/g, ' ') }}
        </span>
      </div>

      <!-- Content body -->
      <div class="flex-grow space-y-4">
        <h3 class="text-lg font-serif font-medium text-zinc-900 leading-snug group-hover:text-emerald-950 transition-colors">
          {{ feedback.explanation }}
        </h3>
        
        <p class="text-sm text-zinc-500 leading-relaxed font-light border-l-2 border-zinc-100 pl-4 py-1">
          {{ feedback.suggestion }}
        </p>
      </div>

      <!-- Action Footer -->
      <div class="mt-6 pt-0 flex items-center justify-end">
        <Button 
            v-if="!feedback.user_acknowledged"
            variant="ghost" 
            size="sm" 
            class="h-8 text-xs font-semibold px-0 text-emerald-600 hover:text-emerald-700 hover:bg-transparent group/btn"
            @click="handleAcknowledge"
        >
            <span class="mr-2">I'll keep this in mind</span>
            <ArrowRight class="w-3.5 h-3.5 transition-transform duration-300 group-hover/btn:translate-x-1" />
        </Button>
        
        <div v-else class="flex items-center text-zinc-400 text-xs font-medium">
             <CheckCircle2 class="w-3.5 h-3.5 mr-1.5" />
             <span>Acknowledged</span>
        </div>
      </div>

    </CardContent>
  </Card>
</template>

