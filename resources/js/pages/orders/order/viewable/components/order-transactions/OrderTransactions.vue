<template>

    <div class="bg-white rounded-lg space-y-4 p-4 mb-4">

        <div class="flex justify-between items-center mb-4">

            <div class="flex items-center space-x-2">

                <h1 class="text-gray-700 font-semibold">Payments</h1>

                <Button
                    size="xs"
                    type="bare"
                    :leftIcon="ExternalLink">
                </Button>

            </div>

            <div class="flex justify-between items-center space-x-4">

                <Button
                    size="xs"
                    type="bare"
                    :leftIcon="Link2"
                    :action="copyPaymentLink">
                    <span>Copy Payment Link</span>
                </Button>

                <template v-if="!isLoadingStore && !isLoadingOrder">

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
                        :approveAction="createTransaction"
                        :approveLoading="isCreatingTransaction">

                        <template #content>

                            <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Add Payment</p>

                            <div class="space-y-4 mb-8">

                                <!-- Payment Name Input -->
                                <Input
                                    type="text"
                                    label="Payment Name"
                                    v-model="form.description"
                                    placeholder="Bank Transfer"
                                    :errorText="formState.getFormError('description')">
                                </Input>

                                <!-- Payment Amount Input -->
                                <Input
                                    type="money"
                                    v-model="form.amount"
                                    label="Payment Amount"
                                    :currency="store.currency.code"
                                    :errorText="formState.getFormError('amount')">
                                </Input>

                                <!-- Proof Of Payment -->
                                <Input
                                    type="file"
                                    :maxFiles="1"
                                    v-model="form.photo"
                                    label="Proof Of Payment"
                                    :imagePreviewGridCols="1"
                                    singleFileUploadMessage="proof of payment attached">
                                </Input>

                            </div>

                        </template>

                    </Modal>

                </template>

                <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-full" height="h-8" rounded="rounded-md" :shine="true"></Skeleton>

                <OrderPaymentStatusSelect
                    v-else
                    class="w-48"
                    :showLabel="false"
                    @change="(paymentStatus) => updateOrder({ payment_status: paymentStatus })">
                </OrderPaymentStatusSelect>

            </div>

        </div>

        <!-- Order Products (Loading Placeholder) -->
        <div v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="space-y-2">

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

                            <Image size="16" class="text-gray-400 flex-shrink-0"></Image>

                        </div>
                    </div>

                </div>

            </div>

        </div>

        <template v-else>

            <template v-if="hasTransactions">

                <OrderTransaction
                    :key="index"
                    :index="index"
                    :transaction="transaction"
                    :onDeleteTransaction="onDeleteTransaction"
                    v-for="(transaction, index) in transactions">
                </OrderTransaction>

                <div class="bg-blue-50 border border-blue-400 rounded-lg p-4 space-y-4">

                    <div class="flex justify-between text-sm font-bold">
                        <span>Amount Paid</span>
                        <span>{{ order.paid_total.amount_with_currency }}</span>
                    </div>

                    <div
                        v-if="order.outstanding_total.amount > 0"
                        class="flex justify-between text-sm font-bold">
                        <span>Amount Due</span>
                        <span class="text-red-500">{{ order.outstanding_total.amount_with_currency }}</span>
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
                approveText="Delete"
                approveType="danger"
                ref="deletePaymentModal"
                :scrollOnContent="false"
                :leftApproveIcon="Trash2"
                :approveAction="deleteTransaction"
                :approveLoading="deletableTransaction && isDeletingTransactionId == deletableTransaction.id">

                <template
                    #content
                    v-if="deletableTransaction">

                    <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Confirm Delete</p>
                    <p class="mb-8">Are you sure you want to permanently delete this payment?</p>

                    <div class="p-4 text-sm bg-gray-100 rounded-lg">
                        <div class="space-x-2">
                            <span class="font-bold">Name:</span>
                            <span>{{ deletableTransaction.description }}</span>
                        </div>
                        <div class="space-x-2">
                            <span class="font-bold">Amount:</span>
                            <span>{{ deletableTransaction.amount.amount_with_currency }}</span>
                        </div>
                    </div>

                </template>

            </Modal>

        </template>

    </div>

</template>

<script>

    import { Image } from 'lucide-vue-next';
    import Alert from '@Partials/Alert.vue';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import { isEmpty } from '@Utils/stringUtils';
    import Skeleton from '@Partials/Skeleton.vue';
    import { Plus, Link2, Trash2, ExternalLink, CircleDollarSign } from 'lucide-vue-next';
    import OrderTransaction from '@Pages/orders/order/viewable/components/order-transactions/OrderTransaction.vue';
    import OrderPaymentStatusSelect from '@Pages/orders/order/editable/components/order-basics/components/OrderPaymentStatusSelect.vue';

    export default {
        inject: ['formState', 'storeState', 'orderState', 'notificationState'],
        components: { Image, Alert, Input, Modal, Button, Skeleton, OrderTransaction, OrderPaymentStatusSelect },
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
                    description: ''
                },
                pagination: null,
                transactions: [],
                isLoadingTransactions: false,
                deletableTransaction: null,
                isCreatingTransaction: false,
                isDeletingTransactionId: null
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.showTransactions();
                }
            },
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
            }
        },
        methods: {
            isEmpty,
            reset() {
                this.form.photo = [];
                this.form.amount = '0.00';
                this.form.description = '';
            },
            async copyPaymentLink() {
                try {
                    await navigator.clipboard.writeText('the payment link');
                    this.notificationState.showSuccessNotification('Payment link copied!');

                } catch (err) {
                    console.error('Failed to copy:', err);
                    this.notificationState.showWarningNotification('Payment link failed to copy');
                }
            },
            async showTransactions() {
                try {

                    this.isLoadingTransactions = true;

                    let config = {
                        params: {
                            order_id: this.orderId,
                            store_id: this.store.id,
                            _relationships: ['photo'].join(',')
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

                    if(this.isEmpty(this.form.description)) {
                        this.formState.setFormError('description', 'Enter payment name');
                    }

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
                    formData.append('description', this.form.description);

                    // Attach transaction photo if available
                    if (this.form.photo.length) {
                        formData.append('photo', this.form.photo[0].file_ref);
                    }

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    const response = await axios.post('/api/transactions', formData, config);

                    this.notificationState.showSuccessNotification('Payment created!');
                    this.$refs.addPaymentModal.hideModal();
                    this.showTransactions();
                    this.reset();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating payment';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create payment:', error);
                } finally {
                    this.isCreatingTransaction = false;
                }
            },
            onDeleteTransaction(transaction) {
                this.deletableTransaction = transaction;
                this.$refs.deletePaymentModal.showModal();
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
        }
    };

</script>
