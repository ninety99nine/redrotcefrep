<template>

    <div class="bg-white rounded-lg p-4 shadow-sm">

        <h1 class="text-lg text-gray-700 font-semibold mb-4">Other</h1>

        <!-- Order Miscellaneous (Loading Placeholder) -->
        <div
            v-if="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)"
            class="flex items-center space-x-4 border-b border-gray-300 shadow-sm rounded-lg py-6 px-4 bg-gray-50">

            <div class="flex items-center justify-center w-16 h-16 border border-dashed border-gray-200 rounded-lg flex-shrink-0">

                <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>

            </div>

            <div class="w-full space-y-4">

                <Skeleton width="w-full" :shine="true"></Skeleton>
                <Skeleton width="w-1/3" :shine="true"></Skeleton>

            </div>

        </div>

        <div v-else class="space-y-2">

            <!-- Remark Input -->
            <Input
                rows="2"
                label="Remark"
                type="textarea"
                placeholder="Optional"
                v-model="orderForm.remark"
                description="Visible to customers"
                :errorText="formState.getFormError('remark')"
                @change="orderState.saveStateDebounced('Remark changed')"
                :skeleton="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)"
                tooltipContent="Use the Remark field to add a note that will be visible to the customer, such as special instructions, order details, or personalized messages.">
            </Input>

            <!-- Adjustment Input -->
            <Input
                type="money"
                label="Adjustment"
                :allowNegativeAmounts="true"
                :currency="store.currency.code"
                v-model="orderForm.adjustment"
                :errorText="formState.getFormError('adjustment')"
                @change="orderState.saveStateDebounced('Adjustment changed')"
                :skeleton="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)"
                description="Modify the order total by adding or subtracting an arbitrary amount"
                tooltipContent="Use the Adjustment field to manually add or subtract an amount for discounts, extra charges, or price corrections.">
            </Input>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Skeleton from '@Partials/Skeleton.vue';

    export default {
        inject: ['formState', 'storeState', 'orderState'],
        components: { Input, Skeleton },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isEditting() {
                return this.$route.name === 'edit-order';
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            }
        }
    };

</script>
