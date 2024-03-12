<template>
    <button type="button" 
        class="action-button" 
        :class="[$props.color ? `btn-${$props.color}` : '']"
        :title="props.title || void 0"
        @click.prevent="(ev) => emits('click', ev)">
        <component :is="props.icon" :size="16" :stroke-width="2" />
    </button>
</template>
  
<script lang="ts">
import type { Component } from 'vue';

export interface ActionButtonProps {
    /**
     * The color of this button.
     */
    color?: 'primary' | 'secondary' | 'danger' | 'warning' | 'success' | 'info' | null;

    /**
     * The icon used on this button.
     */
    icon?: Component;

    /**
     * Alternative title text to increase a11y.
     */
    title?: string;
}

export interface ActionButtonEmits {
    /**
     * Click event handler
     * @param event
     */
    (ev: 'click', event: Event): void;
}
</script>
  
<script lang="ts" setup>

// Define Component
const props = defineProps<ActionButtonProps>();
const emits = defineEmits<ActionButtonEmits>();
</script>
  
<style scoped>
.action-button {
    @apply w-8 h-8 flex border border-solid items-center justify-center relative p-0 outline-none rounded-md cursor-pointer;
    @apply duration-300 ease-in-out transition-colors;
    @apply bg-white text-zinc-500 border-white dark:bg-zinc-900 dark:text-zinc-400 dark:border-zinc-900;

    &:hover {
        @apply text-zinc-900 dark:text-zinc-300;
    }

    svg {
        @apply z-20 pointer-events-none;
    }
}

.action-button.btn-primary {
    &:hover {
        @apply bg-indigo-50 text-indigo-700 border-indigo-700;
        @apply dark:bg-indigo-700 dark:text-black dark:border-indigo-700;
    }
}

.action-button.btn-secondary {
    &:hover {
        @apply text-gray-900 bg-gray-200 border-gray-400;
        @apply dark:text-gray-900 dark:bg-gray-300 dark:border-gray-200;
    }
}

.action-button.btn-danger {
    &:hover {
        @apply bg-red-50 text-red-700 border-red-700;
        @apply dark:bg-red-700 dark:text-black dark:border-red-700;
    }
}

.action-button.btn-warning {
    &:hover {
        @apply bg-amber-50 text-amber-700 border-amber-700;
        @apply dark:bg-amber-700 dark:text-black dark:border-amber-700;
    }
}

.action-button.btn-success {
    &:hover {
        @apply bg-lime-50 text-lime-700 border-lime-700;
        @apply dark:bg-lime-700 dark:text-black dark:border-lime-700;
    }
}

.action-button.btn-info {
    &:hover {
        @apply bg-sky-50 text-sky-700 border-sky-700;
        @apply dark:bg-sky-700 dark:text-black dark:border-sky-700;
    }
}

</style>
  