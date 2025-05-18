<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    users: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Users Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                Users Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-[#0B0F1A] shadow-sm sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-800">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">
                                            Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">
                                            Role
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">
                                            Created At
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ user.name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ user.email }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span v-if="user.is_admin" class="rounded-full bg-blue-600 px-2 py-1 text-xs">
                                                Admin
                                            </span>
                                            <span v-else class="rounded-full bg-gray-600 px-2 py-1 text-xs">
                                                User
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ new Date(user.created_at).toLocaleDateString() }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
