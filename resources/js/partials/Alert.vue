<template>
    <div
        v-if="visible"
        :class="[
            'select-none flex p-4 rounded-lg text-sm border',
            alertClass,
        ]">

        <component v-if="showIcon" :is="iconName" :size="iconSize" :class="['shrink-0', iconMarginTop]" />

        <!-- Content -->
        <div class="ms-4">
            <h3 v-if="$slots.title || title" class="text-sm font-semibold">
                <slot name="title">{{ title }}</slot>
            </h3>
            <div v-if="$slots.description || description" class="mt-1 text-sm">
                <slot name="description">{{ description }}</slot>
            </div>
        </div>

        <!-- Close Button -->
        <button
            v-if="dismissable"
            @click.prevent.stop="visible = false"
            class="ms-auto flex items-center justify-center size-5 rounded-lg border border-transparent text-gray-500 hover:bg-gray-100 hover:text-gray-800 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
        >
            <span class="sr-only">Close</span>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18"></path>
                <path d="m6 6 12 12"></path>
            </svg>
        </button>
    </div>
</template>

<script>

    import { Info, CircleX, CircleCheck, TriangleAlert } from 'lucide-vue-next';

    export default {
        props: {
            type: {
                type: String,
                default: "light",
                validator: (value) => ["light", "secondary", "primary", "success", "warning", "danger"].includes(value),
            },
            title: {
                type: String,
                default: null
            },
            description: {
                type: String,
                default: null
            },
            dismissable: {
                type: Boolean,
                default: true
            },
            showIcon: {
                type: Boolean,
                default: true
            },
            icon: {
                type: [Object, Function, null],
                default: null
            },
            iconSize: {
                type: String,
                default: '16'
            },
            iconMarginTop: {
                type: String,
                default: 'mt-0.5'
            },
        },
        data() {
            return {
                Info,
                CircleX,
                CircleCheck,
                TriangleAlert,
                visible: true,
            };
        },
        computed: {
            iconName() {
                const options = {
                    primary: Info,
                    success: CircleCheck,
                    warning: TriangleAlert,
                    danger: CircleX,
                };

                return this.icon ?? options[this.type];
            },
            alertClass() {
                const classes = {
                    light: "bg-gray-50 border-gray-200 text-gray-800 dark:bg-gray-800/10 dark:border-gray-700 dark:text-gray-500",
                    secondary: "bg-gray-100 border-gray-200 text-gray-800 dark:bg-gray-800/10 dark:border-gray-700 dark:text-gray-500",
                    primary: "bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-800/10 dark:border-blue-900 dark:text-blue-500",
                    success: "bg-green-50 border-green-200 text-green-800 dark:bg-green-800/10 dark:border-green-900 dark:text-green-500",
                    warning: "bg-yellow-50 border-yellow-200 text-yellow-800 dark:bg-yellow-800/10 dark:border-yellow-900 dark:text-yellow-500",
                    danger: "bg-red-50 border-red-200 text-red-800 dark:bg-red-800/10 dark:border-red-900 dark:text-red-500",
                };
                return classes[this.type] || classes.light;
            }
        }
    };
</script>
