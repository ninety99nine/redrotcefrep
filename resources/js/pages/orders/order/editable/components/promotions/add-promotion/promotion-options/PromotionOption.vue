<template>

    <div
        @click="() => onSelectPromotion(promotion)"
        class="flex justify-between items-center cursor-pointer p-3 space-x-4 border rounded-lg bg-gray-50 hover:bg-gray-100">

        <div class="w-full flex items-center justify-between">

            <div>
                <p class="text-sm font-semibold">{{ promotion.name }}</p>
            </div>

            <Pill v-if="promotion.offerDiscount.status" type="success" size="xs">{{ promotion.offerDiscount.name }}</Pill>
            <Pill v-if="promotion.offerFreeDelivery.status" type="success" size="xs">{{ promotion.offerFreeDelivery.name }}</Pill>

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
        },
        computed: {
            photo() {
                return this.promotion._relationships.photo;
            },
            hasPhoto() {
                return this.photo != null;
            }
        }
    };

</script>
