<template>
    <AppLayout title="Analytics">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Analytics
                </h2>
                <Link
                    :href="route('analytics.export', filters)"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                >
                    Export Data
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <form @submit.prevent="filterData" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Platform Filter -->
                                <div>
                                    <InputLabel for="platform" value="Platform" />
                                    <select
                                        id="platform"
                                        v-model="filters.platform"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All Platforms</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="instagram">Instagram</option>
                                        <option value="twitter">Twitter</option>
                                        <option value="linkedin">LinkedIn</option>
                                        <option value="tiktok">TikTok</option>
                                        <option value="youtube">YouTube</option>
                                    </select>
                                </div>

                                <!-- Date Range -->
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

                <!-- Overview Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Total Posts</h3>
                        <div class="text-3xl font-bold">{{ stats.total_posts }}</div>
                        <div class="mt-2 text-sm text-gray-500">
                            {{ stats.avg_posts_per_day.average }} posts/day
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Success Rate</h3>
                        <div class="text-3xl font-bold text-green-600">
                            {{ stats.success_rate.percentage }}%
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            {{ stats.success_rate.successful }} of {{ stats.success_rate.total }} posts
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Active Days</h3>
                        <div class="text-3xl font-bold">{{ stats.avg_posts_per_day.days_active }}</div>
                        <div class="mt-2 text-sm text-gray-500">
                            Days of activity
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Error Rate</h3>
                        <div class="text-3xl font-bold text-red-600">
                            {{ stats.error_stats.total_errors }}
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            Total errors
                        </div>
                    </div>
                </div>

                <!-- Detailed Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Posts by Platform -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Posts by Platform</h3>
                        <div class="space-y-4">
                            <div v-for="(count, platform) in stats.posts_by_platform" :key="platform" class="flex items-center">
                                <div class="w-24 text-sm">{{ platform }}</div>
                                <div class="flex-1">
                                    <div class="h-4 bg-gray-100 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-blue-500"
                                            :style="{ width: `${(count / stats.total_posts) * 100}%` }"
                                        ></div>
                                    </div>
                                </div>
                                <div class="w-16 text-right text-sm">{{ count }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Posts by Status -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Posts by Status</h3>
                        <div class="space-y-4">
                            <div v-for="(count, status) in stats.posts_by_status" :key="status" class="flex items-center">
                                <div class="w-24 text-sm">{{ status }}</div>
                                <div class="flex-1">
                                    <div class="h-4 bg-gray-100 rounded-full overflow-hidden">
                                        <div
                                            :class="statusBarColor(status)"
                                            class="h-full"
                                            :style="{ width: `${(count / stats.total_posts) * 100}%` }"
                                        ></div>
                                    </div>
                                </div>
                                <div class="w-16 text-right text-sm">{{ count }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Most Active Times -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Most Active Times</h3>
                        <div class="space-y-4">
                            <div v-for="time in stats.most_active_times" :key="time.hour" class="flex items-center">
                                <div class="w-24 text-sm">{{ time.hour }}</div>
                                <div class="flex-1">
                                    <div class="h-4 bg-gray-100 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-green-500"
                                            :style="{ width: `${(time.count / Math.max(...stats.most_active_times.map(t => t.count))) * 100}%` }"
                                        ></div>
                                    </div>
                                </div>
                                <div class="w-16 text-right text-sm">{{ time.count }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Common Errors -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Common Errors</h3>
                        <div class="space-y-4">
                            <div v-for="error in stats.error_stats.common_errors" :key="error.message" class="flex items-center">
                                <div class="flex-1 text-sm truncate" :title="error.message">
                                    {{ error.message }}
                                </div>
                                <div class="w-16 text-right text-sm">{{ error.count }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    stats: Object,
    filters: Object,
});

const filters = ref({
    platform: props.filters?.platform || '',
    from_date: props.filters?.from_date || '',
    to_date: props.filters?.to_date || '',
});

const filterData = () => {
    router.get(route('analytics.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.value = {
        platform: '',
        from_date: '',
        to_date: '',
    };
    filterData();
};

const statusBarColor = (status) => {
    const colors = {
        published: 'bg-green-500',
        scheduled: 'bg-yellow-500',
        draft: 'bg-gray-500',
        failed: 'bg-red-500',
    };
    return colors[status] || 'bg-gray-500';
};
</script>
