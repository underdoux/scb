<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: {
        type: [String, Number],
        required: true,
    },
    type: {
        type: String,
        default: 'text',
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:modelValue']);

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
        ref="input"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :type="type"
        :placeholder="placeholder"
        :disabled="disabled"
        class="block w-full rounded-lg border border-border-primary bg-background-primary px-4 py-2.5 text-sm text-white placeholder-gray-400 shadow-sm transition-colors duration-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-2 focus:ring-offset-background-primary disabled:cursor-not-allowed disabled:opacity-50"
    />
</template>
