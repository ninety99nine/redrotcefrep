import { defineStore } from 'pinia';

export const useShopStore = defineStore('shop', {
    state: () => ({
        showDrawer: null,
        hideDrawer: null
    })
});
