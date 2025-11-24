<template>

    <div>

        <!-- Tools -->
        <div class="flex items-center justify-between space-x-8">

            <p class="text-sm text-gray-500">Set days and times customers can expect delivery or pickup</p>

            <Pill type="primary" size="md" :showDot="false" :leftIcon="Clock" :action="showBulkSetTimeslotsModal">
                <span class="ml-1">Bulk Set Timeslots</span>
            </Pill>
        </div>

        <div class="mt-2">

            <div
                :key="index"
                v-for="(operationalHour, index) in deliveryMethodForm.operational_hours"
                :class="[{ 'border-t' : index != 0 }, index == deliveryMethodForm.operational_hours.length - 1 ? 'border-b pb-4' : 'pb-4', 'border-blue-200 border-dashed hover:bg-blue-50 pt-4 px-2 grid grid-cols-2 gap-4 items-start']">

                <div class="col-span-1">

                    <!-- Available Checkbox -->
                    <Input
                        type="checkbox"
                        :inputLabel="days[index]"
                        v-model="operationalHour.available"
                        @change="deliveryMethodState.saveStateDebounced('Operational hour changed')">
                    </Input>

                </div>

                <div class="col-span-1 space-y-2">

                    <div v-for="(hour, index2) in operationalHour.hours" :key="index2">

                        <div class="flex items-center space-x-2">

                            <!-- Start Time Input -->
                            <Input
                                type="time"
                                v-model="hour[0]"
                                :disabled="!operationalHour.available"
                                @change="deliveryMethodState.saveStateDebounced('Operational hour changed')"
                                :errorText="formState.getFormError(`opening_hours.${index}.hours.${index2}.0`)">
                            </Input>

                            <span>-</span>

                            <!-- End Time Input -->
                            <Input
                                type="time"
                                v-model="hour[1]"
                                :disabled="!operationalHour.available"
                                @change="deliveryMethodState.saveStateDebounced('Opening hour changed')"
                                :errorText="formState.getFormError(`opening_hours.${index}.hours.${index2}.1`)">
                            </Input>

                            <div :class="[operationalHour.available ? 'cursor-pointer' : 'opacity-0']">

                                <!-- Add Icon -->
                                <div v-if="index2 == 0" @click="() => addOperationalHour(index)" class="shrink-0 rounded-md border border-gray-300 p-1 hover:bg-blue-50 transition-all">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </div>

                                <!-- Remove Icon -->
                                <div v-else @click="() => removeOperationalHour(index, index2)" class="shrink-0 rounded-md border border-gray-300 p-1 bg-red-50 hover:bg-red-100 transition-all">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                    </svg>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div
                        v-if="operationalHour.available"
                        class="flex space-x-1 items-center mt-2">

                        <!-- Total Timeslots Badge -->
                        <Pill
                            size="xs"
                            type="success"
                            :showDot="false">
                            {{ timeslots[index].length+' '+(timeslots[index].length == 1 ? 'timeslot' : 'timeslots') }}
                        </Pill>

                        <!-- Timeslot Options -->
                        <Tooltip position="top">
                            <template #content>
                                <div class="space-y-2">
                                    <p
                                        :key="index"
                                        v-for="(timeslot, index) in timeslots[index]"
                                        class="flex space-x-2 px-2 border-l-4 border-green-300">
                                        {{ timeslot }}
                                    </p>
                                </div>
                            </template>
                        </Tooltip>

                    </div>

                </div>

            </div>

        </div>

        <!-- Bulk Set Timeslots -->
        <Modal
            approveType="primary"
            :scrollOnContent="false"
            ref="bulkSetTimeslotsModal"
            approveText="Add Timeslots"
            :approveDisabled="hours.length == 0"
            :approveAction="(hideModal) => bulkSetTimeslots(hideModal)">

            <template #content>
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Bulk Set Timeslots</p>
                <p class="text-sm mb-4">Set the timeslots to be created</p>

                <!-- Timeslots -->
                <div class="space-y-4 mb-8">

                    <div v-for="(hour, index) in hours" :key="index">

                        <div class="flex items-center space-x-2 mb-4">

                            <!-- Start Time Input -->
                            <Input
                                type="time"
                                v-model="hour[0]">
                            </Input>

                            <span>-</span>

                            <!-- End Time Input -->
                            <Input
                                type="time"
                                v-model="hour[1]">
                            </Input>

                            <div class="cursor-pointer">

                                <!-- Add Icon -->
                                <div v-if="index == 0" @click="() => addHour()" class="shrink-0 rounded-md border border-gray-300 p-1 hover:bg-blue-50 transition-all">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </div>

                                <!-- Remove Icon -->
                                <div v-else @click="() => removeHour(index)" class="shrink-0 rounded-md border border-gray-300 p-1 bg-red-50 hover:bg-red-100 transition-all">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                    </svg>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">

                        <div v-for="(availableDay, index) in availableDays" :key="index">

                            <!-- Day Checkbox -->
                            <Input
                                type="checkbox"
                                :inputLabel="availableDay.name"
                                v-model="availableDay.selected"
                                :disabled="availableDay.selected && totalSelectedDays === 1">
                            </Input>

                        </div>

                    </div>

                </div>

            </template>

        </Modal>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import { Clock } from 'lucide-vue-next';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import cloneDeep from 'lodash.clonedeep';
    import Tooltip from '@Partials/Tooltip.vue';

    export default {
        inject: ['formState', 'storeState', 'deliveryMethodState', 'notificationState'],
        components: {
            Pill, Input, Modal, Tooltip
        },
        data() {
            return {
                Clock,
                hours: [],
                availableDays: [],
                defaultTimeslot: ['08:00', '16:00'],
                days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
            }
        },
        watch: {
            'deliveryMethodForm.auto_generate_time_slots'(newValue, oldValue) {

                // Only trigger when switching from false to true
                if (newValue && !oldValue) {

                    // Current timeslots (with auto_generate_time_slots = true)
                    const currentTimeslots = this.timeslots.map((day) => day.length).reduce((sum, length) => sum + length, 0);

                    // Only show notification if new timeslots were added
                    if (currentTimeslots > 0) {
                        this.notificationState.showSuccessNotification(`Added ${currentTimeslots} ${currentTimeslots === 1 ? 'timeslot' : 'timeslots'}`);
                    }
                }

            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingDeliveryMethod() {
                return this.deliveryMethodState.isLoadingDeliveryMethod;
            },
            deliveryMethodForm() {
                return this.deliveryMethodState.deliveryMethodForm;
            },
            totalSelectedDays() {
                return this.availableDays.filter((availableDay) => availableDay.selected).length;
            },
            timeslots() {
                const timeslots = [];

                // Helper function to parse time into minutes since midnight
                const timeToMinutes = (time) => {
                    const [hours, minutes] = time.split(':').map(Number);
                    return hours * 60 + minutes;
                };

                // Helper function to convert minutes back to a readable time format
                const minutesToTime = (minutes) => {
                    const hours = Math.floor(minutes / 60).toString().padStart(2, '0');
                    const mins = (minutes % 60).toString().padStart(2, '0');
                    return `${hours}:${mins}`;
                };

                // Iterate over the operational hours for each day
                this.deliveryMethodForm.operational_hours.forEach((day, index) => {

                    const dayTimeslots = new Set();  // Using a Set to avoid duplicates

                    if(day.available) {
                        day.hours.forEach((range) => {
                            const [startTime, endTime] = range;
                            const [startTimeInMinutes, endTimeInMinutes] = range.map(timeToMinutes);

                            if(this.deliveryMethodForm.auto_generate_time_slots) {

                                // Generate timeslots based on the selected interval if auto_generate_time_slots is true
                                const interval = this.deliveryMethodForm.time_slot_interval_unit === 'hour'
                                    ? this.deliveryMethodForm.time_slot_interval_value * 60
                                    : this.deliveryMethodForm.time_slot_interval_value;

                                for (let currentStartTimeInMinutes = startTimeInMinutes; currentStartTimeInMinutes + interval <= endTimeInMinutes; currentStartTimeInMinutes += interval) {
                                    const currentEndTimeInMinutes = currentStartTimeInMinutes + interval;
                                    const currentStartTime = minutesToTime(currentStartTimeInMinutes);

                                    let currentEndTime;
                                    const isLastItem = currentStartTimeInMinutes + interval >= endTimeInMinutes;

                                    if(isLastItem) {
                                        // For the last item, use the full end time
                                        currentEndTime = minutesToTime(currentEndTimeInMinutes);
                                    } else {
                                        // For other items, use the end time minus 1 minute
                                        currentEndTime = minutesToTime(currentEndTimeInMinutes - 1);
                                    }

                                    // Add the formatted timeslot to the Set (no duplicates)
                                    dayTimeslots.add(`${currentStartTime} - ${currentEndTime}`);
                                }

                            } else {
                                // If auto_generate_time_slots is false, use the existing timeslots without modification
                                dayTimeslots.add(`${startTime} - ${endTime}`);
                            }
                        });
                    }

                    // Convert the Set to an array and sort the timeslots in order (from earliest to latest)
                    const sortedTimeslots = [...dayTimeslots].sort((a, b) => {
                        const [startA] = a.split(' - ');
                        const [startB] = b.split(' - ');
                        return timeToMinutes(startA) - timeToMinutes(startB);
                    });

                    // Push the day's sorted timeslots to the main timeslot array
                    timeslots.push(sortedTimeslots);
                });

                return timeslots;
            },
        },
        methods: {
            addHour() {
                this.hours.push(cloneDeep(this.defaultTimeslot));
            },
            removeHour(index) {
                this.hours.splice(index, 1);
            },
            addOperationalHour(index) {
                this.deliveryMethodForm.operational_hours[index].hours.push(cloneDeep(this.defaultTimeslot));
                this.deliveryMethodState.saveStateDebounced('Operational hour changed');
            },
            removeOperationalHour(index, index2) {
                this.deliveryMethodForm.operational_hours[index].hours.splice(index2, 1);
                this.deliveryMethodState.saveStateDebounced('Operational hour changed');
            },
            showBulkSetTimeslotsModal() {
                if(this.hours.length == 0) {
                    this.addHour();
                }
                this.$refs.bulkSetTimeslotsModal.showModal();
            },
            bulkSetTimeslots(hideModal) {

                // Remove duplicates from this.hours (array of arrays)
                const uniqueHours = Array.from(
                    new Set(this.hours.map((range) => JSON.stringify(range)))
                ).map((range) => JSON.parse(range));


                this.deliveryMethodForm.operational_hours.forEach((day, index) => {

                    if(this.availableDays[index].selected) {
                        day.hours = cloneDeep(uniqueHours);
                    }

                    if(this.availableDays[index].selected && !day.available) {
                        day.available = true;
                    }
                });

                this.hours = [];

                this.notificationState.showSuccessNotification('Timeslots added');

                hideModal();
            },
        },
        created() {
            this.availableDays = this.deliveryMethodForm.operational_hours.map((day, index) => {
                return {
                    name: this.days[index],
                    selected: true
                };
            });
        }
    };

</script>
