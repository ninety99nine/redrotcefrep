<template>

    <Modal
        triggerSize="xs"
        triggerType="light"
        header="Print PDF"
        triggerText="Print"
        approveType="primary"
        :leftApproveIcon="Printer"
        :leftTriggerIcon="Printer"
        approveText="Print PDF"
        :approveAction="printOrder"
        :approveLoading="isDownloadingOrder"
        :triggerLoading="isLoadingStore || isLoadingOrder">

        <template #content>

            <div class="flex space-x-2 p-4 text-xs bg-blue-100 rounded-lg">

                <Info size="20" class="shrink-0"></Info>

                <span>Creating the PDF to print may take a moment. Please do not close this window.</span>

            </div>

        </template>

    </Modal>

</template>

<script>

    import axios from 'axios';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import { Info, Printer } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Info, Modal, Button },
        data() {
            return {
                Printer,
                isDownloadingOrder: false
            }
        },
        computed: {
            order() {
                return this.orderState.order;
            },
            store() {
                return this.storeState.store;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
        },
        methods: {
            async printOrder(hideModal) {

                try {

                    if(this.isDownloadingOrder) return;

                    const data = {
                        store_id: this.store.id,
                        order_ids: [this.order.id]
                    };

                    const config = {
                        responseType: "blob"
                    };

                    this.isDownloadingOrder = true;

                    const response = await axios.post(`/api/orders/download`, data, config);

                    const blob = new Blob([response.data], { type: "application/pdf" });
                    const blobUrl = window.URL.createObjectURL(blob);

                    const printWindow = window.open(blobUrl);
                    if (printWindow) {
                        printWindow.onload = () => {
                            printWindow.focus();
                            printWindow.print();
                        };
                    }

                    hideModal();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while printing order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to print order:', error);
                } finally {
                    this.isDownloadingOrder = false;
                }

            }
        }
    };

</script>
