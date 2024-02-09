<template>
    <div class="spoiler" :class="active ? 'active' : null" @click="active = !active">
        <div class="spoiler-label">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
            <span>{{ $props.label }}</span>
        </div>

        <div ref="content" class="spoiler-content" :style="style">
            <div class="content-inner">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue';

// Define Component
defineProps<{
    label: string;
}>();

// States
const active = ref<boolean>(false);
const style = ref<{ height: string }>({ height: '0px' });
const content = ref<HTMLElement>();

// Watch active State
watch(active, newValue => {
    if (!content.value) {
        return;
    }

    if (newValue) {
        const clone = content.value.cloneNode(true) as HTMLElement;
        clone.classList.add('generated');
        content.value.parentElement?.appendChild(clone);

        const height = clone.offsetHeight
        clone.remove();
        style.value.height = `${height}px`;
    } else {
        style.value.height = `0px`;
    }
});
</script>

<style scoped>
.spoiler {
    display: flex;
    align-items: flex-start;
    flex-direction: column;
}

h3 + .spoiler,
.spoiler + .spoiler {
    margin-top: 1.0rem;
}

.spoiler-label {
    cursor: pointer;
    gap: 0.5rem;
    width: auto;
    margin: 0;
    padding: 0.5rem 0.75rem;
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    transition: background-color 300ms ease-in-out, color 300ms ease-in-out;
    border-radius: 0.35rem;

    &:hover {
        background-color: var(--vp-c-gray-3);
    }

    & svg {
        transition: transform 300ms ease-in-out;
    }

    .active & {
        color: var(--vp-c-bg);
        background-color: var(--vp-c-brand-3);

        & svg {
            transform: rotate(90deg);
        }
    }
}

.dark .spoiler.active .spoiler-label {
    color: var(--vp-c-white);
}

.spoiler-content {
    &:not(.generated) {
        height: 0;
        overflow: hidden;
        transition: height 300ms ease-in-out;
    }

    &.generated {
        height: auto !important;
        overflow: visible !important;
        position: absolute !important;
        z-index: -1 !important;
        opacity: 0 !important;
    }
}

.content-inner {
    padding: 0 1.0rem;
}
</style>
