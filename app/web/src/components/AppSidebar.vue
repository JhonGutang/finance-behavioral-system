<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router';
import { LayoutDashboard, Receipt, Menu, LogOut, User, Settings, ChevronsUpDown, BrainCircuit } from 'lucide-vue-next';
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
  AlertDialogFooter,
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
      'flex flex-col bg-white border-r border-slate-200 transition-all duration-300 h-screen',
      isCollapsed ? 'w-16' : 'w-60',
    ]"
  >
    <!-- Sidebar Header: Logo + Toggle -->
    <div class="h-16 flex items-center px-4 border-b border-slate-200 gap-3">
      <button
        @click="emit('toggle-collapse')"
        class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg hover:bg-emerald-50 transition-colors"
        aria-label="Toggle sidebar"
      >
        <Menu class="w-5 h-5 text-emerald-800" />
      </button>
      
      <transition name="fade">
        <h1 v-if="!isCollapsed" class="text-xl font-serif font-bold bg-gradient-to-r from-emerald-900 to-emerald-700 bg-clip-text text-transparent truncate tracking-tight">
          Spendlytic
        </h1>
      </transition>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar font-inter">
      <RouterLink
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        @click="handleNavClick"
        :class="[
          'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group',
          isActive(item)
            ? 'bg-emerald-50 text-emerald-900 font-semibold'
            : 'text-emerald-950/60 hover:bg-emerald-50/50 hover:text-emerald-900',
          isCollapsed ? 'justify-center' : '',
        ]"
      >
        <component
          :is="item.icon"
          :class="[
            'w-5 h-5 flex-shrink-0',
            isActive(item) ? 'text-emerald-900' : 'text-emerald-800/40 group-hover:text-emerald-800',
          ]"
        />
        <transition name="fade">
          <span v-if="!isCollapsed" class="text-sm whitespace-nowrap tracking-tight">
            {{ item.label }}
          </span>
        </transition>
      </RouterLink>
    </nav>

    <!-- Sidebar Footer: User Dropdown -->
    <div class="p-4 border-t border-slate-200">
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <button
            :class="[
              'w-full flex items-center gap-3 px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors border border-transparent hover:border-slate-200 text-left outline-none mb-1 group',
              isCollapsed ? 'justify-center' : '',
            ]"
          >
            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-primary to-purple-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 shadow-sm">
              {{ userInitials }}
            </div>
            <transition name="fade">
              <div v-if="!isCollapsed" class="flex-1 min-w-0 flex items-center justify-between">
                <div class="flex-1 min-w-0 mr-2">
                  <p class="text-sm font-medium text-slate-900 truncate">
                    {{ authStore.user?.name || 'User' }}
                  </p>
                  <p class="text-xs text-slate-500 truncate">
                    {{ authStore.user?.email || 'user@example.com' }}
                  </p>
                </div>
                <ChevronsUpDown class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-colors" />
              </div>
            </transition>
          </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent 
          side="top" 
          :align="isCollapsed ? 'center' : 'end'" 
          class="w-56 mb-2 animate-in slide-in-from-bottom-2 duration-200"
        >
          <DropdownMenuLabel>My Account</DropdownMenuLabel>
          <DropdownMenuSeparator />
          <DropdownMenuGroup>
            <DropdownMenuItem class="cursor-pointer">
              <User class="mr-2 h-4 w-4" />
              <span>Profile</span>
            </DropdownMenuItem>
            <DropdownMenuItem class="cursor-pointer">
              <Settings class="mr-2 h-4 w-4" />
              <span>Settings</span>
            </DropdownMenuItem>
          </DropdownMenuGroup>
          <DropdownMenuSeparator />
          <DropdownMenuItem 
            class="text-red-600 focus:text-red-700 focus:bg-red-50 cursor-pointer"
            @click="isLogoutDialogOpen = true"
          >
            <LogOut class="mr-2 h-4 w-4" />
            <span>Log out</span>
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>

    <!-- Logout Confirmation Dialog -->
    <AlertDialog v-model:open="isLogoutDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you sure you want to log out?</AlertDialogTitle>
          <AlertDialogDescription>
            You will need to enter your credentials again to access your financial data.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="isLogoutDialogOpen = false">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            class="bg-red-600 hover:bg-red-700 text-white"
            @click="handleLogout"
          >
            Log Out
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </aside>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  @apply bg-slate-200 rounded-full;
}
</style>
