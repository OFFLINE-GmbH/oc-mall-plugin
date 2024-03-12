<template>
    <select
        :id="fieldId"
        :name="props.name || void 0"
        :class="[
            'field-input',
            'field-select',
            props.size ? `field-${props.size}` : '',
            props.validation ? `field-${props.validation}` : ''
        ]"
        :disabled="toValue(props.disabled || false) || typeof disabled == 'string'"
        :readonly="toValue(props.readonly || false) || typeof readonly == 'string'"
        :required="toValue(props.required || false) || typeof required == 'string'"
        :invalid="props.validation == 'invalid' ? true : void 0"
        v-model="value">
        <option v-if="props.placeholder" :value="null" disabled>{{ props.placeholder }}</option>
        <option v-for="(option, idx) of props.options" :value="Array.isArray(option) ? option[0] : option.value">
            {{ Array.isArray(option) ? (option[1] || option[0]) : option.label }}
        </option>
    </select>
</template>

<script lang="ts">
import type { MaybeRef } from 'vue';

export type SimpleSelectFieldOption = (string|number)[];

export interface AdvancedFieldOption {
    value: string;
    label: string;
}

export type SelectOptions = (SimpleSelectFieldOption|AdvancedFieldOption)[];

/**
 * SelectField Properties
 */
export interface SelectFieldProps {
    /**
     * A custom select field id, usually passed by the FormControl component. The default value is an 
     * auto-generated UUID.
     */
    id?: null | string;

    /**
     * The name attribute for this select field.
     */
    name?: null | string;

    /**
     * The desired options used for this select field.
     */
    options: SelectOptions;

    /**
     * The value for this select field, must be passed as v-model value.
     */
    modelValue?: null | string | number;

    /**
     * The placeholder attribute for this select field.
     */
    placeholder?: null | string;

    /**
     * The desired size for this select field, note that `md` is the default value.
     */
    size?: 'sm' | 'md' | 'lg';

    /**
     * The validation state for this select field.
     */
    validation?: null | 'invalid' | 'valid';

    /**
     * Additional select field validation message, requires the validation property set either to 
     * valid or invalid.
     */
    validationMessage?: null | string;

    /**
     * The disabled state for this select field.
     */
    disabled?: MaybeRef<boolean>;

    /**
     * The readonly state for this select field.
     */
    readonly?: MaybeRef<boolean>;

    /**
     * The required state for this select field.
     */
    required?: MaybeRef<boolean>;
}

/**
 * SelectField Emits
 */
export interface SelectFieldEmits {
    /**
     * Update model value handler.
     */
    (event: 'update:modelValue', value: string | number | null): void;
}

// Default Export, used for IDE-related auto-import features
export default {
    name: 'SelectField'
};
</script>

<script lang="ts" setup>
import { computed, toValue } from 'vue';

// Define Component
const props = defineProps<SelectFieldProps>();
const emits = defineEmits<SelectFieldEmits>();

// States
const value = computed({
    get() {
        return props.modelValue || props.modelValue === 0 ? props.modelValue : null;
    },
    set(value) {
        emits('update:modelValue', value);
    }
});
const fieldId = computed<string>(() => props.id || `field-${crypto.randomUUID().replace(/\-/g, '')}`);
</script>

<style scoped>
.field-select {
    @apply w-full h-10 px-4 py-2.5 border border-solid rounded-md outline-none shadow-none appearance-none;
    @apply duration-300 ease-in-out;
    @apply bg-transparent border-gray-400;
    @apply dark:bg-gray-900 dark:border-gray-700;
    transition-property: background-color, border-color, box-shadow, color;
    background-size: 24px 24px;
    background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIGNsYXNzPSJsdWNpZGUgbHVjaWRlLWNoZXZyb24tZG93biI+PHBhdGggZD0ibTYgOSA2IDYgNi02Ii8+PC9zdmc+');
    background-repeat: no-repeat;
    background-position: center right 1.0rem;

    &:disabled {
        @apply cursor-not-allowed;
        @apply bg-gray-200 border-gray-400 text-gray-500;
        @apply dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400;
    }

    .is-valid &,
    &.field-valid {
        @apply border-success-600;
    }

    .is-invalid &,
    &.field-invalid {
        @apply border-danger-600;
    }

    &:not(:disabled) {
        @apply cursor-pointer;
    }

    &:not(:disabled):hover {
        @apply border-gray-600;
    }
    
    &:not(:disabled):focus {
        @apply border-primary-600 shadow-primary-400/30;
        box-shadow: 0 0 0 3px var(--tw-shadow-color);
    }
}

/** Sizes */
.field-select.field-sm {
    @apply h-8 py-1.5;
    /* required to prevent zoom-behaviour on apple devices, added here too for consistency */
    /* @see https://css-tricks.com/16px-or-larger-text-prevents-ios-form-zoom/ */
    font-size: 16px;

    @screen md {
        @apply text-sm;
    }
}
.field-select.field-lg {
    @apply h-12 py-4 text-lg;
}
</style>
