<template>

    <vue-countdown
        :time="timeLeftInMilliSeconds"
        class="flex items-center space-x-1"
        v-slot="{ days, hours, minutes, seconds }">

        <slot name="prefix" :hasExpired="hasExpired"></slot>

        <template v-if="type == 'design1'">

            <span
                :class="textClass"
                v-if="days > 0 || hours > 0 || minutes > 0 || seconds > 0">
                <template v-if="days > 0">
                    <span>{{ days }} {{ days == 1 ? 'day ' : 'days '}}</span>
                    <template v-if="hours > 0">
                        <span>{{ hours }} {{ hours == 1 ? 'hr ' : 'hrs '}}</span>
                    </template>
                </template>
                <template v-else-if="hours > 0">
                    <span>{{ hours }} {{ hours == 1 ? 'hr ' : 'hrs '}}</span>
                    <template v-if="minutes > 0">
                        <span>{{ minutes }} {{ minutes == 1 ? 'min ' : 'mins '}}</span>
                    </template>
                </template>
                <template v-else-if="minutes > 0">
                    <span>{{ minutes }} {{ minutes == 1 ? 'min ' : 'mins '}}</span>
                    <template v-if="seconds > 0">
                        <span>{{ seconds }} {{ seconds == 1 ? 'sec ' : 'secs '}}</span>
                    </template>
                </template>
                <template v-else-if="seconds > 0">
                    <span>{{ seconds }} {{ seconds == 1 ? 'sec ' : 'secs '}}</span>
                </template>
            </span>

            <slot v-else>Expired</slot>

            <slot name="suffix" :hasExpired="hasExpired"></slot>

            <Popover v-if="showPopover && !hasExpired" placement="top" :class="popoverClass">
                <template #content>
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                        <thead class="font-bold text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <td class="px-2 py-1 text-center">Days</td>
                                <td class="px-2 py-1 text-center">Hrs</td>
                                <td class="px-2 py-1 text-center">Mins</td>
                                <td class="px-2 py-1 text-center">Secs</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-2 py-1 text-center">{{ days < 10 ? '0'+days : days }}</td>
                                <td class="px-2 py-1 text-center">{{ hours < 10 ? '0'+hours : hours }}</td>
                                <td class="px-2 py-1 text-center">{{ minutes < 10 ? '0'+minutes : minutes }}</td>
                                <td class="px-2 py-1 text-center">{{ seconds < 10 ? '0'+seconds : seconds }}</td>
                            </tr>
                        </tbody>
                    </table>
                </template>
            </Popover>

        </template>

        <template v-else-if="type == 'design2'">

            <div
                class="w-full flex space-x-1"
                v-if="days > 0 || hours > 0 || minutes > 0 || seconds > 0">

                <div class="w-full flex flex-col items-center p-2 bg-gray-100 rounded-lg">
                    <div class="font-bold">{{ days < 10 ? '0'+days : days }}</div>
                    <div>Days</div>
                </div>

                <div class="w-full flex flex-col items-center p-2 bg-gray-100 rounded-lg">
                    <div class="font-bold">{{ hours < 10 ? '0'+hours : hours }}</div>
                    <div>Hrs</div>
                </div>

                <div class="w-full flex flex-col items-center p-2 bg-gray-100 rounded-lg">
                    <div class="font-bold">{{ minutes < 10 ? '0'+minutes : minutes }}</div>
                    <div>Mins</div>
                </div>

                <div class="w-full flex flex-col items-center p-2 bg-gray-100 rounded-lg">
                    <div class="font-bold">{{ seconds < 10 ? '0'+seconds : seconds }}</div>
                    <div>Secs</div>
                </div>

            </div>

            <slot v-else>Expired</slot>

            <slot name="suffix" :hasExpired="hasExpired"></slot>

        </template>

    </vue-countdown>

</template>

<script>

    import dayjs from 'dayjs';
    import Popover from '@Partials/Popover.vue';
    import VueCountdown from '@chenfengyuan/vue-countdown';

    export default {
        components: {
            VueCountdown, Popover
        },
        props: {
            time: {
                type: [String, null],
                required: true
            },
            textClass: {
                type: String,
                default: ''
            },
            type: {
                type: String,
                default: "design1",
                validator: (value) => ["design1", "design2"].includes(value),
            },
            showPopover: {
                type: Boolean,
                default: true
            },
            popoverClass: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                timeLeftInMilliSeconds: 0
            };
        },
        watch: {
            time: {
                immediate: true,
                handler(newVal) {
                    this.calculateTimeLeft(newVal);
                }
            }
        },
        computed: {
            hasExpired() {
                return this.timeLeftInMilliSeconds == 0;
            }
        },
        methods: {
            calculateTimeLeft(time) {

                if(!time) {
                    this.timeLeftInMilliSeconds = 0;
                    return;
                }

                const targetDate = dayjs(time);
                const now = dayjs();
                const diff = targetDate.diff(now);

                if(diff > 0) {
                    this.timeLeftInMilliSeconds = diff;
                } else {
                    this.timeLeftInMilliSeconds = 0;
                }

            }
        }
    };
</script>
