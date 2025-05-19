<template>
  <div class="relative">
    <!-- Notification Bell -->
    <button
      @click="isOpen = !isOpen"
      class="relative p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
    >
      <span class="sr-only">View notifications</span>
      <!-- Bell Icon -->
      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
        />
      </svg>
      <!-- Notification Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 -mt-1 -mr-1 flex items-center justify-center h-4 w-4 rounded-full bg-red-500 text-xs font-medium text-white"
      >
        {{ unreadCount }}
      </span>
    </button>

    <!-- Notification Panel -->
    <div
      v-if="isOpen"
      class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-50"
    >
      <div class="py-1" role="none">
        <div class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-50">
          Notifications
        </div>
        <!-- Notification List -->
        <div class="max-h-96 overflow-y-auto">
          <div v-if="notifications.length === 0" class="px-4 py-3 text-sm text-gray-500">
            No notifications
          </div>
          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="px-4 py-3 hover:bg-gray-50 transition-colors duration-150"
            :class="{ 'bg-blue-50': !notification.read }"
          >
            <div class="flex items-start">
              <!-- Status Icon -->
              <div class="flex-shrink-0 mt-0.5">
                <span
                  class="h-2 w-2 rounded-full"
                  :class="{
                    'bg-green-400': notification.type === 'success',
                    'bg-red-400': notification.type === 'failure',
                    'bg-yellow-400': notification.type === 'token_issue'
                  }"
                ></span>
              </div>
              <!-- Content -->
              <div class="ml-3 flex-1">
                <p class="text-sm text-gray-900">
                  {{ notification.message }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                  {{ formatTime(notification.created_at) }}
                </p>
              </div>
              <!-- Mark as Read Button -->
              <button
                v-if="!notification.read"
                @click.stop="markAsRead(notification.id)"
                class="ml-3 text-xs text-blue-600 hover:text-blue-800"
              >
                Mark as read
              </button>
            </div>
          </div>
        </div>
        <!-- Footer -->
        <div class="px-4 py-2 text-xs text-gray-500 bg-gray-50 flex justify-between items-center">
          <button
            v-if="hasUnread"
            @click="markAllAsRead"
            class="text-blue-600 hover:text-blue-800"
          >
            Mark all as read
          </button>
          <button
            v-if="notifications.length > 0"
            @click="clearAll"
            class="text-red-600 hover:text-red-800"
          >
            Clear all
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const isOpen = ref(false)
const notifications = ref([])
const pollingInterval = ref(null)

const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read).length
})

const hasUnread = computed(() => unreadCount.value > 0)

onMounted(() => {
  fetchNotifications()
  // Poll for new notifications every 30 seconds
  pollingInterval.value = setInterval(fetchNotifications, 30000)
})

onUnmounted(() => {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value)
  }
})

import { viewsApi } from '../services/api.js'

// Mock notifications for development
notifications.value = [
  {
    id: 1,
    message: 'Your Twitter post has been scheduled',
    type: 'success',
    read: false,
    created_at: new Date(Date.now() - 1000 * 60 * 30).toISOString() // 30 minutes ago
  },
  {
    id: 2,
    message: 'LinkedIn token needs to be refreshed',
    type: 'token_issue',
    read: false,
    created_at: new Date(Date.now() - 1000 * 60 * 60).toISOString() // 1 hour ago
  },
  {
    id: 3,
    message: 'Content generation completed',
    type: 'success',
    read: true,
    created_at: new Date(Date.now() - 1000 * 60 * 120).toISOString() // 2 hours ago
  }
]

async function fetchNotifications() {
  // In development, we'll use the mock data
  // In production, this would redirect to the PHP notifications view
  viewsApi.getNotifications()
}

function markAsRead(id) {
  const notification = notifications.value.find(n => n.id === id)
  if (notification) {
    notification.read = true
  }
}

function markAllAsRead() {
  notifications.value = notifications.value.map(n => ({ ...n, read: true }))
}

function clearAll() {
  notifications.value = []
  isOpen.value = false
}

function formatTime(timestamp) {
  const date = new Date(timestamp)
  return date.toLocaleString()
}
</script>

<style scoped>
.notification-panel-enter-active,
.notification-panel-leave-active {
  transition: all 0.2s ease;
}

.notification-panel-enter-from,
.notification-panel-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
