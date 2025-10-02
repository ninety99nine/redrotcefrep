<template>

    <div class="bg-white rounded-lg space-y-4 p-4 mb-4">

        <div class="flex justify-between items-center mb-4">

            <h1 class="text-gray-700 font-semibold">Fulfillment</h1>

            <!-- Collection Status -->
            <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-20" :shine="true"></Skeleton>
            <CollectionStatus v-else></CollectionStatus>

        </div>

        <!-- Order Products (Loading Placeholder) -->
        <div v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="space-y-2">

            <div
                :key="index"
                v-for="(_, index) in [1, 2]" class="rounded-lg overflow-hidden border border-gray-300">

                <div class="space-y-2 p-2 px-4 bg-gray-50">

                    <div class="flex justify-between items-center space-x-8">

                        <Skeleton width="w-1/3" :shine="true"></Skeleton>

                        <div class="flex items-center space-x-2 whitespace-nowrap">

                            <Skeleton width="w-20" :shine="true"></Skeleton>
                            <Skeleton width="w-10" :shine="true"></Skeleton>
                            <Skeleton width="w-10" :shine="true"></Skeleton>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <template v-else>

            <div class="grid grid-cols-2 gap-4">

                <!-- Courier Select -->
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

                <!-- Remark Textarea -->
                <div class="col-span-2">

                    <Input
                        rows="2"
                        label="Remark"
                        type="textarea"
                        placeholder="Optional"
                        v-model="orderForm.remark"
                        description="Visible to customer"
                        :errorText="formState.getFormError('remark')"
                        @blur="(remark) => updateOrder({ remark: remark }, 'remark')"
                        tooltipContent="Use the Remark field to add a note that will be visible to the customer, such as special instructions, order details, or personalized messages.">
                    </Input>

                </div>

            </div>

            <div
                class="bg-gray-50 p-4 border border-gray-200 rounded-lg space-y-2 text-sm"
                v-if="order.customer_name || order.customer_email || order.customer_mobile_number || deliveryMethod || deliveryAddress || order.delivery_date">

                <div v-if="order.customer_name" class="grid grid-cols-3 items-center">
                    <span>Name</span>
                    <div class="col-span-2 flex items-center space-x-2">
                        <UserRoundCheck v-if="customer" size="16"></UserRoundCheck>
                        <span :class="{ 'text-blue-500 hover:underline cursor-pointer' : customer }" @click="customer ? navigateToCustomer : null">{{ order.customer_name }}</span>
                        <Button
                            size="xs"
                            type="light"
                            class="hidden lg:block"
                            :rightIcon="ArrowRight"
                            :action="navigateToCustomer">
                            <span>Show Profile</span>
                        </Button>
                    </div>
                </div>

                <div v-if="order.customer_email" class="grid grid-cols-3">
                    <span>Email</span>
                    <span class="col-span-2">
                        <a :href="`mailto:${order.customer_email}`" target="_blank" class="text-blue-500 hover:underline">
                            {{ order.customer_email }}
                        </a>
                    </span>
                </div>

                <div v-if="order.customer_mobile_number" class="grid grid-cols-3">
                    <span>Mobile</span>
                    <span class="col-span-2">
                        <a :href="`tel:${order.customer_mobile_number.international}`" target="_blank" class="text-blue-500 hover:underline">
                            {{ order.customer_mobile_number.international }}
                        </a>
                    </span>
                </div>

                <div v-if="deliveryMethod" class="grid grid-cols-3">
                    <span>Delivery</span>
                    <span class="col-span-2">{{ deliveryMethod.name }}</span>
                </div>

                <div v-if="deliveryAddress" class="grid grid-cols-3">
                    <span>Address</span>
                    <span class="col-span-2">
                        <a :href="googleMapsUrl" target="_blank" class="text-blue-500 hover:underline">
                            {{ deliveryAddress.complete_address }}
                        </a>
                    </span>
                </div>

                <div v-if="order.delivery_date" class="grid grid-cols-3">
                    <span>Schedule</span>
                    <div class="space-x-2">
                        <span>{{ formattedDate(order.delivery_date) }} ({{ formattedShortWeekday(order.delivery_date) }})</span>
                        <span>{{ order.delivery_timeslot }}</span>
                    </div>
                </div>

                <Alert
                    type="primary"
                    :dismissable="true"
                    title="Profile changes"
                    v-if="
                        customer && (
                            customer.name && order.customer_name && customer.name != order.customer_name ||
                            customer.email && order.customer_email && customer.email != order.customer_email ||
                            customer.mobile_number && order.customer_mobile_number && customer.mobile_number.international != order.customer_mobile_number.international
                        )">

                    <template #description>

                        <ul class="list-disc text-xs space-y-2 border-t border-blue-300 border-dashed pt-2 mt-2 pl-2">

                            <li v-if="customer.name && order.customer_name && customer.name != order.customer_name">Order name (<span class="font-bold">{{ order.customer_name }}</span>) is different from customer profile name (<span class="font-bold">{{ customer.name }}</span>).</li>
                            <li v-if="customer.email && order.customer_email && customer.email != order.customer_email">Order email (<span class="font-bold">{{ order.customer_email }}</span>) is different from customer profile email (<span class="font-bold">{{ customer.email }}</span>).</li>
                            <li v-if="customer.mobile_number && order.customer_mobile_number && customer.mobile_number.international != order.customer_mobile_number.international">Order mobile number (<span class="font-bold">{{ order.customer_mobile_number.international }}</span>) is different from customer profile mobile number (<span class="font-bold">{{ customer.mobile_number.international }}</span>).</li>

                        </ul>

                    </template>

                </Alert>

            </div>

            <template v-if="deliveryAddress">

                <div class="rounded-lg overflow-hidden">

                    <GoogleMaps
                        height="350px"
                        :gmpDraggable="false"
                        :latitude="deliveryAddress.latitude"
                        :longitude="deliveryAddress.longitude">
                    </GoogleMaps>

                </div>

                <div v-if="googleMapsUrl" class="flex justify-end mt-4">

                    <Button
                        size="xs"
                        type="light"
                        buttonClass="h-full"
                        :rightIcon="ExternalLink"
                        :action="openGoogleMapsLink">
                        <span>Google Maps</span>
                    </Button>

                </div>

            </template>

        </template>

    </div>

</template>

<script>

    import axios from 'axios';
    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import Select from '@Partials/Select.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import GoogleMaps from '@Partials/GoogleMaps.vue';
    import { capitalize } from '@Utils/stringUtils.js';
    import { ArrowRight, UserRoundCheck, ExternalLink } from 'lucide-vue-next';
    import { formattedDate, formattedShortWeekday } from '@Utils/dateUtils.js';
    import CollectionStatus from '@Pages/orders/order/components/order-header/CollectionStatus.vue';

    export default {
        inject: ['formState', 'storeState', 'orderState', 'notificationState'],
        components: { UserRoundCheck, Alert, Input, Select, Button, Skeleton, GoogleMaps, CollectionStatus },
        data() {
            return {
                ArrowRight,
                couriers: [],
                ExternalLink,
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
            orderForm() {
                return this.orderState.orderForm;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            customer() {
                return this.order.customer;
            },
            deliveryMethod() {
                return this.order.delivery_method;
            },
            deliveryAddress() {
                return this.order.delivery_address;
            },
            googleMapsUrl() {
                if (!this.isLoadingOrder && this.deliveryAddress.latitude && this.deliveryAddress.longitude) {
                    return `https://www.google.com/maps?q=${this.deliveryAddress.latitude},${this.deliveryAddress.longitude}`;
                }
                return null;
            }
        },
        methods: {
            formattedDate,
            formattedShortWeekday,
            openGoogleMapsLink() {
                window.open(this.googleMapsUrl, '_blank');
            },
            navigateToCustomer() {
                this.$router.push({
                    name: 'edit-customer',
                    params: {
                        'customer_id': this.customer.id
                    },
                    query: {
                        store_id: this.store.id
                    }
                });
            },
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

                    if(type == 'courier') {
                        if(this.orderForm.courier_id) {
                            const courier = this.couriers.find(courier => courier.value === this.orderForm.courier_id);
                            this.notificationState.showSuccessNotification(`Courier updated to ${courier.label}`);
                        }else{
                            this.notificationState.showSuccessNotification(`Courier removed`);
                        }
                        this.orderState.order.courier_id = this.orderForm.courier_id;
                    }else if(type == 'tracking number') {
                        if(this.orderForm.tracking_number) {
                            this.notificationState.showSuccessNotification(`Tracking number updated to ${this.orderForm.tracking_number}`);
                        }else{
                            this.notificationState.showSuccessNotification(`Tracking number removed`);
                        }
                        this.orderState.order.tracking_number = this.orderForm.tracking_number;
                    }else if(type == 'remark') {
                        if(this.orderForm.remark) {
                            this.notificationState.showSuccessNotification(`Remark updated`);
                        }else{
                            this.notificationState.showSuccessNotification(`Remark removed`);
                        }
                        this.orderState.order.remark = this.orderForm.remark;
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
