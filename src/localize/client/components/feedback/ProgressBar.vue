<template>
    <div class="progress" :class="loading ? 'is-loading' : ''">
        <div class="progress-bar">
            <div class="progress-bar-inner" :class="color" :style="{ width: `${value || 0}%` }"></div>
        </div>
        <div class="progress-number" v-if="loading">∞</div>
        <div class="progress-number" v-else>{{ Math.floor(value || 0) }} %</div>
    </div>
</template>

<script lang="ts">
export type ProgressBarValue = any;

export interface ProgressBarProps {
    /**
     * Progress Bar Value
     */
    value: ProgressBarValue | Promise<ProgressBarValue>;

    /**
     * Promise Handler
     */
    valuer: (result: any) => number;
}
</script>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue';

// Define Component
const props = defineProps<ProgressBarProps>();

// States
const loading = ref<boolean>(props.value instanceof Promise);
const value = ref<number|null>(props.value instanceof Promise ? null : props.value)
const color = computed(() => {
    if (value.value == null) {
        return 'bg-transparent';
    } else {
        if (value.value >= 100) {
            return 'bg-emerald-600';
        } else if (value.value >= 90) {
            return 'bg-lime-600';
        } else if (value.value >= 80) {
            return 'bg-yellow-600';
        }else if (value.value >= 50) {
            return 'bg-orange-600';
        } else {
            return 'bg-danger-600';
        }
    }
});

// Component mounted
onMounted(() => {
    if (props.value instanceof Promise) {
        props.value.then((val) => {
            if (typeof props.valuer == 'function') {
                value.value = props.valuer(val);
            } else {
                value.value = val;
            }
        });
        props.value.finally(() => {
            loading.value = false;
        });
    }
});
</script>

<style scoped>
@keyframes loading {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(300%);
    }
}

.progress {
    @apply flex flex-col overflow-hidden;

    & .progress-bar {
        @apply relative w-full h-1 rounded-full;
        @apply bg-gray-300 dark:bg-gray-700;
    }

    & .progress-bar-inner {
        @apply absolute top-0 left-0 w-full h-1 rounded-full dark:opacity-75;
    }

    & .progress-number {
        @apply text-xs font-semibold tabular-nums text-center mt-0.5;
    }
    
    &.is-loading {
        & .progress-bar-inner {
            @apply !w-1/3 !bg-primary-600;
            transform: translateX(-100%);
            animation: 2s ease-in-out 0s infinite normal both loading;
        }
    }
}
</style>
