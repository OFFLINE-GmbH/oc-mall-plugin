<template>
    <div ref="selector" class="locale-selector-field" v-if="visibleState">
        <div class="selector-list" v-if="!loading">
            <div class="list-input">
                <InputField ref="searchField" size="sm" placeholder="Locale or Code..." v-model="searchValue" />
            </div>

            <template v-if="availableLocales.length > 0">
                <div class="list-entry" v-for="[code, locale] of availableLocales" :key="code" @click="onSelect(code)">
                    <div class="entry-title">
                        <span class="title-real">{{ locale.name }}</span>
                        <span class="title-english">{{ locale.english }}</span>
                    </div>
                    <div class="entry-locale">
                        <span>{{ code }}</span>
                    </div>
                </div>
            </template>
            <template v-if="newLocales.length > 0">
                <div class="list-entry-title">Create new Locale</div>
                <div class="list-entry" v-for="[code, locale] of newLocales" :key="code" @click="onCreate(code, locale)">
                    <div class="entry-title">
                        <span class="title-real">{{ locale.name }}</span>
                        <span class="title-english">{{ locale.english }}</span>
                    </div>
                    <div class="entry-locale">
                        <span>{{ code }}</span>
                    </div>
                </div>
            </template>
            <template v-if="availableLocales.length == 0 && newLocales.length == 0">
                <div class="list-empty">
                    No locale found...
                </div>
            </template>
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
import InputField from '@/components/controls/InputField.vue';
import LoadingSpinner from '@/components/feedback/LoadingSpinner.vue';
import { useLocaleStore, type Locale } from '@/stores/locale';
import localeNames from '@/constants/locales';
import wait from '@/support/wait';

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

const searchField = ref<InstanceType<typeof InputField>>();
const searchValue = ref<string>();
const availableLocales = computed<[string, Locale][]>(() => {
    let locales = Object.entries(localeStore.locales);

    if (searchValue.value && searchValue.value.trim().length > 0) {
        let search = searchValue.value.trim().toLowerCase().replace('-', '_');
        locales = locales.filter(([code, locale]) => {
            return (
                locale.name.toLowerCase().includes(search) || 
                locale.english.toLowerCase().includes(search) || 
                locale.code.toLowerCase().includes(search)
            );
        });
    }

    locales.sort((a, b) => {
        if (a[0] == b[0]) {
            return 0;
        }
        return a[0] > b[0] ? 1 : -1;
    });
    return locales;
});

const newLocales = computed<[string, Locale][]>(() => {
    if (!(searchValue.value && searchValue.value.trim().length > 0)) {
        return [];
    }

    let search = searchValue.value.trim().toLowerCase().replace('-', '_');
    const locales = Object.entries(localeNames).filter(([code, name]) => {
        return (
            code.toLowerCase().includes(search) || 
            name.toLowerCase().includes(search)
        );
    }).map(([code, english]) => {
        let temp = code.split('_');
        let short = temp[0].toLowerCase();
        let locale = temp[0].toLowerCase() + (temp.length > 1 ? ('-' + temp[1].toUpperCase()) : '');

        let translate = (new Intl.DisplayNames([locale, short, 'en'], { type: "language" }));
        let name = translate.of(short) || english;
        return [code, {
            code,
            short,
            locale: locale.toLowerCase(),
            name,
            english,
            stats: null,
            _stats: null
        }] as [string, Locale];
    });
    return locales;
});

// Component mounted
onMounted(async () => {
    loading.value = true;
    await localeStore.init();
    loading.value = false;
    
    await wait(300);
    if (searchField.value && searchField.value.input) {
        searchField.value.input.focus();
    }
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
        if (searchField.value && searchField.value.input) {
            searchField.value.input.focus();
        }
    } else {
        if (selector.value) {
            selector.value.classList.remove('is-visible');
        }

        await new Promise(resolve => setTimeout(resolve, 300));
        if (scrollbar.value) {
            scrollbar.value.destroy();
        }

        searchValue.value = '';
        visibleState.value = false;
    }
}, { immediate: true });

/**
 * Select a locale
 * @param code
 */
function onSelect(code: string) {
    localeStore.select(code);
    visible.value = false;
}

/**
 * Create a new locale
 * @param code
 */
function onCreate(code: string, locale: Locale) {
    localeStore.create(code, locale);
    visible.value = false;
}
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

    & .list-input {
        @apply px-3 mb-2 pb-2 border-b border-solid;
        @apply border-b-gray-300;
    }

    & .list-entry-title {
        @apply px-4 py-1.5 my-2 border-y border-solid font-normal;
        @apply border-gray-300 bg-gray-100;
    }

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

    & .list-empty {
        @apply w-full text-center text-sm font-normal py-4;
        @apply text-gray-600;
    }
}
</style>
