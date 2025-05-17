<template>
    <div>
        <button
            v-if="as === 'button'"
            type="submit"
            class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
            @click="$emit('click', $event)"
        >
            <slot />
        </button>

        <Link
            v-else
            :href="href"
            :method="method"
            :as="as"
            class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
        >
            <slot />
        </Link>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        default: '',
    },
    method: {
        type: String,
        default: 'get',
    },
    as: {
        type: String,
        default: 'link',
    },
});

defineEmits(['click']);
</script>
