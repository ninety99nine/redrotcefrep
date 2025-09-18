import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useDesignStore = defineStore('design', {
    state: () => ({
        categories: [],
        categoryData: {},
        designForm: null,
        isUpdatingDesign: false,
        wantsToArrangeDesignCards: false,
    }),
    actions: {
        reset() {
            this.categories = [];
            this.categoryData = {};
            this.designForm = null;
            this.isUpdatingDesign = false;
            this.wantsToArrangeDesignCards = false;
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

                designCard['expanded'] = false;

                return designCard;

            });

            this.designForm = designForm;

            this.saveOriginalState('Original design');
        },
    }
});
