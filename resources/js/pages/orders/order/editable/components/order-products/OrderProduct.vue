<template>

    <div class="rounded-lg overflow-hidden border border-gray-300 py-2 px-4 bg-gray-50">

        <div class="w-full flex space-x-4">

            <div :class="['flex items-center justify-center w-16 h-16 rounded-lg', { 'border border-dashed border-gray-200' : !hasPhotoFilePath }]">

                <img v-if="hasPhotoFilePath" class="w-full object-contain rounded-lg bg-red-200 flex-shrink-0" :src="photoFilePath">

                <svg v-else class="w-6 h-6 text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>

            </div>

            <div class="w-full space-y-4">

                <!-- Name Input -->
                <Input
                    type="text"
                    label="Name"
                    v-model="cartProduct.name"
                    placeholder="Standard Ticket"
                    @input="orderState.saveStateDebounced('Product name changed')">
                </Input>

                <table class="w-full text-sm text-left">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap align-top px-2 pr-2">
                                <span class="text-gray-700 text-xs font-normal uppercase">Regular Price</span>
                            </th>
                            <th class="whitespace-nowrap align-top px-2">
                                <span class="text-gray-700 text-xs font-normal uppercase">Sale Price</span>
                            </th>
                            <th class="whitespace-nowrap align-top px-2 pl-2">
                                <span class="text-gray-700 text-xs font-normal uppercase">Quantity</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="whitespace-nowrap align-top py-2 pr-2">

                                <!-- Unit Regular Price Input -->
                                <Input
                                    type="money"
                                    :currency="store.currency.code"
                                    v-model="cartProduct.unit_regular_price"
                                    @input="orderState.saveStateDebounced('Product regular price changed')">
                                </Input>

                            </td>
                            <td class="whitespace-nowrap align-top p-2">

                                <!-- Unit Sale Price Input -->
                                <Input
                                    type="money"
                                    :currency="store.currency.code"
                                    v-model="cartProduct.unit_sale_price"
                                    @input="orderState.saveStateDebounced('Product sale price changed')">
                                </Input>

                            </td>
                            <td class="whitespace-nowrap align-top py-2 pl-2">

                                <!-- Quantity Input -->
                                <Input
                                    type="number"
                                    v-model="cartProduct.quantity"
                                    @input="orderState.saveStateDebounced('Product quantity changed')">
                                </Input>

                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>

        </div>

        <div class="grid grid-cols-3 my-4">

            <div class="col-span-1 col-start-2 flex items-center justify-center space-x-2">

                <Tag size="16" class="text-gray-500"></Tag>
                <span class="text-gray-700 text-center">{{ store.currency.symbol }}{{ grandTotal }}</span>

            </div>

            <div class="col-span-1">

                <div class="flex justify-end">

                    <Button
                        size="xs"
                        type="danger"
                        :leftIcon="Trash2"
                        :action="() => orderState.removeCartProduct(index)">
                        <span>Remove Product</span>
                    </Button>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import { Tag, Trash2 } from 'lucide-vue-next';
    import { convertToValidMoney } from '@Utils/numberUtils.js';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Tag, Input, Button },
        props: {
            index: {
                type: Number
            },
            cartProduct: {
                type: Object
            }
        },
        data() {
            return {
                Trash2
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasPhotoFilePath() {
                return this.photoFilePath != null;
            },
            photoFilePath() {
                return this.cartProduct.photo_file_path;
            },
            grandTotal() {
                const unitRegularPrice = parseFloat(this.cartProduct.unit_regular_price);
                const unitSalePrice = parseFloat(this.cartProduct.unit_sale_price);
                const quantity = parseInt(this.cartProduct.quantity);
                const currency = this.store.currency.code;

                if(unitSalePrice > 0 && unitSalePrice < unitRegularPrice) {
                    return convertToValidMoney(unitSalePrice * quantity, currency);
                }else{
                    return convertToValidMoney(unitRegularPrice * quantity, currency);
                }
            }
        }
    };

</script>
