<script setup lang="ts">
const emit = defineEmits<{
    selectQuestion: [question: string];
}>();

const suggestedQuestions = [
    "What are your hours?",
    "Is there school on Veterans Day?",
    "What's the tuition for infants?",
    "My child has a fever, can they come in?",
    "Do you provide lunch?",
    "What time is nap?",
];

function handleQuestionClick(question: string) {
    emit('selectQuestion', question);
}
</script>

<template>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <button
            v-for="(question, index) in suggestedQuestions"
            :key="index"
            @click="handleQuestionClick(question)"
            class="rounded-full bg-white px-6 py-3 text-sm text-gray-700 shadow-sm transition-all hover:bg-green-50 hover:shadow-md active:scale-95"
        >
            {{ question }}
        </button>
    </div>
</template>
