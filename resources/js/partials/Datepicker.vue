<template>

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
                        :class="{ 'font-normal text-gray-400' : !secondaryLabelStyle }">
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

        <div>

            <VueDatePicker
                :range="range"
                :is-24="is24"
                :id="uniqueId"
                :format="format"
                :min-date="minDate"
                :max-date="maxDate"
                :min-time="minTime"
                :max-time="maxTime"
                :disabled="disabled"
                :auto-apply="autoApply"
                :model-type="modelType"
                :year-range="yearRange"
                :start-time="startTime"
                v-model="localModelValue"
                :placeholder="placeholder"
                :reverse-years="reverseYears"
                :allowed-dates="allowedDates"
                :disabled-dates="disabledDates"
                :enable-seconds="enableSeconds"
                :enable-minutes="enableMinutes"
                :focus-start-date="focusStartDate"
                :hours-increment="hoursIncrement"
                :no-hours-overlay="noHoursOverlay"
                :hide-offset-dates="hideOffsetDates"
                :minutes-increment="minutesIncrement"
                :seconds-increment="secondsIncrement"
                :enable-time-picker="enableTimePicker"
                :time-picker-inline="timePickerInline"
                :no-minutes-overlay="noMinutesOverlay"
                :no-seconds-overlay="noSecondsOverlay"
                :disabled-week-days="disabledWeekDays"
                :hours-grid-increment="hoursGridIncrement"
                :minutes-grid-increment="minutesGridIncrement"
                :seconds-grid-increment="secondsGridIncrement"
                :ignore-time-validation="ignoreTimeValidation"
                :disable-month-year-select="disableMonthYearSelect"
                :prevent-min-max-navigation="preventMinMaxNavigation"
                :disable-time-range-validation="disableTimeRangeValidation">
            </VueDatePicker>

            <span v-if="errorText" class="scroll-to-error font-medium text-red-500 text-xs mt-1 ml-1">
                {{ errorText }}
            </span>

        </div>

    </div>

</template>

<script>

    import Popover from '@Partials/Popover.vue';
    import Tooltip from '@Partials/Tooltip.vue';
    import '@vuepic/vue-datepicker/dist/main.css';
    import VueDatePicker from '@vuepic/vue-datepicker';
    import capitalize from '@Directives/capitalize.js';
    import { generateUniqueId } from '@Utils/generalUtils.js';

    export default {
        directives: { capitalize },
        components: { Popover, Tooltip, VueDatePicker },
        props: {
            modelValue: {
                type: [String, Array, null]
            },
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
            errorText: {
                type: [String, null],
                default: null
            },

            //  Datetime Input Specific Props
            //  Reference: https://vue3datepicker.com/props/modes
            format: {
                type: String,
                default: 'dd MMM yyyy'    //  dd MMM yyyy HH:mm
            },
            modelType: {
                type: String,
                default: 'yyyy-MM-dd'     //  yyyy-MM-dd HH:mm
            },
            range: {
                type: Boolean,
                default: false
            },
            autoApply: {
                type: Boolean,
                default: true
            },
            minDate: {
                type: [String, null],
                default: null
            },
            maxDate: {
                type: [String, null],
                default: null
            },
            startDate: {
                type: [String, null],
                default: null
            },
            focusStartDate: {
                type: [String, null],
                default: null
            },
            allowedDates: {
                type: [Array, null],
                default: null
            },
            disabledDates: {
                type: [Array, null],
                default: () => []
            },
            disabledWeekDays: {
                type: [Array, null],
                default: () => []
            },
            hideOffsetDates: {
                type: Boolean,
                default: false
            },
            preventMinMaxNavigation: {
                type: Boolean,
                default: false
            },
            ignoreTimeValidation: {
                type: Boolean,
                default: false
            },
            disableMonthYearSelect: {
                type: Boolean,
                default: false
            },
            yearRange: {
                type: Array,
                default: () => [1900, 2100]
            },
            reverseYears: {
                type: Boolean,
                default: false
            },
            filters: {
                type: [Object, null],
                default: function () {

                    /*
                     *  Disable specific values from being selected in the month, year, and time picker overlays:
                     *
                     *  {
                     *     months?: number[];       // 0 = Jan, 11 - Dec
                     *     years?: number[];        // Array of years to disable
                     *     times?: {
                     *         hours?: number[];    // disable specific hours
                     *         minutes?: number[];  // disable sepcific minutes
                     *         seconds?: number[]   // disable specific seconds
                     *     }
                     *  }
                    */
                    return null;
                }
            },
            enableTimePicker: {
                type: Boolean,
                default: true
            },
            timePickerInline: {
                type: Boolean,
                default: false
            },
            is24: {
                type: Boolean,
                default: true
            },
            enableSeconds: {
                type: Boolean,
                default: false
            },
            enableMinutes: {
                type: Boolean,
                default: true
            },
            hoursIncrement: {
                type: [Number, String],
                default: 1
            },
            minutesIncrement: {
                type: [Number, String],
                default: 1
            },
            secondsIncrement: {
                type: [Number, String],
                default: 1
            },
            hoursGridIncrement: {
                type: [Number, String],
                default: 1
            },
            minutesGridIncrement: {
                type: [Number, String],
                default: 5
            },
            secondsGridIncrement: {
                type: [Number, String],
                default: 5
            },
            noHoursOverlay: {
                type: Boolean,
                default: false
            },
            noMinutesOverlay: {
                type: Boolean,
                default: false
            },
            noSecondsOverlay: {
                type: Boolean,
                default: false
            },
            minTime: {
                type: [Object, null],
                default: function () {

                    /*
                     *  Sets the minimal available time to pick:
                     *
                     *  Structure:
                     *
                     *  { hours?: number | string; minutes?: number | string; seconds?: number | string }
                     *
                     *  Example:
                     *
                     *  { hours: 11, minutes: 30 }
                    */
                    return null;
                }
            },
            maxTime: {
                type: [Object, null],
                default: function () {

                    /*
                     *  Sets the maximal available time to pick:
                     *
                     *  Structure:
                     *
                     *  { hours?: number | string; minutes?: number | string; seconds?: number | string }
                     *
                     *  Example:
                     *
                     *  { hours: 11, minutes: 30 }
                    */
                    return null;
                }
            },
            startTime: {
                type: [Object, null],
                default: function () {

                    /*
                     *  Set some default starting time:
                     *
                     *  Structure:
                     *
                     *  { hours?: number | string; minutes?: number | string; seconds?: number | string }
                     *
                     *  Example:
                     *
                     *  { hours: 11, minutes: 30 }
                    */
                    return null;
                }
            },
            disableTimeRangeValidation: {
                type: Boolean,
                default: false
            },
        },
        emits: ['update:modelValue', 'change'],
        data() {
            return {
                datetimePicker: null,
                uniqueId: generateUniqueId('datepicker')
            };
        },
        computed: {
            localModelValue: {
                get() {
                    return this.modelValue;
                },
                set(value) {
                    this.$emit("update:modelValue", value);
                    this.$emit("change", value);
                }
            }
        }
    };

  </script>
