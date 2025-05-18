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
        ? 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white bg-blue-600 transition-colors'
        : 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-400 transition-colors hover:bg-blue-600/10 hover:text-white'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
