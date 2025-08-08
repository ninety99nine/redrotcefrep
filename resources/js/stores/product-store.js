import { defineStore } from 'pinia';
import { v4 as uuidv4 } from 'uuid';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useProductStore = defineStore('product', {
    state: () => ({
        product: null,
        productForms: [],
        productForm: null,
        isUploading: false,
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
            this.productForms = [];
            this.productForm = null;
            this.isUploading = false;
            this.isLoadingProduct = false;
            this.isCreatingProduct = false;
            this.isUpdatingProduct = false;
            this.isDeletingProduct = false;
            this.isCreatingVariants = false;
            this.isChangingVariantArrangement = false;
        },
        setProduct(product) {
            this.product = product;
        },
        setIsLoadingProduct(isLoadingProduct) {
            this.isLoadingProduct = isLoadingProduct;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.productForm ?? this.productForms);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.productForm ?? this.productForms);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.productForm ?? this.productForms);
        },
        setProductForm(product = null, saveState = true) {

            this.productForm = {

                tags: [],
                variants: [],
                categories: [],
                delivery_method_ids: [],
                id: product?.id ?? null,
                sku: product?.sku ?? null,
                data_collection_fields: [],
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

                product.delivery_methods.forEach((deliveryMethod) => {
                    this.productForm.delivery_method_ids.push(deliveryMethod.id);
                });

                product.variants.forEach((variant) => {
                    this.addVariant(variant);
                });

            }

            if(saveState) {
                this.saveOriginalState('Original product');
            }

        },
        setProductForms(products = [], saveState = true) {

            this.productForms = products.flatMap((product) => {

                const parent = {
                    id: product.id,
                    sku: product.sku,
                    name: product.name,
                    type: product.type,
                    photo: product.photo,
                    visible: product.visible,
                    is_free: product.is_free,
                    barcode: product.barcode,
                    tax_overide: product.tax_overide,
                    description: product.description,
                    download_link: product.download_link,
                    position: product.position.toString(),
                    unit_type: product.unit_type?.toString(),
                    unit_value: product.unit_value?.toString(),
                    show_description: product.show_description,
                    parent_product_id: product.parent_product_id,
                    unit_weight: product.unit_weight?.toString(),
                    is_estimated_price: product.is_estimated_price,
                    set_daily_capacity: product.set_daily_capacity,
                    show_price_per_unit: product.show_price_per_unit,
                    stock_quantity_type: product.stock_quantity_type,
                    daily_capacity: product.daily_capacity?.toString(),
                    stock_quantity: product.stock_quantity?.toString(),
                    set_min_order_quantity: product.set_min_order_quantity,
                    set_max_order_quantity: product.set_max_order_quantity,
                    min_order_quantity: product.min_order_quantity?.toString(),
                    max_order_quantity: product.max_order_quantity?.toString(),
                    unit_cost_price: product.unit_cost_price.amount_without_currency,
                    unit_sale_price: product.unit_sale_price.amount_without_currency,
                    unit_regular_price: product.unit_regular_price.amount_without_currency,
                    tax_overide_amount: product.tax_overide_amount?.amount_without_currency,

                    modified: false,
                    is_variant: false,
                    total_variants: product.variants.length,
                    has_variants: product.variants.length > 0,
                };

                const variants = product.variants.map((variant) => ({
                    id: variant.id,
                    sku: variant.sku,
                    type: variant.type,
                    name: variant.name,
                    photo: variant.photo,
                    visible: variant.visible,
                    is_free: variant.is_free,
                    barcode: variant.barcode,
                    tax_overide: variant.tax_overide,
                    description: variant.description,
                    download_link: variant.download_link,
                    position: variant.position.toString(),
                    unit_type: variant.unit_type?.toString(),
                    unit_value: variant.unit_value?.toString(),
                    show_description: variant.show_description,
                    parent_product_id: variant.parent_product_id,
                    unit_weight: variant.unit_weight?.toString(),
                    is_estimated_price: variant.is_estimated_price,
                    set_daily_capacity: variant.set_daily_capacity,
                    show_price_per_unit: variant.show_price_per_unit,
                    stock_quantity_type: variant.stock_quantity_type,
                    daily_capacity: variant.daily_capacity?.toString(),
                    stock_quantity: variant.stock_quantity?.toString(),
                    set_min_order_quantity: variant.set_min_order_quantity,
                    set_max_order_quantity: variant.set_max_order_quantity,
                    min_order_quantity: variant.min_order_quantity?.toString(),
                    max_order_quantity: variant.max_order_quantity?.toString(),
                    unit_cost_price: variant.unit_cost_price.amount_without_currency,
                    unit_sale_price: variant.unit_sale_price.amount_without_currency,
                    unit_regular_price: variant.unit_regular_price.amount_without_currency,
                    tax_overide_amount: variant.tax_overide_amount?.amount_without_currency,

                    modified: false,
                    is_variant: true,
                    total_variants: 0,
                    has_variants: false
                }));

                // Combine parent and its variants
                return [parent, ...variants];
            });

            if (saveState) {
                this.saveOriginalState('Original products');
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
