<template>

        <div class="bg-white rounded-lg p-4 shadow-sm">

        <h1 class="text-lg font-semibold mb-4">Customer</h1>

        <!-- Order Customer (Loading Placeholder) -->
        <div
            v-if="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)"
            class="flex items-center space-x-4 border-b border-gray-300 shadow-sm rounded-lg py-6 px-4 bg-gray-50">

            <div class="flex items-center justify-center w-16 h-16 border border-dashed border-gray-200 rounded-lg flex-shrink-0">

                <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>

            </div>

            <div class="w-full space-y-4">

                <Skeleton width="w-full" :shine="true"></Skeleton>
                <Skeleton width="w-1/3" :shine="true"></Skeleton>

            </div>

        </div>

        <template v-else>

            <template v-if="hasCustomerDetails || modified">

                <div class="space-y-2">

                    <!-- First Name Input -->
                    <Input
                        type="text"
                        placeholder="First name"
                        v-model="orderForm.customer_first_name"
                        :skeleton="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)"
                        @input="orderState.saveStateDebounced('Customer first name changed')">
                    </Input>

                    <!-- Last Name Input -->
                    <Input
                        type="text"
                        placeholder="Last name"
                        v-model="orderForm.customer_last_name"
                        :skeleton="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)"
                        @input="orderState.saveStateDebounced('Customer last name changed')">
                    </Input>

                    <!-- Email Input -->
                    <Input
                        type="email"
                        placeholder="Email"
                        v-model="orderForm.customer_email"
                        @input="orderState.saveStateDebounced('Customer email changed')"
                        :skeleton="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)">
                    </Input>

                    <!-- Mobile Number Input -->
                    <Input
                        type="text"
                        placeholder="+26772000001"
                        v-model="orderForm.customer_mobile_number"
                        v-if="!(isLoadingStore || isLoadingOrder || (isEditting && !hasOrder))"
                        @change="orderState.saveStateDebounced('Customer mobile number changed')">
                    </Input>

                </div>

            </template>

            <div
                v-if="hasEmail || hasMobileNumber"
                class="border-t border-dashed border-gray-300 pt-4 mt-4">
                <Input
                    type="checkbox"
                    inputLabel="Update customer profile"
                    v-model="orderForm.update_profile"
                    inputDescription="Make sure to update both the order customer details and the customer profile"
                    @change="orderState.saveStateDebounced(`Update customer profile (${ orderForm.update_profile ? 'On' : 'Off' })`)">
                </Input>
            </div>

            <div
                :class="[hasCustomerDetails || modified ? 'border-t border-dashed border-gray-300 pt-4 mt-4' : 'flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg space-y-4']">

                <template v-if="!hasCustomerDetails && !modified">
                    <svg class="w-10 h-10 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <span class="text-sm text-gray-500">No customer added</span>
                </template>

                <AddCustomer :hasCustomerDetails="hasCustomerDetails"></AddCustomer>

            </div>

        </template>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { isNotEmpty } from '@Utils/stringUtils';
    import AddCustomer from '@Pages/orders/order/editable/components/order-customer/add-customer/AddCustomer.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Input, Skeleton, AddCustomer },
        data() {
            return {
                modified: false,
                customer_email: null,
                customer_last_name: null,
                customer_first_name: null,
                customer_mobile_number: null,
            }
        },
        watch: {
            orderForm: {
                handler(newValue, oldValue) {
                    if(oldValue == null) {
                        this.customer_email = newValue.customer_email;
                        this.customer_last_name = newValue.customer_last_name;
                        this.customer_first_name = newValue.customer_first_name;
                        this.customer_mobile_number = newValue.customer_mobile_number;
                    }else{
                        this.modified =
                            newValue.customer_email !== this.customer_email ||
                            newValue.customer_last_name !== this.customer_last_name ||
                            newValue.customer_first_name !== this.customer_first_name ||
                            newValue.customer_mobile_number !== this.customer_mobile_number;
                    }
                },
                deep: true
            }
        },
        computed: {
            hasOrder() {
                return this.orderState.hasOrder;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            isEditting() {
                return this.$route.name === 'edit-order';
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            hasEmail() {
                return this.isNotEmpty(this.orderForm.customer_email);
            },
            hasMobileNumber() {
                return this.isNotEmpty(this.orderForm.customer_mobile_number);
            },
            hasCustomerDetails() {
                return this.orderState.hasCustomerDetails
            },
        },
        methods: {
            isNotEmpty: isNotEmpty
        }
    };

</script>
