<script setup lang="ts">
import { useRouter } from 'vue-router';
import { Button } from '@/components/ui/button';
import { Motion } from 'motion-v';
import { 
  TrendingUp, 
  Leaf, 
  Sprout, 
  Flower2,
  ArrowRight,
  ShieldCheck,
  Layers
} from 'lucide-vue-next';

const router = useRouter();

const navigateToDashboard = () => {
  router.push('/dashboard');
};

const features = [
  {
    icon: Leaf,
    title: 'Natural Growth',
    description: 'Watch your savings grow organically with disciplined habits.',
    delay: 0.1
  },
  {
    icon: Sprout,
    title: 'Rooted Insights',
    description: 'Deep analytics that help you understand the core of your spending.',
    delay: 0.2
  },
  {
    icon: Flower2,
    title: 'Blossoming Future',
    description: 'Nurture your financial health for a prosperous tomorrow.',
    delay: 0.3
  }
];

const stats = [
  { label: 'Active Savers', value: '12,500+' },
  { label: 'Wealth Generated', value: '$45M+' },
  { label: 'Trust Score', value: '99.9%' }
];
</script>

<template>
  <div class="min-h-screen bg-[#FDFCF8] text-emerald-950 selection:bg-emerald-200 selection:text-emerald-900 overflow-hidden font-inter">
    <!-- Subtle Grain Texture -->
    <div class="fixed inset-0 pointer-events-none opacity-40 mix-blend-multiply">
      <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-50"></div>
    </div>

    <!-- Navigation -->
    <Motion 
      as="nav"
      :initial="{ y: -20, opacity: 0 }"
      :animate="{ y: 0, opacity: 1 }"
      :transition="{ duration: 0.8, ease: [0.16, 1, 0.3, 1] }"
      class="relative z-50 flex items-center justify-between px-6 py-8 max-w-7xl mx-auto"
    >
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-emerald-900 rounded-full flex items-center justify-center">
            <Layers class="w-4 h-4 text-[#FDFCF8]" />
        </div>
        <span class="text-xl font-serif font-medium tracking-tight text-emerald-950">Spendlytic</span>
      </div>
      <div class="flex items-center gap-8 text-sm font-medium tracking-wide">
        <button @click="navigateToDashboard" class="text-emerald-900/70 hover:text-emerald-900 transition-colors">Log in</button>
        <Button @click="navigateToDashboard" variant="default" class="bg-emerald-900 text-[#FDFCF8] hover:bg-emerald-800 transition-all rounded-full h-11 px-6 text-[11px] font-bold tracking-widest uppercase">
          Start Journey
        </Button>
      </div>
    </Motion>

    <!-- Hero Section -->
    <section class="relative pt-10 pb-32 px-6 max-w-7xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <!-- Text Content -->
        <div class="relative z-10">
            <Motion
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.8, ease: [0.16, 1, 0.3, 1] }"
            >
                <span class="inline-block py-1 px-3 border border-emerald-900/10 rounded-full text-xs font-semibold tracking-wider text-emerald-800 mb-8 uppercase bg-emerald-50">
                    Financial Wellness
                </span>
            </Motion>
            
          <div class="relative">
            <Motion
              as="h1"
              :initial="{ opacity: 0, y: 30 }"
              :animate="{ opacity: 1, y: 0 }"
              :transition="{ duration: 1, delay: 0.1, ease: [0.16, 1, 0.3, 1] }"
              class="text-6xl md:text-7xl lg:text-8xl font-serif font-medium text-emerald-950 mb-8 leading-[1.05]"
            >
              Calm, <br />
              <span class="text-emerald-800 italic">Disciplined</span> <br />
              Wealth.
            </Motion>
          </div>

          <Motion
            as="p"
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ delay: 0.3, duration: 0.8 }"
            class="max-w-md text-lg text-emerald-900/70 mb-12 leading-relaxed font-light"
          >
            Cultivate a relationship with money that brings peace, not stress. 
            Smart insights for a flourishing financial life.
          </Motion>

          <Motion
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ delay: 0.4, duration: 0.8 }"
            class="flex flex-col sm:flex-row gap-5 items-start sm:items-center"
          >
            <Button 
              @click="navigateToDashboard"
              class="h-11 px-10 rounded-full bg-emerald-900 text-[#FDFCF8] hover:bg-emerald-800 transition-all text-[11px] font-bold tracking-[0.2em] uppercase shadow-lg shadow-emerald-900/10"
            >
              Begin Now
            </Button>
            <button 
              @click="() => router.push('/transactions')"
              class="group flex items-center gap-3 text-emerald-900 hover:text-emerald-700 transition-colors py-4 px-4"
            >
              <span class="text-sm font-medium tracking-wide border-b border-emerald-900/30 group-hover:border-emerald-900 transition-all pb-0.5">How it works</span>
              <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </button>
          </Motion>
        </div>

        <!-- Visual/Abstract Art -->
        <div class="relative h-[600px] hidden lg:block">
             <Motion
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                :transition="{ duration: 1.5, ease: [0.16, 1, 0.3, 1] }"
                class="absolute inset-0"
             >
                <!-- Abstract Green Shapes -->
                <div class="absolute top-10 right-10 w-96 h-96 bg-emerald-800/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 left-10 w-80 h-80 bg-emerald-600/5 rounded-full blur-3xl"></div>
                
                <!-- Main Image/Graphic Placeholder - utilizing a clean, editorial composition -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="relative w-full max-w-md aspect-[4/5] bg-emerald-900 rounded-t-[10rem] rounded-b-[2rem] overflow-hidden shadow-2xl shadow-emerald-900/10">
                        <img 
                            src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=1964&auto=format&fit=crop" 
                            alt="Calm Abstract Nature" 
                            class="w-full h-full object-cover opacity-80 mix-blend-overlay hover:scale-105 transition-transform duration-[2s]"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-emerald-950/60 to-transparent"></div>
                        
                        <!-- Floating Card -->
                        <div class="absolute bottom-8 left-8 right-8 bg-[#FDFCF8]/95 backdrop-blur-sm p-6 rounded-2xl border border-emerald-100 shadow-lg">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <div class="text-xs text-emerald-600 uppercase tracking-wider font-bold mb-1">Monthly Growth</div>
                                    <div class="text-3xl font-serif text-emerald-950">+24%</div>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-800">
                                    <TrendingUp class="w-4 h-4" />
                                </div>
                            </div>
                            <div class="w-full bg-emerald-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-800 h-full w-[70%] rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
             </Motion>
        </div>
      </div>
    </section>

    <!-- Editorial Features -->
    <section class="py-24 px-6 border-t border-emerald-900/5">
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
            <Motion
            v-for="(feature, i) in features"
            :key="i"
            :initial="{ opacity: 0, y: 20 }"
            :while-in-view="{ opacity: 1, y: 0 }"
            :viewport="{ once: true }"
            :transition="{ delay: i * 0.2, duration: 0.8 }"
            class="group"
            >
                <div class="w-12 h-12 mb-6 border border-emerald-900/10 rounded-full flex items-center justify-center text-emerald-800 group-hover:bg-emerald-900 group-hover:text-[#FDFCF8] transition-all duration-500 mx-auto">
                    <component :is="feature.icon" class="w-5 h-5" />
                </div>
                <h3 class="text-xl font-serif text-emerald-950 mb-3">{{ feature.title }}</h3>
                <p class="text-emerald-900/60 leading-relaxed font-light">{{ feature.description }}</p>
            </Motion>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-24 bg-emerald-900 text-[#FDFCF8]">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center divide-y md:divide-y-0 md:divide-x divide-emerald-800/50">
                <Motion 
                    v-for="(stat, i) in stats" 
                    :key="i"
                    :initial="{ opacity: 0, scale: 0.9 }"
                    :while-in-view="{ opacity: 1, scale: 1 }"
                    :viewport="{ once: true }"
                    :transition="{ delay: i * 0.1, duration: 0.6 }"
                    class="pt-8 md:pt-0 px-4"
                >
                    <div class="text-5xl md:text-6xl font-serif mb-2">{{ stat.value }}</div>
                    <div class="text-emerald-200/60 uppercase tracking-widest text-xs">{{ stat.label }}</div>
                </Motion>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-32 px-6 text-center max-w-4xl mx-auto">
        <Motion
            :initial="{ opacity: 0, y: 30 }"
            :while-in-view="{ opacity: 1, y: 0 }"
            :viewport="{ once: true }"
            :transition="{ duration: 0.8 }"
        >
            <h2 class="text-4xl md:text-6xl font-serif text-emerald-950 mb-8">Ready to cultivate your wealth?</h2>
            <p class="text-lg text-emerald-900/60 mb-10 font-light">Join a community of disciplined savers building their future.</p>
            <Button 
              @click="navigateToDashboard"
              class="h-12 px-12 rounded-full bg-emerald-950 text-[#FDFCF8] hover:bg-emerald-800 transition-all text-xs font-bold tracking-[0.2em] uppercase shadow-xl hover:shadow-2xl hover:-translate-y-1"
            >
              Start Your Journey
            </Button>
        </Motion>
    </section>

    <!-- Footer -->
    <footer class="border-t border-emerald-900/5 py-12 px-6">
      <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
        <div class="flex items-center gap-2">
          <Layers class="w-5 h-5 text-emerald-900" />
          <span class="font-serif font-medium text-emerald-950">Spendlytic</span>
        </div>
        <div class="flex gap-8 text-sm text-emerald-900/60">
          <a href="#" class="hover:text-emerald-900 transition-colors">Manifesto</a>
          <a href="#" class="hover:text-emerald-900 transition-colors">Architecture</a>
          <a href="#" class="hover:text-emerald-900 transition-colors">Security</a>
        </div>
        <div class="flex items-center gap-2 text-xs text-emerald-900/40">
          <ShieldCheck class="w-4 h-4" />
          <span>Secure. Private. Disciplined.</span>
        </div>
      </div>
    </footer>
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
