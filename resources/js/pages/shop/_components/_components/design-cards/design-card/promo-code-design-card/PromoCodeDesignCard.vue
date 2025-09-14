<template>

    <div
        class="bg-white rounded-2xl p-4"
        v-if="designCard.type == 'promo code' && designCard.metadata.show_promo_code">

        <h1
            v-if="designCard.metadata.title"
            class="text-base text-gray-700 font-semibold">
            {{ designCard.metadata.title }}
        </h1>

        <p
            class="text-sm text-gray-700 mt-2"
            v-if="designCard.metadata.description">
            {{ designCard.metadata.description }}
        </p>

        <div class="space-y-4 mt-4">

            <div class="flex items-center space-x-4">

                <Input
                    type="text"
                    class="w-full"
                    v-model="orderForm.promotion_code"
                    placeholder="Enter discount or referral code"
                    :errorText="formState.getFormError('promotion_code')">
                </Input>

                <CircleCheckBig v-if="shoppingCart && promotionCode.applied" size="24" class="text-green-500"></CircleCheckBig>

            </div>

            <Skeleton v-if="isInspectingShoppingCart && orderForm.promotion_code" width="w-1/3" :shine="true"></Skeleton>

            <div v-else-if="shoppingCart && promotionCode.applied" class="py-2 px-4 border-l-4 border-green-500 bg-green-50 rounded-sm">
                <p class="text-sm font-bold mb-1">{{ promotionCode.name }}</p>
                <p class="text-sm italic">{{ promotionCode.message }}</p>
            </div>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { CircleCheckBig } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'orderState'],
        components: { Input, Skeleton, CircleCheckBig },
        props: {
            designCard: {
                type: Object
            }
        },
        computed: {
            orderForm() {
                return this.orderState.orderForm;
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            promotionCode() {
                return this.shoppingCart.promotion_code;
            },
            isInspectingShoppingCart() {
                return this.orderState.isInspectingShoppingCart;
            }
        }
    }
</script>
