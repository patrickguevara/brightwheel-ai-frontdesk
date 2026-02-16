<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Sidebar from '@/components/operator/Sidebar.vue';

interface Metrics {
    total_questions_today: number;
    total_questions_week: number;
    escalated_count: number;
    auto_resolved_percentage: number;
}

interface RecentActivity {
    id: string;
    event_type: string;
    category: string | null;
    created_at: string;
    conversation: {
        session_id: string;
        parent_name: string | null;
        status: string;
        latest_message: string | null;
    } | null;
}

interface Props {
    metrics: Metrics;
    recentActivity: RecentActivity[];
}

defineProps<Props>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
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
    <Head title="Operator Dashboard" />

    <div class="flex h-screen bg-gray-100">
        <Sidebar current-route="operator.dashboard" />

        <main class="flex-1 overflow-y-auto">
            <div class="p-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                    <p class="mt-2 text-gray-600">Monitor AI frontdesk performance and activity</p>
                </div>

                <!-- Metrics Cards -->
                <div class="mb-8 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Questions Today -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="mb-2 text-sm font-medium text-gray-600">Questions Today</div>
                        <div class="text-3xl font-bold text-gray-900">{{ metrics.total_questions_today }}</div>
                    </div>

                    <!-- Questions This Week -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="mb-2 text-sm font-medium text-gray-600">Questions This Week</div>
                        <div class="text-3xl font-bold text-gray-900">{{ metrics.total_questions_week }}</div>
                    </div>

                    <!-- Escalated -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="mb-2 text-sm font-medium text-gray-600">Escalated</div>
                        <div class="text-3xl font-bold text-red-600">{{ metrics.escalated_count }}</div>
                    </div>

                    <!-- Auto-Resolved -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="mb-2 text-sm font-medium text-gray-600">Auto-Resolved</div>
                        <div class="text-3xl font-bold text-green-600">{{ metrics.auto_resolved_percentage }}%</div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-xl font-semibold text-gray-900">Recent Activity</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="activity in recentActivity"
                            :key="activity.id"
                            class="px-6 py-4 hover:bg-gray-50"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="mb-1 flex items-center gap-2">
                                        <span class="font-medium text-gray-900">{{ activity.event_type }}</span>
                                        <span
                                            v-if="activity.conversation"
                                            :class="getStatusColor(activity.conversation.status)"
                                            class="rounded-full px-2 py-1 text-xs font-medium"
                                        >
                                            {{ activity.conversation.status }}
                                        </span>
                                    </div>
                                    <div v-if="activity.conversation" class="text-sm text-gray-600">
                                        <span v-if="activity.conversation.parent_name" class="font-medium">
                                            {{ activity.conversation.parent_name }}
                                        </span>
                                        <span v-else class="font-medium">
                                            Session: {{ activity.conversation.session_id.substring(0, 8) }}...
                                        </span>
                                    </div>
                                    <div v-if="activity.conversation?.latest_message" class="mt-1 text-sm text-gray-500">
                                        {{ activity.conversation.latest_message.substring(0, 100) }}
                                        <span v-if="activity.conversation.latest_message.length > 100">...</span>
                                    </div>
                                </div>
                                <div class="ml-4 text-sm text-gray-500">
                                    {{ formatDate(activity.created_at) }}
                                </div>
                            </div>
                        </div>
                        <div v-if="recentActivity.length === 0" class="px-6 py-8 text-center text-gray-500">
                            No recent activity
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
