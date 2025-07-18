import { defineStore } from 'pinia';

export const useStoreStore = defineStore('store', {
    state: () => ({
        store: null,
        isLoadingStore: false
    }),
    actions: {
        setStore(store) {
            this.store = store;
        },
        setIsLoadingStore(isLoadingStore) {
            this.isLoadingStore = isLoadingStore;
        }
    },
});
