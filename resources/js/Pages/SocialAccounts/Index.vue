<template>
    <AppLayout title="Social Media Accounts">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Social Media Accounts
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success/Error Messages -->
                <div v-if="flash.success" class="mb-4">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ flash.success }}</span>
                    </div>
                </div>
                <div v-if="flash.error" class="mb-4">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ flash.error }}</span>
                    </div>
                </div>

                <!-- Connected Accounts Grid -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Connected Accounts</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Facebook -->
                        <SocialAccountCard
                            platform="facebook"
                            :account="getAccount('facebook')"
                            @connect="connectAccount"
                            @disconnect="disconnectAccount"
                        />

                        <!-- Twitter -->
                        <SocialAccountCard
                            platform="twitter"
                            :account="getAccount('twitter')"
                            @connect="connectAccount"
                            @disconnect="disconnectAccount"
                        />

                        <!-- Instagram -->
                        <SocialAccountCard
                            platform="instagram"
                            :account="getAccount('instagram')"
                            @connect="connectAccount"
                            @disconnect="disconnectAccount"
                        />

                        <!-- LinkedIn -->
                        <SocialAccountCard
                            platform="linkedin"
                            :account="getAccount('linkedin')"
                            @connect="connectAccount"
                            @disconnect="disconnectAccount"
                        />

                        <!-- TikTok -->
                        <SocialAccountCard
                            platform="tiktok"
                            :account="getAccount('tiktok')"
                            @connect="connectAccount"
                            @disconnect="disconnectAccount"
                        />

                        <!-- YouTube -->
                        <SocialAccountCard
                            platform="youtube"
                            :account="getAccount('youtube')"
                            @connect="connectAccount"
                            @disconnect="disconnectAccount"
                        />
                    </div>
                </div>

                <!-- Account Management Tips -->
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tips for Managing Social Accounts</h3>
                    <div class="prose max-w-none">
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Connect your social media accounts to enable automated posting</li>
                            <li>Ensure your access tokens are up to date to maintain connectivity</li>
                            <li>Review platform-specific requirements and limitations for posting content</li>
                            <li>Monitor your account status regularly for any authorization issues</li>
                            <li>Disconnect accounts you no longer wish to manage through this platform</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AuthenticatedLayout.vue';
import SocialAccountCard from '@/Components/SocialAccountCard.vue';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => [],
    },
});

const flash = computed(() => usePage().props.flash);

// Get account by platform
const getAccount = (platform) => {
    return props.accounts.find(account => account.platform === platform) || null;
};

// Connect social media account
const connectAccount = (platform) => {
    router.visit(route('social-accounts.connect', platform));
};

// Disconnect social media account
const disconnectAccount = (platform) => {
    if (confirm(`Are you sure you want to disconnect your ${platform} account?`)) {
        router.delete(route('social-accounts.disconnect', platform));
    }
};
</script>
