<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h2 class="text-2xl font-bold mb-6">Schedule New Post</h2>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Content Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Content Source</label>
          <div class="mt-2 space-y-4">
            <div class="flex items-center">
              <input
                type="radio"
                v-model="formData.contentSource"
                value="generated"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500"
              />
              <label class="ml-3 block text-sm font-medium text-gray-700">
                Use Generated Content
              </label>
            </div>
            <div class="flex items-center">
              <input
                type="radio"
                v-model="formData.contentSource"
                value="custom"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500"
              />
              <label class="ml-3 block text-sm font-medium text-gray-700">
                Custom Content
              </label>
            </div>
          </div>
        </div>

        <!-- Content Input -->
        <div v-if="formData.contentSource === 'custom'">
          <label for="content" class="block text-sm font-medium text-gray-700">Post Content</label>
          <div class="mt-1">
            <textarea
              id="content"
              v-model="formData.content"
              rows="4"
              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
              placeholder="Enter your post content..."
            ></textarea>
          </div>
        </div>

        <!-- Generated Content Selection -->
        <div v-else>
          <label class="block text-sm font-medium text-gray-700">Select Generated Content</label>
          <select
            v-model="formData.generatedContentId"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
          >
            <option value="">Select content...</option>
            <option v-for="content in generatedContent" :key="content.id" :value="content.id">
              {{ content.preview }}
            </option>
          </select>
        </div>

        <!-- Platform Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Select Platforms</label>
          <div class="mt-2 space-y-2">
            <div v-for="platform in availablePlatforms" :key="platform.id" class="flex items-center">
              <input
                type="checkbox"
                :value="platform.id"
                v-model="formData.platforms"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label class="ml-3 block text-sm font-medium text-gray-700">
                {{ platform.name }}
              </label>
            </div>
          </div>
        </div>

        <!-- Schedule DateTime -->
        <div>
          <label for="scheduleTime" class="block text-sm font-medium text-gray-700">Schedule Time</label>
          <input
            type="datetime-local"
            id="scheduleTime"
            v-model="formData.scheduleTime"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          />
        </div>

        <!-- Submit Button -->
        <div class="pt-5">
          <div class="flex justify-end">
            <button
              type="button"
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              @click="$router.push('/dashboard')"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Scheduling...' : 'Schedule Post' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isSubmitting = ref(false)

const formData = ref({
  contentSource: 'custom',
  content: '',
  generatedContentId: '',
  platforms: [],
  scheduleTime: ''
})

const generatedContent = ref([])
const availablePlatforms = ref([])

onMounted(async () => {
  try {
    // Fetch generated content and platforms from API
    const [contentResponse, platformsResponse] = await Promise.all([
      fetch('/api/generated-content'),
      fetch('/api/platforms')
    ])
    
    generatedContent.value = await contentResponse.json()
    availablePlatforms.value = await platformsResponse.json()
  } catch (error) {
    console.error('Error fetching data:', error)
    // Add error handling/notification here
  }
})

async function handleSubmit() {
  if (isSubmitting.value) return

  try {
    isSubmitting.value = true
    
    const response = await fetch('/api/schedule', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData.value)
    })

    if (!response.ok) {
      throw new Error('Failed to schedule post')
    }

    // Show success message and redirect
    router.push('/dashboard')
  } catch (error) {
    console.error('Error scheduling post:', error)
    // Add error handling/notification here
  } finally {
    isSubmitting.value = false
  }
}
</script>
