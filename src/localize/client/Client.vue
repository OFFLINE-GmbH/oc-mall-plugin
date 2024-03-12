<template>
    <main>
        <aside>
            <PageSidebar />
        </aside>

        <article>
            <header>
                <PageHeader />
            </header>

            <section>
                <RouterView />
            </section>
        </article>
    </main>
</template>

<script setup>
import { RouterView } from 'vue-router';
import PageHeader from '@/components/page/PageHeader.vue';
import PageSidebar from '@/components/page/PageSidebar.vue';
</script>

<style scoped>
:global(#app) {
    @apply w-full h-full overflow-hidden;
}

main {
    @apply w-full h-full flex flex-row;

    & > aside {
        @apply h-full shrink-0 grow-0 flex flex-row basis-24;
        @apply bg-gray-100 text-gray-700;
        @apply dark:bg-gray-800 dark:text-gray-300;
    }

    & > article {
        @apply h-full shrink-0 grow-0 flex flex-col;
        @apply bg-gray-50 dark:bg-gray-900;
        flex-basis: calc(100% - 6rem);

        & > header {
            @apply shrink-0 grow-0 flex flex-row basis-16 px-8 items-center;
        }

        & > section {
            @apply h-full shrink-0 grow-0 flex flex-col;
            flex-basis: calc(100% - theme(height.16));
            height: calc(100% - theme(height.16));
        }
    }
}
</style>