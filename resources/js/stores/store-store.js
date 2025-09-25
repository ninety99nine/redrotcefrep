import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useStoreStore = defineStore('store', {
    state: () => ({
        store: null,
        storeForm: null,
        silentUpdate: null,
        isLoadingStore: false,
        isUpdatingStore: false,
        isDeletingStore: false,
    }),
    actions: {
        reset() {
            this.store = null;
            this.storeForm = null;
            changeHistoryState().reset();
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.storeForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.storeForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.storeForm);
        },
        setStore(store) {
            this.store = store;
        },
        setIsLoadingStore(isLoadingStore) {
            this.isLoadingStore = isLoadingStore;
        },
        setIsUpdatingStore(isUpdatingStore) {
            this.isUpdatingStore = isUpdatingStore;
        },
        setStoreForm(store = null, saveState = true) {

            this.storeForm = {
                name: store?.name ?? null,
                online: store?.online ?? false,
                description: store?.description ?? null,
                offline_message: store?.offline_message ?? null,

                alias: store?.alias ?? null,
                email: store?.email ?? null,
                sms_sender_name: store?.sms_sender_name ?? null,
                ussd_mobile_number: store?.ussd_mobile_number?.international ?? null,
                whatsapp_mobile_number: store?.whatsapp_mobile_number?.international ?? null,

                country: store?.country ?? null,
                currency: store?.currency ?? null,
                language: store?.language ?? null,
                weight_unit: store?.weight_unit ?? null,
                distance_unit: store?.distance_unit ?? null,

                tax_id: store?.tax_id ?? null,
                tax_method: store?.tax_method ?? null,
                tax_percentage_rate: store?.tax_percentage_rate ?? null,

                opening_hours: store?.opening_hours ?? [],
                show_opening_hours: store?.show_opening_hours ?? false,
                allow_checkout_on_closed_hours: store?.allow_checkout_on_closed_hours ?? false,
            };

            if(this.storeForm.opening_hours.length == 0) {

                for (let index = 0; index < 7; index++) {
                    this.storeForm.opening_hours.push({
                        available: false,
                        hours: [
                            ['08:00', '16:00']
                        ]
                    });
                }

            }

            if(saveState) {
                this.saveOriginalState('Original store');
            }

        },
    },
});
