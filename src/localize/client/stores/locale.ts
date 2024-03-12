import { defineStore } from 'pinia';
import localeNames from '@/constants/locales';

export interface LocaleStats {
    lines: number;
    translated: number;
    percentage: number;
    files: { [key: string]: Omit<LocaleStats, 'files'> };
}

export interface Locale {
    code: string;
    short: string;
    locale: string;
    name: string;
    english: string;
    stats: LocaleStats | null | Promise<LocaleStats|null>;
    _stats: null | (() => Promise<LocaleStats|null>);
}

export interface LocaleState {
    /**
     * Selected Locale
     */
    selected: null|Locale;

    /**
     * Known locales
     */
    locales: { [key: string]: Locale };
    
    /**
     * Locale Statistics
     */
    stats: { [key: string]: LocaleStats };
    
    /**
     * Locale Statistics
     */
    statsPromises: { [key: string]: Promise<any> };

    /**
     * Locale Code Mapping
     */
    aliases: Map<string, string>;
}

export const useLocaleStore = defineStore('locale', {
    /**
     * Initial States
     */
    state: (): LocaleState => {
        return {
            selected: null,
            locales: {},
            stats: {},
            statsPromises: {},
            aliases: new Map
        };
    },

    /**
     * Getters
     */
    getters: {
    },

    /**
     * Actions
     */
    actions: {
        /**
         * Initialize Locale Storage
         */
        async init() {
            const response = await fetch('/locales');
            const result = await response.json();

            if (result.status == 'success') {
                for (const locale of (result.result as string[])) {
                    let temp = locale.split('-');
                    let code = temp[0].toLowerCase() + (temp.length > 1 ? ('_' + temp[1].toUpperCase()) : '');
                    let short = temp[0].toLowerCase();
                    if (!(code in localeNames)) {
                        continue;
                    }

                    let translate = (new Intl.DisplayNames([locale, short, 'en'], { type: "language" }));
                    let name = translate.of(short) || localeNames[code];

                    this.aliases.set(locale, code);
                    this.locales[code] = {
                        code,
                        short,
                        locale,
                        name,
                        english: localeNames[code],
                        stats: null,
                        _stats: () => new Promise(async resolve => {
                            const response = await fetch(`/stats/${locale}`);
                            const result = await response.json();
                            
                            if (result.status == 'success') {
                                resolve(result.result);
                            } else {
                                resolve(null);
                            }
                        })
                    };
                }
            } else {

            }
        },

        /**
         * Select Locale
         */
        select(code: string) {
            this.selected = this.locales[code] || null;
        },

        /**
         * Create a new Locale
         * @param code 
         */
        create(code: string, locale: Locale) {
            if (code in this.locales) {
                return this.select(code);
            }

            locale._stats = () => new Promise(async resolve => {
                const response = await fetch(`/stats/${locale.locale}`);
                const result = await response.json();
                
                if (result.status == 'success') {
                    resolve(result.result);
                } else {
                    resolve(null);
                }
            });

            this.aliases.set(locale.locale, code);
            this.locales[code] = locale;
            return this.select(code);
        },

        /**
         * Fetch available strings
         */
        async strings() {
            if (this.selected == null) {
                return [];
            }

            const response = await fetch(`/strings/` + this.selected.locale);
            const result = await response.json();

            if (result.status == 'success') {
                return result.result;
            } else {

            }
        },

        /**
         * Return progress of locale.
         * @param code 
         * @param force
         * @returns 
         */
        async progress(code: string, force: boolean = false): Promise<LocaleStats|null> {
            if (!(code in this.locales) || this.locales[code]._stats === null) {
                return null;
            }

            if (this.locales[code].stats == null || force) {
                this.locales[code].stats = (this.locales[code]._stats as any)();
            }
            return await this.locales[code].stats;
        },

        /**
         * Save LocaleKey
         * @param lang 
         * @param file 
         * @param key 
         * @param value 
         */
        async save(lang: string, file: string, key: string, value: string) {
            const response = await fetch(`/save/${lang}/${file}/${key}`, {
                method: 'post',
                body: value
            });
            const result = await response.json();

            if (result.status == 'success') {
                this.locales[this.aliases.get(lang) || lang].stats = null;
                return result.result;
            } else {

            }
        }
    },
});
