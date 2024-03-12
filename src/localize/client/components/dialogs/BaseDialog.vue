<template>
    <teleport to="#app" v-if="visibleBounced">
        <div class="modal" :class="[
            `modal-${props.size || 'md'}`
        ]" ref="modal" @click="onClickOutside">
            <div class="modal-dialog" ref="dialog">
                <header class="dialog-header" v-if="$slots.header || props.title">
                    <slot name="header">
                        <div class="dialog-title">{{ props.title }}</div>
                    </slot>

                    <button type="button" class="dialog-close" @click="close" v-if="props.closable">
                        <X :stroke-width="1.5" />
                    </button>
                </header>

                <article class="dialog-body" v-if="$slots.default">
                    <slot name="default"></slot>
                </article>

                <footer class="dialog-footer" v-if="$slots.footer">
                    <slot name="footer"></slot>
                </footer>
            </div>
        </div>
    </teleport>
</template>

<script lang="ts">
export interface BaseDialogProps {
    /**
     * The desired dialog title, can be used instead of the header slot.
     */
    title?: string | null;

    /**
     * The desired dialog size.
     */
    size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';

    /**
     * Includes a modal-backdrop element. Using 'static' adds a backdrop modal which does not 
     * close the modal when clicked.
     */
    backdrop?: boolean | 'status';

    /**
     * The dialog visibility state.
     */
    visible?: boolean;

    /**
     * Shows an X-button inside the header, which closes the modal.
     */
    closable?: boolean;
}

export interface BaseDialogSlots {
    /**
     * The default dialog content slot.
     */
    default(): any;

    /**
     * Custom dialog header slot content.
     */
    header(): any;

    /**
     * Custom dialog footer slot content.
     */
    footer(): any;
}

export interface BaseDialogEmits {
    /**
     * Visibility state changed.
     */
    (ev: 'update:visible', value: boolean): void;

    /**
     * Before the modal / dialog is actually shown.
     */
    (ev: 'show'): void;

    /**
     * After the modal / dialog is available, but before the transition has been completed.
     */
    (ev: 'open'): void;

    /**
     * After the modal / dialog is available and fully visible.
     */
    (ev: 'shown'): void;

    /**
     * Before the modal / dialog is actually be removed.
     */
    (ev: 'hide'): void;
    
    /**
     * Before the modal / dialog is actually be removed, but after the transition has been started.
     */
    (ev: 'close'): void;

    /**
     * After the modal / dialog has been fully removed.
     */
    (ev: 'hidden'): void;

    /**
     * When a click outside the modal occurred.
     */
    (ev: 'clickOutside'): void;
}
</script>

<script lang="ts" setup>
import wait from '@/support/wait';
import { X } from 'lucide-vue-next';
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';

// Define Component
const props = defineProps<BaseDialogProps>();
defineSlots<BaseDialogSlots>();
const emits = defineEmits<BaseDialogEmits>();

// States
const modal = ref<HTMLElement|null>(null);
const dialog = ref<HTMLElement|null>(null);
const visibleState = ref<boolean>(false);
const visibleBounced = ref<boolean>(false);

/**
 * Component before unmount
 */
onBeforeUnmount(() => {
    close();
});

/**
 * Handle property changes
 */
watch(props, newValue => {
    if (visibleState.value != newValue.visible) {
        visibleState.value = newValue.visible;
    }
}, { immediate: true });

/**
 * Handle visibility changes
 */
watch(visibleState, async newValue => {
    if (newValue && !visibleBounced.value) {
        await nextTick();
        await open();
    } else if (!newValue && visibleBounced.value) {
        await close();
        await nextTick();
    }
}, { immediate: true });

/**
 * Open Modal
 */
 async function open() {
    visibleBounced.value = true;
    await wait(10);
    if (!modal.value || modal.value.classList.contains('visible')) {
        return;
    }
    
    // Show
    emits('show');
    await wait(10);
    modal.value.classList.add('visible');
    
    // Opened
    emits('open');
    await wait(300);

    // Shown
    emits('shown');
}

/**
 * Close Modal
 */
async function close() {
    if (!modal.value || !modal.value.classList.contains('visible')) {
        return;
    }
    
    // Hide
    emits('hide');
    await wait(10);
    modal.value.classList.remove('visible');

    // Closing
    emits('close');
    await wait(300);

    // Hidden
    emits('hidden');
    await wait(10);
    visibleBounced.value = false;
}

/**
 * Event Listener - Click Outside
 * @param event 
 */
function onClickOutside(event: Event|PointerEvent) {
    if (!dialog.value || !dialog.value) {
        return;
    }

    let target = event.target as HTMLElement;
    if (dialog.value == target || dialog.value.contains(target)) {
        return;
    }

    emits('clickOutside');
    if ((props.backdrop || true) == true) {
        close();
    }
}

// Expose Methods
defineExpose({
    open,
    close,
});
</script>

<style scoped>
.modal {
    @apply inset-0 fixed w-full h-full flex justify-center items-center py-10;
    @apply overflow-x-hidden overflow-y-auto outline-none;
    @apply opacity-0 bg-zinc-950/70;
    @apply duration-300 ease-in-out transition-opacity;
    z-index: 100;
    perspective: 100px;
    backdrop-filter: blur(5px);

    &.visible {
        @apply mt-0 opacity-100;
    }
}

.modal-dialog {
    @apply max-w-[315px] relative m-auto w-full rounded shadow-lg opacity-0;
    @apply bg-zinc-50 dark:bg-zinc-900;
    @apply duration-300 ease-in-out;
    transform: rotateX(20deg) translate(0, -120px) scale(1, 0.2);
    transition-property: transform, opacity;
    
    .visible & {
        @apply opacity-100;
        transform: rotateX(0deg) translate(0, 0);
    }

    @screen md {
        @apply max-w-md;
    }
}
.modal-lg .modal-dialog {
    @apply max-w-[600px];
}

.dialog-header {
    @apply w-full flex justify-between items-center;

    @screen md {
        @apply p-2;
    }

    & :slotted(.dialog-title) {
        @apply px-6 py-2 text-sm uppercase;

        @screen md {
            @apply px-3 text-base font-semibold;
        }
    }
    

    & .dialog-close {
        @apply w-12 h-12 flex items-center justify-center basis-12 ml-auto rounded-none rounded-tr-xl outline-none self-start;
        @apply duration-300 ease-in-out transition-colors;
        @apply bg-zinc-200 dark:bg-zinc-800 shadow-none;

        &:hover {
            @screen md {
                @apply bg-zinc-300 dark:bg-zinc-700;
            }
        }

        @screen md {
            @apply rounded-full;
        }
    }
}

.dialog-body {
    @apply w-full flex shrink-0 grow-0 text-sm overflow-x-auto;

    @screen md {
        @apply text-base;
    }

    & :slotted(.dialog-content) { 
        @apply px-5 py-2;
    }

    @screen md {
        & :slotted(.dialog-content) {
            @apply py-4;
        }
    }
}

.dialog-body :slotted(.dialog-content p:not(:last-child)) {
    @apply mb-2;
}

.dialog-footer {
    @apply w-full flex;

    & :slotted(.footer-content) {
        @apply w-full p-2;
    }

    @screen md {
        & :slotted(.footer-content) {
            @apply px-4 pb-4;
        }
    }
}
</style>
