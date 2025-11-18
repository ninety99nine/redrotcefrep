<template>

    <div class="select-none max-w-xl mx-auto pt-16 pb-40">

        <Button
            size="xs"
            type="light"
            class="mb-4"
            :leftIcon="MoveLeft"
            v-if="hasMembership"
            :action="navigateToShowOrder">
            <span>Back to Dashboard</span>
        </Button>

        <Advert></Advert>

        <OrderDetails></OrderDetails>

        <Actions></Actions>

        <DeliveryAddress></DeliveryAddress>

        <OrderComments></OrderComments>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { MoveLeft } from 'lucide-vue-next';
    import Advert from '@Pages/shop/orders/_components/advert/Advert.vue';
    import Actions from '@Pages/shop/orders/_components/actions/Actions.vue';
    import OrderDetails from '@Pages/shop/orders/_components/order-details/OrderDetails.vue';
    import OrderComments from '@Pages/shop/orders/_components/order-comments/OrderComments.vue';
    import DeliveryAddress from '@Pages/shop/orders/_components/delivery-address/DeliveryAddress.vue';

    export default {
        inject: ['orderState', 'storeState'],
        components: { Advert, Button, Actions, OrderDetails, OrderComments, DeliveryAddress },
        data() {
            return {
                MoveLeft
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
            hasMembership() {
                return this.store?.my_membership != null
            }
        },
        methods: {
            navigateToShowOrder() {
                this.$router.push({
                    name: 'show-order',
                    params: {
                        order_id: this.order.id
                    },
                    query: {
                        store_id: this.store.id
                    }
                });
            }
        }
    };

</script>
