<script setup lang="ts">
interface Props {
  isCollapsed: boolean;
}

defineProps<Props>();
</script>

<template>
  <header class="w-full border-b border-stone-200/60 backdrop-blur-xl bg-[#FDFCF8]/80 flex-shrink-0 font-inter relative z-30 transition-[background-color,border-color] duration-400 ease-in-out overflow-hidden">
    <!-- Subtle Noise Texture -->
    <div class="absolute inset-0 pointer-events-none opacity-[0.02] mix-blend-multiply -z-10 bg-[url('https://grainy-gradients.vercel.app/noise.svg')]"></div>

    <div class="flex items-center justify-between h-20 px-6 sm:px-8 max-w-7xl mx-auto w-full">
      <!-- Left Section: Status -->
      <div class="flex items-center min-w-0">
        <div class="flex items-center gap-2 group cursor-default bg-emerald-50/30 px-3 py-1.5 rounded-full border border-emerald-100/20 group-hover:bg-emerald-50/50 transition-all duration-300">
          <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.4)]"></div>
          <span class="text-[9px] uppercase tracking-[0.2em] font-bold text-emerald-800/40 group-hover:text-emerald-800 transition-colors whitespace-nowrap">Garden Healthy • Phase I</span>
        </div>
      </div>
    </div>
  </header>
</template>
