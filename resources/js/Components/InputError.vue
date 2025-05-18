<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="mt-1 text-sm font-medium text-red-500">
            {{ message }}
        </p>
    </div>
</template>
