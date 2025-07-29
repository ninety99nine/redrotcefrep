<template>

    <div class="max-h-80 overflow-y-auto space-y-2 mb-4">

        <PromotionOption
            :key="index"
            :promotion="promotion"
            :onSelectPromotion="onSelectPromotion"
            v-for="(promotion, index) in promotions">
        </PromotionOption>

    </div>

</template>

<script>

    import PromotionOption from '@Pages/orders/order/editable/components/order-promotions/add-promotion/promotion-options/PromotionOption.vue';

    export default {
        components: { PromotionOption },
        props: {
            promotions: {
                type: Array
            },
            onSelectPromotion: {
                type: Function
            }
        }
    };

</script>
