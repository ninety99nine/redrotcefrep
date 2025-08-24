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
        setTagForm(tag = null) {

            this.tagForm = {

                id: tag?.id ?? null,
                name: tag?.name ?? null,

                products_to_add: [],
                products_to_remove: [],

                customers_to_add: [],
                customers_to_remove: []

            };

        },
        addProduct(product, saveState = true) {
            const exists = this.tagForm.products_to_add.some(currProduct => currProduct.id == product.id);
            if(exists) return;

            const waitingToBeRemoved = this.tagForm.products_to_remove.some(currProduct => currProduct.id == product.id);

            if(waitingToBeRemoved) {
                const index = this.tagForm.products_to_remove.findIndex((currProduct) => currProduct.id == product.id);
                this.tagForm.products_to_remove.splice(index, 1);
            }

            this.tagForm.products_to_add.push({
                id: product.id,
                name: product.name,
                photo: product.photo,
                visible: product.visible,
            });

            if(saveState) this.saveStateDebounced('Product added');
        },
        removeProduct(product) {
            const waitingToBeAdded = this.tagForm.products_to_add.some(currProduct => currProduct.id == product.id);

            if(waitingToBeAdded) {
                const index = this.tagForm.products_to_add.findIndex((currProduct) => currProduct.id == product.id);
                this.tagForm.products_to_add.splice(index, 1);
            }

            this.tagForm.products_to_remove.push({
                id: product.id
            });

            this.saveStateDebounced('Product removed');
        },
        addCustomer(customer, saveState = true) {
            const exists = this.tagForm.customers_to_add.some(currCustomer => currCustomer.id == customer.id);
            if(exists) return;

            const waitingToBeRemoved = this.tagForm.customers_to_remove.some(currCustomer => currCustomer.id == customer.id);

            if(waitingToBeRemoved) {
                const index = this.tagForm.customers_to_remove.findIndex((currCustomer) => currCustomer.id == customer.id);
                this.tagForm.customers_to_remove.splice(index, 1);
            }

            this.tagForm.customers_to_add.push({
                id: customer.id,
                name: customer.name,
                email: customer.email,
                mobile_number: customer.mobile_number
            });

            if(saveState) this.saveStateDebounced('Customer added');
        },
        removeCustomer(customer) {
            const waitingToBeAdded = this.tagForm.customers_to_add.some(currCustomer => currCustomer.id == customer.id);

            if(waitingToBeAdded) {
                const index = this.tagForm.customers_to_add.findIndex((currCustomer) => currCustomer.id == customer.id);
                this.tagForm.customers_to_add.splice(index, 1);
            }

            this.tagForm.customers_to_remove.push({
                id: customer.id
            });

            this.saveStateDebounced('Customer removed');
        },
    },
    getters: {
        hasTag() {
            return this.tag != null;
        }
    }
});
