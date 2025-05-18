<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full rounded-lg border border-gray-800 bg-[#0B0F1A] px-4 py-2 text-sm text-white placeholder-gray-400 shadow-sm transition-colors focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-2 focus:ring-offset-[#020817] disabled:opacity-50"
        v-model="model"
        ref="input"
    />
</template>
