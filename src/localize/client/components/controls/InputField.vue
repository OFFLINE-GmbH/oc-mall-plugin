<template>
    <input ref="input"
        :id="fieldId"
        :type="props.type"
        :name="props.name || void 0"
        :placeholder="props.placeholder || void 0"
        :inputmode="props.inputmode || void 0"
        :autocomplete="props.autocomplete || void 0"
        :step="((isDateTime ? '1' : void 0) as string | undefined)"
        :class="[
            'field-input', 
            `field-${props.type}`, 
            props.size ? `field-${props.size}` : '',
            props.validation ? `field-${props.validation}` : ''
        ]" 
        :disabled="toValue(props.disabled || false) || typeof disabled == 'string'"
        :readonly="toValue(props.readonly || false) || typeof readonly == 'string'"
        :required="toValue(props.required || false) || typeof required == 'string'"
        :invalid="props.validation == 'invalid' ? true : void 0"
        v-model="value" />
</template>

<script lang="ts">
import type { MaybeRef } from 'vue';

export type InputTypes = 'date'
                       | 'datetime-local'
                       | 'email'
                       | 'file'
                       | 'month'
                       | 'number'
                       | 'password'
                       | 'search'
                       | 'tel'
                       | 'text'
                       | 'time'
                       | 'url'
                       | 'week';

export type InputModes = 'none'
                       | 'text'
                       | 'decimal'
                       | 'numeric'
                       | 'tel'
                       | 'search'
                       | 'email'
                       | 'url';

export type InputAutoCompleteAddressValues = 'street-address'
                    | 'address-line1'
                    | 'address-line2'
                    | 'address-line3'
                    | 'address-level1'
                    | 'address-level2'
                    | 'address-level3'
                    | 'address-level4'
                    | 'country'
                    | 'country-name'
                    | 'postal-code';

export type InputAutoCompleteValues = 'off'
                                    | 'on'
                                    | 'name'
                                    | 'honorific-prefix'
                                    | 'given-name'
                                    | 'additional-name'
                                    | 'family-name'
                                    | 'honorific-suffix'
                                    | 'nickname'
                                    | 'email'
                                    | 'username'
                                    | 'new-password'
                                    | 'current-password'
                                    | 'one-time-code'
                                    | 'organization-title'
                                    | 'organization'
                                    | 'shipping'
                                    | 'billing'
                                    | 'cc-name'
                                    | 'cc-given-name'
                                    | 'cc-additional-name'
                                    | 'cc-family-name'
                                    | 'cc-number'
                                    | 'cc-exp'
                                    | 'cc-exp-month'
                                    | 'cc-exp-year'
                                    | 'cc-csc'
                                    | 'cc-type'
                                    | 'transaction-currency'
                                    | 'transaction-amount'
                                    | 'language'
                                    | 'bday'
                                    | 'bday-day'
                                    | 'bday-month'
                                    | 'bday-year'
                                    | 'sex'
                                    | 'tel'
                                    | 'tel-country-code'
                                    | 'tel-national'
                                    | 'tel-area-code'
                                    | 'tel-local'
                                    | 'tel-extension'
                                    | 'impp'
                                    | 'url'
                                    | 'photo'
                                    | 'webauthn'
                                    | InputAutoCompleteAddressValues
                                    | `billing ${InputAutoCompleteAddressValues}`
                                    | `shipping ${InputAutoCompleteAddressValues}`;

/**
 * InputField Properties
 */
export interface InputFieldProps {
    /**
     * A custom input field id, usually passed by the FormControl component. The default value is an 
     * auto-generated UUID.
     */
    id?: null | string;

    /**
     * The type attribute for this input field.
     */
    type?: InputTypes;

    /**
     * The name attribute for this input field.
     */
    name?: null | string;

    /**
     * The value for this input field, must be passed as v-model value.
     */
    modelValue?: null | number | string;

    /**
     * The placeholder attribute for this input field.
     */
    placeholder?: null | string;

    /**
     * Additional browser-supported inputmode attribute for this input field.
     */
    inputmode?: InputModes;

    /**
     * Additional browser-supported autocomplete attribute for this input field.
     */
    autocomplete?: InputAutoCompleteValues;

    /**
     * The desired size for this input field, note that `md` is the default value.
     */
    size?: 'sm' | 'md' | 'lg';

    /**
     * The validation state for this input field.
     */
    validation?: null | 'invalid' | 'valid';

    /**
     * Additional input field validation message, requires the validation property set either to 
     * valid or invalid.
     */
    validationMessage?: null | string;

    /**
     * The disabled state for this input field.
     */
    disabled?: MaybeRef<boolean>;

    /**
     * The readonly state for this input field.
     */
    readonly?: MaybeRef<boolean>;

    /**
     * The required state for this input field.
     */
    required?: MaybeRef<boolean>;
}

/**
 * InputField Emits
 */
export interface InputFieldEmits {
    /**
     * Update model value handler.
     */
    (event: 'update:modelValue', value: null | number | string): void;
}

// Default Export, used for IDE-related auto-import features
export default {
    name: 'InputField'
};
</script>

<script lang="ts" setup>
import { computed, toValue, ref } from 'vue';

// Define Component
const props = withDefaults(defineProps<InputFieldProps>(), {
    type: 'text'
});
const emits = defineEmits<InputFieldEmits>();

// States
const input = ref<HTMLInputElement>();
const value = computed({
    get() {
        return props.modelValue || props.modelValue === 0 ? props.modelValue : null;
    },
    set(value) {
        emits('update:modelValue', value);
    }
});
const fieldId = computed<string>(() => props.id || `field-${crypto.randomUUID().replace(/\-/g, '')}`);

/**
 * Check for datetime inputs for required 'step' attribute iOS fix
 */
const isDateTime = computed<boolean>(() => {
    return ['date', 'month', 'week', 'time', 'datetime', 'datetime-local'].indexOf(props.type) >= 0;
});

// Expose Component
defineExpose({
    input
});
</script>

<style scoped>
.field-input {
    @apply w-full h-10 px-3 py-2.5 border border-solid rounded-md outline-none shadow-none font-normal;
    @apply duration-300 ease-in-out;
    @apply bg-transparent border-gray-400;
    @apply dark:bg-gray-900 dark:border-gray-700;
    transition-property: background-color, border-color, box-shadow, color;

    &:disabled {
        @apply cursor-not-allowed;
        @apply bg-gray-200 border-gray-400 text-gray-500;
        @apply dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400;
    }

    &:read-only {
        @apply bg-gray-200 border-gray-400;
        @apply dark:bg-gray-700 dark:border-gray-500;
    }

    .is-valid &,
    &.field-valid {
        @apply border-success-600;
    }

    .is-invalid &,
    &.field-invalid {
        @apply border-danger-600;
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
.field-input.field-sm {
    @apply h-8 py-1.5;
    /* required to prevent zoom-behaviour on apple devices */
    /* @see https://css-tricks.com/16px-or-larger-text-prevents-ios-form-zoom/ */
    font-size: 16px;

    @screen md {
        @apply text-sm;
    }
}
.field-input.field-lg {
    @apply h-12 py-4 text-lg;
}

/** Condensed */
</style>
