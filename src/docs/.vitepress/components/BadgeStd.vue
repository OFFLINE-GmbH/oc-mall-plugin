<template>
    <div class="badge" :class="`badge-${color}`">
        <div class="badge-inner">
            <slot>
                {{ $props.label }}
            </slot>
        </div>
    </div>
</template>

<script lang="ts" setup>
defineProps<{
    color?: 'tip' | 'info' | 'warning' | 'danger';
    label?: string;
}>()
</script>

<style scoped>
.badge {
    margin: 0.5rem 0;
    display: flex;
}

.badge-inner {
    color: var(--vp-badge-danger-text);
    background-color: var(--vp-badge-danger-bg);
    padding: 0.15rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.35rem;
}

.badge-tip .badge-inner {
    color: var(--vp-badge-tip-text);
    background-color: var(--vp-badge-tip-bg);
}

.badge-info .badge-inner {
    color: var(--vp-badge-info-text);
    background-color: var(--vp-badge-info-bg);
}

.badge-warning .badge-inner {
    color: var(--vp-badge-warning-text);
    background-color: var(--vp-badge-warning-bg);
}

.badge-danger .badge-inner {
    color: var(--vp-badge-danger-text);
    background-color: var(--vp-badge-danger-bg);
}
</style>
