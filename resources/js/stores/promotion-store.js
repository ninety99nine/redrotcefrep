import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const usePromotionStore = defineStore('promotion', {
    state: () => ({
        promotion: null,
        promotionForm: null,
        promotionForms: [],
        isUploading: false,
        isLoadingPromotion: false,
        isCreatingPromotion: false,
        isUpdatingPromotion: false,
        isDeletingPromotion: false,
    }),
    actions: {
        reset() {
            this.promotion = null;
            this.promotionForm = null;
            this.promotionForms = [];
            this.isUploading = false;
            this.isLoadingPromotion = false;
            this.isCreatingPromotion = false;
            this.isUpdatingPromotion = false;
            this.isDeletingPromotion = false;
            changeHistoryState().reset();
        },
        setPromotion(promotion) {
            this.promotion = promotion;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.promotionForm ?? this.promotionForms);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.promotionForm ?? this.promotionForms);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.promotionForm ?? this.promotionForms);
        },
        setPromotionForm(promotion = null, saveState = true) {
            this.promotionForm = {
                id: promotion?.id ?? null,
                name: promotion?.name ?? null,
                code: promotion?.code ?? null,
                active: promotion?.active ?? true,
                description: promotion?.description ?? null,
                offer_discount: promotion?.offer_discount ?? false,
                discount_rate_type: promotion?.discount_rate_type ?? 'flat',
                discount_percentage_rate: promotion?.discount_percentage_rate?.toString() ?? '0',
                discount_flat_rate: promotion?.discount_flat_rate?.amount_without_currency ?? '0.00',
                offer_free_delivery: promotion?.offer_free_delivery ?? false,
                activate_using_code: promotion?.activate_using_code ?? false,
                activate_for_new_customer: promotion?.activate_for_new_customer ?? false,
                activate_for_existing_customer: promotion?.activate_for_existing_customer ?? false,
                activate_using_usage_limit: promotion?.activate_using_usage_limit ?? false,
                remaining_quantity: promotion?.remaining_quantity?.toString() ?? '0',
                activate_using_minimum_grand_total: promotion?.activate_using_minimum_grand_total ?? false,
                minimum_grand_total: promotion?.minimum_grand_total?.amount_without_currency ?? '0.00',
                activate_using_minimum_total_products: promotion?.activate_using_minimum_total_products ?? false,
                minimum_total_products: promotion?.minimum_total_products?.toString() ?? '1',
                activate_using_minimum_total_product_quantities: promotion?.activate_using_minimum_total_product_quantities ?? false,
                minimum_total_product_quantities: promotion?.minimum_total_product_quantities?.toString() ?? '1',
                activate_using_start_datetime: promotion?.activate_using_start_datetime ?? false,
                start_datetime: promotion?.start_datetime ?? null,
                activate_using_end_datetime: promotion?.activate_using_end_datetime ?? false,
                end_datetime: promotion?.end_datetime ?? null,
                activate_using_hours_of_day: promotion?.activate_using_hours_of_day ?? false,
                hours_of_day: promotion?.hours_of_day ?? [],
                activate_using_days_of_the_week: promotion?.activate_using_days_of_the_week ?? false,
                days_of_the_week: promotion?.days_of_the_week ?? [],
                activate_using_days_of_the_month: promotion?.activate_using_days_of_the_month ?? false,
                days_of_the_month: promotion?.days_of_the_month ?? [],
                activate_using_months_of_the_year: promotion?.activate_using_months_of_the_year ?? false,
                months_of_the_year: promotion?.months_of_the_year ?? [],
            };

            if (saveState) {
                this.saveOriginalState('Original promotion');
            }
        },
        setPromotionForms(promotions = [], saveState = true) {
            this.promotionForms = promotions.map((promotion) => ({
                id: promotion.id,
                name: promotion.name,
                code: promotion.code,
                active: promotion.active,
                description: promotion.description,
                offer_discount: promotion.offer_discount,
                discount_rate_type: promotion.discount_rate_type,
                discount_percentage_rate: promotion.discount_percentage_rate?.toString() ?? '0',
                discount_flat_rate: promotion.discount_flat_rate?.amount_without_currency ?? '0.00',
                offer_free_delivery: promotion.offer_free_delivery,
                activate_using_code: promotion.activate_using_code,
                activate_for_new_customer: promotion.activate_for_new_customer,
                activate_for_existing_customer: promotion.activate_for_existing_customer,
                activate_using_usage_limit: promotion.activate_using_usage_limit,
                remaining_quantity: promotion.remaining_quantity?.toString() ?? '0',
                activate_using_minimum_grand_total: promotion.activate_using_minimum_grand_total,
                minimum_grand_total: promotion.minimum_grand_total?.amount_without_currency ?? '0.00',
                activate_using_minimum_total_products: promotion.activate_using_minimum_total_products,
                minimum_total_products: promotion.minimum_total_products?.toString() ?? '1',
                activate_using_minimum_total_product_quantities: promotion.activate_using_minimum_total_product_quantities,
                minimum_total_product_quantities: promotion.minimum_total_product_quantities?.toString() ?? '1',
                activate_using_start_datetime: promotion.activate_using_start_datetime,
                start_datetime: promotion.start_datetime,
                activate_using_end_datetime: promotion.activate_using_end_datetime,
                end_datetime: promotion.end_datetime,
                activate_using_hours_of_day: promotion.activate_using_hours_of_day,
                hours_of_day: promotion.hours_of_day ?? [],
                activate_using_days_of_the_week: promotion.activate_using_days_of_the_week,
                days_of_the_week: promotion.days_of_the_week ?? [],
                activate_using_days_of_the_month: promotion.activate_using_days_of_the_month,
                days_of_the_month: promotion.days_of_the_month ?? [],
                activate_using_months_of_the_year: promotion.activate_using_months_of_the_year,
                months_of_the_year: promotion.months_of_the_year ?? [],
                modified: false
            }));

            if (saveState) {
                this.saveOriginalState('Original promotions');
            }
        }
    },
    getters: {
        hasPromotion() {
            return this.promotion != null;
        }
    }
});
