<template>

    <div class="rounded-lg overflow-hidden border border-gray-300">

        <div class="space-y-2 p-2 px-4 bg-gray-50 hover:bg-gray-100">

            <div class="flex justify-between items-center space-x-8">

                <div class="flex items-center space-x-2">
                    <img
                        alt="Payment Method Logo"
                        class="w-8 h-8 rounded-full"
                        v-if="storePaymentMethod || paymentMethod"
                        :src="storePaymentMethod?.logo?.path ?? paymentMethod.image_url"
                    />
                    <Banknote v-else size="20" class="text-gray-400 shrink-0"></Banknote>
                    <div>
                        <p v-if="storePaymentMethod || paymentMethod" class="font-semibold text-sm">{{ storePaymentMethod?.custom_name ?? paymentMethod.name }}</p>
                        <p v-if="transaction.description" class="text-sm">{{ transaction.description }}</p>
                    </div>
                </div>

                <div class="flex items-center space-x-2 whitespace-nowrap">

                    <span class="text-gray-500 text-xs mr-4">{{ formattedDatetime(transaction.created_at) }}</span>
                    <Pill :type="transaction.payment_status == 'paid' ? 'success' : 'warning'" size="xs" :showDot="false">{{ transaction.payment_status }}</Pill>
                    <span class="text-sm">{{ transaction.amount.amount_with_currency }}</span>

                    <div :class="['flex items-center justify-center w-10 h-10 hover:bg-white rounded-lg cursor-pointer', { 'border border-dashed border-gray-200' : !hasPhoto }]">

                        <img v-if="hasPhoto" class="w-full max-h-10 object-contain rounded-lg shrink-0" :src="photo.path">

                        <Image v-else size="16" class="text-gray-400 shrink-0"></Image>

                    </div>

                    <!-- Delete Transaction -->
                    <Button
                        size="xs"
                        leftIconSize="14"
                        :leftIcon="Trash2"
                        type="bareDanger"
                        v-if="!isWaitingConfirmation"
                        :action="() => onDeleteTransaction(transaction)">
                    </Button>

                </div>

            </div>

            <div
                v-if="isWaitingConfirmation"
                class="flex justify-end space-x-1">

                <Button
                    size="sm"
                    type="danger"
                    leftIconSize="14"
                    :action="() => onDeleteTransaction(transaction, true)"
                    :isLoading="isDeletingTransactionId == transaction.id">
                    <span>Reject</span>
                </Button>

                <Button
                    size="sm"
                    type="success"
                    leftIconSize="14"
                    :action="() => approveTransaction(transaction)"
                    :isLoading="isApprovingTransactionId == transaction.id">
                    <span>Approve</span>
                </Button>

            </div>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import Button from '@Partials/Button.vue';
    import { formattedDatetime } from '@Utils/dateUtils.js';
    import { Image, Trash2, Banknote } from 'lucide-vue-next';

    export default {
        components: { Image, Pill, Button, Banknote },
        props: {
            index: {
                type: Number
            },
            transaction: {
                type: Object
            },
            approveTransaction: {
                type: Function
            },
            onDeleteTransaction: {
                type: Function
            },
            isApprovingTransactionId: {
                type: [null, String]
            },
            isDeletingTransactionId: {
                type: [null, String]
            },
        },
        data() {
            return {
                Trash2,
                expanded: false,
                isDeletingTransaction: false
            }
        },
        computed: {
            hasPhoto() {
                return this.photo != null;
            },
            photo() {
                return this.transaction.photo;
            },
            paymentMethod() {
                return this.transaction.payment_method;
            },
            storePaymentMethod() {
                return this.transaction.store_payment_method;
            },
            isWaitingConfirmation() {
                return this.transaction.payment_status == 'waiting confirmation';
            }
        },
        methods: {
            formattedDatetime: formattedDatetime
        }
    };

</script>
