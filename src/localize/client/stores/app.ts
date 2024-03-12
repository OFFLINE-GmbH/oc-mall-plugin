import { defineStore } from 'pinia';
import localeNames from '@/constants/locales';

export type ColorModes = 'dark' | 'light';

export interface AppStates {
    /**
     * Color Mode
     */
    mode: ColorModes;
}

export const useAppStore = defineStore('app', {
    /**
     * Initial States
     */
    state: (): AppStates => {
        return {
            mode: localStorage.getItem('oct:color-mode') == 'dark' ? 'dark' : 'light'
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
         * Toggle Color Mode
         * @param mode 
         */
        toggleColorMode(mode: ColorModes) {
            const oldMode = this.mode;
            const newMode = this.mode == 'light' ? 'dark' : 'light';

            // Store
            this.mode = newMode;
            localStorage.setItem('oct:color-mode', newMode);

            // Toggle
            const styles = document.createElement('style');
            styles.innerText = '*, *::before, *::after { transition: all 300ms ease-in-out !important; }';
            document.head.appendChild(styles);

            // Set Mode
            document.documentElement.classList.remove(oldMode);
            document.documentElement.classList.add(newMode);

            // Remove
            setTimeout(() => styles.remove(), 300);
        }
    },
});
