import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useOrderStore = defineStore('order', {
    state: () => ({
        order: null,
        orderForm: null,
        isLoadingOrder: false
    }),
    actions: {
        setOrder(order) {
            this.order = order;
        },
        setIsLoadingOrder(isLoadingOrder) {
            this.isLoadingOrder = isLoadingOrder;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.orderForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.orderForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.orderForm);
        },
        setOrderForm(order) {

            this.orderForm = {
                remark: order.remark,
                status: order.status,
                courier_id: order.courier_id,
                internal_note: order.internal_note,
                payment_status: order.payment_status,
                tracking_number: order.tracking_number,
                assigned_to_user_id: order.assigned_to_user_id,
            };

            this.saveOriginalState('Original order');

        },
    },
    getters: {
        hasOrder() {
            return this.order != null;
        }
    }
});
