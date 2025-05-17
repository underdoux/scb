<template>
    <AppLayout title="Social Media Accounts">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Social Media Accounts
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Connected Accounts -->
                <div v-if="accounts.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Connected Accounts</h3>
                        <div class="space-y-4">
                            <div v-for="account in accounts" :key="account.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <!-- Platform Icon -->
                                    <div :class="platformIconClasses(account.platform)" class="w-12 h-12 rounded-full flex items-center justify-center">
                                        <component :is="platformIcon(account.platform)" class="w-6 h-6" />
                                    </div>
                                    <!-- Account Info -->
                                    <div>
                                        <div class="font-medium">{{ platformName(account.platform) }}</div>
                                        <div class="text-sm text-gray-500">@{{ account.platform_username }}</div>
                                        <div class="text-xs text-gray-400">
                                            Connected {{ account.connected_at }}
                                        </div>
                                    </div>
                                </div>
                                <!-- Disconnect Button -->
                                <button
                                    @click="confirmDisconnect(account)"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    Disconnect
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Platforms -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Available Platforms</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="(platform, key) in availablePlatforms" :key="key" class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <!-- Platform Icon -->
                                        <div :class="platformIconClasses(key)" class="w-12 h-12 rounded-full flex items-center justify-center">
                                            <component :is="platformIcon(key)" class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ platform.name }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ platform.connected ? 'Connected' : 'Not Connected' }}
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Connect/Disconnect Button -->
                                    <Link
                                        v-if="!platform.connected"
                                        :href="route('social-accounts.connect', key)"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-gray-800 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                    >
                                        Connect
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disconnect Confirmation Modal -->
        <Modal :show="showDisconnectModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Disconnect {{ selectedAccount ? platformName(selectedAccount.platform) : '' }} Account?
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to disconnect this account? Any scheduled posts for this platform will be cancelled.
                </p>
                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="closeModal">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        :class="{ 'opacity-25': disconnecting }"
                        :disabled="disconnecting"
                        @click="disconnectAccount"
                    >
                        Disconnect Account
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
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

// Icons
import {
    Facebook,
    Instagram,
    Twitter,
    Linkedin,
    Youtube,
    Music2 // For TikTok
} from 'lucide-vue-next';

const props = defineProps({
    accounts: Array,
    availablePlatforms: Object,
});

const showDisconnectModal = ref(false);
const disconnecting = ref(false);
const selectedAccount = ref(null);

const platformIcon = (platform) => {
    return {
        facebook: Facebook,
        instagram: Instagram,
        twitter: Twitter,
        linkedin: Linkedin,
        tiktok: Music2,
        youtube: Youtube,
    }[platform];
};

const platformIconClasses = (platform) => {
    const classes = {
        facebook: 'bg-blue-100 text-blue-600',
        instagram: 'bg-pink-100 text-pink-600',
        twitter: 'bg-sky-100 text-sky-600',
        linkedin: 'bg-blue-100 text-blue-600',
        tiktok: 'bg-gray-100 text-gray-600',
        youtube: 'bg-red-100 text-red-600',
    };
    return classes[platform] || 'bg-gray-100 text-gray-600';
};

const platformName = (platform) => {
    const names = {
        facebook: 'Facebook',
        instagram: 'Instagram',
        twitter: 'Twitter',
        linkedin: 'LinkedIn',
        tiktok: 'TikTok',
        youtube: 'YouTube',
    };
    return names[platform] || platform;
};

const confirmDisconnect = (account) => {
    selectedAccount.value = account;
    showDisconnectModal.value = true;
};

const closeModal = () => {
    showDisconnectModal.value = false;
    selectedAccount.value = null;
};

const disconnectAccount = () => {
    if (!selectedAccount.value) return;

    disconnecting.value = true;
    router.delete(route('social-accounts.disconnect', selectedAccount.value.platform), {
        onFinish: () => {
            disconnecting.value = false;
            closeModal();
        },
    });
};
</script>
