<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-background-primary pt-6 sm:justify-center sm:pt-0">
        <div>
            <Link href="/" class="flex items-center gap-3">
                <ApplicationLogo />
                <span class="text-2xl font-bold text-white">Dashboard</span>
            </Link>
        </div>

        <div class="mt-6 w-full overflow-hidden rounded-lg border border-border-primary bg-background-secondary px-8 py-6 shadow-xl sm:max-w-md">
            <slot />
        </div>
    </div>
</template>
