<template>

    <div class="bg-white rounded-lg p-4 shadow-sm">

        <h1 class="text-lg font-semibold mb-4">Fees</h1>

        <!-- Order Fees (Loading Placeholder) -->
        <div
            v-if="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)"
            class="flex items-center space-x-4 border-b border-gray-300 shadow-sm rounded-lg py-6 px-4 bg-gray-50">

            <div class="flex items-center justify-center w-16 h-16 border border-dashed border-gray-200 rounded-lg flex-shrink-0">

                <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>

            </div>

            <div class="w-full space-y-4">

                <Skeleton width="w-full" :shine="true"></Skeleton>
                <Skeleton width="w-1/3" :shine="true"></Skeleton>

            </div>

        </div>

        <div v-else class="space-y-2">

            <template v-if="hasCartFees">

                <OrderFee
                    :key="index"
                    :index="index"
                    :cartFee="cartFee"
                    v-for="(cartFee, index) in cartFees">
                </OrderFee>

                <div class="flex justify-center pt-2">

                    <!-- Add Fee Button -->
                    <Button
                        size="sm"
                        type="light"
                        :action="addNew"
                        :leftIcon="Plus">
                        <span>Add Fee</span>
                    </Button>

                </div>

            </template>

            <div
                v-else
                class="flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg space-y-4 my-4">

                <template v-if="!hasCartFees">
                    <svg class="w-10 h-10 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span class="text-sm text-gray-500">No fees</span>
                </template>

                <!-- Add Fee Button -->
                <Button
                    size="xs"
                    type="light"
                    :action="addNew"
                    :leftIcon="Plus"
                    buttonClass="my-2">
                    <span>Add Fee</span>
                </Button>

            </div>

        </div>

    </div>

</template>

<script>

    import { Plus } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import OrderFee from '@Pages/orders/order/editable/components/order-fees/OrderFee.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Button, Skeleton, OrderFee },
        data() {
            return {
                Plus
            }
        },
        computed: {
            store() {
                return this.storeState.store;
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
            cartFees() {
                return this.orderState.orderForm.cart_fees;
            },
            isEditting() {
                return this.$route.name === 'edit-order';
            },
            hasCartFees() {
                return this.cartFees.length > 0;
            }
        },
        methods: {
            addNew() {
                this.orderState.addCartFee();
            }
        }
    };

</script>
