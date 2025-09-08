<template>

    <div
        class="bg-white rounded-2xl p-4 space-y-4"
        v-if="designCard.metadata.type == 'promo code' && designCard.metadata.show_promo_code">

        <h1 v-if="designCard.metadata.title" class="text-base text-gray-700 font-semibold mb-4">{{ designCard.metadata.title }}</h1>

        <div class="space-y-4">

            <div class="flex items-center space-x-4">

                <Input
                    type="text"
                    class="w-full"
                    v-model="orderForm.promotion_code"
                    placeholder="Enter discount or referral code"
                    :errorText="formState.getFormError('promotion_code')">
                </Input>

                <transition name="fade-1" mode="out-in">
                    <svg v-if="false" class="w-8 h-8 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                    </svg>
                </transition>

            </div>

            <Skeleton v-if="isInspectingShoppingCart && orderForm.promotion_code" width="w-1/3" :shine="true"></Skeleton>

            <div v-else-if="hasShoppingCart && promotionCode.applied" class="p-2 border-l-4 border-green-300 bg-green-50">
                <p class="text-sm">{{ promotionCode.message }}</p>
            </div>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Skeleton from '@Partials/Skeleton.vue';

    export default {
        inject: ['formState', 'orderState'],
        components: { Input, Skeleton },
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
            hasShoppingCart() {
                return this.orderState.hasShoppingCart;
            },
            isInspectingShoppingCart() {
                return this.orderState.isInspectingShoppingCart;
            }
        }
    }
</script>
