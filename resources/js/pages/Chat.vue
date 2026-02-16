<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { nextTick, ref, watch } from 'vue';
import ChatInput from '@/components/ChatInput.vue';
import ChatMessage from '@/components/ChatMessage.vue';
import SuggestedQuestions from '@/components/SuggestedQuestions.vue';
import type { ChatResponse, Message } from '@/types/chat';

const messages = ref<Message[]>([]);
const sessionId = ref<string | null>(null);
const parentName = ref<string>('');
const nameInput = ref<string>('');
const hasStartedChat = ref(false);
const isLoading = ref(false);
const messagesContainer = ref<HTMLElement | null>(null);

function getCsrfToken(): string | null {
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');
}

async function scrollToBottom() {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight;
    }
}

watch(messages, () => {
    scrollToBottom();
});

async function sendMessage(content: string) {
    if (!content.trim()) return;

    // Validate message length (matches backend validation)
    if (content.length > 1000) {
        const errorMessage: Message = {
            id: `error-${Date.now()}`,
            role: 'assistant',
            content:
                'Your message is too long. Please keep it under 1000 characters.',
            created_at: new Date().toISOString(),
        };
        messages.value.push(errorMessage);
        return;
    }

    const parentMessage: Message = {
        id: `temp-${Date.now()}`,
        role: 'parent',
        content,
        created_at: new Date().toISOString(),
    };

    messages.value.push(parentMessage);
    isLoading.value = true;

    try {
        const csrfToken = getCsrfToken();
        const response = await fetch('/api/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
            },
            body: JSON.stringify({
                message: content,
                ...(sessionId.value && { session_id: sessionId.value }),
                ...(!sessionId.value && { parent_name: parentName.value }),
            }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data: ChatResponse = await response.json();

        if (!sessionId.value && data.session_id) {
            sessionId.value = data.session_id;
        }

        messages.value.push(data.message);
    } catch (error) {
        console.error('Failed to send message:', error);

        const errorMessage: Message = {
            id: `error-${Date.now()}`,
            role: 'assistant',
            content:
                'Sorry, I encountered an error. Please try again or contact the office directly.',
            created_at: new Date().toISOString(),
        };

        messages.value.push(errorMessage);
    } finally {
        isLoading.value = false;
    }
}

function handleSuggestedQuestion(question: string) {
    sendMessage(question);
}

function startChat() {
    if (!nameInput.value.trim()) return;
    parentName.value = nameInput.value.trim();
    hasStartedChat.value = true;
}
</script>

<template>
    <Head title="Chat with Little Oaks" />

    <div
        class="flex h-screen flex-col bg-gradient-to-br from-green-50 to-yellow-50"
    >
        <!-- Header -->
        <header class="bg-white p-4 shadow-sm">
            <div class="mx-auto flex max-w-4xl items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-green-600 text-white"
                >
                    <span class="text-lg font-bold">LO</span>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">
                        Little Oaks Preschool
                    </h1>
                    <p class="text-xs text-gray-500">
                        Ask me anything about the school
                    </p>
                </div>
            </div>
        </header>

        <!-- Messages Area -->
        <div ref="messagesContainer" class="flex-1 overflow-y-auto">
            <div class="mx-auto max-w-4xl p-4">
                <!-- Name Collection Screen -->
                <div
                    v-if="!hasStartedChat"
                    class="flex flex-col items-center justify-center py-12"
                >
                    <div
                        class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-600 text-white"
                    >
                        <span class="text-3xl font-bold">LO</span>
                    </div>
                    <h2 class="mb-2 text-2xl font-bold text-gray-800">
                        Welcome to Little Oaks!
                    </h2>
                    <p class="mb-8 text-center text-gray-600">
                        I'm here to answer your questions about our preschool.
                    </p>
                    <form class="w-full max-w-md" @submit.prevent="startChat">
                        <div class="mb-4">
                            <label
                                for="name"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                What's your name?
                            </label>
                            <input
                                id="name"
                                v-model="nameInput"
                                type="text"
                                name="name"
                                required
                                maxlength="255"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 placeholder:text-gray-400 focus:border-green-600 focus:ring-2 focus:ring-green-600 focus:outline-none"
                                placeholder="Enter your name"
                            />
                        </div>
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-green-600 px-4 py-2 font-medium text-white hover:bg-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none"
                        >
                            Start Chat
                        </button>
                    </form>
                </div>

                <!-- Welcome Screen with Suggested Questions -->
                <div
                    v-else-if="messages.length === 0 && !isLoading"
                    class="flex flex-col items-center justify-center py-12"
                >
                    <div
                        class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-600 text-white"
                    >
                        <span class="text-3xl font-bold">LO</span>
                    </div>
                    <h2 class="mb-2 text-2xl font-bold text-gray-800">
                        Hi {{ parentName }}!
                    </h2>
                    <p class="mb-8 text-center text-gray-600">
                        Ask me anything about Little Oaks, or try one of these:
                    </p>
                    <div class="w-full max-w-2xl">
                        <SuggestedQuestions
                            @select-question="handleSuggestedQuestion"
                        />
                    </div>
                </div>

                <!-- Messages -->
                <div v-else class="space-y-4">
                    <ChatMessage
                        v-for="message in messages"
                        :key="message.id"
                        :message="message"
                    />

                    <!-- Loading Indicator -->
                    <div v-if="isLoading" class="flex justify-start">
                        <div class="flex gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-600 text-white"
                            >
                                <span class="text-sm font-semibold">LO</span>
                            </div>
                            <div
                                class="flex items-center gap-1 rounded-2xl bg-white px-4 py-2 shadow-sm"
                            >
                                <div
                                    class="h-2 w-2 animate-bounce rounded-full bg-gray-400"
                                    style="animation-delay: 0ms"
                                />
                                <div
                                    class="h-2 w-2 animate-bounce rounded-full bg-gray-400"
                                    style="animation-delay: 150ms"
                                />
                                <div
                                    class="h-2 w-2 animate-bounce rounded-full bg-gray-400"
                                    style="animation-delay: 300ms"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div
            v-if="hasStartedChat"
            class="border-t border-gray-200 bg-white p-4"
        >
            <div class="mx-auto max-w-4xl">
                <ChatInput :disabled="isLoading" @send-message="sendMessage" />
            </div>
        </div>
    </div>
</template>
