<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { RouterView, useRoute } from 'vue-router';
import AppHeader from './components/AppHeader.vue';
import AppSidebar from './components/AppSidebar.vue';
import { Menu } from 'lucide-vue-next';
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
} from '@/components/ui/sheet';
import ToastProvider from '@/components/ui/toast/ToastProvider.vue';

const route = useRoute();

// Visibility control: only show sidebar and header on protected routes
const showAuthLayout = computed(() => route.meta.requiresAuth === true);

// Sidebar state
const isSidebarCollapsed = ref(false);
const isMobileDrawerOpen = ref(false);
const isMobile = ref(false);

// Check if mobile on mount and resize
const checkMobile = () => {
  isMobile.value = window.innerWidth < 768;
  if (!isMobile.value) {
    isMobileDrawerOpen.value = false;
  }
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});

const toggleSidebar = () => {
  if (isMobile.value) {
    isMobileDrawerOpen.value = !isMobileDrawerOpen.value;
  } else {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
  }
};

const closeMobileDrawer = () => {
  isMobileDrawerOpen.value = false;
};
</script>

<template>
  <div class="min-h-screen bg-[#FDFCF8] flex relative isolate">
    <!-- Global Atmosphere (Noise) -->
    <div
      class="fixed inset-0 pointer-events-none opacity-[0.03] mix-blend-multiply -z-10 bg-[url('https://grainy-gradients.vercel.app/noise.svg')]">
    </div>

    <!-- Desktop Sidebar - Fixed, sits beside the entire app (hidden on public pages) -->
    <div v-if="!isMobile && showAuthLayout" class="flex-shrink-0 fixed left-0 top-0 h-screen z-40">
      <AppSidebar :is-collapsed="isSidebarCollapsed" @toggle-collapse="toggleSidebar" />
    </div>

    <!-- Mobile Drawer (hidden on public pages) -->
    <Sheet v-if="showAuthLayout" v-model:open="isMobileDrawerOpen">
      <SheetContent side="left" class="p-0 w-72 border-0">
        <SheetHeader class="sr-only">
          <SheetTitle>Navigation Menu</SheetTitle>
        </SheetHeader>
        <AppSidebar :is-collapsed="false" :is-mobile-open="isMobileDrawerOpen" @close-mobile="closeMobileDrawer" />
      </SheetContent>
    </Sheet>

    <!-- Main App Container (Header + Content) - Offset by sidebar width -->
    <div class="flex-1 flex flex-col min-w-0 transition-[margin-left] duration-400 ease-in-out"
      :style="{ marginLeft: !showAuthLayout ? '0' : (isMobile ? '0' : (isSidebarCollapsed ? '80px' : '288px')) }">
      <!-- Mobile: Fixed Menu Button (hidden on public pages) -->
      <button v-if="isMobile && showAuthLayout" @click="toggleSidebar"
        class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-emerald-900 text-white rounded-full shadow-xl flex items-center justify-center hover:bg-emerald-800 transition-all hover:scale-105 active:scale-95"
        aria-label="Open menu">
        <Menu class="w-6 h-6" />
      </button>

      <!-- Header (hidden on public pages) -->
      <AppHeader v-if="showAuthLayout" :is-collapsed="isSidebarCollapsed" />

      <!-- Main Content -->
      <main class="flex-1 overflow-auto">
        <div :class="!showAuthLayout ? '' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8'">
          <RouterView />
        </div>
      </main>
    </div>

    <!-- Global Notifications -->
    <ToastProvider />
  </div>
</template>

<style scoped>
a {
  @apply text-slate-600 hover:text-slate-900;
}
</style>
