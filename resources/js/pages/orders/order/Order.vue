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
        inject: ['orderState', 'storeState', 'formState', 'notificationState', 'changeHistoryState'],
        components: { OrderHeader },
        watch: {
            store(newValue) {
                if(newValue) {
                    this.setup();
                }
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
            orderId() {
                return this.$route.params.order_id;
            },
            duplicateOrderId() {
                return this.$route.query.duplicate_order_id;
            },
            isEditing() {
                return this.$route.name === 'edit-order';
            },
            isCreating() {
                return this.$route.name === 'create-order';
            },
            orderForm() {
                return this.orderState.orderForm;
            },
        },
        methods: {
            setup() {
                if(!this.store) return;

                if(this.orderId || this.duplicateOrderId) {
                    this.showOrder();
                }else{
                    this.orderState.setOrderForm(null);
                }

                if(this.isCreating || this.isEditing) {
                    this.changeHistoryState.removeButtons();
                    this.changeHistoryState.addDiscardButton();
                    this.changeHistoryState.addActionButton(
                        this.isEditing ? 'Save Changes' : 'Create Order',
                        this.isEditing ? this.updateOrder : this.createOrder,
                        'primary',
                        null,
                    );
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
                    this.orderState.setOrderForm(order);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch order:', error);

                    if(error.status == 404) {
                        await this.$router.replace({
                            name: 'show-orders',
                            query: {
                                store_id: this.store.id,
                                searchTerm: this.$route.query.searchTerm,
                                filterExpressions: this.$route.query.filterExpressions,
                                sortingExpressions: this.$route.query.sortingExpressions
                            }
                        });
                    }

                } finally {
                    this.orderState.isLoadingOrder = false;
                }
            },
            async createOrder() {

                try {

                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.orderForm,
                        guest_id: this.guestId,
                        store_id: this.store.id,
                        association: 'team member'
                    };

                    const response = await axios.post(`/api/orders`, data);
                    this.notificationState.showSuccessNotification(`Order created`);
                    this.changeHistoryState.resetHistoryToCurrent();
                    this.orderState.setShoppingCart(null);
                    const order = response.data.order;
                    this.onView(order);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create order:', error);
                } finally {
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async updateOrder() {

                try {

                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.orderForm,
                        guest_id: this.guestId,
                        store_id: this.store.id,
                        association: 'team member'
                    };

                    const response = await axios.put(`/api/orders/${this.order.id}`, data);
                    this.notificationState.showSuccessNotification(`Order updated`);
                    this.changeHistoryState.resetHistoryToCurrent();
                    this.orderState.setShoppingCart(null);
                    const order = response.data.order;
                    this.onView(order);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update order:', error);
                } finally {
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            onView(order) {
                this.$router.push({
                    name: 'show-order',
                    params: {
                        order_id: order.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            setOrderForm(orderForm) {
                this.orderState.orderForm = orderForm;
            }
        },
        beforeRouteUpdate(to, from, next) {
            //  Triggered when navigating between routes sharing same component e.g from "edit-order" to "show-order"
            if (this.changeHistoryState.hasChangeHistory) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            next();
        },
        beforeRouteLeave(to, from, next) {
            //  Triggered when navigating between routes not sharing same component e.g from "edit-order" to "show-store-home"
            if (this.changeHistoryState.hasChangeHistory) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            next();
        },
        unmounted() {
            this.orderState.reset();
            this.changeHistoryState.reset();
        },
        created() {
            this.setup();
            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setOrderForm;
            }
        }
    };

</script>
