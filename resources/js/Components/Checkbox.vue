<script setup>
import { computed } from 'vue';
import { Check } from 'lucide-vue-next';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        type: String,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },
    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <div class="relative flex items-start">
        <div class="flex h-5 items-center">
            <input
                v-model="proxyChecked"
                type="checkbox"
                :value="value"
                :disabled="disabled"
                class="peer h-4 w-4 cursor-pointer appearance-none rounded border border-border-primary bg-background-primary transition-colors checked:border-blue-600 checked:bg-blue-600 hover:bg-background-secondary focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-2 focus:ring-offset-background-primary disabled:cursor-not-allowed disabled:opacity-50"
            />
            <Check class="pointer-events-none absolute h-4 w-4 text-white opacity-0 peer-checked:opacity-100" />
        </div>
        <div class="ml-2 text-sm">
            <slot />
        </div>
    </div>
</template>
