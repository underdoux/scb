<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
    as: {
        type: String,
        default: 'a',
    },
    method: {
        type: String,
        default: 'get',
    },
});

const classes = computed(() => {
    return props.active
        ? 'block w-full pl-3 pr-4 py-2 border-l-4 border-indigo-400 text-left text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out dark:text-indigo-300 dark:bg-indigo-900/50'
        : 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700';
});
</script>

<template>
    <div>
        <button v-if="as === 'button'" :class="classes" v-bind="$attrs">
            <slot />
        </button>

        <Link v-else :href="href" :method="method" :class="classes">
            <slot />
        </Link>
    </div>
</template>
