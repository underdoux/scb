<script setup>
import Navigation from '@/Components/Navigation.vue';
</script>

<template>
    <div class="min-h-screen bg-background-primary">
        <Navigation />
        
        <!-- Main Content -->
        <div class="pl-0 pt-16">
            <!-- Page Content -->
            <main class="min-h-[calc(100vh-4rem)]">
                <!-- Page Heading -->
                <header v-if="$slots.header" class="border-b border-border-primary bg-background-secondary px-6 py-6">
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
