<template>

    <div class="space-y-4 p-4">

        <AddressInput
            :onlyValidate="true"
            heading="Destination"
            :pinLocationOnMap="true"
            @onDeleted="unsetAddress"
            @onValidated="setAddress"
            :address="shoppingCartForm.delivery_address"
            subtitle="Add the address to deliver this order">
        </AddressInput>

    </div>

</template>

<script>

    import AddressInput from '@Partials/AddressInput.vue';

    export default {
        inject: ['orderState', 'shoppingCartState'],
        components: {
            AddressInput
        },
        computed: {
            shoppingCartForm() {
                return this.shoppingCartState.shoppingCartForm;
            }
        },
        methods: {
            setAddress(deliveryAddress) {
                this.shoppingCartState.shoppingCartForm.delivery_address = deliveryAddress;
                this.orderState.saveStateDebounced('Delivery address changed');
            },
            unsetAddress() {
                this.shoppingCartState.shoppingCartForm.delivery_address = null;
                this.shoppingCartState.saveStateDebounced('Delivery address removed');
            }
        }
    };

</script>
