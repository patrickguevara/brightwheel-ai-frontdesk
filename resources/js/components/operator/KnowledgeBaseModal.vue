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
</script>

<template>
    <!-- Will add template in next step -->
    <div v-if="show">Modal Placeholder</div>
</template>
