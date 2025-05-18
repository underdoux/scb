<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
        <div class="p-6">
            <!-- Platform Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full" :class="platformColor">
                        <component :is="platformIcon" class="w-6 h-6 text-white" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 capitalize">
                        {{ platform }}
                    </h3>
                </div>
                
                <!-- Connection Status -->
                <div v-if="account" class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="tokenStatus.class">
                        {{ tokenStatus.text }}
                    </span>
                </div>
            </div>

            <!-- Account Info -->
            <div v-if="account" class="mb-4">
                <div class="text-sm text-gray-600">
                    <p><span class="font-medium">Username:</span> {{ account.platform_username }}</p>
                    <p><span class="font-medium">Connected:</span> {{ formatDate(account.updated_at) }}</p>
                    <p v-if="account.token_expires_at">
                        <span class="font-medium">Token Expires:</span> 
                        {{ formatDate(account.token_expires_at) }}
                    </p>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-4">
                <button v-if="!account"
                    @click="$emit('connect', platform)"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
                    :class="platformButtonColor">
                    Connect {{ platform }}
                </button>
                <button v-else
                    @click="$emit('disconnect', platform)"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Disconnect
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
    FacebookIcon,
    TwitterIcon,
    InstagramIcon,
    LinkedInIcon,
    TikTokIcon,
    YoutubeIcon
} from '@/Components/Icons';

const props = defineProps({
    platform: {
        type: String,
        required: true,
    },
    account: {
        type: Object,
        default: null,
    },
});

// Platform-specific icon component
const platformIcon = computed(() => {
    return {
        facebook: FacebookIcon,
        twitter: TwitterIcon,
        instagram: InstagramIcon,
        linkedin: LinkedInIcon,
        tiktok: TikTokIcon,
        youtube: YoutubeIcon,
    }[props.platform];
});

// Platform-specific colors
const platformColor = computed(() => {
    return {
        facebook: 'bg-[#1877F2]',
        twitter: 'bg-[#1DA1F2]',
        instagram: 'bg-gradient-to-r from-[#833AB4] via-[#FD1D1D] to-[#F77737]',
        linkedin: 'bg-[#0A66C2]',
        tiktok: 'bg-black',
        youtube: 'bg-[#FF0000]',
    }[props.platform];
});

// Platform-specific button colors
const platformButtonColor = computed(() => {
    return {
        facebook: 'bg-[#1877F2] hover:bg-[#0c60cf] focus:ring-[#1877F2]',
        twitter: 'bg-[#1DA1F2] hover:bg-[#0c8bd9] focus:ring-[#1DA1F2]',
        instagram: 'bg-[#FD1D1D] hover:bg-[#e41111] focus:ring-[#FD1D1D]',
        linkedin: 'bg-[#0A66C2] hover:bg-[#084d93] focus:ring-[#0A66C2]',
        tiktok: 'bg-black hover:bg-gray-800 focus:ring-black',
        youtube: 'bg-[#FF0000] hover:bg-[#cc0000] focus:ring-[#FF0000]',
    }[props.platform];
});

// Token status indicator
const tokenStatus = computed(() => {
    if (!props.account) return null;

    const expiresAt = new Date(props.account.token_expires_at);
    const now = new Date();
    const hoursUntilExpiry = (expiresAt - now) / (1000 * 60 * 60);

    if (hoursUntilExpiry <= 0) {
        return {
            text: 'Expired',
            class: 'bg-red-100 text-red-800',
        };
    } else if (hoursUntilExpiry <= 24) {
        return {
            text: 'Expiring Soon',
            class: 'bg-yellow-100 text-yellow-800',
        };
    } else {
        return {
            text: 'Connected',
            class: 'bg-green-100 text-green-800',
        };
    }
});

// Format date for display
const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
