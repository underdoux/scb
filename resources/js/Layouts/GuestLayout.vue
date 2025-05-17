<template>
    <div class="min-h-screen flex flex-col items-center pt-6 sm:justify-center sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-6">
                <Link href="/" class="flex items-center justify-center">
                    <ApplicationLogo />
                </Link>
            </div>
            
            <slot />
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>
