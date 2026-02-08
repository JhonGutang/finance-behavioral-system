<script setup lang="ts">
import { onMounted, ref, onUnmounted, computed } from 'vue';
import { useFeedbackStore } from '@/stores/feedbackStore';
import InsightTimeline from '@/components/feedback/InsightTimeline.vue';
import ProgressTrendChart from '@/components/charts/ProgressTrendChart.vue';
import { 
  BrainCircuit, 
  Trophy, 
  History, 
  TrendingUp, 
  ChevronUp, 
  Loader2, 
  Sparkles
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const feedbackStore = useFeedbackStore();
const showScrollTop = ref(false);
const loadMoreTrigger = ref<HTMLElement | null>(null);

let observer: IntersectionObserver | null = null;

onMounted(async () => {
  await Promise.all([
    feedbackStore.fetchFeedback(false),
    feedbackStore.fetchProgress()
  ]);

  observer = new IntersectionObserver((entries) => {
    if (entries[0]?.isIntersecting && feedbackStore.hasMore && !feedbackStore.isFetchingMore) {
      feedbackStore.fetchMoreFeedback();
    }
  }, { threshold: 0.1 });

  if (loadMoreTrigger.value) {
    observer.observe(loadMoreTrigger.value);
  }

  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  if (observer) observer.disconnect();
  window.removeEventListener('scroll', handleScroll);
});

function handleScroll() {
  showScrollTop.value = window.scrollY > 400;
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

const currentScore = computed(() => feedbackStore.currentProgress?.improvement_score || 0);

const improvementMessage = computed(() => {
    const score = currentScore.value;
    if (score >= 90) return "Masterful discipline.";
    if (score >= 80) return "Excellent habits forming.";
    if (score >= 60) return "Steady progress visible.";
    return "Building the foundation.";
});
</script>

<template>
  <div class="min-h-screen animate-in fade-in duration-1000 slide-in-from-bottom-2 pb-20 font-inter">
     <!-- Background Texture & Atmosphere -->
    <div class="fixed inset-0 pointer-events-none opacity-40 mix-blend-multiply -z-10">
      <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-30"></div>
    </div>
    <div class="fixed inset-0 bg-[#FDFCF8] -z-20"></div>

    <div class="space-y-12 max-w-5xl mx-auto px-6 md:px-8 pt-6 md:pt-8">
      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
          <h1 class="text-4xl md:text-5xl font-serif font-medium text-emerald-950 tracking-tight">Behavioral Wisdom</h1>
          <p class="text-emerald-800 mt-3 font-light text-lg max-w-xl">
             Explore the patterns that shape your financial future.
          </p>
        </div>
      </div>

      <!-- Top Section: Score & Trend -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- Current Score Card -->
        <Card class="md:col-span-4 bg-gradient-to-br from-emerald-900 to-emerald-950 border-0 shadow-xl shadow-emerald-900/20 text-[#FDFCF8] relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:opacity-20 transition-opacity">
               <Trophy class="w-32 h-32 -mr-8 -mt-8" />
            </div>
            <CardContent class="p-8 h-full flex flex-col justify-between relative z-10">
                <div>
                    <h3 class="text-emerald-100 uppercase tracking-[0.2em] text-xs font-bold font-inter mb-1">Current Score</h3>
                    <div class="flex items-baseline gap-1">
                        <span class="text-6xl font-serif font-medium text-white">{{ currentScore }}</span>
                        <span class="text-xl text-emerald-400 font-serif">%</span>
                    </div>
                </div>
                
                <div class="mt-8">
                     <div class="flex items-center gap-2 mb-3">
                        <Sparkles class="w-4 h-4 text-emerald-300" />
                        <span class="text-sm font-medium text-emerald-100">{{ improvementMessage }}</span>
                     </div>
                     <div class="w-full bg-emerald-900/50 h-1.5 rounded-full overflow-hidden">
                        <div 
                            class="bg-emerald-400 h-full rounded-full shadow-[0_0_10px_rgba(52,211,153,0.6)] transition-all duration-1000"
                            :style="{ width: `${currentScore}%` }"
                        ></div>
                     </div>
                </div>
            </CardContent>
        </Card>

        <!-- Progress Trend Chart -->
        <Card class="md:col-span-8 bg-white border border-stone-300 shadow-sm">
            <CardHeader class="pb-2">
                <div class="flex items-center justify-between">
                    <div>
                         <CardTitle class="text-xl font-serif text-emerald-950 flex items-center gap-2">
                             Growth Trajectory
                         </CardTitle>
                         <p class="text-xs text-emerald-900/40 mt-1 font-light">Your behavioral improvement over time</p>
                    </div>
                    <div class="p-2 bg-emerald-50 rounded-lg">
                        <TrendingUp class="w-5 h-5 text-emerald-600" />
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                 <div class="h-[200px] w-full">
                    <ProgressTrendChart 
                      :progress-history="feedbackStore.progressHistory"
                      :loading="feedbackStore.loading"
                    />
                 </div>
            </CardContent>
        </Card>
      </div>

      <!-- Timeline Section -->
      <div class="space-y-8">
         <div class="flex items-center gap-3 border-b border-stone-300 pb-4">
             <div class="p-1.5 bg-emerald-100/50 rounded-lg text-emerald-800">
                 <History class="w-5 h-5" />
             </div>
             <h2 class="text-2xl font-serif text-emerald-950">Insight Journal</h2>
         </div>

         <div v-if="feedbackStore.feedbackHistory.length === 0 && !feedbackStore.loading" class="bg-emerald-50/30 rounded-3xl border border-dashed border-stone-400 py-24 flex flex-col items-center text-center">
            <div class="bg-white p-4 rounded-full mb-4 shadow-sm border border-stone-200">
               <BrainCircuit class="w-10 h-10 text-emerald-200" />
            </div>
            <h3 class="text-xl font-serif text-emerald-900">Your journal is waiting</h3>
            <p class="text-emerald-800 max-w-sm mt-3 font-light italic">
              As you continue tracking your expenses, we'll build a library of personalized advice to help you reach your goals.
            </p>
         </div>

         <div v-else class="relative">
            <InsightTimeline 
                :feedback-history="feedbackStore.feedbackHistory"
                :progress-history="feedbackStore.progressHistory"
                @acknowledge="feedbackStore.acknowledgeFeedback"
            />
            
            <!-- Loader -->
             <div ref="loadMoreTrigger" class="py-12 flex justify-center">
                <div v-if="feedbackStore.isFetchingMore" class="flex items-center gap-3 text-emerald-600/60 bg-white px-6 py-2 rounded-full border border-stone-300 shadow-sm">
                  <Loader2 class="w-4 h-4 animate-spin" />
                  <span class="text-sm font-medium">Unearthing more insights...</span>
                </div>
                <div v-else-if="!feedbackStore.hasMore && feedbackStore.feedbackHistory.length > 0" class="text-emerald-800 font-bold uppercase tracking-widest text-[10px] flex items-center gap-2">
                   <div class="w-1 h-1 rounded-full bg-emerald-400"></div>
                   Beginning of your journey
                   <div class="w-1 h-1 rounded-full bg-emerald-400"></div>
                </div>
             </div>
         </div>
      </div>
    
    <!-- Floating Scroll to Top -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="translate-y-10 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="translate-y-10 opacity-0"
    >
      <Button
        v-if="showScrollTop"
        @click="scrollToTop"
        class="fixed bottom-8 right-8 rounded-full w-11 h-11 shadow-lg shadow-emerald-900/10 z-50 p-0 bg-emerald-900 hover:bg-emerald-800 text-white border border-emerald-800 transition-all hover:-translate-y-1"
        aria-label="Scroll to top"
      >
        <ChevronUp class="w-5 h-5" />
      </Button>
    </Transition>

    </div>
  </div>
</template>

<style scoped>
.font-serif {
  font-family: 'Playfair Display', serif;
}
.font-inter {
  font-family: 'Inter', sans-serif;
}
</style>
