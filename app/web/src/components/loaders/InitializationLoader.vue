<script setup lang="ts">
import { Motion } from 'motion-v';
import { Layers } from 'lucide-vue-next';
</script>

<template>
  <div class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-[#FDFCF8] font-inter">
    <!-- Background Texture -->
    <div class="absolute inset-0 pointer-events-none opacity-40 mix-blend-multiply">
      <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-50"></div>
    </div>
    
    <!-- Abstract Shapes -->
    <Motion
      :initial="{ opacity: 0, scale: 0.8 }"
      :animate="{ opacity: 1, scale: 1 }"
      :transition="{ duration: 1.5, repeat: Infinity, repeatType: 'reverse', ease: 'easeInOut' }"
      class="absolute top-1/4 right-1/4 w-96 h-96 bg-emerald-800/5 rounded-full blur-3xl pointer-events-none"
    />
    <Motion
      :initial="{ opacity: 0, scale: 0.8 }"
      :animate="{ opacity: 1, scale: 1 }"
      :transition="{ duration: 2, repeat: Infinity, repeatType: 'reverse', ease: 'easeInOut', delay: 0.5 }"
      class="absolute bottom-1/4 left-1/4 w-80 h-80 bg-emerald-600/5 rounded-full blur-3xl pointer-events-none"
    />

    <!-- Loader Content -->
    <div class="relative z-10 flex flex-col items-center">
      <Motion
        :initial="{ scale: 0.8, opacity: 0 }"
        :animate="{ scale: 1, opacity: 1 }"
        :transition="{ duration: 0.8, ease: 'easeOut' }"
        class="mb-8 relative"
      >
        <div class="w-16 h-16 bg-emerald-900 rounded-2xl flex items-center justify-center shadow-xl shadow-emerald-900/20">
            <Layers class="w-8 h-8 text-[#FDFCF8]" />
        </div>
        
        <!-- Breathing Ring -->
        <Motion
            class="absolute inset-0 rounded-2xl border border-emerald-900"
            :animate="{ scale: [1, 1.2, 1], opacity: [1, 0, 1] }"
            :transition="{ duration: 2, repeat: Infinity, ease: 'easeInOut' }"
        />
      </Motion>

      <Motion
        as="h2"
        :initial="{ opacity: 0, y: 10 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="{ delay: 0.3, duration: 0.8 }"
        class="text-2xl font-serif font-medium text-emerald-950 mb-2 tracking-tight"
      >
        Preparing your insights
      </Motion>
      
      <Motion
        as="p"
        :initial="{ opacity: 0 }"
        :animate="{ opacity: 1 }"
        :transition="{ delay: 0.6, duration: 0.8 }"
        class="text-emerald-900/60 text-sm font-light tracking-wide"
      >
        Cultivating your financial data...
      </Motion>
    </div>
  </div>
</template>

<style scoped>
.font-serif {
  font-family: 'Playfair Display', serif;
}
</style>
