<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white bg-blue-600 shadow-lg shadow-blue-600/20 transition-all duration-200'
        : 'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-400 transition-all duration-200 hover:bg-blue-600/10 hover:text-white hover:shadow-lg hover:shadow-blue-600/5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-2 focus:ring-offset-background-primary active:bg-blue-600/20'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
