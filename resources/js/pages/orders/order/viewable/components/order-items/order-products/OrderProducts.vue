<template>

    <!-- Order Products (Loading Placeholder) -->
    <div v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="space-y-2">

        <div v-for="(_, index) in [1, 2]" :key="index" class="flex items-center space-x-4 border-b border-gray-300 shadow-sm rounded-lg p-2 bg-gray-50">

            <div class="flex items-center justify-center w-10 h-10 border border-dashed border-gray-200 rounded-lg flex-shrink-0">

                <Image size="20" class="text-gray-400"></Image>

            </div>

            <div class="w-full flex justify-between">

                <Skeleton width="w-1/3" :shine="true"></Skeleton>

                <div class="flex items-center space-x-2">
                    <Skeleton width="w-16" :shine="true"></Skeleton>
                    <Skeleton width="w-4" :shine="true"></Skeleton>
                </div>

            </div>

        </div>

    </div>

    <template v-else>

        <OrderProduct
            :key="index"
            :orderProduct="orderProduct"
            v-for="(orderProduct, index) in orderProducts">
        </OrderProduct>

    </template>

</template>

<script>

    import { Image } from 'lucide-vue-next';
    import Skeleton from '@Partials/Skeleton.vue';
    import OrderProduct from '@Pages/orders/order/viewable/components/order-items/order-products/OrderProduct.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Image, Skeleton, OrderProduct },
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
            orderProducts() {
                return this.order.order_products;
            }
        }
    };

</script>
