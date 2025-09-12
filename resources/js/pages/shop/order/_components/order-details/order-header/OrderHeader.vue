<template>

   <div class="flex items-center justify-between">

        <PaymentStatus></PaymentStatus>

        <div class="flex items-center justify-end space-x-4">

                <EditButton></EditButton>
                <PrintButton></PrintButton>
                <DownloadButton></DownloadButton>
                <VisitButton></VisitButton>

        </div>

   </div>

</template>

<script>

    import EditButton from  '@Pages/shop/order/_components/order-details/order-header/components/EditButton.vue';
    import VisitButton from  '@Pages/shop/order/_components/order-details/order-header/components/VisitButton.vue';
    import PrintButton from  '@Pages/shop/order/_components/order-details/order-header/components/PrintButton.vue';
    import PaymentStatus from  '@Pages/shop/order/_components/order-details/order-header/components/PaymentStatus.vue';
    import DownloadButton from  '@Pages/shop/order/_components/order-details/order-header/components/DownloadButton.vue';

    export default {
        components: { EditButton, VisitButton, PrintButton, PaymentStatus, DownloadButton }
    };

</script>
