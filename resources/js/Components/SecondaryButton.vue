<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-lg border border-gray-800 bg-[#0B0F1A] px-4 py-2 text-sm font-semibold text-gray-300 shadow-sm transition duration-150 ease-in-out hover:bg-gray-800/50 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-2 focus:ring-offset-[#020817] disabled:opacity-50"
    >
        <slot />
    </button>
</template>
