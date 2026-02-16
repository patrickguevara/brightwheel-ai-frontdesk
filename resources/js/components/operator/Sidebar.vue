<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    currentRoute: string;
}

const props = defineProps<Props>();

const isActive = (routeName: string) => {
    return props.currentRoute === routeName;
};

const navLinks = [
    { name: 'Dashboard', route: 'operator.dashboard' },
    { name: 'Conversations', route: 'operator.conversations' },
    { name: 'Knowledge Base', route: 'operator.knowledge-base' },
];
</script>

<template>
    <div class="flex h-screen w-64 flex-col bg-gray-900 text-white">
        <!-- Logo Section -->
        <div class="border-b border-gray-800 p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-bold">Little Oaks</div>
                    <div class="text-xs text-gray-400">Control Center</div>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 space-y-1 p-4">
            <Link
                v-for="link in navLinks"
                :key="link.route"
                :href="route(link.route)"
                :class="[
                    'flex items-center rounded-lg px-4 py-3 text-sm font-medium transition-colors',
                    isActive(link.route)
                        ? 'border-l-4 border-green-500 bg-gray-800 text-white'
                        : 'text-gray-300 hover:bg-gray-800 hover:text-white',
                ]"
            >
                {{ link.name }}
            </Link>
        </nav>

        <!-- Footer -->
        <div class="border-t border-gray-800 p-4">
            <div class="text-xs text-gray-500">
                © 2026 Little Oaks Preschool
            </div>
        </div>
    </div>
</template>
