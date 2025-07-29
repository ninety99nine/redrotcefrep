<template>

    <div class="space-y-4 p-4">

        <AddressInput
            title="Destination"
            :onlyValidate="true"
            :pinLocationOnMap="true"
            @onDeleted="unsetAddress"
            @onValidated="setAddress"
            :address="orderForm.delivery_address"
            subtitle="Add the address to deliver this order">
        </AddressInput>

    </div>

</template>

<script>

    import AddressInput from '@Partials/AddressInput.vue';

    export default {
        inject: ['orderState'],
        components: {
            AddressInput
        },
        computed: {
            orderForm() {
                return this.orderState.orderForm;
            }
        },
        methods: {
            setAddress(deliveryAddress) {
                this.orderState.orderForm.delivery_address = deliveryAddress;
                this.orderState.saveStateDebounced('Delivery address changed');
            },
            unsetAddress() {
                this.orderState.orderForm.delivery_address = null;
                this.orderState.saveStateDebounced('Delivery address removed');
            }
        }
    };

</script>
