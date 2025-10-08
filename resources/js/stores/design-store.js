import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useDesignStore = defineStore('design', {
    state: () => ({
        categories: [],
        designForm: null,
        isLoadingDesignCards: false,
        isUpdatingDesignCards: false
    }),
    actions: {
        reset() {
            this.categories = [];
            this.designForm = null;
            this.isLoadingDesignCards = false;
            this.isUpdatingDesignCards = false;
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
        setDesignForm(design, store) {

            design.design_cards = design.design_cards.map((designCard) => {

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

                designCard['mode'] = '1';
                designCard['expanded'] = false;

                return designCard;

            });

            design['store_settings'] = {
                tips: store.tips,
                combine_fees: store.combine_fees,
                combine_discounts: store.combine_discounts,
                checkout_fees: (store.checkout_fees ?? []).map((checkoutFee) => {
                    return {
                        'name': checkoutFee.name,
                        'rate_type': checkoutFee.rate_type,
                        'percentage_rate': checkoutFee.percentage_rate,
                        'flat_rate': checkoutFee.flat_rate.amount_without_currency,
                    }
                })
            }

            this.designForm = design;

            this.saveOriginalState('Original design');
        },
    }
});
