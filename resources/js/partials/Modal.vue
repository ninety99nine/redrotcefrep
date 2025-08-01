<template>

    <div>

        <slot name="trigger" :showModal="showModal">

            <Button
                v-if="triggerText"
                :size="triggerSize"
                :type="triggerType"
                :action="showModal"
                :skeleton="triggerLoading"
                :leftIcon="leftTriggerIcon"
                :rightIcon="rightTriggerIcon"
                :leftIconSize="leftTriggerIconSize"
                :rightIconSize="rightTriggerIconSize">
                <span>{{ triggerText }}</span>
            </Button>

        </slot>

        <div
            :id="uniqueId"
            :class="[
                'hs-overlay hidden size-full fixed top-0 start-0 z-50 py-20 overflow-x-hidden overflow-y-auto pointer-events-none',
                { '[--overlay-backdrop:static]' : !dismissable }
            ]">

            <div
                :class="[
                    'hs-overlay-animation-target hs-overlay-open:scale-100 hs-overlay-open:opacity-100 scale-95 opacity-0 ease-in-out transition-all duration-200 min-h-[calc(100%-56px)] flex items-center',
                    { 'sm:max-w-lg sm:w-full m-3 sm:mx-auto' : size == 'sm' },
                    { 'md:max-w-2xl md:w-full m-3 md:mx-auto' : size == 'md' },
                    { 'lg:max-w-4xl lg:w-full m-3 lg:mx-auto' : size == 'lg' },
                ]">

                <div class="relative w-full flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">

                    <button
                        type="button"
                        aria-label="Close"
                        @click.prevent.stop="hideModal"
                        class="absolute top-4 right-4 size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent cursor-pointer bg-gray-100 text-gray-700 hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600">
                        <span class="sr-only">Close</span>
                        <X size="20"></X>
                    </button>

                    <div
                        v-if="header || $slots.header"
                        class="p-4 border-b border-gray-200 dark:border-neutral-700">

                        <slot name="header" :hideModal="hideModal">
                            <h3 v-if="header" class="font-bold dark:text-white">
                                {{ header }}
                            </h3>
                        </slot>

                        <slot name="subheader" :hideModal="hideModal">
                            <h3 v-if="subheader" class="text-sm text-gray-500 dark:text-white">
                                {{ subheader }}
                            </h3>
                        </slot>

                    </div>

                    <div
                        v-if="content || $slots.content"
                        :class="[contentClass, { 'overflow-y-auto max-h-80' : scrollOnContent }]">
                        <slot name="content" :hideModal="hideModal">
                            <p class="text-sm text-gray-700 dark:text-neutral-400">
                                {{ content }}
                            </p>
                        </slot>
                    </div>

                    <slot name="footer" :hideModal="hideModal">

                        <div
                            v-if="showFooter && (showDelineButton || showApproveButton)"
                            class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-gray-200 dark:border-neutral-700 mt-6">

                            <Button
                                :size="declineSize"
                                :type="declineType"
                                v-if="showDelineButton"
                                :loading="declineLoading"
                                :leftIcon="leftDeclineIcon"
                                :rightIcon="rightDeclineIcon"
                                :action="declineAction ? () => declineAction(hideModal) : hideModal">
                                <slot name="declineIcon"></slot>
                                <span>{{ declineText }}</span>
                            </Button>

                            <Button
                                :size="approveSize"
                                :type="approveType"
                                v-if="showApproveButton"
                                :loading="approveLoading"
                                :leftIcon="leftApproveIcon"
                                :rightIcon="rightApproveIcon"
                                :action="approveAction ? () => approveAction(hideModal) : hideModal">
                                <slot name="approveIcon"></slot>
                                <span>{{ approveText }}</span>
                            </Button>

                        </div>

                    </slot>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

    import { X } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';
    import { generateUniqueId } from '@Utils/generalUtils.js';

    export default {
        components: { X, Button },
        props: {
            triggerLoading: {
                type: Boolean,
                default: false
            },
            triggerText: {
                type: [String, null],
                default: null
            },
            triggerSize: {
                type: String,
                default: 'sm'
            },
            triggerType: {
                type: String,
                default: 'light'
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
            size: {
                type: String,
                default: "sm",
                validator: (value) => ["sm", "md", "lg"].includes(value),
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
            showDelineButton: {
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
                default: 'Yes'
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
            scrollOnContent: {
                type: Boolean,
                default: true
            },
            dismissable: {
                type: Boolean,
                default: true
            },
            onShow: {
                type: [Function, null],
                default: null
            },
            onHide: {
                type: [Function, null],
                default: null
            },
        },
        data() {
            return {
                uniqueId: generateUniqueId('modal'),
            }
        },
        mounted() {
            setTimeout(() => {
                if (window.HSOverlay) {
                    window.HSOverlay.autoInit();
                }
            }, 500);
        },
        methods: {
            showModal() {
                const modal = document.querySelector(`#${this.uniqueId}`);
                if (modal) {
                    window.HSOverlay.open(modal);
                    if(this.onShow) this.onShow();
                }
            },
            hideModal() {
                const modal = document.querySelector(`#${this.uniqueId}`);
                if (modal) {
                    window.HSOverlay.close(modal);
                    if(this.onHide) this.onHide();
                }
            }
        }
    };

</script>
