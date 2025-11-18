<template>

    <div>

        <!-- Order Totals (Loading Placeholder) -->
        <div
            v-if="isLoadingStore"
            class="flex items-center space-x-4">

            <div class="flex items-center justify-center w-16 h-16 border border-dashed border-gray-200 rounded-lg shrink-0">

                <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>

            </div>

            <div class="w-full space-y-4">

                <Skeleton width="w-full" :shine="true"></Skeleton>
                <Skeleton width="w-1/3" :shine="true"></Skeleton>

            </div>

        </div>

        <div
            v-else
            class="bg-blue-50 border border-blue-400 rounded-lg p-4">

            <template v-if="shoppingCart">

                <template v-if="!isReady || hasDiscounts || hasFees || hasDeliveryName">

                    <!-- Subtotal -->
                    <div class="flex items-center justify-between text-sm border-b border-gray-300 border-dashed pb-2 mb-2">
                        <span>Subtotal</span>
                        <Skeleton v-if="!isReady" width="w-24" color="bg-blue-200" :shine="true"></Skeleton>
                        <span v-else>{{ shoppingCart.totals.subtotal.amount_with_currency }}</span>
                    </div>

                </template>

                <template v-if="!isReady || hasDiscounts">

                    <div class="border-b border-gray-300 border-dashed pb-2 mb-2">

                        <!-- Discounts -->
                        <div
                            :key="index"
                            class="flex items-center justify-between text-sm text-gray-500"
                            v-for="(discount, index) in (!isReady ? [1] : shoppingCart.totals.discounts)">

                            <Skeleton v-if="!isReady" width="w-24" color="bg-blue-200" :shine="true"></Skeleton>
                            <span v-else>{{ discount.name }}</span>

                            <Skeleton v-if="!isReady" width="w-20" color="bg-blue-200" :shine="true"></Skeleton>
                            <span v-else class="text-red-500">- {{ discount.amount.amount_with_currency }}</span>

                        </div>

                    </div>

                </template>

                <!-- Tax -->
                <div
                    v-if="!isReady || hasTaxAmount"
                    class="border-b border-gray-300 border-dashed pb-2 mb-2">

                    <div class="flex items-center justify-between text-sm mb-1">

                        <Skeleton v-if="!isReady" width="w-16" color="bg-blue-200" :shine="true"></Skeleton>
                        <div v-else class="w-full">Tax ({{ shoppingCart.totals.vat.rate.value_symbol }} {{ shoppingCart.totals.vat.method }})</div>

                        <Skeleton v-if="!isReady" width="w-16" color="bg-blue-200" :shine="true"></Skeleton>
                        <span v-else>{{ shoppingCart.totals.vat.amount.amount_with_currency }}</span>

                    </div>

                    <Skeleton v-if="!isReady" width="w-1/3" color="bg-blue-200" :shine="true"></Skeleton>
                    <p v-else class="text-xs text-gray-400">Tax calculated on the discounted subtotal of {{ shoppingCart.totals.subtotal_after_discount.amount_with_currency }}</p>

                </div>

                <template v-if="!isReady || hasFees">

                    <div class="border-b border-gray-300 border-dashed pb-2 mb-2">

                        <!-- Fees -->
                        <div
                            :key="index"
                            class="flex items-center justify-between text-sm text-gray-500"
                            v-for="(fee, index) in ( !isReady ? [1] : shoppingCart.totals.fees)">

                            <Skeleton v-if="!isReady" width="w-20" color="bg-blue-200" :shine="true"></Skeleton>
                            <span v-else>{{ fee.name }}</span>

                            <Skeleton v-if="!isReady" width="w-16" color="bg-blue-200" :shine="true"></Skeleton>
                            <span v-else class="text-green-500">+ {{ fee.amount.amount_with_currency }}</span>

                        </div>

                    </div>

                </template>

                <template v-if="!isReady || hasAdjustmentTotal">

                    <div class="border-b border-gray-300 border-dashed pb-2 mb-2">

                        <!-- Adjustment -->
                        <div class="flex items-center justify-between text-sm text-gray-500">

                            <Skeleton v-if="!isReady" width="w-20" color="bg-blue-200" :shine="true"></Skeleton>
                            <span v-else>Adjustment</span>

                            <Skeleton v-if="!isReady" width="w-16" color="bg-blue-200" :shine="true"></Skeleton>
                            <span v-else :class="[adjustmentTotalPositive ? 'text-green-500' : 'text-red-500']">
                                {{ adjustmentTotalPositive ? '+' : '-' }} {{ `${shoppingCart.totals.adjustment_total.amount_with_currency}`.replace('-', '') }}
                            </span>

                        </div>

                    </div>

                </template>

                <template v-if="!isReady || hasDeliveryName">

                    <div class="border-b border-dashed border-gray-300 pb-2 mb-2">

                        <!-- Delivery Amount -->
                        <div class="flex items-center justify-between text-sm text-gray-500">

                            <Skeleton v-if="!isReady" width="w-20" color="bg-blue-200" :shine="true"></Skeleton>
                            <span v-else>{{ shoppingCart.totals.delivery.name }}</span>

                            <Skeleton v-if="!isReady" width="w-16" color="bg-blue-200" :shine="true"></Skeleton>
                            <span v-else>{{ shoppingCart.totals.delivery.fee.amount_with_currency }}</span>

                        </div>

                    </div>

                </template>

                <!-- Grand Total -->
                <div class="flex items-center justify-between text-lg">

                    <div class="w-full font-bold">Grand Total</div>

                    <Skeleton v-if="!isReady" width="w-24" color="bg-blue-200" :shine="true"></Skeleton>
                    <span v-else class="font-bold">{{ shoppingCart.totals.grand_total.amount_with_currency }}</span>

                </div>

            </template>

            <template v-else>
                <span class="text-sm">Add products to show total</span>
            </template>

        </div>

    </div>

</template>

<script>

    import Skeleton from '@Partials/Skeleton.vue';

    export default {
        inject: ['orderState', 'storeState'],
        components: { Skeleton },
        computed: {
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isInspectingShoppingCart() {
                return this.orderState.isInspectingShoppingCart;
            },
            isReady() {
                return !this.isLoadingStore && !this.isInspectingShoppingCart;
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            hasDeliveryName() {
                return this.shoppingCart.totals.delivery.name != null;
            },
            hasFees() {
                return Object.keys(this.shoppingCart.totals.fees).length > 0;
            },
            hasDiscounts() {
                return Object.keys(this.shoppingCart.totals.discounts).length > 0;
            },
            hasTaxAmount() {
                return this.shoppingCart.totals.vat.amount.amount > 0
            },
            hasAdjustmentTotal() {
                return this.shoppingCart.totals.adjustment_total.amount != 0;
            },
            adjustmentTotalPositive() {
                return this.shoppingCart.totals.adjustment_total.amount > 0;
            },
        }
    };

</script>
