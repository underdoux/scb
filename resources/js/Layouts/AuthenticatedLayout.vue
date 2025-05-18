<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { 
  LayoutDashboard, 
  FolderKanban, 
  CheckSquare, 
  BarChart3, 
  Settings, 
  LogOut,
  Bell,
  Search
} from 'lucide-vue-next';

const showingNavigationDropdown = ref(false);
const navigation = [
  { name: 'Dashboard', href: route('dashboard'), icon: LayoutDashboard },
  { name: 'Projects', href: '#', icon: FolderKanban },
  { name: 'Tasks', href: '#', icon: CheckSquare },
  { name: 'Reporting', href: '#', icon: BarChart3 },
  { name: 'Settings', href: '#', icon: Settings },
];
</script>

<template>
    <div class="min-h-screen bg-[#020817]">
        <!-- Sidebar -->
        <aside class="fixed left-0 top-0 z-40 h-screen w-64 border-r border-gray-800 bg-[#0B0F1A]">
            <!-- Logo -->
            <div class="flex h-16 items-center border-b border-gray-800 px-6">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded bg-blue-600">
                        <span class="text-xl font-bold text-white">D</span>
                    </div>
                    <span class="text-lg font-semibold text-white">Dashboard</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-1 p-4">
                <template v-for="item in navigation" :key="item.name">
                    <NavLink
                        :href="item.href"
                        :active="route().current(item.name.toLowerCase())"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-400 transition-colors hover:bg-blue-600/10 hover:text-white"
                        :class="{ 'bg-blue-600 text-white': route().current(item.name.toLowerCase()) }"
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                        {{ item.name }}
                    </NavLink>
                </template>
            </nav>

            <!-- Logout -->
            <div class="border-t border-gray-800 p-4">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-400 transition-colors hover:bg-blue-600/10 hover:text-white"
                >
                    <LogOut class="h-5 w-5" />
                    Logout
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="pl-64">
            <!-- Top Navigation -->
            <nav class="fixed top-0 z-30 w-full border-b border-gray-800 bg-[#0B0F1A] pl-64">
                <div class="flex h-16 items-center justify-between px-6">
                    <!-- Search -->
                    <div class="flex flex-1 items-center">
                        <div class="relative w-96">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search..."
                                class="h-10 w-full rounded-lg border border-gray-800 bg-gray-900 pl-10 pr-4 text-sm text-white placeholder-gray-400 focus:border-blue-600 focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Right Navigation -->
                    <div class="flex items-center gap-4">
                        <!-- Notifications -->
                        <button class="relative text-gray-400 hover:text-white">
                            <Bell class="h-5 w-5" />
                            <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-blue-600 text-xs text-white">
                                1
                            </span>
                        </button>

                        <!-- User Dropdown -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-2 rounded-lg border border-gray-800 bg-gray-900 px-3 py-2 text-sm font-medium text-white transition-colors hover:border-gray-700">
                                    {{ $page.props.auth.user.name }}
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
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
            </nav>

            <!-- Main Content Area -->
            <main class="min-h-screen pt-16">
                <!-- Page Heading -->
                <header v-if="$slots.header" class="bg-[#0B0F1A] py-6 px-6">
                    <slot name="header" />
                </header>

                <!-- Page Content -->
                <div class="px-6 py-6">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div
            v-show="showingNavigationDropdown"
            class="fixed inset-0 z-50 lg:hidden"
            @click="showingNavigationDropdown = false"
        >
            <div class="fixed inset-0 bg-gray-900/80"></div>
            
            <div class="fixed inset-y-0 left-0 w-64 bg-[#0B0F1A]">
                <!-- Mobile Logo -->
                <div class="flex h-16 items-center border-b border-gray-800 px-6">
                    <Link :href="route('dashboard')" class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded bg-blue-600">
                            <span class="text-xl font-bold text-white">D</span>
                        </div>
                        <span class="text-lg font-semibold text-white">Dashboard</span>
                    </Link>
                </div>

                <!-- Mobile Navigation -->
                <nav class="flex-1 space-y-1 p-4">
                    <template v-for="item in navigation" :key="item.name">
                        <ResponsiveNavLink
                            :href="item.href"
                            :active="route().current(item.name.toLowerCase())"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-400 transition-colors hover:bg-blue-600/10 hover:text-white"
                            :class="{ 'bg-blue-600 text-white': route().current(item.name.toLowerCase()) }"
                        >
                            <component :is="item.icon" class="h-5 w-5" />
                            {{ item.name }}
                        </ResponsiveNavLink>
                    </template>
                </nav>

                <!-- Mobile User Info -->
                <div class="border-t border-gray-800 p-4">
                    <div class="px-2 py-3">
                        <div class="text-sm font-medium text-white">{{ $page.props.auth.user.name }}</div>
                        <div class="text-xs text-gray-400">{{ $page.props.auth.user.email }}</div>
                    </div>
                    <ResponsiveNavLink
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full"
                    >
                        <div class="flex items-center gap-3">
                            <LogOut class="h-5 w-5" />
                            Logout
                        </div>
                    </ResponsiveNavLink>
                </div>
            </div>
        </div>
    </div>
</template>
