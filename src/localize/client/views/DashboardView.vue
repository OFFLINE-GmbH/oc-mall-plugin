<template>
    <PageContainer scrollable>
        <div class="dashboard">
            <div class="pane-left">
                <DetailStateWidget />
            </div>
            <div class="pane-right">

            </div>
        </div>
    </PageContainer>
</template>

<script lang="ts" setup>
import PageContainer from '@/components/page/PageContainer.vue';
import DetailStateWidget from '@/components/widgets/DetailStateWidget.vue';
</script>

<style scoped>
.dashboard {
    @apply flex flex-row flex-wrap;

    & .pane-left {
        @apply flex flex-col shrink-0 grow-0 basis-full order-2;

        @screen xl {
            @apply basis-2/3 order-1;
        }
    }

    & .pane-right {
        @apply flex flex-col shrink-0 grow-0 basis-full order-1;

        @screen xl {
            @apply basis-1/3 order-2;
        }
    }
}
</style>