<template>
    <slot name="default" v-bind="props" :show="show" :hide="hide"></slot>

    <BaseTooltip ref="tooltip" 
        :color="props.color"
        :label="$slots.label ? void 0 : (props.label || void 0)" 
        :class="[`tooltip-${placement}`, visible ? `is-visible` : '']" 
        :style="floatingStyles">
        <template #default v-if="$slots.label">{{ slots.label(props) }}</template>
    </BaseTooltip>
</template>

<script lang="ts">
import type { Middleware, Placement, OffsetOptions } from '@floating-ui/vue';

/**
 * Tooltip Properties
 */
export interface TooltipProps {
    /**
     * The desired color used for this tooltip.
     */
    color?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger' | 'info';

    /**
     * The desired label text for this tooltip, used when no slot is passed.
     */
    label?: string;

    /**
     * The amount of milliseconds until the tooltip starts to get visible.
     */
    delay?: number;

    /**
     * The floating-ui placement option for this tooltip.
     */
    placement?: Placement;

    /**
     * The floating-ui offset option for this tooltip.
     */
    offset?: OffsetOptions;
}

/**
 * Tooltip Slots
 */
export interface TooltipSlots {
    /**
     * Default content slot, used instead of the label property.
     * @param props 
     */
    default(props: TooltipProps & { show: (ev: Event) => void, hide: (ev: Event) => void }): any;

    /**
     * Label content slot, used instead of the label property.
     * @param props 
     */
    label(props: TooltipProps): any;
}

// Default Export, used for IDE-related auto-import features
export default {
    name: 'TooltipStd'
}
</script>

<script lang="ts" setup>
import { useFloating, offset } from '@floating-ui/vue';
import { nextTick, ref, watch } from 'vue';
import BaseTooltip from './BaseTooltip.vue';

// Define Component
const props = withDefaults(defineProps<TooltipProps>(), {
    placement: 'top',
    offset: 10
});
const slots = defineSlots<TooltipSlots>();

// States
const target = ref<HTMLElement>();
const tooltip = ref<InstanceType<typeof BaseTooltip>>();
const tooltipPlacement = ref<Placement>(props.placement);
const tooltipMiddleware = ref<Middleware[]>([
    offset(props.offset || 0)
]);
const timeout = ref<number>();
const visible = ref<boolean>(false);

// Calculate Tooltip Position
const { floatingStyles, placement } = useFloating(target, tooltip, {
    placement: tooltipPlacement,
    middleware: tooltipMiddleware
});

/**
 * Watch Property Changes
 */
watch(props, () => {
    tooltipPlacement.value = props.placement || tooltipPlacement.value;
    tooltipMiddleware.value = [
        offset(props.offset || 0)
    ];
});

/**
 * Show Tooltip
 * @param ev 
 */
function show(ev: Event | PointerEvent) {
    if (props.delay) {
        timeout.value = setTimeout(() => {
            target.value = ev.target as HTMLElement;
            visible.value = true;
        }, props.delay) as any as number;
    } else {
        target.value = ev.target as HTMLElement;
        visible.value = true;
    }
}

/**
 * Hide Tooltip
 * @param ev 
 */
function hide(ev: Event | PointerEvent) {
    if (props.delay) {
        clearTimeout(timeout.value);
        nextTick(() => {
            target.value = void 0;
            visible.value = false;
        });
    } else {
        target.value = void 0;
        visible.value = false;
    }
}

// Expose Component
defineExpose({
    show,
    hide,
    target,
    tooltip
})
</script>

<style scoped>
.tooltip {
    @apply absolute opacity-0 duration-300 ease-in-out;
    z-index: 110;
    transition-property: opacity, margin;

    &.is-visible {
        @apply opacity-100;
    }
    
    &.tooltip-top,
    &.tooltip-top-start,
    &.tooltip-top-end {
        @apply mt-2;

        &.is-visible {
            @apply mt-0;
        }
    }

    &.tooltip-right,
    &.tooltip-right-start,
    &.tooltip-right-end {
        @apply -ml-2;

        &.is-visible {
            @apply ml-0;
        }
    }

    &.tooltip-bottom,
    &.tooltip-bottom-start,
    &.tooltip-bottom-end {
        @apply -mt-2;

        &.is-visible {
            @apply mt-0;
        }
    }
    
    &.tooltip-left,
    &.tooltip-left-start,
    &.tooltip-left-end {
        @apply ml-2;

        &.is-visible {
            @apply ml-0;
        }
    }
}
</style>
