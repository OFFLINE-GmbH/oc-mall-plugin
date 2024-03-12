<template>
    <BaseWidget :loading="loading">
        <div class="widget-table">
            <div class="table-header">
                <div class="table-cell cell-auto">Language</div>
                <div class="table-cell text-center cell-total" style="flex-basis: 15%">Total</div>
                <div class="table-cell text-center" style="flex-basis: 15%">demo.php</div>
                <div class="table-cell text-center" style="flex-basis: 15%">frontend.php</div>
                <div class="table-cell text-center" style="flex-basis: 15%">lang.php</div>
                <div class="table-cell text-center" style="flex-basis: 15%">mail.php</div>
            </div>
            <div class="table-body">
                <div class="table-row" v-for="[code, locale] of availableLocales" :key="code">
                    <div class="table-cell cell-auto">
                        <div class="cell-locale">
                            <div class="locale-id"><span>{{ locale.short }}</span></div>
                            <div class="locale-name">
                                <span class="name-real">{{ locale.name }}</span>
                                <span class="name-english">{{ locale.english }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="table-cell cell-total" style="flex-basis: 15%">
                        <div class="total-value">
                            <ProgressBar :value="localeStore.progress(locale.code)" :valuer="(result) => result.percentage" />
                        </div>
                    </div>
                    <div class="table-cell" style="flex-basis: 15%">
                        <ProgressBar :value="localeStore.progress(locale.code)" :valuer="(result) => result.files['demo.php'].percentage" />
                    </div>
                    <div class="table-cell" style="flex-basis: 15%">
                        <ProgressBar :value="localeStore.progress(locale.code)" :valuer="(result) => result.files['frontend.php'].percentage" />
                    </div>
                    <div class="table-cell" style="flex-basis: 15%">
                        <ProgressBar :value="localeStore.progress(locale.code)" :valuer="(result) => result.files['lang.php'].percentage" />
                    </div>
                    <div class="table-cell" style="flex-basis: 15%">
                        <ProgressBar :value="localeStore.progress(locale.code)" :valuer="(result) => result.files['mail.php'].percentage" />
                    </div>
                </div>
            </div>
        </div>
    </BaseWidget>
</template>

<script lang="ts">
// TypeScript Definition
</script>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue';
import ProgressBar from '@/components/feedback/ProgressBar.vue';
import BaseWidget from '@/components/widgets/BaseWidget.vue';
import { useLocaleStore, type Locale } from '@/stores/locale';

// Stores
const localeStore = useLocaleStore();

// States
const loading = ref<boolean>(true);
const availableLocales = computed<[string, Locale][]>(() => {
    const result = Object.entries(localeStore.locales);
    result.sort((a, b) => {
        if (a[0] == b[0]) {
            return 0;
        }
        return a[0] > b[0] ? 1 : -1;
    });
    return result;
});

// Component mounted
onMounted(async () => {
    loading.value = true;
    await localeStore.init();
    loading.value = false;
});
</script>

<style scoped>
.widget-table {
    @apply w-full flex flex-col;

    & .table-header,
    & .table-body {
        @apply w-full flex items-center;
    }
    & .table-header {
        @apply flex-row;
    }
    & .table-body {
        @apply w-full flex-col;
    }

    & .table-row {
        @apply w-full flex flex-row items-center;

        &:nth-child(even) {
            @apply bg-gray-100 dark:bg-gray-800/50;
        }
    }

    & .table-cell {
        @apply px-4 py-2;

        &:not(.cell-auto) {
            @apply shrink-0 grow-0;
        }

        &.cell-auto {
            @apply flex-1;
        }

        &.cell-total {
            @apply border-x self-stretch;
            @apply bg-gray-200/25 border-gray-300;
            @apply dark:bg-gray-200/10 dark:border-gray-700;

            & .total-value {
                @apply w-full h-full flex flex-row items-center;

                & :deep(.progress) {
                    @apply w-full shrink-0;
                }
            }
        }
    }

    & .table-header .table-cell {
        @apply font-semibold text-xs;
    }

    & .table-body .table-cell {
        @apply text-sm;
    }

    & .cell-locale {
        @apply flex flex-row items-center gap-4;

        & .locale-id {
            @apply w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold;
            @apply bg-gray-800 text-gray-50 dark:bg-gray-700 dark:text-gray-50;
        }

        & .locale-name {
            @apply flex flex-col text-sm;

            & .name-real {
                @apply font-semibold capitalize;
            }
            
            & .name-english {
                @apply font-normal text-xs -mt-1;
            }
        }
    }
}
</style>
