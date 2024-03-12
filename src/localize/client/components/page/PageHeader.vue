<template>
    <div class="page-header">
        <div class="header-title">
            Offline/Mall Translator
        </div>
        <div class="header-target">
            <div class="locale-selector">
                <div class="locale-field source-locale">
                    <span>EN</span>
                </div>

                <div class="source-target">
                    <ArrowRight :size="16" />
                </div>

                <div class="locale-field target-locale" :class="[
                    showSelector ? 'active' : '',
                    localeStore.selected == null ? 'is-empty' : ''
                ]" @click="showSelector = !showSelector">
                    <span>{{ localeStore.selected ? localeStore.selected.code : 'Select Locale' }}</span>
                    <ChevronDown :size="16" />
                    <LocaleSelector v-model:visible="showSelector" @click.prevent.stop="" />
                </div>
            </div>
        </div>
        <div class="header-actions">
            <ShadowButton :icon="appStore.mode == 'light' ? Sun : Moon" @click="appStore.toggleColorMode" />
        </div>
    </div>
</template>

<script lang="ts">
// TypeScript Definition
</script>

<script lang="ts" setup>
import { ArrowRight, ChevronDown, Moon, Sun } from 'lucide-vue-next';
import { ref } from 'vue';
import ShadowButton from '@/components/buttons/ShadowButton.vue';
import LocaleSelector from '@/components/controls/LocaleSelector.vue';
import { useAppStore } from '@/stores/app';
import { useLocaleStore } from '@/stores/locale';

// Stores
const appStore = useAppStore();
const localeStore = useLocaleStore();

// States
const showSelector = ref<boolean>(false);
</script>

<style scoped>
.page-header {
    @apply w-full shrink-0 grow-0 flex flex-row items-center;
}

.header-title {
    @apply basis-1/3 shrink-0 grow-0 font-semibold text-xl;
}

.header-target {
    @apply basis-1/3 shrink-0 grow-0 flex flex-row justify-center items-center;

    & .source-target {
        @apply dark:text-gray-300;
    }

    & .locale-selector {
        @apply flex flex-row items-center border border-solid rounded-md p-1 gap-2;
        @apply border-gray-300 dark:border-gray-700 dark:bg-gray-800;
    }

    & .locale-field {
        @apply relative flex flex-row items-center gap-1 px-3 py-1 rounded text-sm font-semibold border border-solid;
        @apply duration-300 ease-in-out transition-colors;
    }

    & .locale-field.source-locale {
        @apply bg-primary-200 border-primary-200 text-primary-800;
        @apply dark:bg-primary-900 dark:border-primary-900 dark:text-primary-200;
    }

    & .locale-field.target-locale {
        @apply cursor-pointer;
        
        &:not(.is-empty) {
            @apply bg-danger-200 border-danger-200 text-danger-800;
            @apply dark:bg-danger-900 dark:border-danger-900 dark:text-danger-200;
            
            & span {
                @apply uppercase;
            }
        }

        &.is-empty {
            @apply bg-gray-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300;

            &:hover,
            &.active {
                @apply border-gray-400;
            }
        }
    }
}

.header-actions {
    @apply basis-1/3 shrink-0 grow-0 flex flex-row justify-end items-center;
}
</style>
