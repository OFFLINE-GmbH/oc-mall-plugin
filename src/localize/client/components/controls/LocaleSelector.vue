<template>
    <div ref="selector" class="locale-selector-field" v-if="visibleState">
        <div class="selector-list" v-if="!loading">
            <div class="list-entry" v-for="[code, locale] of Object.entries(localeStore.locales)" :key="code" @click="localeStore.select(code)">
                <div class="entry-title">
                    <span class="title-real">{{ locale.name }}</span>
                    <span class="title-english">{{ locale.english }}</span>
                </div>
                <div class="entry-locale">
                    <span>{{ code }}</span>
                </div>
            </div>
        </div>
        <div class="flex flex-col items-center gap-2 py-8" v-else>
            <LoadingSpinner size="sm" color="primary" />
        </div>
    </div>
</template>

<script lang="ts">
export interface LocaleSelectorProps {
    /**
     * Visibility State.
     */
    visible: boolean;
}

export interface LocaleSelectorEmits {
    /**
     * Update visibility state.
     */
    (ev: 'update:visible', value: boolean): void;
}
</script>

<script lang="ts" setup>
import Scrollbar from 'smooth-scrollbar';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import LoadingSpinner from '@/components/feedback/LoadingSpinner.vue';
import { useLocaleStore } from '@/stores/locale';

// Define Component
const props = defineProps<LocaleSelectorProps>();
const emits = defineEmits<LocaleSelectorEmits>();

// Stores
const localeStore = useLocaleStore();

// States
const loading = ref<boolean>(true);
const selector = ref<HTMLElement>();
const scrollbar = ref<Scrollbar>();
const visibleState = ref<boolean>(false);
const visible = computed({
    get() {
        return props.visible;
    },
    set(value: boolean) {
        emits('update:visible', value);
    }
});

// Component mounted
onMounted(async () => {
    loading.value = true;
    await localeStore.init();
    loading.value = false;
});

// Change Visibility
watch(visible, async newValue => {
    if (newValue) {
        visibleState.value = true;
        await new Promise(resolve => setTimeout(resolve, 100));

        if (selector.value) {
            selector.value.classList.add('is-visible');

            await nextTick();
            if (selector.value) {
                scrollbar.value = Scrollbar.init(selector.value);
            }
        }
    } else {
        if (selector.value) {
            selector.value.classList.remove('is-visible');
        }

        await new Promise(resolve => setTimeout(resolve, 300));
        if (scrollbar.value) {
            scrollbar.value.destroy();
        }
        
        visibleState.value = false;
    }
}, { immediate: true });
</script>

<style scoped>
.locale-selector-field {
    @apply absolute left-1/2 top-full translate-y-6 opacity-0 p-0 rounded-md overflow-auto -ml-px -translate-x-1/2;
    @apply duration-300 ease-in-out;
    @apply bg-white shadow-gray-700/25;
    z-index: 100;
    min-width: 220px;
    max-height: 300px;
    box-shadow: 0 0 0 1px var(--tw-shadow-color);
    transition-property: opacity, transform;

    &.is-visible {
        @apply translate-y-1 opacity-100;
    }
}

.selector-list {
    @apply flex flex-col px-0 py-2;

    & .list-entry {
        @apply w-full flex flex-row items-center gap-3 px-3 py-1.5;
        @apply duration-300 ease-in-out transition-colors;
        @apply bg-transparent text-gray-600;
        
        &:hover {
            @apply bg-gray-100 text-gray-800;
        }

        & .entry-title {
            @apply flex flex-col text-sm;

            & .title-real {
                @apply font-semibold capitalize;
            }
            
            & .title-english {
                @apply font-normal text-xs -mt-1;
            }
        }

        & .entry-locale {
            @apply ml-auto;

            & span {
                @apply ml-auto py-0.5 px-2 rounded font-semibold text-xs border border-solid;
                @apply duration-300 ease-in-out transition-colors;
                @apply bg-gray-100 border-gray-100;
            }
        }
        &:hover .entry-locale span {
            @apply border-gray-300;
        }
    }
}
</style>
