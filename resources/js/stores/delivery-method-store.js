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
                description: deliveryMethod?.description ?? '',
                charge_fee: deliveryMethod?.charge_fee ?? false,
                fee_type: deliveryMethod?.fee_type ?? 'flat fee',
                set_schedule: deliveryMethod?.set_schedule ?? false,
                distance_zones: deliveryMethod?.distance_zones ?? [],
                schedule_type: deliveryMethod?.schedule_type ?? 'date',
                operational_hours: deliveryMethod?.operational_hours ?? [],
                weight_categories: deliveryMethod?.weight_categories ?? [],
                postal_code_zones: deliveryMethod?.postal_code_zones ?? [],
                same_day_delivery: deliveryMethod?.same_day_delivery ?? false,
                daily_order_limit: deliveryMethod?.daily_order_limit ?? '100',
                ask_for_an_address: deliveryMethod?.ask_for_an_address ?? false,
                percentage_fee_rate: deliveryMethod?.percentage_fee_rate ?? '10',
                pin_location_on_map: deliveryMethod?.pin_location_on_map ?? false,
                data_collection_fields: deliveryMethod?.data_collection_fields ?? [],
                set_daily_order_limit: deliveryMethod?.set_daily_order_limit ?? false,
                time_slot_interval_value: deliveryMethod?.time_slot_interval_value ?? '1',
                time_slot_interval_unit: deliveryMethod?.time_slot_interval_unit ?? 'hour',
                show_distance_on_invoice: deliveryMethod?.show_distance_on_invoice ?? true,
                auto_generate_time_slots: deliveryMethod?.auto_generate_time_slots ?? false,
                latest_delivery_time_value: deliveryMethod?.latest_delivery_time_value ?? '1',
                flat_fee_rate: deliveryMethod?.flat_fee_rate?.amount_without_currency ?? '0.00',
                earliest_delivery_time_unit: deliveryMethod?.earliest_delivery_time_unit ?? 'day',
                earliest_delivery_time_value: deliveryMethod?.earliest_delivery_time_value ?? '1',
                qualify_on_minimum_grand_total: deliveryMethod?.qualify_on_minimum_grand_total ?? false,
                minimum_grand_total: deliveryMethod?.minimum_grand_total?.amount_without_currency ?? '0.00',
                require_minimum_notice_for_orders: deliveryMethod?.require_minimum_notice_for_orders ?? false,
                restrict_maximum_notice_for_orders: deliveryMethod?.restrict_maximum_notice_for_orders ?? false,
                offer_free_delivery_on_minimum_grand_total: deliveryMethod?.offer_free_delivery_on_minimum_grand_total ?? false,
                free_delivery_minimum_grand_total: deliveryMethod?.free_delivery_minimum_grand_total?.amount_without_currency ?? '0.00',
            };


            if(this.deliveryMethodForm.operational_hours.length == 0) {

                for (let index = 0; index < 7; index++) {
                    this.deliveryMethodForm.operational_hours.push({
                        available: false,
                        hours: [
                            ['08:00', '16:00']
                        ]
                    });
                }

            }
            if(saveState) {
                this.saveOriginalState('Original payment method');
            }

        }
    },
});
