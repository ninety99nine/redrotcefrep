<template>

    <div>

        <!-- Header: Store Details -->
        <div class="flex items-center justify-between">

            <div class="w-full flex items-center space-x-4 ">

                <div v-if="!isLoadingStore && !isLoadingOrder && !logo" class="flex items-center space-x-4">

                    <!-- QR Code -->
                    <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-16" height="h-16" :shine="true"></Skeleton>
                    <img v-else-if="qrCode" :src="qrCode" alt="QR Code" class="w-16 h-16 rounded" />

                </div>

                <!-- Logo -->
                <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-16" height="h-16" :shine="true"></Skeleton>
                <img
                    v-else-if="logo"
                    alt="Store Logo"
                    :src="logo.path"
                    class="w-16 h-16 rounded"
                />

                <!-- Store Name and Link -->
                <div class="w-full">
                    <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-20" :shine="true" class="mb-4"></Skeleton>
                    <p v-else class="text-xl font-bold">{{ store.name ?? 'Store' }}</p>

                    <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-1/3" :shine="true"></Skeleton>
                    <a v-else :href="store.web_link" class="text-sm text-blue-500 hover:underline cursor-pointer">
                        {{ store.web_link }}
                    </a>
                </div>

            </div>

            <div
                class="flex items-center shrink-0 space-x-4"
                v-if="!isLoadingStore && !isLoadingOrder && logo">

                <!-- QR Code -->
                <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-16" height="h-16" :shine="true"></Skeleton>
                <img v-else-if="qrCode" :src="qrCode" alt="QR Code" class="w-16 h-16 rounded" />

            </div>

        </div>

        <!-- Store Contact -->
        <div
            class="flex items-center space-x-2 py-4 mb-4 border-b border-gray-200"
            v-if="isLoadingStore || isLoadingOrder || !hasOrder || store.email || store.whatsapp_mobile_number">

            <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" :shine="true"></Skeleton>
            <p v-else-if="store.whatsapp_mobile_number?.international" class="text-sm">
                WhatsApp: {{ store.whatsapp_mobile_number.international }}
            </p>

            <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" :shine="true"></Skeleton>
            <p v-else-if="store.email" class="text-sm">Email: {{ store.email }}</p>

        </div>

        <!-- Order Details -->
        <div class="mb-6">

            <div class="grid grid-cols-2 gap-1 text-sm">

                <div class="col-span-1 flex items-center space-x-1">
                    <span class="font-semibold whitespace-nowrap">Order No:</span>
                    <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" :shine="true"></Skeleton>
                    <span v-else>#{{ order.number }}</span>
                </div>

                <div class="col-span-1 flex items-center space-x-1">
                    <span class="font-semibold">Date: </span>
                    <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" :shine="true"></Skeleton>
                    <div v-else class="flex space-x-1 items-center">
                        <span>{{ formattedDatetime(order.created_at) }}</span>
                        <Popover
                            placement="top"
                            class="opacity-0 group-hover:opacity-100"
                            :content="formattedRelativeDate(order.created_at)">
                        </Popover>
                    </div>
                </div>

                <div
                    class="col-span-1 flex items-center space-x-1"
                    v-if="isLoadingStore || isLoadingOrder || !hasOrder || order.delivery_method_name">
                    <span class="font-semibold">Delivery Method: </span>
                    <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" :shine="true"></Skeleton>
                    <span v-else>{{ order.delivery_method_name }}</span>
                </div>

                <div
                    class="col-span-1 flex items-center space-x-1"
                    v-if="isLoadingStore || isLoadingOrder || !hasOrder || order.courier">
                    <span class="font-semibold whitespace-nowrap">Tracking No:</span>
                    <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" :shine="true"></Skeleton>
                    <a v-else :href="order.courier.tracking_page" target="_blank" class="text-sm text-blue-500 hover:underline cursor-pointer">
                        {{ order.tracking_number }}
                    </a>
                </div>

            </div>

        </div>

        <!-- Products Table -->
        <div class="border border-gray-300 rounded overflow-hidden mb-6">

            <table class="w-full text-xs text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="whitespace-nowrap border border-gray-300 p-2">Item</th>
                        <th class="whitespace-nowrap border border-gray-300 p-2 text-center">Qty</th>
                        <th class="whitespace-nowrap border border-gray-300 p-2">Unit Price</th>
                        <th class="whitespace-nowrap border border-gray-300 p-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="isLoadingStore || isLoadingOrder || !hasOrder">
                        <td class="border border-gray-300 p-2">
                            <Skeleton :shine="true"></Skeleton>
                        </td>
                        <td class="border border-gray-300 p-2">
                            <Skeleton :shine="true"></Skeleton>
                        </td>
                        <td class="border border-gray-300 p-2">
                            <Skeleton :shine="true"></Skeleton>
                        </td>
                        <td class="border border-gray-300 p-2">
                            <Skeleton :shine="true"></Skeleton>
                        </td>
                    </tr>
                    <template v-else>
                        <tr
                            :key="orderProduct.id"
                            v-for="orderProduct in order.order_products"
                            class="hover:bg-blue-50 cursor-pointer"
                            @click.stop="() => navigateToShowShopProduct(orderProduct)">
                            <td class="border border-gray-300 p-2">
                                <div class="lg:flex lg:items-center lg:space-x-2 lg:space-y-0 space-y-2">
                                    <div
                                        v-if="orderProduct.photo"
                                        class="flex items-center justify-center w-8 h-auto">
                                        <img class="w-full max-h-full object-contain rounded-sm shrink-0" :src="orderProduct.photo.path">
                                    </div>
                                    <span>{{ orderProduct.name }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap border border-gray-300 p-2 text-xs text-center">{{ orderProduct.quantity }}</td>
                            <td class="whitespace-nowrap border border-gray-300 p-2 text-xs">{{ orderProduct.unit_price.amount_with_currency }}</td>
                            <td class="whitespace-nowrap border border-gray-300 p-2 text-xs">{{ orderProduct.grand_total.amount_with_currency }}</td>
                        </tr>
                    </template>
                </tbody>
            </table>

        </div>

        <!-- Totals -->
        <div class="flex justify-end px-2 mb-6">

            <div class="w-full md:w-3/4 lg:w-1/2">

                <table class="w-full text-sm">
                <tbody>
                    <tr class="border-b border-gray-300 border-dashed">
                    <td class="text-right py-1">Subtotal:</td>
                    <td v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="w-24 pl-4">
                        <Skeleton width="w-full" :shine="true"></Skeleton>
                    </td>
                    <td v-else class="text-right py-1">{{ order.subtotal.amount_with_currency }}</td>
                    </tr>

                    <tr
                    class="border-b border-gray-300 border-dashed"
                    v-if="isLoadingStore || isLoadingOrder || !hasOrder || (order && order.discount_total.amount > 0)"
                    >
                    <td class="text-right py-1">Discount:</td>
                    <td v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="w-24 pl-4">
                        <Skeleton width="w-full" :shine="true"></Skeleton>
                    </td>
                    <td v-else class="text-right py-1">{{ order.discount_total.amount_with_currency }}</td>
                    </tr>

                    <tr
                    class="border-b border-gray-300 border-dashed"
                    v-if="isLoadingStore || isLoadingOrder || !hasOrder || (order && order.vat_amount > 0)"
                    >
                    <td class="text-right py-1">Tax {{ isLoadingOrder ? '' : `(${order?.vat_rate ?? '_'})` }}:</td>
                    <td v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="w-24 pl-4">
                        <Skeleton width="w-full" :shine="true"></Skeleton>
                    </td>
                    <td v-else class="text-right py-1">{{ order.vat_amount.amount_with_currency }}</td>
                    </tr>

                    <tr
                    class="border-b border-gray-300 border-dashed"
                    v-if="isLoadingStore || isLoadingOrder || !hasOrder || (order && order.fee_total.amount > 0)"
                    >
                    <td class="text-right py-1">Additional Fees:</td>
                    <td v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="w-24 pl-4">
                        <Skeleton width="w-full" :shine="true"></Skeleton>
                    </td>
                    <td v-else class="text-right py-1">{{ order.fee_total.amount_with_currency }}</td>
                    </tr>

                    <tr class="text-base font-bold border-b border-gray-300 border-dashed">
                    <td class="text-right py-2">Grand Total:</td>
                    <td v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="w-24 pl-4">
                        <Skeleton width="w-full" :shine="true"></Skeleton>
                    </td>
                    <td v-else class="text-right py-2">{{ order.grand_total.amount_with_currency }}</td>
                    </tr>
                </tbody>
                </table>

            </div>

        </div>

        <!-- Customer -->
        <div class="border-b border-gray-200 pb-4">

            <p class="text-sm font-semibold mb-2">Customer</p>

            <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" :shine="true" class="mb-2"></Skeleton>
            <p v-else-if="order.customer_name" class="text-sm">{{ order.customer_name }}</p>

            <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" :shine="true" class="mb-2"></Skeleton>
            <p v-else-if="order.customer_mobile_number?.international" class="text-sm">
                {{ order.customer_mobile_number.international }}
            </p>

            <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" :shine="true"></Skeleton>
            <p v-else-if="order.customer_email" class="text-sm">{{ order.customer_email }}</p>

        </div>

        <p class="text-sm text-center mt-4">Thank you!</p>

    </div>

  </template>

  <script>

    import QRCode from 'qrcode';
    import Popover from '@Partials/Popover.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { formattedDatetime, formattedRelativeDate } from '@Utils/dateUtils.js';

    export default {
        inject: ['orderState', 'storeState'],
        components: { Popover, Skeleton },
        data() {
            return {
                qrCode: null
            }
        },
        watch: {
            order(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            }
        },
        computed: {
            order() {
                return this.orderState.order;
            },
            hasOrder() {
                return this.order != null;
            },
            store() {
                return this.storeState.store;
            },
            logo() {
                return this.store.logo;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            currentPageUrl() {
                return window.location.origin + this.$router.resolve({
                    name: 'show-shop-order',
                    params: {
                        alias: this.store.alias,
                        order_id: this.order.id
                    }
                }).href;
            },
        },
        methods: {
            formattedDatetime,
            formattedRelativeDate,
            setup() {
                if(this.store && this.order) {
                    this.generateQRCode();
                }
            },
            async navigateToShowShopProduct(orderProduct) {
                await this.$router.push({
                    name: 'show-shop-product',
                    params: {
                        alias: this.store.alias,
                        product_id: orderProduct.product_id
                    }
                });
            },
            async generateQRCode() {
                try {

                    this.qrCode = await QRCode.toDataURL(this.currentPageUrl, {
                        margin: 2,
                        width: 150,
                        color: {
                            dark: '#000000',   // Black QR dots
                            light: '#ffffff'   // White background
                        }
                    })

                } catch (err) {
                    console.error('Failed to generate QR code:', err);
                }
            },
        },
        created() {
            this.setup();
        }
    };
  </script>
