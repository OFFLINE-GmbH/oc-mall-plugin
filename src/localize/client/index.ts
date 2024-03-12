
import './styles/index.css';

import { createPinia } from 'pinia';
import { createApp } from 'vue';
import createRouter from '@/plugins/router';

import App from '@/Client.vue';

/**
 * Main Application Handler
 */
async function main() {
    const piniaPlugin = createPinia();
    const routerPlugin = createRouter();

    // Create Application
    const app = createApp(App);

    // Register Plugins
    app.use(piniaPlugin);
    app.use(routerPlugin);

    // Mount
    app.mount('#app');
}
main();
