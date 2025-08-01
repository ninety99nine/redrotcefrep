<template>

    <div
        @click.stop="selectDeliveryMethod"
        class="cursor-pointer p-4 bg-gray-50 border border-gray-300 rounded-lg transition-colors group">

        <div class="flex items-center space-x-2">

            <transition name="fade-1" mode="out-in">
                <svg v-if="isSelectedDeliveryMethod" class="w-6 h-6 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                </svg>
                <div v-else class="w-6 h-6 flex justify-center items-center">
                    <div class="w-4 h-4 bg-gray-100 border border-gray-300 rounded-full"></div>
                </div>
            </transition>

            <div class="w-full">

                <!-- Name -->
                <p
                    class="font-bold text-sm transition-colors">
                    {{ deliveryMethod.name }}
                </p>

                <!-- Desctiption -->
                <p
                    class="text-sm transition-colors">
                    {{ deliveryMethod.description }}
                </p>

                <template v-if="deliveryMethod.charge_fee && (shoppingCartDeliveryMethodOffersFreeDelivery || shoppingCartDeliveryMethodAmount)">

                    <!-- Free Delivery -->
                    <p
                        class="text-sm transition-colors text-green-500 font-bold"
                        v-if="shoppingCartDeliveryMethodOffersFreeDelivery">
                        Free delivery
                    </p>

                    <!-- Flat Fee Rate -->
                    <p
                        v-else-if="usesFlatFee"
                        class="text-sm transition-colors">
                        Flat fee of {{ shoppingCartDeliveryMethodAmount.amount_with_currency }}
                    </p>

                    <!-- Percentage Fee Rate -->
                    <p
                        v-else-if="usesPercentageFee"
                        class="text-sm transition-colors">
                        Percentage fee of {{ shoppingCartDeliveryMethodAmount.amount_with_currency }} ({{ deliveryMethod.percentage_fee_rate }}%)
                    </p>

                    <!-- Fee by weight -->
                    <p
                        v-else-if="usesFeeByWeight"
                        class="text-sm transition-colors">
                        Weight fee of {{ shoppingCartDeliveryMethodAmount.amount_with_currency }} ({{ shoppingCartDeliveryMethodOption.weight.text }})
                    </p>

                    <!-- Fee by distance -->
                    <p
                        v-else-if="usesFeeByDistance"
                        class="text-sm transition-colors">
                        Distance fee of {{ shoppingCartDeliveryMethodAmount.amount_with_currency }} ({{ shoppingCartDeliveryMethodOption.distance.text }})
                    </p>

                    <!-- Fee by postal code -->
                    <p
                        v-else-if="usesFeeByPostalCode"
                        class="text-sm transition-colors">
                        Distance fee of {{ shoppingCartDeliveryMethodAmount.amount_with_currency }} ({{ shoppingCartDeliveryMethodOption.distance.text }})
                    </p>

                </template>

                <div
                    class="bg-red-50 mt-2 p-4"
                    v-if="hasShoppingCartDeliveryMethodUnavailabilityReasons">

                    <!-- Unavailability Reasons -->
                    <div
                        :key="index"
                        v-for="(shoppingCartDeliveryMethodUnavailabilityReason, index) in shoppingCartDeliveryMethodUnavailabilityReasons">

                        <Skeleton v-if="isInspectingShoppingCart" width="w-1/3" :shine="true"></Skeleton>
                        <div v-else class="flex items-center space-x-1 md:space-x-2">
                            <Info size="16" :class="['flex-shrink-0', isSelectedDeliveryMethod ? 'text-red-500' : 'text-blue-500']"></Info>
                            <span :class="['font-medium italic text-xs md:text-sm', isSelectedDeliveryMethod ? 'text-red-500' : 'text-blue-500']">{{ shoppingCartDeliveryMethodUnavailabilityReason }}</span>
                        </div>

                    </div>

                </div>

                <div
                    class="bg-blue-50 mt-2 p-4"
                    v-if="hasShoppingCartDeliveryMethodTips && !hasShoppingCartDeliveryMethodUnavailabilityReasons">

                    <!-- Tips -->
                    <div
                        :key="index"
                        v-for="(shoppingCartDeliveryMethodTip, index) in shoppingCartDeliveryMethodTips">

                        <Skeleton v-if="isInspectingShoppingCart" width="w-1/3" :shine="true"></Skeleton>
                        <div v-else class="flex items-center space-x-1 md:space-x-2">
                            <Info size="16" class="flex-shrink-0 text-blue-500"></Info>
                            <span class="font-medium italic text-xs md:text-sm text-blue-500 ">{{ shoppingCartDeliveryMethodTip }}</span>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <template v-if="isSelectedDeliveryMethod">

            <OrderDeliveryMethodSchedule :deliveryMethod="deliveryMethod"></OrderDeliveryMethodSchedule>

            <OrderDeliveryMethodMap></OrderDeliveryMethodMap>

        </template>

    </div>

</template>

<script>

    import Skeleton from '@Partials/Skeleton.vue';
    import { Info, CircleAlert } from 'lucide-vue-next';
    import OrderDeliveryMethodMap from '@Pages/orders/order/editable/components/order-delivery-methods/OrderDeliveryMethodMap.vue';
    import OrderDeliveryMethodSchedule from '@Pages/orders/order/editable/components/order-delivery-methods/OrderDeliveryMethodSchedule.vue';

    export default {
        inject: ['orderState'],
        components: { Info, CircleAlert, Skeleton, OrderDeliveryMethodMap, OrderDeliveryMethodSchedule },
        props: {
            index: {
                type: Number
            },
            deliveryMethod: {
                type: Object
            }
        },
        computed: {
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            isInspectingShoppingCart() {
                return this.orderState.isInspectingShoppingCart;
            },
            usesFlatFee() {
                return this.deliveryMethod.fee_type == 'flat fee';
            },
            usesPercentageFee() {
                return this.deliveryMethod.fee_type == 'percentage fee';
            },
            usesFeeByWeight() {
                return this.deliveryMethod.fee_type == 'fee by weight';
            },
            usesFeeByDistance() {
                return this.deliveryMethod.fee_type == 'fee by distance';
            },
            usesFeeByPostalCode() {
                return this.deliveryMethod.fee_type == 'fee by postal code';
            },
            isSelectedDeliveryMethod() {
                return this.orderForm.delivery_method_id === this.deliveryMethod.id;
            },
            shoppingCartDeliveryMethodOption() {
                return ((this.shoppingCart || {}).delivery_method_options || []).find((shoppingCartDeliveryMethodOption) => shoppingCartDeliveryMethodOption.id == this.deliveryMethod.id);
            },
            shoppingCartDeliveryMethodAmount() {
                return (this.shoppingCartDeliveryMethodOption || {}).amount;
            },
            shoppingCartDeliveryMethodOffersFreeDelivery() {
                return (this.shoppingCartDeliveryMethodOption || {}).free_delivery;
            },
            shoppingCartDeliveryMethodTips() {
                return ((this.shoppingCartDeliveryMethodOption || {}).tips || []);
            },
            hasShoppingCartDeliveryMethodTips() {
                return this.shoppingCartDeliveryMethodTips.length > 0;
            },
            shoppingCartDeliveryMethodUnavailabilityReasons() {
                return ((this.shoppingCartDeliveryMethodOption || {}).unavailability_reasons || []);
            },
            hasShoppingCartDeliveryMethodUnavailabilityReasons() {
                return this.shoppingCartDeliveryMethodUnavailabilityReasons.length > 0;
            }
        },
        methods: {
            selectDeliveryMethod() {
                this.orderForm.delivery_method_id = this.deliveryMethod.id;
                this.orderState.saveStateDebounced('Delivery method changed');
            }
        }
    };

</script>
