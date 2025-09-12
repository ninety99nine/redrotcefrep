<template>

    <div :class="{ 'border-t border-gray-100 pt-4 mt-4' : index > 0}">

        <div class="w-full flex items-center space-x-4">

            <div :class="['flex items-center justify-center w-10 h-10 rounded-lg', { 'border border-dashed border-gray-200' : !hasPhotoPath }]">

                <img v-if="hasPhotoPath" class="w-10 object-contain rounded-lg flex-shrink-0" :src="photoPath">

                <Image v-else size="16" class="text-gray-300"></Image>

            </div>

            <div class="w-full flex items-start">

                <div class="w-full">

                    <p class="text-sm">{{ cartProduct.name }}</p>

                    <Skeleton v-if="isInspectingShoppingCart" width="w-24" height="h-4" :shine="true" class="mt-2"></Skeleton>

                    <template v-else-if="orderProduct">
                        <Pill v-if="orderProduct.is_free" type="success" size="xs" :showDot="false">free</Pill>
                        <p v-else class="text-xs space-x-2">
                            <span :class="{ 'line-through text-gray-500' : orderProduct.on_sale }">{{ orderProduct.subtotal.amount_with_currency }}</span>
                            <span v-if="orderProduct.on_sale">{{ orderProduct.grand_total.amount_with_currency }}</span>
                        </p>
                    </template>

                </div>

                <!-- Remove Icon Button -->
                <X
                    @click.stop="() => orderState.removeCartProduct(index, false)"
                    class="w-4 h-4 flex-shrink-0 text-gray-400 cursor-pointer hover:opacity-90 active:opacity-80 active:scale-90 transition-all">
                </X>

            </div>

        </div>

        <div class="flex justify-end">

            <div class="w-fit text-sm flex items-center border border-gray-300 rounded-full overflow-hidden">

                <button
                    @click.stop="() => orderState.decreaseCartProductQuantity(index, false)"
                    class="px-3 py-1 bg-gray-200 text-gray-700 hover:bg-gray-300 cursor-pointer">
                    -
                </button>

                <input
                    min="0"
                    value="2"
                    v-model="cartProduct.quantity"
                    class="w-12 text-center border-0 focus:outline-none">

                <button
                    @click.stop="() => orderState.increaseCartProductQuantity(index, false)"
                    class="px-3 py-1 bg-gray-200 text-gray-700 hover:bg-gray-300 cursor-pointer">
                    +
                </button>

            </div>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import { X, Image } from 'lucide-vue-next';
    import Skeleton from '@Partials/Skeleton.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { X, Image, Pill, Input, Button, Skeleton },
        props: {
            index: {
                type: Number
            },
            cartProduct: {
                type: Object
            }
        },
        data() {
            return {

            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasPhotoPath() {
                return this.photoPath != null;
            },
            photoPath() {
                return this.cartProduct.photo_path;
            },
            isInspectingShoppingCart() {
                return this.orderState.isInspectingShoppingCart;
            },
            orderProduct() {
                if(!this.orderState.shoppingCart) return null;
                return this.orderState.shoppingCart.order_products.find(orderProduct => orderProduct.product_id === this.cartProduct.id);
            }
        }
    };

</script>
