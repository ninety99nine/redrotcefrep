<template>

    <div class="max-h-80 overflow-y-auto space-y-2 mb-4">

        <ProductOption
            :key="index"
            :product="product"
            :onSelectProduct="onSelectProduct"
            v-for="(product, index) in products">
        </ProductOption>

    </div>

</template>

<script>

    import ProductOption from '@Pages/orders/order/editable/components/order-products/add-product/product-options/ProductOption.vue';

    export default {
        components: { ProductOption },
        props: {
            products: {
                type: Array
            },
            onSelectProduct: {
                type: Function
            }
        }
    };

</script>
