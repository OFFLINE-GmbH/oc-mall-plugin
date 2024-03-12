<template>
    <div class="references">
        <div class="references-select" v-if="props.references.length > 1">
            <SelectField 
                size="sm"
                :options="(references.map((item, idx) => ({ label: item.source, value: idx })) as any)" 
                v-model="selected" />
        </div>

        <div class="reference" v-if="reference">
            <div class="details">
                <div class="details-line">
                    <File :size="16" />
                    <code>{{ reference.source }}</code>
                </div>
                <div class="details-line">
                    <span>Line</span>
                    <code>{{ reference.line }}</code>
                </div>
                <div class="details-line">
                    <span>Col</span>
                    <code>{{ reference.col }}</code>
                </div>
                <div class="details-line">
                    <span>IDX</span>
                    <code>{{ reference.index }}</code>
                </div>
            </div>
            <ShikiSyntax :code="reference.excerpt" :lang="lang" :focus="reference.excerptFocus" />
        </div>
    </div>
</template>

<script lang="ts">
export interface PartialProps {
    /**
     * References
     */
    references: any[];
}
</script>

<script lang="ts" setup>
import { 
    File,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import SelectField from '@/components/controls/SelectField.vue';
import ShikiSyntax from '@/components/syntax/ShikiSyntax.vue';

// Define Component
const props = defineProps<PartialProps>();

// States
const selected = ref<number>(0);
const reference = computed(() => props.references[selected.value]);
const lang = computed(() => {
    if (!reference.value) {
        return 'txt';
    } else {
        let dot = reference.value.source.lastIndexOf('.') || 0;
        let ext = reference.value.source.slice(dot+1);
        return ext == 'htm' ? 'html' : ext;
    }
})
</script>

<style scoped>
.references {
    @apply flex flex-col;
}

.references-select {
    @apply border-b border-solid mb-4 pb-4 -mx-5 px-5;
    @apply border-gray-300 dark:border-gray-800;

    & :deep(.field-select) {
        @apply w-auto pr-12;
    }
}

.details {
    @apply flex flex-row flex-wrap gap-2 mb-4;
}

.details-line {
    @apply flex flex-row gap-1 items-center border border-solid rounded text-xs px-2 py-1;
    @apply border-gray-300 dark:border-gray-600 dark:bg-gray-800;

    & code {
        @apply mt-px -mb-px;
    }
}
</style>
