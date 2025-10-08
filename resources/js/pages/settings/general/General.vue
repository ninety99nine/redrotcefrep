<template>

    <div class="max-w-2xl mx-auto pt-24 pb-40">

        <!-- Basics -->
        <Basics></Basics>

        <!-- Communication -->
        <Communication></Communication>

        <!-- Localization -->
        <Localization></Localization>

        <!-- Tax -->
        <Tax></Tax>

        <!-- Open Hours -->
        <OpenHours></OpenHours>

        <!-- Danger -->
        <Danger></Danger>

    </div>

</template>

<script>

    import { isEmpty } from '@Utils/stringUtils';
    import Tax from '@Pages/settings/general/_components/tax/Tax.vue';
    import Basics from '@Pages/settings/general/_components/basics/Basics.vue';
    import Danger from '@Pages/settings/general/_components/danger/Danger.vue';
    import OpenHours from '@Pages/settings/general/_components/open-hours/OpenHours.vue';
    import Localization from '@Pages/settings/general/_components/localization/Localization.vue';
    import Communication from '@Pages/settings/general/_components/communication/Communication.vue';

    export default {
        inject: ['formState', 'storeState', 'changeHistoryState', 'notificationState'],
        components: {
            Tax, Basics, OpenHours, Danger, Communication, Localization
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            storeForm() {
                return this.storeState.storeForm;
            }
        },
        methods: {
            isEmpty: isEmpty,
            setup() {
                if(this.store) {
                    this.storeState.setStoreForm(this.store, true);
                }else{
                    this.storeState.setStoreForm(null, false);
                }
            },
            setActionButtons() {
                this.changeHistoryState.removeButtons();
                this.changeHistoryState.addDiscardButton();
                this.changeHistoryState.addActionButton(
                    'Save Changes',
                    this.updateStore,
                    'primary',
                    null,
                );
            },
            async updateStore() {

                try {

                    if(this.storeState.isUpdatingStore) return;

                    this.formState.hideFormErrors();

                    if(this.isEmpty(this.storeForm.name)) {
                        this.formState.setFormError('name', 'The name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.storeState.isUpdatingStore = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.storeForm,
                        store_id: this.store.id
                    }

                    await axios.put(`/api/stores/${this.store.id}`, data);

                    this.notificationState.showSuccessNotification(`Store updated`);
                    this.storeState.saveOriginalState('Original store');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update store:', error);
                } finally {
                    this.storeState.isUpdatingStore = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            setStoreForm(storeForm) {
                this.storeState.storeForm = storeForm;
            }
        },
        created() {

            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setStoreForm;
            }

        }
    };

</script>
