<template>

   <div class="flex items-center justify-between">

        <PaymentStatus></PaymentStatus>

        <div class="flex items-center justify-end space-x-4">

            <EditButton></EditButton>
            <PrintButton></PrintButton>
            <DownloadButton></DownloadButton>
            <HomeButton></HomeButton>

        </div>

   </div>

</template>

<script>

    import EditButton from  '@Pages/shop/orders/_components/order-details/order-header/components/EditButton.vue';
    import HomeButton from  '@Pages/shop/orders/_components/order-details/order-header/components/HomeButton.vue';
    import PrintButton from  '@Pages/shop/orders/_components/order-details/order-header/components/PrintButton.vue';
    import PaymentStatus from  '@Pages/shop/orders/_components/order-details/order-header/components/PaymentStatus.vue';
    import DownloadButton from  '@Pages/shop/orders/_components/order-details/order-header/components/DownloadButton.vue';

    export default {
        components: { EditButton, HomeButton, PrintButton, PaymentStatus, DownloadButton }
    };

</script>
