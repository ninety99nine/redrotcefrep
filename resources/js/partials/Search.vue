<template>

    <div>

        <div class="space-y-2">

            <div
                v-if="$slots.label || label || secondaryLabel || showAsterisk || $slots.description || description || externalLinkName">

                <label
                    :for="uniqueId"
                    v-if="$slots.label || label || secondaryLabel || showAsterisk"
                    :class="{ 'flex items-center text-sm leading-6 font-medium text-gray-900 space-x-1' : !$slots.label }">

                    <slot v-if="$slots.label" name="label"></slot>

                    <template v-else>

                        <span v-capitalize :style="labelStyle">{{ label }}</span>

                        <span
                            v-if="secondaryLabel"
                            :style="secondaryLabelStyle"
                            :class="{ 'font-normal text-gray-400 ml-1' : !secondaryLabelStyle }">
                            {{ secondaryLabel }}
                        </span>

                        <Popover
                            trigger="hover"
                            :content="popoverContent"
                            v-if="popoverContent || $slots.popoverContent">
                            <slot name="popoverContent"></slot>
                        </Popover>

                        <Tooltip
                            trigger="hover"
                            :content="tooltipContent"
                            v-if="tooltipContent || $slots.tooltip">
                            <slot name="tooltipContent"></slot>
                        </Tooltip>

                        <span v-if="showAsterisk" class="text-red-500">*</span>

                    </template>

                </label>

                <slot v-if="$slots.description" name="description"></slot>

                <div v-else-if="description || externalLinkName" class="leading-4">

                    <span v-if="description" class="text-xs text-gray-400 mr-1">{{ description }}</span>

                    <a
                        target="_blank"
                        :href="externalLinkUrl"
                        v-if="externalLinkName"
                        v-bind="type === 'file' ? fileEventListeners : {}"
                        class="inline-block text-xs text-blue-700 hover:underline hover:text-blue-90">
                        <span>{{ externalLinkName }}</span>
                        <svg class="w-3 h-3 inline-block ml-0.5 -mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"></path>
                        </svg>
                    </a>

                </div>

            </div>

            <div
                :class="[
                    'w-full flex',
                    alignItems,
                    {
                        'opacity-50': disabled
                    }
                ]">

                <!-- Prefix Icon Slot -->
                <slot v-if="$slots.prefix" name="prefix"></slot>

                <Skeleton
                    height="h-8"
                    :shine="true"
                    v-if="skeleton"
                    rounded="rounded-lg">
                </Skeleton>

                <div v-else class="w-full relative" ref="dropdown">

                    <!-- Search Input -->
                    <Input
                        ref="search"
                        type="search"
                        class="w-full"
                        :debounced="true"
                        :disabled="disabled"
                        v-model="searchQuery"
                        @focus="toggleDropdown"
                        :placeholder="placeholder"
                        @input="onInputSearchQuery"
                    />

                    <!-- Dropdown Options -->
                    <div v-if="isOpen" class="w-full absolute z-10 mt-1 select-none bg-white border border-gray-300 rounded-md shadow-lg overflow-hidden">

                        <!-- Scrollable Options List -->
                        <ul
                            class="max-h-60 divide-y divide-gray-100 overflow-auto"
                            v-if="isSearching || options.length > 0">

                            <template v-if="isSearching">

                                <template
                                    :key="index"
                                    v-for="(item, index) in [1,2,3]">

                                    <slot name="loadingPlaceholder">

                                        <li
                                            :class="['px-4 py-2 flex items-center space-x-2', disabled ? 'cursor-not-allowed' : 'cursor-pointer']">

                                            <Skeleton width="w-6" height="h-6" rounded="rounded-lg" :shine="true"></Skeleton>
                                            <Skeleton width="w-1/2" height="h-2" :shine="true"></Skeleton>

                                        </li>


                                    </slot>

                                </template>

                            </template>

                            <template v-else>
                                <li
                                    :key="index"
                                    v-for="(option, index) in options"
                                    @click.stop="() => selectOption(option)"
                                    :class="[
                                        'px-4 py-2 text-sm flex justify-between items-center hover:bg-gray-100',
                                        option.disabled ? 'cursor-not-allowed text-gray-400' : 'cursor-pointer text-gray-700',
                                    ]">

                                    <!-- Custom Slot Support -->
                                    <slot name="option" :option="option">

                                        <!-- Default Option Layout -->
                                        <span class="truncate">{{ option.label }}</span>

                                    </slot>

                                </li>
                            </template>

                        </ul>

                        <!-- No Results Found -->
                        <div v-if="!isSearching && options.length === 0" class="text-center px-4 py-2 text-gray-500 text-sm">
                            {{ noResultsText }}
                        </div>

                    </div>

                </div>

                <!-- Suffix Icon Slot -->
                <slot v-if="$slots.suffix" name="suffix"></slot>

            </div>

        </div>

        <span v-if="errorText" class="scroll-to-error font-medium text-red-500 text-xs mt-1 ml-1">
            {{ errorText }}
        </span>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Popover from '@Partials/Popover.vue';
    import Tooltip from '@Partials/Tooltip.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import capitalize from '@Directives/capitalize.js';
    import { generateUniqueId } from '@Utils/generalUtils.js';

    export default {
        directives: { capitalize },
        components: { Input, Popover, Tooltip, Skeleton },
        props: {
            label: {
                type: [String, null],
                default: null
            },
            labelStyle: {
                type: [Object, String, null],
                default: null
            },
            secondaryLabel: {
                type: [String, null],
                default: null
            },
            secondaryLabelStyle: {
                type: [Object, String, null],
                default: null
            },
            popoverContent: {
                type: [String, null],
                default: null
            },
            tooltipContent: {
                type: [String, null],
                default: null
            },
            showAsterisk: {
                type: Boolean,
                default: false
            },
            description: {
                type: [String, null],
                default: null
            },
            externalLinkName: {
                type: [String, null],
                default: null
            },
            externalLinkUrl: {
                type: [String, null],
                default: null
            },
            placeholder: {
                type: [String, null],
                default: null
            },
            disabled: {
                type: Boolean,
                default: false
            },
            skeleton: {
                type: Boolean,
                default: false
            },
            alignItems: {
                type: [String, null],
                default: 'items-center'
            },
            errorText: {
                type: [String, null],
                default: null
            },

            options: {
                type: Array,
                default: () => []
            },
            search: {
                type: [Function, null],
                default: null
            },
            isLoading: {
                type: Boolean,
                default: true
            },
            noResultsText: {
                type: [String, null],
                default: 'No results found'
            },

            emits: ['selected'],
        },
        data() {
            return {
                isOpen: false,
                searchQuery: null,
                isSearching: false,
                uniqueId: generateUniqueId('input'),
            };
        },
        watch: {
            isLoading(newValue) {
                this.isSearching = newValue && this.hasSearchQuery;
            },
            searchQuery(newValue) {
                this.search(newValue);
            }
        },
        computed: {
            hasSearchQuery() {
                return this.searchQuery != null && this.searchQuery.trim() != '';
            }
        },
        methods: {
            openDropdown() {
                this.isOpen = true;
            },
            closeDropdown() {
                this.isOpen = false;
            },
            toggleDropdown() {
                this.isOpen = !this.isOpen;
                this.focusOnInput();
            },
            focusOnInput() {
                this.$nextTick(() => {
                    if (this.$refs.search && document.activeElement !== this.$refs.search) {
                        this.$refs.search.$refs.input.focus();
                    }
                });
            },
            onInputSearchQuery() {
                this.isSearching = true;
                this.openDropdown();
            },
            selectOption(option) {
                if(this.disabled || option.disabled) return;

                this.isOpen = false;
                this.searchQuery = null;
                this.$emit('selected', option);
            },
            handleClickOutside(event) {
                if (this.$refs.dropdown && !this.$refs.dropdown.contains(event.target)) {
                    this.isOpen = false;
                    this.searchQuery = null;
                }
            },
        },
        mounted() {
            document.addEventListener("click", this.handleClickOutside);
        },
        beforeUnmount() {
            document.removeEventListener("click", this.handleClickOutside);
        },
    }

</script>
