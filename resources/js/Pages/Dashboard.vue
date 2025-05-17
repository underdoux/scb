<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 mb-1">Total Posts</div>
                        <div class="text-2xl font-bold">{{ stats.total_posts }}</div>
                        <div class="mt-2 flex items-center text-sm">
                            <div :class="stats.posts_trend >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ stats.posts_trend >= 0 ? '+' : '' }}{{ stats.posts_trend }}%
                            </div>
                            <span class="text-gray-500 ml-2">vs last week</span>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 mb-1">Scheduled Posts</div>
                        <div class="text-2xl font-bold">{{ stats.scheduled_posts }}</div>
                        <div class="mt-2 text-sm text-gray-500">
                            Next: {{ stats.next_scheduled || 'None' }}
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 mb-1">Success Rate</div>
                        <div class="text-2xl font-bold text-green-600">{{ stats.success_rate }}%</div>
                        <div class="mt-2 text-sm text-gray-500">
                            Last 7 days
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 mb-1">Connected Accounts</div>
                        <div class="text-2xl font-bold">{{ stats.connected_accounts }}</div>
                        <div class="mt-2 text-sm text-gray-500">
                            Across {{ stats.platforms }} platforms
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Recent Posts -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Posts</h3>
                            <Link
                                :href="route('posts.index')"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                View All
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="post in recentPosts" :key="post.id" class="flex items-center justify-between border-b pb-4 last:border-0">
                                <div>
                                    <div class="text-sm font-medium">
                                        {{ truncate(post.content, 50) }}
                                    </div>
                                    <div class="flex items-center mt-1">
                                        <span :class="platformClasses(post.platform)" class="text-xs px-2 py-1 rounded-full">
                                            {{ post.platform }}
                                        </span>
                                        <span :class="statusClasses(post.status)" class="ml-2 text-xs px-2 py-1 rounded-full">
                                            {{ post.status }}
                                        </span>
                                    </div>
                                </div>
                                <Link
                                    :href="route('posts.edit', post.id)"
                                    class="text-sm text-gray-600 hover:text-gray-900"
                                >
                                    View
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                            <Link
                                :href="route('logs.index')"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                View All
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="log in recentLogs" :key="log.id" class="flex items-start space-x-3 border-b pb-4 last:border-0">
                                <div :class="logTypeClasses(log.type)" class="w-2 h-2 mt-2 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="text-sm">{{ log.message }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ log.created_at }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Schedule -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Upcoming Schedule</h3>
                            <Link
                                :href="route('posts.create')"
                                class="text-sm text-indigo-600 hover:text-indigo-900"
                            >
                                Schedule New Post
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="schedule in upcomingSchedules" :key="schedule.id" class="flex items-center justify-between border-b pb-4 last:border-0">
                                <div>
                                    <div class="text-sm font-medium">
                                        {{ truncate(schedule.post.content, 50) }}
                                    </div>
                                    <div class="flex items-center mt-1">
                                        <span :class="platformClasses(schedule.post.platform)" class="text-xs px-2 py-1 rounded-full">
                                            {{ schedule.post.platform }}
                                        </span>
                                        <span class="ml-2 text-xs text-gray-500">
                                            {{ schedule.scheduled_time }}
                                        </span>
                                    </div>
                                </div>
                                <Link
                                    :href="route('posts.edit', schedule.post.id)"
                                    class="text-sm text-gray-600 hover:text-gray-900"
                                >
                                    Edit
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Status -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Platform Status</h3>
                            <Link
                                :href="route('social-accounts.index')"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Manage Accounts
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="platform in platformStatus" :key="platform.name" class="flex items-center justify-between border-b pb-4 last:border-0">
                                <div class="flex items-center">
                                    <div :class="platformClasses(platform.name.toLowerCase())" class="w-8 h-8 rounded-full flex items-center justify-center">
                                        <i :class="platformIcon(platform.name.toLowerCase())" class="text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium">{{ platform.name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ platform.connected ? platform.username : 'Not Connected' }}
                                        </div>
                                    </div>
                                </div>
                                <div :class="platform.connected ? 'text-green-600' : 'text-red-600'" class="text-xs font-medium">
                                    {{ platform.connected ? '●' : '○' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stats: Object,
    recentPosts: Array,
    recentLogs: Array,
    upcomingSchedules: Array,
    platformStatus: Array,
});

const truncate = (text, length) => {
    return text.length > length ? text.substring(0, length) + '...' : text;
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
    return classes[platform.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

const platformIcon = (platform) => {
    const icons = {
        facebook: 'fab fa-facebook',
        instagram: 'fab fa-instagram',
        twitter: 'fab fa-twitter',
        linkedin: 'fab fa-linkedin',
        tiktok: 'fab fa-tiktok',
        youtube: 'fab fa-youtube',
    };
    return icons[platform.toLowerCase()] || 'fas fa-globe';
};

const statusClasses = (status) => {
    const classes = {
        published: 'bg-green-100 text-green-800',
        scheduled: 'bg-yellow-100 text-yellow-800',
        draft: 'bg-gray-100 text-gray-800',
        failed: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const logTypeClasses = (type) => {
    const classes = {
        info: 'bg-blue-500',
        warning: 'bg-yellow-500',
        error: 'bg-red-500',
        success: 'bg-green-500',
    };
    return classes[type] || 'bg-gray-500';
};
</script>
