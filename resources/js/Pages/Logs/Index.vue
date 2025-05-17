<template>
    <AppLayout title="Activity Logs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Activity Logs
                </h2>
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('logs.export', filters)"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                    >
                        Export CSV
                    </Link>
                    <DangerButton
                        @click="confirmClear"
                        v-if="stats.total > 0"
                    >
                        Clear All Logs
                    </DangerButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistics -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-gray-600">Total Logs</div>
                        <div class="text-2xl font-semibold">{{ stats.total }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-green-600">Success</div>
                        <div class="text-2xl font-semibold text-green-600">{{ stats.success }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-red-600">Errors</div>
                        <div class="text-2xl font-semibold text-red-600">{{ stats.error }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-yellow-600">Warnings</div>
                        <div class="text-2xl font-semibold text-yellow-600">{{ stats.warning }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-blue-600">Info</div>
                        <div class="text-2xl font-semibold text-blue-600">{{ stats.info }}</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <form @submit.prevent="filterLogs" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Type Filter -->
                                <div>
                                    <InputLabel for="type" value="Log Type" />
                                    <select
                                        id="type"
                                        v-model="filters.type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All Types</option>
                                        <option value="info">Info</option>
                                        <option value="warning">Warning</option>
                                        <option value="error">Error</option>
                                        <option value="success">Success</option>
                                    </select>
                                </div>

                                <!-- Search -->
                                <div>
                                    <InputLabel for="search" value="Search" />
                                    <input
                                        id="search"
                                        type="text"
                                        v-model="filters.search"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Search logs..."
                                    >
                                </div>

                                <!-- Date Range -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="from_date" value="From Date" />
                                        <input
                                            id="from_date"
                                            type="date"
                                            v-model="filters.from_date"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div>
                                        <InputLabel for="to_date" value="To Date" />
                                        <input
                                            id="to_date"
                                            type="date"
                                            v-model="filters.to_date"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-4">
                                <SecondaryButton type="button" @click="resetFilters">
                                    Reset
                                </SecondaryButton>
                                <PrimaryButton type="submit">
                                    Apply Filters
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Logs List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="logs.data.length === 0" class="text-center py-8 text-gray-500">
                            No logs found.
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="log in logs.data" :key="log.id" class="border rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-4">
                                        <!-- Log Type Badge -->
                                        <span :class="logTypeClasses(log.type)" class="px-2 py-1 rounded-full text-xs font-semibold">
                                            {{ log.type }}
                                        </span>
                                        <!-- Log Content -->
                                        <div>
                                            <p class="text-gray-900">{{ log.message }}</p>
                                            <div v-if="log.context" class="mt-2">
                                                <pre class="text-xs text-gray-600 bg-gray-50 p-2 rounded">{{ JSON.stringify(log.context, null, 2) }}</pre>
                                            </div>
                                            <!-- Post Link if available -->
                                            <Link
                                                v-if="log.post"
                                                :href="route('posts.edit', log.post.id)"
                                                class="inline-flex items-center mt-2 text-sm text-gray-600 hover:text-gray-900"
                                            >
                                                View Post ({{ log.post.platform }} - {{ log.post.status }})
                                            </Link>
                                        </div>
                                    </div>
                                    <!-- Timestamp -->
                                    <div class="text-sm text-gray-500" :title="log.created_at_formatted">
                                        {{ log.created_at }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="logs.data.length > 0" class="mt-6">
                            <Pagination :links="logs.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clear Logs Confirmation Modal -->
        <Modal :show="showClearModal" @close="closeClearModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Clear All Logs?
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to clear all logs? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="closeClearModal">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        :class="{ 'opacity-25': clearing }"
                        :disabled="clearing"
                        @click="clearLogs"
                    >
                        Clear All Logs
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    logs: Object,
    filters: Object,
    stats: Object,
});

const filters = ref({
    type: props.filters?.type || '',
    search: props.filters?.search || '',
    from_date: props.filters?.from_date || '',
    to_date: props.filters?.to_date || '',
});

const showClearModal = ref(false);
const clearing = ref(false);

const filterLogs = () => {
    router.get(route('logs.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.value = {
        type: '',
        search: '',
        from_date: '',
        to_date: '',
    };
    filterLogs();
};

const logTypeClasses = (type) => {
    const classes = {
        info: 'bg-blue-100 text-blue-800',
        warning: 'bg-yellow-100 text-yellow-800',
        error: 'bg-red-100 text-red-800',
        success: 'bg-green-100 text-green-800',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};

const confirmClear = () => {
    showClearModal.value = true;
};

const closeClearModal = () => {
    showClearModal.value = false;
};

const clearLogs = () => {
    clearing.value = true;
    router.delete(route('logs.clear'), {
        onFinish: () => {
            clearing.value = false;
            showClearModal.value = false;
        },
    });
};
</script>
