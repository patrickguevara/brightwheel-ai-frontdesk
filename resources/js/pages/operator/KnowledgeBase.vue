<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Sidebar from '@/components/operator/Sidebar.vue';
import KnowledgeBaseModal from '@/components/operator/KnowledgeBaseModal.vue';

interface KnowledgeItem {
    id: string;
    title: string;
    content: string;
    keywords: string[];
    is_active: boolean;
    updated_at: string;
    updated_by_name: string | null;
    category: string;
}

interface Props {
    knowledgeByCategory: Record<string, KnowledgeItem[]>;
}

defineProps<Props>();

const expandedCategories = ref<Record<string, boolean>>({});
const showModal = ref(false);
const editingEntry = ref<KnowledgeItem | null>(null);

const toggleCategory = (category: string) => {
    expandedCategories.value[category] = !expandedCategories.value[category];
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const openCreateModal = () => {
    editingEntry.value = null;
    showModal.value = true;
};

const openEditModal = (item: KnowledgeItem) => {
    editingEntry.value = item;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingEntry.value = null;
};

const handleSaved = () => {
    // Reload the page to get fresh data
    window.location.reload();
};
</script>

<template>
    <Head title="Knowledge Base" />

    <div class="flex h-screen bg-gray-100">
        <Sidebar current-route="operator.knowledge-base" />

        <main class="flex-1 overflow-y-auto">
            <div class="p-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Knowledge Base
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Manage AI frontdesk knowledge and responses
                    </p>
                </div>

                <!-- Knowledge Sections -->
                <div class="space-y-4">
                    <div
                        v-for="(items, category) in knowledgeByCategory"
                        :key="category"
                        class="overflow-hidden rounded-lg bg-white shadow"
                    >
                        <!-- Category Header -->
                        <button
                            @click="toggleCategory(String(category))"
                            class="flex w-full items-center justify-between bg-gray-50 px-6 py-4 text-left hover:bg-gray-100"
                        >
                            <div class="flex items-center gap-3">
                                <svg
                                    :class="{
                                        'rotate-90':
                                            expandedCategories[
                                                String(category)
                                            ],
                                    }"
                                    class="h-5 w-5 text-gray-500 transition-transform"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                                <h2
                                    class="text-lg font-semibold text-gray-900 capitalize"
                                >
                                    {{ category }}
                                </h2>
                                <span
                                    class="rounded-full bg-gray-200 px-2 py-1 text-xs font-medium text-gray-700"
                                >
                                    {{ items.length }}
                                    {{ items.length === 1 ? 'item' : 'items' }}
                                </span>
                            </div>
                        </button>

                        <!-- Category Items -->
                        <div
                            v-if="expandedCategories[String(category)]"
                            class="divide-y divide-gray-200"
                        >
                            <div
                                v-for="item in items"
                                :key="item.id"
                                class="px-6 py-4 hover:bg-gray-50"
                            >
                                <div
                                    class="mb-2 flex items-start justify-between"
                                >
                                    <div class="flex-1">
                                        <div
                                            class="mb-1 flex items-center gap-2"
                                        >
                                            <h3
                                                class="text-base font-semibold text-gray-900"
                                            >
                                                {{ item.title }}
                                            </h3>
                                            <span
                                                v-if="!item.is_active"
                                                class="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800"
                                            >
                                                Inactive
                                            </span>
                                            <span
                                                v-else
                                                class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800"
                                            >
                                                Active
                                            </span>
                                        </div>
                                        <div class="mb-2 text-sm text-gray-600">
                                            {{ item.content.substring(0, 200) }}
                                            <span
                                                v-if="item.content.length > 200"
                                                >...</span
                                            >
                                        </div>
                                        <div
                                            v-if="
                                                item.keywords &&
                                                item.keywords.length > 0
                                            "
                                            class="mb-2 flex flex-wrap gap-1"
                                        >
                                            <span
                                                v-for="keyword in item.keywords"
                                                :key="keyword"
                                                class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-800"
                                            >
                                                {{ keyword }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Last updated:
                                            {{ formatDate(item.updated_at) }}
                                            <span v-if="item.updated_by_name">
                                                by {{ item.updated_by_name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="Object.keys(knowledgeByCategory).length === 0"
                        class="rounded-lg bg-white px-6 py-12 text-center shadow"
                    >
                        <div class="text-gray-500">
                            No knowledge base entries found
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
