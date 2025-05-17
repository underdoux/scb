<template>
    <AppLayout title="Edit Post">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Post
                </h2>
                <div class="flex items-center space-x-4">
                    <span :class="statusClasses(post.status)" class="px-3 py-1 rounded-full text-xs font-semibold">
                        {{ post.status }}
                    </span>
                </div>
            </div>
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
                                :disabled="post.isPublished"
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
                        <div v-if="!post.isPublished" class="bg-gray-50 p-4 rounded-lg">
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
                                <div class="flex justify-end space-x-4">
                                    <PrimaryButton
                                        type="button"
                                        @click="generateVariations"
                                        :class="{ 'opacity-25': generatingContent }"
                                        :disabled="generatingContent || !form.content"
                                    >
                                        Generate Variations
                                    </PrimaryButton>
                                    <PrimaryButton
                                        type="button"
                                        @click="generateContent"
                                        :class="{ 'opacity-25': generatingContent }"
                                        :disabled="generatingContent"
                                    >
                                        Generate New Content
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
                                :disabled="post.isPublished"
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
                                :disabled="post.isPublished"
                            ></textarea>
                            <InputError :message="form.errors.hashtags" class="mt-2" />
                        </div>

                        <!-- Scheduling -->
                        <div v-if="!post.isPublished">
                            <InputLabel for="scheduled_time" value="Schedule Post" />
                            <input
                                id="scheduled_time"
                                type="datetime-local"
                                v-model="form.scheduled_time"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :min="minScheduleTime"
                            >
                            <InputError :message="form.errors.scheduled_time" class="mt-2" />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <DangerButton
                                v-if="!post.isPublished"
                                type="button"
                                @click="confirmDelete"
                            >
                                Delete Post
                            </DangerButton>
                            <Link
                                :href="route('posts.index')"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton
                                v-if="!post.isPublished"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Update Post
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this post?
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Once this post is deleted, all of its data will be permanently deleted.
                </p>
                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="closeModal">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        :class="{ 'opacity-25': deleting }"
                        :disabled="deleting"
                        @click="deletePost"
                    >
                        Delete Post
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    post: Object,
});

const aiPrompt = ref('');
const generatingContent = ref(false);
const showDeleteModal = ref(false);
const deleting = ref(false);

const form = useForm({
    platform: props.post.platform,
    content: props.post.content,
    hashtags: props.post.hashtags,
    scheduled_time: props.post.schedule?.scheduled_time || '',
    gpt_prompt: props.post.gpt_prompt,
    gpt_response: props.post.gpt_response,
});

const minScheduleTime = computed(() => {
    const now = new Date();
    now.setMinutes(now.getMinutes() + 5); // Minimum 5 minutes from now
    return now.toISOString().slice(0, 16);
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

const generateVariations = async () => {
    if (!form.content) return;

    generatingContent.value = true;

    try {
        const response = await fetch(route('posts.generate-variations'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ content: form.content }),
        });

        const result = await response.json();

        if (result.success && result.variations.length > 0) {
            form.content = result.variations[0]; // Use the first variation
        } else {
            alert('Failed to generate variations: ' + (result.error || 'No variations generated'));
        }
    } catch (error) {
        alert('An error occurred while generating variations');
    } finally {
        generatingContent.value = false;
    }
};

const submit = () => {
    form.patch(route('posts.update', props.post.id));
};

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const closeModal = () => {
    showDeleteModal.value = false;
};

const deletePost = () => {
    deleting.value = true;
    router.delete(route('posts.destroy', props.post.id), {
        onFinish: () => {
            deleting.value = false;
            showDeleteModal.value = false;
        },
    });
};

const statusClasses = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        scheduled: 'bg-yellow-100 text-yellow-800',
        published: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
