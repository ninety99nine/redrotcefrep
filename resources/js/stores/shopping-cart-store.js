import { defineStore } from 'pinia';
import { formattedDate } from '@Utils/dateUtils.js';
import { useStoreStore as storeState } from '@Stores/store-store.js';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useShoppingCartStore = defineStore('shopping-cart', {
    state: () => ({
        shoppingCart: null,
        shoppingCartForm: null,
        isInspectingShoppingCart: false
    }),
    actions: {
        setShoppingCart(shoppingCart) {
            this.shoppingCart = shoppingCart;
        },
        setIsInspectingShoppingCart(isInspectingShoppingCart) {
            this.isInspectingShoppingCart = isInspectingShoppingCart;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.shoppingCartForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.shoppingCartForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.shoppingCartForm);
        },
        setDefaultShoppingCartForm() {

            this.shoppingCartForm = {

                cart_fees: [],
                adjustment: null,
                cart_products: [],
                cart_promotions: [],

                update_profile: true,
                customer_email: null,
                customer_last_name: null,
                customer_first_name: null,
                customer_mobile_number: null,

                delivery_date: '',
                delivery_address: null,
                delivery_timeslot: null,
                delivery_method_id: null,

                schedule: {
                    show_all_dates: true,
                    show_all_timeslots: true,
                    change_delivery_schedule: false
                }

            };

            storeState().store.checkout_fees.forEach((checkoutFee) => {
                this.addCartFeeUsingCheckoutFee(checkoutFee);
            });

            //  this.saveOriginalState('Original order');

        },
        setShoppingCartForm(order) {

            this.shoppingCartForm = {

                cart_fees: [],
                cart_products: [],
                cart_promotions: [],
                adjustment: order.adjustment_total.amount_without_currency,

                update_profile: true,
                customer_email: order.customer_email,
                customer_last_name: order.customer_last_name,
                customer_first_name: order.customer_first_name,
                customer_mobile_number: order.customer_mobile_number?.international,

                delivery_address: order.delivery_address,
                delivery_timeslot: order.delivery_timeslot,
                delivery_method_id: order.delivery_method_id,
                delivery_date: order.delivery_date ? formattedDate(order.delivery_date) : null,

                schedule: {
                    show_all_dates: true,
                    show_all_timeslots: true,
                    change_delivery_schedule: false
                },






                remark: order.remark,
                status: order.status,
                courier_id: order.courier_id,
                internal_note: order.internal_note,
                payment_status: order.payment_status,
                tracking_number: order.tracking_number,
                assigned_to_user_id: order.assigned_to_user_id,
            };

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
                const exists = this.shoppingCartForm.cart_fees.some((cartFee) => cartFee.name === checkoutFee.name);
                if(!exists) this.addCartFeeUsingCheckoutFee(checkoutFee, false);
            });

            //  this.saveOriginalState('Original order');

        },
        addCartFeeUsingOrderFee(orderFee) {
            this.shoppingCartForm.cartFees.push({
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

            const existingProduct = this.shoppingCartForm.cart_products.find(p =>
                p.name === orderProduct.name &&
                Number(p.unit_sale_price) === unitSalePrice &&
                Number(p.unit_regular_price) === unitRegularPrice
            );

            if (existingProduct) {

                // Increase quantity if product already exists
                existingProduct.quantity = (parseInt(existingProduct.quantity) + 1).toString();

            } else {

                // Add as a new product
                this.shoppingCartForm.cart_products.push({
                    'name': orderProduct.name,
                    'id': orderProduct.product_id,
                    'is_free': orderProduct.is_free,
                    'quantity': orderProduct.quantity.toString(),
                    'photo_file_path': photo ? photo.file_path : null,
                    'unit_weight': orderProduct.unit_weight.toString(),
                    'unit_sale_price': orderProduct.unit_sale_price.amount,
                    'unit_regular_price': orderProduct.unit_regular_price.amount
                });

            }
        },
        addCartProductUsingProduct(product) {

            const photo = product.photo;

            // Convert numeric values to numbers for consistent comparison
            const unitSalePrice = Number(product.unit_sale_price.amount);
            const unitRegularPrice = Number(product.unit_regular_price.amount);

            const existingProduct = this.shoppingCartForm.cart_products.find(p =>
                p.name === product.name &&
                Number(p.unit_sale_price) === unitSalePrice &&
                Number(p.unit_regular_price) === unitRegularPrice
            );

            if (existingProduct) {

                // Increase quantity if product already exists
                existingProduct.quantity = (parseInt(existingProduct.quantity) + 1).toString();

            } else {

                // Add as a new product
                this.shoppingCartForm.cart_products.push({
                    'quantity': '1',
                    'id': product.id,
                    'name': product.name,
                    'is_free': product.is_free,
                    'unit_weight': product.unit_weight.toString(),
                    'photo_file_path': photo ? photo.file_path : null,
                    'unit_sale_price': product.unit_sale_price.amount,
                    'unit_regular_price': product.unit_regular_price.amount
                });

            }

            this.saveState('Product added');
        },
        addCartProduct() {

            this.shoppingCartForm.cart_products.push({
                'name': '',
                'quantity': '1',
                'is_free': false,
                'unit_weight': '0',
                'photo_file_path': null,
                'unit_sale_price': '0.00',
                'unit_regular_price': '0.00'
            });

            this.saveState('Product added');

        },
        removeCartProduct(index) {
            this.shoppingCartForm.cart_products.splice(index, 1);
            this.saveState('Product removed');
        },

        addCartPromotionUsingOrderPromotion(orderPromotion) {

            const discountPercentageRate = Number(orderPromotion.discount_percentage_rate);
            const discountFlatRate = Number(orderPromotion.discount_flat_rate.amount);

            const existingPromotion = this.shoppingCartForm.cart_promotions.find(p =>
                p.name === orderPromotion.name &&
                Number(p.discount_flat_rate.amount) === discountFlatRate &&
                p.discount_rate_type === orderPromotion.discount_rate_type &&
                p.offer_discount === orderPromotion.offer_discount &&
                Number(p.discount_percentage_rate) === discountPercentageRate &&
                p.offer_free_delivery === orderPromotion.offer_free_delivery
            );

            if (!existingPromotion) {

                // Add as a new promotion
                this.shoppingCartForm.cart_promotions.push({
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

            const discountPercentageRate = Number(promotion.discount_percentage_rate);
            const discountFlatRate = Number(promotion.discount_flat_rate.amount_without_currency);

            const existingPromotion = this.shoppingCartForm.cart_promotions.find(p =>
                p.name === promotion.name &&
                Number(p.discount_flat_rate) === discountFlatRate &&
                p.discount_rate_type === promotion.discount_rate_type &&
                p.offer_discount === promotion.offer_discount &&
                Number(p.discount_percentage_rate) === discountPercentageRate &&
                p.offer_free_delivery === promotion.offer_free_delivery.status
            );

            if (!existingPromotion) {

                // Add as a new promotion
                this.shoppingCartForm.cart_promotions.push({
                    'name': promotion.name,
                    'offer_discount': promotion.offer_discount,
                    'discount_rate_type': promotion.discount_rate_type,
                    'offer_free_delivery': promotion.offer_free_delivery,
                    'discount_flat_rate': promotion.discount_flat_rate.amount_without_currency,
                    'discount_percentage_rate': promotion.discount_percentage_rate.toString()
                });

            }

            this.saveState('Promotion added');
        },
        addCartPromotion() {

            this.shoppingCartForm.cart_promotions.push({
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
            this.shoppingCartForm.cart_promotions.splice(index, 1);
            this.saveState('Promotion removed');
        },

        addCartFeeUsingOrderFee(orderFee) {
            this.shoppingCartForm.cart_fees.push({
                'active': true,
                'removable': false,
                'name': orderFee.name,
                'rate_type': orderFee.rate_type,
                'flat_rate': orderFee.amount.amount_without_currency,
                'percentage_rate': orderFee.percentage_rate.toString()
            });
        },
        addCartFeeUsingCheckoutFee(checkoutFee, active = true) {
            this.shoppingCartForm.cart_fees.push({
                'active': active,
                'removable': false,
                'name': checkoutFee.name,
                'rate_type': checkoutFee.rate_type,
                'flat_rate': checkoutFee.flat_rate.amount_without_currency,
                'percentage_rate': checkoutFee.percentage_rate.toString(),
            });
        },
        addCartFee() {
            console.log('addCartFee');
            this.shoppingCartForm.cart_fees.push({
                'name': 'Testing',
                'active': true,
                'flat_rate': '10',
                'removable': true,
                'rate_type': 'flat',
                'percentage_rate': '10',
            });
            this.saveState('Fee added');
        },
        removeCartFee(index) {
            this.shoppingCartForm.cart_fees.splice(index, 1);
            this.saveState('Fee removed');
        },





        addCartFeeUsingCheckoutFee(checkoutFee, active = true) {
            this.shoppingCartForm.cartFees.push({
                'active': active,
                'removable': false,
                'name': checkoutFee.name,
                'rate_type': checkoutFee.rate_type,
                'percentage_rate': checkoutFee.percentage_rate.toString(),
                'flat_rate': checkoutFee.flat_rate.amount_without_currency,
            });
        },
    }
});
