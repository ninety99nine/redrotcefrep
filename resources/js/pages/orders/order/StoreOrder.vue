<template>

    <div class="pt-24 px-4">

        <!-- Order Header -->
        <OrderHeader></OrderHeader>

        <!-- Order Content -->
        <router-view></router-view>

    </div>

</template>

<script>

    import OrderHeader from '@Pages/orders/order/components/order-header/OrderHeader.vue';

    export default {
        inject: ['orderState', 'shoppingCartState', 'storeState', 'formState', 'notificationState'],
        components: { OrderHeader },
        watch: {
            store(newValue) {

                if(newValue) {
                    if(this.orderId) {
                        this.showOrder();
                    }else{
                        this.shoppingCartState.setDefaultShoppingCartForm();
                    }
                }

            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            orderId() {
                return this.$route.params.order_id;
            }
        },
        methods: {
            async showOrder() {
                try {

                    this.orderState.isLoadingOrder = true;

                    let config = {
                        params: {
                            'store_id': this.store.id,
                            '_relationships': ['orderProducts.photo', 'orderPromotions', 'orderFees', 'orderDiscounts', 'orderComments', 'orderHistory', 'customer', 'courier', 'deliveryAddress'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/orders/${this.orderId}`, config);

                    this.shoppingCartState.setShoppingCartForm(response.data);
                    this.orderState.setOrderForm(response.data);
                    this.orderState.setOrder(response.data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch order:', error);

                    //  this.$router.push({ name: 'show-orders', queries: { 'store_id': this.store.id } });

                } finally {
                    this.orderState.isLoadingOrder = false;
                }
            }
        }
    };

</script>
