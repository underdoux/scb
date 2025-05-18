<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    Menu,
    X,
    Plus,
    ChevronDown,
    LayoutDashboard,
    FileText,
    Share2,
    BarChart2,
    ClipboardList,
    Settings,
    Bell,
    Search,
    User,
    LogOut
} from 'lucide-vue-next';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const showingNavigationDropdown = ref(false);
const searchQuery = ref('');

const navigation = [
    { name: 'Dashboard', href: route('dashboard'), icon: LayoutDashboard },
    { name: 'Posts', href: route('posts.index'), icon: FileText },
    { name: 'Social Accounts', href: route('social-accounts.index'), icon: Share2 },
    { name: 'Analytics', href: route('analytics.index'), icon: BarChart2 },
    { name: 'Logs', href: route('logs.index'), icon: ClipboardList },
    { name: 'Settings', href: route('settings.index'), icon: Settings }
];
</script>

<template>
    <div>
        <!-- Top Navigation Bar -->
        <nav class="fixed top-0 z-30 w-full border-b border-border-primary bg-background-secondary">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <Link :href="route('dashboard')" class="flex items-center gap-2">
                            <ApplicationLogo />
                            <span class="text-lg font-semibold text-white">Dashboard</span>
                        </Link>
                    </div>

                    <!-- Search Bar -->
                    <div class="hidden flex-1 items-center px-16 lg:flex">
                        <div class="relative w-full max-w-xl">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search..."
                                class="h-10 w-full rounded-lg border border-border-primary bg-background-primary pl-10 pr-4 text-sm text-white placeholder-gray-400 focus:border-blue-600 focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Right Side Navigation -->
                    <div class="flex items-center gap-4">
                        <!-- Create Post Button -->
                        <Link
                            :href="route('posts.create')"
                            class="hidden items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 sm:inline-flex"
                        >
                            <Plus class="h-4 w-4" />
                            Create Post
                        </Link>

                        <!-- Notifications -->
                        <button class="relative text-gray-400 hover:text-white">
                            <Bell class="h-5 w-5" />
                            <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-blue-600 text-xs text-white">
                                2
                            </span>
                        </button>

                        <!-- User Menu -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-2 rounded-lg border border-border-primary bg-background-primary px-3 py-2 text-sm font-medium text-white transition-colors hover:border-gray-700">
                                    <span>{{ $page.props.auth.user.name }}</span>
                                    <ChevronDown class="h-4 w-4" />
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')" class="flex items-center gap-2">
                                    <User class="h-4 w-4" />
                                    Profile
                                </DropdownLink>
                                <DropdownLink :href="route('settings.index')" class="flex items-center gap-2">
                                    <Settings class="h-4 w-4" />
                                    Settings
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button" class="flex w-full items-center gap-2">
                                    <LogOut class="h-4 w-4" />
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>

                        <!-- Mobile Menu Button -->
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 transition-colors hover:bg-blue-600/10 hover:text-white focus:outline-none lg:hidden"
                        >
                            <Menu v-show="!showingNavigationDropdown" class="h-6 w-6" />
                            <X v-show="showingNavigationDropdown" class="h-6 w-6" />
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Mobile Navigation Menu -->
        <div
            v-show="showingNavigationDropdown"
            class="fixed inset-0 z-40 lg:hidden"
            @click="showingNavigationDropdown = false"
        >
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50"></div>

            <!-- Menu -->
            <div class="fixed inset-y-0 left-0 w-64 bg-background-secondary p-4">
                <!-- Mobile Search -->
                <div class="relative mb-4">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        class="h-10 w-full rounded-lg border border-border-primary bg-background-primary pl-10 pr-4 text-sm text-white placeholder-gray-400 focus:border-blue-600 focus:outline-none"
                    />
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-1">
                    <template v-for="item in navigation" :key="item.name">
                        <ResponsiveNavLink
                            :href="item.href"
                            :active="route().current(item.name.toLowerCase())"
                            class="flex items-center gap-3"
                        >
                            <component :is="item.icon" class="h-5 w-5" />
                            {{ item.name }}
                        </ResponsiveNavLink>
                    </template>
                </nav>

                <!-- Mobile Create Post Button -->
                <div class="mt-4">
                    <Link
                        :href="route('posts.create')"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                    >
                        <Plus class="h-4 w-4" />
                        Create Post
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
