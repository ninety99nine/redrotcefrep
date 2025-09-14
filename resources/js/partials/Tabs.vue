<template>

    <div class="inline-flex items-center bg-gray-100 rounded-lg p-1 dark:bg-neutral-800 space-x-1">

        <template v-for="tab in tabs" :key="tab.value">

            <button
                type="button"
                @click.prevent.stop="selectTab(tab.value)"
                :class="[
                    'w-full flex items-center justify-center gap-x-2 rounded-md transition cursor-pointer font-medium',
                    size === 'md'
                        ? 'px-4 py-2 text-sm'
                        : 'px-4 py-1 text-sm',
                    tab.value === modelValue
                        ? 'bg-white shadow text-gray-900 dark:bg-neutral-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-white dark:text-neutral-400 dark:hover:text-white'
                ]">

                <!-- Left Icon -->
                <component v-if="tab.leftIcon" :is="tab.leftIcon" :size="tab.leftIconSize ?? 16" />

                <!-- Label -->
                <span class="whitespace-nowrap">{{ tab.label }}</span>

                <!-- Right Icon -->
                <component v-if="tab.rightIcon" :is="tab.rightIcon" :size="tab.rightIconSize ?? 16" />

            </button>

        </template>

    </div>

</template>

<script>
    export default {
        props: {
            modelValue: {
                type: String,
                required: true
            },
            tabs: {
                type: Array,
                required: true,
                default: () => [],
            },
            size: {
                type: String,
                default: 'md',
                validator: value => ['sm', 'md'].includes(value)
            },
        },
        methods: {
            selectTab(value) {
                this.$emit('update:modelValue', value)
                this.$emit('change', value)
            },
        },
    }
</script>
