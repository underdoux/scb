<script setup>
import { CheckCircle, AlertCircle, XCircle, Info } from 'lucide-vue-next';

const props = defineProps({
    type: {
        type: String,
        default: 'default'
    },
    title: {
        type: String,
        required: true
    },
    message: {
        type: String,
        default: ''
    }
});

const iconMap = {
    success: CheckCircle,
    error: XCircle,
    warning: AlertCircle,
    info: Info,
    default: Info
};

const colorMap = {
    success: 'text-green-400',
    error: 'text-red-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400',
    default: 'text-blue-400'
};
</script>

<template>
    <div class="flex items-start gap-3 rounded-lg border border-border-primary bg-background-secondary p-4 shadow-lg">
        <component 
            :is="iconMap[type]" 
            :class="[colorMap[type], 'h-5 w-5 shrink-0']" 
        />
        <div class="flex-1 space-y-1">
            <h3 class="text-sm font-medium text-white">
                {{ title }}
            </h3>
            <p v-if="message" class="text-sm text-gray-400">
                {{ message }}
            </p>
        </div>
    </div>
</template>
