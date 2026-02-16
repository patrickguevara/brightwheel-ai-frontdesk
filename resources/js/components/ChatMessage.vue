<script setup lang="ts">
import { computed } from 'vue';
import { marked } from 'marked';
import type { Message } from '@/types/chat';

const props = defineProps<{
    message: Message;
}>();

const renderedContent = computed(() => {
    if (props.message.role === 'assistant') {
        return marked.parse(props.message.content, { async: false });
    }
    return props.message.content;
});
</script>

<template>
    <div
        :class="[
            'flex gap-3',
            message.role === 'parent' ? 'justify-end' : 'justify-start',
        ]"
    >
        <!-- Assistant Avatar -->
        <div
            v-if="message.role === 'assistant'"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-600 text-white"
        >
            <span class="text-sm font-semibold">LO</span>
        </div>

        <!-- Message Bubble -->
        <div
            :class="[
                'max-w-[80%] rounded-2xl px-4 py-2',
                message.role === 'parent'
                    ? 'bg-green-600 text-white'
                    : 'bg-white text-gray-800 shadow-sm',
            ]"
        >
            <div
                v-if="message.role === 'assistant'"
                class="prose prose-sm max-w-none prose-p:my-2 prose-ul:my-2 prose-ol:my-2 prose-li:my-0"
                v-html="renderedContent"
            />
            <p
                v-else
                class="whitespace-pre-wrap text-sm"
            >
                {{ message.content }}
            </p>

            <!-- Low Confidence Warning -->
            <p
                v-if="
                    message.role === 'assistant' &&
                    message.confidence_score !== undefined &&
                    message.confidence_score < 0.5
                "
                class="mt-2 text-xs italic text-gray-500"
            >
                I'm not completely sure about this answer. Please verify with
                the staff.
            </p>
        </div>
    </div>
</template>
