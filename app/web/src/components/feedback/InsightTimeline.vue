<script setup lang="ts">
import { computed } from 'vue';
import type { FeedbackHistory, UserProgress } from '@/types';
import FeedbackCard from './FeedbackCard.vue';
import { Badge } from '@/components/ui/badge';
import { Calendar, TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';

const props = defineProps<{
  feedbackHistory: FeedbackHistory[];
  progressHistory: UserProgress[];
}>();

defineEmits<{
  (e: 'acknowledge', id: number): void;
}>();

// Group feedback by week
const groupedFeedback = computed(() => {
  const groups: Record<string, {
    weekStart: string;
    weekEnd: string;
    items: FeedbackHistory[];
    progress?: UserProgress;
  }> = {};

  // Sort feedback by date descending
  const sortedFeedback = [...props.feedbackHistory].sort((a, b) => 
    new Date(b.week_start).getTime() - new Date(a.week_start).getTime()
  );

  sortedFeedback.forEach(item => {
    const key = item.week_start;
    if (!groups[key]) {
      // Find corresponding progress for this week
      const progress = props.progressHistory.find(p => p.week_start === item.week_start);
      
      groups[key] = {
        weekStart: item.week_start,
        weekEnd: item.week_end,
        items: [],
        progress
      };
    }
    groups[key].items.push(item);
  });

  return Object.values(groups);
});

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  });
}

function getScoreColor(score: number) {
  if (score >= 80) return 'bg-emerald-100 text-emerald-800 border-emerald-200';
  if (score >= 50) return 'bg-sky-100 text-sky-800 border-sky-200';
  return 'bg-amber-100 text-amber-800 border-amber-200';
}

function getScoreIcon(score: number) {
  if (score >= 80) return TrendingUp;
  if (score >= 50) return Minus;
  return TrendingDown;
}

function getStatusText(score: number) {
  if (score >= 90) return 'Mastery';
  if (score >= 80) return 'Exceptional';
  if (score >= 50) return 'Steady';
  return 'Needs Focus';
}
</script>

<template>
  <div class="relative pb-12">
    <!-- Vertical Line -->
    <div class="absolute left-4 md:left-1/2 top-0 bottom-0 w-px bg-stone-400 -translate-x-1/2 hidden md:block" />
    <div class="absolute left-4 top-0 bottom-0 w-px bg-stone-400 md:hidden" />

    <div class="space-y-12">
      <div 
        v-for="group in groupedFeedback" 
        :key="group.weekStart"
        class="relative"
      >
        <!-- Week Marker Node -->
        <div class="flex items-center justify-start md:justify-center mb-8">
          <div class="z-10 bg-[#FDFCF8] p-1.5 rounded-full border border-stone-300 shadow-sm">
            <div 
              :class="[
                'w-10 h-10 md:w-14 md:h-14 rounded-full flex items-center justify-center flex-col transition-all',
                group.progress ? getScoreColor(group.progress.improvement_score) : 'bg-emerald-50 text-emerald-600'
              ]"
            >
              <template v-if="group.progress">
                <span class="text-xs md:text-sm font-bold leading-none font-inter">{{ group.progress.improvement_score }}%</span>
                <component :is="getScoreIcon(group.progress.improvement_score)" class="w-3 h-3 md:w-4 md:h-4 mt-0.5 opacity-60" />
              </template>
              <Calendar v-else class="w-5 h-5 md:w-6 md:h-6" />
            </div>
          </div>
          
          <!-- Date Range (Mobile & Desktop positions differ slightly for style) -->
          <div class="ml-4 md:absolute md:left-1/2 md:-translate-x-1/2 md:mt-24 text-center transform md:translate-y-2">
            <div class="bg-white px-4 py-1.5 rounded-full border border-stone-300 shadow-sm inline-flex items-center gap-2 whitespace-nowrap group hover:border-emerald-400 transition-colors">
              <span class="text-[10px] md:text-xs font-bold text-emerald-800 uppercase tracking-widest font-inter">
                {{ formatDate(group.weekStart) }} — {{ formatDate(group.weekEnd) }}
              </span>
              <Badge v-if="group.progress" variant="outline" class="h-5 text-[9px] uppercase tracking-tighter border-emerald-100 bg-emerald-50/50 text-emerald-800">
                {{ getStatusText(group.progress.improvement_score) }}
              </Badge>
            </div>
          </div>
        </div>

        <!-- Insights Grid for this week -->
        <div class="pl-12 md:pl-0 md:grid md:grid-cols-2 md:gap-x-24 md:gap-y-12 mt-16 relative">
             <!-- Connector lines for desktop items -->
          <div 
            v-for="(item, itemIndex) in group.items" 
            :key="item.id"
            :class="[
              'mb-8 md:mb-0 relative group/entry',
              itemIndex % 2 === 0 ? 'md:justify-self-end md:text-right' : 'md:justify-self-start'
            ]"
          >
             <!-- Horizontal connector (Desktop only) -->
            <div 
                v-if="itemIndex % 2 === 0"
                class="hidden md:block absolute top-8 -right-12 w-12 h-px bg-gradient-to-r from-stone-400 to-transparent"
            ></div>
            <div 
                v-else
                class="hidden md:block absolute top-8 -left-12 w-12 h-px bg-gradient-to-l from-stone-400 to-transparent"
            ></div>

            <div class="max-w-md w-full">
              <FeedbackCard 
                :feedback="item"
                @acknowledge="$emit('acknowledge', $event)"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Connector to future points -->
    <div class="absolute left-4 md:left-1/2 bottom-0 w-3 h-3 bg-emerald-200 rounded-full -translate-x-1/2 flex items-center justify-center">
      <div class="w-1.5 h-1.5 bg-[#FDFCF8] rounded-full" />
    </div>
  </div>
</template>

<style scoped>
.font-inter {
    font-family: 'Inter', sans-serif;
}
</style>
