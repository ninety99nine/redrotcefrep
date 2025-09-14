import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useDesignStore = defineStore('design', {
    state: () => ({
        categories: [],
        placement: null,
        designCards: [],
        categoryData: {},
        designForm: null,
        isUpdatingDesign: false,
        isLoadingDesignCards: false,
    }),
    actions: {
        reset() {
            this.categories = [];
            this.placement = null;
            this.designCards = [];
            this.categoryData = {};
            this.designForm = null;
            this.isUpdatingDesign = false;
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

            designForm.design_cards = designForm.design_cards.map((designCard) => {

                /**
                 *  Some metadata fields must never be null, but instead of null must
                 *  be an empty string. This is because these fields are used by
                 *  the <vue-easymde> components which expects non-null values.
                 */
                const properties = ['body', 'upper_text', 'lower_text'];

                for (let index = 0; index < properties.length; index++) {
                    const property = properties[index];
                    if(designCard['metadata'].hasOwnProperty(property) && designCard['metadata'][property] == null) {
                        designCard['metadata'][property] = '';
                    }
                }

                return designCard;

            });

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

                dataCollectionField: { label: 'Field', value: 'data collection field' },
                customer: { label: 'Customer', value: 'customer' },
                items:    { label: 'Items', value: 'items' },
                delivery:     { label: 'Delivery', value: 'delivery' },
                promoCode:    { label: 'Promo code', value: 'promo code' },
                tips:         { label: 'Tips', value: 'tips' },
                orderSummary:  { label: 'Order summary', value: 'order summary' },

                paymentMethods:  { label: 'Payment Methods', value: 'payment methods' },
            };

            if(this.placement === 'storefront') {

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

            }else if (this.placement === 'checkout') {

                return [
                    DESIGN_CARD_LIBRARY.dataCollectionField,
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

            }else if (this.placement === 'payment') {

                return [
                    DESIGN_CARD_LIBRARY.paymentMethods
                ];

            }

        }
    }
});
