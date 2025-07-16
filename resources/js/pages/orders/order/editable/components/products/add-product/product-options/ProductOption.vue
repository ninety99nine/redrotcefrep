<template>

    <div
        @click="() => onSelectProduct(product)"
        class="flex justify-between items-center cursor-pointer p-2 space-x-4 rounded-lg bg-gray-50 hover:bg-gray-100">

        <div :class="['flex items-center justify-center w-10 h-10', { 'border border-dashed border-gray-200 rounded-lg' : !hasPhoto }]">

            <img v-if="hasPhoto" class="w-full object-contain rounded-lg bg-red-200 flex-shrink-0" :src="photo.file_path">

            <svg v-else class="w-6 h-6 text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>

        </div>

        <div class="w-full flex items-center justify-between">

            <div>
                <p class="text-sm font-semibold">{{ product.name }}</p>
                <p class="text-xs space-x-2">
                    <span :class="{ 'line-through text-gray-500' : product.on_sale }">{{ product.unit_regular_price.amount_with_currency }}</span>
                    <span v-if="product.on_sale">{{ product.unit_sale_price.amount_with_currency }}</span>
                </p>
            </div>

            <Pill v-if="product.allow_variations.status" type="light" size="xs">{{ product.total_visible_variations }} options</Pill>

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
            product: {
                type: Object
            },
            onSelectProduct: {
                type: Function
            }
        },
        computed: {
            photo() {
                return this.product.photo;
            },
            hasPhoto() {
                return this.photo != null;
            }
        }
    };

</script>
