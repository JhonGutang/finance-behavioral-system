<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router';
import { LayoutDashboard, Receipt, LogOut, User, Settings, ChevronsUpDown, BrainCircuit, Menu } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/authStore';
import { computed, ref } from 'vue';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

interface Props {
  isCollapsed: boolean;
  isMobileOpen?: boolean;
}

interface Emits {
  (e: 'toggle-collapse'): void;
  (e: 'close-mobile'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();
const route = useRoute();
const authStore = useAuthStore();

const isLogoutDialogOpen = ref(false);

const userInitials = computed(() => {
  const name = authStore.user?.name || 'User';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const navItems = [
  {
    to: '/dashboard',
    icon: LayoutDashboard,
    label: 'Dashboard',
    exact: true,
  },
  {
    to: '/insights',
    icon: BrainCircuit,
    label: 'Insights',
    exact: false,
  },
  {
    to: '/transactions',
    icon: Receipt,
    label: 'Transactions',
    exact: false,
  },
];

const isActive = (item: typeof navItems[0]) => {
  if (item.exact) {
    return route.path === item.to;
  }
  return route.path.startsWith(item.to);
};

const handleNavClick = () => {
  if (props.isMobileOpen) {
    emit('close-mobile');
  }
};

const handleLogout = async () => {
  await authStore.logout();
  isLogoutDialogOpen.value = false;
};
</script>

<template>
  <aside
    :class="[
      'flex flex-col bg-[#FDFCF8] border-r border-stone-200 transition-[width] duration-400 ease-in-out h-screen relative z-40 overflow-x-hidden',
      isCollapsed ? 'w-20' : 'w-72',
    ]"
  >
    <!-- Subtle Noise Overlay for Texture -->
    <div class="absolute inset-0 pointer-events-none opacity-[0.03] mix-blend-multiply -z-10 bg-[url('https://grainy-gradients.vercel.app/noise.svg')]"></div>

    <!-- Sidebar Header: Logo + Toggle -->
    <div class="h-24 flex-shrink-0 flex items-center px-5 overflow-hidden">
      <div 
        class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-900 shadow-lg shadow-emerald-900/20 text-white mr-4 transition-all duration-300"
      >
        <BrainCircuit class="w-5 h-5" />
      </div>
      
      <transition name="fade">
        <div v-if="!isCollapsed" class="flex flex-col min-w-0 pointer-events-none">
          <h1 class="text-xl font-serif font-semibold text-emerald-950 truncate tracking-tight leading-tight whitespace-nowrap">
            Spendlytic
          </h1>
          <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-emerald-800/40 whitespace-nowrap">Financial Garden</p>
        </div>
      </transition>
    </div>

    <!-- Navigation Links -->
    <!-- Navigation Links -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto overflow-x-hidden custom-scrollbar font-inter">
      <RouterLink
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        @click="handleNavClick"
        :class="[
          'flex items-center px-4 py-3 rounded-2xl transition-all duration-300 group relative overflow-hidden',
          isActive(item)
            ? 'bg-white shadow-[0_4px_12px_rgba(0,0,0,0.03)] border border-stone-200 text-emerald-950 font-semibold'
            : 'text-emerald-950/50 hover:text-emerald-950 hover:bg-emerald-50/50',
        ]"
      >
        <!-- Active Indicator Dot -->
        <div 
          v-if="isActive(item) && !isCollapsed" 
          class="absolute left-0 w-1 h-4 bg-emerald-600 rounded-r-full"
        ></div>

        <div class="w-6 h-6 flex-shrink-0 flex items-center justify-center mr-3 transition-all duration-300">
          <component
            :is="item.icon"
            :class="[
              'w-5 h-5 transition-colors duration-300',
              isActive(item) ? 'text-emerald-700' : 'text-emerald-800/30 group-hover:text-emerald-800/60',
            ]"
          />
        </div>

        <transition name="fade">
          <span v-if="!isCollapsed" class="text-sm whitespace-nowrap tracking-tight">
            {{ item.label }}
          </span>
        </transition>

        <!-- Tooltip for collapsed state (invisible hover area) -->
        <div 
          v-if="isCollapsed" 
          class="absolute inset-0 z-10 group-hover:bg-emerald-950/5 pointer-events-none"
        ></div>
        <div 
          v-if="isCollapsed" 
          class="absolute left-full ml-4 px-3 py-1 bg-emerald-950 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50 font-medium"
        >
          {{ item.label }}
        </div>
      </RouterLink>
    </nav>

    <!-- Sidebar Footer: User Dropdown -->
    <div class="p-4 border-t border-stone-200 bg-stone-50/30 flex-shrink-0 overflow-hidden">
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <button
            :class="[
              'w-full flex items-center p-2 rounded-2xl bg-white/50 hover:bg-white transition-all duration-300 border border-stone-200/60 hover:border-stone-300 text-left outline-none group shadow-sm hover:shadow-md',
            ]"
          >
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-800 to-emerald-950 flex items-center justify-center text-emerald-50 font-bold text-xs flex-shrink-0 shadow-inner mr-3 transition-all duration-300">
              {{ userInitials }}
            </div>
            <transition name="fade">
              <div v-if="!isCollapsed" class="flex-1 min-w-0 flex items-center justify-between">
                <div class="flex-1 min-w-0 mr-2">
                  <p class="text-xs font-bold text-emerald-950 truncate uppercase tracking-wider">
                    {{ authStore.user?.name || 'User' }}
                  </p>
                  <p class="text-[10px] text-emerald-800/50 truncate font-medium">
                    Gardener
                  </p>
                </div>
                <ChevronsUpDown class="w-3.5 h-3.5 text-emerald-800/30 group-hover:text-emerald-800/60 transition-colors" />
              </div>
            </transition>
          </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent 
          side="right" 
          :align="'end'" 
          class="w-64 ml-4 p-2 rounded-[1.5rem] border-stone-200 bg-[#FDFCF8] shadow-xl animate-in fade-in slide-in-from-left-2 duration-300"
        >
          <DropdownMenuLabel class="px-3 py-2 text-[10px] uppercase tracking-[0.2em] font-bold text-emerald-800/40">Establishment</DropdownMenuLabel>
          <DropdownMenuSeparator class="bg-stone-100" />
          <DropdownMenuGroup class="space-y-1">
            <DropdownMenuItem class="cursor-pointer rounded-xl focus:bg-emerald-50 focus:text-emerald-900 group">
              <User class="mr-3 h-4 w-4 text-emerald-800/40 group-hover:text-emerald-800" />
              <span class="text-sm font-medium">Personal Presence</span>
            </DropdownMenuItem>
            <DropdownMenuItem class="cursor-pointer rounded-xl focus:bg-emerald-50 focus:text-emerald-900 group">
              <Settings class="mr-3 h-4 w-4 text-emerald-800/40 group-hover:text-emerald-800" />
              <span class="text-sm font-medium">Garden Settings</span>
            </DropdownMenuItem>
          </DropdownMenuGroup>
          <DropdownMenuSeparator class="bg-stone-100" />
          <DropdownMenuItem 
            class="text-rose-600 focus:text-rose-700 focus:bg-rose-50 cursor-pointer rounded-xl group"
            @click="isLogoutDialogOpen = true"
          >
            <LogOut class="mr-3 h-4 w-4 text-rose-400 group-hover:text-rose-600" />
            <span class="text-sm font-semibold uppercase tracking-wider text-[11px]">Conclude Session</span>
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>

    <!-- Collapse Toggle (Floating) -->
    <button
      @click="emit('toggle-collapse')"
      class="absolute -right-4 top-10 w-8 h-8 rounded-full bg-white border border-stone-200 shadow-sm flex items-center justify-center text-emerald-800 hover:text-emerald-600 hover:shadow-md transition-all z-50 group"
    >
      <Menu :class="['w-4 h-4 transition-transform duration-500', isCollapsed ? 'rotate-180' : '']" />
    </button>

    <!-- Logout Confirmation Dialog -->
    <AlertDialog v-model:open="isLogoutDialogOpen">
      <AlertDialogContent class="rounded-[2.5rem] border-stone-200 bg-[#FDFCF8] p-8">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-2xl font-serif text-emerald-950">Conclude Presence?</AlertDialogTitle>
          <AlertDialogDescription class="text-emerald-800/70 font-inter">
            Your session will be securely archived. You will need to re-authenticate to tend to your financial garden.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <div class="mt-8 flex flex-col sm:flex-row gap-3">
          <AlertDialogCancel @click="isLogoutDialogOpen = false" class="flex-1 rounded-full h-11 px-6 font-bold uppercase tracking-widest text-[10px] border-stone-200 hover:bg-stone-50 text-emerald-900">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            class="flex-1 bg-emerald-950 hover:bg-emerald-900 text-white rounded-full h-11 px-8 font-bold uppercase tracking-widest text-[11px] shadow-lg shadow-emerald-900/20 transition-all"
            @click="handleLogout"
          >
            Confirm
          </AlertDialogAction>
        </div>
      </AlertDialogContent>
    </AlertDialog>
  </aside>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease-in-out;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  @apply bg-stone-200 rounded-full;
}
</style>
