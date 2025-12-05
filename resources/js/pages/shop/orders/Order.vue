<template>

    <div class="select-none max-w-xl mx-auto p-4 md:pt-8 pb-40">

       <div
            v-if="!isLoadingStore"
            :class="['flex items-center', hasMembership ? 'justify-start' : 'justify-end']">

            <Button
                size="xs"
                type="light"
                class="mb-4"
                :leftIcon="MoveLeft"
                v-if="hasMembership"
                :action="navigateToShowOrder">
                <span>Back to Dashboard</span>
            </Button>

            <Button
                v-else
                size="sm"
                type="light"
                class="mb-4"
                :rightIcon="MoveRight"
                :action="navigateToStoreLogin">
                <span>Login</span>
            </Button>

       </div>

        <Advert v-if="!activeSubscription"></Advert>

        <OrderDetails></OrderDetails>

        <Actions></Actions>

        <DeliveryAddress></DeliveryAddress>

        <OrderComments></OrderComments>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { MoveLeft, MoveRight } from 'lucide-vue-next';
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
                MoveLeft,
                MoveRight
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            hasMembership() {
                return this.store?.my_membership != null
            },
            activeSubscription() {
                return this.store.active_subscription;
            },
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
            },
            async navigateToStoreLogin() {
                if(this.isDesigning) {
                    this.notificationState.showSuccessNotification(`Only opens on the actual store`);
                    return;
                }
                await this.$router.push({
                    name: 'show-login'
                });
            },
        }
    };

</script>
