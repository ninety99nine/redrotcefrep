<template>

    <div class="max-h-80 overflow-y-auto space-y-2 mb-4">

        <CustomerOption
            :key="index"
            :customer="customer"
            :onSelectCustomer="onSelectCustomer"
            v-for="(customer, index) in customers">
        </CustomerOption>

    </div>

</template>

<script>

    import CustomerOption from '@Pages/orders/order/editable/components/order-customer/add-customer/customer-options/CustomerOption.vue';

    export default {
        components: { CustomerOption },
        props: {
            customers: {
                type: Array
            },
            onSelectCustomer: {
                type: Function
            }
        }
    };

</script>
