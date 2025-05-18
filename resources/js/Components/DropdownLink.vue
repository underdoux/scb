<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-400 transition duration-150 ease-in-out hover:bg-blue-600/10 hover:text-white focus:outline-none"
    >
        <slot />
    </Link>
</template>
