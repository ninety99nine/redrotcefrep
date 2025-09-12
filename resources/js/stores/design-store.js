import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useDesignStore = defineStore('design', {
    state: () => ({
        type: null,
        categories: [],
        designCards: [],
        categoryData: {},
        designForm: null,
        isUpdatingDesign: false,
        isLoadingDesignCards: false,
        hasLoadedInitialdesignCards: false
    }),
    actions: {
        reset() {
            this.type = null;
            this.categories = [];
            this.designCards = [];
            this.categoryData = {};
            this.designForm = null;
            this.isUpdatingDesign = false;
            this.hasLoadedInitialdesignCards = false;
            changeHistoryState().reset();
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.designForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.designForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.designForm);
        },
        setDesignForm(designForm) {
            this.designForm = designForm;
            this.saveOriginalState('Original design');
        },
    },
    getters: {
        designCardOptions() {

            const DESIGN_CARD_LIBRARY = {
                products:   { label: 'Products', value: 'products' },
                text:       { label: 'Text', value: 'text' },
                image:      { label: 'Image', value: 'image' },
                video:      { label: 'Video', value: 'video' },
                link:       { label: 'Link', value: 'link' },
                contact:    { label: 'Contact', value: 'contact' },
                countdown:  { label: 'Countdown', value: 'countdown' },
                map:        { label: 'Map', value: 'map' },
                socials:    { label: 'Socials', value: 'socials' },

                customer: { label: 'Customer', value: 'customer' },
                items:    { label: 'Items', value: 'items' },
                delivery:     { label: 'Delivery', value: 'delivery' },
                promoCode:    { label: 'Promo code', value: 'promo code' },
                tips:         { label: 'Tips', value: 'tips' },
                orderSummary:  { label: 'Order summary', value: 'order summary' },

                paymentMethods:  { label: 'Payment Methods', value: 'payment methods' },
            };

            if(this.type === 'storefront') {

                return [
                    DESIGN_CARD_LIBRARY.products,
                    DESIGN_CARD_LIBRARY.text,
                    DESIGN_CARD_LIBRARY.image,
                    DESIGN_CARD_LIBRARY.video,
                    DESIGN_CARD_LIBRARY.link,
                    DESIGN_CARD_LIBRARY.contact,
                    DESIGN_CARD_LIBRARY.countdown,
                    DESIGN_CARD_LIBRARY.map,
                    DESIGN_CARD_LIBRARY.socials
                ];

            }else if (this.type === 'checkout') {

                return [
                    DESIGN_CARD_LIBRARY.customer,
                    DESIGN_CARD_LIBRARY.items,
                    DESIGN_CARD_LIBRARY.delivery,
                    DESIGN_CARD_LIBRARY.promoCode,
                    DESIGN_CARD_LIBRARY.tips,
                    DESIGN_CARD_LIBRARY.orderSummary,

                    DESIGN_CARD_LIBRARY.products,
                    DESIGN_CARD_LIBRARY.text,
                    DESIGN_CARD_LIBRARY.image,
                    DESIGN_CARD_LIBRARY.video,
                    DESIGN_CARD_LIBRARY.link,
                    DESIGN_CARD_LIBRARY.contact,
                    DESIGN_CARD_LIBRARY.countdown,
                    DESIGN_CARD_LIBRARY.map,
                    DESIGN_CARD_LIBRARY.socials
                ];

            }else if (this.type === 'payment') {

                return [
                    DESIGN_CARD_LIBRARY.paymentMethods
                ];

            }

        }
    }
});
