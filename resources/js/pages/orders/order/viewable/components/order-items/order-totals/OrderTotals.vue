<template>

    <div class="bg-blue-50 border border-blue-400 rounded-lg p-4">

        <template v-if="isLoadingOrder || !hasOrder || hasTaxTotal || hasDiscountTotal || hasFeeTotal || hasDeliveryName">

            <!-- Subtotal -->
            <div class="flex items-center justify-between text-sm border-b border-dashed border-gray-300 pb-2 mb-2">
                <span>Subtotal</span>
                <Skeleton v-if="isLoadingOrder || !hasOrder" width="w-24" color="bg-blue-200" :shine="true"></Skeleton>
                <span v-else>{{ order.subtotal.amount_with_currency }}</span>
            </div>

        </template>

        <template v-if="isLoadingOrder || !hasOrder || hasDiscountTotal">

            <div class="border-b border-dashed border-gray-300 pb-2 mb-2">

                <!-- Discounts -->
                <div
                    :key="index"
                    class="flex items-center justify-between text-sm text-gray-500"
                    v-for="(discount, index) in (isLoadingOrder || !hasOrder ? [1] : orderDiscounts)">

                    <Skeleton v-if="isLoadingOrder || !hasOrder" width="w-24" color="bg-blue-200" :shine="true"></Skeleton>
                    <span v-else>{{ discount.name }}</span>

                    <Skeleton v-if="isLoadingOrder || !hasOrder" width="w-20" color="bg-blue-200" :shine="true"></Skeleton>
                    <span v-else>{{ discount.amount.amount_with_currency }}</span>

                </div>

            </div>

        </template>

        <!-- Tax -->
         <div
            v-if="isLoadingOrder || !hasOrder || hasTaxTotal"
            class="border-b border-dashed border-gray-300 pb-2 mb-2">

            <div class="flex items-center justify-between text-sm mb-1">

                <Skeleton v-if="isLoadingOrder || !hasOrder" width="w-16" color="bg-blue-200" :shine="true"></Skeleton>
                <div v-else class="w-full">Tax ({{ removeDecimalTrailingZeros(order.vat_rate) }}% {{ order.vat_method }})</div>

                <Skeleton v-if="isLoadingOrder || !hasOrder" width="w-16" color="bg-blue-200" :shine="true"></Skeleton>
                <span v-else>{{ order.vat_amount.amount_with_currency }}</span>

            </div>

            <Skeleton v-if="isLoadingOrder || !hasOrder" width="w-1/3" color="bg-blue-200" :shine="true"></Skeleton>
            <p v-else-if="order.vat_amount.amount > 0" class="text-xs text-gray-400">Tax calculated on the discounted subtotal of {{ order.subtotal_after_discount.amount_with_currency }}</p>

        </div>

        <template v-if="isLoadingOrder || !hasOrder || hasFeeTotal">

            <div class="border-b border-dashed border-gray-300 pb-2 mb-2">

                <!-- Fees -->
                <div
                    :key="index"
                    class="flex items-center justify-between text-sm text-gray-500"
                    v-for="(orderFee, index) in ( isLoadingOrder || !hasOrder ? [1] : orderFees)">

                    <Skeleton v-if="isLoadingOrder || !hasOrder" width="w-20" color="bg-blue-200" :shine="true"></Skeleton>
                    <span v-else>{{ orderFee.name }}</span>

                    <Skeleton v-if="isLoadingOrder || !hasOrder" width="w-16" color="bg-blue-200" :shine="true"></Skeleton>
                    <span v-else>{{ orderFee.amount.amount_with_currency }}</span>

                </div>

            </div>

        </template>

        <template v-if="isLoadingOrder || !hasOrder || hasDeliveryName">

            <div class="border-b border-dashed border-gray-300 pb-2 mb-2">

                <!-- Delivery Amount -->
                <div class="flex items-center justify-between text-sm text-gray-500">

                    <Skeleton v-if="isLoadingOrder || !hasOrder" width="w-20" color="bg-blue-200" :shine="true"></Skeleton>
                    <span v-else>{{ order.delivery_name }}</span>

                    <Skeleton v-if="isLoadingOrder || !hasOrder" width="w-16" color="bg-blue-200" :shine="true"></Skeleton>
                    <span v-else>{{ order.delivery_fee.amount_with_currency }}</span>

                </div>

            </div>

        </template>

        <!-- Grand Total -->
        <div class="flex items-center justify-between text-base pt-2">

            <div class="w-full font-bold">Grand Total</div>

            <Skeleton v-if="isLoadingOrder || !hasOrder" width="w-24" color="bg-blue-200" :shine="true"></Skeleton>
            <span v-else class="font-bold">{{ order.grand_total.amount_with_currency }}</span>

        </div>

    </div>

</template>

<script>

    import Skeleton from '@Partials/Skeleton.vue';
    import { removeDecimalTrailingZeros } from '@Utils/numberUtils.js';

    export default {
        inject: ['orderState', 'storeState'],
        components: { Skeleton },
        computed: {
            order() {
                return this.orderState.order;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            hasTaxTotal() {
                return this.isLoadingOrder ? false : this.order.vat_amount.amount > 0;
            },
            hasFeeTotal() {
                return this.isLoadingOrder ? false : this.order.fee_total.amount > 0;
            },
            deliveryFee() {
                return this.isLoadingOrder ? this.order.delivery_fee.amount : null;
            },
            hasDeliveryName() {
                return this.isLoadingOrder ? false : this.order.delivery_name != null;
            },
            hasDiscountTotal() {
                return this.isLoadingOrder ? false : this.order.discount_total.amount > 0;
            },
            orderFees() {
                return this.isLoadingOrder ? [] : this.order.order_fees;
            },
            orderDiscounts() {
                return this.isLoadingOrder ? [] : this.order.order_discounts;
            },
        },
        methods: {
            removeDecimalTrailingZeros
        }
    };

</script>
