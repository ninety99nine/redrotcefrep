<template>

    <div
        v-if="deliveryAddressIsRequired"
        class="mt-2 pt-2 border-t border-dashed border-gray-200">

        <span
            v-if="errorText"
            class="scroll-to-error font-medium text-red-500 text-xs mt-1 ml-1">
            {{ errorText }}
        </span>

        <div v-if="loadingMap" class="flex items-center justify-center py-2">
            <Loader>
                <span class="text-sm ml-2">Preparing address</span>
            </Loader>
        </div>

        <AddressInput
            v-else
            triggerClass=""
            :onlyValidate="true"
            @onDeleted="unsetAddress"
            @onValidated="setAddress"
            :pinLocationOnMap="pinLocationOnMap"
            :address="orderForm.delivery_address">
        </AddressInput>

    </div>

</template>

<script>

    import Loader from '@Partials/Loader.vue';
    import AddressInput from '@Partials/AddressInput.vue';

    export default {
        inject: ['formState', 'orderState'],
        components: {
            Loader, AddressInput
        },
        props: {
            index: {
                type: Number
            },
            deliveryMethod: {
                type: Object
            }
        },
        computed: {
            orderForm() {
                return this.orderState.orderForm;
            },
            selectedDeliveryMethod() {
                return this.orderState.selectedDeliveryMethod;
            },
            pinLocationOnMap() {
                return ((this.shoppingCartDeliveryMethodOption || {}).pin_location_on_map || false);
            },
            deliveryAddressIsRequired() {
                return ((this.shoppingCartDeliveryMethodOption || {}).delivery_address_is_required || false);
            },
            shoppingCartDeliveryMethodOption() {
                return this.orderState.getShoppingCartDeliveryMethodOption(this.deliveryMethod);
            },
            errorText() {
                return this.formState.getFormError(`delivery_methods.${this.index}.address`);
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
        },
        created() {
            if(this.orderState.orderForm.delivery_address) {
                this.loadingMap = true;
                setTimeout(() => {
                    this.loadingMap = false;
                }, 1500);
            }
        }
    };

</script>
