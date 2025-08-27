import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useCustomerStore = defineStore('customer', {
    state: () => ({
        customer: null,
        customerForms: [],
        customerForm: null,
        isLoadingCustomer: false,
        isCreatingCustomer: false,
        isUpdatingCustomer: false,
        isDeletingCustomer: false
    }),
    actions: {
        reset() {
            this.customer = null;
            this.customerForms = [];
            this.customerForm = null;
            this.isLoadingCustomer = false;
            this.isCreatingCustomer = false;
            this.isUpdatingCustomer = false;
            this.isDeletingCustomer = false;
            changeHistoryState().reset();
        },
        setCustomer(customer) {
            this.customer = customer;
        },
        setIsLoadingCustomer(isLoadingCustomer) {
            this.isLoadingCustomer = isLoadingCustomer;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.customerForm ?? this.customerForms);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.customerForm ?? this.customerForms);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.customerForm ?? this.customerForms);
        },
        setCustomerForm(customer = null, saveState = true) {

            this.customerForm = {

                tags: [],
                id: customer?.id ?? null,
                name: customer?.name ?? null,
                email: customer?.email ?? null,
                notes: customer?.notes ?? null,
                address: customer?.address ?? null,
                birthday: customer?.birthday ?? null,
                last_name: customer?.last_name ?? null,
                first_name: customer?.first_name ?? null,
                referral_code: customer?.referral_code ?? null,
                mobile_number: customer?.mobile_number?.international ?? null,

            };

            if(customer) {

                customer.tags.forEach((tag) => {
                    this.customerForm.tags.push(tag.id);
                });

            }

            if(saveState) {
                this.saveOriginalState('Original customer');
            }

        },
        setCustomerForms(customers = [], saveState = true) {

            this.customerForms = customers.flatMap((customer) => {

                return {
                    tags: [],
                    id: customer.id,
                    name: customer.name,
                    email: customer.email ?? null,
                    notes: customer.notes ?? null,
                    first_name: customer.first_name,
                    address: customer.address ?? null,
                    birthday: customer.birthday ?? null,
                    last_name: customer.last_name ?? null,
                    referral_code: customer.referral_code ?? null,
                    mobile_number: customer.mobile_number?.international ?? null,

                    modified: false,
                };
            });

            if (saveState) {
                this.saveOriginalState('Original customers');
            }
        }
    },
    getters: {
        hasCustomer() {
            return this.customer != null;
        }
    }
});
