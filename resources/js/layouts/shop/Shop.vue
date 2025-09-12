<template>

    <div>
        <Notifications></Notifications>
        <router-view></router-view>
    </div>

</template>

<script>

    import debounce from 'lodash/debounce';
    import Notifications from '@Layouts/shop/components/Notifications.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Notifications },
        watch: {
            alias() {
                this.showStoreByAlias();
            },
            orderForm: {
                handler(newVal) {
                    console.log('stage 1');
                    if(this.canInspectShoppingCart) {
                        console.log('stage 2');
                        console.log('stage 3');
                        this.orderState.setIsInspectingShoppingCart(true);
                        this.inspectShoppingCartDelayed();
                    }
                },
                deep: true
            },
            orderId(newVal) {
                if(newVal) {
                    //  Important after placing an order at checkout page
                    this.showOrder();
                }
            }
        },
        computed: {
            alias() {
                return this.$route.params.alias;
            },
            store() {
                return this.storeState.store;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            canInspectShoppingCart() {
                return this.orderState.canInspectShoppingCart;
            },
            orderId() {
                return this.$route.params.order_id;
            },
        },
        methods: {
            async showStoreByAlias() {
                try {

                    this.storeState.isLoadingStore = true;

                    let config = {
                        params: {
                            _relationships: ['logo', 'categories'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/stores/alias/${this.alias}`, config);

                    this.storeState.setStore(response.data);

                    if(this.orderId) {
                        await this.showOrder();
                    }else{
                        if(await this.orderState.hasStateFromLocalStorage()) {
                            await this.orderState.setStateFromLocalStorage();
                            await this.inspectShoppingCart();
                        }else{
                            this.orderState.setOrderForm(null, false);
                            setTimeout(() => { this.orderState.canInspectShoppingCart = true }, 1000);
                        }
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch store:', error);
                } finally {
                    this.storeState.isLoadingStore = false;
                }
            },
            async showOrder() {
                try {

                    this.orderState.isLoadingOrder = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['orderProducts.photo', 'orderPromotions', 'orderFees', 'orderDiscounts', 'orderComments', 'customer', 'courier', 'deliveryMethod', 'deliveryAddress'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/orders/${this.orderId || this.duplicateOrderId}`, config);

                    const order = response.data;
                    this.orderState.setOrder(order);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch order:', error);

                    if(error.status == 404) {
                        await this.$router.replace({
                            name: 'show-storefront',
                            params: {
                                alias: this.store.alias
                            }
                        });
                    }

                } finally {
                    this.orderState.isLoadingOrder = false;
                }
            },
            inspectShoppingCartDelayed: debounce(function () {
                this.inspectShoppingCart();
            }, 1000),
            async inspectShoppingCart() {

                try {

                    this.orderState.setIsInspectingShoppingCart(true);

                    const data = {
                        inspect: true,
                        ...this.orderForm,
                        association: 'shopper',
                        store_id: this.store.id,
                    };

                    const response = await axios.post(`/api/orders`, data);
                    this.orderState.setShoppingCart(response.data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while inspecting shopping cart';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to inspect shopping cart:', error);
                } finally {
                    this.orderState.setIsInspectingShoppingCart(false);
                    this.orderState.setStateOnLocalStorage();
                }

            }
        },
        beforeUnmount() {
            this.orderState.resetOrderForm();
        },
        created() {
            this.showStoreByAlias();
            this.orderState.runInspectShoppingCart = this.inspectShoppingCart;
        }
    };

</script>
