import { defineStore } from 'pinia';
import { v4 as uuidv4 } from 'uuid';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useProductStore = defineStore('product', {
    state: () => ({
        product: null,
        productForm: null,
        isLoadingProduct: false,
        isCreatingProduct: false,
        isUpdatingProduct: false,
        isDeletingProduct: false,
        isCreatingVariants: false,
        isChangingVariantArrangement: false,
    }),
    actions: {
        reset() {
            this.product = null;
            this.productForm = null;
            this.isLoadingProduct = false;
            this.originalVariantAttributes = [];
        },
        setProduct(product) {
            this.product = product;
        },
        setIsLoadingProduct(isLoadingProduct) {
            this.isLoadingProduct = isLoadingProduct;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.productForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.productForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.productForm);
        },
        setProductForm(product = null, saveState = true) {

            this.productForm = {

                tags: [],
                variants: [],
                categories: [],
                id: product?.id ?? null,
                sku: product?.sku ?? null,
                name: product?.name ?? null,
                photos: product?.photos ?? [],
                type: product?.type ?? 'physical',
                barcode: product?.barcode ?? null,
                visible: product?.visible ?? true,
                is_free: product?.is_free ?? false,
                description: product?.description ?? null,
                tax_overide: product?.tax_overide ?? false,
                download_link: product?.download_link ?? null,
                unit_type: product?.unit_type?.toString() ?? 'qty',
                unit_value: product?.unit_value?.toString() ?? '1',
                show_description: product?.show_description ?? false,
                unit_weight: product?.unit_weight?.toString() ?? '0.00',
                is_estimated_price: product?.is_estimated_price ?? false,
                set_daily_capacity: product?.set_daily_capacity ?? false,
                show_price_per_unit: product?.show_price_per_unit ?? false,
                daily_capacity: product?.daily_capacity?.toString() ?? '1',
                stock_quantity: product?.stock_quantity?.toString() ?? '100',
                stock_quantity_type: product?.stock_quantity_type ?? 'unlimited',
                set_min_order_quantity: product?.set_min_order_quantity ?? false,
                set_max_order_quantity: product?.set_max_order_quantity ?? false,
                min_order_quantity: product?.min_order_quantity?.toString() ?? '1',
                max_order_quantity: product?.max_order_quantity?.toString() ?? '1',
                unit_cost_price: product?.unit_cost_price.amount_without_currency ?? '0.00',
                unit_sale_price: product?.unit_sale_price.amount_without_currency ?? '0.00',
                unit_regular_price: product?.unit_regular_price.amount_without_currency ?? '0.00',
                tax_overide_amount: product?.tax_overide_amount?.amount_without_currency ?? '0.00',

            };

            if(product) {

                product.tags.forEach((tag) => {
                    this.productForm.tags.push(tag.id);
                });

                product.categories.forEach((category) => {
                    this.productForm.categories.push(category.id);
                });

                product.variants.forEach((variant) => {
                    this.addVariant(variant);
                });

            }

            if(saveState) {
                this.saveOriginalState('Original product');
            }

        },
        addVariant(variant = null) {

            this.productForm.variants.push({

                id: variant?.id ?? null,
                sku: variant?.sku ?? null,
                name: variant?.name ?? null,
                photos: variant?.photos ?? [],
                visible: variant?.visible ?? true,
                barcode: variant?.barcode ?? null,
                unit_cost_price: variant?.unit_cost_price.amount_without_currency ?? '0.00',
                unit_sale_price: variant?.unit_sale_price.amount_without_currency ?? '0.00',
                unit_regular_price: variant?.unit_regular_price.amount_without_currency ?? '0.00',

                show_more: false,
                temporary_id: uuidv4()

            });

            if(!variant) this.saveStateDebounced('Added variant');
        },
        removeVariant(index) {
            this.productForm.variants.splice(index, 1);
            this.saveStateDebounced('Removed variant');
        },
    },
    getters: {
        hasProduct() {
            return this.product != null;
        },
        hasVariants() {
            return this.productForm.variants.length > 0;
        }
    }
});
