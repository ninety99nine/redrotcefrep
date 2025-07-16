<template>

    <div class="space-y-2 bg-gray-50 p-2 border border-gray-300 rounded-lg">

        <!-- Name Input -->
        <Input
            type="text"
            class="w-full"
            v-model="cartFee.name"
            placeholder="Packaging"
            @input="shoppingCartState.saveStateDebounced('Fee name changed')">
        </Input>

        <div class="flex items-center space-x-2">

            <Select
                class="w-60"
                :search="false"
                :options="feeTypes"
                v-model="cartFee.rate_type"
                placeholder="Select fee type"
                @change="shoppingCartState.saveStateDebounced('Fee type changed')">
            </Select>

            <Input
                type="money"
                class="w-60"
                v-model="cartFee.flat_rate"
                :currency="store.currency.code"
                v-if="cartFee.rate_type == RATE_TYPES.FLAT"
                @input="shoppingCartState.saveStateDebounced('Fee amount changed')">
            </Input>

            <Input
                v-else
                class="w-60"
                type="percentage"
                v-model="cartFee.percentage_rate"
                @input="shoppingCartState.saveStateDebounced('Fee amount changed')">
            </Input>

            <!-- Remove Button -->
            <div class="w-full flex justify-end">

                <Button
                    size="xs"
                    type="danger"
                    :leftIcon="Trash2"
                    v-if="cartFee.removable"
                    :action="() => shoppingCartState.removeCartFee(index)">
                    <span>Remove Fee</span>
                </Button>

                <Switch
                    v-else
                    size="xs"
                    prefixText="Apply fee"
                    v-model="cartFee.active"
                    @change="shoppingCartState.saveStateDebounced('Apply fee status changed')"
                />

            </div>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import { Trash2 } from 'lucide-vue-next';
    import Select from '@Partials/Select.vue';
    import Switch from '@Partials/Switch.vue';
    import Button from '@Partials/Button.vue';
    import { RATE_TYPES } from '@Enums/enums.js';
    import { capitalize } from '@Utils/stringUtils.js';

    export default {
        inject: ['storeState', 'shoppingCartState'],
        components: { Input, Select, Switch, Button },
        props: {
            index: {
                type: Number
            },
            cartFee: {
                type: Object
            }
        },
        data() {
            return {
                Trash2,
                RATE_TYPES,
                feeTypes: Object.entries(RATE_TYPES).map(([key, value]) => ({
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
