<template>

    <div class="bg-blue-50 border border-blue-400 rounded-lg p-4">

        <template v-if="!isReady || hasDiscounts || hasFees">

            <!-- Subtotal -->
            <div class="flex items-center justify-between text-sm border-b border-dashed pb-2 mb-2">
                <span>Subtotal</span>
                <LineSkeleton v-if="!isReady" width="w-24" color="bg-blue-200" :shine="true"></LineSkeleton>
                <span v-else>{{ shoppingCart.totals.subtotal.amount_with_currency }}</span>
            </div>

        </template>

        <template v-if="!isReady || hasDiscounts">

            <div class="border-b border-dashed pb-2 mb-2">

                <!-- Discounts -->
                <div
                    :key="index"
                    class="flex items-center justify-between text-sm text-gray-500"
                    v-for="(discount, index) in (!isReady ? [1] : shoppingCart.totals.discounts)">

                    <LineSkeleton v-if="!isReady" width="w-24" color="bg-blue-200" :shine="true"></LineSkeleton>
                    <span v-else>{{ discount.name }}</span>

                    <LineSkeleton v-if="!isReady" width="w-20" color="bg-blue-200" :shine="true"></LineSkeleton>
                    <span v-else>{{ discount.amount.amount_with_currency }}</span>

                </div>

            </div>

        </template>

        <!-- Tax -->
         <div class="border-b border-dashed pb-2 mb-2">

            <div class="flex items-center justify-between text-sm mb-1">

                <LineSkeleton v-if="!isReady" width="w-16" color="bg-blue-200" :shine="true"></LineSkeleton>
                <div v-else class="w-full">Tax ({{ shoppingCart.totals.vat.rate.valueSymbol }} {{ shoppingCart.totals.vat.method }})</div>

                <LineSkeleton v-if="!isReady" width="w-16" color="bg-blue-200" :shine="true"></LineSkeleton>
                <span v-else>{{ shoppingCart.totals.vat.amount.amount_with_currency }}</span>

            </div>

            <LineSkeleton v-if="!isReady" width="w-1/3" color="bg-blue-200" :shine="true"></LineSkeleton>
            <p v-else class="text-xs text-gray-400">Tax calculated on the discounted subtotal of {{ shoppingCart.totals.subtotalAfterDiscount.amount_with_currency }}</p>

        </div>

        <template v-if="!isReady || hasFees">

            <div class="border-b border-dashed pb-2 mb-2">

                <!-- Fees -->
                <div
                    :key="index"
                    class="flex items-center justify-between text-sm text-gray-500"
                    v-for="(fee, index) in ( !isReady ? [1] : shoppingCart.totals.fees)">

                    <LineSkeleton v-if="!isReady" width="w-20" color="bg-blue-200" :shine="true"></LineSkeleton>
                    <span v-else>{{ fee.name }}</span>

                    <LineSkeleton v-if="!isReady" width="w-16" color="bg-blue-200" :shine="true"></LineSkeleton>
                    <span v-else>{{ fee.amount.amount_with_currency }}</span>

                </div>

            </div>

        </template>

        <template v-if="!isReady || hasAdjustmentTotal">

            <div class="border-b border-dashed pb-2 mb-2">

                <!-- Adjustment -->
                <div class="flex items-center justify-between text-sm text-gray-500">

                    <LineSkeleton v-if="!isReady" width="w-20" color="bg-blue-200" :shine="true"></LineSkeleton>
                    <span v-else>Adjustment</span>

                    <LineSkeleton v-if="!isReady" width="w-16" color="bg-blue-200" :shine="true"></LineSkeleton>
                    <span v-else>{{ shoppingCart.totals.adjustmentTotal.amount_with_currency }}</span>

                </div>

            </div>

        </template>

        <!-- Grand Total -->
        <div class="flex items-center justify-between text-base pt-2">

            <div class="w-full font-bold">Grand Total</div>

            <LineSkeleton v-if="!isReady" width="w-24" color="bg-blue-200" :shine="true"></LineSkeleton>
            <span v-else class="font-bold">{{ shoppingCart.totals.grandTotal.amount_with_currency }}</span>

        </div>

    </div>

</template>

<script>

    import LineSkeleton from '@Partials/skeletons/LineSkeleton.vue';

    export default {
        inject: ['orderState', 'storeState'],
        components: { LineSkeleton },
        computed: {
            order() {
                return this.orderState.order;
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
            isInspectingShoppingCart() {
                return this.orderState.isInspectingShoppingCart;
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            hasFees() {
                return this.shoppingCart && this.shoppingCart.totals.fees &&
                            Object.keys(this.shoppingCart.totals.fees).length > 0;
            },
            hasDiscounts() {
                return this.shoppingCart && this.shoppingCart.totals.discounts &&
                            Object.keys(this.shoppingCart.totals.discounts).length > 0;
            },
            hasAdjustmentTotal() {
                return this.shoppingCart && this.shoppingCart.totals.adjustmentTotal.amount != 0;
            },
            isReady() {
                return !(this.isLoadingStore || this.isLoadingOrder || (this.isEditting && !this.hasOrder) || this.isInspectingShoppingCart || !this.shoppingCart);
            },
        }
    };

</script>
