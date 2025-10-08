<template>

    <div class="space-y-2 mb-8">

        <div class="flex items-center justify-center space-x-2">
            <EmailInvoiceButton></EmailInvoiceButton>
            <OrderAgainButton></OrderAgainButton>
        </div>

        <PayButton></PayButton>

    </div>

</template>

<script>

    import PayButton from '@Pages/shop/orders/_components/actions/PayButton.vue';
    import OrderAgainButton from '@Pages/shop/orders/_components/actions/OrderAgainButton.vue';
    import EmailInvoiceButton from '@Pages/shop/orders/_components/actions/EmailInvoiceButton.vue';

    export default {
        components: { PayButton, OrderAgainButton, EmailInvoiceButton }
    };

</script>
