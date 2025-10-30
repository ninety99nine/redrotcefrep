<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center space-y-4 py-12 px-4 sm:px-6 lg:px-8">

        <!-- Redirecting -->
        <transition name="fade-1">
            <p v-if="isPaid" class="w-full max-w-md p-2 bg-white rounded-lg  text-sm text-gray-600 text-center">
                Redirecting to show order in {{ countdown }} second{{ countdown !== 1 ? 's' : '' }}...
            </p>
        </transition>

        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8 transition-all duration-300 ease-in-out transform hover:shadow-xl">
            <!-- Loading State -->
            <div v-if="isLoadingTransaction || isVerifyingPayment" class="flex flex-col items-center justify-center space-y-4">
                <h1 class="flex items-center space-x-3 text-2xl font-bold text-gray-800">
                    <CreditCard size="28" class="text-indigo-600" />
                    <span>Verifying Order Payment</span>
                </h1>
                <p class="text-sm text-gray-500 text-center max-w-xs">Please wait while we verify your payment.</p>
                <Loader class="w-12 h-12 text-indigo-600 animate-pulse" />
            </div>

            <!-- Transaction Details -->
            <template v-if="!isLoadingTransaction && !isVerifyingPayment && transaction">
                <div class="space-y-6">
                    <!-- Status Header -->
                    <div class="flex items-center space-x-3">
                        <CircleCheck v-if="isPaid" size="32" class="text-green-500" />
                        <Clock v-if="isPendingPayment" size="32" class="text-yellow-500" />
                        <CircleAlert v-if="isFailedPayment" size="32" class="text-red-500" />
                        <h1 :class="[
                            'text-2xl font-bold capitalize',
                            isPaid ? 'text-green-600' : isFailedPayment ? 'text-red-600' : 'text-yellow-600'
                        ]">
                            {{ transaction.payment_status }}
                        </h1>
                    </div>

                    <!-- Description -->
                    <p :class="[
                        'text-base',
                        isPaid ? 'text-green-600' : isFailedPayment ? 'text-red-600' : 'text-yellow-600'
                    ]">
                        {{ transaction.description }}
                    </p>

                    <!-- Amount -->
                    <h2 class="text-3xl font-bold text-gray-800">
                        {{ transaction.amount.amount_with_currency }}
                    </h2>

                    <!-- Failure Details -->
                    <div v-if="isFailedPayment" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm font-semibold text-red-600">{{ transaction.failure_type ?? 'Failure Reason' }}</p>
                        <p v-if="transaction.failure_reason" class="text-xs text-red-500">{{ transaction.failure_reason }}</p>
                    </div>

                    <!-- Order Summary -->
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <span class="text-gray-600 text-sm">
                            <span class="font-semibold">{{ order.summary }}</span>
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div v-if="!isVerifyingPayment && (isPendingPayment || isFailedPayment)" class="space-y-4">
                        <Button
                            size="lg"
                            type="primary"
                            buttonClass="w-full"
                            :action="makePayment"
                            :leftIcon="CreditCard"
                            v-if="isPendingPayment"
                            :loading="isRenewTransactionPaymentLink">
                            <span class="ml-2">Make Payment</span>
                        </Button>

                        <Button
                            size="lg"
                            type="primary"
                            buttonClass="w-full"
                            :action="makePayment"
                            :leftIcon="RefreshCcw"
                            v-if="isFailedPayment"
                            :loading="isRenewTransactionPaymentLink">
                            <span class="ml-2">Retry Payment</span>
                        </Button>
                    </div>

                    <!-- Paid Status with Countdown -->
                    <div v-if="isPaid" class="space-y-4">
                        <!-- Transaction Details -->
                        <div class="space-y-2 text-sm text-gray-500">
                            <div class="flex items-center space-x-2">
                                <span>{{ formattedDatetime(transaction.created_at) }}</span>
                                <Popover content="This is the transaction date" placement="top" />
                            </div>
                            <div class="flex items-center space-x-2">
                                <span>{{ transaction.id }}</span>
                                <Popover content="This is the transaction ID" placement="top" />
                            </div>
                        </div>
                    </div>

                    <!-- View Order Button -->
                    <Button
                        size="lg"
                        v-if="store"
                        buttonClass="w-full"
                        :rightIcon="MoveRight"
                        :action="navigateToShowShopOrder"
                        :type="isPaid ? 'primary' : 'light'">
                        <span class="mr-2">View Order</span>
                    </Button>
                </div>
            </template>
        </div>
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
        components: { Button, Loader, Popover, Clock, CreditCard, CircleAlert, CircleCheck, RefreshCcw, MoveRight },
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
                countdown: 5, // Countdown starts at 5 seconds
                countdownInterval: null, // Store interval ID
            };
        },
        watch: {
            store() {
                this.setup();
            },
            isPaid(newValue) {
                if (newValue && !this.isLoadingTransaction && !this.isVerifyingPayment) {
                    this.startCountdown();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
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
            async setup() {
                await this.showTransaction();
                if (this.transaction?.payment_status !== 'paid') {
                    this.verifyDomainPayment();
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

                    const response = await axios.post(`/api/orders/verify-payment/${transactionId}`, data);
                    this.notificationState.showSuccessNotification('Order payment verified!');
                    this.transaction = response.data;
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while verifying order payment';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to verify order payment:', error);
                } finally {
                    this.isVerifyingPayment = false;
                }
            },
            startCountdown() {
                // Clear any existing interval to prevent duplicates
                if (this.countdownInterval) {
                    clearInterval(this.countdownInterval);
                }
                // Set initial countdown value
                this.countdown = 5;
                // Start interval
                this.countdownInterval = setInterval(() => {
                    this.countdown -= 1;
                    if (this.countdown <= 0) {
                        clearInterval(this.countdownInterval);
                        this.navigateToShowShopOrder();
                    }
                }, 1000);
            }
        },
        created() {
            if (this.store) {
                this.setup();
            }
        },
        beforeDestroy() {
            // Clear interval to prevent memory leaks
            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
            }
        }
    };
</script>
