<template>

    <Modal
        triggerSize="xs"
        triggerType="light"
        header="Download PDF"
        triggerText="Download"
        approveType="primary"
        approveText="Download PDF"
        :leftApproveIcon="ArrowDownToLine"
        :leftTriggerIcon="ArrowDownToLine"
        :approveAction="downloadOrder"
        :approveLoading="isDownloadingOrder"
        :triggerLoading="isLoadingStore || isLoadingOrder">

        <template #content>

            <div class="flex space-x-2 p-4 text-xs bg-blue-100 rounded-lg">

                <Info size="20" class="shrink-0"></Info>

                <span>Creating the PDF to download may take a moment. Please do not close this window.</span>

            </div>

        </template>

    </Modal>

</template>

<script>

    import axios from 'axios';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import { Info, ArrowDownToLine } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Info, Button, Modal },
        data() {
            return {
                ArrowDownToLine,
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
            }
        },
        methods: {
            async downloadOrder(hideModal) {

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
                    const link = document.createElement("a");

                    link.href = window.URL.createObjectURL(blob);
                    link.download = `Order #${this.order.number}.pdf`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    hideModal();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while downloading order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to download order:', error);
                } finally {
                    this.isDownloadingOrder = false;
                }

            }
        }
    };

</script>
