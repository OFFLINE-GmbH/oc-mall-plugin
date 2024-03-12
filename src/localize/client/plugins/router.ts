
import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import DashboardView from '@/views/DashboardView.vue';
import TranslatorView from '@/views/TranslatorView.vue';

/**
 * Create Router
 */
const routerPlugin = () => {
    const history = createWebHistory('/#');
    const routes = [
        {
            path: '/',
            name: 'home',
            component: DashboardView
        },
        {
            path: '/translator',
            name: 'translator',
            component: TranslatorView
        },
    ] as RouteRecordRaw[];

    return createRouter({
        history,
        routes
    });
}

// Export Module
export default routerPlugin;