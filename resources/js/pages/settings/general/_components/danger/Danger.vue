<template>

    <div :class="['flex items-end justify-between space-x-4 overflow-hidden rounded-lg p-4 border mb-20', isLoadingStore ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">

        <div class="space-y-2">
            <p>Delete <span class="font-bold text-black">{{ store?.name }}</span>?</p>
            <p class="text-sm">Once this store is deleted you will not be able to recover it. Orders, products, customers, and all other store-related settings will be permanently deleted</p>
        </div>

        <div class="flex justify-end">

            <Modal
                triggerType="danger"
                approveType="danger"
                :approveLeftIcon="Trash2"
                triggerText="Delete Store"
                approveText="Delete Store"
                :approveAction="deleteStore"
                :triggerLoading="isDeletingStore"
                :approveLoading="isDeletingStore">

                <template #content>
                    <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                    <p class="mb-4">Are you sure you want to permanently delete this store? Please confirm by typing <span class="font-bold text-black">{{ store?.name }}</span></p>

                    <!-- Name Text Input -->
                    <Input
                        type="text"
                        class="mb-4"
                        v-model="confirmation"
                        :placeholder="store?.name">
                    </Input>

                </template>

            </Modal>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import { Trash2 } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            Input, Modal
        },
        data() {
            return {
                Trash2,
                confirmation: '',
                isDeletingStore: false
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            storeForm() {
                return this.storeState.storeForm;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isDeletingStore() {
                return this.storeState.isDeletingStore;
            },
        },
        methods: {
            async navigateToShowStores() {
                await this.$router.replace({
                    name: 'show-stores'
                });
            },
            async deleteStore(hideModal) {

                try {

                    if(this.storeState.isDeletingStore) return;

                    this.storeState.isDeletingStore = true;

                    await axios.delete(`/api/stores/${this.store.id}`);

                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 500));    //  Wait for modal to close

                    this.notificationState.showSuccessNotification('Store deleted');

                    await this.navigateToShowStores();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete store:', error);
                    hideModal();
                } finally {
                    this.storeState.isDeletingStore = false;
                }

            },
        }
    };

</script>
