<template>

    <div
        ref="dropdown"
        :class="[dropdownWrapperClasses, 'relative']">

        <!-- Trigger Slot -->
        <slot name="trigger" :isOpen="isOpen" :toggleDropdown="toggleDropdown">

            <Button
                v-if="trigger"
                :size="triggerSize"
                :type="triggerType"
                :action="toggleDropdown"
                :skeleton="triggerLoading"
                :leftIcon="triggerLeftIcon"
                :leftIconSize="triggerLeftIconSize">

                <div
                    class="flex items-center space-x-1"
                    v-if="$slots.triggerText || triggerText || showTriggerArrow">

                    <slot v-if="$slots.triggerText" name="triggerText"></slot>
                    <span v-else-if="triggerText">{{ triggerText }}</span>

                    <template v-if="showTriggerArrow">
                        <svg v-if="isOpen" @click.stop="toggleDropdown" :class="['w-4 h-4 text-gray-700', { 'opacity-20' : triggerLoading }]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                        </svg>

                        <svg v-else @click.stop="toggleDropdown" :class="['w-4 h-4 text-gray-700', { 'opacity-20' : triggerLoading }]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </template>

                </div>

            </Button>

        </slot>

        <!-- Dropdown Content -->
        <div
            role="menu"
            :id="uniqueId"
            :class="[
                dropdownClasses,
                dropdownBorderClasses,
                {
                    'left-0': position === 'right',
                    'right-0': position === 'left',
                    'left-1/2 -translate-x-1/2': position === 'center'
                },
                { 'hidden opacity-0 invisible': !isOpen, 'opacity-100 visible': isOpen },
                'absolute z-50 bg-white mt-1 text-start rounded-lg overflow-hidden shadow-md dark:bg-neutral-800 dark:border-neutral-700 transition-opacity duration-200 ease-in-out'
            ]">

            <slot name="header"></slot>

            <slot name="content" :toggleDropdown="toggleDropdown" :handleItemClick="handleItemClick" :options="options">

                <ul class="max-h-60 overflow-auto">

                    <li
                        :key="index"
                        v-for="(option, index) in options"
                        @click="() => handleItemClick(option)"
                        :class="[
                            'flex items-center space-x-2 px-4 py-2 text-sm hover:bg-gray-100',
                            option.disabled ? 'cursor-not-allowed text-gray-400' : 'cursor-pointer text-gray-700',
                        ]">

                        <!-- Left Icon -->
                        <component v-if="option.icon" :is="option.icon" :size="option.iconSize ?? '16'" />

                        <!-- Custom Slot Support -->
                        <slot name="option" :option="option">
                            <span class="truncate">{{ option.label }}</span>
                        </slot>

                    </li>

                </ul>

            </slot>

            <slot name="footer"></slot>

        </div>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { generateUniqueId } from '@Utils/generalUtils.js';

    export default {
        components: { Button },
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
            triggerLeftIcon: {
                type: [Object, Function, null],
                default: null
            },
            triggerLeftIconSize: {
                type: String,
                default: '16',
            },
            showTriggerArrow: {
                type: Boolean,
                default: false
            },
            options: {
                type: Array,
                default: () => []
            },
            trigger: {
                type: String,
                default: "click",
                validator: (value) => ["click", "hover"].includes(value),
            },
            position: {
                type: String,
                default: "center",
                validator: (value) => ["left", "right", "center"].includes(value),
            },
            dropdownClasses: {
                type: String,
                default: "w-full"
            },
            dropdownBorderClasses: {
                type: String,
                default: "border border-gray-100"
            },
            dropdownWrapperClasses: {
                type: String,
                default: ""
            }
        },
        data() {
            return {
                uniqueId: generateUniqueId('dropdown'),
                isOpen: false
            }
        },
        mounted() {
            setTimeout(() => {
                if (window.HSDropdown) {
                    window.HSDropdown.autoInit();
                }
            }, 500);

            document.addEventListener("click", this.handleClickOutside);
        },
        beforeUnmount() {
            document.removeEventListener("click", this.handleClickOutside);
        },
        methods: {
            showDropdown() {
                if(this.triggerLoading) return;
                this.isOpen = true;
            },
            hideDropdown() {
                if(this.triggerLoading) return;
                this.isOpen = false;
            },
            toggleDropdown(event) {
                if(this.triggerLoading) return;
                event.stopPropagation(); // Prevents immediate closing when clicking inside
                this.isOpen = !this.isOpen;
            },
            handleItemClick(item) {
                if(this.triggerLoading) return;
                if (item.action && typeof item.action === "function") {
                    item.action();
                }
                this.isOpen = false; // Close dropdown after selection
            },
            handleClickOutside(event) {
                if (this.$refs.dropdown && !this.$refs.dropdown.contains(event.target)) {
                    this.isOpen = false;
                }
            }
        }
    };
</script>
