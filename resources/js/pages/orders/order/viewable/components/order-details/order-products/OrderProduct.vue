<template>

    <div
        :class="[{ 'shadow-md' : expanded }, 'rounded-lg overflow-hidden border border-gray-300']">

        <div
            @click="toggleExpansion"
            :class="[{ 'shadow-sm border-b border-gray-300' : expanded }, 'flex justify-between items-center cursor-pointer py-2 px-4 bg-gray-50 hover:bg-gray-100']">

            <div class="w-full flex items-center space-x-4">

                <div :class="['flex items-center justify-center w-16 h-16', { 'border border-dashed border-gray-200 rounded-lg' : !hasProductPhoto }]">

                    <img v-if="hasProductPhoto" class="w-full object-contain rounded-lg bg-red-200 flex-shrink-0" :src="productPhoto.file_path">

                    <Image v-else size="24" class="text-gray-400 flex-shrink-0"></Image>

                </div>

                <div class="w-full flex items-center justify-between">

                    <div class="flex items-center space-x-2">
                        <span class="font-semibold text-sm bg-blue-50 border border-blue-400 px-2 py-1 rounded-full">{{ orderProduct.quantity }}x</span>
                        <span class="text-sm font-semibold">{{ orderProduct.name }}</span>
                    </div>

                    <div class="flex items-center space-x-2 mr-4">
                        <Pill v-if="hasDetectedChanges" type="warning" size="xs" :showDot="false">{{ `${totalDetectedChanges} ${ totalDetectedChanges == 1 ? 'change' : 'changes' } found` }}</Pill>
                        <Pill v-if="orderProduct.on_sale" type="success" size="xs" :showDot="false">on sale</Pill>
                        <Pill v-if="orderProduct.is_free" type="success" size="xs" :showDot="false">free</Pill>
                    </div>

                </div>

            </div>

            <div class="flex items-center space-x-2">

                <span class="text-sm">{{ orderProduct.grand_total.amount_with_currency }}</span>

                <svg v-if="expanded" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                </svg>

                <svg v-else class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>

            </div>

        </div>

        <div v-if="expanded" class="text-sm p-4 pb-8">

            <div class="flex items-end justify-between mb-4 ml-4">

                <h1 class="text-base text-gray-500 font-semibold">Pricing</h1>

                <Button
                    size="xs"
                    type="light"
                    v-if="canShowProduct"
                    :rightIcon="MoveRight"
                    :action="navigateToProduct">
                    <span>Show Product</span>
                </Button>

            </div>

            <div class="w-full overflow-x-auto rounded-lg border border-gray-300 mb-4">

                <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="whitespace-nowrap align-top text-left p-2 pl-4">
                                <span>Regular Price</span>
                            </th>
                            <th class="whitespace-nowrap align-top text-center py-2">
                                <span>Sale Price</span>
                            </th>
                            <th class="whitespace-nowrap align-top text-center py-2">
                                <span>Quantity</span>
                            </th>
                            <th class="whitespace-nowrap align-top text-center py-2">
                                <span>Subtotal</span>
                            </th>
                            <th class="whitespace-nowrap align-top text-center py-2">
                                <span>Discount Total</span>
                            </th>
                            <th class="whitespace-nowrap align-top text-right p-2 pr-4">
                                <span>Grand Total</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="whitespace-nowrap align-top text-left p-2 pl-4">
                                <span>{{ orderProduct.unit_regular_price.amount_with_currency }}</span>
                            </td>
                            <td class="whitespace-nowrap align-top text-center py-2">
                                <span>{{ orderProduct.unit_sale_price.amount_with_currency }}</span>
                            </td>
                            <td class="whitespace-nowrap align-top text-center py-2">
                                <span>{{ orderProduct.quantity }}</span>
                            </td>
                            <td class="whitespace-nowrap align-top text-center py-2">
                                <span>{{ orderProduct.subtotal.amount_with_currency }}</span>
                            </td>
                            <td class="whitespace-nowrap align-top text-center py-2">
                                <span>{{ orderProduct.sale_discount_total.amount_with_currency }}</span>
                            </td>
                            <td class="whitespace-nowrap align-top text-right p-2 pr-4">
                                <span>{{ orderProduct.grand_total.amount_with_currency }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <template v-if="true">

                <h1 class="text-base text-gray-500 font-semibold mb-4 ml-4">Weight</h1>

                <div class="w-full overflow-x-auto rounded-lg border border-gray-300 mb-4">

                    <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="whitespace-nowrap align-top text-left p-2 pl-4">
                                    <span>Unit Weight</span>
                                </th>
                                <th class="whitespace-nowrap align-top text-center p-2">
                                    <span>Quantity</span>
                                </th>
                                <th class="whitespace-nowrap align-top text-center p-2">
                                    <span>Total Weight</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="whitespace-nowrap align-top text-left p-2 pl-4">
                                    <span>{{ orderProduct.unit_weight }}</span>
                                </td>
                                <td class="whitespace-nowrap align-top text-center p-2">
                                    <span>{{ orderProduct.quantity }}</span>
                                </td>
                                <td class="whitespace-nowrap align-top text-center p-2">
                                    <span>{{ orderProduct.unit_weight }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>

            </template>

            <template v-if="orderProduct.sku || orderProduct.barcode">

                <h1 class="text-base text-gray-500 font-semibold mb-4 ml-4">Identification</h1>

                <div class="w-full overflow-x-auto rounded-lg border mb-4">

                    <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th v-if="orderProduct.sku" class="whitespace-nowrap align-top text-left p-2 pl-4">
                                    <span>SKU</span>
                                </th>
                                <th v-if="orderProduct.barcode" class="whitespace-nowrap align-top text-center p-2">
                                    <span>Barcode</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td v-if="orderProduct.sku" class="whitespace-nowrap align-top text-left p-2 pl-4">
                                    <span>{{ orderProduct.sku }}</span>
                                </td>
                                <td v-if="orderProduct.barcode" class="whitespace-nowrap align-top text-center p-2">
                                    <span>{{ orderProduct.barcode }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>

            </template>

            <template v-if="hasDetectedChanges">

                <h1 class="text-base text-gray-500 font-semibold mb-4 ml-4">Changes</h1>

                <div class="space-y-2">
                    <div v-for="(detectedChange, index) in orderProduct.detected_changes" :key="index" class="flex space-x-2 p-2 border-l-8 border-yellow-300 bg-gray-50">
                        <p class="text-xs">{{ detectedChange.message }}</p>
                    </div>
                </div>

            </template>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Button from '@Partials/Button.vue';
    import { Image, MoveRight } from 'lucide-vue-next';

    export default {
        inject: ['orderState', 'storeState'],
        components: { Image, Pill, Button },
        props: {
            orderProduct: {
                type: Object
            }
        },
        data() {
            return {
                MoveRight,
                expanded: false
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasDetectedChanges() {
                return this.orderProduct.detected_changes.length > 0;
            },
            totalDetectedChanges() {
                return this.orderProduct.detected_changes.length;
            },
            hasProductPhoto() {
                return this.productPhoto != null;
            },
            productPhoto() {
                return this.orderProduct.photo;
            },
            canShowProduct() {
                return this.orderProduct.product_id != null;
            },
        },
        methods: {
            toggleExpansion() {
                this.expanded = !this.expanded;
            },
            navigateToProduct() {

                this.$router.push({
                    name: 'show-product',
                    params: {
                        'product_id': this.orderProduct._links.showProduct
                    },
                    query: {
                        'store_id': this.store._links.showStore
                    }
                });

            }
        }
    };

</script>
