<template>

    <Button
        size="lg"
        class="mb-20"
        type="primary"
        buttonClass="w-full"
        :action="createOrder">
        <span>Place order</span>
    </Button>

</template>

<script>

    import Button from '@Partials/Button.vue';

    export default {
        inject: ['formState', 'designState', 'orderState', 'storeState', 'notificationState'],
        components: {
            Button
        },
        props: {
            placement: {
                type: String
            },
            designCards: {
                type: Array
            }
        },
        data() {
            return {
                creatingOrder: false
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            shoppingCart() {
                return this.orderState.shoppingCart;
            },
            designForm() {
                return this.designState.designForm;
            },
            isDesigning() {
                return ['edit-storefront', 'edit-checkout', 'edit-payment', 'edit-menu'].includes(this.$route.name);
            },
            deliveryMethods() {
                return this.orderState.deliveryMethods;
            },
            hasDeliveryMethods() {
                return this.orderState.hasDeliveryMethods;
            },
            selectedDeliveryMethod() {
                return this.orderState.selectedDeliveryMethod;
            },
            customerDesignCard() {
                return this.designCards.find(designCard => designCard.type == 'customer');
            }
        },
        methods: {
            async createOrder() {

                try {

                    this.formState.hideFormErrors();

                    const metadata = this.customerDesignCard.metadata;

                    if(metadata.show_first_name && metadata.first_name_required && (this.orderForm.customer_first_name == null || this.orderForm.customer_first_name.trim() == '')) {
                        this.formState.setFormError('customer_first_name', 'Enter your first name');
                    }

                    if(metadata.show_last_name && metadata.last_name_required && (this.orderForm.customer_last_name == null || this.orderForm.customer_last_name.trim() == '')) {
                        this.formState.setFormError('customer_last_name', 'Enter your last name');
                    }

                    if(metadata.show_mobile_number && metadata.mobile_number_required && (this.orderForm.customer_mobile_number == null || this.orderForm.customer_mobile_number.trim() == '')) {
                        this.formState.setFormError('customer_mobile_number', 'Enter your mobile number');
                    }

                    if(metadata.show_email && metadata.email_required && (this.orderForm.customer_email == null || this.orderForm.customer_email.trim() == '')) {
                        this.formState.setFormError('customer_email', 'Enter your email');
                    }

                    for (let index = 0; index < this.orderForm.cart_products.length; index++) {
                        const cartProduct = this.orderForm.cart_products[index];
                        if (typeof cartProduct.quantity === 'undefined' || cartProduct.quantity === null || isNaN(parseInt(cartProduct.quantity)) || parseInt(cartProduct.quantity) < 1) {
                            this.formState.setFormError(`cart_products.${index}`, 'The quantity must be a number greater than or equal to 1');
                        }
                    }

                    if(this.hasDeliveryMethods && !this.selectedDeliveryMethod) {
                        this.formState.setFormError('delivery_methods', 'Select a delivery method');
                    }else if(this.selectedDeliveryMethod) {
                        const selectedDeliveryMethodOption = this.shoppingCart.delivery_method_options.find(deliveryMethodOption => deliveryMethodOption.is_selected);
                        const index = this.deliveryMethods.findIndex(deliveryMethod => deliveryMethod.id == selectedDeliveryMethodOption.id);
                        if(!selectedDeliveryMethodOption.is_available) {
                            this.formState.setFormError(`delivery_methods.${index}`, `${selectedDeliveryMethodOption.name} is not available`);
                        }else if(selectedDeliveryMethodOption.delivery_address_is_required && !this.orderForm.delivery_address) {
                            this.formState.setFormError(`delivery_methods.${index}.address`, `Add your address`);
                        }else if(selectedDeliveryMethodOption.pin_location_on_map && (!this.orderForm.delivery_address.latitude || !this.orderForm.delivery_address.longitude)) {
                                this.formState.setFormError(`delivery_methods.${index}.address`, `Edit your address to pin your location on the map`);
                        }else if(selectedDeliveryMethodOption.schedule_is_required && !selectedDeliveryMethodOption.schedule_date_complete) {
                            this.formState.setFormError(`delivery_methods.${index}.schedule`, `${selectedDeliveryMethodOption.name} date is required`);
                        }else if(selectedDeliveryMethodOption.schedule_is_required && !selectedDeliveryMethodOption.schedule_time_complete) {
                            this.formState.setFormError(`delivery_methods.${index}.schedule`, `${selectedDeliveryMethodOption.name} time is required`);
                        }
                    }

                    if (this.formState.hasErrors) return;

                    this.orderState.creatingOrder = true;

                    const data = {
                        ...this.orderForm,
                        association: 'shopper',
                        store_id: this.store.id
                    };

                    const response = await axios.post(`/api/orders`, data);
                    this.notificationState.showSuccessNotification(`Order created`);

                    const order = response.data.order;
                    await this.navigateToOrder(order);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create order:', error);
                } finally {
                    this.orderState.creatingOrder = false;
                }

            },
            async navigateToOrder(order) {
                //  Refer to resources/js/layouts/shop/Shop.vue to see what happens
                //  on the orderId wacher after order_id is set on the route.

                await this.$router.push({
                    name: 'show-shop-payment-methods',
                    params: {
                        order_id: order.id,
                        alias: this.store.alias
                    }
                });
            },
        }
    }
</script>
