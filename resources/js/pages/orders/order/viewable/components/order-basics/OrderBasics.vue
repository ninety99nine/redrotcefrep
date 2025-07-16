<template>

    <div class="bg-white rounded-lg p-4 mb-4">

        <!-- Order Basics (Loading Placeholder) -->
        <div v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="grid grid-cols-2 gap-4">

            <div class="col-span-2">

                <div class="flex items-center justify-end space-x-1">
                    <Skeleton width="w-32" :shine="true"></Skeleton>
                    <Skeleton width="w-4" :shine="true"></Skeleton>
                </div>

            </div>

            <div class="col-span-1 space-y-2">
                <span class="text-sm leading-6 font-medium text-gray-900">Status</span>
                <Skeleton width="w-full" height="h-8" rounded="rounded-md" :shine="true"></Skeleton>
            </div>

            <div class="col-span-1 space-y-2">
                <span class="text-sm leading-6 font-medium text-gray-900">Payment Status</span>
                <Skeleton width="w-full" height="h-8" rounded="rounded-md" :shine="true"></Skeleton>
            </div>

            <div class="col-span-1 space-y-2">
                <span class="text-sm leading-6 font-medium text-gray-900">Courier</span>
                <Skeleton width="w-full" height="h-8" rounded="rounded-md" :shine="true"></Skeleton>
            </div>

            <div class="col-span-1 space-y-2">
                <span class="text-sm leading-6 font-medium text-gray-900">Tracking Number</span>
                <Skeleton width="w-full" height="h-8" rounded="rounded-md" :shine="true"></Skeleton>
            </div>

            <div class="col-span-2 space-y-2">
                <span class="text-sm leading-6 font-medium text-gray-900">Internal Note</span>
                <Skeleton width="w-full" height="h-16" rounded="rounded-md" :shine="true"></Skeleton>
            </div>

            <div class="col-span-2 space-y-2">
                <span class="text-sm leading-6 font-medium text-gray-900">Remark</span>
                <Skeleton width="w-full" height="h-16" rounded="rounded-md" :shine="true"></Skeleton>
            </div>

        </div>

        <div v-else class="grid grid-cols-2 gap-4">

            <div class="col-span-2">

                <div class="flex items-center justify-end space-x-1">
                    <span class="text-gray-500 text-xs">{{ formattedDatetime(order.created_at) }}</span>
                    <Popover :content="`Created ${formattedRelativeDate(order.created_at)}`"></Popover>
                </div>

            </div>

            <!-- Status Select Input -->
            <div class="col-span-1">

                <Select
                    label="Status"
                    :search="false"
                    :options="statuses"
                    v-model="orderForm.status"
                    @change="(status) => updateOrder({ status: status }, 'status')">
                </Select>

            </div>

            <!-- Payment Status Select Input -->
            <div class="col-span-1">

                <Select
                    :search="false"
                    label="Payment Status"
                    :options="paymentStatuses"
                    v-model="orderForm.payment_status"
                    @change="(paymentStatus) => updateOrder({ payment_status: paymentStatus }, 'payment status')">
                </Select>

            </div>

            <!-- Courier Select Input -->
            <div class="col-span-1">

                <Select
                    :search="true"
                    label="Courier"
                    :options="couriers"
                    v-model="orderForm.courier_id"
                    :errorText="formState.getFormError('courier_id')"
                    @change="(courierId) => updateOrder({ courier_id: courierId }, 'courier')">
                </Select>

            </div>

            <!-- Tracking Number Text Input -->
            <div class="col-span-1">

                <Input
                    type="text"
                    label="Tracking Number"
                    v-model="orderForm.tracking_number"
                    :errorText="formState.getFormError('tracking_number')"
                    @blur="(trackingNumber) => updateOrder({ tracking_number: trackingNumber }, 'tracking number')">
                </Input>

            </div>

            <!-- Internal Note Textarea -->
            <div class="col-span-2">

                <Input
                    rows="2"
                    type="textarea"
                    label="Internal Note"
                    v-model="orderForm.internal_note"
                    description="Visible to team members"
                    :errorText="formState.getFormError('internal_note')"
                    @blur="(internalNote) => updateOrder({ internal_note: internalNote }, 'internal note')"
                    tooltipContent="Internal information about this order only visible to you and other team members">
                </Input>

            </div>

            <!-- Remark Textarea -->
            <div class="col-span-2">

                <Input
                    rows="2"
                    type="textarea"
                    label="Remark"
                    placeholder="Optional"
                    v-model="orderForm.remark"
                    description="Visible to customer"
                    :errorText="formState.getFormError('remark')"
                    @blur="(remark) => updateOrder({ remark: remark }, 'remark')"
                    tooltipContent="Use the Remark field to add a note that will be visible to the customer, such as special instructions, order details, or personalized messages.">
                </Input>

            </div>

        </div>

    </div>

</template>

<script>

    import axios from 'axios';
    import Input from '@Partials/Input.vue';
    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { capitalize } from '@Utils/stringUtils.js';
    import { formattedDatetime, formattedRelativeDate } from '@Utils/dateUtils.js';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Input, Select, Popover, Skeleton },
        data() {
            return {
                couriers: [],
                isLoadingCouriers: false
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
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
                return this.$route.name === 'edit-order';
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
            formattedDatetime: formattedDatetime,
            formattedRelativeDate: formattedRelativeDate,
            async showCouriers() {
                try {

                    if(this.couriers.length) return;

                    this.isLoadingCouriers = true;

                    let config = {
                        params: {
                            'per_page': 100
                        }
                    };

                    const response = await axios.get('/api/couriers', config);

                    this.couriers = response.data.data.map((courier) => {
                        return {
                            'label': capitalize(courier.name),
                            'value': courier.id
                        }
                    });

                    this.couriers.unshift({
                        'label': 'None',
                        'value': null
                    });

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching stores';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch stores:', error);
                } finally {
                    this.isLoadingCouriers = false;
                }
            },
            async updateOrder(data, type) {

                try {

                    data = {
                        ...data,
                        store_id: this.store.id
                    };

                    await axios.put(`/api/orders/${this.order.id}`, data);

                    if(type == 'status') {
                        this.notificationState.showSuccessNotification(`Order ${this.orderForm.status}`);
                    }else if(type == 'payment status') {
                        this.notificationState.showSuccessNotification(`Order ${this.orderForm.payment_status}`);
                    }else if(type == 'courier') {
                        if(this.orderForm.courier_id) {
                            const courier = this.couriers.find(courier => courier.value === this.orderForm.courier_id);
                            this.notificationState.showSuccessNotification(`Courier updated to ${courier.label}`);
                        }else{
                            this.notificationState.showSuccessNotification(`Courier removed`);
                        }
                    }else if(type == 'tracking number') {
                        if(this.orderForm.tracking_number) {
                            this.notificationState.showSuccessNotification(`Tracking number updated to ${this.orderForm.tracking_number}`);
                        }else{
                            this.notificationState.showSuccessNotification(`Tracking number removed`);
                        }
                    }else if(type == 'remark') {
                        if(this.orderForm.remark) {
                            this.notificationState.showSuccessNotification(`Remark updated`);
                        }else{
                            this.notificationState.showSuccessNotification(`Remark removed`);
                        }
                    }else if(type == 'internal note') {
                        if(this.orderForm.internal_note) {
                            this.notificationState.showSuccessNotification(`Internal note updated`);
                        }else{
                            this.notificationState.showSuccessNotification(`Internal note removed`);
                        }
                    }else {
                        this.notificationState.showSuccessNotification(`Order updated`);
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update order changes:', error);
                }

            }
        },
        created() {
            this.showCouriers();
        }
    };

</script>
