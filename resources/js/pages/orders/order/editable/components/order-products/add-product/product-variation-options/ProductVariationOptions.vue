<template>

    <div class="space-y-2 mb-4">

        <ProductOptions
            :parentProduct="selectedProduct"
            :onSelectProduct="onSelectProduct"
            :products="selectedProduct.variants">
        </ProductOptions>

    </div>

</template>

<script>

    import ProductOptions from '@Pages/orders/order/editable/components/order-products/add-product/product-options/ProductOptions.vue';

    export default {
        components: { ProductOptions },
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
