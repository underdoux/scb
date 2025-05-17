<template>
    <AppLayout title="Create Post">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Post
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Platform Selection -->
                        <div>
                            <InputLabel for="platform" value="Platform" />
                            <select
                                id="platform"
                                v-model="form.platform"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select Platform</option>
                                <option value="facebook">Facebook</option>
                                <option value="instagram">Instagram</option>
                                <option value="twitter">Twitter</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="tiktok">TikTok</option>
                                <option value="youtube">YouTube</option>
                            </select>
                            <InputError :message="form.errors.platform" class="mt-2" />
                        </div>

                        <!-- AI Content Generation -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">AI Content Generation</h3>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="prompt" value="Prompt for AI" />
                                    <textarea
                                        id="prompt"
                                        v-model="aiPrompt"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Describe what kind of content you want to generate..."
                                    ></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <PrimaryButton
                                        type="button"
                                        @click="generateContent"
                                        :class="{ 'opacity-25': generatingContent }"
                                        :disabled="generatingContent"
                                    >
                                        {{ generatingContent ? 'Generating...' : 'Generate Content' }}
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div>
                            <InputLabel for="content" value="Content" />
                            <textarea
                                id="content"
                                v-model="form.content"
                                rows="6"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Write your post content here..."
                            ></textarea>
                            <InputError :message="form.errors.content" class="mt-2" />
                        </div>

                        <!-- Hashtags -->
                        <div>
                            <InputLabel for="hashtags" value="Hashtags" />
                            <textarea
                                id="hashtags"
                                v-model="form.hashtags"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="#example #hashtags"
                            ></textarea>
                            <InputError :message="form.errors.hashtags" class="mt-2" />
                        </div>

                        <!-- Scheduling -->
                        <div>
                            <InputLabel for="scheduled_time" value="Schedule Post (Optional)" />
                            <input
                                id="scheduled_time"
                                type="datetime-local"
                                v-model="form.scheduled_time"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <InputError :message="form.errors.scheduled_time" class="mt-2" />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <Link
                                :href="route('posts.index')"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Create Post
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const aiPrompt = ref('');
const generatingContent = ref(false);

const form = useForm({
    platform: '',
    content: '',
    hashtags: '',
    scheduled_time: '',
    gpt_prompt: '',
    gpt_response: '',
});

const generateContent = async () => {
    if (!aiPrompt.value) return;

    generatingContent.value = true;
    form.gpt_prompt = aiPrompt.value;

    try {
        const response = await fetch(route('posts.generate-content'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ prompt: aiPrompt.value }),
        });

        const result = await response.json();

        if (result.success) {
            form.content = result.content;
            form.hashtags = result.hashtags;
            form.gpt_response = result.raw_response;
        } else {
            alert('Failed to generate content: ' + result.error);
        }
    } catch (error) {
        alert('An error occurred while generating content');
    } finally {
        generatingContent.value = false;
    }
};

const submit = () => {
    form.post(route('posts.store'), {
        onSuccess: () => {
            form.reset();
            aiPrompt.value = '';
        },
    });
};
</script>
