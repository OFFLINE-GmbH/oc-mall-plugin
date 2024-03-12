
<template>
    <div class="loading-spinner">
        <div class="spinner" :class="[
            props.color ? `spinner-${props.color}`: '',
            props.size ? `spinner-${props.size}`: ''
        ]"></div>
    </div>
</template>

<script lang="ts">
/**
 * LoadingSpinner Properties
 */
export interface LoadingSpinnerProps {
    /**
     * The desired color used for the loading spinner.
     */
    color?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger' | 'info';

    /**
     * The desired size of the loading spinner.
     */
    size?: 'sm' | 'md' | 'lg';
}

// Default Export, used for IDE-related auto-import features
export default {
    name: 'LoadingSpinner'
}
</script>

<script lang="ts" setup>
// Define Component
const props = defineProps<LoadingSpinnerProps>();
</script>

<style scoped>
.loading-spinner {
    @apply relative;
}

.spinner {
    @apply w-12 h-12 border-solid !border-t-transparent rounded-full;
    @apply border-gray-900 dark:border-gray-100;
    animation: spin 1s linear 0s infinite;
    border-width: 3px;

    &.spinner-sm {
        @apply w-6 h-6 border-2;
    }
    
    &.spinner-lg {
        @apply w-20 h-20 border-4;
    }

    &.spinner-primary {
        @apply border-primary-600 dark:border-primary-600;
    }
    &.spinner-secondary {
        @apply border-gray-500 dark:border-gray-500;
    }
    &.spinner-success {
        @apply border-success-600 dark:border-success-600;
    }
    &.spinner-warning {
        @apply border-warning-600 dark:border-warning-600;
    }
    &.spinner-danger {
        @apply border-danger-600 dark:border-danger-600;
    }
    &.spinner-info {
        @apply border-info-600 dark:border-info-600;
    }
}
</style>
