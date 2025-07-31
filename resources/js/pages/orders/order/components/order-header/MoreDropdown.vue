<template>

    <div>

        <Dropdown
            position="left"
            :options="options"
            dropdownClasses="w-40">

            <template #content="props">

                <ul class="max-h-60 overflow-auto">

                    <li
                        :key="index"
                        v-for="(option, index) in props.options"
                        @click="() => props.handleItemClick(option)"
                        :class="[
                            'flex items-center space-x-2 px-4 py-2 text-sm cursor-pointer',
                            option.label == 'Delete' ? 'hover:bg-red-100 text-red-700' : 'hover:bg-gray-100 text-gray-700'
                        ]">
                        <span class="truncate">{{ option.label }}</span>
                    </li>

                </ul>

            </template>

        </Dropdown>


        <!-- Confirm Delete Order -->
        <Modal
            approveType="danger"
            ref="deleteOrderModal"
            :leftApproveIcon="Trash2"
            approveText="Delete Order"
            :approveAction="deleteOrder"
            :approveLoading="isDeletingOrder"
            v-if="!isLoadingStore && !isLoadingOrder && hasOrder">

            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Confirm Delete</p>
                <p class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">Order #{{ order.number }}</span>?</p>
            </template>

        </Modal>

    </div>

</template>

<script>

    import axios from 'axios';
    import Modal from '@Partials/Modal.vue';
    import { Trash2 } from 'lucide-vue-next';
    import Dropdown from '@Partials/Dropdown.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Modal, Dropdown },
        data() {
            return {
                Trash2,
                isDeletingOrder: false,
                options: [
                    {
                        label: 'Duplicate',
                        action: this.duplicateOrder,
                    },
                    {
                        label: 'Delete',
                        action: this.confirmDeleteOrder
                    }
                ],
            }
        },
        computed: {
            order() {
                return this.orderState.order;
            },
            store() {
                return this.storeState.store;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
        },
        methods: {
            confirmDeleteOrder() {
                this.$refs.deleteOrderModal.showModal();
            },
            duplicateOrder() {
                this.$router.push({
                    name: 'create-order',
                    query: {
                        store_id: this.store.id,
                        duplicate_order_id: this.order.id
                    }
                });
            },
            async deleteOrder() {

                try {

                    if(this.isDeletingOrder) return;

                    this.isDeletingOrder = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/orders/${this.order.id}`, config);

                    this.$refs.deleteOrderModal.hideModal();
                    await new Promise(resolve => setTimeout(resolve, 1000));    //  Wait for modal to close

                    this.notificationState.showSuccessNotification('Order deleted');

                    await this.$router.replace({
                        name: 'show-orders',
                        query: {
                            store_id: this.store.id,
                            searchTerm: this.$route.query.searchTerm,
                            filterExpressions: this.$route.query.filterExpressions,
                            sortingExpressions: this.$route.query.sortingExpressions
                        }
                    });

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting order';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete order:', error);
                } finally {
                    this.isDeletingOrder = false;
                    this.$refs.deleteOrderModal.hideModal();
                }

            },
        }
    };

</script>
