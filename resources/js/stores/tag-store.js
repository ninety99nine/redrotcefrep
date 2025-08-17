import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useTagStore = defineStore('tag', {
    state: () => ({
        tag: null,
        tagForm: null,
        isLoadingTag: false,
        isCreatingTag: false,
        isUpdatingTag: false,
        isDeletingTag: false,
    }),
    actions: {
        reset() {
            this.tag = null;
            this.tagForm = null;
            this.isLoadingTag = false;
            this.isCreatingTag = false;
            this.isUpdatingTag = false;
            this.isDeletingTag = false;
        },
        setTag(tag) {
            this.tag = tag;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.tagForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.tagForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.tagForm);
        },
        setTagForm(tag = null, saveState = true) {

            this.tagForm = {

                products: [],
                customers: [],
                id: tag?.id ?? null,
                name: tag?.name ?? null

            };

            if(tag) {

                tag.products.forEach((product) => {
                    this.addProduct(product, false);
                });

                tag.customers.forEach((customer) => {
                    this.addCustomer(customer, false);
                });

            }

            if(saveState) {
                this.saveOriginalState('Original tag');
            }

        },
        addProduct(product = null, saveState = true) {

            const exists = this.tagForm.products.some(p => p.id == product.id);
            if(exists) return;

            this.tagForm.products.push({

                id: product.id,
                name: product.name,
                photo: product.photo,
                visible: product.visible

            });

            if(saveState) this.saveStateDebounced('Product added');
        },
        addCustomer(customer = null, saveState = true) {

            const exists = this.tagForm.customers.some(p => p.id == customer.id);
            if(exists) return;

            this.tagForm.customers.push({

                id: customer.id,
                name: customer.name

            });

            if(saveState) this.saveStateDebounced('Customer added');
        },
        removeProduct(index) {
            this.tagForm.products.splice(index, 1);
            this.saveStateDebounced('Product removed');
        },
    },
    getters: {
        hasTag() {
            return this.tag != null;
        }
    }
});
