<script setup>
defineProps({
    value: {
        type: String,
    },
    required: {
        type: Boolean,
        default: false,
    },
    optional: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <label class="block text-sm font-medium">
        <span class="flex items-center gap-1 text-white">
            {{ value || slot }}
            <span v-if="required" class="text-red-500">*</span>
            <span v-if="optional" class="text-xs font-normal text-gray-400">(optional)</span>
        </span>
        <slot name="description">
            <p v-if="$slots.description" class="mt-1 text-sm text-gray-400">
                <slot name="description" />
            </p>
        </slot>
    </label>
</template>
