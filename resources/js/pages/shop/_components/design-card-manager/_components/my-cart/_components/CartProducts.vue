<template>

    <div v-if="isLoadingStore" class="space-y-2">

        <div v-for="(_, index) in [1, 2, 3]" :key="index" class="flex items-center space-x-4 border-b border-gray-300 shadow-sm rounded-lg py-6 px-4 bg-gray-50">

            <div class="flex items-center justify-center w-16 h-16 border border-dashed border-gray-200 rounded-lg flex-shrink-0">

                <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>

            </div>

            <div class="w-full space-y-4">

                <Skeleton width="w-full" :shine="true"></Skeleton>
                <Skeleton width="w-1/3" :shine="true"></Skeleton>

            </div>

        </div>

    </div>

    <div v-else class="space-y-4">

        <div v-if="hasCartProducts">

            <CartProduct
                :key="index"
                :index="index"
                :cartProduct="cartProduct"
                v-for="(cartProduct, index) in cartProducts">
            </CartProduct>

        </div>

        <div
            :class="['relative', { 'flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg space-y-4 my-4' : !hasCartProducts }]">

            <template v-if="!hasCartProducts">
                <svg class="w-10 h-10 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
                <span class="text-sm text-gray-500">No products added</span>
            </template>

        </div>

    </div>

</template>

<script>

    import Skeleton from '@Partials/Skeleton.vue';
    import CartProduct from '@Pages/shop/_components/design-card-manager/_components/my-cart/_components/CartProduct.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Skeleton, CartProduct },
        computed: {
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            cartProducts() {
                return this.orderState.orderForm.cart_products;
            },
            hasCartProducts() {
                return this.cartProducts.length > 0;
            }
        }
    };

</script>
