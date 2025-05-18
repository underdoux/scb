<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';
import { Sun, Moon } from 'lucide-vue-next';

const { isDark, toggleTheme } = useTheme();
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-white pt-6 transition-colors duration-200 dark:bg-background-primary sm:justify-center sm:pt-0">
        <!-- Theme Toggle -->
        <button 
            @click="toggleTheme"
            class="fixed right-4 top-4 flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition-colors duration-200 hover:border-gray-300 hover:text-gray-700 dark:border-border-primary dark:bg-background-secondary dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-white"
            :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        >
            <Sun v-if="isDark" class="h-5 w-5" />
            <Moon v-else class="h-5 w-5" />
        </button>

        <div>
            <Link href="/" class="flex items-center gap-3">
                <ApplicationLogo />
                <span class="text-2xl font-bold text-gray-900 transition-colors duration-200 dark:text-white">Dashboard</span>
            </Link>
        </div>

        <div class="mt-6 w-full overflow-hidden rounded-lg border border-gray-200 bg-white px-8 py-6 shadow-xl transition-colors duration-200 dark:border-border-primary dark:bg-background-secondary sm:max-w-md">
            <slot />
        </div>
    </div>
</template>
