<template>
    <div v-if="loading">
        Loading
    </div>
    <div ref="code" class="code" v-else v-html="html"></div>
</template>

<script lang="ts">
export interface ShikiSyntaxProps {
    code: string;
    lang: string;
    focus?: [number, number];
}
</script>

<script lang="ts" setup>
import { codeToHtml } from 'shiki';
import Scrollbar from 'smooth-scrollbar';
import { nextTick, ref, watch } from 'vue';

// Define Component
const props = defineProps<ShikiSyntaxProps>();

// States
const loading = ref<boolean>(false);
const code = ref<HTMLElement>();
const html = ref<string>();
const scrollbar = ref<any>();

// Watch Code Property
watch(() => props.code, async newValue => {
    loading.value = true;
    html.value = await codeToHtml(props.code, {
        lang: props.lang,
        theme: 'vitesse-dark',
        decorations: props.focus ? [
            {
                start: props.focus[0],
                end: props.focus[1],
                properties: { class: 'highlighted-word' }
            }
        ]: []
    });
    loading.value = false;

    await nextTick();
    if (code.value) {
        if (scrollbar.value) {
            scrollbar.value.destroy();
        }
        scrollbar.value = Scrollbar.init((code.value as any).querySelector('pre'));
    }
}, { immediate: true });
</script>

<style scoped>
.code {
    & :deep(pre) {
        @apply py-4 px-4 overflow-auto text-sm -mx-5;
    }

    & :deep(.line span) {
        @apply opacity-75 blur;
        @apply duration-300 ease-in-out transition-all;
        filter: blur(1px);
    }

    & :deep(pre:hover .line span) {
        filter: blur(0);
    }
    
    & :deep(.line span.highlighted-word) {
        @apply relative opacity-100;
        filter: none;
        
        &::before {
            @apply absolute -left-2 -top-0.5 -bottom-0.5 -right-2 rounded bg-white/15 dark:bg-white/10;
            box-shadow: 0 0 0 1px rgb(255 255 255 / 0.25);
            content: "";
        }
    }
}
</style>
