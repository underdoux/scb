<script setup>
import Navigation from '@/Components/Navigation.vue';
import { useTheme } from '@/Composables/useTheme';

const { isDark } = useTheme();
</script>

<template>
    <div class="min-h-screen bg-white transition-colors duration-200 dark:bg-background-primary">
        <Navigation />
        
        <!-- Main Content -->
        <div class="pl-0 pt-16">
            <!-- Page Content -->
            <main class="min-h-[calc(100vh-4rem)]">
                <!-- Page Heading -->
                <header v-if="$slots.header" class="border-b border-gray-200 bg-white transition-colors duration-200 dark:border-border-primary dark:bg-background-secondary px-6 py-6">
                    <div class="mx-auto max-w-7xl">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <div class="mx-auto max-w-7xl px-6 py-6">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
