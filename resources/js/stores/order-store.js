import { defineStore } from 'pinia';
import { formattedDate } from '@Utils/dateUtils.js';
import { useStoreStore as storeState } from '@Stores/store-store.js';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useOrderStore = defineStore('order', {
    state: () => ({
        order: null,
        orderForm: null,
        shoppingCart: null,
        isLoadingOrder: false,
        isInspectingShoppingCart: false
    }),
    actions: {
        reset() {
            this.order = null;
            this.orderForm = null;
            this.shoppingCart = null;
            this.isLoadingOrder = false;
            this.isInspectingShoppingCart = false;
        },
        setOrder(order) {
            this.order = order;
        },
        setIsLoadingOrder(isLoadingOrder) {
            this.isLoadingOrder = isLoadingOrder;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.orderForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.orderForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.orderForm);
        },
        setOrderForm(order = null) {

            this.orderForm = {

                cart_fees: [],
                cart_products: [],
                cart_promotions: [],

                remark: order?.remark ?? null,
                adjustment: order?.adjustment_total?.amount_without_currency ?? null,

                update_profile: true,
                customer_email: order?.customer_email ?? null,
                customer_last_name: order?.customer_last_name ?? null,
                customer_first_name: order?.customer_first_name ?? null,
                customer_mobile_number: order?.customer_mobile_number?.international ?? null,

                delivery_address: order?.delivery_address ?? null,
                delivery_timeslot: order?.delivery_timeslot ?? null,
                delivery_method_id: order?.delivery_method_id ?? null,
                delivery_date: order?.delivery_date ? formattedDate(order.delivery_date) : null,

                schedule: {
                    show_all_dates: true,
                    show_all_timeslots: true,
                    change_delivery_schedule: false
                },

                remark: order?.remark ?? null,
                status: order?.status ?? 'waiting',
                courier_id: order?.courier_id ?? null,
                internal_note: order?.internal_note ?? null,
                tracking_number: order?.tracking_number ?? null,
                payment_status: order?.payment_status ?? 'unpaid',
                assigned_to_user_id: order?.assigned_to_user_id ?? null,
            };

            if(order) {

                order.order_fees.forEach((orderFee) => {
                    this.addCartFeeUsingOrderFee(orderFee);
                });

                order.order_products.forEach((orderProduct) => {
                    this.addCartProductUsingOrderProduct(orderProduct);
                });

                order.order_promotions.forEach((orderPromotion) => {
                    this.addCartPromotionUsingOrderPromotion(orderPromotion);
                });

                storeState().store.checkout_fees.forEach((checkoutFee) => {
                    const exists = this.orderForm.cart_fees.some((cartFee) => cartFee.name === checkoutFee.name);
                    if(!exists) this.addCartFeeUsingCheckoutFee(checkoutFee, false);
                });

            }else{

                storeState().store.checkout_fees.forEach((checkoutFee) => {
                    this.addCartFeeUsingCheckoutFee(checkoutFee);
                });

            }

            this.saveOriginalState('Original order');

        },
        setShoppingCart(shoppingCart) {
            this.shoppingCart = shoppingCart;
        },
        setIsInspectingShoppingCart(isInspectingShoppingCart) {
            this.isInspectingShoppingCart = isInspectingShoppingCart;
        },
        addCartFeeUsingOrderFee(orderFee) {
            this.orderForm.cart_fees.push({
                'active': true,
                'removable': false,
                'name': orderFee.name,
                'rate_type': orderFee.rate_type,
                'flatRate': orderFee.amount.amount_without_currency,
                'percentage_rate': orderFee.percentage_rate.toString()
            });
        },


        addCartProductUsingOrderProduct(orderProduct) {

            const photo = orderProduct?.photo;

            // Convert numeric values to numbers to prevent issues with decimal formatting
            const unitSalePrice = Number(orderProduct.unit_sale_price.amount);
            const unitRegularPrice = Number(orderProduct.unit_regular_price.amount);

            const existingProduct = this.orderForm.cart_products.find(p =>
                p.name === orderProduct.name &&
                Number(p.unit_sale_price) === unitSalePrice &&
                Number(p.unit_regular_price) === unitRegularPrice
            );

            if (existingProduct) {

                // Increase quantity if product already exists
                existingProduct.quantity = (parseInt(existingProduct.quantity) + 1).toString();

            } else {

                // Add as a new product
                this.orderForm.cart_products.push({
                    'name': orderProduct.name,
                    'id': orderProduct.product_id,
                    'is_free': orderProduct.is_free,
                    'quantity': orderProduct.quantity.toString(),
                    'photo_path': photo ? photo.path : null,
                    'unit_weight': orderProduct.unit_weight.toString(),
                    'unit_sale_price': orderProduct.unit_sale_price.amount,
                    'unit_regular_price': orderProduct.unit_regular_price.amount
                });

            }
        },
        addCartProductUsingProduct(product, parentProduct = null) {

            const photo = product.photo;
            const name = parentProduct ? parentProduct.name+' '+product.name : product.name;

            // Convert numeric values to numbers for consistent comparison
            const unitSalePrice = Number(product.unit_sale_price.amount);
            const unitRegularPrice = Number(product.unit_regular_price.amount);

            const existingProduct = this.orderForm.cart_products.find(p =>
                p.name === name &&
                Number(p.unit_sale_price) === unitSalePrice &&
                Number(p.unit_regular_price) === unitRegularPrice
            );

            if (existingProduct) {

                // Increase quantity if product already exists
                existingProduct.quantity = (parseInt(existingProduct.quantity) + 1).toString();

            } else {

                // Add as a new product
                this.orderForm.cart_products.push({
                    'name': name,
                    'quantity': '1',
                    'id': product.id,
                    'is_free': product.is_free,
                    'photo_path': photo ? photo.path : null,
                    'unit_weight': product.unit_weight.toString(),
                    'unit_sale_price': product.unit_sale_price.amount,
                    'unit_regular_price': product.unit_regular_price.amount
                });

            }

            this.saveState('Product added');
        },
        addCartProduct() {

            this.orderForm.cart_products.push({
                'name': '',
                'quantity': '1',
                'is_free': false,
                'unit_weight': '0',
                'photo_path': null,
                'unit_sale_price': '0.00',
                'unit_regular_price': '0.00'
            });

            this.saveState('Product added');

        },
        removeCartProduct(index) {
            this.orderForm.cart_products.splice(index, 1);
            this.saveState('Product removed');
        },

        addCartPromotionUsingOrderPromotion(orderPromotion) {

            const discountPercentageRate = Number(orderPromotion.discount_percentage_rate);
            const discountFlatRate = Number(orderPromotion.discount_flat_rate.amount);

            const existingPromotion = this.orderForm.cart_promotions.find(p =>
                p.name === orderPromotion.name &&
                Number(p.discount_flat_rate.amount) === discountFlatRate &&
                p.discount_rate_type === orderPromotion.discount_rate_type &&
                p.offer_discount === orderPromotion.offer_discount &&
                Number(p.discount_percentage_rate) === discountPercentageRate &&
                p.offer_free_delivery === orderPromotion.offer_free_delivery
            );

            if (!existingPromotion) {

                // Add as a new promotion
                this.orderForm.cart_promotions.push({
                    'name': orderPromotion.name,
                    'offer_discount': orderPromotion.offer_discount,
                    'discount_rate_type': orderPromotion.discount_rate_type,
                    'offer_free_delivery': orderPromotion.offer_free_delivery,
                    'discount_percentage_rate': orderPromotion.discount_percentage_rate.toString(),
                    'discount_flat_rate': orderPromotion.discount_flat_rate.amount_without_currency,
                });

            }
        },
        addCartPromotionUsingPromotion(promotion = null) {

            console.log(promotion);
            console.log(this.orderForm.cart_promotions);

            const existingPromotion = this.orderForm.cart_promotions.find(p =>
                p.name === promotion.name &&
                p.offer_discount === promotion.offer_discount &&
                p.discount_rate_type === promotion.discount_rate_type &&
                p.offer_free_delivery === promotion.offer_free_delivery &&
                Number(p.discount_percentage_rate) === Number(promotion.discount_percentage_rate) &&
                Number(p.discount_flat_rate) === Number(promotion.discount_flat_rate.amount_without_currency)
            );

            if (!existingPromotion) {

                // Add as a new promotion
                this.orderForm.cart_promotions.push({
                    'name': promotion.name,
                    'offer_discount': promotion.offer_discount,
                    'discount_rate_type': promotion.discount_rate_type,
                    'offer_free_delivery': promotion.offer_free_delivery,
                    'discount_percentage_rate': promotion.discount_percentage_rate.toString(),
                    'discount_flat_rate': promotion.discount_flat_rate.amount_without_currency
                });

            }

            this.saveState('Promotion added');
        },
        addCartPromotion() {

            this.orderForm.cart_promotions.push({
                'name': '',
                'offer_discount': false,
                'discount_flat_rate': '0',
                'discount_rate_type': 'flat',
                'offer_free_delivery': false,
                'discount_percentage_rate': '0'
            });

            this.saveState('Promotion added');

        },
        removeCartPromotion(index) {
            this.orderForm.cart_promotions.splice(index, 1);
            this.saveState('Promotion removed');
        },

        addCartFeeUsingOrderFee(orderFee) {
            this.orderForm.cart_fees.push({
                'active': true,
                'removable': false,
                'name': orderFee.name,
                'rate_type': orderFee.rate_type,
                'flat_rate': orderFee.amount.amount_without_currency,
                'percentage_rate': orderFee.percentage_rate?.toString()
            });
        },
        addCartFeeUsingCheckoutFee(checkoutFee, active = true) {
            this.orderForm.cart_fees.push({
                'active': active,
                'removable': false,
                'name': checkoutFee.name,
                'rate_type': checkoutFee.rate_type,
                'flat_rate': checkoutFee.flat_rate.amount_without_currency,
                'percentage_rate': checkoutFee.percentage_rate?.toString(),
            });
        },
        addCartFee() {
            this.orderForm.cart_fees.push({
                'name': '',
                'active': true,
                'flat_rate': '0',
                'removable': true,
                'rate_type': 'flat',
                'percentage_rate': '0',
            });
            this.saveState('Fee added');
        },
        removeCartFee(index) {
            this.orderForm.cart_fees.splice(index, 1);
            this.saveState('Fee removed');
        },

        addCartCustomer(customer = {}) {

            const hasCustomerDetails = this.hasCustomerDetails;

            this.orderForm.customer_email = customer.email;
            this.orderForm.customer_last_name = customer.last_name;
            this.orderForm.customer_first_name = customer.first_name;
            this.orderForm.customer_mobile_number = customer.mobile_number?.international;

            if(hasCustomerDetails) {
                this.saveState('Customer changed');
            }else{
                this.saveState('Customer added');
            }
        },
    },
    getters: {
        hasOrder() {
            return this.order != null;
        },
        hasCustomerDetails() {
            return this.orderForm.customer_email?.trim() ||
                    this.orderForm.customer_last_name?.trim() ||
                    this.orderForm.customer_first_name?.trim() ||
                    this.orderForm.customer_mobile_number?.trim();
        }
    }
});
