<template>

    <!-- Order Basics (Loading Placeholder) -->
    <div v-if="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)" class="space-y-2">

        <div v-for="(_, index) in [1, 2, 3]" :key="index" class="border-b shadow-sm rounded-lg py-6 px-4 bg-gray-50">
            <LineSkeleton width="w-full" :shine="true"></LineSkeleton>
        </div>

    </div>

    <div v-else class="space-y-2">

        <CustomSelect
            label="Status"
            :search="false"
            :options="statuses"
            v-model="orderForm.status"
            @change="orderState.saveStateDebounced(`Status changed to ${ orderForm.status }`)">
        </CustomSelect>

        <CustomSelect
            :search="false"
            label="Payment Status"
            :options="paymentStatuses"
            v-model="orderForm.paymentStatus"
            @change="orderState.saveStateDebounced(`Payment status changed to ${ orderForm.paymentStatus }`)">
        </CustomSelect>

        <CustomSelect
            :search="false"
            label="Couriers"
            :options="couriers"
            v-model="orderForm.courierId"
            :errorText="formState.getFormError('courierId')"
            @change="orderState.saveStateDebounced('Courier changed')">
        </CustomSelect>

        <CustomInput
            type="text"
            label="Tracking Number"
            v-model="orderForm.trackingNumber"
            :errorText="formState.getFormError('trackingNumber')"
            @input="orderState.saveStateDebounced('Tracking number changed')">
        </CustomInput>

        <CustomInput
            rows="2"
            type="textarea"
            label="Internal Note"
            v-model="orderForm.internalNote"
            :errorText="formState.getFormError('internalNote')"
            @change="orderState.saveStateDebounced('Internal note changed')"
            tooltipContent="Internal information about this order only visible to you and other team members">
        </CustomInput>

    </div>

</template>

<script>

    import axios from 'axios';
    import { capitalize } from '@Utils/stringUtils.js';
    import CustomInput from '@Partials/inputs/CustomInput.vue';
    import CustomSelect from '@Partials/inputs/CustomSelect.vue';
    import LineSkeleton from '@Partials/skeletons/LineSkeleton.vue';

    export default {
        inject: ['apiState', 'formState', 'storeState', 'orderState', 'notificationState'],
        components: { CustomInput, CustomSelect, LineSkeleton },
        data() {
            return {
                couriers: [],
                isLoadingCouriers: false
            }
        },
        computed: {
            hasOrder() {
                return this.orderState.hasOrder;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isEditting() {
                return this.$route.name === 'edit-store-order';
            },
            statuses() {
                const options = ['waiting','cancelled','completed','on its way','ready for pickup'];

                return options.map((option) => {
                    return {
                        'label': capitalize(option),
                        'value': option
                    }
                });
            },
            paymentStatuses() {
                const options = ['paid','unpaid','pending payment','partially paid'];

                return options.map((option) => {
                    return {
                        'label': capitalize(option),
                        'value': option
                    }
                });
            }
        },
        methods: {
            async showCouriers() {

                if(this.couriers.length) return;

                this.isLoadingCouriers = true;

                const url = this.apiState.apiHome['_links']['showCouriers'];

                let config = {
                    params: {
                        'per_page': 100
                    }
                }

                await axios.get(url, config).then(response => {

                    if(response.status == 200) {

                        this.couriers = [
                            {
                                label: 'None',
                                value: null
                            },
                            ...response.data.data.map((courier) => {
                                return {
                                    'label': capitalize(courier.name),
                                    'value': courier.id
                                }
                            })
                        ];

                    }else{
                        this.formState.setFormError('general', response.data.message);
                        this.notificationState.showWarningNotification(response.data.message);
                    }

                }).catch(errorException => {
                    this.formState.setServerFormErrors(errorException);
                });

                this.isLoadingCouriers = false;

            }
        },
        created() {
            this.showCouriers();
        }
    };

</script>
