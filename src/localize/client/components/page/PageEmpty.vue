<template>
    <div class="empty">
        <div class="relative">
            <Rat :size="48" :stroke-width="1" />
            <Rat :size="48" :stroke-width="1" class="blurry" />
        </div>
        <div class="empty-text">
            <h2>{{ props.title }}</h2>
            <p>{{ props.text }}</p>
        </div>
    </div>
</template>

<script lang="ts">
export interface PageEmptyProps {
    title: string;
    text: string; 
}
</script>

<script lang="ts" setup>
import { Rat } from 'lucide-vue-next';

// Define Component
const props = defineProps<PageEmptyProps>();
</script>

<style scoped>
@keyframes hue {
    0% {
        @apply text-emerald-600;
    }
    25% {
        @apply text-indigo-600;
    }
    50% {
        @apply text-pink-600;
    }
    75% {
        @apply text-amber-600;
    }
    100% {
        @apply text-emerald-600;
    }
}
@keyframes hueDark {
    0% {
        @apply text-emerald-400;
    }
    25% {
        @apply text-indigo-400;
    }
    50% {
        @apply text-pink-400;
    }
    75% {
        @apply text-amber-400;
    }
    100% {
        @apply text-emerald-400;
    }
}

.empty {
    @apply w-auto border border-solid rounded-lg p-6 mx-auto my-6 flex flex-row items-center self-center gap-4;
    @apply border-gray-300 bg-gray-100 dark:border-gray-700 dark:bg-gray-800;
    max-width: 440px;

    & svg {
        @apply shrink-0 grow-0 mr-2 opacity-50;
        @apply text-emerald-600;
        animation: 5s ease-in-out 1s infinite reverse both hue;

        .dark & {
            animation-name: hueDark;
        }

        &.blurry {
            @apply absolute top-0 left-0 scale-110;
            filter: blur(5px)
        }
    }

    & h2 {
        @apply text-lg font-semibold
    }
    
    & p {
        @apply text-xs;
    }
}
</style>
