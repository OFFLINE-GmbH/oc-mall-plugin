<template>
    <button
        :type="props.type || 'button'"
        class="btn btn-shadow" 
        :class="[
            props.color ? `btn-${props.color}` : '',
            props.size ? `btn-${props.size}` : '',
            toValue(props.active || false) ? 'btn-active' : '',
            toValue(props.disabled || false) ? 'btn-disabled' : '',
            toValue(props.loading || false) ? 'btn-loading' : ''
        ]"
        :title="props.title || void 0"
        :disabled="toValue(props.disabled || false) || toValue(props.loading || false)">
        <component :is="props.icon" v-bind="iconBinding" />
    </button>
</template>
  
<script lang="ts">
import type { Component, MaybeRef } from 'vue';

/**
 * ShadowButton Properties
 */
export interface ShadowButtonProps {
    /**
     * The desired color used for this button.
     */
    color?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger' | 'info';

    /**
     * An additional icon which is displayed next to the label, if available.
     */
    icon: Component;

    /**
     * Additional properties passed to the icon component, such as `size` or `stroke-width`.
     */
    iconProps?: { [key: string]: any };

    /**
     * The desired size of the button, note that `md` is the default value.
     */
    size?: 'sm' | 'md' | 'lg';

    /**
     * The desired type of the button, note that `button` is the default value.
     */
    type?: 'button' | 'submit' | 'reset';

    /**
     * An alternative text that is used as title attribute on the button.
     */
    title?: string;

    /**
     * The active state for this button.
     */
    active?: MaybeRef<boolean> | boolean;

    /**
     * The disabled state for this button.
     */
    disabled?: MaybeRef<boolean> | boolean;

    /**
     * The loading state for this button.
     */
    loading?: MaybeRef<boolean> | boolean;
}

// Default Export, used for IDE-related auto-import features
export default {
    name: 'ShadowButton'
}
</script>
  
<script lang="ts" setup>
import { computed, toValue } from 'vue';

// Define Component
const props = defineProps<ShadowButtonProps>();

// States
const iconBinding = computed<{ [key: string]: any }>(() => {
    const result = {
        size: { sm: 16, md: 24, lg: 32 }[props.size || 'md'],
        strokeWidth: (props.size || 'md') == 'sm' ? 2 : 1.5,
    };
    return Object.assign(result, props.iconProps || {});
});
</script>
  
<style scoped>
.btn.btn-shadow {
    @apply w-10 h-10 flex items-center justify-center relative p-0 border-0 outline-none rounded-full cursor-pointer;
    @apply duration-300 ease-in-out transition-colors;
    @apply text-gray-600 dark:text-gray-500 bg-transparent;

    svg {
        @apply z-20 pointer-events-none;
    }

    &::before {
        @apply w-12 h-12 absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 rounded-full z-10 scale-0;
        @apply bg-gray-200 dark:bg-gray-800;
        @apply duration-300 ease-in-out transition-transform;
        content: '';
    }

    &:hover,
    &.btn-active {
        @apply text-gray-900 dark:text-gray-300;

        &::before {
            @apply scale-100;
        }
    }
}

/** Sizes */
.btn.btn-shadow.btn-sm {
    @apply w-8 h-8;

    &::before {
        @apply w-10 h-10;
    }
}
.btn.btn-shadow.btn-lg {
    @apply w-12 h-12;

    &::before {
        @apply w-14 h-14;
    }
}

/** Colors */
.btn.btn-shadow.btn-primary {
    @apply text-primary-900 dark:text-primary-300;
    
    &::before {
        @apply bg-primary-700/15;
    }
}

.btn.btn-shadow.btn-secondary {
    @apply text-gray-900 dark:text-gray-300;
    
    &::before {
        @apply bg-gray-700/15;
    }
}

.btn.btn-shadow.btn-success {
    @apply text-success-900 dark:text-success-300;
    
    &::before {
        @apply bg-success-700/15;
    }
}

.btn.btn-shadow.btn-warning {
    @apply text-warning-900 dark:text-warning-300;
    
    &::before {
        @apply bg-warning-700/15;
    }
}

.btn.btn-shadow.btn-danger {
    @apply text-danger-900 dark:text-danger-300;
    
    &::before {
        @apply bg-danger-700/15;
    }
}

.btn.btn-shadow.btn-info {
    @apply text-info-900 dark:text-info-300;
    
    &::before {
        @apply bg-info-700/25;
    }
}
</style>
  