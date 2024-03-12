<template>
    <div ref="container" class="page-container">
        <slot />
    </div>
</template>

<script lang="ts">
export interface PageContainerProps {
    /**
     * Use scrollable container
     */
    scrollable?: boolean | null;
}
</script>

<script lang="ts" setup>
import Scrollbar from 'smooth-scrollbar';
import { onMounted, ref } from 'vue';

// Define Component
const props = defineProps<PageContainerProps>();

// States
const container = ref<HTMLElement>();

// Component mounted
onMounted(() => {
    if (props.scrollable && container.value) {
        Scrollbar.init(container.value);
    }
})
</script>

<style scoped>
.page-container {
    @apply px-8 py-6;
    height: 100%;
}
</style>
