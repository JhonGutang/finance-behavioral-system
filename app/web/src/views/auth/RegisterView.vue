<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Loader2, Mail, Lock, User, Layers } from 'lucide-vue-next';

const authStore = useAuthStore();
const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref<string | null>(null);

const handleRegister = async () => {
  if (password.value !== password_confirmation.value) {
    error.value = 'Passwords do not match';
    return;
  }
  
  error.value = null;
  try {
    await authStore.register({ 
      name: name.value, 
      email: email.value, 
      password: password.value, 
      password_confirmation: password_confirmation.value 
    });
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Registration failed';
  }
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-[#FDFCF8] p-4 font-inter relative overflow-hidden">
    <!-- Background Elements -->
    <div class="fixed inset-0 pointer-events-none opacity-40 mix-blend-multiply">
      <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-50"></div>
    </div>
    <!-- Abstract shapes -->
    <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-emerald-800/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-5%] w-80 h-80 bg-emerald-600/5 rounded-full blur-3xl pointer-events-none"></div>

    <Card class="w-full max-w-md bg-white/60 backdrop-blur-xl border border-emerald-100/50 shadow-2xl shadow-emerald-900/5 relative z-10">
      <CardHeader class="space-y-1 flex flex-col items-center pb-8 pt-8">
        <div class="w-12 h-12 bg-emerald-900 rounded-full flex items-center justify-center mb-6 shadow-lg shadow-emerald-900/10">
          <Layers class="w-6 h-6 text-[#FDFCF8]" />
        </div>
        <CardTitle class="text-3xl font-serif font-medium tracking-tight text-emerald-950">Create Account</CardTitle>
        <CardDescription class="text-emerald-900/60 font-light text-center max-w-xs text-base">Begin your journey to calm, disciplined wealth</CardDescription>
      </CardHeader>
      <CardContent class="grid gap-5">
        <Alert v-if="error" variant="destructive" class="animate-in fade-in slide-in-from-top-2 duration-300 border-red-200 bg-red-50 text-red-800">
          <AlertDescription>{{ error }}</AlertDescription>
        </Alert>
        
        <div class="grid gap-2">
          <Label for="name" class="text-sm font-medium text-emerald-900">Full Name</Label>
          <div class="relative">
            <User class="absolute left-3 top-3 h-4 w-4 text-emerald-700/50" />
            <Input 
              id="name" 
              type="text" 
              placeholder="John Doe" 
              v-model="name" 
              class="pl-10 border-emerald-100 bg-white/50 focus:border-emerald-800 focus:ring-emerald-800/20 text-emerald-950 placeholder:text-emerald-900/30 transition-all rounded-xl h-11"
              :disabled="authStore.loading"
            />
          </div>
        </div>

        <div class="grid gap-2">
          <Label for="email" class="text-sm font-medium text-emerald-900">Email</Label>
          <div class="relative">
            <Mail class="absolute left-3 top-3 h-4 w-4 text-emerald-700/50" />
            <Input 
              id="email" 
              type="email" 
              placeholder="name@example.com" 
              v-model="email" 
              class="pl-10 border-emerald-100 bg-white/50 focus:border-emerald-800 focus:ring-emerald-800/20 text-emerald-950 placeholder:text-emerald-900/30 transition-all rounded-xl h-11"
              :disabled="authStore.loading"
            />
          </div>
        </div>

        <div class="grid gap-2">
          <Label for="password" class="text-sm font-medium text-emerald-900">Password</Label>
          <div class="relative">
            <Lock class="absolute left-3 top-3 h-4 w-4 text-emerald-700/50" />
            <Input 
              id="password" 
              type="password" 
              v-model="password" 
              class="pl-10 border-emerald-100 bg-white/50 focus:border-emerald-800 focus:ring-emerald-800/20 text-emerald-950 placeholder:text-emerald-900/30 transition-all rounded-xl h-11"
              :disabled="authStore.loading"
            />
          </div>
        </div>

        <div class="grid gap-2">
          <Label for="password_confirmation" class="text-sm font-medium text-emerald-900">Confirm Password</Label>
          <div class="relative">
            <Lock class="absolute left-3 top-3 h-4 w-4 text-emerald-700/50" />
            <Input 
              id="password_confirmation" 
              type="password" 
              v-model="password_confirmation" 
              class="pl-10 border-emerald-100 bg-white/50 focus:border-emerald-800 focus:ring-emerald-800/20 text-emerald-950 placeholder:text-emerald-900/30 transition-all rounded-xl h-11"
              :disabled="authStore.loading"
            />
          </div>
        </div>
      </CardContent>
      <CardFooter class="flex flex-col gap-4 pt-4 pb-8">
        <Button 
          class="w-full bg-emerald-900 hover:bg-emerald-800 text-[#FDFCF8] py-6 text-base font-medium tracking-wide rounded-xl shadow-lg shadow-emerald-900/10 hover:shadow-xl hover:shadow-emerald-900/20 transition-all duration-300 transform active:scale-[0.98]"
          :disabled="authStore.loading"
          @click="handleRegister"
        >
          <Loader2 v-if="authStore.loading" class="mr-2 h-4 w-4 animate-spin" />
          <span v-else>Sign Up</span>
        </Button>
        <p class="text-center text-sm text-emerald-900/60">
          Already have an account? 
          <router-link to="/login" class="font-semibold text-emerald-800 hover:text-emerald-950 hover:underline underline-offset-4 transition-colors">
            Sign in
          </router-link>
        </p>
      </CardFooter>
    </Card>
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
