<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap -mb-1">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="mr-1 mb-1 px-4 py-2 text-sm font-medium text-gray-500 border border-gray-800 rounded-lg bg-[#0B0F1A]"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="mr-1 mb-1 px-4 py-2 text-sm font-medium border border-gray-800 rounded-lg transition-colors"
                :class="{
                    'bg-blue-600 border-transparent text-white': link.active,
                    'text-gray-400 hover:text-white hover:bg-blue-600/10 bg-[#0B0F1A]': !link.active
                }"
                v-html="link.label"
            />
        </template>
    </div>
</template>
