<script setup lang="ts">
import { onMounted } from 'vue';
import { useFeedbackStore } from '@/stores/feedbackStore';
import FeedbackCard from './FeedbackCard.vue';
import { Card, CardContent } from '@/components/ui/card';
import { BrainCircuit, Trophy, Info } from 'lucide-vue-next';

const feedbackStore = useFeedbackStore();

onMounted(async () => {
  if (!feedbackStore.isFeedbackFetched || !feedbackStore.isProgressFetched) {
    await Promise.all([
      feedbackStore.fetchFeedback(),
      feedbackStore.fetchProgress()
    ]);
  } else {
    // Background refresh
    feedbackStore.fetchFeedback();
    feedbackStore.fetchProgress();
  }
  
  // If no feedback yet, try evaluating (for demo purposes)
  if (feedbackStore.feedbackHistory.length === 0) {
    await feedbackStore.evaluateRules();
  }
});

function getScoreColor(score: number) {
  if (score >= 80) return 'text-emerald-700';
  if (score >= 50) return 'text-teal-600';
  return 'text-amber-600';
}
</script>

<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-950 flex items-center justify-center text-white shadow-sm ring-1 ring-emerald-950/5">
            <BrainCircuit class="w-5 h-5" />
        </div>
        <div>
           <h2 class="text-xl font-serif font-medium text-emerald-950">Behavioral Insights</h2>
           <p class="text-xs text-emerald-800/60 font-sans">AI-driven financial pattern detection</p>
        </div>
      </div>
      
      <div v-if="feedbackStore.currentProgress" class="flex items-center bg-white px-4 py-2 rounded-xl shadow-sm border border-zinc-200/60">
        <div class="flex flex-col items-end mr-4">
          <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Improvement Score</span>
          <span :class="['text-xl font-serif font-medium tabular-nums', getScoreColor(feedbackStore.currentProgress.improvement_score)]">
            {{ feedbackStore.currentProgress.improvement_score }}%
          </span>
        </div>
        <div class="p-2 rounded-lg bg-amber-50 border border-amber-100">
          <Trophy class="w-5 h-5 text-amber-600" />
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <Card v-if="feedbackStore.feedbackHistory.length === 0 && !feedbackStore.loading" class="border-2 border-dashed border-zinc-200 bg-zinc-50/50">
      <CardContent class="py-16 flex flex-col items-center text-center">
        <div class="bg-white p-4 rounded-full mb-4 shadow-sm border border-zinc-100">
          <Info class="w-8 h-8 text-zinc-400" />
        </div>
        <h3 class="text-lg font-serif text-zinc-900">Establishing Baseline</h3>
        <p class="text-zinc-500 max-w-xs mt-2 font-light text-sm leading-relaxed">
          Keep tracking your transactions. Once we have a week of data, your personalized behavioral insights will appear here.
        </p>
      </CardContent>
    </Card>

    <!-- Feedback Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <FeedbackCard 
        v-for="item in feedbackStore.latestFeedback" 
        :key="item.id" 
        :feedback="item"
        @acknowledge="feedbackStore.acknowledgeFeedback"
      />
    </div>
  </div>
</template>
