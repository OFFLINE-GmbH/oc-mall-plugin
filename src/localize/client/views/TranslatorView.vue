<template>
    <PageContainer>
        <PageEmpty v-if="localeStore.selected == null" 
            title="No locale selected"
            text="You still need to select the language you want to translate." />
        
        <div class="translation-container" v-else-if="loading">
            <div class="flex flex-col items-center gap-2 mx-auto my-10">
                <LoadingSpinner color="primary" />
                <span class="mt-6 text-xl text-semibold">Please Wait</span>
                <span>Loading strings for the selected language...</span>
            </div>
        </div>

        <div class="translation-container" v-else>
            <div class="strings-editor">
                <nav class="strings-files">
                    <ul class="files-tabs" v-if="strings">
                        <li v-for="file of Object.keys(strings)" :key="file"
                            class="tab-item" 
                            :class="tab == file ? 'active' : ''"
                            @click="tab = file">
                            {{ file }}
                        </li>
                    </ul>
                    <div class="keyboard">
                        <span>Use</span>
                        <kbd>CTRL</kbd>
                        <span>+</span>
                        <kbd>&uarr;</kbd>
                        <span>/</span>
                        <kbd>&darr;</kbd>
                        <span>inside text field to move.</span>
                    </div>
                </nav>

                <div class="strings-table" :class="Object.keys(strings).indexOf(tab) != 0 ? 'rounded-tl-lg' : ''" v-if="strings && tab">
                    <div class="table-caption">
                        <div class="actions-left">

                        </div>
                        <div class="actions-right">
                            <CheckboxField label="Show translated" v-model="showTranslatedStrings" />
                        </div>
                    </div>
                    <div class="table-header">
                        <div class="table-cell" :style="{ flexBasis: '75px' }">&nbsp;</div>
                        <div class="table-cell" :style="{ flexBasis: '42%' }">Original</div>
                        <div class="table-cell" :style="{ flexBasis: '42%' }">Translation</div>
                        <div class="table-cell text-right cell-auto">Actions</div>
                    </div>
                    
                    <div ref="editor" class="table-body" :class="showTranslatedStrings ? '' : 'hide-translated'">
                        <div v-for="([key, string], idx) of Object.entries(strings[tab].en)" :key="key"
                            class="table-row string-row"
                            :class="[`is-${strings[tab].status[key]}`]">
                            <div class="table-cell cell-index" :style="{ flexBasis: '75px' }">
                                <div class="string-status" :class="{
                                    'status-success': strings[tab].status[key] == 'translated',
                                    'status-warning': strings[tab].status[key] == 'untranslated',
                                    'status-danger': strings[tab].status[key] == 'missing',
                                }">
                                    <Check :size="16" v-if="strings[tab].status[key] == 'translated'" />
                                    <CircleAlert :size="16" v-else-if="strings[tab].status[key] == 'untranslated'" />
                                    <X :size="16" v-else />
                                </div>
                                <span>{{ idx+1 }}.</span>
                            </div>
                            <div class="table-cell" :style="{ flexBasis: '42%' }" lang="en">
                                <div 
                                    class="text-sm string-field" 
                                    lang="en"
                                    spellcheck="false" 
                                    readonly>{{ string }}</div>
                            </div>
                            <div class="table-cell" :style="{ flexBasis: '42%' }" :lang="localeStore.selected.short">
                                <StringFieldPartial 
                                    :lang="localeStore.selected.locale"
                                    :file="tab"
                                    :locale-key="key"
                                    :value="strings[tab][localeStore.selected.locale][key] ?? ''" 
                                    @next="onGoToNext"
                                    @prev="onGoToPrev" 
                                    @change="onChange" />
                            </div>
                            <div class="justify-end table-cell text-right cell-auto">
                                <div class="flex justify-end gap-2 actions">
                                    <TooltipStd label="Show references" placement="left" :delay="500" v-slot="{ show, hide }">
                                        <ActionButton :icon="CodeXml" color="secondary" 
                                            @click="() => onShowReferences(tab as string, key)" 
                                            @mouseover="show"
                                            @mouseout="hide" />
                                    </TooltipStd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PageContainer>

    <BaseDialog title="String References" size="lg" v-model:visible="showReferences" @hidden="onCloseReferences">
        <div class="w-full -mt-4 dialog-content">
            <div class="flex flex-row flex-wrap gap-2 mb-4">
                <code class="note">lang/{{ localeStore?.selected?.locale }}/{{ referencesFile }}</code>
                <code class="note">{{ referencesKey }}</code>
            </div>

            <template v-if="references">
                <ReferencesPartial :references="references" v-if="references.length > 0" />
                <div class="w-full py-4 text-sm text-center" v-else>
                    <b class="font-semibold">No references found for this locale key.</b>
                </div>
            </template>
        </div>
    </BaseDialog>
</template>

<script lang="ts" setup>
import { Check, CircleAlert, CodeXml, X } from 'lucide-vue-next';
import Scrollbar from 'smooth-scrollbar';
import { nextTick, ref } from 'vue';

import ActionButton from '@/components/buttons/ActionButton.vue';
import CheckboxField from '@/components/controls/CheckboxField.vue';
import BaseDialog from '@/components/dialogs/BaseDialog.vue';
import LoadingSpinner from '@/components/feedback/LoadingSpinner.vue';
import TooltipStd from '@/components/tooltip/TooltipStd.vue';
import PageContainer from '@/components/page/PageContainer.vue';
import PageEmpty from '@/components/page/PageEmpty.vue';
import ReferencesPartial from '@/views/partials/ReferencesPartial.vue';
import StringFieldPartial from '@/views/partials/StringFieldPartial.vue';
import { useLocaleStore } from '@/stores/locale';

// Stores
const localeStore = useLocaleStore();

// States
const editor = ref<HTMLElement>();
const loading = ref<boolean>(true);
const tab = ref<string|null>();
const strings = ref<any>();

const showTranslatedStrings = ref<boolean>(true);

const showReferences = ref<boolean>(false);
const references = ref<any>();
const referencesKey = ref<string|null>();
const referencesFile = ref<string|null>();

// Watch selected locale changes.
localeStore.$subscribe(async (mutation, state) => {
    loading.value = true;
    strings.value = await localeStore.strings();
    tab.value = Object.keys(strings.value)[0] ?? null;
    loading.value = false;

    await nextTick();
    if (editor.value) {
        Scrollbar.init(editor.value);
    }
}, { immediate: true });


/**
 * Go to previous field
 * @param ev
 */
function onGoToPrev(ev: Event) {
    let cur = (ev.target as HTMLElement).closest('.string-row');
    if (!cur) {
        return;
    }

    const prev = cur.previousElementSibling?.querySelector('[contenteditable]');
    if (prev && prev instanceof HTMLElement) {
        prev.focus();
    }
}

/**
 * Go to next field
 * @param ev
 */
function onGoToNext(ev: Event) {
    let cur = (ev.target as HTMLElement).closest('.string-row');
    if (!cur) {
        return;
    }

    const next = cur.nextElementSibling?.querySelector('[contenteditable]');
    if (next && next instanceof HTMLElement) {
        next.focus();
    }
}

/**
 * Value has been changed
 * @param lang
 * @param file
 * @param key
 * @param value
 */
function onChange(lang:string, file: string, key: string, value: string) {
    if (!strings.value) {
        return;
    }

    if (!(file in strings.value)) {
        return;
    }
    if (!(key in (strings.value[file][lang] ?? {}))) {
        return;
    }
    strings.value[file][lang][key] = value;
    strings.value[file].status[key] = value.trim().length > 0 ? 'translated' : 'untranslated';
}

/**
 * Show String References Modal
 * @param tab
 * @param key
 */
function onShowReferences(tab: string, key: string) {
    if (!strings.value) {
        return;
    }

    showReferences.value = true;
    references.value = strings.value[tab].references[key];
    referencesKey.value = key;
    referencesFile.value = tab;
}

/**
 * Close String References Modal
 * @param tab
 * @param key
 */
async function onCloseReferences() {
    references.value = null;
    referencesKey.value = null;
    referencesFile.value = null;
    showReferences.value = false;
}
</script>

<style scoped>
.translation-container {
    @apply h-full;
}

.strings-editor {
    @apply relative flex flex-col h-full;
}

.strings-files {
    @apply flex flex-row items-center;
    
    & .files-tabs {
        @apply flex flex-row;

        & .tab-item {
            @apply relative px-6 py-2.5 border border-solid text-sm font-semibold cursor-pointer z-20 -mb-px rounded-t;
            @apply duration-300 ease-in-out transition-colors;
            @apply bg-transparent border-transparent text-gray-600 dark:text-gray-400;

            &:first-child {
                @apply rounded-tl-lg;
            }
            
            &:last-child {
                @apply rounded-tr-lg;
            }

            &:not(.active):hover {
                @apply bg-gray-100 text-gray-900 border-b-gray-300;
                @apply dark:bg-gray-800 dark:text-gray-300 dark:border-b-gray-700;
            }

            &.active {
                @apply bg-white border-x-gray-300 border-t-gray-300 border-b-white text-gray-900;
                @apply dark:bg-gray-800 dark:border-x-gray-700 dark:border-t-gray-700 dark:border-b-gray-800 dark:text-gray-200;
            }
        }
    }

    & .keyboard {
        @apply ml-auto text-sm flex flex-row items-center gap-1.5;

        & kbd {
            @apply flex-1 rounded px-2 py-0.5 border border-solid;
            @apply bg-white border-gray-300;
            @apply dark:bg-gray-700 dark:border-gray-500 dark:text-gray-300;
        }
    }
}

.strings-table {
    @apply relative flex flex-col border border-solid rounded-b-lg rounded-tr-lg z-10;
    @apply bg-white border-gray-300;
    @apply dark:bg-gray-800 dark:border-gray-700;
    height: calc(100% - 42px);

    & .table-caption {
        @apply w-full flex flex-row items-center justify-between px-4 py-2 font-semibold;

        & .actions-left,
        & .actions-right {
            @apply flex flex-row gap-3 items-center;
        }
    }
    
    & .table-header,
    & .table-body {
        @apply w-full flex items-center;
    }

    & .table-header {
        @apply flex-row;

        & .table-cell {
            @apply font-semibold text-sm dark:border-y;
            @apply bg-gray-100 dark:bg-gray-700/50 dark:border-y-gray-700;
        }
    }

    & .table-body {
        @apply w-full flex-col;

        & :deep(.scroll-content) {
            @apply w-full shrink-0 grow-0 basis-full;
        }
    }

    & .table-row {
        @apply w-full shrink-0 grow-0 basis-full flex flex-row items-center border-t border-solid;
        @apply border-t-gray-200 dark:border-t-gray-700;

        &:first-child {
            @apply dark:border-t-0;
        }
    }
    & .table-body.hide-translated .table-row.is-translated {
        @apply hidden;
    }

    & .table-cell {
        @apply px-4 py-2;

        &:not(.cell-auto) {
            @apply shrink-0 grow-0;
        }

        &.cell-auto {
            @apply flex-1;
        }

        &.cell-index {
            @apply relative text-xs text-right tabular-nums;
            @apply text-gray-600 dark:text-gray-500;
        }
    }
}

.string-status {
    @apply absolute left-0 top-1/2 w-8 h-8 rounded-full flex items-center justify-center -mt-4;
    @apply duration-300 ease-in-out transition-transform;

    &.status-success {
        @apply -translate-x-full;
        @apply text-success-600;

        .table-row:hover & {
            @apply translate-x-0;
        }
    }
    
    &.status-warning {
        @apply text-warning-600;
        
        & + span {
            @apply font-bold;
            @apply text-warning-600;
        }
    }
    
    &.status-danger {
        @apply text-danger-600;
        
        & + span {
            @apply font-bold;
            @apply text-danger-600;
        }
    }
}

.note {
    @apply inline-flex px-3 py-1 text-xs rounded;
    @apply bg-primary-100 text-primary-800;
    @apply dark:bg-primary-900 dark:text-primary-100;
}
</style>