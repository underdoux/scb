<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    settings: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Admin Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                Admin Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-[#0B0F1A] shadow-sm sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="space-y-6">
                            <!-- Site Name -->
                            <div>
                                <h3 class="text-lg font-medium">Site Settings</h3>
                                <div class="mt-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium">Site Name</p>
                                            <p class="text-sm text-gray-400">{{ settings.site_name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Maintenance Mode -->
                            <div>
                                <h3 class="text-lg font-medium">System Status</h3>
                                <div class="mt-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium">Maintenance Mode</p>
                                            <p class="text-sm text-gray-400">
                                                {{ settings.maintenance_mode ? 'Enabled' : 'Disabled' }}
                                            </p>
                                        </div>
                                        <span
                                            :class="[
                                                'px-2 py-1 text-xs rounded-full',
                                                settings.maintenance_mode
                                                    ? 'bg-yellow-600'
                                                    : 'bg-green-600'
                                            ]"
                                        >
                                            {{ settings.maintenance_mode ? 'Maintenance' : 'Active' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
