<template>

    <div class="flex justify-center">

        <CustomModal
            ref="modal"
            :onShow="onShow"
            triggerSize="sm"
            contentClass="px-4"
            triggerType="light"
            :scrollOnContent="false"
            :showApproveButton="false"
            :triggerIcon="hasCustomer ? 'refresh' : 'add'"
            :header="`${hasCustomer ? 'Change' : 'Add'} Customer`"
            :triggerText="`${hasCustomer ? 'Change' : 'Add'} Customer`"
            :triggerLoading="isLoadingOrder || (isEditting && !hasOrder)">

            <template #content>

                <div v-if="hasLoadedInitialCustomers" class="flex justify-center items-center space-x-4">

                    <CustomInput
                        type="search"
                        class="w-full"
                        :debounced="true"
                        v-model="searchTerm"
                        placeholder="Search customers"
                        @input="isLoadingCustomers = true"
                        :skeleton="isLoadingOrder || (isEditting && !hasOrder)">
                    </CustomInput>

                    <Button :action="addNew" type="light" size="xs" icon="add" buttonClass="my-2">
                        <span>Add New</span>
                    </Button>

                </div>

                <template v-if="isLoadingCustomers">

                    <div
                        class="space-y-2 mb-4">

                        <div
                            :key="index"
                            v-for="(_, index) in [1, 2, 3]"
                            class="flex items-center space-x-2 border-b shadow-sm rounded-lg p-2 bg-gray-50 w-full">

                            <!-- Skeleton Loading -->
                            <LineSkeleton width="w-10" height="h-10" rounded="rounded-lg" :shine="true" class="flex-shrink-0"></LineSkeleton>

                            <div class="w-full space-y-2">
                                <LineSkeleton width="w-2/3" :shine="true"></LineSkeleton>
                                <LineSkeleton width="w-1/3" :shine="true"></LineSkeleton>
                            </div>

                        </div>

                    </div>

                </template>

                <CustomerOptions
                    v-if="hasCustomers"
                    :customers="customers"
                    :onSelectCustomer=onSelectCustomer>
                </CustomerOptions>

                <p v-else class="text-sm text-center p-4 rounded-lg bg-gray-50 mb-4">
                    No customers found
                </p>

            </template>

        </CustomModal>

    </div>

</template>

<script>

    import axios from 'axios';
    import Button from '@Partials/buttons/Button.vue';
    import CustomInput from '@Partials/inputs/CustomInput.vue';
    import CustomModal from '@Partials/inputs/CustomModal.vue';
    import LineSkeleton from '@Partials/skeletons/LineSkeleton.vue';
    import CustomerOptions from '@Pages/stores/store/orders/order/editable/components/customer/add-customer/customer-options/CustomerOptions.vue';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Button, CustomInput, CustomModal, LineSkeleton, CustomerOptions },
        props: {
            hasCustomer: {
                type: Boolean
            }
        },
        data() {
            return {
                customers: [],
                searchTerm: null,
                lastSearchTerm: null,
                isLoadingCustomers: false,
                hasLoadedInitialCustomers: false
            }
        },
        watch: {
            searchTerm() {
                this.getCustomers();
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
            isEditting() {
                return this.$route.name === 'edit-store-order';
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
                this.getCustomers();
            },
            async getCustomers() {

                this.isLoadingCustomers = true;

                const url = this.store._links.showStoreCustomers;

                let config = {
                    params: {
                        'store_id': this.store.id
                    }
                };

                if (this.hasSearchTerm) {
                    config.params['search'] = this.searchTerm;
                }

                this.lastSearchTerm = this.searchTerm;

                try {
                    const response = await axios.get(url, config);

                    if (response.status === 200) {

                        if (this.searchTerm === this.lastSearchTerm) {
                            const pagination = response.data;
                            this.customers = pagination.data;
                        }

                    } else {
                        this.formState.setFormError('general', response.data.message);
                        this.notificationState.showWarningNotification(response.data.message);
                    }
                } catch (errorException) {
                    this.formState.setServerFormErrors(errorException);
                }

                if (this.searchTerm === this.lastSearchTerm) {
                    this.isLoadingCustomers = false;
                    this.hasLoadedInitialCustomers = true;
                }
            },
            onSelectCustomer(customer) {
                this.$refs.modal.hideModal();
                this.orderState.addCartCustomer(customer);
            },
            addNew() {
                this.$refs.modal.hideModal();
                this.orderState.addCartPromotion();
            },
        }
    };

</script>
