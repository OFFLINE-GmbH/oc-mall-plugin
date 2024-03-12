<template>
    <div ref="tooltip" class="tooltip" :class="[
        props.color ? `tooltip-${props.color}` : ''
    ]">
        <slot name="default" v-bind="props">
            <span>{{ props.label }}</span>
        </slot>
    </div>
</template>

<script lang="ts">
/**
 * Tooltip Properties
 */
export interface BaseTooltipProps {
    /**
     * The desired color used for this tooltip.
     */
    color?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger' | 'info';

    /**
     * The desired label text for this tooltip, used when no slot is passed.
     */
    label?: string;
}

/**
 * Tooltip Slots
 */
export interface BaseTooltipSlots {
    /**
     * Default content slot, used instead of the label property.
     * @param props 
     */
    default(props: BaseTooltipProps): any;
}

// Default Export, used for IDE-related auto-import features
export default {
    name: 'BaseTooltip'
}
</script>

<script lang="ts" setup>
import { ref } from 'vue';

// Define Component
const props = defineProps<BaseTooltipProps>();
const slots = defineSlots<BaseTooltipSlots>();

// States
const tooltip = ref<HTMLElement>();

// Expose Component
defineExpose({
    tooltip
});
</script>

<style scoped>
.tooltip {
    @apply w-max flex px-3 py-1.5 text-xs font-semibold opacity-100 rounded pointer-events-none;
    @apply bg-gray-800 text-gray-50 dark:bg-gray-200 dark:text-gray-800;
}

/** Colors */
.tooltip.tooltip-primary {
    @apply bg-primary-600 text-primary-50;
}
.tooltip.tooltip-secondary {
    @apply bg-gray-600 text-gray-50;
}
.tooltip.tooltip-success {
    @apply bg-success-600 text-success-50;
}
.tooltip.tooltip-warning {
    @apply bg-warning-600 text-warning-50;
}
.tooltip.tooltip-danger {
    @apply bg-danger-600 text-danger-50;
}
.tooltip.tooltip-info {
    @apply bg-info-600 text-info-50;
}
</style>
