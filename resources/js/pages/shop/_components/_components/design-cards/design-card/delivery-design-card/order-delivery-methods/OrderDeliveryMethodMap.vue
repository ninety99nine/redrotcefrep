<template>

    <div class="mt-2 pt-2 border-t border-dashed border-gray-200">

        <AddressInput
            triggerClass=""
            :onlyValidate="true"
            :pinLocationOnMap="true"
            @onDeleted="unsetAddress"
            @onValidated="setAddress"
            targetClass="mockup-phone"
            :address="orderForm.delivery_address">
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
            },
            unsetAddress() {
                this.orderState.orderForm.delivery_address = null;
            }
        }
    };

</script>
