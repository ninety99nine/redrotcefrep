<template>

    <div
        class="bg-white rounded-2xl p-4 space-y-4"
        v-if="designCard.metadata.type == 'items' && designCard.metadata.title">

        <h1 class="text-lg text-gray-700 font-semibold mb-4">{{ designCard.metadata.title }}</h1>

        <div class="space-y-2">

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
        },
        data() {
            return {
                email: '',
                lastName: '',
                firstName: '',
                mobileNumber: '',
            }
        }
    }
</script>
