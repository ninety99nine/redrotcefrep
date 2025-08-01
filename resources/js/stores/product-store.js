import { defineStore } from 'pinia';
import isEqual from 'lodash/isEqual';
import cloneDeep from 'lodash/cloneDeep';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useProductStore = defineStore('product', {
    state: () => ({
        product: null,
        productForm: null,
        isLoadingProduct: false,
        isCreatingProduct: false,
        isUpdatingProduct: false,
        isDeletingProduct: false,
        isCreatingVariations: false,
        originalVariantAttributes: [],
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
                categories: [],
                variant_attributes: [],

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
                allow_variations: product?.allow_variations ?? false,
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

                product.variant_attributes.forEach((variantAttribute) => {
                    this.addVariantAttributeUsingVariantAttribute(variantAttribute);
                });

            }

            this.originalVariantAttributes = cloneDeep(this.productForm.variant_attributes);

            if(saveState) {
                this.saveOriginalState('Original product');
            }

        },
        addVariantAttributeUsingVariantAttribute(variantAttribute) {
            this.productForm.variant_attributes.push({
                is_editable: false,
                ...variantAttribute
            });
        },
        onAddVariantAttribute() {

            // Check if 'Size' variant attribute exists
            if(!this.productForm.variant_attributes.some(attribute => attribute.name.toLowerCase() === 'size')) {
                this.productForm.variant_attributes.push({
                    'name': 'Size',
                    'is_editable': true,
                    'instruction': 'Select your size',
                    'values': ['Small', 'Medium', 'Large'],
                });
            }
            // Check if 'Colour' variant attribute exists
            else if(!this.productForm.variant_attributes.some(attribute => attribute.name.toLowerCase() === 'colour')) {
                this.productForm.variant_attributes.push({
                    'name': 'Colour',
                    'is_editable': true,
                    'instruction': 'Select your colour',
                    'values': ['Red', 'Blue', 'Green'],
                });
            }
            // Check if 'Material' variant attribute exists
            else if(!this.productForm.variant_attributes.some(attribute => attribute.name.toLowerCase() === 'material')) {
                this.productForm.variant_attributes.push({
                    'name': 'Material',
                    'is_editable': true,
                    'instruction': 'Select your material',
                    'values': ['Cotton', 'Nylon'],
                });
            }
            // Add a default variant attribute if none of the above conditions are met
            else {
                this.productForm.variant_attributes.push({
                    'name': '',
                    'is_editable': true,
                    'instruction': '',
                    'values': [],
                });
            }
            this.saveStateDebounced('Added variant attribute');
        },
        onRemoveVariantAttribute(index) {
            this.productForm.variant_attributes.splice(index, 1);
            this.saveStateDebounced('Removed variant attribute');
        },
        onResetVariantAttributes() {
            this.productForm.variant_attributes = cloneDeep(this.originalVariantAttributes);
            this.saveStateDebounced('Reset variant attributes');
        },
    },
    getters: {
        hasProduct() {
            return this.product != null;
        },
        hasVariantAttributes() {
            return this.productForm.variant_attributes.length > 0;
        },
        hasOriginalVariantAttributes() {
            return this.originalVariantAttributes.length > 0;
        },
        variantAttributesHaveChanged() {
            // Clone the arrays to avoid modifying the original data
            var a = cloneDeep(this.productForm.variant_attributes);
            var b = cloneDeep(this.originalVariantAttributes);

            // Loop through each object in the array and delete the property
            a.forEach(obj => delete obj.is_editable);
            b.forEach(obj => delete obj.is_editable);

            // Compare the modified arrays for equality
            return !isEqual(a, b);
        }
    }
});
