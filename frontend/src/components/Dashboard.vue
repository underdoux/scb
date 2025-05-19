<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h2 class="text-2xl font-bold mb-6">Dashboard</h2>
      
      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-blue-50 p-6 rounded-lg">
          <h3 class="text-lg font-semibold text-blue-800">Scheduled Posts</h3>
          <p class="text-3xl font-bold text-blue-600">{{ stats.scheduledPosts }}</p>
        </div>
        <div class="bg-green-50 p-6 rounded-lg">
          <h3 class="text-lg font-semibold text-green-800">Published Posts</h3>
          <p class="text-3xl font-bold text-green-600">{{ stats.publishedPosts }}</p>
        </div>
        <div class="bg-purple-50 p-6 rounded-lg">
          <h3 class="text-lg font-semibold text-purple-800">Connected Platforms</h3>
          <p class="text-3xl font-bold text-purple-600">{{ stats.connectedPlatforms }}</p>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <ul class="divide-y divide-gray-200">
            <li v-for="activity in recentActivity" :key="activity.id" class="py-3">
              <div class="flex items-center space-x-4">
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ activity.message }}</p>
                  <p class="text-sm text-gray-500">{{ activity.timestamp }}</p>
                </div>
                <div>
                  <span 
                    :class="[
                      'px-2 py-1 text-xs font-semibold rounded-full',
                      activity.status === 'success' ? 'bg-green-100 text-green-800' :
                      activity.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ activity.status }}
                  </span>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button 
            @click="$router.push('/generator')"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Create New Content
          </button>
          <button 
            @click="$router.push('/schedule')"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
          >
            Schedule New Post
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const stats = ref({
  scheduledPosts: 0,
  publishedPosts: 0,
  connectedPlatforms: 0
})

const recentActivity = ref([])

import { viewsApi } from '../services/api.js'

onMounted(() => {
  // Redirect to PHP dashboard view
  viewsApi.getDashboard()
})

// Mock data for development
stats.value = {
  scheduledPosts: 5,
  publishedPosts: 12,
  connectedPlatforms: 3
}

recentActivity.value = [
  {
    id: 1,
    message: 'Post scheduled for Twitter',
    timestamp: '2 hours ago',
    status: 'success'
  },
  {
    id: 2,
    message: 'Content generated for LinkedIn',
    timestamp: '4 hours ago',
    status: 'success'
  },
  {
    id: 3,
    message: 'Facebook post pending approval',
    timestamp: '6 hours ago',
    status: 'pending'
  }
]
</script>
