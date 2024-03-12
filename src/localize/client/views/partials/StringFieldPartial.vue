<template>
    <div class="string-field-area">
        <div class="string-field-loading" :class="[disabled ? 'is-visible' : '']" >
            <LoaderCircle :size="16" />
        </div>
        <div class="string-field-success" :class="[success ? 'is-visible' : '']" >
            <Check :size="16" />
        </div>
        <div
            class="string-field"
            :class="[disabled ? 'is-disabled' : '']" 
            :lang="props.lang" 
            :disabled="disabled"
            spellcheck="true"
            @keyup="onMove"
            @blur="onSave"
            contenteditable>{{ inputValue }}</div>
    </div>
</template>

<script lang="ts">
export interface PartialProps {
    lang: string;
    file: string;
    localeKey: string;
    value: string;
}

export interface PartialEmits {
    (ev: 'next', event: KeyboardEvent): void;
    (ev: 'prev', event: KeyboardEvent): void;
    (ev: 'change', lang: string, file: string, key: string, value: string): void;
}
</script>

<script lang="ts" setup>
import { Check, LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { useLocaleStore } from '@/stores/locale';

// Define Component
const props = defineProps<PartialProps>();
const emits = defineEmits<PartialEmits>();

// Stores
const localeStore = useLocaleStore();

// States
const loading = ref<boolean>(false);
const disabled = ref<boolean>(false);
const success = ref<boolean>(false);
const original = ref<string>(props.value);
const inputValue = ref<string>(props.value);

/**
 * Move Cursor
 */
function onMove(ev: KeyboardEvent) {
    if (!ev.ctrlKey) {
        return;
    }
    if (ev.key == 'ArrowDown') {
        emits('next', ev);
    } else if (ev.key == 'ArrowUp') {
        emits('prev', ev);
    }
}

/**
 * Save Handler
 */
async function onSave(ev: Event) {
    if (loading.value) {
        return;
    }
    
    inputValue.value = (ev.target as HTMLElement).innerText;
    if (inputValue.value === original.value) {
        return;
    }

    loading.value = true;
    disabled.value = true;
    try {
        await localeStore.save(props.lang, props.file, props.localeKey, inputValue.value);
        original.value = inputValue.value;
        success.value = true;
        emits('change', props.lang, props.file, props.localeKey, original.value)
        setTimeout(() => {
            success.value = false;
        }, 3000);
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
        disabled.value = false;
    }
}
</script>

<style scoped>
.string-field-area {
    @apply relative;
}

.string-field-success {
    @apply absolute top-1/2 right-full mr-0 w-0 h-6 flex items-center justify-center -mt-3 overflow-hidden;
    @apply duration-300 ease-in-out;
    @apply text-success-600;
    transition-property: margin, width;

    &.is-visible {
        @apply w-6 mr-2;
    }
}

.string-field-loading {
    @apply absolute top-1/2 left-2 w-0 h-6 flex items-center justify-center -mt-3 overflow-hidden;
    @apply duration-300 ease-in-out;
    transition-property: width;

    &.is-visible {
        @apply w-6;
        animation: 2s linear 300ms infinite normal both spin;
    }
}

.string-field {
    @apply w-full resize-none border border-solid rounded text-sm py-1.5 px-2.5 outline-none shadow-none;
    @apply duration-300 ease-in-out;
    @apply border-gray-300;
    @apply dark:text-gray-400 dark:border-gray-900 dark:bg-gray-900;
    transition-property: background-color, border-color, color, padding;
    
    &:disabled,
    &.is-disabled {
        @apply pl-10;
        @apply border-gray-300 bg-gray-100;
    }

    &:not(:disabled):not(.is-disabled) {
        &:hover {
            @apply border-gray-400 dark:border-gray-700;
        }

        &:focus {
            @apply border-gray-800;
            @apply dark:text-gray-200 dark:border-gray-600;
        }
    }
}
</style>
