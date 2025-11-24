<template>

    <div class="bg-white p-8 shadow-sm rounded-xl mb-2">

        <h1 class="flex items-center font-bold mb-4">
            <Clock size="20" class="mr-2 shrink-0"></Clock>
            <span>Opening Hours</span>
        </h1>

        <p class="text-sm text-gray-500 mb-4">Set your store's operating hours, including availability and time slots for each day. Indicate if the store is open or closed on any specific day.</p>

        <div class="space-y-4">

            <!-- Online Toggle Switch -->
            <Skeleton v-if="isLoadingStore || !store" width="w-8" :shine="true"></Skeleton>
            <Switch
                v-else
                size="xs"
                suffixText="Show opening hours"
                v-model="storeForm.show_opening_hours"
                :errorText="formState.getFormError('show_opening_hours')"
                @change="storeState.saveStateDebounced('Opening hours status changed')"
                tooltipContent="Turn on if you would like your store to showcase its open or closed hours"
            />

            <template v-if="storeForm.show_opening_hours">

                <Switch
                    size="xs"
                    suffixText="Allow checkout during closed hours"
                    v-model="storeForm.allow_checkout_on_closed_hours"
                    :errorText="formState.getFormError('allow_checkout_on_closed_hours')"
                    @change="storeState.saveStateDebounced('Opening hours status changed')"
                    tooltipContent="Turn on to allow customers to place orders during closed hours"
                />

                <Alert
                    type="primary"
                    :dismissable="false"
                    v-if="!storeForm.allow_checkout_on_closed_hours"
                    title="Customers will be restricted from placing orders during closed hours">
                </Alert>

                <div
                    :key="index"
                    v-for="(openingHour, index) in storeForm.opening_hours"
                    class="border-t border-gray-300 border-dashed pt-4 grid grid-cols-2 gap-4 items-start">

                    <div class="col-span-1">

                        <!-- Checkbox -->
                        <Input
                            type="checkbox"
                            :inputLabel="days[index]"
                            v-model="storeForm.opening_hours[index].available"
                            @change="storeState.saveStateDebounced('Opening day changed')">
                        </Input>

                    </div>

                    <div class="col-span-1 space-y-2">

                        <div
                            :key="index2"
                            v-for="(hour, index2) in openingHour.hours">

                            <div class="flex items-center space-x-2">

                                <!-- Start Time Input -->
                                <Input
                                    type="time"
                                    v-model="hour[0]"
                                    :disabled="!openingHour.available"
                                    @change="storeState.saveStateDebounced('Opening hour changed')"
                                    :errorText="formState.getFormError(`opening_hours.${index}.hours.${index2}.0`)">
                                </Input>

                                <span>-</span>

                                <!-- End Time Input -->
                                <Input
                                    type="time"
                                    v-model="hour[1]"
                                    :disabled="!openingHour.available"
                                    @change="storeState.saveStateDebounced('Opening hour changed')"
                                    :errorText="formState.getFormError(`opening_hours.${index}.hours.${index2}.1`)">>
                                </Input>

                                <div :class="[openingHour.available ? 'cursor-pointer' : 'opacity-0']">

                                    <!-- Add Icon -->
                                    <div v-if="index2 == 0" @click="() => addOpeningHour(index)" class="shrink-0 rounded-md border border-gray-300 p-1 hover:bg-blue-50 transition-all">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </div>

                                    <!-- Remove Icon -->
                                    <div v-else @click="() => removeOpeningHour(index, index2)" class="shrink-0 rounded-md border border-gray-300 p-1 bg-red-50 hover:bg-red-100 transition-all">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                        </svg>
                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>

            </template>

        </div>

    </div>

</template>

<script>

    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import { Clock } from 'lucide-vue-next';
    import Switch from '@Partials/Switch.vue';
    import Skeleton from '@Partials/Skeleton.vue';

    export default {
        inject: ['formState', 'storeState'],
        components: {
            Alert, Clock, Input, Switch, Skeleton
        },
        data() {
            return {
                days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            storeForm() {
                return this.storeState.storeForm;
            }
        },
        methods: {
            addOpeningHour(index) {
                if(this.storeForm.opening_hours[index].available) {
                    this.storeForm.opening_hours[index].hours.push(['08:00', '16:00']);
                }
            },
            removeOpeningHour(index, index2) {
                if(this.storeForm.opening_hours[index].available) {
                    this.storeForm.opening_hours[index].hours.splice(index2, 1);
                }
            },
        }
    };

</script>
