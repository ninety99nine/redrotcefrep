<template>

    <div class="flex justify-center">

        <Modal
            ref="modal"
            :onShow="onShow"
            triggerSize="sm"
            contentClass="px-4"
            triggerType="light"
            :scrollOnContent="false"
            :showApproveButton="false"
            :leftTriggerIcon="hasCustomer ? RefreshCcw : Plus"
            :header="`${hasCustomer ? 'Change' : 'Add'} Customer`"
            :triggerText="`${hasCustomer ? 'Change' : 'Add'} Customer`"
            :triggerLoading="isLoadingOrder || (isEditting && !hasOrder)">

            <template #content>

                <div v-if="hasLoadedInitialCustomers" class="flex justify-center items-center space-x-4 my-2">

                    <Input
                        type="search"
                        class="w-full"
                        :debounced="true"
                        v-model="searchTerm"
                        placeholder="Search customers"
                        @input="isLoadingCustomers = true"
                        :skeleton="isLoadingOrder || (isEditting && !hasOrder)">
                    </Input>

                    <Button
                        size="xs"
                        type="light"
                        :leftIcon="Plus"
                        :action="addNew"
                        buttonClass="my-2">
                        <span>Add New</span>
                    </Button>

                </div>

                <template v-if="isLoadingCustomers">

                    <div
                        class="space-y-2 mb-4">

                        <div
                            :key="index"
                            v-for="(_, index) in [1, 2, 3]"
                            class="flex items-center space-x-2 border-b border-gray-300 shadow-sm rounded-lg p-2 bg-gray-50 w-full">

                            <!-- Skeleton Loading -->
                            <Skeleton width="w-10" height="h-10" rounded="rounded-lg" :shine="true" class="flex-shrink-0"></Skeleton>

                            <div class="w-full space-y-2">
                                <Skeleton width="w-2/3" :shine="true"></Skeleton>
                                <Skeleton width="w-1/3" :shine="true"></Skeleton>
                            </div>

                        </div>

                    </div>

                </template>

                <CustomerOptions
                    :customers="customers"
                    v-else-if="hasCustomers"
                    :onSelectCustomer=onSelectCustomer>
                </CustomerOptions>

                <p v-else class="text-sm text-center p-4 rounded-lg bg-gray-50 mb-4">
                    No customers found
                </p>

            </template>

        </Modal>

    </div>

</template>

<script>

    import axios from 'axios';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { Plus, RefreshCcw } from 'lucide-vue-next';
    import CustomerOptions from '@Pages/orders/order/editable/components/order-customer/add-customer/customer-options/CustomerOptions.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Input, Modal, Button, Skeleton, CustomerOptions },
        props: {
            hasCustomer: {
                type: Boolean
            }
        },
        data() {
            return {
                Plus,
                RefreshCcw,
                customers: [],
                searchTerm: null,
                lastSearchTerm: null,
                isLoadingCustomers: false,
                hasLoadedInitialCustomers: false
            }
        },
        watch: {
            searchTerm() {
                this.showCustomers();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isEditting() {
                return this.$route.name === 'edit-order';
            },
            hasSearchTerm() {
                return this.searchTerm != null && this.searchTerm.trim() != '';
            },
            hasCustomers() {
                return this.customers.length > 0;
            },
        },
        methods: {
            onShow() {
                this.hasLoadedInitialCustomers = false;
                this.lastSearchTerm = null;
                this.searchTerm = null;
                this.customers = [];
                this.showCustomers();
            },
            async showCustomers() {
                try {

                    this.isLoadingCustomers = true;

                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };

                    if (this.hasSearchTerm) {
                        config.params['search'] = this.searchTerm;
                    }

                    this.lastSearchTerm = this.searchTerm;

                    const response = await axios.get('/api/customers', config);

                    if (this.searchTerm === this.lastSearchTerm) {
                        const pagination = response.data;
                        this.customers = pagination.data;
                        this.isLoadingCustomers = false;
                        this.hasLoadedInitialCustomers = true;
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching customers';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch customers:', error);
                } finally {
                    this.isLoadingCustomers = false;
                }
            },
            onSelectCustomer(customer) {
                this.$refs.modal.hideModal();
                this.orderState.addCartCustomer(customer);
            },
            addNew() {
                this.$refs.modal.hideModal();
                this.orderState.addCartCustomer();
            },
        }
    };

</script>
