<template>

    <div class="pt-24 px-4">

        <div class="grid grid-cols-12 gap-4 mb-4">

            <div class="col-span-8 col-start-3">

                <div class="select-none bg-white rounded-lg p-4 mb-4">

                    <div class="flex items-center space-x-4">

                        <!-- Back Button -->
                        <Button
                            size="xs"
                            type="light"
                            :action="goBack"
                            :leftIcon="MoveLeft">
                            <span>Back</span>
                        </Button>

                        <div v-if="isLoadingStore || isLoadingCustomer || (isEditing && !hasCustomer)" class="flex items-center space-x-2">
                            <Skeleton width="w-40"></Skeleton>
                            <Skeleton width="w-4"></Skeleton>
                        </div>

                        <template v-else>

                            <!-- Heading -->
                            <div class="flex items-center space-x-1">

                                <h1 class="text-lg text-gray-700 font-semibold">
                                    {{ isCreating ? 'Add Customer' : customerForm.name || '...' }}
                                </h1>

                                <Popover content="Customers are groups that help organize your products so customers can easily browse and find what they’re looking for." placement="top"></Popover>

                            </div>

                        </template>

                    </div>

                </div>

                <div class="relative mb-8">

                    <BackdropLoader v-if="isLoadingCustomer || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4 mb-4">

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                            <!-- First Name Input -->
                            <Input
                                type="text"
                                label="First Name"
                                placeholder="John"
                                v-model="customerForm.first_name"
                                :errorText="formState.getFormError('first_name')"
                                @input="customerState.saveStateDebounced('First name changed')">
                            </Input>

                            <!-- Last Name Input -->
                            <Input
                                type="text"
                                label="Last Name"
                                placeholder="John"
                                v-model="customerForm.last_name"
                                :errorText="formState.getFormError('last_name')"
                                @input="customerState.saveStateDebounced('Last name changed')">
                            </Input>

                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                            <!-- Email Input -->
                            <Input
                                type="email"
                                label="Email"
                                v-model="customerForm.email"
                                placeholder="johndoe@example.com"
                                :errorText="formState.getFormError('email')"
                                @input="customerState.saveStateDebounced('Email changed')">
                            </Input>

                            <!-- Mobile Number Input -->
                            <Input
                                type="text"
                                label="Mobile Number"
                                placeholder="+26772000001"
                                v-model="customerForm.mobile_number"
                                :errorText="formState.getFormError('mobile_number')"
                                @input="customerState.saveStateDebounced('Mobile number changed')">
                            </Input>

                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                            <!-- Birthday Input -->
                            <Input
                                type="date"
                                label="Birthday"
                                v-model="customerForm.birthday"
                                :errorText="formState.getFormError('birthday')"
                                @input="customerState.saveStateDebounced('Birthday changed')">
                            </Input>

                            <!-- Referral Code Input -->
                            <Input
                                type="text"
                                label="Referral Code"
                                v-model="customerForm.referral_code"
                                :errorText="formState.getFormError('referral_code')"
                                @input="customerState.saveStateDebounced('Referral code changed')">
                            </Input>

                        </div>

                        <!-- Notes Input -->
                        <Input
                            rows="2"
                            label="Notes"
                            type="textarea"
                            v-model="customerForm.notes"
                            :errorText="formState.getFormError('notes')"
                            placeholder="Say something about the customer"
                            @input="customerState.saveStateDebounced('Notes changed')">
                        </Input>

                        <!-- Tags -->
                        <SelectTags
                            label="Tags"
                            :options="tags"
                            placeholder="Add tag"
                            v-model="customerForm.tags"
                            :errorText="formState.getFormError('tags')"
                            @change="customerState.saveStateDebounced('Tags changed')" />

                    </div>

                    <!-- Address Input -->
                    <AddressInput
                        title="Address"
                        :onlyValidate="true"
                        :pinLocationOnMap="true"
                        @onDeleted="unsetAddress"
                        @onValidated="setAddress"
                        :address="customerForm.address"
                        triggerClass="space-y-4 p-4 rounded-lg shadow-lg bg-white"
                        :subtitle="customerForm.address ? 'Change customer address' : 'Add customer address'">
                    </AddressInput>

                </div>

                <div
                    v-if="customer"
                    :class="['flex items-center justify-between space-x-4 overflow-hidden rounded-lg p-4 border mb-20', isLoadingCustomer ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">

                    <div class="space-y-2">
                        <p>Delete <span class="font-bold text-black">{{ customerForm.name }}</span>?</p>
                        <p class="text-sm">Once this customer is deleted you will not be able to recover it.</p>
                    </div>

                    <div class="flex justify-end">

                        <Modal
                            triggerType="danger"
                            approveType="danger"
                            :approveLeftIcon="Trash2"
                            triggerText="Delete Customer"
                            approveText="Delete Customer"
                            :approveAction="deleteCustomer"
                            :triggerLoading="isDeletingCustomer"
                            :approveLoading="isDeletingCustomer">

                            <template #content>
                                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                                <p class="mb-8">Are you sure you want to permanently delete <span class="font-bold text-black">{{ customerForm.name }}</span>?</p>
                            </template>

                        </Modal>

                    </div>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

    import isEqual from 'lodash/isEqual';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';
    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import { isEmpty } from '@Utils/stringUtils';
    import Skeleton from '@Partials/Skeleton.vue';
    import SelectTags from '@Partials/SelectTags.vue';
    import { Trash2, MoveLeft } from 'lucide-vue-next';
    import AddressInput from '@Partials/AddressInput.vue';
    import BackdropLoader from '@Partials/BackdropLoader.vue';

    export default {
        inject: ['formState', 'storeState', 'customerState', 'changeHistoryState', 'notificationState'],
        components: {
            Input, Modal, Button, Loader, Select, Popover,
            Skeleton, SelectTags, AddressInput, BackdropLoader
        },
        data() {
            return {
                Trash2,
                MoveLeft,
                tags: [],
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            },
            customerId(newValue) {
                if(newValue) {
                    this.setup();
                    this.setActionButtons();
                }
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            customer() {
                return this.customerState.customer;
            },
            hasCustomer() {
                return this.customerState.hasCustomer;
            },
            customerId() {
                return this.$route.params.customer_id;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingCustomer() {
                return this.customerState.isLoadingCustomer;
            },
            isEditing() {
                return this.$route.name === 'edit-customer';
            },
            isCreating() {
                return this.$route.name === 'create-customer';
            },
            customerForm() {
                return this.customerState.customerForm;
            },
            isSubmitting() {
                if(this.changeHistoryState.actionButtons.length == 0) return false;
                return this.changeHistoryState.actionButtons[1].loading;
            },
            isDeletingCustomer() {
                return this.customerState.isDeletingCustomer;
            }
        },
        methods: {
            isEmpty: isEmpty,
            goBack() {
                this.navigateToCustomers();
            },
            async setup() {
                if(this.customerForm == null) this.customerState.setCustomerForm(null, this.isCreating);
                if(this.isEditing && this.store) await this.showCustomer();

                if(this.store) {

                    this.tags = this.store.customer_tags.map((tag) => {
                        return {
                            label: tag.name,
                            value: tag.id
                        }
                    });

                }
            },
            async navigateToCustomers() {
                await this.$router.replace({
                    name: 'show-customers',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            setAddress(address) {
                this.customerState.customerForm.address = address;
                this.customerState.saveStateDebounced('Address changed');
            },
            unsetAddress() {
                this.customerState.customerForm.address = null;
                this.customerState.saveStateDebounced('Address removed');
            },
            async onView(customer) {
                await this.$router.push({
                    name: 'edit-customer',
                    params: {
                        customer_id: customer.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            async showCustomer() {
                try {

                    this.customerState.isLoadingCustomer = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['address', 'tags'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/customers/${this.customerId}`, config);

                    const customer = response.data;
                    this.customerState.setCustomer(customer);
                    this.customerState.setCustomerForm(customer);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching customer';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch customer:', error);

                    if (error.response?.status === 404) {
                        await this.$router.replace({
                            name: 'show-customers',
                            query: {
                                store_id: this.store.id,
                                searchTerm: this.$route.query.searchTerm,
                                filterExpressions: this.$route.query.filterExpressions,
                                sortingExpressions: this.$route.query.sortingExpressions
                            }
                        });
                    }

                } finally {
                    this.customerState.isLoadingCustomer = false;
                }
            },
            async createCustomer() {

                try {

                    if(this.customerState.isCreatingCustomer) return;

                    this.formState.hideFormErrors();

                    if(this.isEmpty(this.customerForm.first_name)) {
                        this.formState.setFormError('first_name', 'The first name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.customerState.isCreatingCustomer = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.customerForm,
                        store_id: this.store.id
                    }

                    const response = await axios.post(`/api/customers`, data);
                    const createdCustomer = response.data.customer;

                    const originalState = this.changeHistoryState.getOriginalState();

                    if(!isEqual(this.customerForm.tags, originalState.tags)) {

                        //  Update store silently
                        this.storeState.silentUpdate();

                    }

                    this.notificationState.showSuccessNotification(`Customer created`);
                    this.customerState.saveOriginalState('Original customer');
                    await this.onView(createdCustomer);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating customer';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create customer:', error);
                } finally {
                    this.customerState.isCreatingCustomer = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async updateCustomer() {

                try {

                    if(this.customerState.isUpdatingCustomer) return;

                    this.formState.hideFormErrors();

                    if(this.isEmpty(this.customerForm.first_name)) {
                        this.formState.setFormError('first_name', 'The first name is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.customerState.isUpdatingCustomer = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.customerForm,
                        store_id: this.store.id
                    }

                    await axios.put(`/api/customers/${this.customerForm.id}`, data);

                    const originalState = this.changeHistoryState.getOriginalState();

                    if(!isEqual(this.customerForm.tags, originalState.tags)) {

                        //  Update store silently
                        this.storeState.silentUpdate();

                    }

                    this.notificationState.showSuccessNotification(`Customer updated`);
                    this.customerState.saveOriginalState('Original customer');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating customer';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update customer:', error);
                } finally {
                    this.customerState.isUpdatingCustomer = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }

            },
            async deleteCustomer(hideModal) {

                try {

                    if(this.customerState.isDeletingCustomer) return;

                    this.customerState.isDeletingCustomer = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/customers/${this.customer.id}`, config);

                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 500));    //  Wait for modal to close

                    this.notificationState.showSuccessNotification('Customer deleted');

                    await this.navigateToCustomers();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting customer';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete customer:', error);
                    hideModal();
                } finally {
                    this.customerState.isDeletingCustomer = false;
                }

            },
            setActionButtons() {
                if(this.isCreating || this.isEditing) {
                    this.changeHistoryState.removeButtons();
                    this.changeHistoryState.addDiscardButton();
                    this.changeHistoryState.addActionButton(
                        this.isEditing ? 'Save Changes' : 'Create Customer',
                        this.isEditing ? this.updateCustomer : this.createCustomer,
                        'primary',
                        null,
                    );
                }
            },
            setCustomerForm(customerForm) {
                this.customerState.customerForm = customerForm;
            }
        },
        beforeRouteLeave(to, from, next) {
            //  Triggered when navigating between routes not sharing same component e.g from "edit-customer" to "show-store-home"
            if (this.changeHistoryState.hasChangeHistory) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            next();
        },
        unmounted() {
            this.customerState.reset();
        },
        created() {

            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];

            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setCustomerForm;
            }

        }
    };

</script>
