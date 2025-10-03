import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useDomainStore = defineStore('domain', {
    state: () => ({
        domain: null,
        domainForm: null,
        isUploading: false,
        isLoadingDomain: false,
        isUpdatingDomain: false,
        isDeletingDomain: false,
        isVerifyingDomain: false,
    }),
    actions: {
        reset() {
            this.domain = null;
            this.domainForm = null;
            this.isUploading = false;
            this.isLoadingDomain = false;
            this.isUpdatingDomain = false;
            this.isDeletingDomain = false;
            this.isVerifyingDomain = false;
            changeHistoryState().reset();
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.domainForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.domainForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.domainForm);
        },
        setDomain(domain) {
            this.domain = domain;
        },
        setDomainForm(domain = null, saveState = true) {

            this.domainForm = {
                name: domain?.name ?? null
            };

            if(saveState) {
                this.saveOriginalState('Original domain');
            }

        },
    },
});
