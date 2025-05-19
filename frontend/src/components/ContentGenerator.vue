<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h2 class="text-2xl font-bold mb-6">AI Content Generator</h2>

      <!-- Content Generation Form -->
      <form @submit.prevent="generateContent" class="space-y-6">
        <!-- Topic Input -->
        <div>
          <label for="topic" class="block text-sm font-medium text-gray-700">Topic</label>
          <div class="mt-1">
            <input
              type="text"
              id="topic"
              v-model="formData.topic"
              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
              placeholder="Enter your topic..."
              required
            />
          </div>
        </div>

        <!-- Content Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Content Type</label>
          <select
            v-model="formData.contentType"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
          >
            <option value="promotional">Promotional</option>
            <option value="informative">Informative</option>
            <option value="engaging">Engaging</option>
          </select>
        </div>

        <!-- Length -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Length</label>
          <select
            v-model="formData.length"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
          >
            <option value="short">Short</option>
            <option value="medium">Medium</option>
            <option value="long">Long</option>
          </select>
        </div>

        <!-- Tone -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Tone</label>
          <select
            v-model="formData.tone"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
          >
            <option value="professional">Professional</option>
            <option value="casual">Casual</option>
            <option value="humorous">Humorous</option>
          </select>
        </div>

        <!-- Generate Button -->
        <div>
          <button
            type="submit"
            class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            :disabled="isGenerating"
          >
            {{ isGenerating ? 'Generating...' : 'Generate Content' }}
          </button>
        </div>
      </form>

      <!-- Generated Content -->
      <div v-if="generatedContent.length > 0" class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Generated Content</h3>
        
        <div class="space-y-4">
          <div v-for="(content, index) in generatedContent" :key="index" class="bg-gray-50 p-4 rounded-lg">
            <div class="prose max-w-none">
              {{ content.text }}
            </div>
            <div class="mt-4 flex justify-end space-x-4">
              <button
                @click="editContent(index)"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Edit
              </button>
              <button
                @click="scheduleContent(content)"
                class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Schedule
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isGenerating = ref(false)

const formData = ref({
  topic: '',
  contentType: 'engaging',
  length: 'medium',
  tone: 'professional'
})

const generatedContent = ref([])

async function generateContent() {
  if (isGenerating.value) return

  try {
    isGenerating.value = true
    
    const response = await fetch('/api/generate-content', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData.value)
    })

    if (!response.ok) {
      throw new Error('Failed to generate content')
    }

    const data = await response.json()
    generatedContent.value = [
      ...generatedContent.value,
      { text: data.content, metadata: formData.value }
    ]

    // Clear form after successful generation
    formData.value.topic = ''
  } catch (error) {
    console.error('Error generating content:', error)
    // Add error handling/notification here
  } finally {
    isGenerating.value = false
  }
}

function editContent(index) {
  const content = generatedContent.value[index]
  // Implement edit functionality
  console.log('Edit content:', content)
}

function scheduleContent(content) {
  // Store the content in localStorage or Vuex store
  localStorage.setItem('scheduleContent', JSON.stringify(content))
  // Navigate to schedule page
  router.push('/schedule')
}
</script>
