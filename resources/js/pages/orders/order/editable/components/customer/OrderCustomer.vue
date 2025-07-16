<template>

    <div>

        <h1 class="text-lg text-gray-700 font-semibold mb-4">Customer</h1>

        <!-- Order Customer (Loading Placeholder) -->
        <div
            v-if="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)"
            class="flex items-center space-x-4 border-b shadow-sm rounded-lg py-6 px-4 bg-gray-50">

            <div class="flex items-center justify-center w-16 h-16 border border-dashed border-gray-200 rounded-lg flex-shrink-0">

                <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>

            </div>

            <div class="w-full space-y-4">

                <LineSkeleton width="w-full" :shine="true"></LineSkeleton>
                <LineSkeleton width="w-1/3" :shine="true"></LineSkeleton>

            </div>

        </div>

        <template v-else>

            <template v-if="hasCustomerDetails || modified">

                <div class="space-y-2">

                    <!-- First Name Input -->
                    <CustomInput
                        type="text"
                        placeholder="First name"
                        v-model="orderForm.customer.firstName"
                        @input="orderState.saveStateDebounced('Customer first name changed')"
                        :skeleton="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)">
                    </CustomInput>

                    <!-- Last Name Input -->
                    <CustomInput
                        type="text"
                        placeholder="Last name"
                        v-model="orderForm.customer.lastName"
                        @input="orderState.saveStateDebounced('Customer last name changed')"
                        :skeleton="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)">
                    </CustomInput>

                    <!-- Email Input -->
                    <CustomInput
                        type="email"
                        placeholder="Email"
                        v-model="orderForm.customer.email"
                        @input="orderState.saveStateDebounced('Customer email changed')"
                        :skeleton="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)">
                    </CustomInput>

                    <!-- Mobile Number Input -->
                    <CustomInput
                        type="mobile"
                        v-model="orderForm.customer.mobileNumber"
                        v-if="!(isLoadingStore || isLoadingOrder || (isEditting && !hasOrder))"
                        @change="orderState.saveStateDebounced('Customer mobile number changed')">
                    </CustomInput>

                </div>

            </template>

            <div
                v-if="hasEmail || hasMobileNumber"
                class="border-t border-dashed pt-4 mt-4">
                <CustomInput
                    type="checkbox"
                    inputLabel="Update customer profile"
                    v-model="orderForm.customer.updateProfile"
                    inputDescription="Make sure to update both the order customer details and the customer profile"
                    @change="orderState.saveStateDebounced(`Update customer profile (${ orderForm.customer.updateProfile ? 'On' : 'Off' })`)">
                </CustomInput>
            </div>

            <div
                :class="[hasCustomerDetails || modified ? 'border-t border-dashed pt-4 mt-4' : 'flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg space-y-4']">

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

    import CustomInput from '@Partials/inputs/CustomInput.vue';
    import LineSkeleton from '@Partials/skeletons/LineSkeleton.vue';
    import AddCustomer from '@Pages/stores/store/orders/order/editable/components/customer/add-customer/AddCustomer.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { CustomInput, LineSkeleton, AddCustomer },
        data() {
            return {
                modified: false
            }
        },
        watch: {
            hasCustomerDetails() {
                this.modified = true;
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
            hasEmail() {
                return this.orderState.hasEmail;
            },
            hasMobileNumber() {
                return this.orderState.hasMobileNumber;
            },
            hasCustomerDetails() {
                return this.orderState.hasCustomerDetails;
            }
        }
    };

</script>
