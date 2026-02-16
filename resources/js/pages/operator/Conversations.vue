<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Sidebar from '@/components/operator/Sidebar.vue';

interface Conversation {
    id: string;
    session_id: string;
    parent_name: string | null;
    status: string;
    message_count: number;
    last_message: string | null;
    created_at: string;
}

interface PaginatedConversations {
    data: Conversation[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Props {
    conversations: PaginatedConversations;
}

defineProps<Props>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-blue-100 text-blue-800';
        case 'escalated':
            return 'bg-red-100 text-red-800';
        case 'resolved':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="Conversations" />

    <div class="flex h-screen bg-gray-100">
        <Sidebar current-route="operator.conversations" />

        <main class="flex-1 overflow-y-auto">
            <div class="p-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Conversations</h1>
                    <p class="mt-2 text-gray-600">View all parent conversations with the AI frontdesk</p>
                </div>

                <!-- Conversations Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Parent / Session
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Messages
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Last Message
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Created
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="conversation in conversations.data"
                                :key="conversation.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ conversation.parent_name || 'Anonymous' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ conversation.session_id.substring(0, 12) }}...
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        :class="getStatusColor(conversation.status)"
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold leading-5"
                                    >
                                        {{ conversation.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                    {{ conversation.message_count }}
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="conversation.last_message" class="max-w-md text-sm text-gray-500">
                                        {{ conversation.last_message.substring(0, 80) }}
                                        <span v-if="conversation.last_message.length > 80">...</span>
                                    </div>
                                    <div v-else class="text-sm text-gray-400">No messages</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(conversation.created_at) }}
                                </td>
                            </tr>
                            <tr v-if="conversations.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    No conversations found
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="conversations.last_page > 1" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ (conversations.current_page - 1) * conversations.per_page + 1 }}</span>
                                to
                                <span class="font-medium">{{ Math.min(conversations.current_page * conversations.per_page, conversations.total) }}</span>
                                of
                                <span class="font-medium">{{ conversations.total }}</span>
                                results
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in conversations.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'rounded px-3 py-1 text-sm',
                                        link.active
                                            ? 'bg-green-600 text-white'
                                            : link.url
                                              ? 'bg-white text-gray-700 hover:bg-gray-50'
                                              : 'cursor-not-allowed bg-gray-100 text-gray-400',
                                    ]"
                                    :preserve-state="true"
                                    :preserve-scroll="true"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
