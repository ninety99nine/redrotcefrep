<template>
    <div class="select-none max-w-xl mx-auto pt-16">

        <div
            @click="navigateToShowShopOrder"
            class="w-full bg-white border border-gray-100 shadow-sm rounded-xl cursor-pointer hover:bg-gray-50 hover:scale-105 active:opacity-80 active:scale-100 transition-all duration-300 mb-4">

            <div class="p-4 flex items-center space-x-4">

                <StoreLogo v-if="store && store.logo" size="w-10 h-10" :showButton="false"></StoreLogo>

                <div class="w-full flex justify-between space-x-4">

                    <div class="w-full">
                        <Skeleton v-if="isLoadingStore || isLoadingOrder" width="w-40" height="h-2" class="mb-2" :shine="true"></Skeleton>
                        <p v-else class="text-gray-700 font-semibold text-sm">{{ order.customer_name }}</p>

                        <Skeleton v-if="isLoadingStore || isLoadingOrder" width="w-2/3" height="h-2" :shine="true"></Skeleton>
                        <p v-else class="text-gray-500 text-sm">{{ order.summary }}</p>
                    </div>

                    <Skeleton v-if="isLoadingStore || isLoadingOrder" width="w-2/3" height="h-2" class="" :shine="true"></Skeleton>
                    <p v-else class="text-right text-gray-400 text-xs">Order #{{ order.number }}</p>

                </div>

            </div>

        </div>

        <div class="w-full bg-gray-50 border border-gray-100 shadow rounded-xl p-10">
            <div class="w-16 h-16 mx-auto flex items-center justify-center bg-green-100 text-green-500 rounded-full mb-4">
                <Loader v-if="isLoadingStorePaymentMethod || isCreatingTransaction" type="primary"></Loader>
                <ReceiptText v-else size="24"></ReceiptText>
            </div>
            <p class="text-xl font-semibold text-center mb-2">Confirming Payment</p>
            <p class="text-sm text-gray-500 text-center mb-8">
                Your order has been received and our team is confirming your payment.
                <br/>
                Send us a message to keep up with your order.
            </p>

            <template v-if="clicked">

                <div class="flex justify-center space-x-2 mb-4">

                    <Button
                        size="md"
                        type="light"
                        :leftIcon="ArrowLeft"
                        :action="navigateToStorefront">
                        <span class="text-xs">Back to shop</span>
                    </Button>

                    <Button
                        size="md"
                        type="success"
                        :action="navigateToShowShopOrder">
                        <span class="text-xs">Go to invoice</span>
                    </Button>

                </div>

                <div v-if="!isLoadingStore && !isLoadingOrder">

                    <p class="text-sm text-red-500 text-center mt-8 mb-4">WhatsApp is not opening?</p>

                    <div
                        :key="index"
                        v-for="(accordion, index) in accordions"
                        @click.stop="() => toggleAccordian(index)"
                        :class="['border border-gray-300', { 'rounded-t-lg' : index == 0 }, { 'rounded-b-lg' : index == accordions.length - 1 }]">

                        <div :class="['flex items-center justify-between bg-gray-50 hover:bg-gray-100 p-2 cursor-pointer', { 'shadow' : accordion.expanded }, { 'rounded-t-lg' : index == 0 }, { 'rounded-b-lg' : index == accordions.length - 1 && !accordion.expanded }]">
                            <p class="text-sm text-gray-700">{{ accordion.label }}</p>

                            <ChevronUp v-if="accordion.expanded" size="16"></ChevronUp>
                            <ChevronDown v-else size="16"></ChevronDown>
                        </div>

                        <vue-slide-up-down :active="accordion.expanded">

                            <template v-if="index == 0">

                                <p class="text-xs text-gray-500 p-4 mb-4">
                                    Look for the option called <span class="font-bold">"Open in external browser"</span>
                                </p>

                                <img :src="'/images/open_in_external_browser.png'" class="max-w-80 rounded-lg overflow-hidden m-4 mx-auto" />

                            </template>

                            <template v-else-if="index == 1">

                                <div class="p-4 space-y-2">

                                    <p class="text-xs text-gray-500 mb-4">
                                        Copy order details below and send to our <span class="font-bold">Whatsapp number</span>
                                    </p>

                                    <div
                                        v-if="store.whatsapp_mobile_number"
                                        class="flex items-center justify-between">

                                        <span class="text-gray-700 font-semibold text-xs">{{ store.whatsapp_mobile_number.international }}</span>

                                        <Copy
                                            :showText="false"
                                            :text="store.whatsapp_mobile_number.international">

                                            <template #trigger="props">

                                                <Button
                                                    size="xs"
                                                    type="light"
                                                    leftIconSize="14"
                                                    :leftIcon="CopyIcon"
                                                    :action="() => props.loading ? props.copyToClipboardNotReady() : props.copyToClipboard()">
                                                    <span class="text-xs ml-1">copy our whatsapp number</span>
                                                </Button>

                                            </template>

                                        </Copy>

                                    </div>

                                    <template v-if="whatsappMessage">

                                        <div
                                            class="flex items-center justify-end">

                                            <Copy
                                                :showText="false"
                                                :text="whatsappMessage">

                                                <template #trigger="props">

                                                    <Button
                                                        size="xs"
                                                        type="light"
                                                        leftIconSize="14"
                                                        :leftIcon="CopyIcon"
                                                        :action="() => props.loading ? props.copyToClipboardNotReady() : props.copyToClipboard()">
                                                        <span class="text-xs ml-1">copy order details</span>
                                                    </Button>

                                                </template>

                                            </Copy>

                                        </div>

                                        <WhatsappMessage
                                            class="h-60"
                                            :animate="false"
                                            :loopAnimation="false"
                                            :messages="[
                                                {
                                                    sender: 'You', text: whatsappMessage, timestamp: now, isOwnMessage: true
                                                },
                                            ]"/>

                                    </template>

                                </div>

                            </template>

                            <template v-else-if="index == 2">

                                <p class="text-xs text-gray-500 p-4 mb-4">
                                    Use your mobile camera to scan this <span class="font-bold">QR Code</span>
                                </p>

                                <img :src="qrCode" alt="QR Code" class="my-4 mx-auto border border-gray-300 rounded-lg shadow max-w-60" />

                            </template>

                        </vue-slide-up-down>

                    </div>

                </div>

            </template>

            <div
                v-else
                class="flex justify-center">
                <Button
                    size="xs"
                    type="success"
                    :action="sendUsingWhatsapp">
                    <div class="flex items-center space-x-1">
                        <WhatsappIcon color="#ffffff" class="mx-2"></WhatsappIcon>
                        <span class="text-xs">Send using Whatsapp</span>
                    </div>
                </Button>
            </div>
        </div>

    </div>
</template>

<script>

    import dayjs from 'dayjs';
    import QRCode from 'qrcode';
    import Copy from '@Partials/Copy.vue';
    import Loader from '@Partials/Loader.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import VueSlideUpDown from 'vue-slide-up-down';
    import StoreLogo from '@Components/StoreLogo.vue';
    import { isNotEmpty } from '@Utils/stringUtils.js';
    import WhatsappIcon from '@Partials/WhatsappIcon.vue';
    import WhatsappMessage from '@Partials/WhatsappMessage.vue';
    import { ArrowLeft, ReceiptText, ChevronUp, ChevronDown, Copy as CopyIcon } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'orderState', 'notificationState'],
        components: { Copy, Loader, Button, Skeleton, VueSlideUpDown, ReceiptText, ChevronUp, ChevronDown, StoreLogo, WhatsappIcon, WhatsappMessage },
        data() {
            return {
                CopyIcon,
                ArrowLeft,
                qrCode: null,
                clicked: false,
                storePaymentMethod: null,
                now: dayjs().format('HH:mm'),
                isCreatingTransaction: false,
                isLoadingStorePaymentMethod: false,
                accordions: [
                    {
                        label: 'Open in external browser',
                        expanded: false
                    },
                    {
                        label: 'Send order details manually',
                        expanded: false
                    },
                    {
                        label: 'Scan QR code to open this page on your mobile',
                        expanded: false
                    }
                ]
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
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            storePaymentMethodId() {
                return this.$route.query.store_payment_method_id ?? null;
            },
            orderUrl() {
                return `${window.location.origin + this.$router.resolve({
                    name: 'show-shop-order',
                    params: {
                        alias: this.store.alias,
                        order_id: this.order.id
                    }
                }).href}`;
            },
            currentPageUrl() {
                return window.location.origin + this.$router.resolve({
                    name: 'show-shop-confirming-payment',
                    params: {
                        alias: this.store.alias,
                        order_id: this.order.id
                    }
                }).href;
            },
            whatsappMessage() {
                if(this.order) {
                    let message = '';

                    message += `*Order #${this.order.number}*\n`;

                    for (let index = 0; index < this.order.order_products.length; index++) {
                        const orderProduct = this.order.order_products[index];
                        message += `\n*${orderProduct.quantity}x* ${orderProduct.name} ${orderProduct.grand_total.amount_with_currency}`;
                    }

                    message += `\n\n*Grand Total: ${this.order.grand_total.amount_with_currency}* (Qty: ${this.order.total_uncancelled_product_quantities})\n`;

                    const hasName = this.isNotEmpty(this.order.customer_name);
                    const hasEmail = this.isNotEmpty(this.order.customer_email);
                    const hasMobile = this.isNotEmpty(this.order.customer_mobile_number);

                    if(hasName) {
                        message += `\nName: ${this.order.customer_name}`;
                    }

                    if(hasMobile) {
                        message += `\nMobile: ${this.order.customer_mobile_number.international}`;
                    }

                    if(hasEmail) {
                        message += `\nEmail: ${this.order.customer_email}`;
                    }

                    if(hasName || hasMobile || hasEmail) {
                        message += `\n`;
                    }

                    if(this.storePaymentMethod != null) {
                        message += `\nPayment : ${this.storePaymentMethod.name} (Confirm Payment)\n`;
                    }

                    if(this.order.delivery_method != null) {
                        message += `\nDelivery: ${this.order.delivery_method.name}`;
                    }

                    if(this.order.delivery_address != null) {
                        message += `\nAddress: ${this.order.delivery_address.complete_address}`;
                    }

                    message += `\n\nSee invoice ${this.orderUrl}`;

                    return message;
                }
                return null;
            }
        },
        methods: {
            isNotEmpty,
            async setup() {
                if(this.store && this.order) {
                    this.generateQRCode();
                    await this.showStorePaymentMethod();
                    if(!['paid'].includes(this.order.payment_status)) this.createTransaction();
                }
            },
            toggleAccordian(index) {
                for (let i = 0; i < this.accordions.length; i++) {
                    if(i == index) {
                        this.accordions[i].expanded = !this.accordions[i].expanded;
                    }else{
                        this.accordions[i].expanded = false;
                    }
                }
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
            async navigateToShowShopOrder() {
                await this.$router.push({
                    name: 'show-shop-order',
                    params: {
                        alias: this.store.alias,
                        order_id: this.order.id,
                    }
                });
            },
            async navigateToStorefront() {
                await this.$router.push({
                    name: 'show-storefront',
                    params: {
                        alias: this.store.alias
                    }
                });
            },
            sendUsingWhatsapp() {
                const phone = this.store.whatsapp_mobile_number ? `phone=${this.store.whatsapp_mobile_number.international.replace('+', '')}&` : '';
                window.open(`https://wa.me/?${phone}text=${encodeURIComponent(this.whatsappMessage)}`, "_blank");
                this.clicked = true;
            },
            async showStorePaymentMethod() {
                try {

                    this.isLoadingStorePaymentMethod = true;

                    let config = {
                        params: {
                            _relationships: ['logo', 'paymentMethod'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/store-payment-methods/${this.storePaymentMethodId}`, config);

                    this.storePaymentMethod = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching payment method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch payment method:', error);
                } finally {
                    this.isLoadingStorePaymentMethod = false;
                }
            },
            async createTransaction() {

                try {

                    this.isCreatingTransaction = true;

                    const data = {
                        owner_type: 'order',
                        owner_id: this.order.id,
                        store_id: this.store.id,
                        currency: this.store.currency,
                        payment_status: 'waiting confirmation',
                        amount: this.order.outstanding_total.amount,
                        store_payment_method_id: this.storePaymentMethod.id,
                    };

                    console.log('createTransaction !!!');
                    console.log('createTransaction !!!');
                    console.log('createTransaction !!!');
                    console.log('createTransaction !!!');
                    console.log('createTransaction !!!');
                    await axios.post('/api/transactions', data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating payment';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create payment:', error);
                } finally {
                    this.isCreatingTransaction = false;
                }
            }
        },
        created() {
            this.setup();
        }
    }
</script>
