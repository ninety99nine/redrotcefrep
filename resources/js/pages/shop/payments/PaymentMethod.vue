<template>

    <div class="select-none max-w-xl mx-auto pt-8 pb-40">

        <div class="flex flex-col items-center">

            <div class="w-full grid grid-cols-3 mb-4">

                <div class="col-span-1 flex items-center">

                    <!-- Back Button -->
                    <Button
                        size="xs"
                        type="light"
                        :leftIcon="MoveLeft"
                        :action="navigateToShowShopPaymentMethods">
                        <span>Back</span>
                    </Button>

                </div>

                <div class="col-span-1 flex items-center justify-center">

                    <!-- Logo -->
                    <Skeleton v-if="isLoadingOrder || isLoadingStorePaymentMethod || !hasOrder" width="w-20" height="h-20" :shine="true"></Skeleton>
                    <div v-else class="w-16 h-16 rounded-full overflow-hidden flex items-center justify-center">
                        <img
                            alt="Payment Method Logo"
                            class="h-full object-contain"
                            :src="storePaymentMethod.logo ? storePaymentMethod.logo.path : paymentMethod.image_url"
                        />
                    </div>

                </div>

            </div>

            <!-- Heading -->
            <Skeleton v-if="isLoadingOrder || isLoadingStorePaymentMethod || !hasOrder" width="w-40" height="h-8" :shine="true" class="mb-2"></Skeleton>
            <h2 v-else class="text-4xl font-semibold text-center mb-2">{{ order.outstanding_total.amount_with_currency }}</h2>

            <!-- Instruction -->
            <Skeleton v-if="isLoadingOrder || isLoadingStorePaymentMethod || !hasOrder" width="w-1/2" :shine="true" class="mb-8"></Skeleton>
            <p v-else class="text-gray-500 text-center mb-8">You're almost done! Just complete your payment.</p>

            <!-- Payment Success Alert -->
            <Alert
                dismissable
                class="w-full"
                type="success"
                :title="`${storePaymentMethod.custom_name} payment has been paid successfully.`"
                v-if="!isLoadingOrder && !isLoadingStorePaymentMethod && this.$route.query.status === 'success'"
            />

            <!-- Payment Fail Alert -->
            <Alert
                dismissable
                type="danger"
                class="w-full"
                :title="`${storePaymentMethod.custom_name} payment has failed.`"
                v-else-if="!isLoadingOrder && !isLoadingStorePaymentMethod && this.$route.query.status === 'fail'"
            />

        </div>

        <div class="space-y-3 mb-4">

            <template v-if="isLoadingOrder || isLoadingStorePaymentMethod || !hasOrder">

                <!-- Order Summary (Loading Placeholder) -->
                <div class="flex items-center space-x-4 bg-white p-4 shadow-sm rounded-xl">

                    <Skeleton width="w-10" height="h-10" :shine="true"></Skeleton>

                    <div class="w-full flex justify-between">

                        <div class="w-full space-y-2">

                            <Skeleton width="w-1/3" height="h-4" :shine="true"></Skeleton>

                            <div class="w-full space-y-1">
                                <Skeleton width="w-2/3" :shine="true"></Skeleton>
                                <Skeleton width="w-2/5" :shine="true"></Skeleton>
                            </div>

                        </div>

                        <Skeleton width="w-16" :shine="true"></Skeleton>

                    </div>

                </div>

                <!-- Payment Methods (Loading Placeholder) -->
                <div class="space-y-2">

                    <div class="border-b border-gray-300 shadow-sm rounded-lg py-6 px-4 bg-white space-y-4">

                        <div class="flex items-center space-x-4">
                            <Skeleton width="w-8" height="h-8" :shine="true" class="shrink-0"></Skeleton>
                            <Skeleton width="w-40" :shine="true"></Skeleton>
                        </div>

                        <Skeleton width="w-full" height="h-10" rounded="rounded-lg" :shine="true"></Skeleton>

                        <div class="flex justify-center">
                            <Skeleton width="w-40" :shine="true"></Skeleton>
                        </div>

                        <Skeleton width="w-full" height="h-24" rounded="rounded-lg" :shine="true"></Skeleton>

                    </div>

                </div>

            </template>

            <template v-else>

                <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">

                    <div class="p-4 flex items-center space-x-4">

                        <StoreLogo v-if="store && store.logo" size="w-10 h-10" :showButton="false"></StoreLogo>

                        <div class="w-full flex justify-between space-x-4">

                            <div class="w-full">
                                <p class="text-gray-700 font-semibold text-sm">{{ order.customer_name }}</p>
                                <p class="text-gray-500 text-sm">{{ order.summary }}</p>
                            </div>

                            <p class="text-right text-gray-400 text-xs">Order #{{ order.number }}</p>

                        </div>

                    </div>

                    <div v-if="isConvertingCurrency || conversion" class="bg-gray-50 text-xs text-center border-t border-dashed border-gray-300 py-4">

                        <Skeleton v-if="isConvertingCurrency" width="w-1/3" :shine="true" class="mx-auto"></Skeleton>
                        <p v-else-if="conversion">
                            {{ conversion.from.amount_with_currency }} converts to {{ conversion.to.amount_with_currency }}
                        </p>

                    </div>

                </div>

                <div>

                    <template v-if="automatedVerification">

                        <Button
                            size="lg"
                            type="primary"
                            :action="payOrder"
                            buttonClass="w-full"
                            :disabled="isConvertingCurrency"
                            :loading="isGeneratingPaymentLink">
                            <span>{{ isConvertingCurrency ? 'Converting Currency' : 'Pay' }}</span>
                        </Button>

                    </template>

                    <template v-else-if="manualVerification">

                        <div class="bg-white border border-gray-100 p-4 shadow-sm rounded-xl space-y-4">

                            <div
                                class="flex items-center space-x-2">

                                <div class="w-8 h-8 bg-gray-100 border border-gray-300 rounded-full flex items-center justify-center">
                                    <span class="text-sm text-black font-bold">1</span>
                                </div>

                                <Skeleton v-if="isCheckingIfPhotoIsQrCode" width="w-1/3" :shine="true"></Skeleton>

                                <template v-else-if="paymentLink">
                                    <span class="text-sm">Click to Pay or Scan QR code</span>
                                </template>

                                <template v-else-if="qrCodeString || (photo && photoIsQrCode)">
                                    <span class="text-sm">Scan QR code to Pay</span>
                                </template>

                                <template v-else-if="paymentMethod.type == 'bank transfer'">
                                    <span class="text-sm">Send money to this account</span>
                                </template>

                                <template v-else>
                                    <span class="text-sm">Follow instructions to make payment</span>
                                </template>

                            </div>

                            <template v-if="paymentLink">

                                <Button
                                    size="lg"
                                    type="primary"
                                    buttonClass="w-full"
                                    :action="payViaLink"
                                    :disabled="isConvertingCurrency">
                                    <span>{{ isConvertingCurrency ? 'Converting Currency' : 'Pay' }}</span>
                                </Button>

                                <template v-if="paymentLinkQrCode">

                                    <div
                                        @click="toggleQrCode"
                                        class="w-1/2 mx-auto flex items-center justify-center space-x-2 bg-gray-50 border border-gray-300 rounded-full p-1 cursor-pointer hover:bg-gray-100 active:opacity-80 active:scale-95 transition-all">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                        </svg>
                                        <span class="text-xs"> Scan QR Code to Pay</span>
                                    </div>

                                    <img v-if="showQrCode" :src="paymentLinkQrCode" alt="QR Code" class="border border-gray-300 rounded-lg shadow mx-auto" />

                                </template>

                            </template>

                            <div
                                class="relative"
                                v-else-if="qrCode || photo">

                                <img v-if="qrCodeLogo" class="w-10 h-10 bg-white rounded-md p-1 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2" :src="`/images/qr-code-logos/${qrCodeLogo}.png`">
                                <img :src="qrCode || photo.path" alt="QR Code" :class="['mx-auto', {'border border-gray-300 rounded-lg shadow max-w-60' : qrCode || photoIsQrCode}]" />

                            </div>

                            <div
                                class="space-y-4"
                                v-else-if="supportsDialCode">

                                <template v-if="simplifiedDialCode">
                                    <p class="text-sm">Dial <span @click="dialToPay" class="font-semibold text-blue-700 hover:text-blue-500 cursor-pointer underline"><code>{{ simplifiedDialCode }}</code></span> on {{ storePaymentMethod.custom_name }}, Choose 'Send money' or 'Merchant Payment', Enter phone number or merchant code, input amount, and Confirm with your PIN.</p>
                                </template>
                                <template v-else>
                                    <p class="text-sm">Dial on {{ storePaymentMethod.custom_name }}, Choose 'Send money' or 'Merchant Payment', Enter phone number or merchant code, input amount, and Confirm with your PIN.</p>
                                </template>

                                <Button
                                    size="lg"
                                    type="primary"
                                    :action="dialToPay"
                                    buttonClass="w-full"
                                    v-if="simplifiedDialCode"
                                    :disabled="isConvertingCurrency">
                                    <span>{{ isConvertingCurrency ? 'Converting Currency' : 'Dial to Pay' }}</span>
                                </Button>

                            </div>

                            <div
                                class="bg-gray-50 text-sm border border-gray-300 p-4 rounded-xl space-y-4"
                                v-if="additionalFields.length || storePaymentMethod.instruction">

                                <div
                                    :key="index"
                                    class="flex items-center justify-between"
                                    v-for="(additionalField, index) in additionalFields">

                                    <span>{{ additionalField.label }}</span>

                                    <div class="flex items-center space-x-1">

                                        <Copy
                                            :showText="false"
                                            :text="additionalField.value">

                                            <template #trigger>
                                                <div class="flex items-center space-x-1.5">
                                                    <CopyIcon size="16" class="shrink-0"></CopyIcon>
                                                    <span>{{ additionalField.displayValue ?? additionalField.value }}</span>
                                                </div>
                                            </template>

                                        </Copy>

                                    </div>

                                </div>

                                <div v-if="storePaymentMethod.instruction" class="bg-gray-50 text-sm border-t border-dashed pt-4">
                                    <p>{{ storePaymentMethod.instruction }}</p>
                                </div>

                            </div>

                        </div>

                        <div class="flex justify-center">
                            <div class="border-l-4 border-dashed border-gray-100 h-4"></div>
                        </div>

                        <div class="bg-white border border-gray-100 p-4 shadow-sm rounded-xl space-y-4">

                            <div class="flex space-x-2">

                                <div class="w-8 h-8 bg-gray-100 border border-gray-300 rounded-full flex items-center justify-center">
                                    <span class="text-sm text-black font-bold">2</span>
                                </div>

                                <div>
                                    <p class="text-sm">Did you pay?</p>
                                    <p class="text-xs text-gray-500">Let us know so that we confirm your payment on our side</p>
                                </div>

                            </div>

                            <div class="space-y-2">

                                <Button
                                    size="md"
                                    type="success"
                                    buttonClass="w-full"
                                    :action="navigateToShowShopConfirmingPayment">
                                    <span>I have paid</span>
                                </Button>

                                <div class="py-2 flex items-center text-xs text-gray-400 uppercase before:flex-1 before:border-t before:border-gray-200 before:me-6 after:flex-1 after:border-t after:border-gray-200 after:ms-6">
                                    Or
                                </div>

                                <Button
                                    size="md"
                                    type="light"
                                    buttonClass="w-full"
                                    :action="navigateToShowShopPendingPayment">
                                    <span>Contact us before paying</span>
                                </Button>

                            </div>

                        </div>

                    </template>

                </div>

            </template>

        </div>

    </div>

</template>

<script>

    import jsQR from 'jsqr';
    import QRCode from 'qrcode';
    import Copy from '@Partials/Copy.vue';
    import Alert from '@Partials/Alert.vue';
    import Button from '@Partials/Button.vue';
    import { MoveLeft } from 'lucide-vue-next';
    import Skeleton from '@Partials/Skeleton.vue';
    import StoreLogo from '@Components/StoreLogo.vue';
    import { Copy as CopyIcon } from 'lucide-vue-next';
    import { parsePhoneNumberFromString } from 'libphonenumber-js';

    export default {
        inject: ['formState', 'storeState', 'orderState', 'notificationState'],
        components: { Copy, CopyIcon, Alert, Button, Skeleton, StoreLogo },
        data() {
            return {
                MoveLeft,
                qrCode: null,
                conversion: null,
                showQrCode: false,
                photoIsQrCode: null,
                paymentLinkQrCode: null,
                storePaymentMethod: null,
                isConvertingCurrency: false,
                isGeneratingPaymentLink: false,
                isCheckingIfPhotoIsQrCode: false,
                isLoadingStorePaymentMethod: false,
            }
        },
        watch: {
            order() {
                this.orderLoaded();
            },
            storePaymentMethod() {
                this.storePaymentMethodLoaded();
            },
            async paymentLink(newVal) {
                this.paymentLinkQrCode = await this.generateQRCode(newVal);
            },
            async photo(newVal) {
                if(newVal) {
                    this.photoIsQrCode = await this.isQrCode(newVal.path);
                }
            },
            async qrCodeString(newVal) {
                this.qrCode = await this.generateQRCode(newVal, 200);
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            storePaymentMethodId() {
                return this.$route.params.store_payment_method_id;
            },
            conversionCurrency() {

                if(this.storePaymentMethod.configs?.currency) {
                    return this.storePaymentMethod.configs?.currency;
                }

                const supportedCurrencies = this.paymentMethod?.currencies ?? [];

                if (supportedCurrencies.length === 0) {
                    return 'USD';   // fallback if currencies are not defined
                }

                const orderCurrency = this.order?.currency.toUpperCase();

                if (orderCurrency && supportedCurrencies.includes(orderCurrency)) {
                    return orderCurrency; // use store currency if supported
                }

                return supportedCurrencies[0]; // fallback to first supported
            },
            conversionAmount() {
                return this.conversion?.to?.amount;  //  Amount without the money comma separators
            },
            paymentMethod() {
                return this.storePaymentMethod.payment_method;
            },
            manualVerification() {
                return !this.automatedVerification;
            },
            automatedVerification() {
                return this.paymentMethod.automated_verification;
            },
            photo() {
                return this.storePaymentMethod?.photo;
            },
            supportsDialCode() {
                return this.storePaymentMethod.configs.hasOwnProperty('dial_code');
            },
            dialCode() {
                const storeCountry = this.store.country.toUpperCase();
                let dialCode = this.storePaymentMethod.configs?.dial_code;
                const rawUssdCodes = this.paymentMethod?.ussd_codes ?? {};

                const supportedUssdCodes = Object.fromEntries(
                    Object.entries(rawUssdCodes).map(([key, value]) => [key.toUpperCase(), value])
                );

                // Fallback if custom dial code is not provided
                dialCode = dialCode ?? supportedUssdCodes[storeCountry] ?? null;

                if (dialCode) {
                    const amount = Math.round(this.conversionAmount);
                    const reference = this.order.number;

                    dialCode = dialCode
                        .replace('{amount}', amount)
                        .replace('{ref}', reference);
                }

                return dialCode;
            },
            simplifiedDialCode() {
                if (!this.dialCode) return null;

                const parts = this.dialCode.split('*');
                if (parts.length < 2) return this.dialCode; // Already simple

                return parts[0] + '*' + parts[1].replace('#', '') + '#';
            },
            paymentLink() {
                if (!this.order || !this.storePaymentMethod || this.isConvertingCurrency) return null;

                let url = null;
                const type = this.paymentMethod.type;
                const orderNumber = this.order.number;
                const configs = this.storePaymentMethod.configs;

                if (type === 'paypal me') {

                    const username = configs.username;
                    url = `https://www.paypal.com/paypalme/${username}/${this.conversionAmount}`;

                }else if (type === 'wise') {

                    const idType = configs.id_type;

                    if(idType == 'wise username') {

                        const username = configs.username;
                        url = `https://wise.com/pay/me/${username}`;

                    }else{

                        const businessUsername = configs.business_username;
                        url = `https://wise.com/pay/business/${businessUsername}`;

                    }
                }else if (type === 'revolut') {

                    const username = configs.username;
                    url = `https://revolut.me/${username}`;

                }else if (type === 'ziina') {

                    const username = configs.username;
                    url = `https://pay.ziina.com/${username}?amount=${this.conversionAmount}`;

                }else if (type === 'pesapal') {

                    const username = configs.username;
                    const mobile = this.order.customer_mobile_number?.international?.replace('+', '') ?? '';
                    const email = encodeURIComponent(this.order.customer_email ?? '');
                    const currency = this.conversionCurrency;
                    const amount = this.conversionAmount;
                    const reference = orderNumber;

                    url = `https://payments.pesapal.com/${username}` +
                        `?email=${email}` +
                        `&amt=${amount}` +
                        `&mobile=${mobile}` +
                        `&cur=${currency}` +
                        `&desc=${reference}`;

                }else if (type === 'cash app') {

                    const username = configs.username;
                    url = `https://cash.app/${username}/${this.conversionAmount}`;

                }else if (type === 'venmo') {

                    const username = configs.username;
                    url = `https://venmo.com/?txn=pay&recipients=${username}&amount=${this.conversionAmount}&note=Order%20${orderNumber}&audience=private`;

                }else if (type === 'zelle') {

                    const token = configs.email;    // typically an email or phone number

                    const payload = {
                        amount: this.conversionAmount,
                        name: this.store.name,
                        action: "payment",
                        token: token
                    };

                    const base64Data = btoa(encodeURIComponent(JSON.stringify(payload)));
                    url = `https://enroll.zellepay.com/qr-codes?data=${base64Data}`;

                }else if (type === 'yoco') {

                    const username = configs.username;
                    const encodedEmail = encodeURIComponent(this.order.customer_email ?? '');
                    const encodedLastName = encodeURIComponent(this.order.customer_last_name ?? '');
                    const encodedFirstName = encodeURIComponent(this.order.customer_first_name ?? '');

                    const baseUrl = window.location.origin + this.$route.path;
                    const cancelUrl = encodeURIComponent(`${baseUrl}?status=fail`);
                    const successUrl = encodeURIComponent(`${baseUrl}?status=success`);

                    url = `https://pay.yoco.com/${username}` +
                        `?amount=${this.conversionAmount}` +
                        `&reference=${orderNumber}` +
                        `&firstName=${encodedFirstName}` +
                        `&lastName=${encodedLastName}` +
                        `&email=${encodedEmail}` +
                        `&successUrl=${successUrl}` +
                        `&cancelUrl=${cancelUrl}`;

                }else if (type === 'ikhokha') {

                    url = configs.url;

                }else if (type === 'snapscan') {

                    url = `${configs.url}?amount=${this.conversionAmount}`;

                }else if (type === 'lynk') {

                    url = configs.url;

                }else if (type === 'wave') {

                    const idType = configs.id_type;

                    if(idType == 'wave payment link') {
                        url = configs.url;
                    }

                }else if (type === 'wigwag') {

                    const username = configs.username;
                    url = `https://just.wigwag.me/${username}/${this.conversionAmount}/${orderNumber}`;

                }else if (type === 'tikkie') {

                    url = configs.url;

                }else if (type === 'instapay') {

                    const idType = configs.id_type;

                    if(idType == 'instapay payment link') {
                        url = configs.url;
                    }

                }else if (type === 'touch n go') {

                    url = configs.url;

                }else if (type === 'bkash') {

                    url = configs.url;

                }else if (type === 'kaspi') {

                    url = configs.url;

                }else if (type === 'other') {

                    if(configs.paymentLink) {

                        url = configs.paymentLink;

                        if(url) {

                            const amount = this.conversionAmount;
                            const currency = this.conversionCurrency;
                            const lastName = this.order.customer_last_name;
                            const firstName = this.order.customer_first_name;
                            const email = encodeURIComponent(this.order.customer_email ?? '');
                            const mobile = this.order.customer_mobile_number?.international ?? '';

                            url = url
                                .replace('{email}', email)
                                .replace('{amount}', amount)
                                .replace('{mobile}', mobile)
                                .replace('{ref}', orderNumber)
                                .replace('{currency}', currency)
                                .replace('{lastName}', lastName)
                                .replace('{firstName}', firstName);
                        }

                    }

                }

                return url;
            },
            qrCodeString() {
                return this.emvcoQrCodeString || this.epcQrCodeString || this.mpesaQrCodeString || this.orangeMoneyQrCodeString;
            },
            emvcoQrCodeString() {
                if (!this.order || !this.storePaymentMethod || this.isConvertingCurrency) return null;

                const type = this.paymentMethod.type;
                const configs = this.storePaymentMethod.configs;
                const orderNumber = this.order.number.toString();

                if (type === 'gcash') {

                    const amount = this.conversionAmount;
                    const amountLength = amount.length.toString().padStart(2, '0');

                    const storeName = this.store.name.substring(0, 25);
                    const storeNameLength = storeName.length.toString().padStart(2, '0');

                    const merchantInformation = `27830012com.p2pqrpay0111GXCHPHM2XXX02089996440303152170200000006560417DWQM4TK3JDN${configs.user_id}`;

                    // Generate QR Code string in EMVCo format
                    const rawPayload = (
                        `000201` +                                            // Tag 00 → Format Type - Merchant Presented Mode
                        `010212` +                                            // Tag 01 → Point of Initiation Method - Dynamic QR code, the amount is embedded
                        `${merchantInformation}` +                            // Tag 27 → Merchant Account Information
                        `52040000` +                                          // Tag 52 → Merchant Category Code - 0000 = unspecified / generic merchant
                        `5303608` +                                           // Tag 53 → Transaction Currency
                        `54${amountLength}${amount}` +                        // Tag 54 → Transaction Amount
                        `5802PH` +                                            // Tag 58 → Country Code
                        `59${storeNameLength}${storeName}` +                  // Tag 59 → Merchant Name
                        `6011Philippines` +                                   // Tag 60 → Merchant City
                        `6304`                                                // Tag 63 → Cyclic redundancy check (CRC for data integrity)
                    );

                    const crc = this.generateCrc16(rawPayload);
                    return rawPayload + crc;

                }else if (type === 'pix') {

                    const amount = this.conversionAmount;
                    const amountLength = amount.length.toString().padStart(2, '0');

                    const storeName = this.store.name.substring(0, 25);
                    const storeNameLength = storeName.length.toString().padStart(2, '0');

                    let pixKey = null;
                    const idType = configs.id_type;

                    if(idType == 'phone number') {
                        pixKey = configs.phone_number;
                    }else if(idType == 'email') {
                        pixKey = configs.email;
                    }else if(idType == 'cpf') {
                        pixKey = configs.cpf;
                    }else if(idType == 'cnpj') {
                        pixKey = configs.cnpj;
                    }

                    // Build Merchant Account Information (Tag 26)
                    const gui = 'br.gov.bcb.pix';
                    const guiField = `00${gui.length.toString().padStart(2, '0')}${gui}`;
                    const keyField = `01${pixKey.length.toString().padStart(2, '0')}${pixKey}`;
                    const merchantAccount = `26${(guiField + keyField).length.toString().padStart(2, '0')}${guiField}${keyField}`;

                    // Build Additional Data Field (Tag 62)
                    const ref = `REF${orderNumber}`;
                    const referenceField = `01${ref.length.toString().padStart(2, '0')}${ref}`;
                    const additionalDataField = `62${referenceField.length.toString().padStart(2, '0')}${referenceField}`;

                    // Payload without CRC (Tag 63)
                    const rawPayload = (
                        `000201` +                                            // Tag 00 → Format Type - Merchant Presented Mode
                        `010212` +                                            // Tag 01 → Point of Initiation Method - Dynamic QR code, the amount is embedded
                        `${merchantAccount}` +                                // Tag 26 → Merchant Account Information
                        `52040000` +                                          // Tag 52 → Merchant Category Code - 0000 = unspecified / generic merchant
                        `5303986` +                                           // Tag 53 → Transaction Currency
                        `54${amountLength}${amount}` +                        // Tag 54 → Transaction Amount
                        `5802BR` +                                            // Tag 58 → Country Code
                        `59${storeNameLength}${storeName}` +                  // Tag 59 → Merchant Name
                        `6006Brazil` +                                        // Tag 60 → Merchant City
                        `${additionalDataField}` +                            // Tag 62 → Additional Data Field (Order Number - optional)
                        `6304`                                                // Tag 63 → Cyclic redundancy check (CRC for data integrity)
                    );

                    const crc = this.generateCrc16(rawPayload);
                    return rawPayload + crc;

                }else if (type === 'paynow') {

                    const amount = this.conversionAmount;
                    const amountLength = amount.length.toString().padStart(2, '0');

                    const storeName = this.store.name.substring(0, 25);
                    const storeNameLength = storeName.length.toString().padStart(2, '0');

                    let payNowKey = null;
                    const idType = configs.id_type;

                    if(idType == 'phone number') {
                        payNowKey = configs.phone_number;
                    }else if(idType == 'uen') {
                        payNowKey = configs.uen;
                    }else if(idType == 'vpa') {
                        payNowKey = configs.vpa;
                    }

                    // Build Merchant Account Information (Tag 26)
                    const gui = 'SG.PAYNOW';
                    const guiField = `00${gui.length.toString().padStart(2, '0')}${gui}`;
                    const keyType = '11'; // 11 indicates phone number
                    const keyTypeField = `01${keyType.length.toString().padStart(2, '0')}${keyType}`;
                    const keyField = `02${payNowKey.length.toString().padStart(2, '0')}${payNowKey}`;
                    const merchantAccount = `26${(guiField + keyTypeField + keyField).length.toString().padStart(2, '0')}${guiField}${keyTypeField}${keyField}`;

                    // Build Additional Data Field (Tag 62)
                    const ref = `REF${orderNumber}`;
                    const referenceField = `01${ref.length.toString().padStart(2, '0')}${ref}`;
                    const additionalDataField = `62${referenceField.length.toString().padStart(2, '0')}${referenceField}`;

                    // Payload without CRC (Tag 63)
                    const rawPayload = (
                        `000201` +                                            // Tag 00 → Format Type - Merchant Presented Mode
                        `010212` +                                            // Tag 01 → Point of Initiation Method - Dynamic QR code, the amount is embedded
                        `${merchantAccount}` +                                // Tag 26 → Merchant Account Information
                        `52040000` +                                          // Tag 52 → Merchant Category Code - 0000 = unspecified / generic merchant
                        `5303702` +                                           // Tag 53 → Transaction Currency (702 = SGD)
                        `54${amountLength}${amount}` +                        // Tag 54 → Transaction Amount
                        `5802SG` +                                            // Tag 58 → Country Code
                        `59${storeNameLength}${storeName}` +                  // Tag 59 → Merchant Name
                        `6009Singapore` +                                     // Tag 60 → Merchant City
                        `${additionalDataField}` +                            // Tag 62 → Additional Data Field (Order Number - optional)
                        `6304`                                                // Tag 63 → Cyclic redundancy check (CRC for data integrity)
                    );

                    const crc = this.generateCrc16(rawPayload);
                    return rawPayload + crc;

                }else if (type === 'promptpay') {

                    const amount = this.conversionAmount;
                    const amountLength = amount.length.toString().padStart(2, '0');

                    const storeName = this.store.name.substring(0, 25);
                    const storeNameLength = storeName.length.toString().padStart(2, '0');

                    let promptPayKey = null;
                    const idType = configs.id_type;

                    if (idType === 'phone number') {
                        promptPayKey = configs.phone_number.replace('+', '');
                    } else if (idType === 'national id or tax id') {
                        promptPayKey = configs.national_id_or_tax_id;
                    } else if (idType === 'e-wallet id') {
                        promptPayKey = configs.ewallet_id;
                    } else if (idType === 'bank account number') {
                        promptPayKey = configs.bank_account_number;
                    }

                    // PromptPay AID
                    const aid = 'A000000677010111';
                    const aidField = `00${aid.length.toString().padStart(2, '0')}${aid}`;
                    const keyField = `01${promptPayKey.length.toString().padStart(2, '0')}${promptPayKey}`;
                    const merchantAccount = `29${(aidField + keyField).length.toString().padStart(2, '0')}${aidField}${keyField}`;

                    // Build Additional Data Field (Tag 62)
                    const ref = `REF${orderNumber}`;
                    const referenceField = `01${ref.length.toString().padStart(2, '0')}${ref}`;
                    const additionalDataField = `62${referenceField.length.toString().padStart(2, '0')}${referenceField}`;

                    // Payload without CRC (Tag 63)
                    const rawPayload = (
                        `000201` +                                            // Tag 00 → Format Type - Merchant Presented Mode
                        `010212` +                                            // Tag 01 → Point of Initiation Method - Dynamic QR code
                        `${merchantAccount}` +                                // Tag 29 → PromptPay Merchant Account Info
                        `52040000` +                                          // Tag 52 → Merchant Category Code - Generic
                        `5303764` +                                           // Tag 53 → Transaction Currency (764 = THB)
                        `54${amountLength}${amount}` +                        // Tag 54 → Transaction Amount
                        `5802TH` +                                            // Tag 58 → Country Code
                        `59${storeNameLength}${storeName}` +                  // Tag 59 → Merchant Name
                        `6007Bangkok` +                                       // Tag 60 → Merchant City
                        `${additionalDataField}` +                            // Tag 62 → Reference data
                        `6304`                                                // Tag 63 → CRC (will be added below)
                    );

                    const crc = this.generateCrc16(rawPayload);
                    return rawPayload + crc;
                }

                return null;
            },
            epcQrCodeString() {
                if (!this.order || !this.storePaymentMethod || this.isConvertingCurrency) return null;

                const type = this.paymentMethod.type;
                const configs = this.storePaymentMethod.configs;
                const orderNumber = this.order.number.toString();

                if (type === 'sepa credit transfer') {

                    const bic = configs.bic ?? '';                                  // optional, e.g. "DEUTDEFFXXX"
                    const name =  configs.account_holder_name.substring(0, 70);     // Max 70 chars
                    const iban = configs.bank_account_number;                       // e.g. "DE44500105175407324931"
                    const amount = `EUR${this.conversionAmount}`;                   // EUR amount
                    const reference = `Ref ${orderNumber}`;                         // Free-form text or "RF" format

                    // Construct the SEPA QR payload (EPC069-12 format)
                    const payload = [
                        'BCD',                  // Service tag
                        '002',                  // Version
                        '1',                    // Character set (1 = UTF-8)
                        'SCT',                  // SEPA Credit Transfer
                        bic,                    // BIC (optional but recommended)
                        name,                   // Beneficiary name
                        iban,                   // IBAN
                        amount,                 // Amount
                        '',                     // Purpose code (optional)
                        '',                     // Remittance info structured (e.g. RF18539007547034)
                        reference               // Remittance info unstructured
                    ].join('\n');

                    return payload;
                }

                return null;
            },
            mpesaQrCodeString() {
                if (!this.order || !this.storePaymentMethod || this.isConvertingCurrency) return null;

                const type = this.paymentMethod.type;
                const configs = this.storePaymentMethod.configs;
                const orderNumber = this.order.number.toString();

                if (type === 'm-pesa') {
                    const amount = this.conversionAmount;
                    const reference = orderNumber;

                    let recipientId = null;

                    if (configs.id_type === 'phone number') {
                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        recipientId = phoneNumber.formatNational().replace(/\s+/g, '');
                    } else if (configs.id_type === 'till number') {
                        recipientId = configs.till_number;
                    }

                    if (!recipientId) return null;

                    // Return format: SM|<recipient>|<amount>|<reference>
                    return `SM|${recipientId}|${amount}|${reference}`;
                }

                return null;
            },
            orangeMoneyQrCodeString() {
                if (!this.order || !this.storePaymentMethod || this.isConvertingCurrency) return null;

                const type = this.paymentMethod.type;
                const configs = this.storePaymentMethod.configs;

                if (type === 'orange money') {
                    return configs.merchant_code_qr;
                }

                return null;
            },
            additionalFields() {
                let fields = [];

                const type = this.paymentMethod.type;
                const configs = this.storePaymentMethod.configs;

                if(type === 'paypal me') {

                    fields.push({
                        label: 'ID',
                        value: configs.username
                    });

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'wise') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'revolut') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'ziina') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'pesapal') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'cash app') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'venmo') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'zelle') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'yoco') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'ikhokha') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'snapscan') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'lynk') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'wave') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'wigwag') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'tikkie') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'mb way') {

                    const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                    let nationalNumber =  phoneNumber.formatNational();
                    nationalNumber = nationalNumber.replace(/\s+/g, '');

                    fields.push({
                        label: 'MB Way Phone Number',
                        value: nationalNumber,
                        displayValue: nationalNumber
                    });

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'mcb juice') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }else if(idType == 'mcb account number') {

                        const mcbAccountNumber = configs.mcb_account_number;

                        fields.push({
                            label: 'MCB Account Number',
                            value: mcbAccountNumber,
                            displayValue: mcbAccountNumber
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'instapay') {

                    const idType = configs.id_type;

                    if(idType == 'instapay id') {

                        const instapayID = configs.instapay_id;

                        fields.push({
                            label: 'Instapay ID',
                            value: instapayID,
                            displayValue: instapayID
                        });

                    }else if(idType == 'phone number') {

                        const phoneNumber = configs.phone_number.replace('+', '');

                        fields.push({
                            label: 'Phone number',
                            value: phoneNumber,
                            displayValue: phoneNumber
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'gcash') {

                    fields.push({
                        label: 'ID',
                        value: configs.user_id
                    });

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'pix') {

                    const idType = configs.id_type;

                    if(idType == 'email') {

                        const email = configs.email;

                        fields.push({
                            label: 'Email',
                            value: email,
                            displayValue: email
                        });

                    }else if(idType == 'phone number') {

                        const phoneNumber = configs.phone_number.replace('+', '');

                        fields.push({
                            label: 'Phone number',
                            value: phoneNumber,
                            displayValue: phoneNumber
                        });

                    }else if(idType == 'cpf') {

                        const cpf = configs.cpf;

                        fields.push({
                            label: 'CPF',
                            value: cpf,
                            displayValue: cpf
                        });

                    }else if(idType == 'cnpj') {

                        const cnpj = configs.cnpj;

                        fields.push({
                            label: 'CNPJ (Brazilian companies)',
                            value: cnpj,
                            displayValue: cnpj
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'qris') {

                    fields.push({
                        label: 'Merchant Name',
                        value: this.store.name,
                        displayValue: this.store.name,
                    });

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'duitnow') {

                    fields.push({
                        label: 'Merchant Name',
                        value: this.store.name,
                        displayValue: this.store.name,
                    });

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'paynow') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }else if(idType == 'uen') {

                        const uen = configs.uen;

                        fields.push({
                            label: 'UEN',
                            value: uen,
                            displayValue: uen
                        });

                    }else if(idType == 'vpa') {

                        const vpa = configs.vpa;

                        fields.push({
                            label: 'VPA (Virtual Payment Address)',
                            value: vpa,
                            displayValue: vpa
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'promptpay') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = configs.phone_number.replace('+', '');

                        fields.push({
                            label: 'Phone number',
                            value: phoneNumber,
                            displayValue: phoneNumber
                        });

                    }else if(idType == 'national id or tax id') {

                        const nationalIdOrTaxId = configs.national_id_or_tax_id;

                        fields.push({
                            label: 'National ID or Tax ID',
                            value: nationalIdOrTaxId,
                            displayValue: nationalIdOrTaxId
                        });

                    }else if(idType === 'e-wallet id') {

                        const eWalletID = configs.ewallet_id;

                        fields.push({
                            label: 'E-wallet ID',
                            value: eWalletID,
                            displayValue: eWalletID
                        });

                    }else if(idType === 'bank account number') {

                        const bankAccountNumber = configs.bank_account_number;

                        fields.push({
                            label: 'Bank Account Number',
                            value: bankAccountNumber,
                            displayValue: bankAccountNumber
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'touch n go') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'upi') {

                    const upiID = configs.upi_id;

                    fields.push({
                        label: 'ID',
                        value: upiID,
                        displayValue: upiID
                    });

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'sepa credit transfer') {

                    const accountHolderName = configs.account_holder_name;
                    const bankAccountNumber = configs.bank_account_number;
                    const bic = configs.bic;

                    fields.push({
                        label: 'Merchant Name',
                        value: accountHolderName,
                        displayValue: accountHolderName
                    });

                    fields.push({
                        label: 'Account Number',
                        value: bankAccountNumber,
                        displayValue: bankAccountNumber
                    });

                    if(bic) {

                        fields.push({
                            label: 'BIC',
                            value: bic,
                            displayValue: bic
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'oxxo') {

                    const oxxoAccountNumber = configs.oxxo_account_number;

                    fields.push({
                        label: 'Número de cuenta',
                        value: oxxoAccountNumber,
                        displayValue: oxxoAccountNumber
                    });

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'm-pesa') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }else if(idType == 'till number') {

                        const tillNumber = configs.till_number;

                        fields.push({
                            label: 'Till number',
                            value: tillNumber,
                            displayValue: tillNumber
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'airtel') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }else if(idType == 'merchant code') {

                        const merchantCode = configs.merchant_code;

                        fields.push({
                            label: 'Merchant code',
                            value: merchantCode,
                            displayValue: merchantCode
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'mtn momo') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }else if(idType == 'merchant code') {

                        const merchantCode = configs.merchant_code;

                        fields.push({
                            label: 'Merchant code',
                            value: merchantCode,
                            displayValue: merchantCode
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'orange money') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }else if(idType == 'merchant code') {

                        const merchantCode = configs.merchant_code;

                        fields.push({
                            label: 'Merchant code',
                            value: merchantCode,
                            displayValue: merchantCode
                        });

                    }else if(idType == 'merchant code qr') {

                        const merchantCodeQR = configs.merchant_code_qr;

                        fields.push({
                            label: 'Merchant code (QR)',
                            value: merchantCodeQR,
                            displayValue: merchantCodeQR
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'cellmoni') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }else if(idType == 'merchant code') {

                        const merchantCode = configs.merchant_code;

                        fields.push({
                            label: 'Merchant code',
                            value: merchantCode,
                            displayValue: merchantCode
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'moncash') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }else if(idType == 'merchant code') {

                        const merchantCode = configs.merchant_code;

                        fields.push({
                            label: 'Merchant code',
                            value: merchantCode,
                            displayValue: merchantCode
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'tigopesa') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }else if(idType == 'merchant code') {

                        const merchantCode = configs.merchant_code;

                        fields.push({
                            label: 'Merchant code',
                            value: merchantCode,
                            displayValue: merchantCode
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'ecocash') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }else if(idType == 'merchant code') {

                        const merchantCode = configs.merchant_code;

                        fields.push({
                            label: 'Merchant code',
                            value: merchantCode,
                            displayValue: merchantCode
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'innbucks') {

                    const idType = configs.id_type;

                    if(idType == 'phone number') {

                        const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                        let nationalNumber =  phoneNumber.formatNational();
                        nationalNumber = nationalNumber.replace(/\s+/g, '');

                        fields.push({
                            label: 'Phone number',
                            value: nationalNumber,
                            displayValue: nationalNumber
                        });

                    }else if(idType == 'merchant code') {

                        const merchantCode = configs.merchant_code;

                        fields.push({
                            label: 'Merchant code',
                            value: merchantCode,
                            displayValue: merchantCode
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'esewa') {

                    const phoneNumber = parsePhoneNumberFromString(configs.phone_number);
                    let nationalNumber =  phoneNumber.formatNational();
                    nationalNumber = nationalNumber.replace(/\s+/g, '');

                    fields.push({
                        label: 'Phone number',
                        value: nationalNumber,
                        displayValue: nationalNumber
                    });

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'bkash') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'kaspi') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'bank transfer') {

                    const bankName = configs.bank_name;
                    const accountNumber = configs.account_number;
                    const swiftOrBankCode = configs.swift_or_bank_code;
                    const accountHolderName = configs.account_holder_name;
                    const branchCodeOrSortCode = configs.branch_code_or_sort_code;

                    fields.push({
                        label: 'Account number',
                        value: accountNumber,
                        displayValue: accountNumber
                    });

                    fields.push({
                        label: 'Account Holder Name',
                        value: accountHolderName,
                        displayValue: accountHolderName
                    });

                    fields.push({
                        label: 'Bank Name',
                        value: bankName,
                        displayValue: bankName
                    });

                    if(swiftOrBankCode) {

                        fields.push({
                            label: 'SWIFT / Bank Code',
                            value: swiftOrBankCode,
                            displayValue: swiftOrBankCode
                        });

                    }

                    if(branchCodeOrSortCode) {

                        fields.push({
                            label: 'Branch Code / Sort Code',
                            value: branchCodeOrSortCode,
                            displayValue: branchCodeOrSortCode
                        });

                    }

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }else if(type === 'cash on delivery') {

                    fields.push({
                        label: 'Reference',
                        value: this.order.number,
                        displayValue: `#${this.order.number}`
                    });

                }

                return fields;
            },
            qrCodeColors() {
                const type = this.paymentMethod?.type;

                if (type === 'paynow') {
                    return {
                        dark: '#800080',   // Purple QR dots
                        light: '#ffffff'   // White background
                    };
                }

                return {
                    dark: '#000000',   // Black QR dots
                    light: '#ffffff'   // White background
                };
            },
            qrCodeLogo() {
                const type = this.paymentMethod.type;

                if (type === 'gcash') {
                    return 'qrph';
                }else if (type === 'paynow') {
                    return 'paynow';
                }else if (type === 'm-pesa') {
                    return 'mpesa';
                }else if (type === 'orange money') {
                    return 'orange-money';
                }

                return false;
            },
        },
        methods: {
            orderLoaded() {
                if(this.order.payment_status === 'paid' || this.order.outstanding_total.amount == 0 || this.store.skip_payment_page) {
                    this.navigateToShowShopOrder();
                    return;
                }

                if(this.storePaymentMethod && !this.isConvertingCurrency) {
                    this.convertCurrency();
                }
            },
            storePaymentMethodLoaded() {
                if(this.order && !this.isConvertingCurrency) {
                    this.convertCurrency();
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
            async navigateToShowShopConfirmingPayment() {
                await this.$router.push({
                    name: 'show-shop-confirming-payment',
                    query: {
                        store_payment_method_id: this.storePaymentMethod.id
                    },
                    params: {
                        alias: this.store.alias,
                        order_id: this.order.id,
                    }
                });
            },
            async navigateToShowShopPendingPayment() {
                await this.$router.push({
                    name: 'show-shop-pending-payment',
                    query: {
                        store_payment_method_id: this.storePaymentMethod.id
                    },
                    params: {
                        alias: this.store.alias,
                        order_id: this.order.id,
                    }
                });
            },
            navigateToShowShopPaymentMethods() {
                this.$router.push({
                    name: 'show-shop-payment-methods',
                    params: {
                        'alias': this.store.alias,
                        'order_id': this.order.id
                     }
                });
            },
            payViaLink() {
                window.open(this.paymentLink, '_blank');
            },
            dialToPay() {
                const code = this.dialCode;
                if (!code) return;

                const encoded = encodeURIComponent(code);
                const telLink = `tel:${encoded}`;

                // Trigger the dial
                window.open(telLink, '_blank');
            },
            toggleQrCode() {
                this.showQrCode = !this.showQrCode;
            },
            generateCrc16(input) {
                let crc = 0xFFFF;

                for (let i = 0; i < input.length; i++) {
                    crc ^= input.charCodeAt(i) << 8;

                    for (let j = 0; j < 8; j++) {
                        if (crc & 0x8000) {
                            crc = (crc << 1) ^ 0x1021;
                        } else {
                            crc <<= 1;
                        }
                        crc &= 0xFFFF;
                    }
                }

                return crc.toString(16).toUpperCase().padStart(4, '0');
            },
            async generateQRCode(data, width = 150) {
                try {

                    return await QRCode.toDataURL(data, {
                        margin: 2,
                        width: width,
                        color: this.qrCodeColors
                    })

                } catch (err) {
                    console.error('Failed to generate QR code:', err);
                }
            },
            async isQrCode(filePath) {
                this.isCheckingIfPhotoIsQrCode = true;

                const response = await fetch(filePath);
                const blob = await response.blob();
                const bitmap = await createImageBitmap(blob);

                const canvas = document.createElement('canvas');
                canvas.width = bitmap.width;
                canvas.height = bitmap.height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(bitmap, 0, 0);

                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                this.isCheckingIfPhotoIsQrCode = false;

                return !!code; // returns true if a QR code is detected
            },
            async convertCurrency() {
                try {

                    this.isConvertingCurrency = true;

                    let data = {
                        'amount': this.order.outstanding_total.amount,
                        'from': this.order.currency.toUpperCase(),
                        'to': this.conversionCurrency,
                    }

                    const response = await axios.post(`/api/convert/currency`, data);

                    this.conversion = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while converting currency';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to convert currency:', error);

                } finally {
                    this.isConvertingCurrency = false;
                }
            },
            async showStorePaymentMethod() {
                try {

                    this.isLoadingStorePaymentMethod = true;

                    let config = {
                        params: {
                            _relationships: ['logo', 'photo', 'paymentMethod'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/store-payment-methods/${this.storePaymentMethodId}`, config);

                    this.storePaymentMethod = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching payment method';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch payment method:', error);

                    if (error.response?.status === 404) {
                        await this.$router.replace({
                            name: 'show-storefront',
                            params: {
                                alias: this.store.alias
                            }
                        });
                    }

                } finally {
                    this.isLoadingStorePaymentMethod = false;
                }
            },
            async payOrder() {

                try {

                    if(this.isGeneratingPaymentLink) return;

                    this.isGeneratingPaymentLink = true;

                    let data = {
                        'store_payment_method_id': this.storePaymentMethod.id
                    };

                    const response = await axios.post(`/api/orders/${this.order.id}/pay`, data);

                    const successful = response.data.successful;

                    if(successful) {

                        const transaction = response.data.transaction;

                        if(transaction.metadata.hasOwnProperty('message')) {
                            const message = transaction.metadata.message;
                            this.notificationState.showWarningNotification(message);
                        }else{
                            const dpoPaymentUrl = response.data.transaction.metadata.dpo_payment_url;
                            window.location.href = dpoPaymentUrl;
                        }

                    }else{
                        const message = response.data.message;
                        this.notificationState.showWarningNotification(message);
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while paying';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to pay:', error);
                } finally {
                    this.isGeneratingPaymentLink = false;
                }

            }
        },
        created() {
            if(this.order) this.orderLoaded();
            this.showStorePaymentMethod();
        }
    };

</script>
