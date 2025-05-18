<script setup>
import { computed } from 'vue';
import { AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    message: {
        type: String,
    },
    messages: {
        type: Array,
        default: () => [],
    },
});

const hasError = computed(() => props.message || props.messages.length > 0);
const allMessages = computed(() => {
    if (props.message) return [props.message];
    return props.messages;
});
</script>

<template>
    <div v-if="hasError" class="mt-1">
        <div v-for="(message, index) in allMessages" :key="index" class="flex items-center gap-1.5">
            <AlertCircle class="h-4 w-4 text-red-500" />
            <p class="text-sm font-medium text-red-500">{{ message }}</p>
        </div>
    </div>
</template>
