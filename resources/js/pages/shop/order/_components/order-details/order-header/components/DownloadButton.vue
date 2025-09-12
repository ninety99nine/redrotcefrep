<template>

    <Button
        size="sm"
        type="bare"
        leftIconSize="18"
        loaderType="dark"
        :action="downloadOrder"
        :skeleton="isLoadingOrder"
        :leftIcon="ArrowDownToLine"
        :loading="isLoadingStore || isDownloadingOrder">
    </Button>

</template>

<script>

    import axios from 'axios';
    import Button from '@Partials/Button.vue';
    import { ArrowDownToLine } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Button },
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
            async downloadOrder() {

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
