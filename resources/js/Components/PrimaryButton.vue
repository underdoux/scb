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
        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-b from-blue-500 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition-all duration-200 hover:from-blue-600 hover:to-blue-700 hover:shadow-blue-600/30 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-2 focus:ring-offset-background-primary active:from-blue-700 active:to-blue-800 disabled:pointer-events-none disabled:opacity-50 dark:focus:ring-offset-background-primary"
        :class="{
            'px-3 py-2 text-xs': size === 'small',
            'px-4 py-2.5 text-sm': size === 'default',
            'px-5 py-3 text-base': size === 'large',
        }"
    >
        <slot />
    </button>
</template>
