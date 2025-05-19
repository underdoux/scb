<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h2 class="text-2xl font-bold mb-6">Connected Social Media Accounts</h2>

      <!-- Platform Cards -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-2">
        <!-- Facebook -->
        <div class="bg-white overflow-hidden shadow rounded-lg divide-y divide-gray-200">
          <div class="px-4 py-5 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <!-- Facebook Icon -->
                  <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Facebook</h3>
                  <p class="text-sm text-gray-500">
                    {{ accounts.facebook ? 'Connected' : 'Not Connected' }}
                  </p>
                </div>
              </div>
              <button
                v-if="!accounts.facebook"
                @click="connectAccount('facebook')"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
              >
                Connect
              </button>
              <button
                v-else
                @click="disconnectAccount('facebook')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50"
              >
                Disconnect
              </button>
            </div>
          </div>
        </div>

        <!-- Twitter -->
        <div class="bg-white overflow-hidden shadow rounded-lg divide-y divide-gray-200">
          <div class="px-4 py-5 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <!-- Twitter Icon -->
                  <svg class="h-8 w-8 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Twitter</h3>
                  <p class="text-sm text-gray-500">
                    {{ accounts.twitter ? 'Connected' : 'Not Connected' }}
                  </p>
                </div>
              </div>
              <button
                v-if="!accounts.twitter"
                @click="connectAccount('twitter')"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-400 hover:bg-blue-500"
              >
                Connect
              </button>
              <button
                v-else
                @click="disconnectAccount('twitter')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50"
              >
                Disconnect
              </button>
            </div>
          </div>
        </div>

        <!-- LinkedIn -->
        <div class="bg-white overflow-hidden shadow rounded-lg divide-y divide-gray-200">
          <div class="px-4 py-5 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <!-- LinkedIn Icon -->
                  <svg class="h-8 w-8 text-blue-700" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">LinkedIn</h3>
                  <p class="text-sm text-gray-500">
                    {{ accounts.linkedin ? 'Connected' : 'Not Connected' }}
                  </p>
                </div>
              </div>
              <button
                v-if="!accounts.linkedin"
                @click="connectAccount('linkedin')"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-700 hover:bg-blue-800"
              >
                Connect
              </button>
              <button
                v-else
                @click="disconnectAccount('linkedin')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50"
              >
                Disconnect
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const accounts = ref({
  facebook: false,
  twitter: false,
  linkedin: false
})

onMounted(async () => {
  try {
    const response = await fetch('/api/connected-accounts')
    const data = await response.json()
    accounts.value = data
  } catch (error) {
    console.error('Error fetching connected accounts:', error)
    // Add error handling/notification here
  }
})

async function connectAccount(platform) {
  try {
    const response = await fetch(`/oauth/${platform}/login`)
    const data = await response.json()
    window.location.href = data.authUrl
  } catch (error) {
    console.error(`Error connecting to ${platform}:`, error)
    // Add error handling/notification here
  }
}

async function disconnectAccount(platform) {
  try {
    const response = await fetch(`/oauth/${platform}/disconnect`, {
      method: 'POST'
    })
    
    if (response.ok) {
      accounts.value[platform] = false
    } else {
      throw new Error(`Failed to disconnect ${platform}`)
    }
  } catch (error) {
    console.error(`Error disconnecting from ${platform}:`, error)
    // Add error handling/notification here
  }
}
</script>
