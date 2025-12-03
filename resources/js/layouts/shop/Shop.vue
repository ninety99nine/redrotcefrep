<template>

    <div>
        <Notifications></Notifications>
        <Menu v-if="showMenu"></Menu>
        <Header v-if="showHeader"></Header>
        <router-view></router-view>
    </div>

</template>

<script>

    import debounce from 'lodash.debounce';
    import Menu from '@Pages/shop/_components/menu/Menu.vue';
    import Notifications from '@Layouts/shop/components/Notifications.vue';
    import Header from '@Pages/shop/_components/design-card-manager/_components/header/Header.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Menu, Header, Notifications },
        data() {
            return {
                orderAgain: false
            }
        },
        watch: {
            // !!!  IMPORTANT NOTE  !!!
            // Watcher runs in sequence, so we want $route() to always run
            // before orderId(), so we must place it above orderId(). We
            // need to set orderAgain before we can get to processing
            // the orderId() watcher.
            $route(to, from) {
                this.orderAgain = from.name === 'show-shop-order' && to.name === 'show-checkout';

                /**
                 *  Get the store again so that we can update the page views on the server.
                 *  Refer to: Middleware -> RecordStoreVisit
                 */
                this.showStore();
            },
            orderId(newVal) {
                const orderToOrderAgain = this.orderAgain ? this.order : null;
                this.orderState.resetOrderForm();

                if(newVal) {
                    //  Important after placing an order at checkout page
                    this.showOrder();
                }else{
                    this.prepareStoreForShopping(orderToOrderAgain);
                }
            },
            orderForm: {
                handler() {
                    if(this.canInspectShoppingCart) {
                        this.orderState.setIsInspectingShoppingCart(true);
                        this.inspectShoppingCartDelayed();
                    }
                },
                deep: true
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            alias() {
                return this.$route.params.alias || null;
            },
            orderId() {
                return this.$route.params.order_id || null;
            },
            order() {
                return this.orderState.order;
            },
            hasOrder() {
                return this.order != null;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            canInspectShoppingCart() {
                return this.orderState.canInspectShoppingCart;
            },
            showHeader() {
                return ['show-storefront', 'show-search', 'show-shop-category'].includes(this.$route.name);
            },
            showMenu() {
                return !['show-checkout', 'show-shop-payment-methods', 'show-shop-payment-method', 'show-shop-confirming-payment', 'show-shop-pending-payment'].includes(this.$route.name);
            }
        },
        methods: {
            async prepareStoreForShopping(orderToOrderAgain = null) {
                if(await this.orderState.hasStateFromLocalStorage()) {
                    await this.orderState.setStateFromLocalStorage();
                    await this.inspectShoppingCart();
                }else{
                    //  If we want to order again
                    if(orderToOrderAgain) {
                        this.orderState.setOrderForm(orderToOrderAgain, false);
                        this.orderState.canInspectShoppingCart = true;
                    }else{
                        this.orderState.setOrderForm(null, false);
                        setTimeout(() => { this.orderState.canInspectShoppingCart = true }, 1000);
                    }
                }
            },
            //  Responds to showing store via custom domain or alias
            async showStore() {
                try {

                    if(!this.store) this.storeState.isLoadingStore = true;

                    let config = {
                        params: {
                            _relationships: ['logo', 'backgroundPhoto', 'categories', 'myMembership'].join(',')
                        }
                    };

                    let response;

                    if (window.storeId && !this.alias) {
                        // Show by storeId for custom domains (Refer to: resources/views/render.blade.php for window.storeId)
                        response = await axios.get(`/api/stores/${window.storeId}`, config);
                    } else if (this.alias) {
                        // Show by alias for alias-based routes
                        response = await axios.get(`/api/stores/alias/${this.alias}`, config);
                    } else {
                        return;
                    }

                    this.storeState.setStore(response.data);

                    document.body.style.backgroundColor = this.store.bg_color;

                    if(this.orderId) {
                        await this.showOrder();
                    }else{
                        await this.prepareStoreForShopping();
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch store:', error);
                    this.$router.replace({ name: 'notFound' });
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

                    if (error.response?.status === 404) {
                        await this.$router.replace({
                            name: 'show-storefront',
                            params: {
                                alias: window.storeId ? null : this.store.alias
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
        unmounted() {
            document.body.style.backgroundColor = null;
        },
        created() {
            if(this.store) {
                if(this.orderId) {
                    this.showOrder();
                }else{
                    this.prepareStoreForShopping();
                }
            }else{
                this.showStore();
            }
        }
    };

</script>
