<template>

    <div class="select-none bg-white rounded-lg p-4 mb-4">

        <div class="flex justify-between items-center">

            <div class="flex items-center space-x-4">

                <!-- Back Button -->
                <BackButton></BackButton>

                <div v-if="isLoadingStore || isLoadingOrder || (isEditting && !hasOrder)" class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl font-bold tracking-tight text-gray-300">#</span>
                        <Skeleton width="w-12" :shine="true"></Skeleton>
                        <Skeleton width="w-4" :shine="true"></Skeleton>
                    </div>
                    <div class="flex space-x-1">
                        <Skeleton width="w-16" :shine="true"></Skeleton>
                        <Skeleton width="w-4" :shine="true"></Skeleton>
                    </div>
                    <div class="flex space-x-1">
                        <Skeleton width="w-24" :shine="true"></Skeleton>
                        <Skeleton width="w-4" :shine="true"></Skeleton>
                    </div>
                </div>

                <!-- Heading -->
                <h1 v-else class="text-xl text-gray-700 font-semibold">
                    {{ isCreating ? 'Add Order' : `#${order.number}` }}
                </h1>

                <template v-if="hasOrder">

                    <!-- Status -->
                    <Status></Status>

                    <!-- Payment Status -->
                    <PaymentStatus></PaymentStatus>

                </template>

            </div>

            <template v-if="!isEditting && !isCreating">

                <div class="flex space-x-2 items-center">

                    <!-- Whatsapp Button -->
                    <WhatsappButton></WhatsappButton>

                    <!-- Edit Button -->
                    <EditButton></EditButton>

                    <!-- Print Button -->
                    <PrintButton></PrintButton>

                    <!-- Download Button -->
                    <DownloadButton></DownloadButton>

                    <!-- Navigation Arrows -->
                    <NavigationArrows></NavigationArrows>

                </div>

            </template>

        </div>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import Status from '@Pages/orders/order/components/order-header/Status.vue';
    import BackButton from '@Pages/orders/order/components/order-header/BackButton.vue';
    import EditButton from '@Pages/orders/order/components/order-header/EditButton.vue';
    import PrintButton from '@Pages/orders/order/components/order-header/PrintButton.vue';
    import PaymentStatus from '@Pages/orders/order/components/order-header/PaymentStatus.vue';
    import DownloadButton from '@Pages/orders/order/components/order-header/DownloadButton.vue';
    import WhatsappButton from '@Pages/orders/order/components/order-header/WhatsappButton.vue';
    import NavigationArrows from '@Pages/orders/order/components/order-header/NavigationArrows.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: {
            Button, Skeleton, Status, BackButton, EditButton, PrintButton, PaymentStatus,
            DownloadButton, WhatsappButton, NavigationArrows
        },
        computed: {
            order() {
                return this.orderState.order;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isEditting() {
                return this.$route.name === 'edit-order';
            },
            isCreating() {
                return this.$route.name === 'create-order';
            }
        }
    };

</script>
