<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    Bars3Icon,
    XMarkIcon,
    PlusIcon,
    ChevronDownIcon,
} from '@heroicons/vue/24/outline';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const showingNavigationDropdown = ref(false);
const isAdmin = computed(() => $page.props.auth.user.is_admin);
</script>

<template>
    <nav class="border-b border-gray-800 bg-[#0B0F1A]">
        <!-- Primary Navigation Menu -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <!-- Left Side -->
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex shrink-0 items-center">
                        <Link :href="route('dashboard')" class="flex items-center gap-2">
                            <ApplicationLogo />
                            <span class="text-lg font-semibold text-white">Dashboard</span>
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-4 sm:-my-px sm:ml-10 sm:flex">
                        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </NavLink>

                        <NavLink :href="route('posts.index')" :active="route().current('posts.*')">
                            Posts
                        </NavLink>

                        <NavLink :href="route('social-accounts.index')" :active="route().current('social-accounts.*')">
                            Social Accounts
                        </NavLink>

                        <NavLink :href="route('analytics.index')" :active="route().current('analytics.*')">
                            Analytics
                        </NavLink>

                        <NavLink :href="route('logs.index')" :active="route().current('logs.*')">
                            Logs
                        </NavLink>

                        <!-- Admin Links -->
                        <template v-if="isAdmin">
                            <NavLink :href="route('admin.users')" :active="route().current('admin.users')">
                                Users
                            </NavLink>
                            <NavLink :href="route('admin.settings')" :active="route().current('admin.settings')">
                                Settings
                            </NavLink>
                        </template>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <!-- Create Post Button -->
                    <Link :href="route('posts.create')"
                        class="mr-4 inline-flex items-center rounded-lg border border-transparent bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-[#0B0F1A] active:bg-blue-800">
                        <PlusIcon class="mr-1 h-4 w-4" />
                        Create Post
                    </Link>

                    <!-- Settings Dropdown -->
                    <div class="relative ml-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-2 rounded-lg border border-gray-800 bg-[#020817] px-3 py-2 text-sm font-medium text-white transition-colors hover:border-gray-700">
                                    {{ $page.props.auth.user.name }}
                                    <ChevronDownIcon class="h-4 w-4" />
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="-mr-2 flex items-center sm:hidden">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-blue-600/10 hover:text-white focus:outline-none">
                        <Bars3Icon v-show="!showingNavigationDropdown" class="h-6 w-6" />
                        <XMarkIcon v-show="showingNavigationDropdown" class="h-6 w-6" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="sm:hidden">
            <div class="space-y-1 px-4 pb-3 pt-2">
                <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                    Dashboard
                </ResponsiveNavLink>

                <ResponsiveNavLink :href="route('posts.index')" :active="route().current('posts.*')">
                    Posts
                </ResponsiveNavLink>

                <ResponsiveNavLink :href="route('social-accounts.index')" :active="route().current('social-accounts.*')">
                    Social Accounts
                </ResponsiveNavLink>

                <ResponsiveNavLink :href="route('analytics.index')" :active="route().current('analytics.*')">
                    Analytics
                </ResponsiveNavLink>

                <ResponsiveNavLink :href="route('logs.index')" :active="route().current('logs.*')">
                    Logs
                </ResponsiveNavLink>

                <!-- Responsive Admin Links -->
                <template v-if="isAdmin">
                    <ResponsiveNavLink :href="route('admin.users')" :active="route().current('admin.users')">
                        Users
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.settings')" :active="route().current('admin.settings')">
                        Settings
                    </ResponsiveNavLink>
                </template>
            </div>

            <!-- Responsive Settings Options -->
            <div class="border-t border-gray-800 pb-1 pt-4">
                <div class="px-4">
                    <div class="text-base font-medium text-white">
                        {{ $page.props.auth.user.name }}
                    </div>
                    <div class="text-sm font-medium text-gray-400">
                        {{ $page.props.auth.user.email }}
                    </div>
                </div>

                <div class="mt-3 space-y-1 px-4">
                    <ResponsiveNavLink :href="route('profile.edit')">
                        Profile
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                        Log Out
                    </ResponsiveNavLink>
                </div>
            </div>
        </div>
    </nav>
</template>
