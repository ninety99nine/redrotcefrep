<template>
    <div>
        <slot name="trigger" :showDrawer="showDrawer">
            <Button
                v-if="trigger"
                :size="triggerSize"
                :type="triggerType"
                :action="showDrawer"
                :skeleton="triggerLoading"
                :leftIcon="leftTriggerIcon"
                :rightIcon="rightTriggerIcon"
                :leftIconSize="leftTriggerIconSize"
                :rightIconSize="rightTriggerIconSize">
                <span>{{ trigger }}</span>
            </Button>
        </slot>

        <Teleport :to="backdropTarget">
            <div
                v-if="visible"
                @click.stop="dismissable ? hideDrawer() : null"
                :class="['fixed inset-0 z-50 flex transition-all duration-250', isOpen ? 'bg-slate-900/75' : 'bg-transparent']">

                <div
                    @click.stop
                    :class="[
                        'h-full transform transition-all duration-300 ease-in-out',
                        position === 'right' ? 'ml-auto' : 'mr-auto',
                        { 'w-full max-w-xs': size === 'xs' },
                        { 'w-full max-w-sm': size === 'sm' },
                        { 'w-full max-w-xl': size === 'md' },
                        { 'w-full max-w-4xl': size === 'lg' },
                        { 'translate-x-0': isOpen, 'translate-x-full': !isOpen && position === 'right', '-translate-x-full': !isOpen && position === 'left' }
                    ]">

                    <div class="relative flex flex-col h-full bg-white border border-gray-200 shadow-lg dark:bg-neutral-800 dark:border-neutral-700">

                        <button
                            type="button"
                            aria-label="Close"
                            @click.stop="closeOnX ? closeOnX() : hideDrawer()"
                            class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full border border-transparent bg-gray-100 text-gray-700 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400">
                            <X size="20" />
                        </button>

                        <div
                            v-if="header || $slots.header"
                            class="py-3 px-4 border-b border-gray-200 dark:border-neutral-700">
                            <slot name="header" :hideDrawer="hideDrawer">
                                <h3 v-if="header" class="font-bold dark:text-white">
                                    {{ header }}
                                </h3>
                            </slot>
                            <slot name="subheader" :hideDrawer="hideDrawer">
                                <h3 v-if="subheader" class="text-sm text-gray-500 dark:text-white">
                                    {{ subheader }}
                                </h3>
                            </slot>
                        </div>

                        <div
                            v-if="content || $slots.content"
                            :class="['flex-1', contentClass, { 'overflow-y-auto max-h-80': scrollOnContent }]">
                            <slot name="content" :hideDrawer="hideDrawer">
                                <p class="text-sm text-gray-700 dark:text-neutral-400">
                                    {{ content }}
                                </p>
                            </slot>
                        </div>

                        <slot name="footer" :hideDrawer="hideDrawer">
                            <div
                                v-if="showFooter && (showCancelButton || showApproveButton)"
                                class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-gray-200 dark:border-neutral-700">
                                <Button
                                    :size="declineSize"
                                    :type="declineType"
                                    v-if="showCancelButton"
                                    :loading="declineLoading"
                                    :leftIcon="leftDeclineIcon"
                                    :disabled="declineDisabled"
                                    :rightIcon="rightDeclineIcon"
                                    :action="declineAction ? () => declineAction(hideDrawer) : hideDrawer">
                                    <slot name="declineIcon"></slot>
                                    <span>{{ declineText }}</span>
                                </Button>
                                <Button
                                    :size="approveSize"
                                    :type="approveType"
                                    v-if="showApproveButton"
                                    :loading="approveLoading"
                                    :leftIcon="leftApproveIcon"
                                    :disabled="approveDisabled"
                                    :rightIcon="rightApproveIcon"
                                    :action="approveAction ? () => approveAction(hideDrawer) : hideDrawer">
                                    <slot name="approveIcon"></slot>
                                    <span>{{ approveText }}</span>
                                </Button>
                            </div>
                        </slot>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script>
import { X } from 'lucide-vue-next';
import Button from '@Partials/Button.vue';
import { generateUniqueId } from '@Utils/generalUtils.js';

export default {
    components: { X, Button },
    props: {
        position: {
            type: String,
            default: "right",
            validator: (value) => ["left", "right"].includes(value),
        },
        trigger: {
            type: [String, null],
            default: null
        },
        triggerLoading: {
            type: Boolean,
            default: false
        },
        triggerSize: {
            type: String,
            default: 'sm'
        },
        triggerType: {
            type: String,
            default: 'primary'
        },
        leftTriggerIcon: {
            type: [Object, Function, null],
            default: null
        },
        rightTriggerIcon: {
            type: [Object, Function, null],
            default: null
        },
        leftTriggerIconSize: {
            type: String,
            default: '16',
        },
        rightTriggerIconSize: {
            type: String,
            default: '16',
        },
        header: {
            type: [String, null],
            default: null
        },
        subheader: {
            type: [String, null],
            default: null
        },
        content: {
            type: [String, null],
            default: null
        },
        contentClass: {
            type: String,
            default: 'p-4'
        },
        showFooter: {
            type: Boolean,
            default: true
        },
        showCancelButton: {
            type: Boolean,
            default: true
        },
        declineText: {
            type: String,
            default: 'Cancel'
        },
        declineType: {
            type: String,
            default: 'light'
        },
        declineSize: {
            type: String,
            default: 'sm'
        },
        leftDeclineIcon: {
            type: [Object, Function, null],
            default: null
        },
        rightDeclineIcon: {
            type: [Object, Function, null],
            default: null
        },
        declineAction: {
            type: [Function, null],
            default: null
        },
        declineDisabled: {
            type: Boolean,
            default: false
        },
        declineLoading: {
            type: Boolean,
            default: false
        },
        showApproveButton: {
            type: Boolean,
            default: true
        },
        approveText: {
            type: String,
            default: 'Confirm'
        },
        approveSize: {
            type: String,
            default: 'sm'
        },
        approveType: {
            type: String,
            default: 'primary'
        },
        leftApproveIcon: {
            type: [Object, Function, null],
            default: null
        },
        rightApproveIcon: {
            type: [Object, Function, null],
            default: null
        },
        approveAction: {
            type: [Function, null],
            default: null
        },
        approveLoading: {
            type: Boolean,
            default: false
        },
        approveDisabled: {
            type: Boolean,
            default: false
        },
        scrollOnContent: {
            type: Boolean,
            default: true
        },
        dismissable: {
            type: Boolean,
            default: true
        },
        size: {
            type: String,
            default: "xs",
            validator: (value) => ["xs", "sm", "md", "lg"].includes(value),
        },
        onShow: {
            type: [Function, null],
            default: null
        },
        onHide: {
            type: [Function, null],
            default: null
        },
        closeOnX: {
            type: Function,
            default: null
        },
        targetClass: {
            type: String,
            default: 'body'
        },
    },
    data() {
        return {
            isOpen: false,
            visible: false,
            backdropTarget: 'body',
            uniqueId: generateUniqueId('drawer'),
        };
    },
    watch: {
        targetClass() {
            this.updateBackdropTarget();
        },
    },
    methods: {
        updateBackdropTarget() {
            if (!this.$el) {
                this.backdropTarget = 'body';
                return;
            }

            let parent = this.$el.parentElement;
            const targetClasses = this.targetClass.split(',').map(cls => cls.trim());

            while (parent && parent !== document.body) {
                if (targetClasses.some(cls => parent.classList.contains(cls))) {
                    this.backdropTarget = parent;
                    return;
                }
                parent = parent.parentElement;
            }

            this.backdropTarget = 'body';
        },
        showDrawer() {
            this.visible = true;
            setTimeout(() => {
                this.isOpen = true;
            }, 100);
            if (this.onShow) this.onShow();
        },
        hideDrawer() {
            this.isOpen = false;
            setTimeout(() => {
                this.visible = false;
            }, 250);
            if (this.onHide) this.onHide();
        },
    },
    mounted() {
        this.updateBackdropTarget();
    },
};
</script>
