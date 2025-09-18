<template>

    <div class="max-h-80 overflow-y-auto space-y-2 mb-4">

        <CartProductOption
            :key="index"
            :product="product"
            :parentProduct="parentProduct"
            :onSelectProduct="onSelectProduct"
            v-for="(product, index) in products">
        </CartProductOption>

    </div>

</template>

<script>

    import CartProductOption from '@Pages/shop/_components/design-card-manager/_components/design-cards/design-card/items-design-card/cart-products/add-product/cart-product-options/CartProductOption.vue';

    export default {
        components: { CartProductOption },
        props: {
            parentProduct: {
                type: Object,
                default: null
            },
            products: {
                type: Array
            },
            onSelectProduct: {
                type: Function
            }
        }
    };

</script>
