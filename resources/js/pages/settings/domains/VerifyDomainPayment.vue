<template>
    <div class="min-h-screen relative">
        <div class="z-10 pt-32 px-20 relative">
            <div class="flex flex-col items-center" v-if="isLoadingTransaction || isVerifyingPayment">
                <h1 class="flex justify-center items-center space-x-2 text-xl text-gray-700 font-semibold mb-4">
                    <CreditCard size="24"></CreditCard>
                    <span>Verifying Domain Payment</span>
                </h1>
                <p class="w-96 text-sm text-gray-600 text-center mb-8">One moment, we are verifying your domain purchase payment</p>
                <Loader class="mb-8"></Loader>
            </div>

            <template v-if="transaction">
                <div class="flex flex-col items-center justify-center space-y-4 mb-8">
                    <div :class="[{ 'animated-border-green' : isPaid }, { 'animated-border-red' : isFailedPayment }, { 'animated-border-cyan' : isPendingPayment }, 'w-96 bg-white py-4 px-4 shadow-sm rounded-xl']">
                        <div class="flex items-center space-x-2 mb-2">
                            <CircleCheck v-if="isPaid" size="28" class="text-green-600"></CircleCheck>
                            <Clock v-if="isPendingPayment" size="28" class="text-yellow-600"></Clock>
                            <CircleAlert v-if="isFailedPayment" size="28" class="text-red-600"></CircleAlert>
                            <h1 :class="[{ 'text-green-600' : isPaid }, { 'text-red-600' : isFailedPayment }, { 'text-yellow-600' : isPendingPayment }, 'capitalize text-xl font-bold']">
                                {{ transaction.payment_status }}
                            </h1>
                        </div>

                        <h2 :class="[{ 'text-green-600' : isPaid }, { 'text-red-600' : isFailedPayment }, { 'text-yellow-600' : isPendingPayment }, 'text-sm mb-4']">
                            {{ transaction.description }}
                        </h2>

                        <h2 :class="[{ 'text-green-600' : isPaid }, { 'text-red-600' : isFailedPayment }, { 'text-yellow-600' : isPendingPayment }, 'text-3xl text-gray-700 font-bold space-x-1 mb-4']">
                            <span>{{ transaction.amount.amount_with_currency }}</span>
                        </h2>

                        <div v-if="isFailedPayment" class="text-red-600 p-4 bg-red-50 border border-red-300 rounded-lg mb-4">
                            <p class="text-sm font-semibold mb-2">{{ transaction.failure_type ?? 'Failure Reason' }}</p>
                            <p v-if="transaction.failure_reason" class="text-xs">{{ transaction.failure_reason }}</p>
                        </div>

                        <div class="bg-gray-50 border p-4 rounded-lg mb-4">
                            <span class="text-gray-600"><span class="font-bold">{{ domain.name }}</span></span>
                        </div>

                        <template v-if="!isVerifyingPayment">
                            <Button
                                size="sm"
                                type="primary"
                                :action="makePayment"
                                :leftIcon="CreditCard"
                                v-if="isPendingPayment"
                                buttonClass="w-full mb-4"
                                :loading="isRenewTransactionPaymentLink">
                                <span>Make Payment</span>
                            </Button>

                            <Button
                                size="sm"
                                type="primary"
                                :action="makePayment"
                                v-if="isFailedPayment"
                                :leftIcon="RefreshCcw"
                                buttonClass="w-full mb-4"
                                :loading="isRenewTransactionPaymentLink">
                                <span>Retry Payment</span>
                            </Button>

                            <template v-if="isPaid">
                                <div class="flex items-center space-x-2 text-xs mb-2">
                                    <span class="text-gray-400">{{ formattedDatetime(transaction.created_at) }}</span>
                                    <Popover content="This is the transaction date" placement="top"></Popover>
                                </div>
                                <div class="flex items-center space-x-2 text-xs">
                                    <span class="text-gray-400">{{ transaction.id }}</span>
                                    <Popover content="This is the transaction ID" placement="top"></Popover>
                                </div>
                            </template>
                        </template>
                    </div>

                    <Button
                        size="md"
                        v-if="store"
                        buttonClass="w-96"
                        rightIconSize="28"
                        :rightIcon="MoveRight"
                        :action="navigateToDomains"
                        :type="isPaid ? 'primary' : 'light'">
                        <span class="mr-2">View Domains</span>
                    </Button>
                </div>
            </template>
        </div>
        <img :src="'/images/clouds.png'" class="absolute top-32">
    </div>
</template>

<script>
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';
    import Popover from '@Partials/Popover.vue';
    import { isPastDate, formattedDatetime } from '@Utils/dateUtils.js';
    import { Clock, CreditCard, CircleAlert, CircleCheck, RefreshCcw, MoveRight } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: { Button, Loader, Popover, Clock, CreditCard, CircleAlert, CircleCheck },
        data() {
            return {
                MoveRight,
                CreditCard,
                RefreshCcw,
                isPastDate,
                formattedDatetime,
                transaction: null,
                isVerifyingPayment: false,
                isLoadingTransaction: false,
                isRenewTransactionPaymentLink: false,
            };
        },
        watch: {
            store: {
                handler: async function (newValue) {
                    if (newValue) {
                        await this.showTransaction();
                        this.verifyDomainPayment();
                    }
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            domain() {
                return this.transaction?.owner;
            },
            isPaid() {
                return this.transaction?.payment_status === 'paid';
            },
            isFailedPayment() {
                return this.transaction?.payment_status === 'failed payment';
            },
            isPendingPayment() {
                return this.transaction?.payment_status === 'pending payment';
            },
            dpoPaymentUrl() {
                return this.transaction?.metadata?.dpo_payment_url;
            },
            dpoPaymentLinkHasExpired() {
                return isPastDate(this.transaction?.metadata?.dpo_payment_url_expires_at);
            }
        },
        methods: {
            navigateToDomains() {
                this.$router.push({
                    name: 'show-domains',
                    query: { store_id: this.store.id }
                });
            },
            async showTransaction() {
                try {
                    this.isLoadingTransaction = true;
                    const transactionId = this.$route.query.transaction_id;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['owner'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/transactions/${transactionId}`, config);
                    this.transaction = response.data;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching transaction';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch transaction:', error);
                } finally {
                    this.isLoadingTransaction = false;
                }
            },
            makePayment() {
                if (this.isRenewTransactionPaymentLink) return;
                if (this.dpoPaymentLinkHasExpired) {
                    this.renewTransactionPaymentLink();
                } else {
                    this.isRenewTransactionPaymentLink = true;
                    window.location.assign(this.dpoPaymentUrl);
                }
            },
            async renewTransactionPaymentLink() {
                try {
                    this.isRenewTransactionPaymentLink = true;

                    let data = {
                        store_id: this.store.id
                    };

                    const response = await axios.post(`/api/transactions/${this.transaction.id}/renew`, data);
                    const transaction = response.data;
                    window.location.href = transaction.metadata.dpo_payment_url;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while renewing payment link';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to renew payment link:', error);
                } finally {
                    this.isRenewTransactionPaymentLink = false;
                }
            },
            async verifyDomainPayment() {
                try {
                    this.isVerifyingPayment = true;
                    const transactionId = this.$route.query.transaction_id;

                    let data = {
                        store_id: this.store.id
                    };

                    const response = await axios.post(`/api/domains/verify-payment/${transactionId}`, data);
                    this.notificationState.showSuccessNotification('Domain payment verified!');
                    this.transaction = response.data;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while verifying domain payment';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to verify domain payment:', error);
                } finally {
                    this.isVerifyingPayment = false;
                }
            }
        }
    };
</script>
