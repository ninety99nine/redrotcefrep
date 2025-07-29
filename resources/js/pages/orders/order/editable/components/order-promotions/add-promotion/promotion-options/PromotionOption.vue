<template>

    <div
        @click="() => onSelectPromotion(promotion)"
        class="flex justify-between items-center cursor-pointer p-3 space-x-4 border border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100">

        <div class="w-full flex items-center justify-between">

            <div>
                <p class="text-sm font-semibold">{{ promotion.name }}</p>
            </div>

            <div class="flex items-center space-x-1">
                <Pill v-if="promotion.offer_discount" type="success" size="xs">discount</Pill>
                <Pill v-if="promotion.offer_free_delivery" type="success" size="xs">free delivery</Pill>
            </div>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';

    export default {
        components: {
            Pill
        },
        props: {
            promotion: {
                type: Object
            },
            onSelectPromotion: {
                type: Function
            }
        }
    };

</script>
