<template>
    <div class="widget">
        <div class="widget-loading" v-if="props.loading">
            <span>Loading...</span>
        </div>
        <div class="widget-content" v-else>
            <slot />
        </div>
    </div>
</template>

<script lang="ts">
export interface BaseWidgetProps {
    /**
     * Loading State
     */
    loading: boolean;
}

export interface BaseWidgetSlots {
    /**
     * Default content Slot
     */
    default(): any;
}
</script>

<script lang="ts" setup>

// Define Component
const props = defineProps<BaseWidgetProps>();
const slots = defineSlots<BaseWidgetSlots>();
</script>

<style scoped>
.widget {
    @apply border border-solid rounded-lg;
    @apply border-gray-300 dark:border-gray-800;
}

.widget-loading {
    @apply flex items-center justify-center p-8;
    @apply text-gray-600;
}
</style>
