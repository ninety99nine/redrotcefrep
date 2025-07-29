<template>

    <div class="rounded-lg border border-fuchsia-300 py-2 px-4 bg-fuchsia-50">

        <div class="w-full space-y-4">

            <!-- Name Input -->
            <Input
                type="text"
                label="Name"
                v-model="cartPromotion.name"
                placeholder="Standard Ticket"
                @input="orderState.saveStateDebounced('Promotion name changed')">
            </Input>

            <div class="grid grid-cols-3 gap-1">

                <Switch
                    size="xs"
                    suffixText="Offer discount"
                    v-model="cartPromotion.offer_discount"
                    @input="orderState.saveStateDebounced('Promotion discount offer changed')"
                />

                <template v-if="cartPromotion.offer_discount">

                    <Select
                        class="w-full"
                        :search="false"
                        :options="discountRateTypes"
                        placeholder="Select discount type"
                        v-model="cartPromotion.discount_rate_type"
                        @change="orderState.saveStateDebounced('Promotion discount type changed')">
                    </Select>

                    <Input
                        type="money"
                        :currency="store.currency.code"
                        v-model="cartPromotion.discount_flat_rate"
                        v-if="cartPromotion.discount_rate_type == RATE_TYPES.FLAT"
                        @input="orderState.saveStateDebounced('Promotion discount amount changed')">
                    </Input>

                    <Input
                        v-else
                        type="percentage"
                        v-model="cartPromotion.discount_percentage_rate"
                        @input="orderState.saveStateDebounced('Promotion discount amount changed')">
                    </Input>

                </template>

            </div>

        </div>

        <div class="flex justify-between my-4">

            <Switch
                size="xs"
                suffixText="Offer free delivery"
                v-model="cartPromotion.offer_free_delivery"
                @input="orderState.saveStateDebounced('Promotion fee delivery offer changed')"
            />

            <Button
                size="xs"
                type="danger"
                :leftIcon="Trash2"
                :action="() => orderState.removeCartPromotion(index)">
                <span>Remove Promotion</span>
            </Button>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import { Trash2 } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';
    import Switch from '@Partials/Switch.vue';
    import Select from '@Partials/Select.vue';
    import { RATE_TYPES } from '@Enums/enums.js';
    import { capitalize } from '@Utils/stringUtils.js';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Input, Button, Switch, Select },
        props: {
            index: {
                type: Number
            },
            cartPromotion: {
                type: Object
            }
        },
        data() {
            return {
                Trash2,
                RATE_TYPES,
                discountRateTypes: Object.entries(RATE_TYPES).map(([key, value]) => ({
                    label: capitalize(value),
                    value: value
                }))
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            }
        }
    };

</script>
