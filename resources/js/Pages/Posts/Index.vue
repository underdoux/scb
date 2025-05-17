<template>
    <AppLayout title="Posts">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Posts
                </h2>
                <Link :href="route('posts.create')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Create Post
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <form @submit.prevent="filterPosts" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Platform Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Platform</label>
                                <select v-model="filters.platform" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Platforms</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="twitter">Twitter</option>
                                    <option value="linkedin">LinkedIn</option>
                                    <option value="tiktok">TikTok</option>
                                    <option value="youtube">YouTube</option>
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select v-model="filters.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="published">Published</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>

                            <!-- Search -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Search</label>
                                <input type="text" v-model="filters.search" placeholder="Search posts..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Date Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">From Date</label>
                                    <input type="date" v-model="filters.from_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">To Date</label>
                                    <input type="date" v-model="filters.to_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            <!-- Sort Options -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sort By</label>
                                    <select v-model="filters.sort_by" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="created_at">Created Date</option>
                                        <option value="scheduled_time">Scheduled Time</option>
                                        <option value="platform">Platform</option>
                                        <option value="status">Status</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sort Direction</label>
                                    <select v-model="filters.sort_direction" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="desc">Descending</option>
                                        <option value="asc">Ascending</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <button type="button" @click="resetFilters" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Reset
                            </button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Posts List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div v-if="posts.data.length === 0" class="text-center py-8 text-gray-500">
                            No posts found.
                        </div>
                        <div v-else class="space-y-6">
                            <div v-for="post in posts.data" :key="post.id" class="border rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center space-x-4 mb-4">
                                            <!-- Platform Badge -->
                                            <span :class="platformClasses(post.platform)" class="px-3 py-1 rounded-full text-xs font-semibold">
                                                {{ post.platform }}
                                            </span>
                                            <!-- Status Badge -->
                                            <span :class="statusClasses(post.status)" class="px-3 py-1 rounded-full text-xs font-semibold">
                                                {{ post.status }}
                                            </span>
                                        </div>
                                        <!-- Content Preview -->
                                        <p class="text-gray-600 mb-2 line-clamp-2">{{ post.content }}</p>
                                        <!-- Hashtags -->
                                        <p v-if="post.hashtags" class="text-blue-500 text-sm">{{ post.hashtags }}</p>
                                    </div>
                                    <!-- Actions -->
                                    <div class="flex space-x-2">
                                        <Link v-if="!post.isPublished" :href="route('posts.edit', post.id)" class="inline-flex items-center px-3 py-1 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                            Edit
                                        </Link>
                                        <button v-if="post.isScheduled" @click="cancelSchedule(post)" class="inline-flex items-center px-3 py-1 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                                <!-- Schedule Info -->
                                <div v-if="post.schedule" class="mt-4 text-sm text-gray-500">
                                    Scheduled for: {{ formatDate(post.schedule.scheduled_time) }}
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="posts.data.length > 0" class="mt-6">
                            <Pagination :links="posts.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    posts: Object,
    filters: Object,
});

const filters = ref({
    platform: props.filters?.platform || '',
    status: props.filters?.status || '',
    search: props.filters?.search || '',
    from_date: props.filters?.from_date || '',
    to_date: props.filters?.to_date || '',
    sort_by: props.filters?.sort_by || 'created_at',
    sort_direction: props.filters?.sort_direction || 'desc',
});

const filterPosts = () => {
    router.get(route('posts.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.value = {
        platform: '',
        status: '',
        search: '',
        from_date: '',
        to_date: '',
        sort_by: 'created_at',
        sort_direction: 'desc',
    };
    filterPosts();
};

const cancelSchedule = (post) => {
    if (confirm('Are you sure you want to cancel this scheduled post?')) {
        router.post(route('posts.cancel-schedule', post.id), {}, {
            preserveScroll: true,
        });
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleString();
};

const platformClasses = (platform) => {
    const classes = {
        facebook: 'bg-blue-100 text-blue-800',
        instagram: 'bg-pink-100 text-pink-800',
        twitter: 'bg-sky-100 text-sky-800',
        linkedin: 'bg-blue-100 text-blue-800',
        tiktok: 'bg-gray-100 text-gray-800',
        youtube: 'bg-red-100 text-red-800',
    };
    return classes[platform] || 'bg-gray-100 text-gray-800';
};

const statusClasses = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        scheduled: 'bg-yellow-100 text-yellow-800',
        published: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

// Debounce search input
let searchTimeout;
watch(() => filters.value.search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filterPosts();
    }, 300);
});
</script>
