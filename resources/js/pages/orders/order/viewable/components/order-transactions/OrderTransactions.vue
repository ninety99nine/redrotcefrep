<template>

    <div class="bg-white rounded-lg space-y-4 p-4 mb-4">

        <div class="flex justify-between items-center mb-4">

            <Button
                size="xs"
                type="bare"
                :rightIcon="ExternalLink"
                :action="navigateToShopPaymentMethods"
                v-if="!isLoadingStore && !isLoadingOrder && order.outstanding_total.amount > 0">
                <span class="text-base text-gray-700 font-semibold mr-2">Payments</span>
            </Button>
            <span v-else class="text-base text-gray-700 font-semibold mr-2">Payments</span>

            <div class="flex justify-between items-center space-x-4">

                <template v-if="!isLoadingStore && !isLoadingOrder && order.outstanding_total.amount > 0">

                    <Button
                        size="xs"
                        type="bare"
                        :leftIcon="Link2"
                        :action="copyPaymentLink">
                        <span>Copy Payment Link</span>
                    </Button>

                    <!-- Create Transaction -->
                    <Modal
                        triggerSize="xs"
                        triggerType="light"
                        approveType="primary"
                        ref="addPaymentModal"
                        :leftApproveIcon="Plus"
                        :leftTriggerIcon="Plus"
                        :scrollOnContent="false"
                        leftTriggerIconSize="14"
                        triggerText="Add Payment"
                        approveText="Add Payment"
                        :onShow="onShowAddPaymentModal"
                        :approveAction="createTransaction"
                        :approveLoading="isCreatingTransaction">

                        <template #content>

                            <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Add Payment</p>

                            <div class="space-y-4 mb-8">

                                <!-- Payment Amount Input -->
                                <Input
                                    type="money"
                                    label="Amount"
                                    v-model="form.amount"
                                    :currency="store.currency.code"
                                    :errorText="formState.getFormError('amount')">
                                </Input>

                                <!-- Payment Method Input -->
                                <Select
                                    :search="true"
                                    label="Method"
                                    :options="paymentMethodOptions"
                                    v-model="form.payment_method_id"
                                    :errorText="formState.getFormError('payment_method_id')">

                                    <template #selectedOption="props">

                                        <div class="flex items-center space-x-2">

                                            <img
                                                alt="Payment Method Logo"
                                                v-if="props.selectedOption"
                                                class="w-6 h-6 rounded-full"
                                                :src="props.selectedOption.image_url"
                                            />

                                            <span class="text-gray-700 text-sm truncate">{{ props.selectedOption?.label ?? 'Select payment method' }}</span>

                                        </div>

                                    </template>

                                    <template #option="props">

                                        <div class="flex items-center space-x-2">

                                            <img
                                                alt="Payment Method Logo"
                                                class="w-6 h-6 rounded-full"
                                                :src="props.option.image_url"
                                            />

                                            <span class="truncate">{{ props.option.label }}</span>

                                        </div>

                                    </template>

                                </Select>

                                <!-- Payment Description Input -->
                                <Input
                                    type="text"
                                    label="Description"
                                    secondaryLabel="(Optional)"
                                    v-model="form.description"
                                    placeholder="Deposit payment"
                                    :errorText="formState.getFormError('description')">
                                </Input>

                                <!-- Proof Of Payment -->
                                <Input
                                    type="file"
                                    :maxFiles="1"
                                    v-model="form.photo"
                                    label="Proof Of Payment"
                                    :imagePreviewGridCols="1"
                                    secondaryLabel="(Optional)"
                                    singleFileUploadMessage="proof of payment attached">
                                </Input>

                            </div>

                        </template>

                    </Modal>

                </template>

                <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-full" height="h-8" rounded="rounded-md" :shine="true"></Skeleton>

                <OrderPaymentStatusSelect
                    v-else
                    class="w-52"
                    :showLabel="false"
                    @change="(paymentStatus) => updateOrder({ payment_status: paymentStatus })">
                </OrderPaymentStatusSelect>

            </div>

        </div>

        <div class="space-y-3">

            <!-- Order Transactions (Loading Placeholder) -->
            <template v-if="isLoadingStore || isLoadingOrder || !hasOrder">

                <div
                    :key="index"
                    v-for="(_, index) in [1, 2]" class="rounded-lg overflow-hidden border border-gray-300">

                    <div class="space-y-2 p-2 px-4 bg-gray-50">

                        <div class="flex justify-between items-center space-x-8">

                            <Skeleton width="w-1/3" :shine="true"></Skeleton>

                            <div class="flex items-center space-x-2 whitespace-nowrap">

                                <Skeleton width="w-20" :shine="true"></Skeleton>
                                <Skeleton width="w-10" :shine="true"></Skeleton>
                                <Skeleton width="w-10" :shine="true"></Skeleton>

                            </div>

                        </div>

                        <div class="flex justify-end items-end space-x-2">

                            <Button
                                size="xs"
                                :skeleton="true"
                                leftIconSize="14"
                                :leftIcon="Trash2"
                                type="outlineDanger">
                            </Button>

                            <div class="flex items-center justify-center w-10 h-10 rounded-lg border border-dashed border-gray-200">

                                <Image size="16" class="text-gray-400 shrink-0"></Image>

                            </div>
                        </div>

                    </div>

                </div>

            </template>

            <template v-else>

                <template v-if="hasTransactions">

                    <OrderTransaction
                        :key="index"
                        :index="index"
                        :transaction="transaction"
                        :approveTransaction="approveTransaction"
                        :onDeleteTransaction="onDeleteTransaction"
                        v-for="(transaction, index) in transactions"
                        :isApprovingTransactionId="isApprovingTransactionId"
                        :isDeletingTransactionId="isDeletingTransactionId">
                    </OrderTransaction>

                    <div class="bg-blue-50 border border-blue-400 rounded-lg p-4 space-y-4">

                        <div
                            class="flex justify-between text-sm">
                            <span :class="{ 'font-bold text-red-500' : order.outstanding_total.amount > 0 }">Amount Due</span>
                            <span :class="{ 'font-bold text-red-500' : order.outstanding_total.amount > 0 }">{{ order.outstanding_total.amount_with_currency }}</span>
                        </div>

                        <div class="flex justify-between text-sm font-bold">
                            <span>Amount Paid</span>
                            <span>{{ order.paid_total.amount_with_currency }}</span>
                        </div>

                    </div>

                </template>

                <Alert
                    v-else
                    type="light"
                    :dismissable="false"
                    :icon="CircleDollarSign"
                    title="No transctions yet" />

                <!-- Delete Transaction -->
                <Modal
                    approveType="danger"
                    ref="deletePaymentModal"
                    :scrollOnContent="false"
                    :approveAction="deleteTransaction"
                    :leftApproveIcon="isRejecting ? null : Trash2"
                    :approveText="isRejecting ? 'Reject' : 'Delete'"
                    :approveLoading="deletableTransaction && isDeletingTransactionId == deletableTransaction.id">

                    <template
                        #content
                        v-if="deletableTransaction">

                        <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Confirm Delete</p>
                        <p class="mb-8">Are you sure you want to permanently {{ isRejecting ? 'reject' : 'delete' }} this payment?</p>

                        <div class="flex items-center space-x-4 p-4 text-sm bg-gray-100 rounded-lg">

                            <img
                                alt="Payment Method Logo"
                                class="w-8 h-8 rounded-full"
                                v-if="deletableTransaction.store_payment_method || deletableTransaction.payment_method"
                                :src="deletableTransaction.store_payment_method?.logo?.path ?? deletableTransaction.payment_method.image_url"
                            />
                            <Banknote v-else size="20" class="text-gray-400 shrink-0"></Banknote>

                            <div class="w-full">
                                <div class="space-x-2">
                                    <span>Name:</span>
                                    <span class="font-bold">{{ deletableTransaction.store_payment_method?.custom_name ?? deletableTransaction.payment_method.name }}</span>
                                </div>
                                <div class="space-x-2">
                                    <span>Amount:</span>
                                    <span class="font-bold">{{ deletableTransaction.amount.amount_with_currency }}</span>
                                </div>
                            </div>

                        </div>
                    </template>

                </Modal>

            </template>

        </div>

    </div>

</template>

<script>

    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Select from '@Partials/Select.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { Image, Banknote } from 'lucide-vue-next';
    import { isEmpty, isNotEmpty } from '@Utils/stringUtils';
    import { Plus, Link2, Trash2, ExternalLink, CircleDollarSign } from 'lucide-vue-next';
    import OrderTransaction from '@Pages/orders/order/viewable/components/order-transactions/OrderTransaction.vue';
    import OrderPaymentStatusSelect from '@Pages/orders/order/editable/components/order-basics/components/OrderPaymentStatusSelect.vue';

    export default {
        inject: ['formState', 'storeState', 'orderState', 'notificationState'],
        components: { Image, Banknote, Alert, Input, Modal, Select, Button, Skeleton, OrderTransaction, OrderPaymentStatusSelect },
        data() {
            return {
                Plus,
                Link2,
                Trash2,
                ExternalLink,
                CircleDollarSign,
                form: {
                    photo: [],
                    amount: '0.00',
                    description: '',
                    payment_method_id: null
                },
                pagination: null,
                transactions: [],
                isRejecting: false,
                deletableTransaction: null,
                isLoadingTransactions: false,
                isCreatingTransaction: false,
                isApprovingTransactionId: null,
                isDeletingTransactionId: null,

                searchTerm: null,
                paymentMethods: [],
                lastSearchTerm: null,
                isLoadingPaymentMethods: false,
                hasLoadedInitialPaymentMethods: false,
            }
        },
        watch: {
            store() {
                this.setup();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            order() {
                return this.orderState.order;
            },
            orderId() {
                return this.$route.params.order_id;
            },
            orderForm() {
                return this.orderState.orderForm;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            hasTransactions() {
                return this.transactions.length > 0;
            },
            paymentMethodOptions() {
                return this.paymentMethods.map((paymentMethod) => {
                    return {
                        value: paymentMethod.id,
                        label: paymentMethod.name,
                        image_url: paymentMethod.image_url,
                    }
                });
            }
        },
        methods: {
            isEmpty,
            isNotEmpty,
            setup() {
                if(this.store) {
                    this.showTransactions();
                }
            },
            onShowAddPaymentModal() {
                this.form.photo = [];
                this.form.description = '';
                this.form.amount = this.order.outstanding_total.amount_without_currency;

                this.searchTerm = null;
                this.lastSearchTerm = null;

                if(!this.hasLoadedInitialPaymentMethods && this.paymentMethods.length == 0) {
                    this.showPaymentMethods();
                }
            },
            navigateToShopPaymentMethods() {

                const resolve = this.$router.resolve({
                    name: 'show-shop-payment-methods',
                    params: {
                        order_id: this.order.id,
                        alias: this.store.alias
                    }
                })

                window.open(resolve.href, '_blank', 'noopener,noreferrer');
            },
            async copyPaymentLink() {
                try {
                    await navigator.clipboard.writeText(
                        window.location.origin + this.$router.resolve({
                            name: 'show-shop-payment-methods',
                            params: {
                                order_id: this.order.id,
                                alias: this.store.alias
                            }
                        }).href
                    );
                    this.notificationState.showSuccessNotification('Payment link copied!');

                } catch (err) {
                    console.error('Failed to copy:', err);
                    this.notificationState.showWarningNotification('Payment link failed to copy');
                }
            },
            async showPaymentMethods() {
                try {

                    this.isLoadingPaymentMethods = true;

                    let config = {
                        params: {
                            per_page: 100,
                            store_id: this.store.id,
                            association: 'team member',
                        }
                    };

                    if (this.hasSearchTerm) {
                        config.params['search'] = this.searchTerm;
                    }

                    this.lastSearchTerm = this.searchTerm;

                    const response = await axios.get('/api/payment-methods', config);

                    if (this.searchTerm === this.lastSearchTerm) {
                        const pagination = response.data;
                        this.paymentMethods = pagination.data;
                        this.hasLoadedInitialPaymentMethods = true;

                        if(this.paymentMethods.length) {
                            this.form.payment_method_id = this.paymentMethods[0].id;
                        }
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching payment methods';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch payment methods:', error);
                } finally {
                    this.isLoadingPaymentMethods = false;
                }
            },
            async showTransactions() {
                try {

                    this.isLoadingTransactions = true;

                    let config = {
                        params: {
                            order_id: this.orderId,
                            store_id: this.store.id,
                            _relationships: ['photo', 'paymentMethod', 'storePaymentMethod.logo'].join(',')
                        }
                    };

                    const response = await axios.get('/api/transactions', config);

                    this.pagination = response.data;
                    this.transactions = this.pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching transactions';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch transactions:', error);
                } finally {
                    this.isLoadingTransactions = false;
                }
            },
            async createTransaction() {

                try {

                    if(this.isCreatingTransaction) return;

                    this.formState.hideFormErrors();

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.isCreatingTransaction = true;

                    let formData = new FormData();
                    formData.append('return', 1);
                    formData.append('owner_type', 'order');
                    formData.append('owner_id', this.orderId);
                    formData.append('store_id', this.store.id);
                    formData.append('amount', this.form.amount);
                    formData.append('currency', this.store.currency);
                    formData.append('payment_method_id', this.form.payment_method_id);

                    if(this.isNotEmpty(this.form.description)) {
                        formData.append('description', this.form.description);
                    }

                    // Attach transaction photo if available
                    if (this.form.photo.length) {
                        formData.append('photo', this.form.photo[0].file_ref);
                    }

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    await axios.post('/api/transactions', formData, config);

                    this.$refs.addPaymentModal.hideModal();

                    this.showTransactions();
                    await this.showOrder();

                    this.notificationState.showSuccessNotification('Payment created!');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating payment';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create payment:', error);
                } finally {
                    this.isCreatingTransaction = false;
                }
            },
            onDeleteTransaction(transaction, isRejecting = false) {
                this.isRejecting = isRejecting;
                this.deletableTransaction = transaction;
                this.$refs.deletePaymentModal.showModal();
            },
            async approveTransaction(transaction) {

                try {

                    this.isApprovingTransactionId = transaction.id;

                    const data = {
                        payment_status: 'paid',
                        store_id: this.store.id
                    }

                    await axios.put(`/api/transactions/${transaction.id}`, data);

                    this.showTransactions();
                    await this.showOrder();

                    this.notificationState.showSuccessNotification('Payment approved');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while approving payment';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to approve payment:', error);
                } finally {
                    this.isApprovingTransactionId = null;
                }

            },
            async deleteTransaction() {

                try {

                    this.isDeletingTransactionId = this.deletableTransaction.id;
                    const index = this.transactions.findIndex((transaction) => transaction.id == this.deletableTransaction.id);

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/transactions/${this.deletableTransaction.id}`, config);

                    await this.showOrder();
                    this.notificationState.showSuccessNotification('Payment deleted');
                    this.$refs.deletePaymentModal.hideModal();
                    this.transactions.splice(index, 1);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting transaction';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete transaction:', error);
                } finally {
                    this.isDeletingTransactionId = null;
                }

            },
            async showOrder() {
                try {

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    const response = await axios.get(`/api/orders/${this.order.id}`, config);

                    const order = response.data;
                    this.orderState.order.paid_total = order.paid_total;
                    this.orderState.order.payment_status = order.payment_status;
                    this.orderState.orderForm.payment_status = order.payment_status;
                    this.orderState.order.outstanding_total = order.outstanding_total;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch order:', error);
                }
            },
            async updateOrder(data) {

                try {

                    data = {
                        ...data,
                        store_id: this.store.id
                    };

                    await axios.put(`/api/orders/${this.orderId}`, data);

                    this.notificationState.showSuccessNotification(`Order ${this.orderForm.payment_status}`);
                    this.orderState.order.payment_status = this.orderForm.payment_status;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update order changes:', error);
                }

            }
        },
        created() {
            this.setup();
        }
    };

</script>
