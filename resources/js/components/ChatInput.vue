<script setup lang="ts">
import { ref } from 'vue';

interface Props {
    disabled?: boolean;
}

withDefaults(defineProps<Props>(), {
    disabled: false,
});

const emit = defineEmits<{
    sendMessage: [message: string];
}>();

const input = ref('');

function handleSubmit() {
    const message = input.value.trim();
    if (message) {
        emit('sendMessage', message);
        input.value = '';
    }
}
</script>

<template>
    <form
        @submit.prevent="handleSubmit"
        class="flex gap-2 rounded-full bg-white p-2 shadow-md"
    >
        <input
            v-model="input"
            type="text"
            :disabled="disabled"
            placeholder="Type your message..."
            class="flex-1 rounded-full border-0 px-4 py-2 text-sm text-gray-900 outline-none placeholder:text-gray-400 disabled:opacity-50"
        />
        <button
            type="submit"
            :disabled="disabled || !input.trim()"
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-600 text-white transition-colors hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="h-5 w-5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"
                />
            </svg>
        </button>
    </form>
</template>
