import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useCategoryStore = defineStore('category', {
    state: () => ({
        category: null,
        categoryForm: null,
        isUploading: false,
        isLoadingCategory: false,
        isCreatingCategory: false,
        isUpdatingCategory: false,
        isDeletingCategory: false,
    }),
    actions: {
        reset() {
            this.category = null;
            this.categoryForm = null;
            this.isUploading = false;
            this.isLoadingCategory = false;
            this.isCreatingCategory = false;
            this.isUpdatingCategory = false;
            this.isDeletingCategory = false;
            changeHistoryState().reset();
        },
        setCategory(category) {
            this.category = category;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.categoryForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.categoryForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.categoryForm);
        },
        setCategoryForm(category = null, saveState = true) {

            this.categoryForm = {

                products: [],
                id: category?.id ?? null,
                name: category?.name ?? null,
                photos: category?.photos ?? [],
                visible: category?.visible ?? true,
                description: category?.description ?? null,

            };

            if(category) {

                category.products.forEach((product) => {
                    this.addProduct(product, false);
                });

            }

            if(saveState) {
                this.saveOriginalState('Original category');
            }

        },
        addProduct(product, saveState = true) {

            const exists = this.categoryForm.products.some(p => p.id == product.id);
            if(exists) return;

            this.categoryForm.products.push({

                id: product.id,
                name: product.name,
                photo: product.photo,
                visible: product.visible

            });

            if(saveState) this.saveStateDebounced('Product added');
        },
        removeProduct(index) {
            this.categoryForm.products.splice(index, 1);
            this.saveStateDebounced('Product removed');
        },
    },
    getters: {
        hasCategory() {
            return this.category != null;
        }
    }
});
