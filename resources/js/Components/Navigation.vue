<template>
    <nav class="bg-white border-b border-gray-100">
        <!-- Primary Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left Side -->
                <div class="flex">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <Link :href="route('dashboard')">
                            <ApplicationLogo class="block h-9 w-auto" />
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
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
                    </div>
                </div>

                <!-- Right Side -->
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <!-- Create Post Button -->
                    <Link :href="route('posts.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-4">
                        <PlusIcon class="w-4 h-4 mr-1" />
                        Create Post
                    </Link>

                    <!-- Settings Dropdown -->
                    <div class="ml-3 relative">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                        {{ $page.props.auth.user.name }}
                                        <ChevronDownIcon class="ml-2 -mr-0.5 h-4 w-4" />
                                    </button>
                                </span>
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
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <Bars3Icon v-show="!showingNavigationDropdown" class="h-6 w-6" />
                        <XMarkIcon v-show="showingNavigationDropdown" class="h-6 w-6" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
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
            </div>

            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">
                        {{ $page.props.auth.user.name }}
                    </div>
                    <div class="font-medium text-sm text-gray-500">
                        {{ $page.props.auth.user.email }}
                    </div>
                </div>

                <div class="mt-3 space-y-1">
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

<script setup>
import { ref } from 'vue';
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
</script>
