<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['small', 'default', 'large'].includes(value),
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center gap-2 rounded-lg border border-border-primary bg-background-secondary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-background-primary hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-2 focus:ring-offset-background-primary active:bg-background-primary disabled:pointer-events-none disabled:opacity-50"
        :class="{
            'px-3 py-2 text-xs': size === 'small',
            'px-4 py-2.5 text-sm': size === 'default',
            'px-5 py-3 text-base': size === 'large',
        }"
    >
        <slot />
    </button>
</template>
