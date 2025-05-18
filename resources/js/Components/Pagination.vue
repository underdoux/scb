<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-vue-next';

const props = defineProps({
    links: {
        type: Array,
        required: true,
    },
});

const hasPages = computed(() => props.links.length > 3);

const normalizedLinks = computed(() => {
    return props.links.map(link => ({
        ...link,
        label: link.label.replace('&laquo;', '').replace('&raquo;', ''),
        isNext: link.label.includes('Next'),
        isPrev: link.label.includes('Previous'),
    }));
});
</script>

<template>
    <div v-if="hasPages" class="flex items-center justify-between">
        <div class="flex flex-1 items-center justify-between sm:hidden">
            <Link
                v-for="link in [normalizedLinks[0], normalizedLinks[normalizedLinks.length - 1]]"
                :key="link.label"
                :href="link.url"
                v-show="link.url"
                class="relative inline-flex items-center rounded-lg border border-border-primary bg-background-secondary px-4 py-2 text-sm font-medium text-white transition-colors hover:border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-2 focus:ring-offset-background-primary disabled:pointer-events-none disabled:opacity-50"
                :class="{ 'pointer-events-none opacity-50': !link.url }"
            >
                {{ link.label }}
            </Link>
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-lg shadow-sm" aria-label="Pagination">
                    <template v-for="(link, index) in normalizedLinks" :key="index">
                        <!-- Previous/Next buttons -->
                        <Link
                            v-if="link.isPrev || link.isNext"
                            :href="link.url"
                            :class="[
                                'relative inline-flex items-center gap-1 border border-border-primary bg-background-secondary px-3 py-2 text-sm font-medium text-white transition-colors hover:border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-2 focus:ring-offset-background-primary disabled:pointer-events-none disabled:opacity-50',
                                { 'rounded-l-lg': link.isPrev },
                                { 'rounded-r-lg': link.isNext },
                                { 'pointer-events-none opacity-50': !link.url }
                            ]"
                        >
                            <ChevronLeft v-if="link.isPrev" class="h-4 w-4" />
                            <ChevronRight v-if="link.isNext" class="h-4 w-4" />
                            <span class="sr-only">{{ link.label }}</span>
                        </Link>

                        <!-- Ellipsis -->
                        <span
                            v-else-if="link.label === '...'"
                            class="relative inline-flex items-center border border-border-primary bg-background-secondary px-4 py-2 text-sm font-medium text-gray-400"
                        >
                            <MoreHorizontal class="h-4 w-4" />
                        </span>

                        <!-- Page numbers -->
                        <Link
                            v-else
                            :href="link.url"
                            :class="[
                                'relative inline-flex items-center border border-border-primary px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-2 focus:ring-offset-background-primary disabled:pointer-events-none disabled:opacity-50',
                                {
                                    'z-10 border-blue-600 bg-blue-600 text-white': link.active,
                                    'bg-background-secondary text-white hover:border-gray-700': !link.active,
                                    'pointer-events-none opacity-50': !link.url
                                }
                            ]"
                        >
                            {{ link.label }}
                        </Link>
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>
