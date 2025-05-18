<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        default: 'get',
    },
    as: {
        type: String,
        default: 'a',
    },
});
</script>

<template>
    <Link
        :href="href"
        :method="method"
        :as="as"
        class="block w-full px-4 py-2.5 text-start text-sm font-medium text-gray-400 transition-all duration-200 hover:bg-blue-600/10 hover:text-white hover:shadow-sm focus:bg-blue-600/5 focus:text-white focus:outline-none active:bg-blue-600/20"
    >
        <slot />
    </Link>
</template>
