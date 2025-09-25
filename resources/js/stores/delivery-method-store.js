import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useDeliveryMethodStore = defineStore('deliveryMethod', {
    state: () => ({
        deliveryMethod: null,
        deliveryMethodForm: null,
        isLoadingDeliveryMethod: false,
        isCreatingDeliveryMethod: false,
        isUpdatingDeliveryMethod: false,
        isDeletingDeliveryMethod: false,
    }),
    actions: {
        reset() {
            this.deliveryMethod = null;
            this.deliveryMethodForm = null;
            this.isLoadingDeliveryMethod = false;
            this.isCreatingDeliveryMethod = false;
            this.isUpdatingDeliveryMethod = false;
            this.isDeletingDeliveryMethod = false;
            changeHistoryState().reset();
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.deliveryMethodForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.deliveryMethodForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.deliveryMethodForm);
        },
        setDeliveryMethod(deliveryMethod) {
            this.deliveryMethod = deliveryMethod;
        },
        setDeliveryMethodForm(deliveryMethod = null, saveState = true) {

            // Initialize deliveryMethodForm with defaults
            this.deliveryMethodForm = {
                name: deliveryMethod?.name ?? '',
                active: deliveryMethod?.active ?? true,
                address: deliveryMethod?.address ?? null,
                charge_fee: deliveryMethod?.charge_fee ?? true,
                description: deliveryMethod?.description ?? '',
                fee_type: deliveryMethod?.fee_type ?? 'flat fee',
                distance_zones: deliveryMethod?.distance_zones ?? [],
                flat_fee_rate: deliveryMethod?.flat_fee_rate ?? '0.00',
                weight_categories: deliveryMethod?.weight_categories ?? [],
                postal_code_zones: deliveryMethod?.postal_code_zones ?? [],
                daily_order_limit: deliveryMethod?.daily_order_limit ?? '100',
                percentage_fee_rate: deliveryMethod?.percentage_fee_rate ?? '10',
                minimum_grand_total: deliveryMethod?.minimum_grand_total ?? '0.00',
                set_daily_order_limit: deliveryMethod?.set_daily_order_limit ?? false,
                show_distance_on_invoice: deliveryMethod?.show_distance_on_invoice ?? true,
                qualify_on_minimum_grand_total: deliveryMethod?.qualify_on_minimum_grand_total ?? false,
                free_delivery_minimum_grand_total: deliveryMethod?.free_delivery_minimum_grand_total ?? '0.00',
                offer_free_delivery_on_minimum_grand_total: deliveryMethod?.offer_free_delivery_on_minimum_grand_total ?? false,
            };

            if(saveState) {
                this.saveOriginalState('Original payment method');
            }

        }
    },
});
