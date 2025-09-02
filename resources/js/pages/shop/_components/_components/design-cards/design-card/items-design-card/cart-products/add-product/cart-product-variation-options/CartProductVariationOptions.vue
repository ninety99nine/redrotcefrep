<template>

    <div class="space-y-2 mb-4">

        <CartProductOptions
            :parentProduct="selectedProduct"
            :onSelectProduct="onSelectProduct"
            :products="selectedProduct.variants">
        </CartProductOptions>

    </div>

</template>

<script>

    import CartProductOptions from '@Pages/shop/_components/_components/design-cards/design-card/items-design-card/cart-products/add-product/cart-product-options/CartProductOptions.vue';

    export default {
        components: { CartProductOptions },
        props: {
            selectedProduct: {
                type: Object
            },
            onSelectProduct: {
                type: Function
            }
        }
    };

</script>
