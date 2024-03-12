<template>
    <nav>
        <div class="page-logo">
            <PageLogo width="48" height="48" />
        </div>

        <ul class="page-navi">
            <li class="navi-item">
                <RouterLink :to="{ name: 'home' }" class="navi-link">
                    <CircleGauge :stroke-width="1.5" />
                </RouterLink>
            </li>

            <li class="navi-item">
                <RouterLink :to="{ name: 'translator' }" class="navi-link">
                    <ScanText :stroke-width="1.5" />
                </RouterLink>
            </li>

            <li class="navi-item">
                <RouterLink :to="{ name: 'issues' }" class="navi-link">
                    <BadgeAlert :stroke-width="1.5" />
                </RouterLink>
            </li>
        </ul>

        <ul class="page-navi page-socials">
            <li class="navi-item">
                <a href="https://github.com/OFFLINE-GmbH/oc-mall-plugin" class="navi-link" target="_blank">
                    <Github :stroke-width="1.5" />
                </a>
            </li>
        </ul>
    </nav>
</template>

<script lang="ts">
// TypeScript Definition
</script>

<script lang="ts" setup>
import { BadgeAlert, CircleGauge, Github, ScanText } from 'lucide-vue-next';
import { RouterLink } from 'vue-router';
import PageLogo from '@/components/page/PageLogo.vue';
</script>

<style scoped>
nav {
    @apply w-full h-full flex flex-col;
}

.page-logo {
    @apply flex shrink-0 grow-0 basis-16 items-center justify-center;
}

.page-navi {
    @apply w-full mt-8 mb-auto flex flex-col;

    &.page-socials {
        @apply mt-auto mb-8;
    }

    & .navi-item {
        @apply w-full basis-16 items-center justify-center;
    }

    & .navi-link {
        @apply relative w-full h-full flex items-center justify-center;
        @apply duration-300 ease-in-out transition-colors;
        @apply text-gray-600 dark:text-gray-400;

        &::before {
            @apply absolute top-1/2 left-1/2 w-12 h-12 -ml-6 -mt-6 rounded-xl z-10 scale-0;
            @apply duration-300 ease-in-out;
            @apply bg-gray-200 dark:bg-gray-900;
            content: "";
            transition-property: background-color, transform;
        }

        & svg {
            @apply relative z-20 pointer-events-none;
        }

        &:not(.router-link-active):hover {
            @apply text-gray-900 dark:text-gray-200;
            
            &::before {
                @apply scale-100;
            }
        }

        &.router-link-active {
            @apply text-gray-100 dark:text-gray-900;
            
            &::before {
                @apply scale-100;
                @apply bg-gray-900 dark:bg-gray-200;
            }
        }
    }
}
</style>
