<template>

    <Button
        size="sm"
        type="bare"
        loaderType="dark"
        leftIconSize="18"
        :leftIcon="Printer"
        :action="printOrder"
        :loading="isDownloadingOrder"
        :skeleton="isLoadingStore || isLoadingOrder">
    </Button>

</template>

<script>

    import axios from 'axios';
    import Button from '@Partials/Button.vue';
    import { Printer } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Button },
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
            }
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
