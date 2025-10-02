import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useStoreStore = defineStore('store', {
    state: () => ({
        store: null,
        storeForm: null,
        silentUpdate: null,
        isUploading: false,
        isLoadingStore: false,
        isUpdatingStore: false,
        isDeletingStore: false,
    }),
    actions: {
        reset() {
            this.store = null;
            this.storeForm = null;
            this.isUploading = false;
            this.isLoadingStore = false;
            this.isUpdatingStore = false;
            this.isDeletingStore = false;
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

                order_number_prefix: store?.order_number_prefix ?? null,
                order_number_suffix: store?.order_number_suffix ?? null,
                order_number_padding: store?.order_number_padding?.toString() ?? null,
                order_number_counter: store?.order_number_counter?.toString() ?? null,

                message_footer: store?.message_footer ?? null,
                show_sms_channel: store?.show_sms_channel ?? false,
                show_line_channel: store?.show_line_channel ?? false,
                show_whatsapp_channel: store?.show_whatsapp_channel ?? true,
                line_channel_username: store?.line_channel_username ?? null,
                show_telegram_channel: store?.show_telegram_channel ?? false,
                show_messenger_channel: store?.show_messenger_channel ?? false,
                telegram_channel_username: store?.telegram_channel_username ?? null,
                messenger_channel_username: store?.messenger_channel_username ?? null,

                logo: store?.logo ? [store.logo] : [],
                seo_image: store?.seo_image ? [store.seo_image] : [],
                seo_title: store?.seo_title ?? null,
                seo_description: store?.seo_description ?? null,
                seo_keywords: store?.seo_keywords ?? [],
                google_analytics_id: store?.google_analytics_id ?? null,
                meta_pixel_id: store?.meta_pixel_id ?? null,
                tiktok_pixel_id: store?.tiktok_pixel_id ?? null,


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
