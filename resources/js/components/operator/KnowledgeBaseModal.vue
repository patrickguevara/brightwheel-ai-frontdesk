<script setup lang="ts">
import { ref, computed, watch } from 'vue';

interface KnowledgeEntry {
    id?: string;
    category: string;
    title: string;
    content: string;
    keywords: string[];
    is_active: boolean;
}

interface Props {
    show: boolean;
    entry?: KnowledgeEntry | null;
}

interface Emits {
    (e: 'close'): void;
    (e: 'saved'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const categories = [
    'hours', 'tuition', 'enrollment', 'health', 'meals',
    'schedule', 'pickup', 'safety', 'classrooms', 'policies', 'general',
];

const form = ref({
    category: '',
    title: '',
    content: '',
    keywords: '',
    is_active: true,
});

const errors = ref<Record<string, string>>({});
const loading = ref(false);

const isEditMode = computed(() => !!props.entry?.id);

// Reset form when modal opens/closes
watch(() => props.show, (show) => {
    if (show && props.entry) {
        form.value = {
            category: props.entry.category,
            title: props.entry.title,
            content: props.entry.content,
            keywords: props.entry.keywords.join('\n'),
            is_active: props.entry.is_active,
        };
    } else if (show) {
        form.value = {
            category: 'general',
            title: '',
            content: '',
            keywords: '',
            is_active: true,
        };
    }
    errors.value = {};
});

const close = () => {
    emit('close');
};

const save = async () => {
    loading.value = true;
    errors.value = {};

    // Convert keywords from textarea to array
    const keywords = form.value.keywords
        .split('\n')
        .map(k => k.trim())
        .filter(k => k.length > 0);

    const data = {
        category: form.value.category,
        title: form.value.title,
        content: form.value.content,
        keywords,
        is_active: form.value.is_active,
    };

    try {
        const url = isEditMode.value
            ? `/operator/knowledge-base/${props.entry!.id}`
            : '/operator/knowledge-base';

        const method = isEditMode.value ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            if (response.status === 422) {
                const errorData = await response.json();
                errors.value = errorData.errors || {};
                return;
            }
            throw new Error('Failed to save entry');
        }

        emit('saved');
        emit('close');
    } catch (error) {
        console.error('Error saving entry:', error);
        errors.value = { general: 'Failed to save entry. Please try again.' };
    } finally {
        loading.value = false;
    }
};

const deleteEntry = async () => {
    if (!confirm('Are you sure you want to delete this entry? This cannot be undone.')) {
        return;
    }

    loading.value = true;

    try {
        const response = await fetch(`/operator/knowledge-base/${props.entry!.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to delete entry');
        }

        emit('saved');
        emit('close');
    } catch (error) {
        console.error('Error deleting entry:', error);
        errors.value = { general: 'Failed to delete entry. Please try again.' };
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="close"
        ></div>

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="relative w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl"
                @click.stop
            >
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ isEditMode ? 'Edit Knowledge Base Entry' : 'Create Knowledge Base Entry' }}
                    </h2>
                    <button
                        @click="close"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="save" class="space-y-4">
                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.category"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        >
                            <option v-for="cat in categories" :key="cat" :value="cat">
                                {{ cat.charAt(0).toUpperCase() + cat.slice(1) }}
                            </option>
                        </select>
                        <p v-if="errors.category" class="mt-1 text-sm text-red-600">
                            {{ errors.category }}
                        </p>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            placeholder="Enter title"
                        />
                        <p v-if="errors.title" class="mt-1 text-sm text-red-600">
                            {{ errors.title }}
                        </p>
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.content"
                            rows="8"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            placeholder="Enter content"
                        ></textarea>
                        <p v-if="errors.content" class="mt-1 text-sm text-red-600">
                            {{ errors.content }}
                        </p>
                    </div>

                    <!-- Keywords -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Keywords
                        </label>
                        <textarea
                            v-model="form.keywords"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            placeholder="Enter keywords, one per line"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Enter one keyword per line
                        </p>
                    </div>

                    <!-- Is Active Toggle -->
                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500"
                        />
                        <label class="ml-2 block text-sm text-gray-700">
                            Active
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-between pt-4">
                        <button
                            v-if="isEditMode"
                            type="button"
                            @click="deleteEntry"
                            :disabled="loading"
                            class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            Delete
                        </button>
                        <div v-else></div>

                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="close"
                                :disabled="loading"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="loading"
                                class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:opacity-50"
                            >
                                {{ loading ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
