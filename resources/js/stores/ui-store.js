import { defineStore } from 'pinia';

export const useUiStore = defineStore('ui', {
    state: () => ({
        isLoading: false,
    }),
    actions: {
        showLoader() {
            this.isLoading = true;
        },
        hideLoader() {
            this.isLoading = false;
        },
    },
});
