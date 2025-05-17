<template>
    <div>
        <button
            v-if="as === 'button'"
            :class="classes"
            @click="$emit('click', $event)"
        >
            <slot />
        </button>

        <Link
            v-else
            :href="href"
            :class="classes"
            :method="method"
            as="button"
        >
            <slot />
        </Link>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    active: {
        type: Boolean,
        default: false,
    },
    href: {
        type: String,
        default: '',
    },
    as: {
        type: String,
        default: 'link',
    },
    method: {
        type: String,
        default: 'get',
    },
});

const classes = computed(() => {
    return {
        'block w-full pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium focus:outline-none transition duration-150 ease-in-out': true,
        'border-indigo-400 text-indigo-700 bg-indigo-50 focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700': props.active,
        'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300': !props.active,
    };
});

defineEmits(['click']);
</script>
