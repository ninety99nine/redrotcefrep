<template>

    <div
        class="bg-white rounded-2xl p-4"
        v-if="designCard.type == 'items' && designCard.metadata.show_items">

        <h1
            v-if="designCard.metadata.title"
            class="text-base text-gray-700 font-semibold">
            {{ designCard.metadata.title }}
        </h1>

        <p
            v-if="designCard.metadata.description"
            class="text-sm text-gray-700 mt-2">
            {{ designCard.metadata.description }}
        </p>

        <div class="space-y-2 mt-4">

            <CartProducts></CartProducts>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import CartProducts from '@Pages/shop/_components/_components/design-cards/design-card/items-design-card/cart-products/CartProducts.vue';

    export default {
        components: { Input, CartProducts },
        props: {
            designCard: {
                type: Object
            }
        }
    }
</script>
